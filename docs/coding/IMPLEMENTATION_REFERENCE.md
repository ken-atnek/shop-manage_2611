# 管理画面デザイン 実装連携ガイド

## この資料の目的

管理画面を制作用PHP（共通部品のインクルード）・Sassで実装し、バックエンド担当者へ引き渡すための基準。Codexがプロンプトの依頼範囲で制作用PHP（HTML部分）・Sassを作成し、ユーザーが必要な箇所を調整し、Claudeが確認する。デザインはブラウザで確認しながら固める。

Codexは依頼範囲外のPHP・HTMLを作成・変更せず、作成した構造・状態・命名は資料にも整理する。Figmaは必要な場合のみ使用する。

## 資料の優先順位

1. `docs/SPEC.md`
2. `DESIGN_TOKENS.md`
3. `docs/design/DESIGN.md`
4. `docs/coding/LAYOUT_TEMPLATE.md`
5. `docs/coding/IMPLEMENTATION_REFERENCE.md`

案件固有の仕様とユーザーの最新指示を優先する。

## 基本方針

- 共通UIのHTML構造とSassのルールをMaster / Clientで揃える
- フォーム、テーブル、ナビゲーションなどの用途を明確にする
- 見た目のためだけに不要なラッパーを増やさない
- 状態違いは実装時にclassや属性で表現しやすく整理する
- バックエンド処理は実装しないが、送信単位と繰り返し単位は明記する
- 実装難易度を理由にデザインを簡略化しない

## Client画面の制作単位

受注一覧の編集・制作確認は`shop-partner/orders.php`、固有スタイルは`shop-partner/assets/scss/order.scss`→`shop-partner/assets/css/order.css`とする。Client画面で共用するスタイルは`shop-partner/assets/scss/common.scss`→`shop-partner/assets/css/common.css`とし、PHP画面と静的HTMLのどちらも`common.css`→ページ別CSSの順に読み込む。

商品一覧の編集・制作確認は`shop-partner/products.php`、固有スタイルは`shop-partner/assets/scss/products.scss`→`shop-partner/assets/css/products.css`。`common.css`→`products.css`の順に読み込み、検索条件の配置と商品テーブルの仮の見た目だけを`products.scss`に置く。

`_reset.scss`は`common.scss`だけで読み込み、`order.scss`・`products.scss`では重複させない。共通Sassへの参照は、エディタからも参照先を開けるよう相対パスで記載する。

- ルートの`scss/`はreset・変数・既存の関数やmixinなどの読み込み専用とし、画面スタイルは各管理画面側へ置く
- `common.scss`は`@use → コメントヘッダー → body・レイアウト等の基礎 → HEADER → サイドメニュー → フォーム部品 → ページ見出し → 検索 → 一覧見出し → 一覧の状態 → ページ送り`の順とする。ブランド用CSSカスタムプロパティは現在どおり`body`へ設定する
- `order.scss`は`@use → コメントヘッダー → 受注一覧固有のセクション`とする。KPI、受注一覧だけの検索条件配置、列見出し、注文の箱、商品明細、進捗状況、操作、展開行、拡大表示を置き、共通部分は戻さない
- `common.scss`＋ページ別Sassの構成を継続し、受注一覧完成後も`admin.scss`への再編を前提にしない
- 既存の関数・mixinを利用し、Codexは依頼なく新しいmixin・`@extend`・関数・パーシャルを追加したり、ファイルを再編したりしない

Client画面で共用する操作は`shop-partner/assets/js/common.js`、画面固有の操作はページ別JSへ置く。受注一覧は`common.js`→`order.js`の順に`</body>`直前で読み込み、`order.js`は`common.js`の`toggleAccordion`を展開行に使用する。

商品一覧は`common.js`だけを読み込む。`products.js`は作成しない。詳細検索の対象は`aria-controls="product-search-panel"`で指定し、条件クリア後は先頭の「公開」チェックボックスへフォーカスする。

### 制作用PHPのインクルード

- PHPインクルードはユーザー承認済みの制作補助。Laravel・DB・認証・受注処理等は作らず、バックエンド担当者が後からBladeのレイアウト・`@include`等へ組み込む
- 編集するファイルは`shop-partner/orders.php`・`shop-partner/products.php`と`shop-partner/includes/inc_header.php`・`inc_sidebar.php`。共通部品は`inc_`で始め、`require __DIR__ . '/includes/…';`で読み込む。見た目のためのラッパーは追加しない
- 各ページで`$currentPage`を設定する。受注一覧は`orders`、商品一覧は`products`とし、サイドバーの該当リンクだけに`aria-current="page"`を付ける。未作成画面のリンクは引き続き`#`のまま
- 制作確認用のURLは`/shop-partner/orders.php`・`/shop-partner/products.php`。PHP版のリンク・検索フォームは`.php`へ向け、静的出力では`.html`へ切り替える。外部入力でinclude先や現在地を決めない
- 静的HTMLは常設しない。PHPが動かない環境で見せる必要がある場合だけ、プロジェクトルートで`ddev exec php scripts/export-html.php`を実行して書き出す。出力したHTMLはGit管理外（`.gitignore`）で、直接編集しない。ページ追加時はスクリプトの`$pages`へ対象を明示的に追加する
- NodeによるHTMLの組み立て・新しいnpmパッケージ・PHPフレームワークは追加しない。Sassのコンパイル方法は既存のまま
- バックエンド担当者へは制作用PHPと共通部品（`includes/`）・Sass・CSSを引き渡す。静的HTMLは納品しない

## クラス名とSCSSの書き方

- 既存HTMLのクラス名を優先する。`.admin-layout`・`.admin-sidebar`に加え、`.box-identity`・`.box-actions`・`.service-name`など、現在の短い名前とネストをそのまま使う
- `.admin-*`／`.order-*`の接頭辞やBEMは必須にしない。新しい名前は役割が分かるものとし、同名の衝突に気を付ける。依頼なく既存クラスを一括改名しない
- `header`・`nav`・`a`・`button`などの要素セレクタも利用できる。意図した範囲に適用されるよう親セレクタを確認し、不要なクラスやネストを増やさない
- 文字サイズは`scss/_settings.scss`の既存の流動フォント設定と`fz()`を使う。`fz(18)`等は16px基準の相対指定であり、常に18px表示になるわけではない
- 文字サイズは画面幅に応じて流動的に変わる仕様（本文は概ね12〜16px）。読ませたい箇所は`fz()`で個別に大きくし、PC・タブレット横・タブレット縦の幅で確認する
- `scss/_reset.scss`の`* { font-size: 62.5% }`は流動フォント方式の前提として維持する。文字を含む要素には必ず`font-size: inherit`か`fz()`を指定する。未指定の要素は親の62.5%になる（`div`・`p`・`h1`〜`h4`・`dl`・`table`・`td`・`th`・`label`等に注意。`span`・`strong`・`em`・`small`・`time`はリセットで1em）
- `fz()`はem基準のため入れ子で掛け算になる。基準にする要素で1回だけ使い、子は`inherit`にする
- 例外として、入力欄（`input`・`textarea`）の文字は`1.6rem`（16px）固定とする。16px未満だとiOSのSafariがフォーカス時に画面を拡大するため
- ボタンは主（`$color-button-primary-*`）・副（`$color-button-secondary-*`）・切り替え（白地＋枠）・アイコン（背景なし）に分ける。通常のボタンは`scss/_ui.scss`の`button-base`を使い、主ボタンは`button-primary`、副ボタンは`button-secondary`を後から重ねる。色は`scss/_colors.scss`の変数を使い、ブランド色の塗りはログアウトだけ
- リセットの`font-weight: unset`により太さは親から継承されるため、太字が必要な要素は`font-weight`を明示する
- アイコンはインラインsvgで書く。`common.scss`ではsvgの幅・線・塗りを上書きせず、`scss/_reset.scss`の指定に任せる
  - 線のアイコン：`fill`・`stroke`・`stroke-linecap`・`stroke-linejoin`の属性を書かない（リセットの「fill属性のないsvg＝文字色の線・丸い角」が効く）。`stroke-width`と`width`・`height`・`viewBox`は書く
  - 塗りのアイコン：`fill`属性を書く（リセットで文字色の塗りになる）
  - すべてのアイコンに`aria-hidden="true" focusable="false"`を付け、意味はボタンの`aria-label`で伝える。色は`color`で変える
- フォーカス枠は`scss/_reset.scss`の共通指定（`:where(...)`で詳細度0、幅3px・`--color-brand-accent`）を基本とし、枠が切れる・埋もれる箇所だけ個別に上書きする
- フォントはNoto Sans JPをGoogle Fontsから読み込む。各HTMLの`<head>`で、`common.css`より前に`fonts.googleapis.com`・`fonts.gstatic.com`の`preconnect`と`family=Noto+Sans+JP:wght@400;500;700&display=swap`の`stylesheet`を置く
- 余白・幅・罫線・角丸などは、現在のように`px`・`rem`・`em`の直接指定も認める。すべての数値の変数化や、削除済みの`$space-*`・`$font-size-*`等の復活は行わない
- 共通で調整する必要がある値だけ、既存変数を利用する。新しい変数の追加は必要になった箇所に限定する。色は既存の色変数とブランド用CSSカスタムプロパティを優先する
- サイズの旧仮案よりユーザーが調整した現行SCSSを優先し、実装時は読みやすさ・タップ領域・画面幅をブラウザで確認する。小さい文字や操作しにくい箇所は、構成を変えず個別に調整する

### アイコンセット

- 操作用の線アイコンはLucide（ISC License）を基本とする。LICENSEの一覧にあるFeather由来のアイコン（受注一覧で使用する4種が該当）は、MITの通知も含めて保持する。SVGコードだけをインラインで使い、npmパッケージ・Iconify等の外部スクリプトは読み込まない。ライセンス全文は`docs/design/reference/LUCIDE_LICENSE`
- LucideのSVGは公式の子要素（`path`・`circle`・`line`等）の形を変えず、外側の`svg`を`width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"`にそろえる
- `fill`・`stroke`・`stroke-linecap`・`stroke-linejoin`属性は付けず、`scss/_reset.scss`の線色・線端・角の指定を効かせる。用途名はSVGではなく操作するボタン・リンクの`aria-label`と`title`で伝える

| 用途 | Lucideの名前 | 使っている画面 |
|---|---|---|
| 入稿画像の拡大 | `zoom-in` | Client受注一覧 |
| 入稿画像のダウンロード | `download` | Client受注一覧 |
| ページ送り「前へ」 | `chevron-left` | Client受注一覧・商品一覧 |
| ページ送り「次へ」 | `chevron-right` | Client受注一覧・商品一覧 |
| 商品詳細への編集 | `pencil` | Client商品一覧 |
| メモ・入稿画像の展開 | Lucideではない（独自SVG） | Client受注一覧 |
| 詳細検索の開閉 | Lucideではない（CSSの罫線） | Client受注一覧・商品一覧 |
| ステータスの開閉 | Lucideではない（`scss/_ui.scss`の背景SVG） | Client受注一覧 |

## 制作環境と引き渡し

- Sassの編集元、出力先、npmコマンドは`docs/SPEC.md`の「フォルダとSass」を参照する
- 引き渡すものは制作用PHP（ページと`includes/`の共通部品）、共通・管理画面別のSass、生成CSSと必要なアセット、仕様資料とする。静的HTMLは含めない
- Sassの再コンパイル用に`package.json`と`package-lock.json`も渡す。`node_modules/`は含めない
- Laravel・Bladeへの組み込みはバックエンド担当者が同じリポジトリで行う。フロントの作業ではLaravelを構築しない
- Vite・Astroは現時点では使用しない
- 画面ごとの組み込み時の注意点は申し送り資料にまとめる（Client受注一覧：`docs/coding/HANDOFF_CLIENT_ORDER_LIST.md`）

## HTML要素の考え方

- ページ共通の主見出しは`h1`を基本にする
- ナビゲーションは`nav`を使用する
- 主要コンテンツは`main`を使用する
- 入力項目は`label`と`input`、`select`、`textarea`を用途に合わせて使用する
- 実行操作は`button`、ページ遷移は`a`を基本にする
- 表形式のデータは、意味上適切であれば`table`を使用する
- ダイアログは実装環境に合わせて`dialog`または適切なARIA属性を検討する

## コメント記述ルール

### ファイル冒頭のコメントヘッダー

手動作成するHTML・制作用PHP・Sassには、用途・ファイルの場所・利用元・作成日・更新日を統一形式で記載する。これは、こうの湯・ふもと旅館のコメントヘッダー規約を、本プロジェクトの静的HTML・Sass向けに読み替えたもの。依頼なく新規画面のHTMLを生成しない方針は維持し、承認済みのPHPインクルードの静的出力とは区別する。

- 新規ファイルの作成時に記載する。既存ファイルにない場合は、そのファイルを次に修正するときに追加する。ヘッダー追加のためだけの一括改修はしない
- タイトルは「熊日モール＋管理画面種別＋ページ名または用途」で簡潔に書く。対応するHTMLとSassで名前をそろえる
- `URL`はWebサイトのURLではなく、プロジェクトルートを基準に`/`から始めたファイルパスを書く
- `Referenced in`は主な利用ページ・読み込み元を書く。単独のHTMLページは自身のパス、Sassは対応するHTMLまたは読み込み元Sassのパスを記載する
- `Created`は作成日を保持する。既存の作成日が不明なら推測せず`不明`とし、確認できた時点で補う
- ファイルを修正したら`Last updated`を実際の修正日へ更新する。日付は`YYYY-MM-DD`で統一する。下記の`YYYY-MM-DD`はテンプレート用のプレースホルダーで、そのまま実ファイルへ残さない
- パスや利用元に誤記がある場合は、対象ファイルを修正するときに併せて直す
- 生成CSS・ソースマップには手動で追加しない。外部ライブラリなどの既存ライセンス・著作権表記は維持する
- 制作用PHPは、ページ本文ではdoctype直後のHTMLコメント、共通部品（`includes/`）とCLIスクリプトは先頭のPHPコメント（`<?php /* … */ ?>`）に記載し、出力HTMLの本文に制作用コメントを出さない。ページ冒頭のPHP設定はHTMLを出力しない

### HTMLのテンプレート

`<!doctype html>`は先頭に維持し、その直後、`<html>`より前へHTMLコメントとして記載する。

```html
<!doctype html>
<!-- =======================================
 * 熊日モール Client 受注一覧
 * URL: /shop-partner/orders.php
 * Referenced in: /shop-partner/orders.php
 * Created: YYYY-MM-DD
 * Last updated: YYYY-MM-DD
 * ======================================= -->
```

### Sassのテンプレート

先頭の`@use`群の直後へ`/* ... */`のコメントヘッダーを記載する。`@use`がなければ先頭に置き、`@charset`がある場合はその後とする。読み込み先・順序・ファイル構成は変更せず、本プロジェクトの既存ルールを優先する。

```scss
// ===== 読み込み =====
@use "../../../scss/index" as *;

/* =======================================
 * 熊日モール Client 受注一覧 スタイル
 * URL: /shop-partner/assets/scss/order.scss
 * Referenced in: /shop-partner/orders.php
 * Created: YYYY-MM-DD
 * Last updated: YYYY-MM-DD
 * ======================================= */

// ===== ページ固有のセクション =====
```

### コード内の補足コメント

- ファイルヘッダーとは別に、`// ===== セクション名 =====`や`/* ... */`による既存のセクション区切りを維持する。固定の書式へ一括置換せず、各ファイルの書き方に合わせる
- コードを読めば分かる内容を繰り返さず、実装理由・制約・状態など分かりにくい箇所だけ短く説明する。Sassの内部説明には`//`を使う
- 未確定の仕様を確定事項として書かず、不要な旧コードのコメントアウトを蓄積しない。HTMLコメントには顧客情報・認証情報を記載しない

### 規約の参照元

- こうの湯：`/Users/ken/site_data/xbaf8039.xbiz.jp/kounoyu.jp/public_html/kounoyu_2608/docs/rules/tsx-comment-rules.md`、同ディレクトリの`scss-comment-rules.md`
- ふもと旅館：`/Users/ken/site_data/xbaf8039.xbiz.jp/fumotoryokan.com/public_html/fumoto_2606/docs/rules/tsx-comment-rules.md`、同ディレクトリの`scss-comment-rules.md`
- ふもと旅館採用：`/Users/ken/site_data/xbaf8039.xbiz.jp/fumoto-ryokan.jp/public_html/recruit/recruit_fumoto_2609/docs/DEVELOPMENT.md`の「コメントヘッダー」

取り込むのはファイルのメタ情報と更新日の運用。TSX・Astro・module.scssやfoundationの構成は導入せず、HTMLのコメント構文・配置と本プロジェクトの読み込み先へ置き換える。

## 状態の命名候補

| 状態 | class候補 |
|---|---|
| 選択中 | `.is-active` / `.is-selected` |
| 現在位置 | `.is-current` |
| 無効 | `.is-disabled` |
| エラー | `.is-error` |
| 読み込み中 | `.is-loading` |
| 展開中 | `.is-open` |
| 未保存 | `.is-dirty` |

JavaScriptの操作対象には、必要に応じて`id`、`data-*`、`aria-expanded`などを付ける。

## 実装引き渡し時に整理する内容

- 画面名と目的
- 共通レイアウトとページ固有部分
- コンポーネント名と状態
- 入力項目、必須・任意、補助文、エラー文
- 表示データ、空状態、読み込み状態、エラー状態
- モーダルやドロワーの開閉条件
- 保存、削除、公開など操作後の結果
- レスポンシブ時の並びと表示・非表示

## 完了時の確認

- 制作用PHPと仕様資料から主要な構造・状態・操作を判断できるか
- 入力、リンク、ボタンの用途が区別できるか
- コンポーネントの全状態が揃っているか
- デザイントークンと画面内の値が一致しているか
- 指定された画面幅でレイアウトを確認したか
- バックエンド担当者が送信単位、繰り返し単位、動的表示箇所を判断できるか

