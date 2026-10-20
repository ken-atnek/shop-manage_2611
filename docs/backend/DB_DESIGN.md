# 熊日モール DB設計（案）

2026-10-20。実装前の論理設計。型はMySQL／MariaDB想定、`bigint`は符号なし、金額は円の整数、日時は`datetime`。PKは各表の`id bigint`、共通で`created_at`・`updated_at datetime`を置く案。FKは原則削除制限とし、注文履歴は物理削除しない。括弧内の必須は保存時の条件であり、未確定条件は下記に明記する。

参照略号：S販＝`../SPEC.md`「販売店と送料の確定事項」、S商＝同「Client商品詳細（登録・編集）の確定事項」、S受＝同「Client受注一覧の確定事項」、D商＝`../design/CLIENT_PRODUCT_FORM.md`、D受＝`../design/CLIENT_ORDER_LIST.md`、H商＝`../coding/HANDOFF_CLIENT_PRODUCT_FORM.md`。

## パートナー・販売店・配送

| テーブル | 項目（型・必須） | 関連・制約／根拠 |
|---|---|---|
| `partners` | `name varchar(255)` 必須、`status varchar(20)` 必須（仮） | 1パートナーが複数販売店。認証・停止状態の仕様は未確定。S販 |
| `stores` | `partner_id bigint` 必須、`name varchar(255)` 必須、`postal_code varchar(8)` 任意、`prefecture_code char(2)` 任意、`address text` 任意、`contact_name varchar(255)` 任意、`phone varchar(30)` 任意、`email varchar(255)` 任意、`carrier_id bigint` 必須、`size_based boolean` 必須、`free_shipping_threshold_yen int` 任意 | FK→partners/carriers。販売者情報の詳細は未確定。送料無料閾値NULL＝不使用、0は要確認。S販・D商 |
| `carriers` | `code varchar(20)` 必須・一意（yamato/sagawa/yupack/other）、`name varchar(100)` 必須、`revision varchar(40)` 任意（仮） | 標準3社はSeeder管理。「その他」は販売店独自規格。改定履歴方式は未確定。S販「運送会社の規格」「データの持ち方」 |
| `carrier_sizes` | `carrier_id bigint` 必須、`size_cm smallint` 必須、`max_weight_kg decimal(6,2)` 必須 | FK→carriers、(carrier_id,size_cm)一意。SPECの標準サイズと重さを初期登録。S販規格表 |
| `carrier_cool_rules` | `carrier_id bigint` 必須、`temperature varchar(10)` 必須（cold/frozen）、`supported boolean` 必須、`max_total_cm decimal(6,1)` 任意、`max_weight_kg decimal(6,2)` 任意、`max_longest_cm decimal(6,1)` 任意、`max_height_cm decimal(6,1)` 任意（仮） | FK→carriers、(carrier_id,temperature)一意。ゆうパック冷凍は非対応、冷蔵の横倒し不可時の高さ条件は入力未整備。S販規格表・未確定事項 |
| `store_sizes` | `store_id bigint` 必須、`carrier_size_id bigint` 任意、`custom_size_cm smallint` 任意、`custom_max_weight_kg decimal(6,2)` 任意 | FK→stores/carrier_sizes。標準3社は選択のみ、otherは独自値。store内のサイズ一意、標準規格の運送会社一致を検証。独自温度条件は未確定。S販 |
| `store_shipping_rates` | `store_shipping_table_id bigint` 必須、`prefecture_code char(2)` 必須、`base_fee_yen int` 任意（案） | FK→store_shipping_tables。(store_shipping_table_id,prefecture_code)一意。送料表ごと47行。通常送料の全欄必須は画面案で未確定。S販 |
| `store_shipping_tables` | `store_id bigint` 必須、`store_size_id bigint` 任意、`cool_surcharge_yen int` 任意 | FK→stores/store_sizes。1販売店の非サイズ別は1行、サイズ別は使用サイズごと1行。上表の`cool_surcharge_yen`は置かず本表を正とする（冗長化回避）。NULLと0を区別。S販 |

47都道府県コードは固定のコード表またはアプリ定数で検証する案。送料表の改定履歴と注文時のスナップショットは別扱い。

## 商品

| テーブル | 項目（型・必須） | 関連・制約／根拠 |
|---|---|---|
| `products` | `store_id bigint` 必須、`name varchar(255)` 必須、`description text` 任意、`price_incl_tax_yen int` 必須、`tax_rate_percent tinyint` 必須（8/10）、`no_stock_management boolean` 必須、`stock_quantity int` 任意、`temperature varchar(10)` 必須（normal/cold/frozen）、`packing_length_cm decimal(6,1)` 任意、`packing_width_cm decimal(6,1)` 任意、`packing_height_cm decimal(6,1)` 任意、`packing_weight_kg decimal(6,2)` 任意、`accepts_upload boolean` 必須、`is_food boolean` 必須、`food_label text` 任意、`allergens_other text` 任意、`publish_status varchar(10)` 必須（public/private） | FK→stores。stockは管理なしならNULL。梱包4項目の必須条件・精度は未確定。判定サイズは保存せず規格と寸法から再計算。食品オフでもfood_label等を保持する案。S商・H商 |
| `allergens` | `code varchar(40)` 必須・一意、`name varchar(100)` 必須、`sort_order smallint` 必須、`active boolean` 必須 | 法改定に合わせた表示マスタ。「該当なし」は品目ではなく商品側の選択状態として扱う案。S商 |
| `product_allergens` | `product_id bigint` 必須、`allergen_id bigint` 必須 | 複合一意。FK→products/allergens。食品オフでも行を保持。該当なしとの排他条件は未確定。S商 |
| `product_images` | `product_id bigint` 必須、`storage_disk varchar(40)` 必須、`storage_path varchar(1024)` 必須、`original_name varchar(255)` 任意、`mime_type varchar(100)` 必須、`size_bytes bigint` 必須、`sort_order smallint` 必須 | FK→products、(product_id,sort_order)一意。1枚目がメイン。0枚許可は案。保存先・形式・容量・上限枚数は要確認。S商・H商§4 |

`allergens[]`の`none`を保持するには `products.allergens_none boolean` を追加する案。ほかの品目・自由記入との排他が決まってから制約を確定する。価格は税込の入力値を正として保持し、注文時に税抜・税額を確定する。商品の価格・税率・販売店の後日変更が注文に波及しないよう注文明細へ写す。

## 注文・入稿

| テーブル | 項目（型・必須） | 関連・制約／根拠 |
|---|---|---|
| `orders` | `partner_id bigint` 必須、`store_id bigint` 必須、`purchase_group_key varchar(64)` 任意（仮）、`order_number varchar(50)` 必須・一意、`ordered_at datetime` 必須、`shipping_status varchar(20)` 必須、`shipping_status_updated_at datetime` 必須、`customer_note text` 任意、`cancel_return_reason text` 任意、`cancel_return_note text` 任意、`destination_prefecture_code char(2)` 必須、`destination_name varchar(255)` 必須、`destination_postal_code varchar(8)` 任意、`destination_address text` 必須、`destination_phone varchar(30)` 任意、`contact_email varchar(255)` 任意、`shipping_fee_incl_tax_yen int` 必須、`items_total_incl_tax_yen int` 必須、`total_incl_tax_yen int` 必須、`free_shipping_applied boolean` 必須、`applied_shipping_size_cm smallint` 任意、`applied_temperature varchar(10)` 必須 | FK→partners/stores。販売店ごとに1注文、送料1行、発送状況1つ。購入単位を束ねるキーは決済方式確認待ち。配送先・連絡先の参照元は未確定。状態更新時だけ専用日時を更新。S受・S販・D受 |
| `order_items` | `order_id bigint` 必須、`product_id bigint` 必須、`product_name_snapshot varchar(255)` 必須、`quantity int` 必須、`unit_price_incl_tax_yen int` 必須、`tax_rate_percent tinyint` 必須、`subtotal_incl_tax_yen int` 必須、`cancel_return_item_note varchar(100)` 任意 | FK→orders/products。商品名・価格・税率を注文時に写す。明細ごとの記録は任意100字の仮値。S受・D受 |
| `order_upload_images` | `order_item_id bigint` 必須、`storage_disk varchar(40)` 必須、`storage_path varchar(1024)` 必須、`original_name varchar(255)` 必須、`mime_type varchar(100)` 必須、`size_bytes bigint` 必須、`sort_order smallint` 必須 | FK→order_items。注文者が添付、管理画面は閲覧・ダウンロードのみ。件数・形式・容量・アクセス制御は未確定。S受・D受 |

注文確定時に送料・送料無料判定・税率別内訳をサーバーで計算し、表示再現に必要な金額・適用条件を注文へ保存する。税率別内訳・端数処理の確定後に`order_tax_summaries`等を追加する案。決済、顧客アカウント、販売店変更、返品返金の詳細は購入側のシステム確認後に設計する。キャンセル／返品の記録は発送状態を戻しても削除しない。

## `product-form.php`のname対応

| name | DB対応・処理 |
|---|---|
| `store_id` | `products.store_id`。ログインパートナー所属を検証 |
| `product_name` / `description` | `products.name` / `description` |
| `price` / `tax_rate` | `price_incl_tax_yen` / `tax_rate_percent` |
| `stock` / `no_stock_management` | `stock_quantity` / `no_stock_management`。チェック時stockは未送信・DBはNULL案 |
| `temperature` | `products.temperature` |
| `packing[length]` / `[width]` / `[height]` / `[weight]` | `products.packing_*`。cm/kg、精度未確定 |
| `upload` | `products.accepts_upload`。未送信はfalse |
| `is_food` / `food_label` | `products.is_food` / `food_label`。オフ時の未送信は既存値維持案 |
| `allergens[]` / `allergens_other` | `product_allergens`＋`products.allergens_none`案 / `products.allergens_other`。正式codeと排他条件未確定 |
| `publish` | `products.publish_status` |
| `images[]` / `image_order[]` / `image_delete[]` | `product_images`の追加／並び順／削除。`new:n`はimagesの1始まり、`existing:ID`は当該商品所属を検証。H商§4 |

`image_order[]`・`image_delete[]`の既存ID改ざん、同一画像重複、添付の保存失敗をサーバー側で検証する。画面の販売店JSONは上記テーブルから組み立て、送料金額は含めない（H商§6）。
