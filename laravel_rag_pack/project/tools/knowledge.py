#!/usr/bin/env python3
"""Offline declaration inventory and bounded lexical retrieval. Python 3.9+."""

import argparse
from collections import Counter
from dataclasses import dataclass
from hashlib import sha256
import json
import math
from pathlib import Path
import re
import sys

PACK = Path(__file__).resolve().parents[1]
ROOT = PACK.parents[1]
KNOWLEDGE = PACK / "knowledge"


def strip_comments(source):
    """Remove PHP comments, preserving strings, offsets and line numbers.

    Lightweight scanner for this project's declarations, not a PHP AST parser.
    Heredoc and dynamic PHP syntax need manual verification.
    """
    pattern = re.compile(r"'(?:\\.|[^'\\])*'|\"(?:\\.|[^\"\\])*\"|//[^\n]*|/\*[\s\S]*?\*/|\#[^\n]*")
    return pattern.sub(
        lambda m: re.sub(r"[^\n]", " ", m.group())
        if m.group().startswith(("//", "/*", "#")) else m.group(), source
    )


def line_at(source, offset):
    return source.count("\n", 0, offset) + 1


def methods(source):
    pattern = r"^\s*((?:public|protected|private)\s+(?:static\s+)?function\s+(\w+)\s*\([^)]*\)(?:\s*:\s*[?\\\w|]+)?)"
    for match in re.finditer(pattern, source, re.M):
        signature = " ".join(match.group(1).split())
        # Never export literal defaults, even if a future signature contains one.
        signature = re.sub(r"'(?:\\.|[^'\\])*'|\"(?:\\.|[^\"\\])*\"", "'<literal>'", signature)
        yield match.group(2), signature, line_at(source, match.start(1))


def identifiers(value):
    return re.findall(r"['\"]([A-Za-z_][A-Za-z_0-9]*)['\"]", value)


def heading(path, source, root):
    relative = path.relative_to(root).as_posix()
    digest = sha256(source.encode()).hexdigest()[:12]
    return ["", "## " + path.stem, "", "Source: `" + relative + "`; SHA256 `" + digest + "`.", ""]


def stack_inventory(root):
    lines = ["# Versi stack dan dependency", "",
             "Generated dari metadata Composer dan tag image; bukan inspeksi runtime VM.",
             "Constraint menyatakan rentang yang diizinkan, lock menyatakan versi terpilih."]
    files = {}
    for name in ("composer.json", "composer.lock"):
        path = root / name
        if path.is_file() and not path.is_symlink():
            raw = path.read_text(encoding="utf-8")
            files[name] = json.loads(raw)
            lines.extend(heading(path, raw, root))
    manifest = files.get("composer.json", {})
    locked = files.get("composer.lock", {})
    packages = {p["name"]: p for p in locked.get("packages", []) + locked.get("packages-dev", [])}
    lines.extend(["", "## Dependency langsung", "",
                  "| Grup | Dependency | Constraint | Versi lock |",
                  "| --- | --- | --- | --- |"])
    for group in ("require", "require-dev"):
        for name, constraint in sorted(manifest.get(group, {}).items()):
            version = packages.get(name, {}).get("version", "tidak ada versi package di lock")
            lines.append("| " + group + " | `" + name + "` | `" + constraint + "` | " + version + " |")
    framework_php = packages.get("laravel/framework", {}).get("require", {}).get("php")
    if framework_php:
        lines.extend(["", "Kebutuhan PHP framework yang terkunci: `" + framework_php + "`."])
    docker = root / "Dockerfile"
    if docker.is_file() and not docker.is_symlink():
        raw = docker.read_text(encoding="utf-8")
        lines.extend(heading(docker, raw, root))
        for image in re.findall(r"^FROM\s+(\S+)", raw, re.M):
            lines.append("Image dasar: `" + image + "`; tag bukan versi runtime terverifikasi.")
    return "\n".join(lines) + "\n", len(files) + int(docker.is_file() and not docker.is_symlink())


def refresh(root=ROOT, destination=None):
    """Extract only declarations; never traverse env, dumps, logs or seed data."""
    root = Path(root)
    destination = Path(destination) if destination else KNOWLEDGE / "generated"
    destination.mkdir(parents=True, exist_ok=True)
    preamble = "\n\nGenerated statis oleh tools/knowledge.py refresh. Deklarasi source, bukan bukti runtime/schema hidup.\n"
    counts = {}
    for folder, output, title in (
        ("app/Http/Controllers", "CONTROLLERS.md", "Inventaris controller"),
        ("app/Models", "MODELS.md", "Inventaris model"),
        ("database/migrations", "MIGRATIONS.md", "Inventaris migration"),
    ):
        lines = ["# " + title + preamble]
        paths = sorted((root / folder).glob("*.php"))
        for path in paths:
            if path.is_symlink():
                continue
            raw = path.read_text(encoding="utf-8")
            source = strip_comments(raw)
            lines.extend(heading(path, raw, root))
            if output != "MIGRATIONS.md":
                if output == "MODELS.md":
                    table = re.search(r"\$table\s*=\s*['\"]([A-Za-z_0-9]+)['\"]", source)
                    if table:
                        lines.append("Tabel: `" + table.group(1) + "`.")
                    fillable = re.search(r"\$fillable\s*=\s*\[([\s\S]*?)\]", source)
                    if fillable:
                        lines.append("Fillable: " + ", ".join("`" + v + "`" for v in identifiers(fillable.group(1))) + ".")
                    traits = re.findall(r"^\s+use\s+([\w, ]+);", source, re.M)
                    if traits:
                        lines.append("Traits: `" + "; ".join(traits) + "`.")
                    pk = re.search(r"\$primaryKey\s*=\s*['\"]([\w]+)['\"]", source)
                    lines.append("Primary key: " + ("eksplisit `" + pk.group(1) + "`" if pk else "tidak dioverride di class; periksa parent/trait/schema") + ".")
                method_list = list(methods(source))
                for name, signature, number in method_list:
                    lines.append("- L" + str(number) + " `" + signature + "`")
                if output == "MODELS.md":
                    relation = re.compile(r"return\s+\$this->(belongsTo|hasMany|hasOne|belongsToMany)\(([^;\n]+)\)")
                    for match in relation.finditer(source):
                        previous = [m for m in method_list if m[2] <= line_at(source, match.start())]
                        owner = previous[-1][0] if previous else "?"
                        target = re.match(r"\s*([\\\w]+)::class", match.group(2))
                        keys = identifiers(match.group(2))
                        summary = (target.group(1) if target else "dynamic target") + "; keys=" + ", ".join(keys)
                        lines.append("- Relasi `" + owner + "`: `" + match.group(1) + "` → " + summary)
            else:
                for match in re.finditer(r"Schema::create\(\s*['\"]([\w]+)['\"]", source):
                    lines.append("Create tabel: `" + match.group(1) + "`.")
                for match in re.finditer(r"\$table->(\w+)\(\s*(?:['\"]([\w]+)['\"])?([^;\n]*)", source):
                    kind, name, remainder = match.groups()
                    flags = [flag for flag in ("nullable", "unique", "index") if "->" + flag + "(" in remainder]
                    lines.append("- L" + str(line_at(source, match.start())) + " `" + kind + "(" + (name or "") + ")`" + ("; " + ", ".join(flags) if flags else ""))
                lines.append("Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.")
        (destination / output).write_text("\n".join(lines) + "\n", encoding="utf-8")
        counts[output] = len(paths)

    lines = ["# Inventaris deklarasi route" + preamble,
             "Baca grup middleware dan kurung penutup di source asli. Closure/group di bawah adalah penunjuk; bukan effective route:list."]
    for name in ("api.php", "web.php"):
        path = root / "routes" / name
        if not path.is_file() or path.is_symlink():
            continue
        raw = path.read_text(encoding="utf-8")
        lines.extend(heading(path, raw, root))
        for number, line in enumerate(strip_comments(raw).splitlines(), 1):
            # Fixed route files only. No closure body or unrelated PHP is copied.
            if line.strip().startswith("Route::"):
                declaration = line.strip().split("function", 1)[0].rstrip()
                lines.append("- L" + str(number) + " `" + declaration + "`")
    (destination / "ROUTES.md").write_text("\n".join(lines) + "\n", encoding="utf-8")
    counts["ROUTES.md"] = sum((root / "routes" / n).is_file() for n in ("api.php", "web.php"))
    stack, counts["STACK.md"] = stack_inventory(root)
    (destination / "STACK.md").write_text(stack, encoding="utf-8")
    return counts


@dataclass
class Chunk:
    source: str
    line: int
    title: str
    body: str


def chunks(folder=KNOWLEDGE, limit=1600):
    folder = Path(folder)
    for path in sorted(folder.rglob("*.md")):
        if path.is_symlink():
            continue
        relative = path.relative_to(folder).as_posix()
        title = path.stem
        buffer = []
        length = 0
        start = 1
        for number, line in enumerate(path.read_text(encoding="utf-8").splitlines(), 1):
            if line.startswith("#") or length + len(line) + 1 > limit:
                if buffer and any(item.strip() for item in buffer):
                    yield Chunk(relative, start, title, "\n".join(buffer).strip())
                buffer, length, start = [], 0, number
                if line.startswith("#"):
                    title = line.lstrip("# ")
            # Bound even future generated long declaration lines.
            for offset in range(0, max(len(line), 1), limit):
                part = line[offset:offset + limit]
                if length + len(part) + 1 > limit and buffer:
                    yield Chunk(relative, start, title, "\n".join(buffer).strip())
                    buffer, length, start = [], 0, number
                buffer.append(part)
                length += len(part) + 1
        if buffer and any(item.strip() for item in buffer):
            yield Chunk(relative, start, title, "\n".join(buffer).strip())


def tokens(value):
    expanded = re.sub(r"([a-z0-9])([A-Z])", r"\1 \2", value)
    return re.findall(r"[a-z0-9]+", expanded.lower())


ALIASES = {
    "pegawai": "employee user", "karyawan": "employee user", "cicilan": "installment payment",
    "pembayaran": "payment finance", "pelunasan": "installment completed",
    "pesanan": "order", "pelanggan": "customer", "gudang": "inventory logistic",
    "stok": "stock inventory quantity", "tanggal": "date timezone", "resep": "recipes",
    "paket": "package", "foto": "image", "gambar": "image upload", "izin": "role middleware",
    "penelusuran": "navigation route", "alur": "flow", "halusinasi": "fakta source",
}
STOP = set("yang dan di ke dari ini itu saya anda untuk dengan pada adalah apa bagaimana mengapa apakah bisa to the a of in is".split())


def rank(query, documents):
    query_tokens = [t for t in tokens(query) if t not in STOP]
    expanded = set(query_tokens)
    for term in query_tokens:
        expanded.update(tokens(ALIASES.get(term, "")))
    counters = [Counter(tokens(d.source + " " + d.title + " " + d.body)) for d in documents]
    if not counters or not expanded:
        return []
    average = sum(sum(c.values()) for c in counters) / len(counters) or 1
    frequency = Counter()
    for counter in counters:
        frequency.update(counter.keys())
    results = []
    for document, counter in zip(documents, counters):
        score = 0.0
        size = sum(counter.values())
        for term in expanded:
            count = counter[term]
            if count:
                inverse = math.log(1 + (len(documents) - frequency[term] + 0.5) / (frequency[term] + 0.5))
                score += inverse * (count * 2.2) / (count + 1.2 * (0.25 + 0.75 * size / average))
        # Prefer documents actually about the identifier, not generic mentions.
        title_tokens = set(tokens(document.source + " " + document.title))
        score += 2 * len(set(query_tokens) & title_tokens)
        if score > 0:
            results.append((score, document))
    return sorted(results, key=lambda item: (-item[0], item[1].source, item[1].line))


def render(query, results, top_k=4, budget=7500, prompt=False, pack=PACK):
    intro = ""
    ending = ""
    if prompt:
        intro = (pack / "prompts/maintenance.txt").read_text(encoding="utf-8").strip()
        intro += "\n\n<project_brief>\n" + (pack / "knowledge/00_PROJECT_BRIEF.md").read_text(encoding="utf-8").strip() + "\n</project_brief>\n\n<retrieved_context>\n"
        ending = "\n</retrieved_context>\n\nPermintaan pengguna:\n" + query + "\n"
    else:
        intro = "Konteks terpilih. Referensi baris berikut merujuk dokumen knowledge, bukan source PHP.\n"
    if len(intro) + len(ending) + 180 > budget:
        raise ValueError("Budget terlalu kecil untuk instruksi/brief/pertanyaan; tambah --budget-chars atau pendekkan query.")
    parts = []
    used = len(intro) + len(ending)
    selected = 0
    for score, item in results:
        if selected >= top_k:
            break
        if prompt and item.source == "00_PROJECT_BRIEF.md":
            continue
        header = "\n[knowledge/" + item.source + ":" + str(item.line) + "; " + item.title + "]\n"
        remaining = budget - used - len(header) - 2
        if remaining < 180:
            break
        body = item.body
        if len(body) > remaining:
            body = body[:remaining - 22] + "\n[potongan dibatasi]"
        fragment = header + body + "\n"
        parts.append(fragment)
        used += len(fragment)
        selected += 1
    if not parts:
        parts.append("\nTidak ada passage relevan. Cari identifier lain atau periksa source asli.\n")
    return intro + "".join(parts) + ending


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    commands = parser.add_subparsers(dest="command", required=True)
    commands.add_parser("refresh", help="Refresh inventaris deklarasi saja, tanpa boot Laravel")
    search = commands.add_parser("search", help="Cari Markdown knowledge secara offline")
    search.add_argument("query")
    search.add_argument("--top-k", type=int, default=4)
    search.add_argument("--budget-chars", type=int, default=7500, help="Batas seluruh output; bukan hitungan token")
    search.add_argument("--prompt", action="store_true", help="Sertakan brief, instruksi, dan query")
    args = parser.parse_args()
    if args.command == "refresh":
        for output, count in refresh().items():
            print(output + ": " + str(count) + " source files")
        return
    if args.top_k < 1 or args.budget_chars < 256 or not args.query.strip():
        parser.error("Query wajib diisi, top-k >= 1, budget-chars >= 256.")
    try:
        result = render(args.query, rank(args.query, list(chunks())), args.top_k, args.budget_chars, args.prompt)
    except ValueError as exc:
        parser.error(str(exc))
    sys.stdout.write(result)
    print("Catatan: batas karakter bukan batas token. Sisakan konteks untuk history dan output/reasoning.", file=sys.stderr)


if __name__ == "__main__":
    main()
