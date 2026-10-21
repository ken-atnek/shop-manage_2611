# Client店舗の登録・編集 バックエンドへの申し送り

- 作成日：2026-10-21
- 対象：`shop-partner/store-form.php`（制作用PHP）、`shop-partner/assets/scss/store-form.scss`、生成CSS、`shop-partner/assets/js/store-form.js`
- 作成：フロント（Ken・Codex）

共通の内容は[HANDOFF_CLIENT_COMMON.md](HANDOFF_CLIENT_COMMON.md)を参照。ここには店舗フォーム固有の内容だけを記載する。仕様の正は[SPECの店舗と送料](../SPEC.md#店舗と送料の確定事項)と[店舗フォーム資料](../design/CLIENT_STORE_FORM.md)。食い違う場合は最新の仕様資料を優先し、フロントへ知らせてほしい。

制作状況は「完成（引き渡し前）」。申し送りの整備まで完了。`action="#"`の表示用フォームで、保存処理・サーバー検証・DB連携は実装していない。以下のname・id・JSONは現行フロントの仮契約で、本番契約の確定はバックエンドと行う。変更時はJS・ARIA参照も合わせてフロントと調整する。

## 1. 制作用PHPのうち、Bladeへ移さないもの

共通レイアウト・静的出力の扱いは[共通申し送り§1](HANDOFF_CLIENT_COMMON.md#1-共通レイアウトと制作専用の仕組み)を参照。

| 対象 | 内容 | Bladeでの扱い |
|---|---|---|
| `$previewState`・各`$is…State` | `new`・`no-size`・`sagawa`・`error`・`saved`・`registered` | 実際の新規／編集、入力・保存値、検証・保存結果で分岐する。previewを移さない |
| 基本情報・ID・金額・日時・エラーの表示例 | 通常はID 1のヤマト、no-sizeはID 2のゆうパック、sagawaはID 3の佐川 | 実データへ置き換える。`id`クエリは制作側では取得に使っていない |
| `$staticExport`・`$pageExtension` | PHP／HTMLリンク切り替え | 一覧への戻り先を含め本番ルートへ置き換える |
| `includes/data_carriers.php` | 標準3社の規格を返す制作用配列 | 本番はDBの規格＋初期Seederへ置き換える。[QA No.4](../QA_BACKEND.md#no4-運送会社の規格)で確認済み。初期Master規格編集は設けない |
| `includes/data_prefectures.php` | 13区分・47都道府県を返す制作用配列 | 本番の都道府県コード表・定数等へ置き換える。コードの正は[DB設計](../backend/DB_DESIGN.md#パートナー店舗配送)、地方への対応は[SPECの表](../SPEC.md#店舗と送料の案)。QA No.4は規格の回答で、都道府県の保存方式を確定する回答ではない |

表示用配列・固定エラーは移さないが、全サイズ表の出力構造、対象判定、入力値の復元は本番データで再現する。

## 2. 新規／編集と保存後の表示

| 対象 | 新規 | 編集 |
|---|---|---|
| h1・title | 店舗登録 | 店舗編集 |
| メタ情報 | ID・更新日時なし | `.store-meta`にID・更新日時。日時は`time datetime`付き。登録日時・送信用ID欄は追加しない |
| 基本情報 | 空欄 | 保存済み値 |
| 運送会社・サイズ別・送料無料 | 未選択・オフ・オフ | 保存済み設定。未選択方式・未使用サイズの保存済み料金も復元元として読み込む |
| `data-sizes-initialized` | 未操作の新規だけ文字列`"false"` | 文字列`"true"` |

`data-sizes-initialized`は業務データではなく、最初にサイズ別をオンにしたときだけ全標準サイズを選ぶ初期化フラグ。JSが初回オンで`true`にする。新規の検証エラー再表示でも、既に入力した使用サイズを保持する場合は`true`で出し、再び全選択しない。チェック状態と復元値を一致させる。

サイドバーは新規／編集とも「店舗」が現在地。リンクと`aria-current`は[一覧申し送り§4](HANDOFF_CLIENT_STORE_LIST.md#4-追加編集リンクと現在地)を参照。

- 新規保存後はIDのある編集画面へ移り、「店舗を登録しました」を表示する
- 編集保存後は同じ編集画面で「保存しました」を表示する
- 通知はメタ情報の後・formの前の`.save-status`、`role="status"`。通知にフォーカスを移さない。商品フォームと同じ配置・見た目
- 保存後の遷移の正は[SPEC](../SPEC.md#配送送料の変更と送信)。`registered`／`saved`は遷移後の表示例で、制作側の保存実装ではない

## 3. 送信内容と必須条件

`.store-form`は`method="post"`・`novalidate`。4つの箱を1回のPOSTで送る。`action="#"`を本番保存ルートへ置き換える。以下は実際に出力するnameの全パターン。`{size}`はcmの整数値、`{code}`は`01`〜`47`の2桁都道府県コード。フォーム送信の値は文字列であり、サーバーで検証・型変換する。条件の正は[SPECの入力条件](../SPEC.md#店舗フォームの入力条件と初期値)。

| 項目 | name | 値・条件 |
|---|---|---|
| 店舗名 | `name` | テキスト、必須・50文字まで |
| 郵便番号 | `postal_code` | テキスト、任意。発送元の社内連絡用 |
| 住所 | `address` | 1行テキスト、任意。同上 |
| 担当者 | `contact_name` | テキスト、任意・50文字まで。同上 |
| 電話番号 | `phone` | `type="tel"`、任意。ハイフンあり／なしを許容。同上 |
| 運送会社 | `carrier_code` | 必須。`yamato`／`sagawa`／`yupack`。「その他」は初期対象外 |
| サイズ別 | `size_based` | チェック時`1`。未チェックは未送信 |
| 使用サイズ | `used_sizes[]` | 選択中の会社のcm値を文字列の配列で送る（例`60`・`80`）。方式オフでは未送信 |
| 共通・通常送料 | `shipping_tables[common][rates][{code}][base_fee_yen]` | 送信対象表の47欄すべて必須。0〜99,999円の整数。空欄を0に補完しない |
| サイズ別・通常送料 | `shipping_tables[by_size][{size}][rates][{code}][base_fee_yen]` | 同上。使用サイズの各表 |
| 共通・冷蔵追加料金 | `shipping_tables[common][cool_refrigerated_surcharge_yen]` | 対象欄は空欄可（非対応）。入力時0〜99,999円の整数。0は対応・追加無料 |
| 共通・冷凍追加料金 | `shipping_tables[common][cool_frozen_surcharge_yen]` | 同上 |
| サイズ別・冷蔵追加料金 | `shipping_tables[by_size][{size}][cool_refrigerated_surcharge_yen]` | 同上。会社・サイズ・温度帯の対象判定は§4 |
| サイズ別・冷凍追加料金 | `shipping_tables[by_size][{size}][cool_frozen_surcharge_yen]` | 同上 |
| 送料無料の有無 | `free_shipping_enabled` | チェック時`1`。未チェックは未送信 |
| 送料無料の条件金額 | `free_shipping_threshold_yen` | オン時1〜99,999円の整数。0不可。オフ時disabledで未送信 |

店舗名・運送会社・通常送料には現行PHPの`required`・`aria-required="true"`があるが、formの`novalidate`によりブラウザ検証で保存を止めない。商品フォームの「requiredなし」とは現行属性が異なる。必須・範囲の検証はサーバー側で行う。

チェックボックスの未チェック用hidden値`0`は置いていない。チェックの有無は使用状態として解釈し、金額キーの欠落とは区別する。全国・地方の一括入力欄にはnameがなく、保存されない。サイズ規格・追跡URL・表示用ID・更新日時・`partner_id`・保存ボタンも送信項目ではない。所属・更新対象の確認はサーバーで行う。

### name・JSONとDB設計の対応・差異

[DB_DESIGN.md](../backend/DB_DESIGN.md#パートナー店舗配送)は設計案。次は変換が必要な点と記述の差異で、今回DB資料・コードは変更していない。

| 現行フロント | DB設計 | 本番での対応・確認 |
|---|---|---|
| `carrier_code`（会社コード） | `stores.carrier_id`、`carriers.code` | コードから会社IDへ解決する |
| `used_sizes[]`（cm値） | `store_sizes.carrier_size_id`、`is_selected` | 選択会社と`carrier_sizes.size_cm`で規格IDを特定し、使用状態と保存料金を分離する |
| `shipping_tables[common]`／`[by_size][{size}]` | `store_shipping_tables.store_size_id` | 共通はNULL、サイズ別は対応する`store_sizes.id`。入力のcm値をそのまま外部キーにしない |
| `rates[{code}][base_fee_yen]` | `store_shipping_rates.prefecture_code char(2)`・`base_fee_yen` | 所属送料表へ対応付け、コードは2桁を保持する。地方の一括入力額は保存しない |
| JSONの`refrigerated`／`frozen` | `carrier_cool_rules.temperature`の`cold`／`frozen` | 冷蔵は`cold`→`refrigerated`へ変換。料金列の`cool_refrigerated_surcharge_yen`・`cool_frozen_surcharge_yen`自体は一致 |
| JSONの`size`・`maxWeightKg`・`maxTotalCm`・任意`maxLongestCm` | `size_cm`・`max_weight_kg`・`max_total_cm`・`max_longest_cm`、`supported` | camelCaseの数値へ変換。decimalの取得値を文字列のまま渡さない。非対応の温度帯は`null` |
| `free_shipping_enabled`＋条件金額 | 独立したenabled列なし。閾値NULL＝不使用の案 | チェック有無とDBの不使用状態を対応付ける。オフ時の画面内保持は確定だが、オフで保存後の金額再利用の永続化方法は未確定。欠落した金額だけから更新意図を決めない |
| 送料無料は1〜99,999円、0不可（SPEC確定） | 「0の適用解釈は要確認」が残る | 最新SPECを正として検証し、DB側の旧記述の同期を依頼する |
| 郵便番号・住所・電話番号の詳細制限は未確定 | `varchar(8)`・`text`・`varchar(30)`の案 | 型案を確定した画面のmaxlength・検証規則として扱わない |
| 商品フォームの温度帯別JSON・JSは対応済み | 「現行JSONは温度帯共通」の旧記述が残る | 最新SPEC・商品フォーム資料を正とする。キー名・形式の本番契約確認は継続（§8） |

基本情報の名称、店舗名・担当者の50文字、通常送料と温度帯別料金の列名・空欄と0の区別は一致している。会社ごとに規格IDを持つDBと、画面内で同じcm値の送料表を共用する実装の復元対応は、§4・§8の区別を維持して組み込む。

## 4. 送料の送信対象・保持とクール便判定

正は[SPECの変更と送信](../SPEC.md#配送送料の変更と送信)と[QA No.3](../QA_BACKEND.md#no3-購入側注文送料)。CSSで見えないことと、送信しないことを混同しない。

| 領域 | 属性・送信 |
|---|---|
| 選んでいない方式 | 方式fieldsetを`disabled`＋`hidden`。送らない |
| 使っていないサイズ | サイズ表fieldsetを`disabled`＋`hidden`。送らない |
| 使用サイズの、表示中でないタブ | パネルは`hidden`だけ。全使用タブを送る |
| 非対応温度帯・クール便規格外のサイズ欄 | 温度帯fieldsetを`disabled`＋`hidden`。送らない |
| 送料無料オフの条件金額 | 欄を`disabled`。見えるまま値を保持し、送らない |

全9サイズ（60・80・100・120・140・160・170・180・200）と共通表をあらかじめ出力する。JSは方式・サイズごとの送料入力要素を破棄せず、同じcm値の表を会社間でも共用する。会社別の金額履歴を別々に保持する実装ではない。会社変更で候補外になったサイズは選択から外れ、元の会社へ戻して再選択するとその表の値を再利用できる。手動解除・方式切り替え・送料無料オフでも画面内の値は残る。

**DBの保存済み料金は未送信を理由に削除・NULL・0円へ更新しない。** 未選択方式・未使用サイズの通常送料・クール追加料金を保持し、現在の更新・計算対象から外す。対象外温度帯の未送信も、空欄で送信された追加料金とは区別する。送信対象欄の明示的な空欄は「その温度帯は非対応」、`0`は「対応・追加無料」。送料無料の使用状態はチェックの有無で判定する。非送信だった編集中の値はPOSTだけでは復元できないので、保存済み値の保持と区別する。

### サイズごとのクール便欄

判定の正は[SPECの決定4](../SPEC.md#サイズごとのクール便欄の対象2026-10-21kenの決定4)。現行PHPの初期出力とJSの`canUseCool`は同じ式を使う。サーバー側でも、現在の会社・方式・使用サイズ・温度帯から同じ判定で対象を検証する。hidden／disabledやクライアントの判定結果だけに依存しない。

- 温度帯非対応なら対象外。サイズ共通は会社が対応する温度帯をそのまま対象にする
- サイズ別の最小標準サイズは、温度帯に対応していれば対象
- それ以外は`min(S, C) > S_prev` **または** `min(W_S, K) > W_prev`なら対象。Sはサイズcm、W_Sはその重量上限、C・Kは温度帯の寸法・重量上限。S_prev・W_prevは会社の**全標準サイズ**の直前であり、店舗の使用サイズだけから求めない
- ヤマト140・160・180・200、ゆうパック170は対象外。佐川160、ゆうパック160冷蔵は対象。ゆうパック冷凍は全表で対象外
- ゆうパック160冷蔵は「3辺合計150cmまで」を`aria-describedby`で関連付ける。150cm・重量・最長辺等の実際の規格を広げない

## 5. エラーと入力値の保持

エラーの基本形は[共通申し送り§5](HANDOFF_CLIENT_COMMON.md#5-入力エラー)を参照。店舗固有の追加契約は次のとおり。

- メタ情報とformの間に`#store-errors.error-summary`を置く。`aria-labelledby="store-errors-heading"`・`tabindex="-1"`を維持する。見出しは「入力内容を確認してください（N件）」で、Nは**まとめの行数**
- 基本情報・送料無料等は1欄1行。送料は**送料表ごとに1行**にまとめ、その表の最初の有効なエラー欄へリンクする。通常送料と追加料金が同じ表でエラーなら、同じ行に含める
- 各行の`data-error-fields`に、その行に属する全エラー欄のidを空白区切りで指定する。JSは有効な最初のエラー欄へhrefを同期し、全対象がdisabledなら行を隠して件数から除外する。まとめの本文はサーバーが組み立てる
- 各欄は`aria-invalid="true"`、固有id付き`.form-error`、そのidを含む`aria-describedby`。既存の補助文idも残す。現行エラー文idは入力id＋`-error`。通常送料の未入力文は短い「入力してください」
- 非選択方式・未使用サイズ・対象外温度帯・送料無料オフなどdisabledの欄を、サーバーのエラー対象にも含めない。JSでも`:disabled`（祖先fieldsetの無効化を含む）を除外する
- 各タブの件数はパネル内の有効な`aria-invalid="true"`欄の数。`.tab-error`で「! 件数」、tabの`aria-label`で「120サイズ、エラー2件」。印は`aria-hidden="true"`とし二重読みを避ける。まとめの行数とタブ件数は別
- 初期表示は最初の有効なエラーのあるサイズタブを選び、フォーカスはまとめへ。まとめリンクでは`activateTab`で開き、地方カードの帯（カード外はラベル）が固定タブの下へ見える位置へスクロール後、`focus({ preventScroll: true })`する
- 入力を直すだけではエラー属性・印・件数は消さず、再保存後の応答で更新する。方式・使用サイズの変更で対象外になった欄の件数除外は別に行う
- 新規／編集の文脈、基本情報、会社・方式・使用サイズ・送料無料のチェック、**表示していない使用タブを含む全送信値**を復元する。保存済みの非送信料金も別途読み込み、空欄へ一律初期化しない。非送信だった未保存編集値の復元方法は未確定

現行の`preview=error`は新規入力途中の表示例。通常の初期タブと違い、JS初期化後は100サイズを選ぶ。

| まとめの行 | 対象id | 個別欄の値・文言 |
|---|---|---|
| 店舗名を入力してください | `store-name` | 空欄／店舗名を入力してください |
| 100サイズ：クール便の追加料金（冷蔵）を確認してください | `shipping-size-100-refrigerated` | abc／0〜99,999円の整数で入力してください |
| 120サイズ：送料が未入力の都道府県が2件あります | `shipping-size-120-pref-01`・`shipping-size-120-pref-47` | 北海道・沖縄県が空欄／各「入力してください」。リンク先は北海道 |
| 送料無料にする金額を確認してください | `store-free-shipping-threshold` | 0／1〜99,999円の整数で入力してください |

まとめ4行・個別5欄。ヤマト160冷蔵は規格外でdisabledのためエラー例にしない（Kenの回答により100へ変更済み）。本番ではこの固定例を移さず、検証結果から出力する。

## 6. 運送会社JSONと本番データへの置き換え

`<script type="application/json" id="store-carrier-data">`を固有JSより前に置く。会社コードをキーとするオブジェクトで、`#store-carrier`のvalueと一致させる。店舗IDをキーとする商品フォームの`store-shipping-data`とは別の契約。次のJSONは現在のPHP出力と同じ全3社の例。規格値の正は[SPEC](../SPEC.md#運送会社の規格正の資料)と本番の規格DBであり、申し送りの例を更新元としてハードコードしない。

```json
{
  "yamato": {
    "name": "ヤマト運輸",
    "sizes": [
      {"size": 60, "maxWeightKg": 2},
      {"size": 80, "maxWeightKg": 5},
      {"size": 100, "maxWeightKg": 10},
      {"size": 120, "maxWeightKg": 15},
      {"size": 140, "maxWeightKg": 20},
      {"size": 160, "maxWeightKg": 25},
      {"size": 180, "maxWeightKg": 30},
      {"size": 200, "maxWeightKg": 30}
    ],
    "cool": {
      "refrigerated": {"maxTotalCm": 120, "maxWeightKg": 15},
      "frozen": {"maxTotalCm": 120, "maxWeightKg": 15}
    }
  },
  "sagawa": {
    "name": "佐川急便",
    "sizes": [
      {"size": 60, "maxWeightKg": 2},
      {"size": 80, "maxWeightKg": 5},
      {"size": 100, "maxWeightKg": 10},
      {"size": 140, "maxWeightKg": 20},
      {"size": 160, "maxWeightKg": 30}
    ],
    "cool": {
      "refrigerated": {"maxTotalCm": 140, "maxWeightKg": 30},
      "frozen": {"maxTotalCm": 140, "maxWeightKg": 30}
    }
  },
  "yupack": {
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
```

| キー | 型・条件 |
|---|---|
| `yamato`・`sagawa`・`yupack` | 会社コードの文字列キー。現行JSONに`other`は含めない |
| `name` | 会社名の文字列 |
| `sizes` | `{size, maxWeightKg}`の配列。数値のsize昇順で全標準サイズを含める。使用サイズで絞らない |
| `size`・`maxWeightKg` | JSONの数値。cmのサイズ区切り・kgの上限。文字列にしない |
| `cool.refrigerated`・`cool.frozen` | 対応時はオブジェクト、非対応時は`null`。空文字・空配列・booleanへ変えない |
| `maxTotalCm`・`maxWeightKg`・任意の`maxLongestCm` | JSONの数値。`maxLongestCm`は現行ゆうパック冷蔵だけ。対象判定は§4、規格表示はこれらの値を使う |

JSONには店舗の金額・使用サイズ・チェック状態を混ぜない。HTMLの復元値がそれらを持つ。PHPは`JSON_UNESCAPED_UNICODE`・`JSON_HEX_TAG`・`JSON_HEX_AMP`・`JSON_HEX_APOS`・`JSON_HEX_QUOT`・`JSON_THROW_ON_ERROR`で出力している。Bladeでもscript要素を壊さない安全な出力と型を維持する。

## 7. JSの依存先と読み込み

共通head・読み込み順・キャッシュ対策は[共通申し送り§2](HANDOFF_CLIENT_COMMON.md#2-headの読み込みと初期化)・[§8](HANDOFF_CLIENT_COMMON.md#8-cssjsの読み込み順とキャッシュ対策)を参照。固有CSSは`store-form.css`、固有JSは`store-form.js`。JSONを先に置き、固有JSはcommon.jsの後に1回読み込む。外部パッケージ・共通アコーディオンには依存しない。

| id・class・属性・name | JSとの関係・維持する構造 |
|---|---|
| `.store-form`・祖先`main` | 操作対象のformと、その外にあるエラーまとめの検索範囲。JSON以外の操作要素は同じform内 |
| `data-sizes-initialized` | 文字列true／false。初回全選択の制御（§2） |
| `#store-carrier-data`・`#store-carrier` | JSON取得・会社コードによる参照（§6） |
| `#store-carrier-rules`・`#carrier-rules-heading` | 規格表示の再構築。見出しは残す。JS生成classは`.form-help`・`.carrier-sizes`・`.carrier-cool` |
| `#store-size-based`・`#store-size-based-help` | 会社未選択中はdisabled、補助文を表示。選択後のオン／オフで方式を切り替える |
| `#store-used-sizes`・`.check-group`・`input[name="used_sizes[]"]` | fieldset内にチェック群。初期値は`input:checked`から読む。JSが`.form-check`のlabelと`#store-size-{size}`、name・valueを再作成する |
| `#store-size-notice` | 外れたサイズの通知。常設`aria-live="polite"`・`aria-atomic="true"` |
| `#shipping-mode-common`・`#shipping-mode-by_size` | 方式fieldset。hiddenとdisabledを同期 |
| `.shipping-table`・`data-shipping-size` | 値commonまたはcm文字列。共通表＋全サイズのfieldsetを事前に出力。表・送料入力要素は切り替えで再作成しない |
| `#shipping-common-table`・`#shipping-panel-{size}` | 共通表／サイズ表。サイズ表は`role="tabpanel"`、使用時`aria-labelledby="shipping-tab-{size}"`、未使用時`aria-label`。panelのidはJS生成タブとの接続先 |
| `#store-size-tabs`・`#shipping-tab-{size}` | `role="tablist"`内の`[role="tab"]`。`aria-controls`・`aria-selected`・tabindexを同期。選択中だけ0、他は-1。←→循環・Home／End、クリックでもフォーカス移動 |
| `[data-size-spec]` | 会社変更でサイズ見出し横の規格を更新、会社未選択はhidden |
| `[data-cool-temperature]` | 値refrigerated／frozen。内側のinputを持つfieldset。§4に従いdisabled＋hidden |
| `[data-cool-unselected]`・`[data-cool-unavailable]`・`[data-cool-help]` | 会社未選択・規格外・料金の補助文の表示切り替え。補助文にはidを付ける |
| `[data-yupack-limit]` | ゆうパック160冷蔵補助文。idは`shipping-size-160-refrigerated-limit`。inputのaria-describedbyを補助文・上限・既存エラーidに同期 |
| `[data-bulk-scope]`・`[data-bulk-input]` | buttonの値all／region、対応する補助inputのid。buttonはtype=button。全国は所属表、地方は祖先`.shipping-region`が入力範囲 |
| `[data-prefecture-fee]`・`.bulk-notice` | 対象の県inputへ一括反映。通知は各表に1つ常設し、`aria-live="polite"`・`aria-atomic="true"` |
| `#store-free-shipping`・`#store-free-shipping-threshold` | チェックに合わせて条件金額をdisabled、値は保持 |
| `#store-save`・formの`aria-busy` | submitボタンの送信中表示・二重送信防止・復帰 |
| `#store-errors`・`#store-errors-heading` | 同じmain内のまとめと行数。まとめはtabindex=-1、初期focus対象 |
| `[data-error-fields]`・行内の`a[href^="#"]` | エラー欄idの空白区切りリスト、リンク先の同期・移動（§5） |
| `[aria-invalid="true"]`・`:disabled`・`aria-describedby`・`.form-error` | 対象エラーの抽出と表示。既存エラー文参照は補助文更新時も残す |
| `.tab-error`・tabの`aria-label`・印の`aria-hidden` | JSが生成する「! 件数」と読み上げ名 |
| `.shipping-region`・`.region-heading`・labelのfor／inputのid・`.sr-only` | まとめリンク時は地方の帯、カード外はinput.labelsの見えるlabelをスクロール基準にする。sr-onlyは位置基準から除外 |
| `--store-tabs-height`・パネル内`input, button`のscroll-margin-top | ResizeObserverでタブ高を設定。focusin後にラベルが固定タブに隠れた場合だけ縦位置を補正。選択tabはクリック・キー・幅変更で横方向にも表示範囲へ戻す |

入力idの体系は基本情報が`store-name`・`store-postal-code`・`store-address`・`store-contact-name`・`store-phone`、通常送料が`shipping-common-pref-{code}`／`shipping-size-{size}-pref-{code}`、追加料金が`shipping-common-{temperature}`／`shipping-size-{size}-{temperature}`。一括入力は`shipping-common-all`／`shipping-size-{size}-all`、地方は同じ接頭辞＋`-{region}-bulk`。通知idは接頭辞＋`-notice`。各ラベル・エラーリンクと一致させる。JSは料金nameを文字列解析せずdata属性で表を識別し、nameに直接依存する選択処理は`used_sizes[]`。

一括入力は確認なしで上書きする。全角数字を計算用に半角化し、0〜99,999の整数だけ反映。成功後は対応する補助欄だけを空にし「N件に入力しました」、不正なら値を残して「0〜99,999の整数を入力してください」。押したボタンのフォーカスを維持する。

### 送信・未保存警告・復帰と検証範囲

基本の流れは[商品フォーム申し送り§7](HANDOFF_CLIENT_PRODUCT_FORM.md#7-jsの依存先と読み込み)と同じ。店舗ではinput／change（会社・方式・使用サイズ・送料無料を含む）と成功した一括入力で変更ありとする。一度変更すると元の値へ戻しても解除しない。

submitでbeforeunloadの警告を解除し、`#store-save`だけをdisabled・「保存中…」、formを`aria-busy="true"`にして通常POST。入力欄全体を無効にしない。2回目のsubmitは止める。`pageshow.persisted`でボタン・文言・aria-busyを戻し、未保存の変更があれば警告を再登録する。

これまでの1280×800・834×1194の確認では、各状態・エラー移動・送信対象・操作を確認済み（[制作記録](../design/CLIENT_STORE_FORM.md#フォーム3回目の制作記録js依存先2026-10-21)）。送信中・二重submit・警告の登録／解除・復帰分岐は一時表示コピーで検証した。会社変更だけ／一括入力だけの実離脱でbeforeunload発火・キャンセル要求は記録したが、**標準ダイアログの実表示・取消は自動操作環境で保持できず、実bfcache復帰も確実に再現していない**。復帰は一時テストでpageshow.persistedを発生させた範囲。通常ブラウザでの最終確認が残る。本番コードに検証用処理は含めていない。今回の申し送り作成ではブラウザ再検証は行わず、現行PHP出力・JS・既存記録を照合した。

## 8. 未確定のまま残っている項目

[SPECの店舗と送料の未確定事項](../SPEC.md#店舗と送料の未確定事項)を正とし、今回の制作完了を業務仕様全体の確定と解釈しない。

- 郵便番号・住所・電話番号の詳細検証、その他の運送会社・独自規格、離島追加料金、複数配送方法・個口拡張、削除・停止時の商品、商品の店舗変更、Masterの店舗画面、追跡URL・お届け時間帯、パートナー情報画面の制作時期
- [QA No.3](../QA_BACKEND.md#no3-購入側注文送料)：料金保持・Laravel側の注文分割／送料計算は確認済み。接続・API・処理タイミング・決済・税額等は残る確認事項。購入画面への店舗名表示はフロントで決める
- サーバーエラー後の非送信値の復元方法、会社別のDB規格IDと画面内のcm別共用表の復元対応、送料無料オフ保存後の条件金額再利用の永続化方法。保存済み料金の保持と画面内の未保存値保持を混同しない
- [QA No.4](../QA_BACKEND.md#no4-運送会社の規格)：DB＋Seeder・初期Master規格編集なしは確認済み。改定の更新処理・適用日時・履歴、将来の編集機能の詳細は未確定
- 商品フォーム側の温度帯別`coolSurchargeSetBySize`（サイズ→{refrigerated, frozen}）・`coolSurchargeSet`（{refrigerated, frozen}）はフロント対応済みだが、キー名・形はバックエンドとの契約確認を継続。[商品フォーム申し送り§6](HANDOFF_CLIENT_PRODUCT_FORM.md#6-店舗の送料jsonと判定)を参照。店舗フォームの規格JSONと取り違えない
- 商品フォームでクール便規格外を注意だけにするか保存エラーにするか、ゆうパックの横倒し不可時の高さ条件は未確定。店舗フォームの160冷蔵欄・150cm補助文は確定済み

組み込み順は[SPEC](../SPEC.md#bladeへの組み込み順2026-10-21kenの決定)・[QA No.8](../QA_BACKEND.md#no8-blade組み込み)を参照。申し送り整備は完了したが、Blade組み込み・担当者への実際の引き渡し・未確定契約の承認はまだ行っていない。
