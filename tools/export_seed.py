#!/usr/bin/env python3
"""Export the site's content as a JSON file the WordPress theme can import.

The static design and the theme have to say the same thing, so the seed the
theme ships is generated from the same sources the static build reads rather
than typed out a second time.

Usage: python3 tools/export_seed.py
"""
import json
import pathlib
import re
import sys

sys.path.insert(0, str(pathlib.Path(__file__).parent))

from build import SERVICES, SRC, parse_page  # noqa: E402
from service_detail import DETAIL  # noqa: E402

sys.path.insert(0, str(pathlib.Path(__file__).parent / "content"))
from services import SERVICE_PAGES  # noqa: E402

OUT = pathlib.Path(__file__).parent.parent / "theme/eight-fields/data/seed.json"


def services():
    rows = []
    for i, s in enumerate(SERVICES):
        d = DETAIL[s["slug"]]
        # The現行サイト copy, where it has been transcribed for this service.
        page = SERVICE_PAGES.get(s["slug"])
        rows.append(
            {
                "slug": s["slug"],
                "title": s.get("title_long") or s["title"],
                "menu_order": i + 1,
                "en": s["en"],
                "excerpt": s["lead"],
                "catch": page["catch"] if page else s["catch"],
                "sub": page["sub"] if page else d.get("catch_sub", ""),
                "image": s["img"],
                "fit": "contain" if s.get("fit") == "contain" else "",
                "content": list(page["intro"]) if page else list(d["intro"]),
                "sections": [
                    {
                        "heading": sec.get("heading", ""),
                        "style": sec.get("style", "band"),
                        "side": sec.get("side", "right"),
                        "fit": sec.get("fit", ""),
                        "text": sec.get("text", ""),
                        "list": sec.get("list", []),
                        "list_heading": sec.get("list_heading", ""),
                        "boxed": bool(sec.get("boxed")),
                    }
                    for sec in (page["sections"] if page else [])
                ],
                "faq": [{"q": q, "a": a} for q, a in d["faq"]],
            }
        )
    return rows


def pages():
    """The front matter of every source page, minus the ones WordPress owns.

    `service_` is the CPT archive and each service detail is a `service` post,
    so neither needs a WordPress page.
    """
    skip = {"service_", "service"}
    rows = []
    for path in sorted((SRC / "pages").glob("*.html")):
        meta, body = parse_page(path)
        slug = meta.get("path", "").strip("/")
        if slug in skip:
            continue
        home = meta.get("home") == "1"
        rows.append(
            {
                "slug": slug or "home",
                # The front page's own <title> is the SEO line; in WordPress the
                # page just needs a name an editor can find in the page list.
                "title": "ホーム" if home else meta["title"],
                "en": meta.get("en", ""),
                "lead": meta.get("lead", ""),
                "image": meta.get("hero", ""),
                "home": home,
                "body": article_paragraphs(body),
            }
        )
    return rows


def article_paragraphs(body):
    """The prose inside the page's `.ef-article`, as plain paragraphs.

    Templates own the layout, but the copy that sits in the body of a page —
    the representative's message, for one — belongs to the editor, so it is
    seeded as post content rather than baked into PHP.
    """
    m = re.search(r'<div class="ef-article[^"]*">(.*?)</div>', body, re.S)
    if not m:
        return []
    out = []
    for para in re.findall(r"<p>(.*?)</p>", m.group(1), re.S):
        # Keep the inline emphasis; drop anything else the layout added.
        para = re.sub(r"</?(?!strong\b|em\b|br\b)[a-zA-Z][^>]*>", "", para)
        out.append(re.sub(r"\s+", " ", para).strip())
    return out


def main():
    data = {"pages": pages(), "services": services()}
    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text(json.dumps(data, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(f"{OUT.relative_to(pathlib.Path.cwd())}: {len(data['pages'])} pages, {len(data['services'])} services")


if __name__ == "__main__":
    main()
