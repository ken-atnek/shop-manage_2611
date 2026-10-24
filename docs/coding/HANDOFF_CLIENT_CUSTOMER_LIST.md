# Client顧客一覧 バックエンドへの申し送り

- 作成日：2026-10-23
- 対象：`shop-partner/customers.php`（制作用PHP）、`shop-partner/assets/scss/customers.scss`、生成CSS（`shop-partner/assets/css/customers.css`。`npm run sass`で作る。Gitでは追跡しない）
- 作成：フロント（Ken・Codex）

共通の内容は[HANDOFF_CLIENT_COMMON.md](HANDOFF_CLIENT_COMMON.md)を参照。ここには顧客一覧固有の内容だけを記載する。仕様の正は[SPECの顧客管理の確定事項](../SPEC.md#顧客管理の確定事項)と[顧客一覧の画面資料](../design/CLIENT_CUSTOMER_LIST.md)。食い違う場合は仕様資料を優先し、フロントへ知らせてほしい。詳細固有の内容は[顧客詳細の申し送り](HANDOFF_CLIENT_CUSTOMER_DETAIL.md)を参照する。

## 1. 制作用PHPのうち、Bladeへ移さないもの

共通レイアウト・静的出力の扱いは[共通申し送り§1](HANDOFF_CLIENT_COMMON.md#1-共通レイアウトと制作専用の仕組み)を参照。

| 対象 | 内容 | Bladeでの扱い |
|---|---|---|
| `$previewState`（`?preview=`）と`$isEmptyState`・`$isNoResultsState`・`$isDateErrorState`・`$isErrorState`・`$isPagerMiddleState` | `empty`・`no-results`・`date-error`・`error`・`pager-middle`の制作確認用分岐。静的出力時はpreviewなし | 移さず、実データ・検索結果・検証結果・取得結果で分岐する |
| `$listCount`・`$filterNote` | 986件／空・検索結果0件では0件。「すべてを表示」／`no-results`だけ「検索結果」 | 実際の件数・適用条件から生成する |
| `$customers`とループ内の`$customer` | 架空の6件。IDは1042・87・615・2308・319・1705。氏名・`example.com`メール・000始まりの電話番号も表示例 | 閲覧可能な実データのループへ置き換える。電話番号は配列にあるが表には出さない |
| 各行の`count`・`amount`・`date`・`dateLabel` | 順に3回／¥42,400／2026-10-21、1回／¥12,800／10-21、128回／¥1,234,567／10-19、8回／¥12,800／10-18、3回／¥5,600／10-17、16回／¥204,000／10-16（すべて2026年）。表示日付は`YYYY/MM/DD` | 固定文字列を移さず、実際の集計値と最終購入日から書式を生成する |
| 日付エラーの固定入力値 | 開始日2026-10-20・終了日2026-10-10 | 実際の入力値を復元する |
| ページ数・番号・`?page=N` | 986件・1ページ50件・全20ページ。通常1ページ目、途中10ページ目でも同じ6行 | 実際の件数・現在ページ・条件保持URLへ置き換える。6行に制限する仕様ではない |
| `$staticExport`・`$pageExtension`・`$detailPath`・`$currentPage = 'customers'` | PHP／HTMLの拡張子切り替え、`customer-detail.php?id={顧客ID}`の仮リンク、現在地 | 本番ルート・現在地判定に置き換え、「顧客一覧」の`aria-current="page"`を維持する |
| フォームの`action`、全件表示・再読み込みの`customers.php`、検索結果0件の`href="#"` | 制作上の送信・遷移先。検索項目のnameも制作上の仮名 | 実際のルーティング・検索契約へ置き換える。条件解除は共通申し送り§6を参照 |

`$emailParts`は`@`で最大2分割する表示処理、`$detailLabel`は「{氏名}さんの詳細」の生成処理。変数名を引き継ぐ必要はないが、§3のエスケープ・折り返し・読み上げ名を維持する。

検索・日付判定・集計・IDと実データの対応・実際のページ切り替えは未実装。PHPコメントは、§9のとおり顧客テーブル方式と本番URLの形をバックエンドで決めて知らせてもらう現行方針に合わせた（QA No.11、2026-10-23）。

## 2. 画面の構成

共通ヘッダー・サイドバーの中の`main.admin-main`に、次の順で置く。

| 順 | 領域 | 主なclass・idと構造 |
|---|---|---|
| 1 | ページ見出し | `.page-heading`内の`h1`「顧客一覧」 |
| 2 | 検索 | `form.search-box`（`method="get" aria-label="顧客の検索"`）。`.filter-bar`に`label.sr-only`・`input#customer-keyword[type="search"]`・検索ボタン・`button.detail-toggle[type="button"]` |
| 3 | 詳細検索 | `.detail-toggle[aria-controls="customer-search-panel"]`と`.search-panel#customer-search-panel`。内部は`.search-panel-inner`→`.search-fields`→`fieldset.form-field.last-purchase-field`・`legend.form-label`「最終購入日」。`.date-range`に両日付入力（`input.form-input[type="date"]`）、区切りは`aria-hidden="true"` |
| 4 | 検索操作・エラー | `.search-actions`に`button.clear-conditions[type="button"]`と「この条件で検索」のsubmit。エラーはfieldset内の`p.form-error#customer-date-error` |
| 5 | 一覧見出し | `.list-heading[aria-live="polite"][aria-atomic="true"]`に`h2#customer-list-heading`・`.count`・`.filter-note` |
| 6 | 一覧 | `.customers-scroll`内の`table.customers-table[aria-labelledby="customer-list-heading"]`。1顧客＝tbodyの1行。各列は§3 |
| 7 | ページ送り | `nav.pagination[aria-label="顧客一覧のページ"]`・`ul.pagination-list[role="list"]`・`.page-status` |

labelの`for`とinputの`id`、詳細検索の`aria-controls`とパネルidを対応させる。開閉・`aria-expanded`・`inert`・条件クリアは[共通申し送り§4](HANDOFF_CLIENT_COMMON.md#4-commonjsの詳細検索とアコーディオン)。`.customers-scroll`を独立したスクロール領域にせず、共通申し送り§1に従う。

## 3. 表示の決まり

7列の順序を維持し、各列の`th`は`scope="col"`とする。氏名は、そのClientが閲覧できる注文のうち最新の注文で確定した注文者情報を使う。電話番号は列に出さず、キーワード検索と顧客詳細で扱う。

| 列 | class | 書式・内容 |
|---|---|---|
| ID | `.customer-id` | 顧客ID。右揃え・`tabular-nums`。識別方式は§9 |
| 氏名 | `.customer-name` | 通常文字。省略せず折り返し、氏名自体をリンクにしない |
| メールアドレス | `.customer-email`・`.customer-email-value`・`.email-part` | 注文者のメール。`@`直前の`wbr`・省略は下記 |
| 購入回数 | `.customer-count` | 「○回」。右揃え・`tabular-nums`・値の途中で折り返さない |
| 購入金額の合計（税込） | `.customer-amount`・見出し内の`.tax-note` | `¥`＋3桁区切り。右揃え・`tabular-nums`・値の途中で折り返さない。「（税込）」はspanでまとまりにし、その中では改行しない |
| 最終購入日 | `.customer-purchased` | 日付だけの`time`。`datetime="YYYY-MM-DD"`、表示`YYYY/MM/DD`。時刻は出さない。右揃え・`tabular-nums` |
| 詳細 | `.customer-detail`・`a.detail-link` | Lucide `eye`。`aria-label`と`title`はともに「{氏名}さんの詳細」。SVGは`aria-hidden="true" focusable="false"`、20px角。40px角の操作領域・フォーカス枠を維持 |

メールは`@`で最大2分割し、エスケープした各部分を`.email-part`へ出力する。2つの部分の間に`wbr`を置き、後半は`@`を含める。各部分の途中では改行せず、列幅を超える部分だけ`text-overflow: ellipsis`で省略する。`.customer-email-value`の`title`にエスケープした全文を残す。`@`がなければ1部分だけ出す現行の構造を基準にする。

氏名・メール本文・title・詳細リンクの属性値等を適切にエスケープする。仮の詳細URLは本番ルートへ置き換え、URLやパラメータにメールアドレスを出さない。顧客テーブル方式・顧客ID・具体的なURLの形は§9で決めてほしい。

## 4. 購入回数・購入金額の合計

- Clientが閲覧できる所属パートナーの店舗の注文だけを対象とし、ほかのパートナーの注文を表示・集計に使わない
- 購入回数・購入金額の合計にキャンセル・返品の注文を含めない（2026-10-22、Kenの決定）。[SPECの集計の確定事項](../SPEC.md#集計の確定事項)とそろえる。判定値は§9の関連項目を参照
- 購入回数を店舗ごとに分かれた注文の数で数えるか、購入の単位で数えるかは未確定。同じパートナーの複数店舗で一度に購入する場合を含め、固定例から決めない
- 最新の注文がキャンセル・返品の場合、その注文者情報を基本情報に使うかは未確定。回数・金額から除外する決定とは別の論点として残す

## 5. 検索と入力エラー

| 条件 | 制作上のname | id・label |
|---|---|---|
| キーワード | `keyword` | `#customer-keyword`、読み上げラベル・placeholder「氏名・メールアドレス・電話番号で検索」 |
| 最終購入日（開始日） | `purchased_from` | `#customer-purchased-from`、`label.sr-only`「開始日」 |
| 最終購入日（終了日） | `purchased_to` | `#customer-purchased-to`、`label.sr-only`「終了日」 |

nameは表示例で、正式名はバックエンドで決めてよい（変更した場合はフロントへ知らせてほしい）。検索対象の氏名・メール・電話は確定。一致方法、日付の境界・片側指定・タイムゾーン、ID検索の採否は未確定。商品一覧の確定した検索細則をそのまま顧客検索の仕様にしない。

日付エラーは[共通申し送り§5](HANDOFF_CLIENT_COMMON.md#5-入力エラー)の構造に従う。両入力に`aria-invalid="true" aria-describedby="customer-date-error"`を付け、1つの`p#customer-date-error`へ関連付ける。逆転文は「終了日は開始日以降の日付を指定してください」。エラー時はパネルを開き、`aria-expanded="true"`・`inert`なしで入力値を復元する。正常時はエラー用の属性・文を出さない。

顧客の日付fieldsetは`.last-purchase-field`。受注一覧・商品一覧用の`.date-field`・`.updated-field`ではないため、共通申し送り§5の終了日下への配置変更の対象と混同しない。制作側は逆転例だけで、日付形式エラー専用previewはない。

パネル内のクリアは日付欄だけを空にして先頭の`#customer-purchased-from`へフォーカスし、パネル外のキーワードを維持する。検索結果0件の条件解除リンクとは分ける（共通申し送り§4・§6）。

## 6. 並び順とページ送り

最終購入日の新しい順は案であり、同日の順序も未確定。並び替えの操作は制作画面に置いていない。

ページ送りは1ページ50件とし、構造・表示規則・条件保持は[共通申し送り§7](HANDOFF_CLIENT_COMMON.md#7-ページ送り)を参照。`aria-current="page"`、番号の「ページ」、矢印の「前のページ／次のページ」、省略記号の`aria-hidden`を維持する。986件・全20ページ・1／10ページ目は表示例。1ページしかない場合の表示は未確定。

## 7. 状態ごとの出し分けと制作確認用URL

以下は現行の表示例。空・失敗部品の見た目は画面資料の案として残る。共通の扱いは[共通申し送り§6](HANDOFF_CLIENT_COMMON.md#6-一覧の表示状態)を参照。

| 状態 | 制作確認用URL | 表示 |
|---|---|---|
| 通常 | `https://shop-manage-2611.ddev.site/shop-partner/customers.php` | 検索・一覧見出し・7列の表・1ページ目のページ送り |
| 途中のページ | `https://shop-manage-2611.ddev.site/shop-partner/customers.php?preview=pager-middle` | 同じ6行、20ページ中10ページ目 |
| 購入者なし | `https://shop-manage-2611.ddev.site/shop-partner/customers.php?preview=empty` | 0件・「すべてを表示」、`.list-empty .message`に「購入者がまだいません」。表・ページ送り・空状態内のリンクなし |
| 検索結果0件 | `https://shop-manage-2611.ddev.site/shop-partner/customers.php?preview=no-results` | 0件・「検索結果」、「条件に一致する顧客はいません」。`.empty-actions`に`.show-all`の「すべての顧客を表示」と「条件をクリア」。表・ページ送りなし |
| 最終購入日の逆転 | `https://shop-manage-2611.ddev.site/shop-partner/customers.php?preview=date-error` | 詳細検索を開き、2つの入力から1つのエラー文を参照。制作例は表・ページ送りも表示 |
| 取得失敗 | `https://shop-manage-2611.ddev.site/shop-partner/customers.php?preview=error` | `.list-empty.list-error[role="alert"]`に「顧客一覧を取得できませんでした」、`.error-help`に「時間をおいて、もう一度読み込んでください。」、`.empty-actions .show-all`に「再読み込み」。検索は残し、一覧見出し・表・ページ送りなし |

取得失敗を0件に置き換えない。全件表示・再読み込みは本番の一覧URLへ向け、previewを持ち越さない。検索・ページ切り替えは制作PHPでは動作しない。

## 8. 読み込みと共通部分

CSSは`common.css`→`customers.css`、body末尾のJSは`common.js`だけ。ページ固有JSはない。共通head・サイドバー・キャッシュ対策は[共通申し送り§2](HANDOFF_CLIENT_COMMON.md#2-headの読み込みと初期化)・[§3](HANDOFF_CLIENT_COMMON.md#3-共通サイドバーの初期化と保存)・[§8](HANDOFF_CLIENT_COMMON.md#8-cssjsの読み込み順とキャッシュ対策)に従う。詳細でも「顧客一覧」を現在地にする。

一覧のheadには制作上の空favicon（`href="data:,"`）がある。詳細にはなく、faviconの共通化は今後の検討事項。表の列幅・最小幅等の調整案は画面資料に残す。

## 9. バックエンドで決めてほしいこと

`QA_BACKEND.md` No.11 は回答待ちにせず、バックエンドで決めて実装してよい（2026-10-23、Kenの決定）。決めた内容はフロントへ知らせてほしい。フロントがSPEC・画面資料・申し送りへ反映する。No.11の質問・フロントの案・回答欄は当時の記録として維持し、新しいQA項目は追加しない。下表は[No.11のフロントの案](../QA_BACKEND.md#no11-顧客管理のデータ閲覧範囲会員との関係)を前提とし、採用方式をここで確定しない。

| 決めてほしいこと | フロントの前提 |
|---|---|
| 注文時点の注文者情報の保存欄・保存方法 | 届け先とは別に、注文者の氏名・郵便番号・住所・電話番号・メールアドレスのスナップショットを注文に残す。現在のDB設計に保存欄が不足するというNo.11の前提で依頼する。詳細の履歴では表示しないが、最新情報の表示元・届け先の判定に必要 |
| 顧客のまとめ方、顧客テーブル方式の採否と顧客ID | 注文者メールアドレスを大文字・小文字を区別せずにまとめる。全パートナー共通の顧客テーブルを持ち、注文ごとにメールで探し、なければ作って注文に顧客IDを付ける方式はフロントの希望案。顧客テーブル方式そのものは未確定。一覧先頭と詳細の箱内には顧客IDを表示する |
| 詳細のURLの形 | メールアドレスをURL・パラメータに出さない。顧客IDで詳細URLを作ることを希望する。`customer-detail.php?id={顧客ID}`は仮の形 |
| 注文者メールアドレスの必須化 | 顧客をまとめる条件なので注文時の必須項目としたい。No.11では現在のDB設計の`contact_email`が任意という前提。必須化・保存の扱いを決めてほしい |
| 閲覧範囲の制御（Client／Master・パートナー絞り込み） | Clientは所属パートナーの店舗の注文だけで最新情報・履歴・購入回数・金額合計・最終購入日を表示・集計する。ほかのパートナーの注文を使わない。Masterは全パートナーの注文・顧客を対象とし、パートナーで絞り込めるようにする。サーバー側で範囲を制御する。詳細の未存在・権限外は同じ表示とする案で、判定・HTTP応答も決めてほしい |
| 会員登録・ゲスト購入の有無と会員アカウントとの関係 | 会員名簿ではなく、購入者の履歴を閲覧する画面。購入側の仕様を確認し、会員アカウントと注文時点の注文者情報の関連付けを決める。一覧に会員／ゲスト列は置かない |
| 届け先が注文者と同じかの判定方法 | 購入時の「注文者と同じ住所に届ける」の選択を注文に記録する方法を希望する。記録がなければ、その注文時点の注文者情報と届け先を比較する。比較項目・表記ゆれの扱いも決めてほしい。最新の基本情報とは比較しない。判定済み値を詳細へ渡し、同じなら1行、違うなら届け先の3段落を表示する |

関連して、[SPECの集計「バックエンドで決めてほしいこと」](../SPEC.md#バックエンドで決めてほしいこと)・[集計の申し送り§9](HANDOFF_CLIENT_SALES.md#9-バックエンドで決めてほしいこと)の`orders.purchase_group_key`の確定・必須化／Clientの範囲で同じ購入を判定する方法と、キャンセル・返品の判定値も参照してほしい。前者は顧客一覧の購入回数の単位に関わるが、回数を注文単位・購入単位のどちらにするかは引き続き未確定。判定値の決定によって、キャンセル・返品の注文を回数・金額から除外する確定条件を変えない。

## 10. 未確定のまま残っている項目

業務条件は[SPECの顧客管理の未確定事項](../SPEC.md#顧客管理の未確定事項)、画面側は[一覧資料の未確定事項](../design/CLIENT_CUSTOMER_LIST.md#未確定事項)を参照。§9のバックエンドへの決定依頼とは分け、残る仕様上の論点を記載する。

- 購入回数を店舗ごとの注文で数えるか、購入単位で数えるか
- 最新の注文がキャンセル・返品だった場合、その注文者情報を基本情報に使うか
- メールアドレスが変わった同じ人をまとめる仕組みを設けるか。現行のメール集約条件では別の顧客になる
- フリガナ等の注文者情報の項目詳細（§9の保存すべき5項目とは別）
- 一覧に店舗での絞り込みを置くか
- ID検索の採否、キーワードの一致方法、最終購入日期間の境界・片側指定・タイムゾーン
- 最終購入日の新しい順という案の採否と、同日の顧客の順序
- 検索結果0件の「条件をクリア」の遷移先・解除対象、1ページだけの場合のページ送り（共通申し送り§9）

列幅・状態部品等の見た目の案は画面資料に残す。未確定事項を固定の表示例や他画面の検索仕様から決めない。
