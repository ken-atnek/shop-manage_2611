# Client受注一覧 UI UXレビュー報告

- 報告日：2026-10-19
- レビュー実施日：2026-10-18
- 対象：Client受注一覧
- 対象URL：`/shop-partner/orders.php`
- 実施者：Codex
- 状態：レビューのみ。PHP・Sass・CSS・JavaScriptは未変更

## 目的と結論

Client受注一覧について、案件固有の仕様・デザイントークンとVercel Web Interface Guidelinesを基準に、実装コードとブラウザ表示を確認した。

基本レイアウト、配色、HTMLの意味構造、一覧内の主要操作は良好で、1280px・1024px・834pxの確認範囲では重大な表示崩れはなかった。一方、バックエンド引き渡し前に優先して確認したい項目がある。

1. ステータスメニュー内でキーボードフォーカスが見えない
2. ステータス変更の保存方式と保存状態が未確定
3. 詳細検索の入力エラー表示領域が未実装
4. 0件・読込中・通信エラー等の一覧状態が未整理

## 優先度

| 優先度 | 扱い |
|---|---|
| P1 | バックエンド引き渡し前に修正または仕様確定したい |
| P2 | 共通UIとしてほかの画面へ展開する前に改善したい |
| P3 | 表示への影響は小さいが、保守性やガイドライン準拠のため整理したい |

## P1 バックエンド引き渡し前に確認

### 1 ステータス選択肢のフォーカス枠が見えない

- 対象：`scss/_ui.scss:98-131`
- 関連：`scss/_reset.scss:127-134`
- 確認結果：ステータスメニューを開き、矢印キーで選択肢を移動するとradioへフォーカスは移るが、ブラウザ上ではフォーカス枠が見えない
- 原因：radioの共通outlineは外側へ5px出るが、`.list-wrapper`と`.selectbox__panel`の`overflow: hidden`で切れる。さらにlabelがradioの前面に重なる
- 最小対応案：`input[type="radio"]:focus-visible + label`へ、内側に収まるoutlineまたはbox-shadowを設定する
- 備考：矢印キーによる選択、Enterで閉じる処理、ボタンへのフォーカス復帰自体は正常

### 2 ステータス変更が保存済みに見える

- 対象：`shop-partner/assets/js/order.js:36-53`
- 関連：`shop-partner/orders.php:215-254`、`docs/design/CLIENT_ORDER_LIST.md:171`
- 現状：radioを選ぶと、表示文字・色・hidden値・aria-labelがその場で変わる
- 未実装：通信、保存中表示、成功表示、失敗時の復元、最終更新日時の変更
- リスク：利用者には保存済みに見えるが、実際には永続化されていない状態が発生する
- 必要な判断：自動保存か、明示的な保存ボタン方式か
- 自動保存の場合：保存中、成功、失敗、再試行、操作中の無効化、`aria-live`通知を定義する
- 明示保存の場合：未保存状態、保存・取消操作、画面離脱時の警告を定義する
- 備考：現行仕様でも保存方法は未確定であり、現在のフロント実装の単純な不具合ではなく、仕様決定が必要な項目

### 3 注文日の入力エラー表示領域がない

- 対象：`shop-partner/orders.php:83-90`
- 仕様：`docs/design/CLIENT_ORDER_LIST.md:134`
- 現状：開始日と終了日の入力欄はあるが、日付範囲の逆転等を表示するエラー文の受け皿がない
- 対応案：日付fieldset内にエラー文領域を用意し、両入力欄から`aria-describedby`で関連付ける。エラー時の属性・classも決める
- 備考：判定処理はバックエンドと揃える。フロントでは表示構造と状態だけ準備すればよい

### 4 一覧の主要状態が未整理

- 仕様：`docs/design/CLIENT_ORDER_LIST.md:165`、`docs/SPEC.md:170-205`
- 現状：通常の注文あり状態だけを実装している
- 未整理：受注データなし、検索結果0件、読込中、通信エラー、権限による操作不可、保存中・成功・失敗
- 対応案：少なくとも「受注データなし」「検索結果0件」「通信エラー」の構造・文言・次の操作を決める
- 備考：バックエンド処理は対象外。Bladeへ組み込む担当者が状態分岐を判断できる資料とHTML構造を用意する

## P2 共通UIへ展開する前に改善

### 5 一部の操作領域がタッチ操作の推奨サイズより小さい

ブラウザで確認した表示サイズは次のとおり。

| 操作 | 実測の高さ |
|---|---:|
| サイトを確認 | 21px |
| ログアウト | 37px |
| サイドメニュー | 39px |
| ステータスボタン | 36px |
| 注文の展開ボタン | 40px |
| 入稿画像の拡大・ダウンロード | 40px |
| 検索・詳細検索 | 44px |

- 対象：`shop-partner/assets/scss/common.scss:120-143`、`common.scss:162-189`、`scss/_ui.scss:189-199`、`shop-partner/assets/scss/order.scss:599-634`、`order.scss:715-729`
- 判断点：834px幅をタブレットのタッチ操作対象とするなら、主要操作を44px程度へ広げたい
- 最小対応案：見た目のアイコンや文字サイズは変えず、padding・min-height・疑似要素等でクリック領域だけを広げる

### 6 メインコンテンツへのスキップリンクがない

- 対象：`shop-partner/orders.php:30-34`
- 現状：キーボード利用者は、ページを開くたびにヘッダーとサイドメニューを通過する必要がある
- 対応案：body先頭付近に「メインコンテンツへ移動」を置き、`main`へidを付ける。通常時は非表示、フォーカス時だけ表示する

### 7 動きを減らす設定が一部へ反映されない

- 対象：`scss/_ui.scss:40-59`、`scss/_ui.scss:95-109`、`scss/_ui.scss:144`、`scss/_ui.scss:235-263`、`scss/_reset.scss:35-43`
- 良好：詳細検索と注文の展開行は`prefers-reduced-motion: no-preference`内だけでアニメーションする
- 問題：ステータスメニューのトランジションと、htmlの`scroll-behavior: smooth`が動きを減らす設定でも残る
- 対応案：トランジションを`no-preference`内へ移し、変化させるプロパティを明示する。スムーススクロールも同様に限定する

### 8 メモ保存ボタンの読み上げ名が重複する

- 対象：`shop-partner/orders.php:318`、`459`、`574`
- 現状：複数注文を展開すると、読み上げ上はすべて「保存」になる
- 最小対応案：見た目の文言は「保存」のまま、`aria-label="注文#2610170112345のメモを保存"`のように注文を識別できる名前を付ける

### 9 閉じた展開行の画像も先に読み込まれる

- 対象：`shop-partner/orders.php:280`、`295`、`436`
- 現状：展開行は初期状態で閉じているが、サムネイル画像は通常読み込みされる
- リスク：注文数・入稿画像数が増えた場合、初期表示時の通信量が増える
- 最小対応案：サムネイルへ`loading="lazy"`を付ける。必要に応じて`decoding="async"`も検討する

## P3 軽微な整理

### 10 検索用入力の補助属性

- 対象：`shop-partner/orders.php:63-68`、`87-90`、`118-128`
- 候補：検索条件の入力へ`autocomplete="off"`、メール検索へ`spellcheck="false"`を指定する
- 補足：ユーザー本人の連絡先を入力するフォームではなく、注文検索用の条件であることを前提とする

### 11 無効なwidth指定が生成CSSに残る

- 対象：`scss/_ui.scss:91`
- 現状：`width: 3var (--corner-radius);`は無効なCSSとして出力される
- 表示影響：`apply-status-box`側の`width: 36px`で上書きされるため、現在の表示には影響していない
- 対応案：無効な指定を削除するか、意図した計算式へ修正する

### 12 ダイアログ内のスクロール連鎖

- 対象：`shop-partner/assets/scss/order.scss:768-780`
- 現状：dialog自体は画面内に収まり、Esc・閉じるボタン・背景クリックで閉じられる
- 改善案：ダイアログ内でスクロールが発生する場合に備え、`overscroll-behavior: contain`を検討する

## 確認済みの良好な点

- 1280×800、1024×768、834×1194でページ全体の横方向のはみ出しなし
- 1280px・1024pxでは注文一覧内の横スクロールなし
- 834pxでは注文一覧の内部だけ横スクロールし、列見出しと注文行が一緒に動く
- 横スクロール領域はTabでフォーカスでき、左右キーで移動可能
- KPI、検索、一覧、注文カードの情報階層が明確
- `main`、見出し、`table`、`label`、`button`、`a`、`dialog`を用途に合わせて使用
- 商品明細は読み上げ用の列見出しを持つ
- ステータスは色だけでなく文字ラベルでも識別できる
- ステータスの矢印キー、Enter、Esc、外側クリック、フォーカス復帰が動作する
- 閉じた詳細行は`inert`で操作・読み上げ対象から外れる
- 入稿画像dialogはEscで閉じ、元の拡大ボタンへフォーカスが戻る
- 詳細検索の開閉と、詳細条件だけを対象としたクリア処理が動作する
- 配色・罫線・ボタン種別・ステータス色は`DESIGN_TOKENS.md`と一致
- 1280px・1024px・834pxの確認中、ブラウザコンソールに警告・エラーなし
- `shop-partner/assets/js/order.js`の構文チェック正常

## 今回未確認の範囲

- Safari・Firefoxでの表示と操作
- VoiceOver等の読み上げソフト実機
- タッチ端末実機
- OSの「視差効果を減らす」を実際に切り替えた表示
- 入稿画像のダウンロード完了
- メモ保存、検索実行、KPI切り替え、ステータス保存等のバックエンド処理
- PHP構文チェックとSass再コンパイル。今回はレビューのみでファイルを変更していないため未実施

## 参照した資料と実装

- `docs/SPEC.md`
- `DESIGN_TOKENS.md`
- `docs/design/DESIGN.md`
- `docs/design/CLIENT_ORDER_LIST.md`
- `docs/coding/LAYOUT_TEMPLATE.md`
- `docs/coding/IMPLEMENTATION_REFERENCE.md`
- `docs/MEMORY.md`
- `shop-partner/orders.php`
- `shop-partner/includes/inc_header.php`
- `shop-partner/includes/inc_sidebar.php`
- `shop-partner/assets/scss/common.scss`
- `shop-partner/assets/scss/order.scss`
- `shop-partner/assets/js/order.js`
- `scss/_reset.scss`
- `scss/_ui.scss`
- [Vercel Web Interface Guidelines](https://raw.githubusercontent.com/vercel-labs/web-interface-guidelines/main/command.md)

## Claudeへの確認依頼

コードは変更せず、次の観点で再確認する。

1. 各指摘を「妥当」「誤検知」「仕様判断が必要」に分類する
2. `docs/SPEC.md`、`DESIGN_TOKENS.md`、`docs/design/CLIENT_ORDER_LIST.md`と矛盾する提案がないか確認する
3. P1の見落とし、または優先度の過不足がないか確認する
4. 現行実装の不具合と、バックエンド実装前の未確定仕様を分けて報告する
5. 追加指摘は`ファイル:行番号`、影響、最小対応案の順で記載する
6. 全面リファクタや、依頼範囲外の画面変更は提案しない

推奨する対応順は、ステータス保存方式の確定、ステータスメニューのフォーカス表示、入力エラー領域、一覧の空・エラー状態、タッチ領域の順とする。
