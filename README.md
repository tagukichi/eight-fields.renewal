# エイトフィールズ株式会社 サイトリニューアル

現行サイト（WordPress）のリニューアルにあたり、**ページ構成はそのままに**デザインを刷新した提案一式です。
静的な HTML / CSS / JS でデザインを作成し、そのまま WordPress テーマ（PHP）に変換しています。

| | |
|---|---|
| **デザイン案プレビュー** | https://tagukichi.github.io/eight-fields.renewal/ ※下記の初回設定が必要 |
| **WordPress テーマ** | [`theme/eight-fields/`](theme/eight-fields/) |
| 作業ブランチ | `claude/eight-fields-redesign-9c64yk` |

---

## 1. デザインの方向性

現行ロゴ（∞マーク）から抽出した **ブランドブルー `#43ACDC`** と **ブランドイエロー `#FFBC2C`** を軸に、
信頼感を出すためのディープネイビー `#0B2E42` を加えた3色構成です。

| 役割 | 色 | 用途 |
|---|---|---|
| Logo Blue | `#43ACDC` | 図版・アイコン・ハイライト |
| Blue (AA) | `#1C7FA8` | リンク・本文中のアクセント（コントラスト確保用） |
| Logo Amber | `#FFBC2C` | 主要CTA・アクセントライン |
| Deep Navy | `#0B2E42` | ヒーロー／フッターの地色 |

設計の考え方:

- **「営業会社＝施工会社」という強みを前面に。** 現行サイトでは会社概要の一項目だった内容を、
  トップページの「選ばれる3つの理由」として引き上げています。
- **6サービスを横断して見せる。** 単体販売ではなく「組み合わせるほど効果が出る」構成にし、
  サービス一覧に「つくる → ためる → 使う → 保つ」の導線を追加しました。
- **問い合わせまでの距離を短く。** 追従CTAバー（SP）、全ページ共通のコンタクトバンド、
  ヘッダーの電話番号を常設しています。
- **アクセシビリティ。** キーボード操作・スキップリンク・`prefers-reduced-motion` に対応。
  文字色は本文で 4.5:1 以上のコントラストを確保しています。

## 2. ページ構成（現行サイトと1:1）

| ページ | URL | テンプレート |
|---|---|---|
| トップページ | `/` | `front-page.php` |
| 会社概要 | `/company/` | `page.php` |
| 代表挨拶（ごあいさつ） | `/greeting/` | `page.php` |
| サービス（一覧） | `/service_/` | `archive-service.php` |
| 太陽光 | `/service/solar/` | `single-service.php` |
| 蓄電池 | `/service/storage_battery/` | `single-service.php` |
| オール電化（エコキュート・IH） | `/service/allelectric/` | `single-service.php` |
| 屋根塗装・外壁塗装 | `/service/wall_painting/` | `single-service.php` |
| EV車・V2H | `/service/ev/` | `single-service.php` |
| メンテナンスサポート | `/service/maintenance/` | `single-service.php` |
| お知らせ（一覧） | `/news/` | `index.php` |
| お知らせ（詳細） | `/news/<slug>/` | `single.php` |
| お問い合わせ | `/contact/` | `page.php` |

## 2-b. GitHub Pages の初回設定（1回だけ必要）

デプロイ用の GitHub Actions ワークフローは設定済みですが、**Pages の初回有効化だけは
リポジトリ設定から行う必要があります**（GitHub の仕様上、ワークフローの `GITHUB_TOKEN` では
Pages サイトを新規作成できないため）。

1. リポジトリの **Settings → Pages** を開く
2. **Build and deployment → Source** を **GitHub Actions** に変更
3. **Actions → Deploy design proposal to GitHub Pages → Run workflow** で再実行

以降は `claude/eight-fields-redesign-9c64yk` への push で自動デプロイされます。

> リポジトリが Private の場合、GitHub Pages の公開には有料プラン（Pro / Team 以上）が必要です。

## 3. リポジトリ構成

```
├── src/                     デザイン案のソース
│   ├── assets/css/style.css デザインシステム本体（1ファイル）
│   ├── assets/js/main.js    UI挙動・アニメーション
│   ├── assets/js/vendor/    GSAP 3.15 + ScrollTrigger（同梱）
│   ├── assets/img/          画像素材
│   └── pages/*.html         各ページの本文フラグメント
├── tools/build.py           src/ → docs/ を生成するビルドスクリプト
├── tools/service_detail.py  サービス詳細6ページの原稿
├── docs/                    ★ GitHub Pages に配信される生成物
├── theme/eight-fields/      ★ WordPress テーマ
└── .github/workflows/       GitHub Pages 自動デプロイ
```

### アニメーションについて

スクロール演出とトップページのファーストビューは [GSAP 3.15](https://gsap.com/) + ScrollTrigger で
実装しています。CDN ではなくテーマに同梱しているため、外部サービスへの依存はありません
（`assets/js/vendor/`）。GSAP は Standard "no charge" ライセンスで、コーポレートサイトでの利用は無償です。

GSAP が読み込めない場合は IntersectionObserver によるシンプルなフェードインに、
JavaScript が無効な場合は `<noscript>` で全要素を表示状態にフォールバックします。
`prefers-reduced-motion: reduce` の環境ではアニメーションを行いません。

### デザイン案のビルド

```bash
python3 tools/build.py --base /        # ローカル確認用
cd docs && python3 -m http.server 8000
```

GitHub Pages 用は `--base /eight-fields.renewal/`（ワークフローが自動で付与）。

## 4. WordPress テーマの導入

```bash
# theme/eight-fields ごと wp-content/themes/ に配置して有効化
cp -r theme/eight-fields /path/to/wp-content/themes/
```

### 有効化後に必要な設定

1. **パーマリンク設定** — 「投稿名」を選択して保存（リライトルールの再生成）。
   お知らせを `/news/<slug>/` にする場合はカスタム構造で `/news/%postname%/` を指定してください。
2. **設定 → 表示設定** — フロントページに固定ページ「ホーム」、投稿ページに「お知らせ」を割り当て。
3. **外観 → メニュー** — 「グローバルナビゲーション」に会社概要／ごあいさつ／サービス（子に6サービス）／お知らせ／お問い合わせを設定。
   メニュー項目の「説明」欄に英字ラベル（`COMPANY` など）を入れると、SPドロワーに小さく表示されます。
4. **外観 → カスタマイズ → 会社情報** — 電話番号・FAX・住所・受付時間・地図URLを設定。
   ロゴは「サイト基本情報 → ロゴ」から差し替えできます。
5. **ファビコン** — テーマに正方形のファビコンを同梱しています（`assets/img/favicon.*`）。
   「外観 → カスタマイズ → サイト基本情報 → サイトアイコン」を設定した場合はそちらが優先されます。
6. **サービス（CPT: `service`）** — 各サービスにアイキャッチ画像を設定。
   カスタムフィールド `ef_service_en` に英字ラベル（`SOLAR` など）を入れるとカードのバッジに反映されます。
   並び順は「ページ属性 → 順序」で制御します。
7. **固定ページ** — カスタムフィールド `ef_page_en` / `ef_page_lead` で、
   ページヒーローの英字ラベルとリード文を設定できます。
8. **お問い合わせフォーム** — Contact Form 7 等のショートコードを `/contact/` の本文に配置してください。
   フォーム部品のスタイル（`.ef-input` / `.ef-choice` / `.ef-consent` など）は用意済みです。

### CPT `service` について

`service` が未登録の環境でもテーマ単体で動くよう、`inc/post-types.php` で登録しています。
CPT UI などで既に登録済みの場合は `post_type_exists()` により自動的にスキップされるため、
既存の設定を上書きしません。

### 動作確認済み

WordPress 最新版 + PHP 8.4 で、全テンプレート（トップ／固定ページ／CPTアーカイブ／CPT詳細／
投稿一覧／投稿詳細／404）の表示を確認しています。テーマ由来の PHP エラー・警告はありません。

## 5. 画像素材について

いただいた元データ（`assets-original/`）に差し替え済みです。ロゴは 613×383 の背景透過PNG、
サービス写真は 1600×1000 まで解像度が上がっています。

**まだ差し替えが必要なもの（現行サイトのスクリーンショットから抽出した仮素材）:**

| ファイル | 用途 | 現在 | 希望 |
|---|---|---|---|
| `battery.jpg` | 蓄電池のメイン写真 | 555×599（仮） | 幅1600px以上 |
| `ceo.jpg` | 代表者写真（ごあいさつ・トップ） | 555×600（仮） | 幅1200px以上 |

**未使用のままお預かりしている素材**（配置先が決まれば反映します）:

- `Group-3336.png` / `Group-3337-1.png` … スライダー用バナー（PC / SP）
- `sliderlogo.png` / `sliderlogo_sp.png` … エイトフィールズ × 金山製作所
- `22005231`（安全安心）/ `24771223`（家族フィギュア）/ `34379754`（MERIT）/ `34465725`（重要！）
  / `3783554`（家族と家）/ `4303329`（ECO）/ `34234085`（点検）/ `30619421`（新緑）
- `Group-3335-1.png` … 「メンテナンスサポート」の文字が写真に焼き込まれているため、
  テキストは HTML 側で出す設計と重複します。文字なしの元データがあればそちらを使います。

差し替えは `assets-original/` に置いていただければ反映します。

## 6. 掲載テキストについて

会社概要・ごあいさつ・サービスのリード文は、**現行サイトの記載をそのまま使用**しています。
一方で、以下は今回のデザイン提案として新規に書き起こしたものです。ご確認・修正をお願いします。

- トップページのキャッチコピー、「選ばれる3つの理由」
- 各サービス詳細ページの本文・メリット・FAQ（蓄電池ページのみ現行サイトの原稿を踏襲）
- 「ご相談から施工までの流れ」の各ステップ
- お知らせ一覧のサンプル記事（実データではありません）

数値（施工実績 一万棟以上／対応7都県／スタッフ28名／設立2023年）は会社概要の記載に基づいています。
