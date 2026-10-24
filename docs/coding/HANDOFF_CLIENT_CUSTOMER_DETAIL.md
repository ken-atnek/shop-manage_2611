# Client顧客詳細 バックエンドへの申し送り

- 作成日：2026-10-23
- 対象：`shop-partner/customer-detail.php`（制作用PHP）、`shop-partner/assets/scss/customer-detail.scss`、生成CSS（`shop-partner/assets/css/customer-detail.css`。`npm run sass`で作る。Gitでは追跡しない）
- 作成：フロント（Ken・Codex）

共通の内容は[HANDOFF_CLIENT_COMMON.md](HANDOFF_CLIENT_COMMON.md)を参照。ここには顧客詳細固有の内容だけを記載する。仕様の正は[SPECの顧客管理の確定事項](../SPEC.md#顧客管理の確定事項)と[顧客詳細の画面資料](../design/CLIENT_CUSTOMER_DETAIL.md)。食い違う場合は仕様資料を優先し、フロントへ知らせてほしい。顧客のデータ・識別・閲覧範囲についての決定依頼は[顧客一覧の申し送り§9](HANDOFF_CLIENT_CUSTOMER_LIST.md#9-バックエンドで決めてほしいこと)にまとめる。

## 1. 制作用PHPのうち、Bladeへ移さないもの

共通レイアウト・静的出力の扱いは[共通申し送り§1](HANDOFF_CLIENT_COMMON.md#1-共通レイアウトと制作専用の仕組み)を参照。

| 対象 | 内容 | Bladeでの扱い |
|---|---|---|
| `$previewState`（`?preview=`）・`$isSingleState`・`$isNotFoundState`・`$isErrorState` | `single`・`not-found`・`error`の制作確認用分岐。静的出力時はpreviewなし | 実際の顧客・注文・取得結果で分岐する |
| `$sampleCustomerIds`・`$requestedId`・`$customerId` | 候補1042・87・615・2308・319・1705のみ受け付け、それ以外は1042へ戻す。未指定は通常1042／singleは87、静的出力は1042。全IDで基本情報・履歴は同じ | 候補への制限・1042へのフォールバックは移さない。実際の識別子・所属・権限で取得し、未存在等を判定する。任意IDではnot-foundを再現できない |
| `$latestBuyer`・`$previousBuyer`・`$giftRecipient`・`$longRecipient`・`$customerEmail` | 架空の氏名・住所・000-0000の郵便番号・000始まりの電話番号・`customer.detail@example.com` | 閲覧可能な注文時点の注文者情報・届け先へ置き換える。最新情報と各注文の保存情報を区別する |
| `$orders`と各`$order`・`$item` | 5件・2店舗・5状況。注文番号2610210112345・2610200112346・2609150212347・2608010212348・2606010112349、2026年6〜10月の日時、商品名・個数・小計・送料はダミー | 実際の履歴・明細のループへ置き換える。13桁の番号を採番仕様にしない。ID・読み上げ名は実際の注文と対応させる |
| 注文合計・送料 | 新しい順に¥12,800・¥5,600・¥24,000・¥3,200・¥3,000。送料は順に800・800・0・800・800円。商品明細の小計合計＋送料をそろえた表示例 | 実際の注文時の保存金額を表示する。送料無料の文言は制作上の案 |
| `$orders[0]['destinationSameBuyer']`・`$orders[3]['destinationSameBuyer']` | 最新と返品対応の2注文に`true`を固定設定。他は未設定でfalse扱い | 固定値を移さず、バックエンドで判定した値を渡す（§7） |
| `note`・`record`・商品ごとの`note` | 備考は最新・対応中の2件、返品対応・キャンセルに記録と商品メモを各1件。長文・改行を含む架空の例 | 実際の保存済み内容を条件に合わせて表示する。保存処理をこの画面へ追加しない |
| `$purchaseCount`・`$purchaseAmount`・購入状況の固定`time` | 通常3回・¥42,400（キャンセル・返品の2件を除いた値）、singleは1回・¥12,800、最終購入日2026-10-21／2026/10/21 | 実際の集計・最終購入日へ置き換える。購入回数の単位は未確定のため、固定例から決めない |
| singleの`$orders = [$orders[0]]` | 1件だけに絞る制作分岐 | 実際の注文件数で表示する。履歴を5件／1件に制限する仕様ではない |
| `$staticExport`・`$pageExtension`・`$currentPage = 'customers'`・戻る／再読み込みリンク | PHP／HTMLの切り替え、`customers.php`・`customer-detail.php?id={顧客ID}`の仮URL、現在地 | 本番ルート・現在地判定へ置き換え、詳細でもサイドバーの「顧客一覧」に`aria-current="page"`を付ける |

`$hasCancelRecord`は表示条件の例（§4）。`cancelled`／`return`をDBの正式な保存値と決めず、保存値との対応をバックエンドで決める。PHPコメントは、顧客テーブル方式と本番URLの形をバックエンドで決めて知らせてもらう方針（§7、QA No.11、2026-10-23）と、キャンセル・返品を除外する確定条件（§3）に合わせた。購入回数の単位は未確定のままとする。

## 2. 画面の構成

共通ヘッダー・サイドバーの中の`main.admin-main.customer-detail-page`に、次の順で置く。入力フォーム・保存バーはない。

| 順 | 領域 | 主なclass・idと構造 |
|---|---|---|
| 1 | ページ見出し | `.page-heading`に`h1`「顧客詳細」と`a.back-link`「顧客一覧へ戻る」 |
| 2 | 上の箱 | `section.form-box.customer-profile[aria-labelledby="customer-profile-heading"]`。氏名の`h2#customer-profile-heading`→`p.customer-meta`→`.profile-contact`の3つのp→`dl.purchase-summary` |
| 3 | 履歴全体 | `section.customer-history[aria-labelledby="customer-history-heading"]`と`h2#customer-history-heading`「注文履歴」 |
| 4 | 注文ごとの箱 | `section.form-box.history-order[aria-labelledby="history-{注文番号}"]`。`.history-heading`に`h3#history-{注文番号}`と`dl.order-status`（`dt.sr-only`は「発送状況」）。概要は`dl.order-summary` |
| 5 | 商品明細・送料 | `table.item-table[aria-label="注文{注文番号}の商品明細と送料"]`。`colgroup`・thead・tbody・tfoot。列見出しは`th scope="col"` |
| 6 | 届け先 | 同じ場合は`p.address-group.same-destination`。違う場合は`section.address-group[aria-labelledby="destination-{注文番号}"]`、`h4#destination-{注文番号}`と`.contact-lines`の3つのp |
| 7 | 備考 | `section.customer-note[aria-labelledby="customer-note-{注文番号}"]`、対応するh4とp。ある注文だけ |
| 8 | キャンセル・返品の記録 | `section.cancel-record[aria-labelledby="cancel-record-{注文番号}"]`、対応するh4、`p.record-label`、`p.record-reason`。条件に合う注文だけ |

履歴内のidは注文ごとに一意とし、`aria-labelledby`と見出しのidをそろえる。商品メモの`.item-note`は末尾の独立した節ではなく、商品明細の`.item-name`セル内に置く（§4）。

## 3. 基本情報と購入状況の表示

- 上の白い箱は、氏名をh2、その下に「顧客ID {ID}」の`p.customer-meta`、続けて`.profile-contact`にメールアドレス・電話番号・「〒郵便番号 住所」の順でラベルなしの3段落を置く。「基本情報（最新）」の見出し・補助文・各項目ラベルは出さない
- 表示元は、Clientが閲覧できる所属パートナーの店舗の注文のうち、最新の注文で確定した注文者情報。ほかのパートナーの注文や会員アカウントの現在値へ置き換えない。最新の注文がキャンセル・返品の場合の採用は未確定
- 氏名・住所・メールは省略せず折り返す。注文履歴の届け先とは別に扱う。住所項目の採否・IDの細かい配置は画面資料の案を維持する
- 同じ箱の下部の`dl.purchase-summary`で、`dt`と`dd`を対応させる。購入回数「○回」、購入金額の合計は`¥`＋3桁区切り、見出しに`span.tax-note`「（税込）」、最終購入日は日付だけの`time[datetime="YYYY-MM-DD"]`・表示`YYYY/MM/DD`。値はラベルと同じ左端・`tabular-nums`。集計表示・配置自体は画面資料の案
- 履歴・購入状況ともClientの閲覧範囲に限定する。購入回数・購入金額の合計にはキャンセル・返品の注文を含めない（2026-10-22、Kenの決定）。注文履歴ではキャンセル・返品の注文・記録も表示するため、履歴件数をそのまま購入回数にしない。購入回数を店舗ごとの注文で数えるか購入単位で数えるかは未確定

## 4. 注文履歴の表示

注文ごとの順番は、概要 → 商品明細・送料 → 届け先 → 備考（お客様より）→ キャンセル・返品の記録。注文を新しい順に並べるのは案で、現行は常時展開する表示例。

| 対象 | 出力・条件 |
|---|---|
| 注文番号・発送状況 | h3は「#{注文番号}」。発送状況は`.shipping-status`と`status-new`（新規受付）・`status-processing`（対応中）・`status-shipped`（発送済）・`status-return`（返品対応）・`status-cancelled`（キャンセル）。表示専用spanで受注一覧と同じ状態色。ボタン・選択肢・`data-selectbox`等は付けない。番号・状況の表示自体は既存の案 |
| 概要 | `.order-summary`に注文日時・店舗・合計金額（税込）。日時は`time`の`datetime`と`YYYY/MM/DD HH:MM`を対応させる。`.order-amount`の合計金額は`¥`＋3桁区切り、左揃え・`tabular-nums`、見出しに`.tax-note` |
| 商品明細 | 商品名・個数・小計（税込）の3列。`td.item-name`はリンクにせず省略なしで折り返す。`.quantity`・`.subtotal`はcol・th・tdのclassを維持し、数値は右揃え・`tabular-nums`。小計は`¥`＋3桁区切り |
| 送料 | tfootで`th scope="row" colspan="2"`と金額のtd。送料は`¥`＋3桁区切り、右揃え・`tabular-nums`。0円は制作例の「送料（送料無料）」・`¥0`。注文合計は概要だけに出し、明細下で重複しない |
| 届け先が注文者と同じ | `.address-group.same-destination`の「届け先：注文者と同じ」1行だけ。氏名・住所・電話を重複表示しない |
| 届け先が異なる | h4「届け先」の下の`.contact-lines`に、氏名／〒郵便番号 住所／電話番号の3段落。項目ラベル・`dl`・`address`は置かない。すべての幅で明細の下に全幅で表示 |
| 備考 | 空白を除いて内容がある場合だけ`.customer-note`。h4「備考（お客様より）」と本文を表示 |
| 記録 | 発送状況がキャンセル・返品対応で、空白を除いて記録がある注文だけ`.cancel-record`。h4「キャンセル・返品の記録」、`.record-label`「理由・対応メモ」、本文`.record-reason` |
| 商品メモ | 記録を表示する注文で、その商品にメモがある場合だけ`p.item-note`を商品名の下へ出す。先頭の`span.sr-only`「商品ごとのメモ：」を維持し、本文を補助色で区別する |

備考・記録がなければ、対応する見出し・空欄・「なし」は出さない。発送状況のclassは表示用で、DBの保存値との対応・キャンセル／返品の判定値は一覧の申し送り§9の関連項目を参照する。

注文時点の注文者情報はDBに残す前提だが、注文履歴には表示しない。受注一覧へのリンク・入稿画像・編集フォーム・保存操作は置かない。

氏名・店舗名・商品名・連絡先・備考・記録・メモをエスケープする。注文番号を含むid・aria属性やURLも適切に生成・エスケープし、固定の安全なダミー値で行っている直接出力を実データへそのまま転用しない。備考・理由／対応メモ・商品メモは現行の`white-space: pre-wrap`・`overflow-wrap: anywhere`で改行と全文を維持する。届け先・備考・記録は`fz(14)`、商品メモは商品名セルの文字サイズを継承する。

## 5. 状態ごとの出し分けと制作確認用URL

以下は現行の表示案。全状態でページ見出し・見出し横の一覧へ戻るリンク・サイドバー現在地を残す。

| 状態 | 制作確認用URL | 表示 |
|---|---|---|
| 通常 | `https://shop-manage-2611.ddev.site/shop-partner/customer-detail.php?id=1042` | 顧客ID1042・基本情報・購入状況・注文5件。備考あり／なし・記録あり／なし・同じ／異なる届け先を含む |
| 購入1回 | `https://shop-manage-2611.ddev.site/shop-partner/customer-detail.php?id=87&preview=single` | 顧客ID87・購入1回・¥12,800・最新の注文1件。同じ届け先と長い備考を表示 |
| 見つからない | `https://shop-manage-2611.ddev.site/shop-partner/customer-detail.php?preview=not-found` | `.list-empty.customer-not-found .message`「顧客が見つかりませんでした」、`.empty-actions .show-all`「顧客一覧へ戻る」。基本情報・ID・購入状況・履歴なし |
| 取得失敗 | `https://shop-manage-2611.ddev.site/shop-partner/customer-detail.php?preview=error` | `.list-empty.list-error[role="alert"]`、`.message`「顧客詳細を取得できませんでした」、`.error-help`「時間をおいて、もう一度読み込んでください。」、`.empty-actions .show-all`「再読み込み」。基本情報・ID・購入状況・履歴なし |

見つからない表示は存在しないIDと権限外IDで共通にし、権限外の顧客の存在を区別させない案。実際の識別・権限判定とHTTP応答はバックエンドで決めてほしい（§7）。制作PHPはIDの存在・所属・権限を判定せず、previewで表示するだけ。通常の詳細に注文0件は想定しない。

取得失敗は見つからない状態と区別する。再読み込みはpreviewを外し、同じ実際の顧客詳細URLへ向ける。戻るリンクは本番の顧客一覧URLへ置き換える。

## 6. 読み込みと共通部分

CSSは`common.css`→`customer-detail.css`、body末尾のJSは`common.js`だけ。詳細固有JS・受注一覧の`order.js`は読み込まない。共通head・サイドバー・キャッシュ対策は[共通申し送り§2](HANDOFF_CLIENT_COMMON.md#2-headの読み込みと初期化)・[§3](HANDOFF_CLIENT_COMMON.md#3-共通サイドバーの初期化と保存)・[§8](HANDOFF_CLIENT_COMMON.md#8-cssjsの読み込み順とキャッシュ対策)を参照。

`.form-box`等の画面固有の指定は`customer-detail.scss`にある。受注一覧の表示・色を参考にしていても、展開操作・ステータス保存の仕組みを持ち込まない。faviconの共通化と箱の寸法等の調整案は画面資料に残る。

## 7. バックエンドで決めてほしいこと

[一覧の申し送り§9](HANDOFF_CLIENT_CUSTOMER_LIST.md#9-バックエンドで決めてほしいこと)を参照。QA No.11は回答を待たずにバックエンドで決めて実装し、決めた内容をフロントへ知らせてもらう（2026-10-23、Kenの決定）。フロントがSPEC・画面資料・両申し送りへ反映する。表は一覧側にまとめ、ここには詳細に特に関係する点だけを示す。

- 顧客ID・詳細URLの形：メールアドレスをURLへ出さず、一覧から対象顧客を識別する。顧客テーブル方式は希望案のままで、制作上の候補ID処理を採用しない
- 閲覧範囲・見つからない時の判定とHTTP応答：Clientの範囲だけで最新情報・履歴・集計を取得し、未存在IDと権限外IDで同じ表示にする案に沿って決めてほしい
- 届け先の判定：購入時の選択を注文に記録する方法を希望し、なければその注文時点の注文者情報と比較する。最新の基本情報とは比較しない。判定済みの値で表示を分ける

## 8. 未確定のまま残っている項目

業務条件は[SPECの顧客管理の未確定事項](../SPEC.md#顧客管理の未確定事項)、画面側は[詳細資料の未確定事項](../design/CLIENT_CUSTOMER_DETAIL.md#未確定事項)を参照。§7の決定依頼と、残る画面・業務仕様の論点を分ける。

- 購入回数を店舗ごとの注文で数えるか購入単位で数えるか、最新の注文がキャンセル・返品だった場合の基本情報
- メールアドレス変更時の同一人物の扱い、フリガナ等の注文者情報の項目詳細
- 詳細内に店舗での絞り込みを置くか
- 注文履歴を常時展開・折りたたみ・ページ送りのどれにするか、多数の履歴の扱い。新しい順という案の採否
- 基本情報の住所項目の採否、購入状況の表示・同じ箱の下部への配置、顧客IDの細かい配置
- 注文番号・発送状況の表示の案、送料無料の文言。発送状況の正式な呼称・選択肢はSPECの未確定事項も参照する
- 一覧のID検索の採否（詳細内に検索欄を追加する意味ではない）
- 顧客単位の社内用メモを将来追加するか。今回は置かない

見つからない時の共通表示は現状の画面案のままで、実際の判定・HTTP応答は§7の決定依頼に含める。購入回数・金額からキャンセル・返品を除外する点は確定済みで、ここには残さない。
