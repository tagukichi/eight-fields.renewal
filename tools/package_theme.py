#!/usr/bin/env python3
"""Build the installable WordPress theme archive.

Produces dist/eight-fields.zip, laid out so that WordPress's
「外観 → テーマ → 新規追加 → テーマのアップロード」 accepts it directly.

Usage: python3 tools/package_theme.py
"""
import pathlib
import shutil
import sys
import zipfile

ROOT = pathlib.Path(__file__).parent.parent
THEME = ROOT / "theme/eight-fields"
DIST = ROOT / "dist"
OUT = DIST / "eight-fields.zip"

# Editor leftovers and OS junk have no business inside a distributed theme.
SKIP_NAMES = {".DS_Store", "Thumbs.db", ".gitkeep"}
SKIP_SUFFIXES = {".map", ".orig", ".rej", ".bak"}
SKIP_DIRS = {"__pycache__", ".git", "node_modules"}

REQUIRED = [
    "style.css",
    "functions.php",
    "index.php",
    "screenshot.png",
    "data/seed.json",
    "assets/css/style.css",
    "assets/js/main.js",
]


def check():
    missing = [p for p in REQUIRED if not (THEME / p).exists()]
    if missing:
        sys.exit("missing from the theme: " + ", ".join(missing))

    header = (THEME / "style.css").read_text(encoding="utf-8")
    if "Theme Name:" not in header:
        sys.exit("style.css has no theme header")


def files():
    for path in sorted(THEME.rglob("*")):
        if not path.is_file():
            continue
        if path.name in SKIP_NAMES or path.suffix in SKIP_SUFFIXES:
            continue
        if SKIP_DIRS & set(path.relative_to(THEME).parts):
            continue
        yield path


def main():
    check()

    if DIST.exists():
        shutil.rmtree(DIST)
    DIST.mkdir()

    count = 0
    with zipfile.ZipFile(OUT, "w", zipfile.ZIP_DEFLATED, compresslevel=9) as z:
        for path in files():
            z.write(path, pathlib.Path("eight-fields") / path.relative_to(THEME))
            count += 1

    size = OUT.stat().st_size
    print(f"{OUT.relative_to(ROOT)}: {count} files, {size / 1024 / 1024:.1f} MB")


if __name__ == "__main__":
    main()
