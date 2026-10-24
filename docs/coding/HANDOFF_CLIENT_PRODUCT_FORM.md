# Client商品詳細（登録・編集） バックエンドへの申し送り

- 作成日：2026-10-20
- 対象：`shop-partner/product-form.php`（制作用PHP）、`shop-partner/assets/scss/product-form.scss`、生成CSS（`npm run sass`で作る。Gitでは追跡しない）、`shop-partner/assets/js/product-form.js`
- 作成：フロント（Ken・Codex）

共通の内容は[HANDOFF_CLIENT_COMMON.md](HANDOFF_CLIENT_COMMON.md)を参照。ここには商品フォーム固有の内容だけを記載する。仕様の正は[仕様書](../SPEC.md)と[商品フォーム資料](../design/CLIENT_PRODUCT_FORM.md)で、内容が食い違う場合は最新の仕様資料を優先し、フロントへ知らせてほしい。

フロントの制作状況は「完成（引き渡し前。画像一時保存への移行は未対応）」。保存処理・サーバー検証・DB連携は実装していない。以下では、現在の制作実装と、バックエンド回答への対応と残る確認事項を分ける。

**呼び方の変更（2026-10-20）**：資料では「店舗」に統一する。商品フォームのラベル・選択肢・判定と注意の表示文言は「店舗」へ変更済み（2026-10-20）。以下の文言・JSONのサンプル名も現行画面と一致している。`store_id`・`#product-store`・`store-shipping-data`・JSONのキーなど、コードの名前は変更しない。

食品表示へ店舗情報を初期値として入れる案は取り下げ済み。パッケージの表示どおりに入力し、店舗の選び直しで本文を上書きしない（[SPEC](../SPEC.md#店舗と送料の確定事項)）。

## 1. 制作用PHPのうち、Bladeへ移さないもの

制作専用の仕組み・共通レイアウトの扱いは[共通申し送り§1](HANDOFF_CLIENT_COMMON.md#1-共通レイアウトと制作専用の仕組み)を参照。

| 対象 | 内容 | Bladeでの扱い |
|---|---|---|
| `$previewState`（`?preview=`）・各`$is…State` | 新規・食品・エラー・保存完了・注意等の10状態の表示確認 | preview・状態フラグを移さず、実際の新規／編集・入力値・検証結果・保存結果で分岐する |
| 商品・店舗・画像・日時等の表示例 | 商品ID986、既存画像ID101～103、店舗2件、サンプル画像等 | 実データに置き換える。現行の`?id=…`は商品を取得せず、同じ表示例を出す |
| `$sizeResultExample`・`$coolWarningExample`等 | 初期HTML用の固定表示例 | previewによる文言分岐を移さない。初期の実入力と設定に合う表示を出し、JSも読み込み時に同期する |
| `$staticExport`・`$pageExtension` | 制作確認用のPHP／HTMLリンク切り替え | 本番ルート・アセットURLへ置き換える |
| `includes/data_carriers.php` | 配列を返す制作用の運送会社規格 | ファイルを本番のデータソースとして移さない。規格の正はSPEC。本番は回答4で確認済みのDBテーブル＋Seederへ置き換える（初期Master編集なし） |
| サイドバー「商品登録」の`?preview=new` | 制作用の新規表示リンク | 実際の新規登録URLへ置き換える。商品一覧の編集リンク・保存バーの一覧リンクも本番ルートへ置き換える |

## 2. 新規／編集と保存後の表示

1つのフォームで新規／編集を出し分ける。見出し・現在地は現在の制作案で、業務上の正式な画面名との統一は未確定。

| 対象 | 新規 | 編集 |
|---|---|---|
| h1・titleの画面名 | 商品登録 | 商品編集 |
| サイドバーの現在地 | 商品登録（制作側の`$currentPage = 'product-new'`） | 商品一覧（`'products'`） |
| ID・登録日時・更新日時 | 未登録のため表示しない | 見出し下に読み取り専用で表示。日時は機械可読な`datetime`付きの`time`要素。入力・送信項目にしない |
| 各入力欄 | 商品名・説明・価格・梱包4項目等は空欄。画像0枚。店舗は未選択（1件なら自動選択する案は未確定） | 保存済みの値を表示。画像は保存順で出力する |
| 消費税率 | `10`（標準税率） | 保存済みの税率 |
| 公開状態 | `private`（非公開） | 保存済みの公開状態 |
| 配送温度帯 | `normal`（常温） | 保存済みの温度帯 |
| 食品・入稿 | 食品オフ・入稿なし | 保存済みのチェック状態。制作用の入稿は全状態で「なし」の表示例 |

- 新規保存後はIDが付いた編集画面へ移り、上部に「商品を登録しました」を`role="status"`で表示する
- 編集保存後は同じ編集画面で「保存しました」を`role="status"`で表示する。完了メッセージへフォーカスは移さない
- 公開状態を含め、ページ全体を保存時に反映する。即時保存・削除・複製・下書き専用操作は置かない。下書きは非公開保存で代用する

## 3. 送信内容と必須条件

formは`.product-form`、`method="post"`・`enctype="multipart/form-data"`・`novalidate`。現行の`action="#"`を保存ルートへ置き換える。以下のname・valueは現在のフロントとの接続契約で、正式名はバックエンドと確認する。変更する場合は§7のJS依存先も合わせて変更し、フロントへ知らせてほしい。

| 項目 | name | 値・条件 |
|---|---|---|
| 店舗 | `store_id` | 店舗ID。必須。selectのvalueとJSONのキーを一致させる |
| 商品名 | `product_name` | テキスト。必須 |
| 商品説明 | `description` | テキストエリア。必須の指定なし |
| 新しく追加する画像 | `images[]` | 複数ファイル。順番・削除との対応は§4 |
| 画像の表示順 | `image_order[]` | `existing:{ID}`／`new:{番号}`の配列 |
| 削除する既存画像 | `image_delete[]` | 既存画像のIDだけの配列 |
| 価格（税込） | `price` | 数値用テキスト。必須。単位「円（税込）」は送信値に含めない。税抜・税額・端数処理はバックエンドで行う |
| 消費税率 | `tax_rate` | ラジオ`8`／`10`。必須 |
| 在庫数 | `stock` | 数値用テキスト。在庫管理なしのときdisabled。必須・入力範囲は未確定 |
| 在庫を管理しない | `no_stock_management` | チェック時`1`。在庫数を持たず、いつでも販売できる商品用 |
| 配送温度帯 | `temperature` | ラジオ`normal`（常温）／`cold`（冷蔵）／`frozen`（冷凍）。必須 |
| 梱包時の縦・横・高さ | `packing[length]`・`packing[width]`・`packing[height]` | 各cm。必須条件・入力範囲・桁は未確定 |
| 梱包時の重さ | `packing[weight]` | kg。必須条件・入力範囲・桁は未確定 |
| 注文者からのデータ入稿 | `upload` | 「入稿あり」のチェック時`1`。ありの場合の必須／任意設定・注文者向け説明文欄は未実装・未確定 |
| この商品は食品です | `is_food` | チェック時`1`。食品fieldsetの外に置く |
| 食品表示 | `food_label` | テキストエリア。食品オンのとき必須で、本文が空でないことを検証。名称・期限等を個別に必須チェックする仕組みはない |
| アレルゲン | `allergens` | 食品表示とは別のテキストエリア。食品オン時に本文が空でないことが必須。含まない場合は「なし」と入力。詰め合わせごとの表記や製造ラインの注意書きもパッケージどおりに記入する |
| 公開状態 | `publish` | ラジオ`public`／`private`。保存と同時に反映する |

- 必須表示は文字「必須」。テキスト等の`aria-required="true"`と、ラジオのfieldset／legend内の必須表示を維持し、HTMLの`required`は付けない。ブラウザ標準の検証で止めず、サーバー側で検証する
- 未チェックのチェックボックスは送信されない。現在は未チェック用のhidden値`0`を置いていないため、バックエンドで有無を解釈する
- 食品オフでは#food-fieldsをhiddenだけにし、disabledは付けない。food_label・allergensはオフでも送信される。is_foodが未送信（オフ）のときも直前の編集値を保存して非表示にする。隠した欄はTab対象外。aria-requiredは維持し、必須のサーバー検証は食品オン時だけ行う
- 在庫管理なしでは`#product-stock`がdisabledとなり、`stock`は送信されない。画面内の値は保持する。一覧では「—」＋読み上げ用「在庫管理なし」とし、「在庫切れ」「残りわずか」の対象外。在庫検索での扱いだけ未確定
- 回答2のB方式（オフ直前の編集値も保存）を受け入れ、フロントは対応済み。独立したallergens欄はKenの2026-10-21決定で維持し、products.allergens本文の追加をバックエンドへ依頼中。回答2の統合案とは区別し、[SPECの確定事項](../SPEC.md#client商品詳細登録編集の確定事項)を正とし、経緯は[QA No.2のフロントの対応](../QA_BACKEND.md#no2-食品オフ保存の値)を参照する
- 判定サイズは表示用のテキストで、送信用のサイズ欄や送料入力欄はない。判定結果を確定した送料として受け取らない

アレルゲンは#allergensのtextareaとlabelのforを対応させ、aria-required="true"・aria-describedby="allergens-help"を維持する。補助文は「パッケージのアレルゲン表示どおりに入力してください。含まない場合は『なし』と入力してください。」。編集・エラー時は本文を保持する。最小3行・最大8行の共通textarea方式で、JSは#food-fieldsの開閉だけを制御する。

チェックボックス方式は[B案（購入画面で絞り込み・アイコン表示をする場合）](../design/CLIENT_PRODUCT_FORM.md#b案購入画面で絞り込みアイコン表示をする場合)として残す。現時点で購入画面の絞り込み・アイコン表示は予定しておらず、現行の送信は配列やその他欄ではなくallergens本文1つとする。B案の品目マスタ・排他条件・送信形式は再採用時だけ検討する。

## 4. 商品画像の送信と操作

2026-10-21に[QA No.1のフロントの対応](../QA_BACKEND.md#no1-商品画像)で一時保存方式を受け入れた。JPEG・PNG・WebP／1枚5MB／既存＋追加で最大10枚、0枚保存可・1枚目がメイン・縦横比制限なし。画像なしはバックエンドが用意する画像を使う。**画像のPHP・JSと以下の送信契約は旧実装のままで、次回変更する（未対応）**。採用済みの方式と現行コードを混同しない。

以下は現行の旧実装の説明。まだ選択時には送信せず保存時にファイルをまとめて送る。下記new:{番号}の契約は採用済みのtemp:{一時画像ID}へ次回移行し、本番へそのまま組み込まない。画像の申し送り全体の移行も次回に行う。

| 送信値 | 対応 |
|---|---|
| `images[]` | 保存時点で残っている新規ファイルだけ。既存画像は含めない |
| `image_order[]`の`existing:101` | ID101の既存画像。配列の位置が最終表示順 |
| `image_order[]`の`new:1` | `images[]`の1枚目（ファイル配列の添字0）。`new:2`は2枚目。新規画像の移動・削除後に1から採番し直し、ファイル順と同期する |
| `image_delete[]`の`102` | ID102の既存画像の削除指示。`existing:`は付けない。新規画像の削除はファイル配列から外すだけ |

例：`image_order[] = [existing:101, new:1, existing:103, new:2]`、`image_delete[] = [102]`なら、既存101→新規1枚目→既存103→新規2枚目の順。既存画像のIDは表示例に固定しない。

- 「前へ／後ろへ」で並べ替え、先頭だけメインバッジを出す。端のボタンのdisabled・画像番号・aria-label・titleはJSが更新する。移動後は押したボタンへ戻し、端で無効になった場合は反対側のボタンへフォーカスする
- 削除は保存まで確定しないため、確認ダイアログを出さない。次の画像の有効なボタン、なければファイル入力へフォーカスする
- 新規画像はファイル選択・ドロップで追加する。Object URLでプレビューし、削除時に解放する。DataTransferでファイル配列と表示順を同期する。画像以外は追加せず「画像ファイルを選択してください」と通知する。現行画像コードの形式・容量・枚数制限は未対応。採用済み条件は上記のとおり
- 操作結果は`#image-status`のpolite live領域で通知する。ドラッグによる並べ替えは実装していない。並べ替えはボタン操作、ドロップは追加用
- 入力エラー後に新しく選んだ画像をファイル入力へ復元することはできず、選び直しになる。一時保存への移行後は有効な一時画像を保持する。現行JSと送信契約の変更は次回

## 5. エラーと入力値の保持

基本のエラー属性は[共通申し送り§5](HANDOFF_CLIENT_COMMON.md#5-入力エラー)を参照。商品フォームは次を追加する。

- メタ情報とformの間に`#product-errors.error-summary`を出力する。`aria-labelledby="product-errors-heading"`・`tabindex="-1"`を維持する。見出し`#product-errors-heading`は「入力内容を確認してください（N件）」とし、実際のエラー件数を出す
- まとめの各リンクはエラーのある入力欄のidへ向ける。現行の3件は`#product-name`・`#product-price`・`#food-label`。件数・文言・リンクを検証結果に合わせて出力する。JSは初期表示時にまとめへフォーカスを移す
- 各欄に`.form-error`と固有のidを出力し、入力側に`aria-invalid="true"`・`aria-describedby`を付ける。補助文がある場合は補助文idも残す。例：食品表示は`food-label-help food-label-error`を参照する
- ラジオのエラー属性は各ラジオへ付ける。fieldset／legendでグループ名・必須を伝え、内側へ`role="radiogroup"`やグループ用のエラー属性を追加しない。税率のエラー時も既存の注意領域`tax-warning`への参照を残す
- ラジオ・チェックボックス単体に赤い四角の入力枠は付けない。税率はカードの赤枠と`.form-error`で伝える
- エラー時は新規／編集の文脈と入力値・チェック・店舗の選択を保持する。制作の`error`は新規入力途中の例であり、本番のすべてのエラーを新規扱いにしない
- 食品の欄にエラーがあれば食品オンで出力し、`#food-fields`のhiddenを外す（disabledは付けない）。JSもチェックに合わせて同期するため、チェックとfieldsetを一致させる
- ファイル以外の入力値と、既存画像の並び替え・削除指示も復元する。現行の画像コードでは新規画像は再選択が必要だが、一時保存への移行後は有効な一時画像を保持する（次回対応）。現在のerrorは「商品名」「価格」「食品表示」が空で、税率は10%を保持する例

## 6. 店舗の送料JSONと判定

`<script type="application/json" id="store-shipping-data">`をJSより前に置く。ルートは店舗IDをキーにしたオブジェクトで、`#product-store`のvalueと一致させる。[商品フォーム資料のJSON全形](../design/CLIENT_PRODUCT_FORM.md#制作2-aのjsと店舗json2026-10-20)も参照。以下は実装と同じ形の店舗2の例であり、値は本番データに置き換える。

```json
{
  "2": {
    "name": "熊本ギフト サンプル店舗",
    "carrierId": "yupack",
    "sizeBased": false,
    "usedSizes": [],
    "coolSurchargeSetBySize": {},
    "coolSurchargeSet": {"refrigerated": true, "frozen": false},
    "carrier": {
      "name": "ゆうパック",
      "sizes": [
        {"size": 60, "maxWeightKg": 25},
        {"size": 80, "maxWeightKg": 25},
        {"size": 100, "maxWeightKg": 25},
        {"size": 120, "maxWeightKg": 25},
        {"size": 140, "maxWeightKg": 25},
        {"size": 160, "maxWeightKg": 25},
        {"size": 170, "maxWeightKg": 25}
      ],
      "cool": {
        "refrigerated": {"maxTotalCm": 150, "maxWeightKg": 25, "maxLongestCm": 100},
        "frozen": null
      }
    }
  }
}
```

| キー | 意味・型 |
|---|---|
| 店舗ID | 文字列キー。selectに出す各店舗の設定を持つ |
| `name`・`carrierId` | 店舗名・運送会社ID。初期対象のIDは`yamato`／`sagawa`／`yupack`。既存定義の`other`は保持するが初期対象外 |
| `sizeBased` | boolean。サイズ別送料の有無 |
| `usedSizes` | 使用するサイズの数値配列。サイズ別ありで判定対象を絞る。サイズ別なしでは空配列とし、JSは運送会社の全サイズから取扱上限を確認する |
| `coolSurchargeSetBySize` | 案（バックエンドと確認）。サイズ→{refrigerated: boolean, frozen: boolean}。サイズ別ありで、選んだ温度帯の設定有無を見る |
| `coolSurchargeSet` | 案（バックエンドと確認）。サイズ別なしの{refrigerated: boolean, frozen: boolean}。冷蔵・冷凍を別々に判断する |
| `carrier.name` | 配送の先頭の読み取り専用表示と、注意文に使う運送会社名 |
| `carrier.sizes` | `{size, maxWeightKg}`の配列。sizeはサイズ番号かつ3辺合計の上限cm、maxWeightKgはそのサイズの重さ上限kg。値は数値型 |
| `carrier.cool.refrigerated`・`.frozen` | それぞれ冷蔵・冷凍の条件。非対応は`null`。対応する場合は数値の`maxTotalCm`・`maxWeightKg`と、条件がある場合の`maxLongestCm`を持つ |

- 温度帯ごとの追加料金の空欄はfalse、0円を含む設定ありはtrue。金額・47都道府県の送料表・送料無料の閾値は、この表示用JSONには渡さない
- 店舗設定の`carrierId`で規格を取得し、`carrier`へ結合する。制作用はdata_carriers.phpを参照するが、本番は回答4で確認済みのDBテーブル＋Seederへ置き換える（初期Master編集なし）。標準3社の重さ上限等を店舗側に複製・編集させない。規格の正・出典は[SPECの運送会社の規格](../SPEC.md#運送会社の規格正の資料)
- 「その他」は初期対象外・後回し。既存定義は今回変更しない。将来対応時の独自サイズ・重さ・温度帯条件の設定・結合方法は未確定で、制作用データの空のsizesやnullのcoolを確定した独自規格として出さない
- JSONは数値・boolean・null・配列・オブジェクトを区別し、script要素を壊さない方法で出力する。現在のPHPは`JSON_HEX_TAG`等でエスケープしている。文字列の数値・booleanへ変えない

### 梱包サイズとクール便の注意

- 縦・横・高さ・重さの入力後300ms程度で更新する。全角数字を半角として扱うが、入力値自体は書き換えない。空欄・非数値なら「縦・横・高さ・重さを入力すると判定します」。店舗未選択なら「店舗を選ぶと判定します」
- サイズ別ありでは、使用サイズのうち3辺合計と規格の重さ上限の両方に収まる最小サイズを表示する。例：「判定：80サイズ（3辺合計75cm・1.2kg）」。収まらなければ「対応するサイズがありません（3辺合計〇cm・〇kg）」
- サイズ別なしでは「この店舗はサイズ別の送料を使いません（3辺合計〇cm・〇kg）」。運送会社の上限を超えると「{運送会社}の上限（3辺合計{○}cm・{○}kg）を超えています（3辺合計〇cm・〇kg）」
- 冷蔵・冷凍の注意は、運送会社の温度帯非対応→実寸・重さのクール上限→最長辺の条件→店舗の追加料金未設定の順。通常送料の判定サイズ番号だけでクール上限を判断しない
- 文言は「この店舗の運送会社（ゆうパック）は冷凍に対応していません」「ヤマト運輸のクール便は3辺合計120cm・15kgまでです」「ゆうパックのクール便は最長辺100cmまでです」「この店舗では{サイズ}サイズの冷凍便に対応していません」（サイズ別なしは「この店舗は冷凍便に対応していません」）。冷蔵は「冷蔵便」とする。温度帯そのものの非対応は寸法未入力でも表示し、寸法・重量・料金の注意は4入力が有効になってから判断する
- 判定と注意はpolite live領域で通知し、同じ文言の再設定を避ける。初期表示・店舗変更・温度帯変更時も現在値で更新する。食品や配送の入力値は店舗変更で消さない
- **食品と10%税率の注意はJSONを使わない**。食品チェックがオンかつ税率10%なら「食品は通常8%です。税率を確認してください」を表示する。チェック・税率変更・初期表示で同期し、自動で税率を変えない
- 現在のJSの判定・注意はフロントの表示だけで、保存は止めない。購入側で注文分割・送料計算を行う前提で、回答3で今回のLaravelのサーバー側で注文分割・送料計算を行うと確認済み。接続・API・処理時点などの詳細は引き続き同No.3を参照。サーバー側の検証や送料計算を、画面表示の結果で代用しない

## 7. JSの依存先と読み込み

CSSは`common.css`→`product-form.css`。body末尾のJSONに続けて、`common.js`→`product-form.js`の順に各1回読み込む。キャッシュ対策は[共通申し送り§8](HANDOFF_CLIENT_COMMON.md#8-cssjsの読み込み順とキャッシュ対策)を参照。商品フォームのJSは外部パッケージや`toggleAccordion`には依存しない。

| id・class・属性・name | JSとの関係・維持する構造 |
|---|---|
| `.product-form`・祖先の`main` | 対象フォームとエラーまとめの検索範囲。入力・画像・保存操作の要素は同じform内、エラーまとめは同じmain内に置く。JSONはbody末尾に置く |
| `#product-food`・`#food-fields` | 食品チェックとfieldset。aria-controls="food-fields"、fieldsetのhiddenだけを同期し、オフでもfood_label・allergensを送信する。checkboxへaria-expandedは付けない |
| `#product-no-stock`・`#product-stock` | 在庫管理なしと在庫数。disabledを同期 |
| `input[name="tax_rate"]` | 値8／10。各ラジオから`aria-describedby="tax-warning"`で注意領域を参照 |
| `#tax-warning .tax-warning`・`[data-tax-warning-text]` | 外側は常設の`aria-live="polite" aria-atomic="true"`、内側をhiddenで切り替え本文を更新 |
| `#product-store`・`#store-shipping-data` | selectのvalueでJSONの店舗設定を取得。scriptは`type="application/json"` |
| `#shipping-carrier`・`#shipping-carrier-help`・`.carrier-name` | 配送の先頭の運送会社名と補助文。PHPも選択中の店舗のcarrier.nameで初期出力する。JSは初期表示・店舗変更で同期。名前は常設のpolite・atomic領域、未選択時は「店舗を選ぶと表示します」、補助文「店舗の設定で決まります」はhidden。入力・送信name・設定画面リンクは置かない |
| `#packing-length`・`#packing-width`・`#packing-height`・`#packing-weight` | 4入力の現在値。nameは§3を参照 |
| `input[name="temperature"]` | 値normal／cold／frozen。各ラジオから`aria-describedby="cool-warning"`で注意領域を参照 |
| `#shipping-size-result` | 判定結果。`aria-live="polite" aria-atomic="true"`を維持 |
| `#cool-warning .shipping-warning`・`[data-cool-warning-text]` | 外側は常設のpolite・atomic領域。内側をhiddenで切り替え、本文を更新 |
| `#product-images-row`・`#product-image-list` | 画像行とol。0枚のときも要素を残して行だけhiddenにする。各画像は直下のli |
| `li[data-image-key]`・`input[name="image_order[]"]` | 既存はexisting:ID、新規はnew:番号。li内のhidden値をJSが更新する |
| `.image-preview`・`.main-image-label`・li内のimg | メインバッジの追加・表示切り替え、altの番号更新 |
| `button[data-image-action]` | 値previous／next／delete。li内に置く。type="button"・aria-label・title・端のdisabledを維持 |
| `#product-images` | name="images[]"・type="file"・multiple。視覚的に隠してもフォーカス可能な入力として残す |
| `#product-image-dropzone`・`.is-dragover` | ファイル選択・ドロップ追加とドラッグ中の表示。labelのforはproduct-imagesを参照 |
| `#product-image-template` | template内の先頭要素を新規画像のliとして複製。既存画像と同じhidden・img・操作ボタン構造にする |
| `#image-delete-fields`・`image_delete[]` | 削除する既存IDのhiddenを追加する領域。form内に残す |
| `#image-status` | 画像操作の通知。polite・atomicのlive領域 |
| `#product-save` | 保存ボタン。submitでdisabled・「保存中…」、formへaria-busy="true"。入力欄全体をdisabledにしない |
| `#product-errors` | 存在すれば初期表示でfocus。tabindex="-1"を維持 |

- フォームのinput・changeと画像操作で変更ありとし、beforeunloadでブラウザ標準の警告を要求する。一度変更すれば、元に戻したかまでは比較しない
- 保存時は離脱警告を解除し、二重submitを止める。戻る際の`pageshow.persisted`では保存ボタン・文言・aria-busyを戻し、未保存なら警告を再登録する
- 制作2-Bでは1280×800・834×1194の操作と送信値を照合済み。2026-10-21にSafariの実ブラウザでbfcache（`pageshow.persisted`）からの復帰を確認済み（Ken）。「保存する」→すぐに「戻る」で、保存ボタンが「保存する」の文言・押せる状態に戻ることを確認した。離脱確認ダイアログの実表示・取消は今回未確認で、通常ブラウザ操作での最終確認を残す。イベント発火・警告の再登録等は従来の一時テストの確認範囲を維持する（詳細は[作業記録](../MEMORY.md#2026-10-20-商品フォーム制作2-b画像操作送信離脱)）

## 8. 未確定のまま残っている項目

[QA_BACKEND.md](../QA_BACKEND.md)のNo.1〜8には回答とフロントの対応を記録済み。特に[No.1 商品画像](../QA_BACKEND.md#no1-商品画像)・[No.2 食品オフ保存の値](../QA_BACKEND.md#no2-食品オフ保存の値)・[No.4 運送会社の規格](../QA_BACKEND.md#no4-運送会社の規格)を参照する。画像一時保存は採用済み・フロント実装は次回、食品オフのB方式は今回対応、独立allergens本文のDB追加は依頼中。規格はDBテーブル＋Seeder・初期Master編集なしで確認済み。画像API等の残る詳細だけを未確定とする（ログインIDはメールアドレスに決定、2026-10-23）。

商品フォーム固有の案・未確定事項は[SPECの商品詳細の未確定事項](../SPEC.md#client商品詳細登録編集の未確定事項案)も参照。特に次を確認してほしい。

- 商品名・説明・食品表示等の文字数上限、価格・在庫の入力範囲、未指定の必須・初期値
- 梱包4項目を必須にする条件（サイズ別送料を使う場合だけ必須の案）、単位・桁・入力範囲・保存値。現行JSでは正の小数を受け付け、0以下・符号・指数・桁区切りは未入力相当。上限ちょうどは「以下」で判定しているが、業務仕様は未確定
- 運送会社の取扱上限超過・クール便非対応時に保存を止めるか。現状は注意だけ。ゆうパックの横倒し不可時の高さ50cm条件は判定方法が未確定・未実装で、150cmの冷蔵条件と通常160サイズの追加料金の対応も要確認。「その他」は初期対象外・後回しで、独自条件の設定方法も未確定
- 店舗が1つの場合の自動選択、登録後に店舗を変更できるか。現行JSは選び直しで梱包入力を保持して再判定する。店舗情報入りの食品表示ひな形案は取り下げ済みで、店舗変更時に食品表示本文は更新しない
- 在庫管理なしの在庫検索での扱い。在庫管理する商品の在庫0時の公開／非公開、「残りわずか」の基準値・共通／商品ごとの設定
- 食品表示・アレルゲン各本文の文字数上限、食品表示の表示義務の範囲（熊日側への確認を継続）。項目別入力へ戻すB案と、購入画面で絞り込み・アイコン表示をする場合のアレルゲンのチェックボックスB案は画面資料に保持している。現行方式に品目マスタ出力・該当なしの排他条件を持ち込まない
- 入稿ありの必須／任意、注文者向け説明文、形式・容量・件数・文字入稿の有無と上限
- 画像の一時保存APIのURL・応答形式、NG画像のファイル名・保存場所・対象画面、公開URLの接続、定期処理の実行方法、トリミング・リサイズ。採用済み条件への画像実装・画像の申し送りの移行は次回。セール価格・公開予約・更新者表示・更新日時の契機、見出し等の正式名称は引き続き未確定

回答が決まった内容はSPEC・商品フォーム資料・本申し送りへ反映し、name・JSON・マークアップの変更はフロントと合わせて進めてほしい。

2026-10-21の組み込み順は、商品一覧→受注一覧→商品フォーム（画像対応後）→店舗（制作完了後）。1つのLaravel・セッション認証・サーバーのPHP 8.3（8.4.27へ更新予定）／Laravel 13／MariaDB 10.11、XAMPPでbackend/publicを表示、フロントDDEV維持、バックエンドがアセットをLaravelのpublicへコピーする方針は[SPECの確定事項](../SPEC.md#バックエンド構成開発環境の確定事項)・[組み込み順](../SPEC.md#bladeへの組み込み順2026-10-21kenの決定)で確認済み。確認の経緯は[QA No.5](../QA_BACKEND.md#no5-masterclientとログイン)・[No.6](../QA_BACKEND.md#no6-技術構成)・[No.7](../QA_BACKEND.md#no7-ddev生成css)・[No.8](../QA_BACKEND.md#no8-blade組み込み)を参照する。具体的な接続・配置方法等は未確定（ログインIDはメールアドレスに決定、2026-10-23）。
