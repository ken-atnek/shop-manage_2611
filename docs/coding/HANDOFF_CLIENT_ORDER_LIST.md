# Client受注一覧 バックエンドへの申し送り

- 作成日：2026-10-19
- 対象：`shop-partner/orders.php`（制作用PHP）、`shop-partner/assets/js/common.js`・`order.js`、`shop-partner/assets/scss/common.scss`・`order.scss`
- 作成：フロント（Ken・Claude）

Laravel・Bladeへ組み込むときに知っておいてほしいことをまとめた資料。仕様の正は`docs/SPEC.md`と`docs/design/CLIENT_ORDER_LIST.md`で、この資料は組み込み時の注意点に絞っている。内容が食い違う場合は仕様資料を優先し、フロントへ知らせてほしい。

## 1. 制作用PHPのうち、Bladeへ移さないもの

| 対象 | 内容 | Bladeでの扱い |
|---|---|---|
| `$previewState`（`?preview=`） | 表示確認用の状態切り替え。`pager-middle`・`empty`・`no-results`・`date-error` | 移さない。実際のデータ・検索結果・検証結果で分岐する |
| `$listCount`・`$kpiCounts`・`$filterNote` | 表示例の件数・文言 | 実際の件数・選択中のKPI名に置き換える |
| `$staticExport`・`$pageExtension` | 静的HTML書き出し用 | 移さない。リンク先はルーティングに合わせる |
| `$currentPage` | サイドバーの`aria-current="page"`の判定 | 現在のルートで判定する |
| 注文3件・ページ送りの番号 | 表示例のデータ | ループと実データへ置き換える |

`includes/inc_header.php`・`inc_sidebar.php`は、Bladeのレイアウト・`@include`へ置き換える。

## 2. 一覧の表示状態

| 状態 | 条件 | 表示 |
|---|---|---|
| 通常 | 注文が1件以上 | 列見出し＋注文の一覧（`.orders-scroll`）＋ページ送り（`.pagination`） |
| データなし | 注文が1件もない | `.orders-scroll`とページ送りの代わりに`.list-empty`「まだ注文はありません」。KPIはすべて0件 |
| 検索結果0件 | 検索・KPIの条件に一致する注文がない | `.list-empty`「条件に一致する注文はありません」＋`.show-all`「すべての注文を表示」＋「条件をクリア」。KPIは全体の件数のまま |

- 一覧見出し（`.list-heading`）の件数と対象名（`filter-note`）は、表示中の条件に合わせて出力する
- 「条件をクリア」の遷移先（キーワード・詳細条件を外すURL）はバックエンドで決める。今は`href="#"`の仮リンク
- 一覧はサーバー側で描画する前提のため、一覧の「読込中」表示は作っていない

## 3. ページ送り

- 1ページ50件。並び順は仮で新しい注文順（未確定）
- ページ数・現在ページ・各リンクのURLはバックエンドで生成する。今の`?page=N`とパラメータ名は仮。**キーワード・KPI・詳細条件のパラメータを引き継いだURLにする**（今の仮リンクは引き継がない）
- 表示ルール（番号と省略記号で最大7項目）
  - 全7ページ以下：全番号を表示
  - 8ページ以上：最初と最後のページは常に表示し、現在ページの前後1ページを基本に、空いた範囲を「…」にする
  - 例（全20ページ）：1ページ目 `1 2 3 4 5 … 20 ›`、10ページ目 `‹ 1 … 9 10 11 … 20 ›`、20ページ目 `‹ 1 … 16 17 18 19 20`
  - 「前へ」は2ページ目以降、「次へ」は最終ページより前だけ出力する
- マークアップ
  - 現在ページは`<a aria-current="page">`
  - 番号には読み上げ用の`<span class="sr-only">ページ</span>`、矢印には`前のページ`／`次のページ`を入れる
  - 「…」は`<span class="pagination-ellipsis" aria-hidden="true">`
  - 右端の`<p class="page-status">現在 / 全体<span class="sr-only">ページ</span></p>`は表示だけ
- 1ページしかない場合にページ送りを出すかは未確定。フロントの案は「出さない」

## 4. KPIの切り替え

- 方式（URLパラメータのリンク／JSでの絞り込み）はバックエンドで決めてほしい
- リンク方式にする場合は、見た目を変えずに`<button type="button" aria-pressed="true|false">`を`<a href="…">`へ差し替え、選択中だけ`aria-current="true"`を付ける。Sassの`[aria-pressed="true"]`の指定は`[aria-current="true"]`へ合わせて変更が必要なので、フロントへ連絡してほしい
- 初期選択は「すべて」。KPIの件数は検索条件に関係なく全体の件数を表す
- KPIの集計条件（未着手・発送待ちの対象ステータス）、キーワード・詳細条件との組み合わせは未確定

## 5. 詳細検索と入力エラー

- 詳細検索パネル（`#order-search-panel`）は初期状態で閉じている（`inert`、ボタンは`aria-expanded="false"`）
- **詳細条件が入った状態で検索結果を表示するときは、パネルを開いた状態で出力する**：`#order-search-panel`の`inert`を外し、`.detail-toggle`を`aria-expanded="true"`にする。入力値・チェックも復元する
- 入力エラー（例：注文日の開始日と終了日が逆）の判定はバックエンドで行う。エラー時だけ次を出力する（例は`?preview=date-error`）
  - 対象の入力欄に`aria-invalid="true"`と`aria-describedby="エラー文のid"`
  - fieldset／項目内に`<p class="form-error" id="…">エラー文</p>`
  - 注文日は開始日・終了日の両方から、1つのエラー文（`#order-date-error`）を参照する
- `name`属性（`date_from`・`date_to`・`status[]`・`phone`・`email`・`product`・`keyword`）は表示例。正式な項目名はバックエンドで決めてよい（変えた場合はフロントへ連絡してほしい）

## 6. ステータス（発送状況）の保存

- 選んだ時点で即時保存（非同期）。注文ごとの保存ボタンは置かない
- ステータス変更では注文者へメールを送らない。**「キャンセル」を選んだときだけ確認ダイアログ**を挟む
- 保存中・成功・失敗の表示は、通信方式が決まってからフロントで作る。決まっている内容は次のとおり
  - 保存中：そのステータスのボタンを操作不可にする
  - 成功：最終更新日時（`.updated time`）を保存時刻へ書き換え、`aria-live`で「保存しました」を伝える
  - 失敗：元のステータスへ戻し、エラー文と再試行の手段を出す。最終更新日時は変えない
- 今の`order.js`は表示・hidden値・読み上げ名を同期するだけで、通信していない（`syncStatus`）。保存処理を足す位置はここ
- `<input type="hidden" name="order-status[注文ID]" data-selectbox-hidden>`は**どのformにも入っていない**。非同期で送るなら値の参照元として使い、form送信にするなら配置を変える必要がある
- 選択肢の値は表示例で`1`新規受付・`2`対応中・`3`発送済・`4`返品対応・`5`キャンセル。正式な値はバックエンドで決めてよい。ラベルのclass（`status-new`・`status-processing`・`status-shipped`・`status-return`・`status-cancelled`）は色の指定に使うので変えない
- 最終更新日時はステータスの保存時だけ更新する。メモ保存・画像の確認やダウンロードでは更新しない

## 7. メモ

- 注文ごとの`<form class="memo-form" method="post" action="">`。送信先・項目名（`order_id`・`memo`）は表示例
- 保存処理・保存後の表示（成功・失敗）はバックエンドで実装する。表示方法の詳細は未確定
- labelと保存ボタンには、読み上げ用の注文番号（`sr-only`）が入っている。ループで出力するときも残す

## 8. 入稿画像

- 注文内の商品明細ごとに紐づく。入稿のある商品だけを出力し、どの商品にも入稿がない注文は`.submissions`ごと出力しない
- 展開ボタン`.order-toggle`は、メモまたは入稿画像があるときだけ`has-content`を付ける（濃い色と点で示す）。読み上げ名・`title`の末尾の「（メモ・入稿画像あり）」も同じ条件で付ける
- **サムネイルは縮小画像を出力し、`loading="lazy"`を付けてほしい**。今は表示例のため、拡大表示と同じ元画像を使っている。展開行は初期状態で閉じているため、元画像のままだと一覧を開いただけで全画像を読み込む
- 拡大ボタン`.image-preview`の`data-image-src`（拡大用の画像）・`data-image-alt`・`data-file-name`をJSが共通のdialogへ渡す
- ダウンロードリンクの`download`属性は、別ドメインから配信すると効かない。その場合はサーバー側で`Content-Disposition: attachment`を返してほしい

## 9. JSが依存しているclass・属性・id

Bladeへ組み込むときは、次を変えないでほしい（変える場合は担当する`common.js`または`order.js`も合わせて修正が必要）。

| 対象 | 用途 |
|---|---|
| `.select-customer-status[data-selectbox]`、`.selectbox__head`、`.list-wrapper`（`hidden`）、`[data-selectbox-value]`、`[data-selectbox-hidden]`、中のradioとlabel | ステータスの開閉・選択・表示の同期 |
| `.order-card`内の`h3`のテキスト | 読み上げ名に使う注文番号 |
| `.order-toggle`の`aria-controls`・`aria-expanded`・`has-content`、対応する`.order-detail`の`id`と`inert` | メモ・入稿画像の展開。`order.js`から`common.js`の`toggleAccordion`を使う |
| `.image-preview`の`data-*`、`#submission-preview`（dialog）、`.preview-image`、`#preview-file-name`、`.dialog-close` | 入稿画像の拡大表示（全注文で1つのdialogを共用） |
| `.detail-toggle`の`aria-controls`・`aria-expanded`、対応する検索パネルの`id`と`inert`、`.clear-conditions`、パネル内の最初の`input` | `common.js`による詳細検索の開閉とクリア。パネルは`aria-controls`の値から取得する |

- 注文ごとのidは注文IDから作っている（`order-{ID}`・`status-panel-{ID}`・`status-{ID}-{状態キー}`・`order-detail-{ID}`・`submissions-{ID}`・`memo-{ID}`）。ページ内で重複しないようにする
- radioの`name`は注文ごと（`order-status-choice-{ID}`）に分ける
- `common.js`→`order.js`の順に`</body>`直前で各1回読み込む。`common.js`を先に読み込まないと、`order.js`の展開行で`toggleAccordion`を使えない
- JS・CSSを更新したときに古いファイルがブラウザに残らないよう、読み込むURLにバージョンを付けてほしい（Laravelの仕組み、またはファイルの更新日時を`?v=`に付けるなど）。`common.js`と`order.js`は必ず組で更新する。片方だけ古いと、受注一覧の操作がすべて止まることがある

## 10. 未確定のまま残っている項目

`docs/SPEC.md`の「未確定事項」と`docs/design/CLIENT_ORDER_LIST.md`の「次に決めること」も参照。

- 一覧の並び順、1ページしかない場合のページ送りの表示
- KPIの方式と集計条件、キーワード・詳細条件との組み合わせ
- ステータスの通信方式と、保存中・成功・失敗の具体的な表示
- メモの保存後の表示、文字数・閲覧権限
- 入稿画像の形式・容量・枚数の上限、ファイル名の規則
- 送料の計算方法・送料無料の条件・消費税の表示・端数処理
- 権限による操作不可の表示
