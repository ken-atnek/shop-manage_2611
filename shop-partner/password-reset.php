<?php
// 制作確認用。パスワードの保存・トークンの確認などの業務処理は持たない。
$pageExtension = ($staticExport ?? false) ? 'html' : 'php';

// Master・Clientの共通テンプレート。shop-master/password-reset.phpから$adminType = 'master'で読み込む。
$isMaster = ($adminType ?? 'client') === 'master';
$adminName = $isMaster ? '管理画面' : 'パートナー管理画面';

// 制作時の表示確認専用。Laravel・Bladeでは使用せず、バックエンド側の状態分岐へ置き換える。
$previewState = ($staticExport ?? false) ? '' : ($_GET['preview'] ?? '');
$isFieldErrorState = $previewState === 'field-error';
$isInvalidLinkState = $previewState === 'invalid-link';

// 目のアイコンは2つの欄で同じ。
$eyeIcons = '<svg class="icon-show" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" /><circle cx="12" cy="12" r="3" /></svg>'
    . '<svg class="icon-hide" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49" /><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242" /><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143" /><path d="m2 2 20 20" /></svg>';
?>
<!doctype html>
<!-- =======================================
 * 熊日モール Master・Client共通 新しいパスワードの設定
 * URL: /shop-partner/password-reset.<?= $pageExtension . PHP_EOL ?>
 * Referenced in: /shop-master/password-reset.php・/docs/design/COMMON_LOGIN.md・/index.html
 * 編集元: /shop-partner/password-reset.php（Masterは/shop-master/password-reset.phpから読み込む）
 * Created: 2026-10-23
 * Last updated: 2026-10-23
 * ======================================= -->
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>新しいパスワードの設定｜熊日モール <?= $adminName ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;600&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="assets/css/common.css" />
    <link rel="stylesheet" href="assets/css/login.css" />
  </head>

  <body class="login-page">
    <main class="login-main">
      <div class="login-box">
        <p class="login-identity">
          <span class="service-name">熊日モール</span>
          <span class="admin-name"><?= $adminName ?></span>
        </p>
        <h1>新しいパスワードの設定</h1>

        <?php if ($isInvalidLinkState): ?>
          <!-- 期限切れ・使用済みのリンク。文言は仮（2026-10-23、Ken了承）。 -->
          <div class="error-summary" role="alert">
            <p>このリンクは使えません。もう一度再設定を申し込んでください</p>
          </div>
          <p class="login-links"><a href="password-forgot.<?= $pageExtension ?>">再設定を申し込む</a></p>
        <?php else: ?>
          <!-- action・nameは制作例。完了後はログイン画面へ戻し「パスワードを変更しました」を出す（login.php?preview=password-changed）。 -->
          <form class="login-form" method="post" action="#" novalidate>
            <!-- トークンとメールアドレスの送り方はバックエンドで決める。Laravel標準の再設定フォームに合わせた仮置き。 -->
            <input type="hidden" name="token" value="sample-token" />
            <input type="hidden" name="email" value="tanaka@example.jp" />

            <div class="form-field">
              <label class="form-label" for="reset-password">新しいパスワード</label>
              <div class="password-field">
                <input type="password" id="reset-password" name="password" autocomplete="new-password" aria-describedby="reset-password-help<?= $isFieldErrorState ? ' reset-password-error' : '' ?>"<?= $isFieldErrorState ? ' aria-invalid="true" autofocus' : '' ?> />
                <button class="password-toggle" type="button" aria-controls="reset-password" aria-pressed="false" aria-label="パスワードを表示" title="パスワードを表示" hidden><?= $eyeIcons ?></button>
              </div>
              <p class="form-help" id="reset-password-help">8文字以上で入力してください</p>
              <?php if ($isFieldErrorState): ?>
                <p class="form-error" id="reset-password-error">8文字以上で入力してください</p>
              <?php endif; ?>
            </div>

            <div class="form-field">
              <label class="form-label" for="reset-password-confirmation">新しいパスワード（確認）</label>
              <div class="password-field">
                <input type="password" id="reset-password-confirmation" name="password_confirmation" autocomplete="new-password"<?= $isFieldErrorState ? ' aria-invalid="true" aria-describedby="reset-password-confirmation-error"' : '' ?> />
                <button class="password-toggle" type="button" aria-controls="reset-password-confirmation" aria-pressed="false" aria-label="パスワードを表示" title="パスワードを表示" hidden><?= $eyeIcons ?></button>
              </div>
              <?php if ($isFieldErrorState): ?>
                <p class="form-error" id="reset-password-confirmation-error">パスワードが一致しません</p>
              <?php endif; ?>
            </div>

            <button class="login-submit" type="submit" data-busy-label="変更中…">パスワードを変更する</button>
          </form>
        <?php endif; ?>

        <p class="login-links"><a href="login.<?= $pageExtension ?>">ログイン画面に戻る</a></p>
      </div>
    </main>
    <script src="assets/js/login.js"></script>
  </body>
</html>
