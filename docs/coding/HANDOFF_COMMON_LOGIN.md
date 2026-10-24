# 共通 ログイン・パスワード再設定・確認コード バックエンドへの申し送り

- 作成日：2026-10-23
- 対象：Master・Clientのログイン系5画面の制作用PHP、`shop-partner/assets/scss/`・`shop-master/assets/scss/`の`common.scss`・`login.scss`、生成CSS（各`assets/css/common.css`・`login.css`。`npm run sass`で作る。Gitでは追跡しない）、各`assets/js/login.js`とMasterの`login-code.js`、Clientの`includes/inc_header.php`（具体的なパスは§1・§9）
- 作成：フロント（Ken・Codex）

仕様の正は[SPECのログインの確定事項](../SPEC.md#ログインの確定事項)と[ログインの画面資料](../design/COMMON_LOGIN.md)。食い違う場合は仕様資料を優先し、フロントへ知らせてほしい。実装の照合元は`013fb74`時点の対象ファイル（作業前`ken-work 819e449`でも差分なし）。

ログイン系はサイドバーと`common.js`を使わない。[Client共通の申し送り](HANDOFF_CLIENT_COMMON.md)のサイドバー・検索・一覧向けの構造を組み込まず、Google Fontsは[共通申し送り§2](HANDOFF_CLIENT_COMMON.md#2-headの読み込みと初期化)、アセットURLのバージョン付けは[§8](HANDOFF_CLIENT_COMMON.md#8-cssjsの読み込み順とキャッシュ対策)の該当部分を参照する。ログイン系の読み込み順は本資料§1を優先する。

「確定」はSPECまたは画面資料にあるKenの決定、「仮」は仮のままでよいとされた文言・値、「案」は画面資料の提案、「未確定」は判断が残る仕様、「未確認」は実機等で確かめていないもの。Masterの行き先の仮置きは、§4に確定事項と分けて記載する。

## 1. 対象の画面とファイル

5画面・PHPは7ファイル。「送信しました」は再設定の申し込みと同じURLの送信後の状態で、別PHPを作らない。

| 画面 | ClientのPHP | MasterのPHP | Sass・生成CSS・JS |
|---|---|---|---|
| ログイン | `shop-partner/login.php` | `shop-master/login.php` | 下表のClient／Master共通一式 |
| 再設定の申し込み | `shop-partner/password-forgot.php` | `shop-master/password-forgot.php` | 同上 |
| 送信しました | `shop-partner/password-forgot.php`の送信後 | `shop-master/password-forgot.php`の送信後 | 同上 |
| 新しいパスワードの設定 | `shop-partner/password-reset.php` | `shop-master/password-reset.php` | 同上 |
| 確認コードの入力（Masterのみ） | — | `shop-master/login-code.php` | Master共通一式＋`shop-master/assets/js/login-code.js` |

| 種類 | Client共通一式 | Master共通一式 |
|---|---|---|
| Sass | `shop-partner/assets/scss/common.scss`・`shop-partner/assets/scss/login.scss` | `shop-master/assets/scss/common.scss`・`shop-master/assets/scss/login.scss` |
| 生成CSS（`npm run sass`で作る。Gitでは追跡しない） | `shop-partner/assets/css/common.css`・`shop-partner/assets/css/login.css` | `shop-master/assets/css/common.css`・`shop-master/assets/css/login.css` |
| JS | `shop-partner/assets/js/login.js` | `shop-master/assets/js/login.js` |

- Masterの`login.php`・`password-forgot.php`・`password-reset.php`は、`$adminType = 'master'`を入れてClientの同名テンプレートを読む。`login-code.php`はMasterに単独で置く
- Masterの`login.scss`はClientの`login.scss`を`@use`する。Master専用のログインレイアウトはない。色はMasterの`common.scss`がClientの共通Sassを読み、ブランド用カスタムプロパティ9つを上書きすることで変わる。Masterの色の値は仮
- Masterの`login.js`はClientのコピー。変更するときは両方を直す（先頭コメント以外の処理は同じ）
- 切り替わるのは上部の管理画面名、`title`、PCのひと言、保持チェックボックスの有無、色。[Master・Clientの違い](../design/COMMON_LOGIN.md#masterclientの違い)を参照
- CSSは`common.css`→`login.css`。JSは`</body>`直前の`login.js`、確認コードだけ`login.js`→`login-code.js`。`common.js`は読まない。PHP内の相対アセットパスは、開いたMaster／Clientのディレクトリを基準にする

## 2. 制作用PHPのうち、Bladeへ移さないもの

| 対象 | 内容 | Bladeでの扱い |
|---|---|---|
| `$previewState`（`?preview=`）と各`$is…State` | §5の制作確認専用の状態分岐。`$codeInvalid`・`$formError`も制作上の状態から生成 | preview判定を移さず、実際の検証・認証結果・セッションのお知らせへ置き換える |
| `$staticExport`・`$pageExtension` | 静的出力時の拡張子切り替え。静的出力ではpreviewなし | 移さず、本番のルートでリンクを生成する |
| 各formの`action="#"` | ログイン・再設定・コード確認・再送の仮の送信先。すべてPOST | 実際の送信先とCSRFを組み込む。`novalidate`のあるフォームはサーバー側で検証し、§5の表示を返す |
| `$email`・hidden・コード欄・送り先の表示例 | `tanaka@example.jp`・`tanaka@example`・`sample-token`・`1234`・`k***@example.jp` | 実際の入力・再設定用トークン・対象メール・伏せた送り先に置き換える。パスワードを復元しない |
| `$resendSeconds` | 通常・再送後60、入力／コードエラー35、期限切れ・再送可能0秒の固定例 | サーバーで残り秒数を計算し、§7の属性・文字・無効状態へ出力する |
| `$adminType`・`$isMaster`・`$adminName`・`$loginTagline` | 管理画面種別による表記・保持欄の切り替え | 制作用PHPの読み込み方式をそのまま移さず、Bladeでの共通化・切り替え方はバックエンドで決める。表示差は維持する |

認証・メール送信・パスワード保存・トークンやコードの照合・回数制限は制作PHPにない。固定値やpreviewを本番の判定に使わない。

## 3. 画面の構成

B案は確定（2026-10-23、Kenの決定）。寸法・文字サイズ・色は[画面資料のレイアウト](../design/COMMON_LOGIN.md#レイアウトb案確定2026-10-23kenの決定)を参照する。

| 順 | 領域 | 構造 |
|---|---|---|
| 1 | ページの枠 | `body.login-page`内の`.login-layout`。1194pxより広い幅は左右分割、以下は上の帯＋フォーム |
| 2 | ブランド | `header.login-brand`内の`p.login-identity`に`span.login-logo`・`span.admin-name`。続いて`p.login-tagline`（1194px以下では非表示） |
| 3 | フォーム領域 | `main.login-main`→`.login-box`に`h1`、必要なお知らせ・エラー、説明文、`form.login-form`、`.login-links` |
| 4 | 入力欄 | `.form-field`に`label.form-label`とinput。パスワードは`.password-field`にinputと`.password-toggle`。補助文`.form-help`・エラー`.form-error`を関連付ける |
| 5 | 確認コードの再送 | 主フォームとは別の`form.resend-form`（POST）に`.resend-button`・`#resend-timer`・`#resend-notice` |

ロゴの差し替え口は`<span class="login-logo">熊日モール</span>`。画像に替える場合はこの要素を置き換える。「送信しました」はフォームなし、無効な再設定リンクもフォームなしで再申し込みリンクを出す。

## 4. フロントで決めた仕様

以下は[SPECの確定事項](../SPEC.md#ログインの確定事項)と[画面資料の項目・状態](../design/COMMON_LOGIN.md#項目)に沿う確定仕様（2026-10-23、Kenの決定）。

- ログインIDはメールアドレス。保守会社（Ken）がアカウントを作り、管理画面にアカウント管理機能は設けない
- 初めて使うときは、アカウント作成後に再設定メールを送り、本人が新しいパスワードを決める。パスワードは8文字以上、確認を含む2回入力。完了後はログイン画面へ戻し、完了のお知らせを出す
- 再設定リンクの有効期限は60分。確認コードの10分とは別の期限
- ログイン状態の保持はClientのみ、初期オフ。Masterには出さない（二段階認証の抜け道になるため）
- ログインに5回続けて失敗したら約1分止める。アカウントのロックはしない。待機中も画面の入力欄・ボタンは無効にしない。回数制限はサーバーで行う
- 認証に失敗しても、メールとパスワードのどちらが違うかを出さない。再設定の申し込みは登録の有無にかかわらず同じ送信後表示とし、入力したメールアドレスも出さない
- Masterはパスワードが合ったあと、メールで送った6桁の確認コードを入力する。有効期限10分、再送は送信から60秒後、再送で前のコードは無効。5回間違えたらログイン画面からやり直す。送り先は一部を伏せる（伏せ方は未確定）
- 未ログインで管理画面を開いたらログイン画面へ、ログイン後は開こうとしていたページへ戻す。直接ログインしたときはClientの受注一覧へ。Masterは未確定のため、**フロントの仮置き：Masterのトップ（画面は未制作。URLはバックエンドが仮に決めてよい）**。Kenの確認待ちで、確定ではない（§10・§11）

Laravelの標準に任せる前提：全フォーム（コード再送・ログアウトも含む）のCSRF、ログイン時のセッションIDの作り直し、メールアドレスとIPの組での連続失敗の集計は標準の仕組みに沿い、独自に作り込まない。今回の「5回・約1分」の設定・回数のリセット等の詳細はバックエンドで決める。標準を使えばこの案件の制限が自動的に付く、という意味ではない。対象は`backend/composer.json`の`^13.0`、`composer.lock`の`v13.38.0`。確認した出典はLaravel 13.x公式の[認証とセッション](https://laravel.com/docs/13.x/authentication#authenticating-users)、[ログイン試行の制限](https://laravel.com/docs/13.x/authentication#login-throttling)、[CSRF](https://laravel.com/docs/13.x/csrf#preventing-csrf-requests)（2026-10-23確認）。本資料では具体的なクラス名・メソッド名は指定しない。

## 5. 状態ごとの出し分けと本番での置き換え

URLは制作確認用で、本番の遷移先ではない。共通画面のMaster版は下表の`shop-partner`を`shop-master`に置き換える。確認コード関連はMasterのみ。

| 画面・preview | 制作確認用URL | 本番で該当する状況・表示 |
|---|---|---|
| ログイン・通常 | `https://shop-manage-2611.ddev.site/shop-partner/login.php` | 通常の入力フォーム |
| `field-error` | `https://shop-manage-2611.ddev.site/shop-partner/login.php?preview=field-error` | メール形式不正とパスワード未入力の例。欄の下にエラー |
| `auth-error` | `https://shop-manage-2611.ddev.site/shop-partner/login.php?preview=auth-error` | 認証の失敗。上部の共通エラー、メールを残しパスワードを空にする |
| `locked` | `https://shop-manage-2611.ddev.site/shop-partner/login.php?preview=locked` | 5回続けて失敗した待機中。アカウントロックを意味しない |
| `password-changed` | `https://shop-manage-2611.ddev.site/shop-partner/login.php?preview=password-changed` | 再設定完了後のお知らせ |
| `code-failed`（Master） | `https://shop-manage-2611.ddev.site/shop-master/login.php?preview=code-failed` | コードを5回間違え、最初からログインし直す |
| 再設定申し込み・通常 | `https://shop-manage-2611.ddev.site/shop-partner/password-forgot.php` | メール入力フォーム |
| `field-error` | `https://shop-manage-2611.ddev.site/shop-partner/password-forgot.php?preview=field-error` | メール未入力の例 |
| `sent` | `https://shop-manage-2611.ddev.site/shop-partner/password-forgot.php?preview=sent` | 登録の有無を明かさず送信後表示。フォームを出さない |
| 新しいパスワード・通常 | `https://shop-manage-2611.ddev.site/shop-partner/password-reset.php` | 有効なリンクからの入力 |
| `field-error` | `https://shop-manage-2611.ddev.site/shop-partner/password-reset.php?preview=field-error` | 8文字未満と確認の不一致の例 |
| `invalid-link` | `https://shop-manage-2611.ddev.site/shop-partner/password-reset.php?preview=invalid-link` | 期限切れ・使用済み。フォームなし、再申し込みへのリンク |
| 確認コード・通常 | `https://shop-manage-2611.ddev.site/shop-master/login-code.php` | コード送信直後。制作例は再送まで60秒 |
| `field-error` | `https://shop-manage-2611.ddev.site/shop-master/login-code.php?preview=field-error` | 6桁でない例（`1234`）。残り35秒は表示例 |
| `code-error` | `https://shop-manage-2611.ddev.site/shop-master/login-code.php?preview=code-error` | コードの不一致。残り35秒は表示例 |
| `expired` | `https://shop-manage-2611.ddev.site/shop-master/login-code.php?preview=expired` | コード期限切れ。上部エラー、再送可能（0秒） |
| `resent` | `https://shop-manage-2611.ddev.site/shop-master/login-code.php?preview=resent` | 再送後のお知らせ。60秒から数え直す |
| `resend-ready` | `https://shop-manage-2611.ddev.site/shop-master/login-code.php?preview=resend-ready` | 再送待機終了。再送可能（0秒） |

本番ではpreviewの分岐を消し、Laravelのバリデーションエラー・認証結果・セッションのお知らせに置き換える。previewがない未入力エラー等も、§8の文で返す。

- 欄のエラーは`p.form-error`を直下に置き、その入力に`aria-invalid="true"`・`aria-describedby="エラーのid"`を付ける。最初の誤りへ`autofocus`。正常時はエラー用属性・文を出さない
- idの対応：`#login-email`→`#login-email-error`、`#login-password`→`#login-password-error`、`#forgot-email`→`#forgot-email-error`、`#reset-password`→`#reset-password-error`、`#reset-password-confirmation`→`#reset-password-confirmation-error`、`#login-code`→`#login-code-error`。labelの`for`も維持する
- `#reset-password`は正常時から`aria-describedby="reset-password-help"`を持つ。エラー時は`reset-password-error`を追加し、補助文との関連を消さない
- 認証の失敗・連続失敗は、メールをエスケープして残し、パスワードは空、パスワード欄へフォーカスする。コード欄は現行どおり通常も`autofocus`を持つ
- フォーム全体の失敗は`.error-summary[role="alert"]`、成功・送信後のお知らせは`.save-status[role="status"]`。欄ごとのエラーと区別する。実際のエラーを固定の成功表示に置き換えない

## 6. フォームのname属性とautocomplete属性

以下は現行テンプレートと[画面資料の項目](../design/COMMON_LOGIN.md#項目)を照合した値。Masterの共通3画面も同じ。autocompleteは指定の決まりと一致し、変更不要。

| 画面 | 欄 | `name` | `autocomplete` | Laravel標準との違い |
|---|---|---|---|---|
| ログイン | メール | `email` | `username` | 標準の認証例と同じname |
| ログイン | パスワード | `password` | `current-password` | 同じ |
| ログイン（Clientのみ） | 保持チェック | `remember`（`value="1"`） | 指定なし | 慣例的な名前。標準認証への真偽値の受け渡しはバックエンドで行う。固定のnameを要求する仕組みではない |
| 再設定申し込み | メール | `email` | `username` | 標準の再設定例と同じname |
| 新しいパスワード | パスワード | `password` | `new-password` | 同じ |
| 新しいパスワード | 確認用パスワード | `password_confirmation` | `new-password` | 同じ |
| 新しいパスワード | トークン（hidden） | `token` | 指定なし | 同じ。再設定用トークンで、CSRF用とは別 |
| 新しいパスワード | メール（hidden） | `email` | 指定なし | nameは同じ。hiddenで送る形は仮置き、送り方はバックエンドで決める |
| 確認コード（Master） | コード | `code` | `one-time-code` | このメール確認コード方式はLaravel本体の標準認証にないため、フロントが決めた名前 |

`email`・`password`・`password_confirmation`・`token`に標準例との名前の違いはない。公式出典は[認証](https://laravel.com/docs/13.x/authentication#authenticating-users)・[保持](https://laravel.com/docs/13.x/authentication#remembering-users)・[パスワード再設定](https://laravel.com/docs/13.x/passwords#resetting-the-password)（13.x、2026-10-23確認）。コードのnameを変える場合はフロントへ知らせてほしい。CSRFは制作フォームに未実装で、本番で別途組み込む。

メールは`type="email"`・`autocapitalize="none"`・`spellcheck="false"`、コードは`type="text"`・`inputmode="numeric"`・`maxlength="6"`を維持する。コードを数値型の入力にしない。

## 7. JSが使うclass・data属性・id

次の依存先は変えないでほしい。必要があればCSS・JSも一緒に調整し、フロントへ知らせてほしい。

| 依存先 | 動き・維持すること |
|---|---|
| `.password-toggle`・`aria-controls`・参照先inputのid | JSが対象欄を取得し、初期の`hidden`を外す。JSがないときはボタンを出さない。`type="button"`を維持 |
| `aria-pressed`・`.icon-show`・`.icon-hide` | `false`では隠したパスワードとeye、`true`では表示したパスワードとeye-off。aria-label・titleは「パスワードを表示」で固定（Kenの決定） |
| `form button[data-busy-label]` | 送信前にパスワードを隠して`aria-pressed="false"`へ。送信開始後にボタンを無効化し、属性の送信中文言へ切り替える。JSが元の文字を`data-label`に保存する |
| `pageshow`の`persisted` | 戻る操作で復元されたときに通常の送信ボタンと元の文言を戻す。`.resend-button`の無効状態は`login-code.js`が残り時間から判定する |
| `.resend-button`・`data-resend-seconds` | 読み込み時に、サーバーが`data-resend-seconds`へ出力した残り秒数と`Date.now()`から終了時刻を求める。毎秒、終了時刻と現在時刻の差から残り秒数を計算するため、タブが裏にあって`setInterval`の実行が遅れても残り秒数がずれない |
| `#resend-timer`内の`.resend-seconds` | 残り秒数の表示。0秒でボタンを有効にし、`aria-describedby`を外してタイマーを`hidden`にする |
| `#resend-notice.sr-only[aria-live="polite"]` | 再送可能になったときだけ「コードを再送できます」と1回知らせる。残り秒数を毎秒読み上げない |

初期表示はサーバーで計算した残り秒数を、ボタンの`data-resend-seconds`と`.resend-seconds`の文字の両方へ同じ値で出す。0より大きいときはボタンに`disabled aria-describedby="resend-timer"`を付け、タイマーは表示する。0ならボタンは有効でその関連付けを付けず、`#resend-timer`に`hidden`を付ける。待機中・0秒・再送後の判定と60秒の制限はサーバー側でも行い、JSの有効／無効だけに任せない。

## 8. 画面に出す文言の一覧

[画面資料のエラーと状態](../design/COMMON_LOGIN.md#エラーと状態)・[項目](../design/COMMON_LOGIN.md#項目)を基準にする。確定／仮／案はその資料とKenの決定に従う。バックエンドの言語ファイル（バリデーション等）でも同じ文面にしてほしい。

| 用途 | 文言 | 扱い |
|---|---|---|
| メール未入力／形式 | 「メールアドレスを入力してください」／「メールアドレスの形式で入力してください」 | 確定 |
| パスワード未入力／8文字未満／確認不一致 | 「パスワードを入力してください」／「パスワードが8文字未満です」／「パスワードが一致しません」 | 確定（8文字未満は2026-10-23、Kenの決定） |
| コード未入力／形式 | 「確認コードを入力してください」／「6桁の数字で入力してください」 | 確定。未入力文の専用previewはない |
| 認証の失敗 | 「メールアドレスまたはパスワードが正しくありません」 | 確定 |
| 5回続けて失敗 | 「しばらくしてからもう一度お試しください」 | 確定 |
| パスワード変更完了 | 「パスワードを変更しました」 | 確定 |
| コードを5回間違えた | 「確認コードの入力に続けて失敗したため、もう一度ログインしてください」 | 仮 |
| コードの不一致 | 「確認コードが正しくありません」 | 案（欄の下へ出す位置も案） |
| コード期限切れ | 「確認コードの有効期限が切れました。コードを再送してください」 | 仮 |
| 再送完了 | 「コードを再送しました。前のコードは使えません。」 | 案 |
| 無効な再設定リンク | 「このリンクは使えません。もう一度再設定を申し込んでください」 | 仮 |
| 再設定の送信後 | 「登録があれば、再設定のメールを送りました。」 | 表示内容は確定。画面資料・コードに合わせ末尾の句点を付ける（SPECは句点なしで内容を記載） |
| 申し込みの説明 | 「登録しているメールアドレスを入力してください。パスワードを再設定するためのメールを送ります。」 | 案 |
| 送信後の補足 | 「メールに記載されたリンクから、新しいパスワードを設定してください。届かない場合は、メールアドレスを確かめてもう一度お申し込みください。」 | 案 |
| パスワードの補助文 | 「8文字以上で入力してください」 | 確定。エラー文とは分ける |
| コードの送り先・説明 | 「k***@example.jp にコードを送りました。メールに記載された6桁のコードを入力してください。有効期限は10分です。」 | 文は案。メールは表示例、伏せ方は未確定。10分は確定 |
| 再送待機／再送可能の読み上げ | 「あと〇秒で再送できます」／「コードを再送できます」 | 画面資料の項目に沿う表示。〇は実際の残り秒数 |

| 用途 | 通常の文言 | 送信中の文言・補足 |
|---|---|---|
| 主ボタン | 「ログイン」／「再設定のメールを送る」／「パスワードを変更する」／「確認する」 | 順に「ログイン中…」／「送信中…」／「変更中…」／「確認中…」（画面資料の項目どおり） |
| 再送ボタン | 「コードを再送する」 | 「再送中…」（現行JS向けの`data-busy-label`。画面資料には送信中文言の個別記載なし、案として引き継ぐ） |
| 目のボタン | 「パスワードを表示」 | aria-label・titleとも固定、確定 |
| リンク | 「パスワードを忘れた方」／「ログイン画面に戻る」／「再設定を申し込む」 | 画面資料どおり |
| 保持欄 | 「ログイン状態を保持する」 | Clientのみ・初期オフ、確定 |

見出し・title・上部表記は確定。`h1`とtitleの画面名は「ログイン」「パスワードの再設定」（送信後も同じ）「新しいパスワードの設定」「確認コードの入力」。titleはClient「{画面名}｜熊日モール パートナー管理画面」、Master「{画面名}｜熊日モール 管理画面」。上部はClient「熊日モール｜パートナー管理画面」、Master「熊日モール｜管理画面」（縦線はCSSで表現）。PCのひと言はClient「受注・商品・店舗をまとめて管理」、Master「熊日モールの運営管理」（Masterは仮）。欄のラベルは§6の欄名を基に、「メールアドレス」「パスワード」「新しいパスワード」「新しいパスワード（確認）」「確認コード（6桁）」とする。

## 9. ログイン後のヘッダー

- 対象は`shop-partner/includes/inc_header.php`。`.admin-name`は「{パートナー名} 管理画面」（確定）。「熊日メディアコム 管理画面」は表示例で、本番はログイン中のパートナー名を使う
- 長い名前は1行で省略し、`title`に全文を残す。本文・属性値の両方をエスケープする。`.box-identity`の`min-width: 0`、`.admin-name`の省略指定、`.box-actions`の幅を縮めない指定と余白を維持し、ヘッダーの高さを変えない
- ログアウトは既存の`.box-actions`内のPOSTのform・buttonを使い、置き場所を変えない。`action="#"`とCSRF未実装は仮のため、本番の送信先とCSRFへ置き換える
- 現在の`backend/resources/views/partner/partials/header.blade.php`にはログアウト処理のTODOがあり、管理画面名も旧「パートナー管理画面」でtitleがない。フロントの現行ヘッダーとの差として、ログイン組み込み時に合わせて取り込んでほしい（今回はバックエンドを変更していない）

## 10. バックエンドで決めてほしいこと

以下はバックエンドで決めて実装し、決めた内容をフロントへ知らせてほしい。フロントがSPEC・画面資料・申し送りへ反映する。`QA_BACKEND.md`には追加しない。[SPECの未確定事項](../SPEC.md#ログインの未確定事項)も参照する。

| 決めてほしいこと | フロントの前提 |
|---|---|
| アカウントの作り方と、作成後の再設定メールの送り方 | 保守会社（Ken）が作る。artisanコマンド等の運用方法を決める。管理画面にアカウント管理は作らず、本人が再設定画面で初回パスワードを決める |
| ユーザーのテーブルとパートナーの紐付け | Clientは所属パートナーの範囲で閲覧・操作する。具体的なテーブル・関連付けは未確定 |
| セッションの保存先と有効期限 | 保持しない場合・Clientで保持する場合を決める。Masterには保持欄を出さない |
| Master・Client間でログイン状態を共有するか、サブドメインとLaravelのつなぎ方 | 1環境につき1つのLaravelアプリ。`backend/routes/web.php`のTODOではローカルは仮に`/partner`、サーバーはサブドメインで分ける予定、ログイン必須化はログイン作業時。フロントからのお願いとして、共有する場合もMasterの確認コードを通らずにMasterへ入れる形は避けてほしい（Masterに保持欄を出さない理由と同じ） |
| 再設定メール・確認コードメールの送信元と文面 | 画面のお知らせとメール本文を混同しない。再設定リンク60分（§4・SPECの確定事項）、コード10分の条件にそろえる |
| 確認コードの生成・保存・期限・回数上限の実装 | このメール確認コード方式はLaravel本体の標準にない。6桁・10分・再送60秒・再送で旧コード無効・5回でログインし直しは確定。認証失敗・連続失敗・コードの回数の数え方やリセット、止める正確な秒数等の実装を決める。ログインの連続失敗はメールとIPの組を前提とする |
| 送り先メールアドレスをどこまで伏せるか | 一部を伏せることは確定。`k***@example.jp`は表示例、具体的な伏せ方は未確定 |
| 商品一覧の仮のパートナーIDの置き換え | `backend/app/Http/Controllers/Partner/ProductController.php`の43〜44行目にTODOと`$partnerId = 1;`がある（Bladeではなくコントローラー）。ログイン中のパートナーへ置き換え、閲覧範囲を制御する |
| ログイン系画面をブラウザに保存させない指定 | `Cache-Control: no-store`など。Safariの「戻る」で送信中の表示や入力が残らないようにする対策として、指定方法・対象画面を決める。§7の復元処理により、Safariの「戻る」で送信中の表示やパスワードが残らないことは確認済み（2026-10-23、Ken） |
| Masterで直接ログインしたときの行き先 | §4のフロントの仮置き（Kenの確認待ち。確定ではない）はMasterのトップ（未制作）。URLはバックエンドが仮に決めてよい。開こうとしていたページへ戻す確定仕様は維持する |

## 11. 未確定・未確認のまま残っている項目

仕様上の論点は[SPECの未確定事項](../SPEC.md#ログインの未確定事項)・[画面資料の未確定事項](../design/COMMON_LOGIN.md#未確定事項)を参照する。§10の決定依頼と、以下の仮・案・未確認を確定扱いにしない。

- **未確定**：送り先の伏せ方、Masterの直接ログイン後の行き先（Masterの制作のときに決める）、認証失敗・連続失敗・コード入力の回数の数え方と止める正確な秒数等の実装。ログイン失敗はメールとIPの組、5回・約1分という前提の中で実装を決める。アカウント作成・セッション・共有・メール・コード実装等の詳細は§10
- **仮**：再設定リンクの期限切れ・使用済み、確認コードの期限切れ・5回失敗の文言、Masterのひと言・色。Masterのトップへの遷移もフロントの仮置き（Kenの確認待ち。確定ではない。内容は§4）
- **案**：申し込みの説明・送信後の補足、コード送信先の説明、コード不一致の文と表示位置、再送完了の文等。文言の区分は§8を参照する
- **確認済み（Safari、2026-10-23、Ken）**：パスワード欄のAutoFillの鍵のアイコンと目のボタンは重ならない。スマホ幅の上の帯は崩れない。「戻る」で戻っても送信中の表示やパスワードは残らない。Chromium等での確認は[画面資料の制作記録](../design/COMMON_LOGIN.md#b案の制作確認2026-10-23)を参照する
