# Client商品詳細（登録・編集） バックエンドへの申し送り

- 作成日：2026-10-20
- 対象：`shop-partner/product-form.php`（制作用PHP）、`shop-partner/assets/scss/product-form.scss`、生成CSS、`shop-partner/assets/js/product-form.js`
- 作成：フロント（Ken・Codex）

共通の内容は[HANDOFF_CLIENT_COMMON.md](HANDOFF_CLIENT_COMMON.md)を参照。ここには商品フォーム固有の内容だけを記載する。仕様の正は[仕様書](../SPEC.md)と[商品フォーム資料](../design/CLIENT_PRODUCT_FORM.md)で、内容が食い違う場合は最新の仕様資料を優先し、フロントへ知らせてほしい。

フロントの制作状況は「完成（引き渡し前）」。保存処理・サーバー検証・DB連携は実装していない。以下では、現在の制作実装と、バックエンド確認待ちの仮決めを分ける。

## 1. 制作用PHPのうち、Bladeへ移さないもの

制作専用の仕組み・共通レイアウトの扱いは[共通申し送り§1](HANDOFF_CLIENT_COMMON.md#1-共通レイアウトと制作専用の仕組み)を参照。

| 対象 | 内容 | Bladeでの扱い |
|---|---|---|
| `$previewState`（`?preview=`）・各`$is…State` | 新規・食品・エラー・保存完了・注意等の10状態の表示確認 | preview・状態フラグを移さず、実際の新規／編集・入力値・検証結果・保存結果で分岐する |
| 商品・販売店・画像・日時等の表示例 | 商品ID986、既存画像ID101～103、販売店2件、サンプル画像等 | 実データに置き換える。現行の`?id=…`は商品を取得せず、同じ表示例を出す |
| `$sizeResultExample`・`$coolWarningExample`等 | 初期HTML用の固定表示例 | previewによる文言分岐を移さない。初期の実入力と設定に合う表示を出し、JSも読み込み時に同期する |
| `$staticExport`・`$pageExtension` | 制作確認用のPHP／HTMLリンク切り替え | 本番ルート・アセットURLへ置き換える |
| `includes/data_carriers.php` | 配列を返す制作用の運送会社規格 | ファイルを本番のデータソースとして移さない。規格の正はSPEC。本番の持ち方は[SPECの確認事項4](../SPEC.md#バックエンドへの確認事項)で確認する |
| サイドバー「商品登録」の`?preview=new` | 制作用の新規表示リンク | 実際の新規登録URLへ置き換える。商品一覧の編集リンク・保存バーの一覧リンクも本番ルートへ置き換える |

## 2. 新規／編集と保存後の表示

1つのフォームで新規／編集を出し分ける。見出し・現在地は現在の制作案で、業務上の正式な画面名との統一は未確定。

| 対象 | 新規 | 編集 |
|---|---|---|
| h1・titleの画面名 | 商品登録 | 商品編集 |
| サイドバーの現在地 | 商品登録（制作側の`$currentPage = 'product-new'`） | 商品一覧（`'products'`） |
| ID・登録日時・更新日時 | 未登録のため表示しない | 見出し下に読み取り専用で表示。日時は機械可読な`datetime`付きの`time`要素。入力・送信項目にしない |
| 各入力欄 | 商品名・説明・価格・梱包4項目等は空欄。画像0枚。販売店は未選択（1件なら自動選択する案は未確定） | 保存済みの値を表示。画像は保存順で出力する |
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
| 販売店 | `store_id` | 販売店ID。必須。selectのvalueとJSONのキーを一致させる |
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
| アレルゲン | `allergens[]` | 食品オンのとき、1つ以上または「該当なし」が必須。表示例の値は`wheat`・`egg`・`milk`・`soy`・`apple`・`sesame`・`none`。正式な品目・値はバックエンドのマスタから出力する |
| その他のアレルゲン | `allergens_other` | 自由記入。その他だけを入力した場合の検証・「該当なし」との排他条件は未確定 |
| 公開状態 | `publish` | ラジオ`public`／`private`。保存と同時に反映する |

- 必須表示は文字「必須」。テキスト等の`aria-required="true"`と、ラジオのfieldset／legend内の必須表示を維持し、HTMLの`required`は付けない。ブラウザ標準の検証で止めず、サーバー側で検証する
- 未チェックのチェックボックスは送信されない。現在は未チェック用のhidden値`0`を置いていないため、バックエンドで有無を解釈する
- 食品オフでは`#food-fields`のfieldset全体が`disabled`＋`hidden`となり、`food_label`・`allergens[]`・`allergens_other`は送信されず、Tabの対象にもならない。オン／オフの操作自体では値を消さない
- 在庫管理なしでは`#product-stock`がdisabledとなり、`stock`は送信されない。画面内の値は保持する。一覧では「—」＋読み上げ用「在庫管理なし」とし、「在庫切れ」「残りわずか」の対象外。在庫検索での扱いだけ未確定
- 食品オフ保存でも保存済みの食品表示を残し、オフ中は表示しないのがフロントの仮決め。disabledによる未送信を削除指示として扱わない。オフ前の未保存入力・アレルゲンの保存方法を含め、[SPECの確認事項2](../SPEC.md#バックエンドへの確認事項)へ回答してほしい
- 判定サイズは表示用のテキストで、送信用のサイズ欄や送料入力欄はない。判定結果を確定した送料として受け取らない

## 4. 商品画像の送信と操作

画像は選択時には送信せず、保存時にほかの欄と1回のPOSTで送る[フロントの仮決め](../SPEC.md#フロントの仮決めバックエンドの確認待ち)。保存先・形式・容量・枚数・一時保存方式・下記の仮nameと契約は[SPECの確認事項1](../SPEC.md#バックエンドへの確認事項)で確認する。

| 送信値 | 対応 |
|---|---|
| `images[]` | 保存時点で残っている新規ファイルだけ。既存画像は含めない |
| `image_order[]`の`existing:101` | ID101の既存画像。配列の位置が最終表示順 |
| `image_order[]`の`new:1` | `images[]`の1枚目（ファイル配列の添字0）。`new:2`は2枚目。新規画像の移動・削除後に1から採番し直し、ファイル順と同期する |
| `image_delete[]`の`102` | ID102の既存画像の削除指示。`existing:`は付けない。新規画像の削除はファイル配列から外すだけ |

例：`image_order[] = [existing:101, new:1, existing:103, new:2]`、`image_delete[] = [102]`なら、既存101→新規1枚目→既存103→新規2枚目の順。既存画像のIDは表示例に固定しない。

- 「前へ／後ろへ」で並べ替え、先頭だけメインバッジを出す。端のボタンのdisabled・画像番号・aria-label・titleはJSが更新する。移動後は押したボタンへ戻し、端で無効になった場合は反対側のボタンへフォーカスする
- 削除は保存まで確定しないため、確認ダイアログを出さない。次の画像の有効なボタン、なければファイル入力へフォーカスする
- 新規画像はファイル選択・ドロップで追加する。Object URLでプレビューし、削除時に解放する。DataTransferでファイル配列と表示順を同期する。画像以外は追加せず「画像ファイルを選択してください」と通知する。形式・容量・枚数の上限は未設定
- 操作結果は`#image-status`のpolite live領域で通知する。ドラッグによる並べ替えは実装していない。並べ替えはボタン操作、ドロップは追加用
- 入力エラー後に新しく選んだ画像をファイル入力へ復元することはできず、選び直しになる。選択時の一時保存方式にできるなら、再選択を避けられるためそちらがよい。方式を変更する場合はJSと送信契約も見直す

## 5. エラーと入力値の保持

基本のエラー属性は[共通申し送り§5](HANDOFF_CLIENT_COMMON.md#5-入力エラー)を参照。商品フォームは次を追加する。

- メタ情報とformの間に`#product-errors.error-summary`を出力する。`aria-labelledby="product-errors-heading"`・`tabindex="-1"`を維持する。見出し`#product-errors-heading`は「入力内容を確認してください（N件）」とし、実際のエラー件数を出す
- まとめの各リンクはエラーのある入力欄のidへ向ける。現行の3件は`#product-name`・`#product-price`・`#food-label`。件数・文言・リンクを検証結果に合わせて出力する。JSは初期表示時にまとめへフォーカスを移す
- 各欄に`.form-error`と固有のidを出力し、入力側に`aria-invalid="true"`・`aria-describedby`を付ける。補助文がある場合は補助文idも残す。例：食品表示は`food-label-help food-label-error`を参照する
- ラジオのエラー属性は各ラジオへ付ける。fieldset／legendでグループ名・必須を伝え、内側へ`role="radiogroup"`やグループ用のエラー属性を追加しない。税率のエラー時も既存の注意領域`tax-warning`への参照を残す
- ラジオ・チェックボックス単体に赤い四角の入力枠は付けない。税率はカードの赤枠と`.form-error`で伝える
- エラー時は新規／編集の文脈と入力値・チェック・販売店の選択を保持する。制作の`error`は新規入力途中の例であり、本番のすべてのエラーを新規扱いにしない
- 食品の欄にエラーがあれば食品オンで出力し、`#food-fields`のdisabled・hiddenを外す。JSもチェックに合わせて同期するため、チェックとfieldsetを一致させる
- ファイル以外の入力値と、既存画像の並び替え・削除指示も復元する。新規画像だけは§4のとおり再選択が必要。現在のerrorは「商品名」「価格」「食品表示」が空で、税率は10%を保持する例

## 6. 販売店の送料JSONと判定

`<script type="application/json" id="store-shipping-data">`をJSより前に置く。ルートは販売店IDをキーにしたオブジェクトで、`#product-store`のvalueと一致させる。[商品フォーム資料のJSON全形](../design/CLIENT_PRODUCT_FORM.md#制作2-aのjsと販売店json2026-10-20)も参照。以下は実装と同じ形の販売店2の例であり、値は本番データに置き換える。

```json
{
  "2": {
    "name": "熊本ギフト サンプル販売店",
    "carrierId": "yupack",
    "sizeBased": false,
    "usedSizes": [],
    "coolSurchargeSetBySize": {},
    "coolSurchargeSet": true,
    "carrier": {
      "name": "ゆうパック",
      "sizes": [
        {"size": 60, "maxWeightKg": 25}, {"size": 80, "maxWeightKg": 25},
        {"size": 100, "maxWeightKg": 25}, {"size": 120, "maxWeightKg": 25},
        {"size": 140, "maxWeightKg": 25}, {"size": 160, "maxWeightKg": 25},
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
| 販売店ID | 文字列キー。selectに出す各販売店の設定を持つ |
| `name`・`carrierId` | 販売店名・運送会社ID。現在のIDは`yamato`／`sagawa`／`yupack`／`other` |
| `sizeBased` | boolean。サイズ別送料の有無 |
| `usedSizes` | 使用するサイズの数値配列。サイズ別ありで判定対象を絞る。サイズ別なしでは空配列とし、JSは運送会社の全サイズから取扱上限を確認する |
| `coolSurchargeSetBySize` | サイズをキーとするbooleanのオブジェクト。例：`{"60": true, "80": false}`。サイズ別ありで使用する |
| `coolSurchargeSet` | boolean。サイズ別なしの販売店の追加料金設定有無 |
| `carrier.name` | 注意文に使う運送会社名 |
| `carrier.sizes` | `{size, maxWeightKg}`の配列。sizeはサイズ番号かつ3辺合計の上限cm、maxWeightKgはそのサイズの重さ上限kg。値は数値型 |
| `carrier.cool.refrigerated`・`.frozen` | それぞれ冷蔵・冷凍の条件。非対応は`null`。対応する場合は数値の`maxTotalCm`・`maxWeightKg`と、条件がある場合の`maxLongestCm`を持つ |

- 追加料金の空欄は`false`、0円を含む設定ありは`true`。金額・47都道府県の送料表・送料無料の閾値は、この表示用JSONには渡さない
- 販売店設定の`carrierId`で規格を取得し、`carrier`へ結合する。制作用は`data_carriers.php`を参照するが、本番は本番のデータソースに置き換える。標準3社の重さ上限等を販売店側に複製・編集させない。規格の正・出典は[SPECの運送会社の規格](../SPEC.md#運送会社の規格正の資料)
- 「その他」は販売店の独自サイズ・重さ・温度帯条件を同じ`carrier`の形へ結合する。制作用データの空のsizesやnullのcoolを、そのまま確定した独自規格として出さない。具体的な設定方法は未確定
- JSONは数値・boolean・null・配列・オブジェクトを区別し、script要素を壊さない方法で出力する。現在のPHPは`JSON_HEX_TAG`等でエスケープしている。文字列の数値・booleanへ変えない

### 梱包サイズとクール便の注意

- 縦・横・高さ・重さの入力後300ms程度で更新する。全角数字を半角として扱うが、入力値自体は書き換えない。空欄・非数値なら「縦・横・高さ・重さを入力すると判定します」。販売店未選択なら「販売店を選ぶと判定します」
- サイズ別ありでは、使用サイズのうち3辺合計と規格の重さ上限の両方に収まる最小サイズを表示する。例：「判定：80サイズ（3辺合計75cm・1.2kg）」。収まらなければ「対応するサイズがありません（3辺合計〇cm・〇kg）」
- サイズ別なしでは「この販売店はサイズ別の送料を使いません（3辺合計〇cm・〇kg）」。運送会社の上限を超えると「{運送会社}の上限（3辺合計{○}cm・{○}kg）を超えています（3辺合計〇cm・〇kg）」
- 冷蔵・冷凍の注意は、運送会社の温度帯非対応→実寸・重さのクール上限→最長辺の条件→販売店の追加料金未設定の順。通常送料の判定サイズ番号だけでクール上限を判断しない
- 文言は「この販売店の運送会社（ゆうパック）は冷凍に対応していません」「ヤマト運輸のクール便は3辺合計120cm・15kgまでです」「ゆうパックのクール便は最長辺100cmまでです」「{サイズ}サイズのクール便に対応していません」（サイズ別なしは「この販売店はクール便に対応していません」）。温度帯そのものの非対応は寸法未入力でも表示し、寸法・重量・料金の注意は4入力が有効になってから判断する
- 判定と注意はpolite live領域で通知し、同じ文言の再設定を避ける。初期表示・販売店変更・温度帯変更時も現在値で更新する。食品や配送の入力値は販売店変更で消さない
- **食品と10%税率の注意はJSONを使わない**。食品チェックがオンかつ税率10%なら「食品は通常8%です。税率を確認してください」を表示する。チェック・税率変更・初期表示で同期し、自動で税率を変えない
- 現在のJSの判定・注意はフロントの表示だけで、保存は止めない。購入側で注文分割・送料計算を行う前提で、担当するシステム・処理場所は[SPECの確認事項3](../SPEC.md#バックエンドへの確認事項)で確認する。サーバー側の検証や送料計算を、画面表示の結果で代用しない

## 7. JSの依存先と読み込み

CSSは`common.css`→`product-form.css`。body末尾のJSONに続けて、`common.js`→`product-form.js`の順に各1回読み込む。キャッシュ対策は[共通申し送り§8](HANDOFF_CLIENT_COMMON.md#8-cssjsの読み込み順とキャッシュ対策)を参照。商品フォームのJSは外部パッケージや`toggleAccordion`には依存しない。

| id・class・属性・name | JSとの関係・維持する構造 |
|---|---|
| `.product-form`・祖先の`main` | 対象フォームとエラーまとめの検索範囲。入力・画像・保存操作の要素は同じform内、エラーまとめは同じmain内に置く。JSONはbody末尾に置く |
| `#product-food`・`#food-fields` | 食品チェックとfieldset。`aria-controls="food-fields"`、fieldsetのhidden・disabledを同期。checkboxへaria-expandedは付けない |
| `#product-no-stock`・`#product-stock` | 在庫管理なしと在庫数。disabledを同期 |
| `input[name="tax_rate"]` | 値8／10。各ラジオから`aria-describedby="tax-warning"`で注意領域を参照 |
| `#tax-warning .tax-warning`・`[data-tax-warning-text]` | 外側は常設の`aria-live="polite" aria-atomic="true"`、内側をhiddenで切り替え本文を更新 |
| `#product-store`・`#store-shipping-data` | selectのvalueでJSONの販売店設定を取得。scriptは`type="application/json"` |
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
- 制作2-Bでは1280×800・834×1194の操作と送信値を照合済み。ただし離脱確認ダイアログの実表示・取消、実bfcacheによる復帰は通常ブラウザ操作で最終確認が残る。イベント発火・復帰分岐は一時テストで確認した（詳細は[作業記録](../MEMORY.md#2026-10-20-商品フォーム制作2-b画像操作送信離脱)）

## 8. 未確定のまま残っている項目

[SPECのバックエンドへの確認事項](../SPEC.md#バックエンドへの確認事項)は8項目とも回答待ち。バックエンド担当者がマージ後に同節の回答欄へ直接記入する。画像の送信方式（No.1）、食品オフ保存（No.2）、運送会社規格のDBテーブル＋Seeder（No.4）は[フロントの仮決め](../SPEC.md#フロントの仮決めバックエンドの確認待ち)であり、回答までは確定仕様として扱わない。

商品フォーム固有の案・未確定事項は[SPECの商品詳細の未確定事項](../SPEC.md#client商品詳細登録編集の未確定事項案)も参照。特に次を確認してほしい。

- 商品名・説明・食品表示等の文字数上限、価格・在庫の入力範囲、未指定の必須・初期値
- 梱包4項目を必須にする条件（サイズ別送料を使う場合だけ必須の案）、単位・桁・入力範囲・保存値。現行JSでは正の小数を受け付け、0以下・符号・指数・桁区切りは未入力相当。上限ちょうどは「以下」で判定しているが、業務仕様は未確定
- 運送会社の取扱上限超過・クール便非対応時に保存を止めるか。現状は注意だけ。ゆうパックの横倒し不可時の高さ50cm条件は判定方法が未確定・未実装で、150cmの冷蔵条件と通常160サイズの追加料金の対応も要確認。「その他」の独自条件の設定方法も未確定
- 販売店が1つの場合の自動選択、登録後に販売店を変更できるか。現行JSは選び直しで梱包入力を保持して再判定する。食品オン時に販売店情報入りのひな形を入れる案は未実装で、本文を上書きする条件・販売店変更時の更新も未確定
- 在庫管理なしの在庫検索での扱い。在庫管理する商品の在庫0時の公開／非公開、「残りわずか」の基準値・共通／商品ごとの設定
- アレルゲンの「該当なし」の排他条件とその他自由記入の検証、食品表示の表示義務の範囲（熊日側への確認を継続）。項目別入力へ戻すB案は画面資料に保持している
- 入稿ありの必須／任意、注文者向け説明文、形式・容量・件数・文字入稿の有無と上限
- 画像の保存先・形式・容量・枚数・比率・一時保存、セール価格・公開予約・更新者表示・更新日時の契機、見出し等の正式名称

回答が決まった内容はSPEC・商品フォーム資料・本申し送りへ反映し、name・JSON・マークアップの変更はフロントと合わせて進めてほしい。
