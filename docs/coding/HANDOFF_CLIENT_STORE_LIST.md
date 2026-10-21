# Client店舗一覧 バックエンドへの申し送り

- 作成日：2026-10-21
- 対象：`shop-partner/stores.php`（制作用PHP）、`shop-partner/assets/scss/stores.scss`、生成CSS
- 作成：フロント（Ken・Codex）

共通の内容は[HANDOFF_CLIENT_COMMON.md](HANDOFF_CLIENT_COMMON.md)を参照。ここには店舗一覧固有の内容だけを記載する。仕様の正は[SPECの店舗と送料](../SPEC.md#店舗と送料の確定事項)と[店舗一覧資料](../design/CLIENT_STORE_LIST.md)。食い違う場合は最新の仕様資料を優先し、フロントへ知らせてほしい。

制作状況は「完成（引き渡し前）」。申し送りの整備まで完了し、実データ取得・Blade組み込みはバックエンドで行う。

## 1. 制作用PHPのうち、Bladeへ移さないもの

共通レイアウト・静的出力の扱いは[共通申し送り§1](HANDOFF_CLIENT_COMMON.md#1-共通レイアウトと制作専用の仕組み)を参照。

| 対象 | 内容 | Bladeでの扱い |
|---|---|---|
| `$previewState`・`$isEmptyState`・`$isErrorState` | `preview=empty`／`error` | 実際の取得結果・取得失敗で分岐する。previewを本番の状態判定に使わない |
| 表示例の3店舗 | ID 1〜3・店舗名・運送会社・送料方式・商品数・日時 | 所属パートナーの実データへ置き換える |
| `$staticExport`・`$pageExtension` | PHP／HTMLリンク切り替え | 本番ルートへ置き換える |
| 新規リンクの`?preview=new` | 制作上の新規表示 | 本番の新規フォームURLにする |

コード内に残る「店舗フォームは未作成」というコメントは旧制作段階の記述。フォームは制作済みで、コメントを本番仕様として移さない。今回はPHPを変更していない。

## 2. 店舗テーブルの構造と書式

`.stores-table`は`aria-labelledby="store-list-heading"`でページ見出しに関連付け、列見出しは`th scope="col"`。並び順・集計・更新契機の正は[SPEC](../SPEC.md#一覧の並び順集計更新日時)。ID昇順で固定し、検索・詳細検索・並び替え・ページ送りは設けない。

| 列 | class | 値・表示 |
|---|---|---|
| ID | `product-id` | 店舗ID。商品一覧と同じクラスを維持。幅6.4rem・右揃え・tabular-nums・折り返しなし |
| 店舗名 | `store-name` | 登録名。残り幅を使い、省略・行数制限なしで折り返す |
| 運送会社 | `store-carrier` | 選択中の会社の名称 |
| 送料 | `store-shipping` | サイズ別オンなら「サイズ別」、オフなら「サイズ共通」。「一律」は使わない |
| 商品数 | `store-products` | 非公開を含む、その店舗の全商品数。右揃え。0件は`0` |
| 更新日時 | `store-updated` | 店舗情報／送料の保存時に更新した日時。商品の変更では更新しない。`time datetime`に機械可読な日時、内側のspanで`YYYY/MM/DD`と`HH:mm`の2行表示 |
| 編集 | `store-edit` | `.edit-link`の鉛筆アイコン。行全体はリンクにしない |

表の最小幅は設けず、`.stores-scroll`を独立したスクロール領域・ランドマークにはしない。店舗固有の幅・折り返し等の制作案は[画面資料](../design/CLIENT_STORE_LIST.md#見た目の案kenが画面で調整決定)を参照。

## 3. 状態ごとの出し分け

基本形は[共通申し送り§6](HANDOFF_CLIENT_COMMON.md#6-一覧の表示状態)を参照。

| 状態 | 出力 |
|---|---|
| 通常 | 7列の表。表示例の3件に件数を固定しない |
| 店舗0件 | 表を出さず、`.list-empty`に「店舗がまだ登録されていません」、`.empty-actions`に「店舗を追加」 |
| 取得失敗 | 表を出さず、`.list-empty.list-error`に`role="alert"`。「店舗一覧を取得できませんでした」「時間をおいて、もう一度読み込んでください。」と「再読み込み」リンク。0件の表示に置き換えない |

再読み込みは本番の一覧URLへ向ける。制作側の`preview=error`は外す。

## 4. 追加・編集リンクと現在地

- 見出し右と空状態の「店舗を追加」は、制作用では`store-form.php?preview=new`。本番では新規フォームURLへ置き換える。834px幅以下では見出し下に回る。`a`要素・Lucideのplusアイコンを維持する
- 編集は制作用の`store-form.php?id={ID}`から、本番の対象店舗の編集URLへ置き換える。`aria-label`と`title`はともに「{店舗名}を編集」。表示名・属性値を適切にエスケープする
- サイドバーは「店舗」1項目で一覧URLへ向ける。制作側は一覧・新規／編集フォームのすべてで`$currentPage = 'stores'`。本番も両画面でこのリンクに`aria-current="page"`を付ける。開閉等の基本形は[共通申し送り§3](HANDOFF_CLIENT_COMMON.md#3-共通サイドバーの初期化と保存)を参照
- 制作用フォームは`id`で実データを取得しない。本番の新規／編集の出し分けは[店舗フォーム申し送り](HANDOFF_CLIENT_STORE_FORM.md#2-新規編集と保存後の表示)を参照

## 5. JSと読み込み

店舗一覧固有のJSはない。共通のhead・読み込み順・キャッシュ対策は[共通申し送り§2](HANDOFF_CLIENT_COMMON.md#2-headの読み込みと初期化)・[§8](HANDOFF_CLIENT_COMMON.md#8-cssjsの読み込み順とキャッシュ対策)に従い、ページ固有CSSは`stores.css`とする。

表の基本形・ID列・数値の桁揃え・日時2行・編集リンクは`products.scss`から`stores.scss`へ転記している。共通化候補だが、この引き渡しで共通Sassへの移動は行っていない。

## 6. 未確定のまま残っている項目

業務条件は[SPECの店舗と送料の未確定事項](../SPEC.md#店舗と送料の未確定事項)を参照。特に購入画面への店舗名表示、削除・停止時の商品の扱い、Masterでの店舗画面の扱いは未確定。初期画面へ削除・停止操作を追加しない。

[QA No.3](../QA_BACKEND.md#no3-購入側注文送料)で購入処理の担当・料金保持は確認済みだが、購入画面への店舗名表示は引き続きフロントで決める。[QA No.5](../QA_BACKEND.md#no5-masterclientとログイン)のMaster／Clientの管理対象範囲の決定を、Master店舗画面の詳細や削除仕様の確定と混同しない。列幅等の制作案は画面資料に残す。
