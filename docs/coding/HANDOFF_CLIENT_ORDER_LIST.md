# Client受注一覧 バックエンドへの申し送り

- 作成日：2026-10-19
- 更新日：2026-10-19
- 対象：`shop-partner/orders.php`（制作用PHP）、`shop-partner/assets/js/order.js`、`shop-partner/assets/scss/order.scss`
- 作成：フロント（Ken・Claude）

Laravel・Bladeへ組み込むときに知っておいてほしいことをまとめた資料。仕様の正は`docs/SPEC.md`と`docs/design/CLIENT_ORDER_LIST.md`で、この資料は組み込み時の注意点に絞っている。内容が食い違う場合は仕様資料を優先し、フロントへ知らせてほしい。

共通の内容は[HANDOFF_CLIENT_COMMON.md](HANDOFF_CLIENT_COMMON.md)を参照。レイアウト・head・サイドバー・共通JS・検索パネルの状態・エラー表示・空状態の共通構造・ページ送り・読み込み順は共通資料へ移した。ここには受注一覧固有の内容だけを記載する。

## 1. 制作用PHPのうち、Bladeへ移さないもの

| 対象 | 内容 | Bladeでの扱い |
|---|---|---|
| `$previewState`（`?preview=`）と各`$is…State` | 表示確認用の状態切り替え。`pager-middle`＝全20ページ中10ページ目、`empty`＝データなし、`no-results`＝検索結果0件、`date-error`＝注文日の逆転エラー | 制作専用の仕組みは共通申し送り§1のとおり移さず、実際の状態で分岐する |
| `$listCount`・`$kpiCounts`・`$filterNote` | 表示例の件数・文言（通常8件、KPIは8／3／2件） | 実際の件数・選択中のKPI名に置き換える |
| 注文3件・ページ送りの番号 | 表示例のデータ。8件のうち3件だけを掲載し、ページ送りは表示確認のため20ページの例を置いている | ループと実データへ置き換える。8件で20ページになる仕様ではない |

## 2. 受注一覧の表示状態

共通の空状態の構造と全件表示・条件解除の扱いは[共通申し送り§6](HANDOFF_CLIENT_COMMON.md#6-一覧の表示状態)を参照。

| 状態 | 受注一覧固有の表示 |
|---|---|
| 通常 | 列見出し＋注文の一覧（`.orders-scroll`） |
| データなし | 「まだ注文はありません」。KPIはすべて0件 |
| 検索結果0件 | 「条件に一致する注文はありません」＋「すべての注文を表示」「条件をクリア」。KPIは全体の件数のまま |

- `.order-detail-inner`の`position: relative`は維持する。閉じた内容の`.sr-only`が外側のarticleを基準に配置され、クリップを逃れて一覧の高さを押し広げることを防ぐ
- 末尾ステータスメニュー用の下余白19.2remは、一覧内スクロールを廃止したことで不要になったため削除済み。メニューを一覧の枠で切らない

## 3. 並び順

- 並び順は仮で新しい注文順（未確定）
- 件数・ページ番号・マークアップ・条件を引き継ぐURLは[共通申し送り§7](HANDOFF_CLIENT_COMMON.md#7-ページ送り)を参照

## 4. KPIの切り替え

- 方式（URLパラメータのリンク／JSでの絞り込み）はバックエンドで決めてほしい
- リンク方式にする場合は、見た目を変えずに`<button type="button" aria-pressed="true|false">`を`<a href="…">`へ差し替え、選択中だけ`aria-current="true"`を付ける。Sassの`[aria-pressed="true"]`の指定は`[aria-current="true"]`へ合わせて変更が必要なので、フロントへ連絡してほしい
- 初期選択は「すべて」。KPIの件数は検索条件に関係なく全体の件数を表す
- KPIの集計条件（未着手・発送待ちの対象ステータス）、キーワード・詳細条件との組み合わせは未確定

## 5. 受注一覧の検索項目とid

共通の開閉・値の復元・入力エラー表示は[共通申し送り§4・§5](HANDOFF_CLIENT_COMMON.md#4-commonjsの詳細検索とアコーディオン)を参照。

- 詳細検索パネルは`#order-search-panel`。パネル内クリア後の先頭inputは注文日の開始日`#order-date-from`
- 注文日の開始日・終了日は`#order-date-from`・`#order-date-to`、共通のエラー文は`#order-date-error`。制作確認の`?preview=date-error`では開始日2026-10-20・終了日2026-10-10の逆転例を出している
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

Bladeへ組み込むときは、次を変えないでほしい（変える場合は`order.js`も合わせて修正が必要）。

| 対象 | 用途 |
|---|---|
| `.select-customer-status[data-selectbox]`、`.selectbox__head`、`.list-wrapper`（`hidden`）、`[data-selectbox-value]`、`[data-selectbox-hidden]`、中のradioとlabel | ステータスの開閉・選択・表示の同期 |
| `.order-card`内の`h3`のテキスト | 読み上げ名に使う注文番号 |
| `.order-toggle`の`aria-controls`・`aria-expanded`・`has-content`、対応する`.order-detail`の`id`と`inert` | メモ・入稿画像の展開。`order.js`から`common.js`の`toggleAccordion`を使う |
| `.image-preview`の`data-*`、`#submission-preview`（dialog）、`.preview-image`、`#preview-file-name`、`.dialog-close` | 入稿画像の拡大表示（全注文で1つのdialogを共用） |

- 注文ごとのidは注文IDから作っている（`order-{ID}`・`status-panel-{ID}`・`status-{ID}-{状態キー}`・`order-detail-{ID}`・`submissions-{ID}`・`memo-{ID}`）。ページ内で重複しないようにする
- radioの`name`は注文ごと（`order-status-choice-{ID}`）に分ける

## 10. 未確定のまま残っている項目

`docs/SPEC.md`の「未確定事項」と`docs/design/CLIENT_ORDER_LIST.md`の「次に決めること」も参照。

- 一覧の並び順
- KPIの方式と集計条件、キーワード・詳細条件との組み合わせ
- ステータスの通信方式と、保存中・成功・失敗の具体的な表示
- メモの保存後の表示、文字数・閲覧権限
- 入稿画像の形式・容量・枚数の上限、ファイル名の規則
- 送料の計算方法・送料無料の条件・消費税の表示・端数処理
- 権限による操作不可の表示
