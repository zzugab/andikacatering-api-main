#!/usr/bin/env python3
"""Read-only Ollama tool-calling agent for this repository."""

import argparse
import json
import os
from pathlib import Path
import re
import sys
from urllib.request import Request, urlopen

ROOT = Path(__file__).resolve().parents[3]
PACK = ROOT / "laravel_rag_pack" / "project"
DEFAULT_MODEL = os.environ.get("OLLAMA_MODEL", "deepseek-r1:14b")
MAX_RESULT = 12000
BLOCKED = {".git", ".env", "vendor", "node_modules", "storage", "dump-db"}
SUFFIXES = {".php", ".md", ".txt", ".json", ".yml", ".yaml", ".xml", ".js", ".css", ".sql"}


def safe_path(raw):
    target = (ROOT / (raw or ".")).resolve()
    try:
        relative = target.relative_to(ROOT)
    except ValueError as exc:
        raise ValueError("path berada di luar root proyek") from exc
    if set(relative.parts) & BLOCKED or target.name == ".env" or ".env." in target.name:
        raise ValueError("path diblokir untuk mencegah pembacaan secret/log/data")
    return target, relative.as_posix()


def clip(value):
    value = str(value)
    return value if len(value) <= MAX_RESULT else value[:MAX_RESULT] + "\n[hasil dipotong]"


def list_project_files(prefix=""):
    base, _ = safe_path(prefix)
    if not base.is_dir():
        raise ValueError("path bukan direktori")
    result = []
    for path in sorted(base.rglob("*")):
        if not path.is_file() or path.suffix.lower() not in SUFFIXES:
            continue
        try:
            _, relative = safe_path(path.relative_to(ROOT).as_posix())
        except ValueError:
            continue
        result.append(relative)
        if len(result) >= 300:
            break
    return clip("\n".join(result) or "(tidak ada file teks)")


def search_project(query, prefix=""):
    if not query or len(query) > 200:
        raise ValueError("query wajib berisi 1-200 karakter")
    base, _ = safe_path(prefix)
    pattern = re.compile(query, re.I)
    hits = []
    for path in sorted(base.rglob("*")):
        if not path.is_file() or path.suffix.lower() not in SUFFIXES:
            continue
        try:
            _, relative = safe_path(path.relative_to(ROOT).as_posix())
            lines = path.read_text(encoding="utf-8", errors="replace").splitlines()
        except (OSError, ValueError):
            continue
        for number, line in enumerate(lines, 1):
            if pattern.search(line):
                hits.append(f"{relative}:{number}: {line[:300]}")
                if len(hits) >= 100:
                    return clip("\n".join(hits))
    return clip("\n".join(hits) or "(tidak ditemukan)")


def read_project_file(path, start_line=1, end_line=240):
    if start_line < 1 or end_line < start_line or end_line - start_line > 300:
        raise ValueError("rentang baris tidak valid; maksimal 300 baris")
    target, relative = safe_path(path)
    if not target.is_file() or target.suffix.lower() not in SUFFIXES:
        raise ValueError("hanya file teks source/dokumentasi yang dapat dibaca")
    lines = target.read_text(encoding="utf-8", errors="replace").splitlines()
    return clip("\n".join(f"{i}: {line}" for i, line in enumerate(lines[start_line - 1:end_line], start_line)))


FUNCTIONS = {"list_project_files": list_project_files, "search_project": search_project, "read_project_file": read_project_file}
TOOLS = [
    {"type": "function", "function": {"name": "list_project_files", "description": "List safe text files under the project root.", "parameters": {"type": "object", "properties": {"prefix": {"type": "string"}}, "required": []}}},
    {"type": "function", "function": {"name": "search_project", "description": "Search safe project files with a regular expression and return paths and line numbers.", "parameters": {"type": "object", "properties": {"query": {"type": "string"}, "prefix": {"type": "string"}}, "required": ["query"]}}},
    {"type": "function", "function": {"name": "read_project_file", "description": "Read a bounded line range from one safe project text file.", "parameters": {"type": "object", "properties": {"path": {"type": "string"}, "start_line": {"type": "integer"}, "end_line": {"type": "integer"}}, "required": ["path"]}}},
]


def call_ollama(url, model, messages):
    payload = {"model": model, "messages": messages, "tools": TOOLS, "stream": False, "options": {"temperature": 0.6, "num_ctx": 4096}, "think": False}
    request = Request(url.rstrip("/") + "/api/chat", data=json.dumps(payload).encode(), headers={"Content-Type": "application/json"})
    with urlopen(request, timeout=300) as response:
        return json.loads(response.read().decode())


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("question")
    parser.add_argument("--model", default=DEFAULT_MODEL)
    parser.add_argument("--url", default=os.environ.get("OLLAMA_URL", "http://127.0.0.1:11434"))
    parser.add_argument("--max-turns", type=int, default=6)
    args = parser.parse_args()
    system = (PACK / "prompts/maintenance.txt").read_text(encoding="utf-8")
    brief = (PACK / "knowledge/00_PROJECT_BRIEF.md").read_text(encoding="utf-8")
    messages = [{"role": "system", "content": system}, {"role": "user", "content": "Project root tersedia melalui tools baca-saja.\n\nKonteks:\n" + brief + "\n\nPertanyaan:\n" + args.question}]
    for _ in range(args.max_turns):
        message = call_ollama(args.url, args.model, messages).get("message", {})
        messages.append(message)
        calls = message.get("tool_calls") or []
        if not calls:
            print(message.get("content", ""))
            return
        for call in calls:
            function = call.get("function", {})
            name = function.get("name")
            arguments = function.get("arguments") or {}
            try:
                result = FUNCTIONS[name](**arguments) if name in FUNCTIONS else "ERROR: tool tidak diizinkan"
            except Exception as exc:
                result = "ERROR: " + str(exc)
            messages.append({"role": "tool", "tool_name": name, "content": result})
    print("Agent berhenti setelah batas tool-call; ulangi dengan pertanyaan lebih kecil.", file=sys.stderr)


if __name__ == "__main__":
    main()
