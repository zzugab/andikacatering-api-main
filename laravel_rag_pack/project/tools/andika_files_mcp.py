"""Read-only MCP filesystem server for Hermes."""

from pathlib import Path
import re

from mcp.server.fastmcp import FastMCP

ROOT = Path(__file__).resolve().parents[3]
BLOCKED = {".git", ".env", "vendor", "node_modules", "storage", "dump-db"}
SUFFIXES = {".php", ".md", ".txt", ".json", ".yml", ".yaml", ".xml", ".js", ".css", ".sql"}
MAX_RESULT = 12000
mcp = FastMCP("andika-files")


def safe_path(path: str):
    target = (ROOT / (path or ".")).resolve()
    try:
        relative = target.relative_to(ROOT)
    except ValueError as exc:
        raise ValueError("path berada di luar root project") from exc
    if set(relative.parts) & BLOCKED or target.name == ".env" or ".env." in target.name:
        raise ValueError("path diblokir")
    return target, relative.as_posix()


def clip(value: str) -> str:
    return value if len(value) <= MAX_RESULT else value[:MAX_RESULT] + "\n[hasil dipotong]"


@mcp.tool()
def list_project_files(prefix: str = ".") -> str:
    """List readable text files below a project directory."""
    base, _ = safe_path(prefix)
    if not base.is_dir():
        raise ValueError("path bukan direktori")
    files = []
    for item in sorted(base.rglob("*")):
        if item.is_file() and item.suffix.lower() in SUFFIXES:
            try:
                _, relative = safe_path(item.relative_to(ROOT).as_posix())
            except ValueError:
                continue
            files.append(relative)
    return clip("\n".join(files) or "(tidak ada file)")


@mcp.tool()
def search_project(query: str, prefix: str = ".") -> str:
    """Search readable source files with a regular expression."""
    if not query or len(query) > 200:
        raise ValueError("query wajib 1-200 karakter")
    base, _ = safe_path(prefix)
    pattern = re.compile(query, re.IGNORECASE)
    hits = []
    for item in sorted(base.rglob("*")):
        if not item.is_file() or item.suffix.lower() not in SUFFIXES:
            continue
        try:
            _, relative = safe_path(item.relative_to(ROOT).as_posix())
            lines = item.read_text(encoding="utf-8", errors="replace").splitlines()
        except (OSError, ValueError):
            continue
        for number, line in enumerate(lines, 1):
            if pattern.search(line):
                hits.append(f"{relative}:{number}: {line[:300]}")
                if len(hits) >= 100:
                    return clip("\n".join(hits))
    return clip("\n".join(hits) or "(tidak ditemukan)")


@mcp.tool()
def read_project_file(path: str, start_line: int = 1, end_line: int = 240) -> str:
    """Read at most 300 lines from a readable project text file."""
    if start_line < 1 or end_line < start_line or end_line - start_line > 300:
        raise ValueError("rentang baris tidak valid; maksimal 300 baris")
    target, _ = safe_path(path)
    if not target.is_file() or target.suffix.lower() not in SUFFIXES:
        raise ValueError("hanya file teks yang dapat dibaca")
    lines = target.read_text(encoding="utf-8", errors="replace").splitlines()
    return clip("\n".join(f"{i}: {line}" for i, line in enumerate(lines[start_line - 1:end_line], start_line)))


if __name__ == "__main__":
    mcp.run(transport="stdio")
