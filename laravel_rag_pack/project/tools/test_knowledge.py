"""Offline checks for privacy boundaries, retrieval and context limits."""

import importlib.util
import json
from pathlib import Path
import re
import sys
import tempfile
import unittest

SPEC = importlib.util.spec_from_file_location("andika_knowledge", Path(__file__).with_name("knowledge.py"))
knowledge = importlib.util.module_from_spec(SPEC)
sys.modules[SPEC.name] = knowledge
SPEC.loader.exec_module(knowledge)


class KnowledgeTests(unittest.TestCase):
    def test_stack_follows_lock_updates_and_distinguishes_php_constraint(self):
        with tempfile.TemporaryDirectory() as temporary:
            root = Path(temporary)
            (root / "composer.json").write_text(json.dumps({"require": {"php": "^8.1", "laravel/framework": "^13.30"}}))
            lock = {"packages": [{"name": "laravel/framework", "version": "v13.38.0", "require": {"php": "^8.3"}}]}
            (root / "composer.lock").write_text(json.dumps(lock))
            rendered, count = knowledge.stack_inventory(root)
            self.assertEqual(count, 2)
            self.assertIn("v13.38.0", rendered)
            self.assertIn("^8.1", rendered)
            self.assertIn("framework yang terkunci: `^8.3`", rendered)
            lock["packages"][0]["version"] = "v13.39.0"
            (root / "composer.lock").write_text(json.dumps(lock))
            rendered, _ = knowledge.stack_inventory(root)
            self.assertIn("v13.39.0", rendered)
            self.assertNotIn("v13.38.0", rendered)

    def test_comment_removal_preserves_lines_and_quoted_urls(self):
        source = "<?php\n// public function fake()\n/*\nprivate function hidden()\n*/\npublic function real($url = 'http://local/#ok') {}\n"
        cleaned = knowledge.strip_comments(source)
        self.assertEqual(source.count("\n"), cleaned.count("\n"))
        self.assertIn("http://local/#ok", cleaned)
        self.assertEqual([m[0] for m in knowledge.methods(cleaned)], ["real"])
        self.assertEqual(list(knowledge.methods(cleaned))[0][2], 6)

    def test_refresh_exports_declarations_without_body_or_seed_env_data(self):
        with tempfile.TemporaryDirectory() as temporary:
            root = Path(temporary)
            for folder in ("app/Http/Controllers", "app/Models", "database/migrations", "database/seeders", "routes"):
                (root / folder).mkdir(parents=True)
            (root / ".env").write_text("PASSWORD=ENV_PRIVATE_SENTINEL")
            (root / "database/seeders/Users.php").write_text("SEED_PRIVATE_SENTINEL")
            (root / "app/Http/Controllers/Demo.php").write_text("""<?php
class Demo {
    // public function obsolete() {}
    public function reset(string $key = 'DEFAULT_PRIVATE_SENTINEL') {
        $mail->Password = 'BODY_PRIVATE_SENTINEL';
    }
}
""")
            (root / "app/Models/DemoModel.php").write_text("""<?php
class DemoModel {
    protected $table = 'demos';
    protected $fillable = ['uuid', 'user_id'];
    public function user() {
        return $this->belongsTo(UserModel::class, 'user_id', 'uuid');
    }
}
""")
            (root / "database/migrations/create_demos.php").write_text("""<?php
Schema::create('demos', function ($table) {
    $table->string('name')->default('SCHEMA_PRIVATE_SENTINEL')->nullable();
});
""")
            (root / "routes/api.php").write_text("""<?php
// Route::get('/obsolete', [Demo::class, 'obsolete']);
Route::post('/reset', [Demo::class, 'reset']);
""")
            destination = root / "output"
            knowledge.refresh(root, destination)
            export = "\n".join(p.read_text() for p in destination.glob("*.md"))
            for name in ("ENV", "SEED", "DEFAULT", "BODY", "SCHEMA"):
                self.assertFalse(name + "_PRIVATE_SENTINEL" in export)
            self.assertNotIn("obsolete", export)
            self.assertIn("public function reset", export)
            self.assertIn("keys=user_id, uuid", export)
            self.assertIn("string(name)", export)
            before = {p.name: p.read_bytes() for p in destination.glob("*.md")}
            knowledge.refresh(root, destination)
            self.assertEqual(before, {p.name: p.read_bytes() for p in destination.glob("*.md")})

    def test_chunking_keeps_heading_and_bounds_large_documents(self):
        with tempfile.TemporaryDirectory() as temporary:
            root = Path(temporary)
            (root / "sample.md").write_text("# Root\n\n## Section\n" + ("abc " * 500) + "\n")
            sections = list(knowledge.chunks(root, limit=300))
            self.assertGreater(len(sections), 2)
            self.assertTrue(all(len(c.body) <= 300 for c in sections))
            self.assertTrue(all(c.source == "sample.md" for c in sections))
            self.assertTrue(any(c.title == "Section" for c in sections))

    def test_retrieval_finds_actual_domain_and_macro_facts(self):
        documents = list(knowledge.chunks())
        checks = (
            ("success argumen kedua message HTTP", "Argumen kedua"),
            ("storeWithCustomer customer order payment", "Urutan write"),
            ("cicilan pelunasan", "destroyInstallment"),
            ("migration users role_id is_active", "drift"),
            ("eventEnd event_finish", "event_finish"),
        )
        for query, expected in checks:
            with self.subTest(query=query):
                passages = "\n".join(c.body for _, c in knowledge.rank(query, documents)[:4])
                self.assertIn(expected.lower(), passages.lower())

    def test_bounded_prompt_includes_query_and_source_citations(self):
        query = "storeWithCustomer customer order payment"
        output = knowledge.render(query, knowledge.rank(query, list(knowledge.chunks())), budget=7500, prompt=True)
        self.assertLessEqual(len(output), 7500)
        self.assertIn("[knowledge/03_DOMAIN_FLOWS.md:", output)
        self.assertTrue(output.endswith(query + "\n"))
        self.assertIn("</retrieved_context>", output)
        with self.assertRaises(ValueError):
            knowledge.render(query, [], budget=100, prompt=True)

    def test_empty_retrieval_does_not_fabricate_evidence(self):
        result = knowledge.rank("zxqvnonexistent987", list(knowledge.chunks()))
        self.assertEqual(result, [])
        self.assertIn("Tidak ada passage relevan", knowledge.render("zxqvnonexistent987", result))

    def test_existing_smtp_literals_are_not_in_exported_pack(self):
        # Do not print literal values, including in assertion failure output.
        auth = (knowledge.ROOT / "app/Http/Controllers/AuthController.php").read_text()
        literals = re.findall(r"\$mail->(?:Password|Username)\s*=\s*['\"]([^'\"]+)['\"]", auth)
        self.assertTrue(literals, "Expected SMTP literal declarations were not found; reassess privacy check.")
        exported = "\n".join(p.read_text() for p in knowledge.PACK.rglob("*") if p.is_file() and p.suffix in (".md", ".txt", ".py"))
        for value in literals:
            self.assertFalse(value in exported, "An SMTP literal was exported; inspect locally without printing credentials.")


if __name__ == "__main__":
    unittest.main()
