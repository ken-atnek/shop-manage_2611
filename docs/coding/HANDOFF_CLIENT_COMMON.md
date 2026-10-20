# Client共通 バックエンドへの申し送り

- 作成日：2026-10-19
- 対象：`shop-partner/includes/`、両ページの`head`、`shop-partner/assets/scss/common.scss`、`shop-partner/assets/js/common.js`
- 作成：フロント（Ken・Codex）

Client画面をLaravel・Bladeへ組み込むときの共通の注意点をまとめた資料。画面固有の内容は[受注一覧](HANDOFF_CLIENT_ORDER_LIST.md)・[商品一覧](HANDOFF_CLIENT_PRODUCT_LIST.md)を参照する。仕様の正は`docs/SPEC.md`と各画面資料で、内容が食い違う場合は仕様資料を優先し、フロントへ知らせてほしい。

## 1. 共通レイアウトと制作専用の仕組み

- `shop-partner/includes/inc_header.php`・`inc_sidebar.php`は、Bladeのレイアウト・`@include`へ置き換える
- `$currentPage`は制作用の現在地判定。受注一覧は`orders`、商品一覧は`products`で、該当リンクだけに`aria-current="page"`を付けている。Bladeでは現在のルートで判定し、現在地の見た目と属性を維持する
- `$staticExport`・`$pageExtension`は静的HTML書き出し用なので移さない。リンク・フォームの送信先は実際のルーティングへ置き換える。共通ヘッダーの「サイトを確認」・ログアウト、未作成画面の`#`も仮の指定
- 制作確認用の`?preview=`と、それに基づく状態フラグはBladeへ移さない。実際のデータ・検索結果・検証結果で分岐する。画面ごとのpreviewの種類と表示例は各申し送り§1を参照
- スクロールは縦横ともページ全体で行う。一覧のラッパー（`.orders-scroll`・`.products-scroll`）は名前付きスクロール領域ではない。`overflow-x: auto`や`role="region"`・スクロール用の`aria-label`・`tabindex="0"`を戻さない
- body・`.admin-main`の`min-width: min-content`とサイドバーの固定幅を維持し、一覧の最小幅をページ全体へ伝える。ヘッダーの面・下線とサイドバーの面をページの右端・下端まで続ける。table・注文自体の名前付けは維持する

## 2. headの読み込みと初期化

- Google FontsのNoto Sans JPを400・500・600で読み込む。`fonts.googleapis.com`・`fonts.gstatic.com`のpreconnectと、`family=Noto+Sans+JP:wght@400;500;600&display=swap`のstylesheetを`common.css`より前に置く
- 見出し・強調数値・現在地・サービス名は600、ラベル・ボタン・バッジ／受注ステータスは500、本文・表の中身は400。700は使用しない。詳細は`DESIGN_TOKENS.md`を参照
- `orders.php`・`products.php`のheadにある同一のサイドバー初期化インラインスクリプトを、共通レイアウトへ移して`common.css`より前に1回読み込む。`matchMedia("(max-width: 1194px)")`または保存値`collapsed`ならhtmlへ`.sidebar-collapsed`を付け、初期描画のちらつきを防ぐ。`common.js`が同じ状態とボタンの属性を同期する
- headと`common.js`のlocalStorage読み書きはtry/catchで保護する。保存できなくても当該ページ内の開閉・詳細検索を止めない。幅1194pxは共通Sassの`$tbw`と必ず同時に変更する
- CSPでインラインスクリプトを制限する場合はバックエンド側でnonce等を付ける。Google FontsのCSS・フォント配信元も利用するCSPに合わせて許可する

## 3. 共通サイドバーの初期化と保存

- PC（1194pxより広い）は初期値を開いた状態（18rem）とし、畳むと6.4remになる。本文幅も変える。localStorageのキー`kumanichi-client-sidebar`へ値`expanded`／`collapsed`を保存する。保存なし・不明な値は開く。タブレットの開閉ではこの値を上書きしない
- タブレット（1194px以下、向きは問わない）は新規表示・再読み込み・戻る／進むの`pageshow`で畳む。本文には6.4remだけ確保し、開くと18remのナビを本文に重ねる。Esc・外側クリック・開閉ボタンで閉じたら開閉ボタンへフォーカスを戻す。PCへ幅を戻した場合はPCの保存状態へ戻る
- サイドバーはモーダルではなく通常のナビ。畳んだリンクをinert／hiddenにせず、文字だけを`.sr-only`相当で隠す。読み上げ名・同名のtitle・44px以上の操作領域・現在地の面と左バー・フォーカス枠を維持する
- 重ね表示の境界は線で示し、影は使わない。幅のtransitionは`prefers-reduced-motion: no-preference`時だけ0.2秒で、動きを減らす設定では無効

JS・CSSが依存するclass・属性（変える場合は共通JS・CSSも合わせて修正が必要）：

| 対象 | 用途 |
|---|---|
| `.admin-sidebar`、中の`.sidebar-toggle`、`aria-expanded`・`aria-label`・`title` | `common.js`によるナビの開閉、外側クリックの判定、閉じた後のフォーカス復帰。「メニューを畳む／メニューを開く」の名前を同期する |
| htmlの`.sidebar-collapsed`・`.sidebar-ready` | head・共通JS・共通CSSで共有する開閉状態と、操作時だけ幅のtransitionを有効にする印 |
| `.sidebar-content`・`.nav-label`・`.sidebar-close-icon`・`.sidebar-open-icon`、`#client-navigation`とボタンの`aria-controls` | 重ね表示・文字の視覚的な非表示・アイコン切り替え、ボタンとナビの関連付け。リンクのtitleと現在地`aria-current="page"`も維持 |

## 4. common.jsの詳細検索とアコーディオン

| 対象 | 用途 |
|---|---|
| `.detail-toggle`の`aria-controls`・`aria-expanded`、対応する`.search-panel`の`id`と`inert` | パネルは`aria-controls`の値から取得する。開閉時に`aria-expanded`・`inert`を同期し、フォーカスは詳細検索ボタンに残す |
| パネル内の`.clear-conditions`、最初の`input` | パネル内の入力値を空にし、チェックを解除して、最初のinputへフォーカスする |
| `toggleAccordion(button, panel)` | ボタンの`aria-expanded`とパネルの`inert`を切り替え、開いたかを返す共通関数。詳細検索と、`order.js`の展開行から使用する |

- 通常の初期状態は閉じる（パネルは`inert`、ボタンは`aria-expanded="false"`）。閉じたパネルをTab操作・読み上げの対象から外す
- 詳細条件が入った状態で検索結果を表示するときは、パネルを開いた状態で出力する。パネルの`inert`を外し、`.detail-toggle`を`aria-expanded="true"`にして、入力値・チェックも復元する
- `.clear-conditions`は`type="button"`のままにする。キーワード・KPI等のパネル外の条件は維持し、クリアだけでは検索実行や一覧の更新をしない。検索結果0件の「条件をクリア」リンクとは別の操作
- フォーカス先を日付等に固定しない。現行の先頭inputは受注一覧が注文日の開始日、商品一覧が「公開」のチェックボックス
- `.detail-toggle`がないページでもエラーにしない。詳細検索の処理はブロックスコープ内、`toggleAccordion`はページ別JSから使える位置に維持する
- `.search-panel-inner`の`position: relative`は維持する。閉じた内容の絶対配置`.sr-only`が外側を基準にしてクリップを逃れ、一覧の高さを押し広げることを防ぐ

## 5. 入力エラー

- 入力エラーの判定はバックエンドで行う。対象欄が見えるよう詳細検索パネルを開き、入力値を復元する
- エラー時だけ対象の入力欄に`aria-invalid="true"`と`aria-describedby="エラー文のid"`、fieldset／項目内に`<p class="form-error" id="…">エラー文</p>`を出力する。正常時はエラー用属性・文を出さない
- 期間は開始日・終了日の両方から1つのエラー文を参照する。開始日と終了日が逆の場合の文言は「終了日は開始日以降の日付を指定してください」。画面固有の入力・エラーidは各申し送りを参照

## 6. 一覧の表示状態

| 状態 | 条件 | 表示 |
|---|---|---|
| 通常 | 表示対象のデータがある | 一覧＋ページ送り |
| データなし | データ自体が1件もない | 一覧とページ送りの代わりに`.list-empty`で案内する |
| 検索結果0件 | 条件に一致するデータがない | `.list-empty`で案内し、`.empty-actions`内に`.show-all`の全件表示リンクと「条件をクリア」リンクを置く |

- 文言と画面固有の表示は各申し送りを参照。一覧見出し（`.list-heading`）の件数と対象名（`.filter-note`）は表示中の条件に合わせて出力する。既存の`aria-live="polite"`・`aria-atomic="true"`を維持する
- 「条件をクリア」の遷移先（キーワード・詳細条件を外すURL）・解除対象はバックエンドで決める。今は`href="#"`の仮リンク。パネル内の入力を空にするだけの共通JSとは分けて組み込む
- 一覧はサーバー側で描画する前提のため、一覧の「読込中」表示は作っていない

## 7. ページ送り

- 共通部品`.pagination`の構造・classを維持する
- 1ページ50件。並び順は画面ごとの申し送りを参照
- ページ数・現在ページ・各リンクのURLはバックエンドで生成する。今の`?page=N`とパラメータ名は仮。キーワード・詳細条件・画面固有の絞り込み（受注一覧のKPI等）のパラメータを引き継いだURLにする（今の仮リンクは引き継がない）
- 表示ルール（番号と省略記号で最大7項目。前後の矢印はこの数に含めない）
  - 全7ページ以下：全番号を表示
  - 8ページ以上：最初と最後のページは常に表示し、現在ページの前後1ページを基本に、空いた範囲を「…」にする。先頭付近は先頭5番号、末尾付近は末尾5番号を表示する
  - 例（全20ページ）：1ページ目 `1 2 3 4 5 … 20 ›`、10ページ目 `‹ 1 … 9 10 11 … 20 ›`、20ページ目 `‹ 1 … 16 17 18 19 20`
  - 「前へ」は2ページ目以降、「次へ」は最終ページより前だけ出力する
- マークアップ
  - `<nav class="pagination" aria-label="画面に応じた一覧のページ">`内に`<ul class="pagination-list" role="list">`を置く
  - 現在ページは`<a aria-current="page">`
  - 番号には読み上げ用の`<span class="sr-only">ページ</span>`、矢印には`前のページ`／`次のページ`を入れる。矢印のSVGは`aria-hidden="true" focusable="false"`を維持する
  - 「…」は`<span class="pagination-ellipsis" aria-hidden="true">`
  - 右端の`<p class="page-status">現在 / 全体<span class="sr-only">ページ</span></p>`は表示だけ
- 1ページしかない場合にページ送りを出すかは未確定。フロントの案は「出さない」

## 8. CSS・JSの読み込み順とキャッシュ対策

- CSSは`common.css`→ページ別CSS。resetは`common.scss`だけで読み込み、ページ別Sassで重複させない
- JSは`common.js`→ページ別JSの順に`</body>`直前で各1回読み込む。受注一覧は`common.js`→`order.js`、商品一覧は`common.js`だけ
- `common.js`を先に読み込まないと、`order.js`の展開行で`toggleAccordion`を使えない。読み込み順を変えたり、共通関数をページ別JSから参照できないスコープへ移したりしない
- JS・CSSを更新したときに古いファイルがブラウザに残らないよう、読み込むURLにバージョンを付けてほしい（Laravelの仕組み、またはファイルの更新日時を`?v=`に付けるなど）。共通ファイルと対応するページ別ファイルは組で更新する。特に`common.js`と`order.js`は必ず組で更新する。片方だけ古いと、受注一覧の操作がすべて止まることがある

## 9. 未確定のまま残っている項目

- 検索結果0件の「条件をクリア」の遷移先・解除対象
- 1ページしかない場合のページ送りの表示

画面ごとの業務条件・保存方式等は各申し送りの未確定事項を参照する。決まった内容は`docs/SPEC.md`と該当する画面資料にも反映してほしい。
