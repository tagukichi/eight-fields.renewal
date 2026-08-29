#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
EIGHT FIELDS renewal — static site builder.

Assembles the design proposal in `docs/` from:
  src/pages/*.html   page body fragments (with a leading front-matter comment)
  src/assets/**      css / js / images

The chrome generated here (head, header, footer) maps 1:1 onto the WordPress
theme partials in theme/eight-fields/ (header.php / footer.php).

    python3 tools/build.py [--base /eight-fields.renewal/]
"""

import argparse
import re
import shutil
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from service_detail import DETAIL, FLOW  # noqa: E402

ROOT = Path(__file__).resolve().parent.parent
SRC = ROOT / "src"
OUT = ROOT / "docs"

# ---------------------------------------------------------------- site data

SITE = {
    "name": "エイトフィールズ株式会社",
    "name_en": "EIGHT FIELDS",
    "tagline": "光熱費の削減から家の修繕まで、暮らしのエネルギーをまるごと。",
    "tel": "03-6670-5540",
    "fax": "03-6323-8861",
    "zip": "131-0042",
    "address": "東京都墨田区東墨田2-12-20",
    "hours": "平日 9:00 - 18:00",
    "founded": "2023年10月17日",
    "ceo": "金山 準",
    "group": "有限会社 金山製作所",
    "map_embed": (
        "https://www.google.com/maps?q="
        "%E3%80%92131-0042%20%E6%9D%B1%E4%BA%AC%E9%83%BD%E5%A2%A8%E7%94%B0"
        "%E5%8C%BA%E6%9D%B1%E5%A2%A8%E7%94%B02-12-20&hl=ja&z=17&output=embed"
    ),
    "map_link": (
        "https://www.google.com/maps/search/?api=1&query="
        "%E3%80%92131-0042%20%E6%9D%B1%E4%BA%AC%E9%83%BD%E5%A2%A8%E7%94%B0"
        "%E5%8C%BA%E6%9D%B1%E5%A2%A8%E7%94%B02-12-20"
    ),
}

SERVICES = [
    {
        "slug": "solar",
        "title": "太陽光",
        "en": "SOLAR",
        "img": "solar.jpg",
        "lead": "太陽の光によって発電した電気を自宅で使用し、余った電気は電力会社が買い取ります。",
        "catch": "つくる電気で、毎月の電気代を下げる。",
        "icon": "solar",
    },
    {
        "slug": "storage_battery",
        "title": "蓄電池",
        "en": "BATTERY",
        "img": "battery.jpg",
        "lead": "電気を蓄えておくことができるため、災害や事故などによって停電が起こったとしても、緊急時に様々な電化製品を使用することができます。",
        "catch": "光熱費をしっかり抑えて、家族の未来に安心を。",
        "icon": "battery",
    },
    {
        "slug": "allelectric",
        "title": "オール電化",
        "title_long": "オール電化（エコキュート・IH）",
        "en": "ALL ELECTRIC",
        "img": "allelectric.jpg",
        "fit": "contain",
        "lead": "毎日の給湯や調理を、もっと快適で省エネに。光熱費の見直しから機器交換まで、最適なプランをご提案します。",
        "catch": "給湯も調理も、電気ひとつで軽やかに。",
        "icon": "allelectric",
    },
    {
        "slug": "wall_painting",
        "title": "屋根塗装・外壁塗装",
        "en": "PAINTING",
        "img": "painting.jpg",
        "lead": "屋根・外壁の塗装や修繕前の調査を行います。",
        "catch": "塗り替えは、家の寿命を延ばす投資。",
        "icon": "painting",
    },
    {
        "slug": "ev",
        "title": "EV車・V2H",
        "en": "EV & V2H",
        "img": "ev.jpg",
        "lead": "EV車・V2Hの販売から工事・納車までお任せ下さい。",
        "catch": "クルマが、家の電源になる時代へ。",
        "icon": "ev",
    },
    {
        "slug": "maintenance",
        "title": "メンテナンスサポート",
        "en": "MAINTENANCE",
        "img": "maintenance.jpg",
        "lead": "国からもメンテナンスの推奨がされている太陽光発電・蓄電池・オール電化等設備のメンテナンスをします。",
        "catch": "つけて終わり、にしないために。",
        "icon": "maintenance",
    },
]

# Demo entries for the news list. Replace with real WordPress posts.
NEWS = [
    {"date": "2026-08-01", "cat": "info", "cat_label": "お知らせ",
     "title": "夏季休暇のお知らせ", "slug": "summer-holiday-2026"},
    {"date": "2026-07-18", "cat": "column", "cat_label": "コラム",
     "title": "2026年度の住宅用蓄電池 補助金について（東京都）", "slug": "subsidy-2026"},
    {"date": "2026-07-02", "cat": "works", "cat_label": "施工事例",
     "title": "【墨田区 T様邸】太陽光5.6kW＋蓄電池9.8kWh を施工しました", "slug": "works-sumida-t"},
    {"date": "2026-06-20", "cat": "info", "cat_label": "お知らせ",
     "title": "対応エリアに群馬県を追加しました", "slug": "area-gunma"},
    {"date": "2026-06-05", "cat": "works", "cat_label": "施工事例",
     "title": "【松戸市 K様邸】屋根塗装・外壁塗装を施工しました", "slug": "works-matsudo-k"},
    {"date": "2026-05-22", "cat": "column", "cat_label": "コラム",
     "title": "V2Hはどんな家庭に向いている？導入前に確認したい3つのこと", "slug": "v2h-checklist"},
    {"date": "2026-05-08", "cat": "info", "cat_label": "お知らせ",
     "title": "ゴールデンウィーク休業のお知らせ", "slug": "gw-2026"},
    {"date": "2026-04-15", "cat": "works", "cat_label": "施工事例",
     "title": "【足立区 S様邸】エコキュート＋IHクッキングヒーターへ入れ替え", "slug": "works-adachi-s"},
]

NEWS_CATS = [("all", "すべて"), ("info", "お知らせ"), ("works", "施工事例"), ("column", "コラム")]

# ------------------------------------------------------------------- icons

ICONS = {
    "solar": '<svg viewBox="0 0 32 32" fill="none" aria-hidden="true"><path d="M16 2v4M27 7l-2.8 2.8M30 18h-4M6 18H2M7.8 9.8 5 7" stroke="#FFBC2C" stroke-width="2" stroke-linecap="round"/><circle cx="16" cy="18" r="4.4" fill="#FFBC2C"/><path d="m8 30 3-9h10l3 9H8Z" fill="#43ACDC"/><path d="M9.6 26.5h12.8M14.9 21v9M17.1 21v9" stroke="#fff" stroke-width="1.3"/></svg>',
    "battery": '<svg viewBox="0 0 32 32" fill="none" aria-hidden="true"><rect x="5" y="6" width="22" height="21" rx="3.5" fill="#43ACDC"/><path d="M11 3.5h10" stroke="#0B2E42" stroke-width="2.2" stroke-linecap="round"/><path d="m17.4 10.5-5.2 7.3h3.6l-1.2 5.4 5.4-7.6h-3.7l1.1-5.1Z" fill="#FFBC2C"/></svg>',
    "allelectric": '<svg viewBox="0 0 32 32" fill="none" aria-hidden="true"><path d="M4 14 16 4l12 10" stroke="#43ACDC" stroke-width="2.2" stroke-linejoin="round"/><path d="M7 14v13h18V14" stroke="#43ACDC" stroke-width="2.2" stroke-linejoin="round"/><circle cx="16" cy="20" r="4.6" fill="#FFBC2C"/><path d="M13.6 20h4.8M16 17.6v4.8" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/></svg>',
    "painting": '<svg viewBox="0 0 32 32" fill="none" aria-hidden="true"><path d="M4 12 16 3l12 9" stroke="#FFBC2C" stroke-width="2.4" stroke-linejoin="round"/><rect x="9" y="14" width="14" height="7" rx="2" fill="#43ACDC"/><path d="M16 21v4.5" stroke="#43ACDC" stroke-width="2" stroke-linecap="round"/><rect x="13.4" y="25" width="5.2" height="5.5" rx="1.6" fill="#0B2E42"/></svg>',
    "ev": '<svg viewBox="0 0 32 32" fill="none" aria-hidden="true"><path d="M3 20.5h20v5H3z" fill="#43ACDC"/><path d="m5.5 20.5 2.6-6.2A2 2 0 0 1 10 13h6.4a2 2 0 0 1 1.8 1.2l2.4 6.3" fill="#43ACDC"/><circle cx="8" cy="26" r="2.4" fill="#0B2E42"/><circle cx="18" cy="26" r="2.4" fill="#0B2E42"/><path d="M25 8v6.5a3.5 3.5 0 0 1-3.5 3.5" stroke="#FFBC2C" stroke-width="2" stroke-linecap="round"/><path d="M23 3.5V8M27 3.5V8" stroke="#FFBC2C" stroke-width="2" stroke-linecap="round"/></svg>',
    "maintenance": '<svg viewBox="0 0 32 32" fill="none" aria-hidden="true"><path d="M20.2 4.6a6.6 6.6 0 0 0-8.4 8.4L4.4 20.4a2.6 2.6 0 0 0 0 3.7l3.5 3.5a2.6 2.6 0 0 0 3.7 0l7.4-7.4a6.6 6.6 0 0 0 8.4-8.4l-4 4-3.6-3.6 4-4Z" fill="#43ACDC"/><circle cx="9.6" cy="22.4" r="1.7" fill="#fff"/><path d="M24.5 21.5 28 25M22 24l3.5 3.5" stroke="#FFBC2C" stroke-width="2.4" stroke-linecap="round"/></svg>',
    "chat": '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 11.4c0 4-4 7.2-9 7.2a10.7 10.7 0 0 1-2.6-.3L4.5 20.5l1-3.7A6.9 6.9 0 0 1 3 11.4C3 7.4 7 4.2 12 4.2s9 3.2 9 7.2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><circle cx="8.6" cy="11.4" r="1.15" fill="currentColor"/><circle cx="12" cy="11.4" r="1.15" fill="currentColor"/><circle cx="15.4" cy="11.4" r="1.15" fill="currentColor"/></svg>',
    "check": '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="#3B9C6D"/><path d="m7.5 12.3 3 3 6-6.6" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    "phone": '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.3 3h3l1.5 3.8-2.3 1.4a12 12 0 0 0 5.3 5.3l1.4-2.3L19 12.7v3a2.3 2.3 0 0 1-2.5 2.3A15.5 15.5 0 0 1 4 5.5 2.3 2.3 0 0 1 6.3 3Z" fill="currentColor"/></svg>',
    "mail": '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="2.5" y="4.5" width="19" height="15" rx="2.5" stroke="currentColor" stroke-width="1.8"/><path d="m3.5 6.5 8.5 6 8.5-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
    "pin": '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 22s7-6.3 7-11.4A7 7 0 0 0 5 10.6C5 15.7 12 22 12 22Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><circle cx="12" cy="10.4" r="2.6" fill="currentColor"/></svg>',
    "arrow": '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M7 4.5 12.5 10 7 15.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    "up": '<svg viewBox="0 0 18 18" fill="none" aria-hidden="true"><path d="M9 14.5V3.5M3.8 8.7 9 3.5l5.2 5.2" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    "caret": '<svg class="ef-caret" viewBox="0 0 12 8" fill="none" aria-hidden="true"><path d="M1 1.5 6 6.5l5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
}

# ------------------------------------------------------------- navigation

def nav_items(base):
    """Global navigation. `children` renders as a dropdown / drawer sub-list."""
    return [
        {"label": "ホーム", "en": "HOME", "url": base, "key": "home", "desktop": False},
        {"label": "会社概要", "en": "COMPANY", "url": base + "company/", "key": "company"},
        {"label": "ごあいさつ", "en": "GREETING", "url": base + "greeting/", "key": "greeting"},
        {
            "label": "サービス", "en": "SERVICE", "url": base + "service_/", "key": "service",
            "children": [
                {"label": s.get("title_long", s["title"]), "url": f"{base}service/{s['slug']}/",
                 "key": "service-" + s["slug"], "icon": s["icon"]}
                for s in SERVICES
            ],
        },
        {"label": "お知らせ", "en": "NEWS", "url": base + "news/", "key": "news"},
        {"label": "お問い合わせ", "en": "CONTACT", "url": base + "contact/", "key": "contact"},
    ]


def is_current(item, key):
    if item["key"] == key:
        return True
    return any(c["key"] == key for c in item.get("children", []))


# ---------------------------------------------------------------- partials

def logo(base, size="header"):
    """The supplied lockup asset; the footer sits on navy so it needs the
    white-lettering variant."""
    footer = size != "header"
    cls = "ef-logo ef-logo--footer" if footer else "ef-logo"
    src = "logo-lockup-white.png" if footer else "logo-lockup.png"
    loading = ' loading="lazy"' if footer else ""
    return f"""<a class="{cls}" href="{base}">
          <img class="ef-logo__lockup" src="{base}assets/img/{src}"
               alt="{SITE['name']}" width="895" height="160" decoding="async"{loading}>
        </a>"""


def header(base, key, overlay):
    items = nav_items(base)
    cls = "ef-header ef-header--overlay" if overlay else "ef-header"

    nav = []
    for it in items:
        cur = " is-current" if is_current(it, key) else ""
        if it.get("desktop") is False:
            cur += " ef-nav__item--home"
        if it.get("children"):
            subs = "".join(
                f"""<li><a class="ef-nav__sublink" href="{c['url']}">
                  <span class="ef-ico">{ICONS[c['icon']]}</span>{c['label']}</a></li>"""
                for c in it["children"]
            )
            nav.append(f"""<li class="ef-nav__item{cur}">
              <a class="ef-nav__link" href="{it['url']}">{it['label']}{ICONS['caret']}</a>
              <ul class="ef-nav__sub">{subs}</ul>
            </li>""")
        else:
            nav.append(
                f"""<li class="ef-nav__item{cur}"><a class="ef-nav__link" href="{it['url']}">{it['label']}</a></li>"""
            )

    drawer = []
    for it in items:
        if it.get("children"):
            panel_id = "ef-dsub-" + it["key"]
            subs = "".join(
                f"""<li><a class="ef-drawer__sublink" href="{c['url']}">
                  <span class="ef-ico">{ICONS[c['icon']]}</span>{c['label']}</a></li>"""
                for c in it["children"]
            )
            drawer.append(f"""<li>
              <div class="ef-drawer__row">
                <a class="ef-drawer__link" href="{it['url']}">
                  <span>{it['label']}<small>{it['en']}</small></span>
                </a>
                <button class="ef-drawer__toggle" type="button" data-drawer-toggle
                        aria-expanded="false" aria-controls="{panel_id}">
                  <span class="ef-drawer__caret"></span>
                  <span class="ef-sr">{it['label']}のサブメニューを開閉</span>
                </button>
              </div>
              <div class="ef-drawer__sub" id="{panel_id}" hidden>
                <div><ul class="ef-drawer__sublist">{subs}</ul></div>
              </div>
            </li>""")
        else:
            drawer.append(f"""<li>
              <a class="ef-drawer__link" href="{it['url']}">
                <span>{it['label']}<small>{it['en']}</small></span>{ICONS['arrow']}
              </a>
            </li>""")

    tel_digits = SITE["tel"].replace("-", "")
    return f"""<header class="{cls}" data-header>
      <div class="ef-header__inner">
        {logo(base)}
        <nav class="ef-nav" aria-label="グローバルナビゲーション">
          <ul class="ef-nav__list">{''.join(nav)}</ul>
        </nav>
        <div class="ef-header__actions">
          <a class="ef-header__tel" href="tel:{tel_digits}">
            <b>{SITE['tel']}</b><small>{SITE['hours']}</small>
          </a>
          <a class="ef-btn ef-btn--primary ef-btn--sm" href="{base}contact/">無料相談・お見積り</a>
          <button class="ef-burger" type="button" data-burger
                  aria-label="メニューを開く" aria-expanded="false" aria-controls="ef-drawer">
            <span></span><span></span><span></span>
          </button>
        </div>
      </div>
    </header>

    <div class="ef-drawer" id="ef-drawer" data-drawer aria-hidden="true">
      <ul class="ef-drawer__list">{''.join(drawer)}</ul>
      <div class="ef-drawer__foot">
        <a class="ef-btn ef-btn--primary ef-btn--block" href="{base}contact/">無料相談・お見積り</a>
        <a class="ef-btn ef-btn--outline ef-btn--block" href="tel:{tel_digits}">
          {ICONS['phone']}<span>{SITE['tel']}</span>
        </a>
        <p class="ef-help ef-center">{SITE['hours']}／土日祝も対応可（要予約）</p>
      </div>
    </div>"""


def cta_band(base):
    tel_digits = SITE["tel"].replace("-", "")
    return f"""<section class="ef-section ef-cta">
      <div class="ef-container">
        <div class="ef-cta__inner">
          <div data-reveal>
            <span class="ef-eyebrow">Contact</span>
            <h2 class="ef-cta__title">「うちの場合はどうなる？」<br>その一言から、お聞かせください。</h2>
            <p class="ef-cta__text">
              お見積り・シミュレーションは無料です。営業から施工まで自社で一貫して行うため、
              お住まいの条件に合わせた現実的なプランと費用を、その場でご提示できます。
              太陽光・蓄電池以外の、ご自宅で気になる箇所のご相談も歓迎です。
            </p>
          </div>
          <div class="ef-cta__panel" data-reveal data-reveal-delay="1">
            <a class="ef-cta__tel" href="tel:{tel_digits}">
              <small>お電話でのご相談</small>
              <b>{SITE['tel']}</b>
              <span class="ef-cta__hours">{SITE['hours']}</span>
            </a>
            <a class="ef-btn ef-btn--primary ef-btn--block" href="{base}contact/">
              {ICONS['mail']}<span>フォームで問い合わせる</span>
            </a>
            <a class="ef-btn ef-btn--ghost ef-btn--block" href="{base}service_/">サービス一覧を見る</a>
          </div>
        </div>
      </div>
    </section>"""


def footer(base):
    tel_digits = SITE["tel"].replace("-", "")
    svc_links = "".join(
        f"""<li><a href="{base}service/{s['slug']}/">{s.get('title_long', s['title'])}</a></li>"""
        for s in SERVICES
    )
    return f"""<footer class="ef-footer" data-footer>
      <div class="ef-footer__main">
        <div class="ef-container">
          <div class="ef-footer__grid">
            <div>
              {logo(base, 'footer')}
              <address class="ef-footer__addr">
                <b>{SITE['name']}</b>
                〒{SITE['zip']}<br>{SITE['address']}<br>
                FAX：{SITE['fax']}
              </address>
              <a class="ef-footer__tel" href="tel:{tel_digits}">
                <b>{SITE['tel']}</b><small>{SITE['hours']}</small>
              </a>
            </div>
            <nav class="ef-footer__nav" aria-label="フッターナビゲーション">
              <div class="ef-footer__col">
                <h3>Service</h3>
                <ul class="ef-footer__list">{svc_links}</ul>
              </div>
              <div class="ef-footer__col">
                <h3>Company</h3>
                <ul class="ef-footer__list">
                  <li><a href="{base}company/">会社概要</a></li>
                  <li><a href="{base}greeting/">ごあいさつ</a></li>
                  <li><a href="{base}service_/">サービス一覧</a></li>
                  <li><a href="{base}news/">お知らせ</a></li>
                </ul>
              </div>
              <div class="ef-footer__col">
                <h3>Contact</h3>
                <ul class="ef-footer__list">
                  <li><a href="{base}contact/">お問い合わせ</a></li>
                  <li><a href="tel:{tel_digits}">お電話でのご相談</a></li>
                </ul>
              </div>
            </nav>
          </div>
        </div>
      </div>
      <div class="ef-footer__bottom">
        <div class="ef-container" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px;">
          <div class="ef-footer__legal">
            <a href="{base}contact/">お問い合わせ</a>
            <a href="{base}company/">会社概要</a>
          </div>
          <small class="ef-footer__copy">&copy; {SITE['name']} All rights reserved.</small>
        </div>
      </div>
    </footer>

    <a class="ef-totop" href="#top" data-totop aria-label="ページの先頭へ戻る">{ICONS['up']}</a>"""


def document(base, meta, body, preview_note=True):
    title = meta["title"]
    full_title = title if meta.get("home") else f"{title}｜{SITE['name']}"
    desc = meta.get("desc", SITE["tagline"])
    # The front page hero is light now, so the header keeps its normal
    # light treatment rather than the white-on-dark overlay variant.
    overlay = False
    is_home = meta.get("home") == "1"
    key = meta.get("key", "")

    body_class = ""

    return f"""<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{full_title}</title>
<meta name="description" content="{desc}">
<meta name="format-detection" content="telephone=no">
<meta property="og:type" content="{'website' if is_home else 'article'}">
<meta property="og:title" content="{full_title}">
<meta property="og:description" content="{desc}">
<meta property="og:site_name" content="{SITE['name']}">
<link rel="icon" href="{base}assets/img/favicon.ico" sizes="32x32">
<link rel="icon" href="{base}assets/img/favicon.png" type="image/png" sizes="512x512">
<link rel="apple-touch-icon" href="{base}assets/img/apple-touch-icon.png">
<meta name="theme-color" content="#0B2E42">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{base}assets/css/style.css">
<meta name="robots" content="noindex, nofollow">
<style>
  html.ef-locked {{ overflow: hidden; }}
  html.ef-locked body > *:not(.ef-gate) {{ display: none !important; }}
  .ef-gate {{ display: none; }}
  html.ef-locked .ef-gate {{
    display: grid; place-items: center;
    position: fixed; inset: 0; z-index: 9999; padding: 24px;
    background: linear-gradient(168deg, #FFFFFF 0%, #F5FAFD 40%, #E7F3FA 100%);
  }}
  .ef-gate__card {{
    width: min(400px, 100%); padding: 38px 32px 34px;
    background: #fff; border: 1px solid #EAF0F3; border-radius: 22px;
    box-shadow: 0 24px 60px rgba(11,46,66,.14); text-align: center;
  }}
  .ef-gate__card img {{ width: 210px; height: auto; margin: 0 auto 24px; }}
  .ef-gate__card h1 {{ margin: 0; font-size: 19px; color: #12262F; }}
  .ef-gate__lead {{ margin: 10px 0 26px; font-size: 13.5px; color: #7C8F98; line-height: 1.8; }}
  .ef-gate__field {{ display: block; text-align: left; margin-bottom: 14px; }}
  .ef-gate__field span {{ display: block; margin-bottom: 6px; font-size: 12px; font-weight: 700; color: #46595E; letter-spacing: .06em; }}
  .ef-gate__field input {{
    width: 100%; padding: 13px 15px; font-size: 16px;
    border: 1.5px solid #DCE4E9; border-radius: 10px; background: #fff; color: #12262F;
  }}
  .ef-gate__field input:focus {{ outline: none; border-color: #1C7FA8; box-shadow: 0 0 0 4px rgba(28,127,168,.1); }}
  .ef-gate__btn {{
    width: 100%; margin-top: 8px; padding: 15px; border-radius: 999px; border: 0; cursor: pointer;
    background: #FFBC2C; color: #0B2E42; font-size: 15px; font-weight: 700;
    box-shadow: 0 8px 22px rgba(255,188,44,.32);
  }}
  .ef-gate__err {{ margin: 16px 0 0; font-size: 13px; color: #D64545; }}
</style>
<script>
  /* Preview gate: keeps the draft out of casual view and out of search results.
     This is not server-side authentication, which GitHub Pages cannot provide. */
  (function () {{
    try {{ if (sessionStorage.getItem('ef-preview') === '1') return; }} catch (e) {{}}
    document.documentElement.className += ' ef-locked';
  }})();
</script>
<noscript><style>[data-reveal]{{opacity:1;transform:none}}</style></noscript>
</head>
<body id="top" class="{body_class}">
<div class="ef-gate" role="dialog" aria-modal="true" aria-labelledby="ef-gate-title">
  <form class="ef-gate__card" id="ef-gate-form" autocomplete="on">
    <img src="{base}assets/img/logo-lockup.png" alt="エイトフィールズ株式会社" width="895" height="160">
    <h1 id="ef-gate-title">デザイン案プレビュー</h1>
    <p class="ef-gate__lead">閲覧には ID とパスワードが必要です。</p>
    <label class="ef-gate__field"><span>ID</span>
      <input type="text" id="ef-gate-id" name="username" autocomplete="username" autocapitalize="off" required></label>
    <label class="ef-gate__field"><span>PASSWORD</span>
      <input type="password" id="ef-gate-pw" name="password" autocomplete="current-password" required></label>
    <button class="ef-gate__btn" type="submit">閲覧する</button>
    <p class="ef-gate__err" id="ef-gate-err" hidden>ID またはパスワードが違います。</p>
  </form>
</div>

<a class="ef-skip" href="#main">本文へスキップ</a>
{header(base, key, overlay)}
<main id="main">
{body}
{cta_band(base)}
</main>
{footer(base)}
<script src="{base}assets/js/vendor/gsap.min.js" defer></script>
<script src="{base}assets/js/vendor/ScrollTrigger.min.js" defer></script>
<script src="{base}assets/js/main.js" defer></script>
<script>
  (function () {{
    var form = document.getElementById('ef-gate-form');
    if (!form) return;
    var HASH = 'c195d2d8756234367242ba7616c5c60369bc25ced2dcb5b92808d31b58ef217a';

    function sha256(text) {{
      if (!(window.crypto && window.crypto.subtle)) return Promise.resolve(null);
      return crypto.subtle.digest('SHA-256', new TextEncoder().encode(text)).then(function (buf) {{
        return Array.prototype.map.call(new Uint8Array(buf), function (b) {{
          return ('0' + b.toString(16)).slice(-2);
        }}).join('');
      }});
    }}

    form.addEventListener('submit', function (e) {{
      e.preventDefault();
      var id = document.getElementById('ef-gate-id').value.trim();
      var pw = document.getElementById('ef-gate-pw').value;

      Promise.all([sha256(id), sha256(pw)]).then(function (h) {{
        var ok = (h[0] === null)
          ? (id === 'eight' && pw === 'eight')   /* no SubtleCrypto outside a secure context */
          : (h[0] === HASH && h[1] === HASH);
        if (ok) {{
          try {{ sessionStorage.setItem('ef-preview', '1'); }} catch (err) {{}}
          document.documentElement.classList.remove('ef-locked');
        }} else {{
          document.getElementById('ef-gate-err').hidden = false;
        }}
      }});
    }});
  }})();
</script>
</body>
</html>
"""


# --------------------------------------------------------- page assembly

FM_RE = re.compile(r"^\s*<!--\s*(.*?)\s*-->", re.S)


def parse_page(path):
    raw = path.read_text(encoding="utf-8")
    m = FM_RE.match(raw)
    meta, body = {}, raw
    if m:
        for line in m.group(1).splitlines():
            line = line.strip()
            if not line or ":" not in line:
                continue
            k, v = line.split(":", 1)
            meta[k.strip()] = v.strip()
        body = raw[m.end():]
    return meta, body


def crumbs(base, trail):
    """trail: list of (label, url|None) — the last item is the current page."""
    lis = [f'<li><a href="{base}">ホーム</a></li>']
    for label, url in trail:
        if url:
            lis.append(f'<li><a href="{url}">{label}</a></li>')
        else:
            lis.append(f'<li><span aria-current="page">{label}</span></li>')
    return f"""<nav class="ef-crumbs" aria-label="パンくずリスト">
      <div class="ef-container"><ol class="ef-crumbs__list">{''.join(lis)}</ol></div>
    </nav>"""


def page_hero(base, meta):
    img = meta.get("hero")
    media = ""
    if img:
        media = (f'<div class="ef-phero__media">'
                 f'<img src="{base}assets/img/{img}" alt="" loading="eager" decoding="async">'
                 f'</div>')
    lead = f'<p class="ef-phero__text">{meta["lead"]}</p>' if meta.get("lead") else ""
    return f"""<section class="ef-phero">
      {media}
      <div class="ef-container">
        <p class="ef-phero__en">{meta.get('en', '')}</p>
        <h1 class="ef-phero__title">{meta.get('h1', meta['title'])}</h1>
        {lead}
      </div>
    </section>"""


def expand(text, base):
    """Resolve the handful of placeholders available inside page fragments."""
    repl = {
        "{{BASE}}": base,
        "{{TEL}}": SITE["tel"],
        "{{TEL_DIGITS}}": SITE["tel"].replace("-", ""),
        "{{FAX}}": SITE["fax"],
        "{{ZIP}}": SITE["zip"],
        "{{ADDRESS}}": SITE["address"],
        "{{HOURS}}": SITE["hours"],
        "{{NAME}}": SITE["name"],
        "{{CEO}}": SITE["ceo"],
        "{{FOUNDED}}": SITE["founded"],
        "{{GROUP}}": SITE["group"],
        # These land inside HTML attributes, so the query separators need escaping.
        "{{MAP_EMBED}}": SITE["map_embed"].replace("&", "&amp;"),
        "{{MAP_LINK}}": SITE["map_link"].replace("&", "&amp;"),
        "{{SERVICE_CARDS}}": "",
        "{{SERVICE_OTHERS}}": "",
        "{{NEWS_LATEST}}": "",
        "{{NEWS_FULL}}": "",
        "{{NEWS_TABS}}": "",
    }
    for k, v in repl.items():
        if v:
            text = text.replace(k, v)
    for name, svg in ICONS.items():
        text = text.replace("{{ICON:%s}}" % name, svg)
    return text


def service_cards(base, exclude=None, limit=None):
    out = []
    items = [s for s in SERVICES if s["slug"] != exclude]
    if limit:
        items = items[:limit]
    for i, s in enumerate(items):
        out.append(f"""<article class="ef-card ef-scard" data-reveal data-reveal-delay="{i % 3 + 1}">
          <a href="{base}service/{s['slug']}/" style="display:contents;color:inherit;">
            <div class="ef-card__media{' ef-card__media--contain' if s.get('fit') == 'contain' else ''}">
              <img src="{base}assets/img/{s['img']}" alt="{s['title']}" loading="lazy" decoding="async">
              <span class="ef-card__badge">{s['en']}</span>
            </div>
            <div class="ef-card__body">
              <span class="ef-scard__no">0{SERVICES.index(s) + 1}</span>
              <h3 class="ef-card__title">{s.get('title_long', s['title'])}</h3>
              <p class="ef-card__text">{s['lead']}</p>
              <p class="ef-card__foot"><span class="ef-link">詳しく見る</span></p>
            </div>
          </a>
        </article>""")
    return "".join(out)


def news_items(base, limit=None):
    out = []
    for n in (NEWS[:limit] if limit else NEWS):
        d = n["date"].split("-")
        out.append(f"""<li class="ef-news__item" data-cat="{n['cat']}">
          <a class="ef-news__link" href="{base}news/{n['slug']}/">
            <time class="ef-news__date" datetime="{n['date']}">{d[0]}.{d[1]}.{d[2]}</time>
            <span class="ef-news__cat ef-news__cat--{n['cat']}">{n['cat_label']}</span>
            <span class="ef-news__title">{n['title']}</span>
            <span class="ef-news__arrow">{ICONS['arrow']}</span>
          </a>
        </li>""")
    return "".join(out)


def news_tabs():
    out = []
    for i, (key, label) in enumerate(NEWS_CATS):
        active = " is-active" if i == 0 else ""
        out.append(f'<button class="ef-tab{active}" type="button" role="tab" '
                   f'aria-selected="{"true" if i == 0 else "false"}" data-filter="{key}">{label}</button>')
    return "".join(out)



# ------------------------------------------------- service detail pages

def service_detail_body(base, svc):
    d = DETAIL[svc["slug"]]
    idx = SERVICES.index(svc)

    intro = "".join(f"<p>{t}</p>" for t in d["intro"])
    catch_sub = f'<p class="ef-lead">{d["catch_sub"]}</p>' if d.get("catch_sub") else ""

    merits = []
    for i, (title, text, img) in enumerate(d["merits"]):
        media = ""
        if img:
            # A diagram is artwork on white and must not be cropped.
            contain = " ef-merit__media--contain" if img.startswith("diagram") else ""
            media = (f'<div class="ef-split__media ef-merit__media{contain}">'
                     f'<img src="{base}assets/img/{img}" alt="" loading="lazy" decoding="async">'
                     f'</div>')
        reverse = " ef-split--reverse" if i % 2 else ""
        body = f'''<div>
            <span class="ef-feature__no">MERIT {i + 1:02d}</span>
            <h3 class="ef-h3 ef-merit__title">{title}</h3>
            <p class="ef-lead">{text}</p>
          </div>'''
        if media:
            gap = "" if i == 0 else " ef-merit--gap"
            merits.append(f'<div class="ef-merit ef-split{reverse}{gap}" data-reveal>{media}{body}</div>')
        else:
            merits.append(f'<div class="ef-merit ef-merit--rule" data-reveal>{body}</div>')

    steps = "".join(
        f'''<li class="ef-flow__item">
          <span class="ef-flow__no">STEP<b>{i + 1:02d}</b></span>
          <div><h3 class="ef-flow__title">{t}</h3><p class="ef-flow__text">{x}</p></div>
        </li>'''
        for i, (t, x) in enumerate(FLOW))

    faq = "".join(
        f'''<div class="ef-faq__item">
          <h3><button class="ef-faq__q" type="button" data-faq-q
                  aria-expanded="false" aria-controls="faq-{svc['slug']}-{i}">
            <span>{q}</span><span class="ef-faq__mark"></span>
          </button></h3>
          <div class="ef-faq__a" id="faq-{svc['slug']}-{i}" hidden>
            <div class="ef-faq__inner"><div>{a}</div></div>
          </div>
        </div>'''
        for i, (q, a) in enumerate(d["faq"]))

    outro = ""
    if d.get("outro"):
        paras = "".join(f"<p>{t}</p>" for t in d["outro"])
        outro = f'''<section class="ef-section ef-section--sand">
      <div class="ef-container ef-container--narrow">
        <div class="ef-head" data-reveal>
          <span class="ef-eyebrow">Outlook</span>
          <h2 class="ef-h2">{d["outro_title"]}</h2>
        </div>
        <div class="ef-article" data-reveal data-reveal-delay="1">{paras}</div>
      </div>
    </section>'''

    return f'''<section class="ef-section">
      <div class="ef-container">
        <div class="ef-split">
          <div class="ef-split__media" data-reveal>
            <img src="{base}assets/img/{svc['img']}" alt="{svc['title']}" loading="lazy" decoding="async">
            <span class="ef-split__badge"><b>{idx + 1:02d}</b><span>{svc['en']}</span></span>
          </div>
          <div data-reveal data-reveal-delay="1">
            <span class="ef-eyebrow">{svc['en']}</span>
            <h2 class="ef-h2">{svc['catch']}</h2>
            {catch_sub}
            <div class="ef-article ef-mt-24">{intro}</div>
            <div class="ef-actions ef-mt-32">
              <a class="ef-btn ef-btn--primary" href="{base}contact/">この設備について相談する</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="ef-section ef-section--sand">
      <div class="ef-container">
        <div class="ef-head" data-reveal>
          <span class="ef-eyebrow">Merit</span>
          <h2 class="ef-h2">{svc.get('title_long', svc['title'])}の3つのメリット</h2>
        </div>
        {''.join(merits)}
      </div>
    </section>

    {outro}

    <section class="ef-section">
      <div class="ef-container">
        <div class="ef-split ef-split--top">
          <div data-reveal>
            <span class="ef-eyebrow">Flow</span>
            <h2 class="ef-h2">導入までの流れ</h2>
            <p class="ef-lead">
              ご相談・現地調査・お見積りまでは無料です。
              営業から施工まで自社で行うため、打ち合わせの内容がそのまま現場に伝わります。
            </p>
          </div>
          <div data-reveal data-reveal-delay="1"><ol class="ef-flow">{steps}</ol></div>
        </div>
      </div>
    </section>

    <section class="ef-section ef-section--sand">
      <div class="ef-container ef-container--narrow">
        <div class="ef-head ef-head--center" data-reveal>
          <span class="ef-eyebrow">FAQ</span>
          <h2 class="ef-h2">よくあるご質問</h2>
        </div>
        <div class="ef-faq" data-reveal data-reveal-delay="1">{faq}</div>
      </div>
    </section>

    <section class="ef-section">
      <div class="ef-container">
        <div class="ef-head" data-reveal>
          <span class="ef-eyebrow">Other services</span>
          <h2 class="ef-h2">ほかのサービス</h2>
        </div>
        <div class="ef-grid ef-grid--3">{service_cards(base, exclude=svc['slug'])}</div>
      </div>
    </section>'''


def build_service_pages(base):
    out = []
    for svc in SERVICES:
        meta = {
            "title": svc.get("title_long", svc["title"]),
            "key": "service-" + svc["slug"],
            "en": svc["en"],
            "h1": svc.get("title_long", svc["title"]),
            "hero": svc["img"],
            "lead": svc["lead"],
            "desc": f"{svc.get('title_long', svc['title'])}｜{svc['lead']}",
        }
        trail = [("サービス", base + "service_/"), (svc["title"], None)]
        body = "\n".join([
            page_hero(base, meta),
            crumbs(base, trail),
            service_detail_body(base, svc),
        ])
        dest = OUT / "service" / svc["slug"] / "index.html"
        dest.parent.mkdir(parents=True, exist_ok=True)
        html_doc = document(base, meta, body)
        dest.write_text(html_doc, encoding="utf-8")
        out.append((f"/service/{svc['slug']}/", len(html_doc)))
    return out


# ---------------------------------------------------- news detail pages

NEWS_BODY = {
    "summer-holiday-2026": [
        ("p", "平素は格別のご高配を賜り、厚く御礼申し上げます。"),
        ("p", "誠に勝手ながら、下記の期間を夏季休暇とさせていただきます。"),
        ("h2", "休業期間"),
        ("p", "2026年8月11日（火）〜 2026年8月16日（日）"),
        ("p", "休業期間中にいただいたお問い合わせにつきましては、"
              "8月17日（月）以降に順次ご対応いたします。"
              "ご不便をおかけいたしますが、何卒ご了承くださいますようお願い申し上げます。"),
        ("h2", "緊急のご連絡について"),
        ("p", "施工済みのお客様で、設備の不具合など緊急のご用件がある場合は、"
              "お手数ですがお電話にてご連絡ください。順次折り返しご対応いたします。"),
    ],
}

NEWS_DEFAULT = [
    ("p", "この記事はデザイン確認用のサンプルです。"
          "本番環境では WordPress の投稿本文がここに表示されます。"),
    ("h2", "見出し（H2）の表示例"),
    ("p", "本文の表示例です。行間・文字サイズ・リンクの下線などは、"
          "長い文章でも読み疲れしないよう調整しています。"
          "<a href=\"#\">リンクはこのように表示されます。</a>"),
    ("h3", "見出し（H3）の表示例"),
    ("ul", ["箇条書きの表示例です", "2行目以降も読みやすい行間を確保しています",
            "アイコンはブランドカラーのアクセントを使用"]),
    ("blockquote", "引用や補足はこのように表示されます。"
                   "補助金の要件など、注意して読んでほしい情報に使えます。"),
    ("ol", ["番号付きリストの表示例", "手順の説明などに使用します", "番号は自動で振られます"]),
]


def news_article(entry):
    blocks = NEWS_BODY.get(entry["slug"], NEWS_DEFAULT)
    out = []
    for kind, value in blocks:
        if kind == "ul":
            out.append("<ul>" + "".join(f"<li>{v}</li>" for v in value) + "</ul>")
        elif kind == "ol":
            out.append("<ol>" + "".join(f"<li>{v}</li>" for v in value) + "</ol>")
        else:
            out.append(f"<{kind}>{value}</{kind}>")
    return "".join(out)


def build_news_pages(base):
    out = []
    for i, n in enumerate(NEWS):
        d = n["date"].split("-")
        meta = {
            "title": n["title"],
            "key": "news",
            "en": "NEWS",
            "h1": n["title"],
            "desc": n["title"] + "｜" + SITE["name"],
        }
        prev_n = NEWS[i - 1] if i > 0 else None
        next_n = NEWS[i + 1] if i < len(NEWS) - 1 else None
        nav = []
        nav.append(
            f'''<a class="ef-btn ef-btn--outline ef-btn--sm" href="{base}news/{prev_n["slug"]}/">前の記事</a>'''
            if prev_n else '''<span class="ef-btn ef-btn--outline ef-btn--sm" style="opacity:.4;pointer-events:none;">前の記事</span>''')
        nav.append(f'''<a class="ef-btn ef-btn--dark ef-btn--sm" href="{base}news/">お知らせ一覧</a>''')
        nav.append(
            f'''<a class="ef-btn ef-btn--outline ef-btn--sm" href="{base}news/{next_n["slug"]}/">次の記事</a>'''
            if next_n else '''<span class="ef-btn ef-btn--outline ef-btn--sm" style="opacity:.4;pointer-events:none;">次の記事</span>''')

        hero = f'''<section class="ef-phero">
          <div class="ef-container">
            <p class="ef-phero__en">NEWS</p>
            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:14px;margin-top:14px;">
              <time class="ef-news__date" datetime="{n['date']}"
                    style="color:rgba(255,255,255,.72);">{d[0]}.{d[1]}.{d[2]}</time>
              <span class="ef-news__cat ef-news__cat--{n['cat']}">{n['cat_label']}</span>
            </div>
            <h1 class="ef-phero__title" style="margin-top:14px;">{n['title']}</h1>
          </div>
        </section>'''

        body = "\n".join([
            hero,
            crumbs(base, [("お知らせ", base + "news/"), (n["title"], None)]),
            f'''<section class="ef-section">
              <div class="ef-container ef-container--narrow">
                <div class="ef-article" data-reveal>{news_article(n)}</div>
                <div class="ef-actions ef-actions--center ef-mt-64"
                     style="padding-top:36px;border-top:1px solid var(--ef-line-soft);">{''.join(nav)}</div>
              </div>
            </section>''',
        ])

        dest = OUT / "news" / n["slug"] / "index.html"
        dest.parent.mkdir(parents=True, exist_ok=True)
        html_doc = document(base, meta, body)
        dest.write_text(html_doc, encoding="utf-8")
        out.append((f"/news/{n['slug']}/", len(html_doc)))
    return out

# ------------------------------------------------------------------- build

def build(base):
    if OUT.exists():
        shutil.rmtree(OUT)
    OUT.mkdir(parents=True)

    shutil.copytree(SRC / "assets", OUT / "assets")
    (OUT / ".nojekyll").write_text("", encoding="utf-8")
    # the design proposal is not meant to be indexed
    (OUT / "robots.txt").write_text("User-agent: *\nDisallow: /\n", encoding="utf-8")

    pages = sorted((SRC / "pages").glob("*.html"))
    built = []

    for path in pages:
        meta, body = parse_page(path)
        out_path = meta.get("path", "").strip("/")

        body = expand(body, base)
        body = body.replace("{{SERVICE_CARDS}}", service_cards(base))
        body = body.replace("{{SERVICE_OTHERS}}", service_cards(base, exclude=meta.get("service", "")))
        body = body.replace("{{NEWS_LATEST}}", news_items(base, limit=4))
        body = body.replace("{{NEWS_FULL}}", news_items(base))
        body = body.replace("{{NEWS_TABS}}", news_tabs())

        chunks = []
        if meta.get("home") != "1":
            trail = []
            raw_trail = meta.get("crumbs", meta["title"])
            for part in raw_trail.split("|"):
                part = part.strip()
                if ">" in part:
                    label, url = part.split(">", 1)
                    trail.append((label.strip(), base + url.strip().lstrip("/")))
                else:
                    trail.append((part, None))
            chunks.append(page_hero(base, meta))
            chunks.append(crumbs(base, trail))
        chunks.append(body)

        html_doc = document(base, meta, "\n".join(chunks))

        dest = OUT / (out_path + "/index.html" if out_path else "index.html")
        dest.parent.mkdir(parents=True, exist_ok=True)
        dest.write_text(html_doc, encoding="utf-8")
        built.append(("/" + out_path + "/" if out_path else "/", len(html_doc)))

    built += build_service_pages(base)
    built += build_news_pages(base)

    for url_path, size in sorted(built):
        print(f"  {url_path:34s} {size / 1024:6.1f} KB")
    print(f"\n{len(built)} pages -> {OUT.relative_to(ROOT)}/ (base={base})")


if __name__ == "__main__":
    ap = argparse.ArgumentParser()
    ap.add_argument("--base", default="/eight-fields.renewal/",
                    help="URL prefix the site is served from (GitHub Pages project path)")
    args = ap.parse_args()
    build(args.base if args.base.endswith("/") else args.base + "/")
