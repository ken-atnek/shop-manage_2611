<?php
// 制作確認用。認証・検証などの業務処理は持たない。
$pageExtension = ($staticExport ?? false) ? 'html' : 'php';

// Master・Clientの共通テンプレート。shop-master/login.phpから$adminType = 'master'で読み込む。
$isMaster = ($adminType ?? 'client') === 'master';
$adminName = $isMaster ? '管理画面' : 'パートナー管理画面';

// 制作時の表示確認専用。Laravel・Bladeでは使用せず、バックエンド側の状態分岐へ置き換える。
$previewState = ($staticExport ?? false) ? '' : ($_GET['preview'] ?? '');
$isFieldErrorState = $previewState === 'field-error';
$isAuthErrorState = $previewState === 'auth-error';
$isLockedState = $previewState === 'locked';
$isPasswordChangedState = $previewState === 'password-changed';
$isCodeFailedState = $isMaster && $previewState === 'code-failed';

// 認証の失敗・連続失敗ではメールアドレスを残し、パスワードは空にする。値は表示例。
$email = ($isAuthErrorState || $isLockedState) ? 'tanaka@example.jp' : ($isFieldErrorState ? 'tanaka@example' : '');
$formError = match (true) {
    $isAuthErrorState => 'メールアドレスまたはパスワードが正しくありません',
    $isLockedState => 'しばらくしてからもう一度お試しください',
    $isCodeFailedState => '確認コードの入力に続けて失敗したため、もう一度ログインしてください',
    default => '',
};
?>
<!doctype html>
<!-- =======================================
 * 熊日モール Master・Client共通 ログイン
 * URL: /shop-partner/login.<?= $pageExtension . PHP_EOL ?>
 * Referenced in: /shop-master/login.php・/docs/design/COMMON_LOGIN.md・/index.html
 * 編集元: /shop-partner/login.php（Masterは/shop-master/login.phpから読み込む）
 * Created: 2026-10-23
 * Last updated: 2026-10-23
 * ======================================= -->
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ログイン｜熊日モール <?= $adminName ?></title>
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
        <h1>ログイン</h1>

        <?php if ($isPasswordChangedState): ?>
          <p class="save-status" role="status">パスワードを変更しました</p>
        <?php endif; ?>
        <?php if ($formError !== ''): ?>
          <div class="error-summary" role="alert">
            <p><?= $formError ?></p>
          </div>
        <?php endif; ?>

        <!-- action・nameは制作例。認証・入力チェック・連続失敗の制限はバックエンドが組み込む。 -->
        <form class="login-form" method="post" action="#" novalidate>
          <div class="form-field">
            <label class="form-label" for="login-email">メールアドレス</label>
            <input type="email" id="login-email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" autocomplete="username" autocapitalize="none" spellcheck="false"<?= $isFieldErrorState ? ' aria-invalid="true" aria-describedby="login-email-error" autofocus' : '' ?> />
            <?php if ($isFieldErrorState): ?>
              <p class="form-error" id="login-email-error">メールアドレスの形式で入力してください</p>
            <?php endif; ?>
          </div>

          <div class="form-field">
            <label class="form-label" for="login-password">パスワード</label>
            <div class="password-field">
              <input type="password" id="login-password" name="password" autocomplete="current-password"<?= $isFieldErrorState ? ' aria-invalid="true" aria-describedby="login-password-error"' : '' ?><?= ($isAuthErrorState || $isLockedState) ? ' autofocus' : '' ?> />
              <!-- login.jsが表示する。JSが使えないときは出さない。 -->
              <button class="password-toggle" type="button" aria-controls="login-password" aria-pressed="false" aria-label="パスワードを表示" title="パスワードを表示" hidden>
                <svg class="icon-show" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" /><circle cx="12" cy="12" r="3" /></svg>
                <svg class="icon-hide" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49" /><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242" /><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143" /><path d="m2 2 20 20" /></svg>
              </button>
            </div>
            <?php if ($isFieldErrorState): ?>
              <p class="form-error" id="login-password-error">パスワードを入力してください</p>
            <?php endif; ?>
          </div>

          <?php if (!$isMaster): ?>
            <!-- Clientのみ。Masterは二段階認証の抜け道になるため出さない。 -->
            <label class="form-check">
              <input type="checkbox" name="remember" value="1" />
              <span>ログイン状態を保持する</span>
            </label>
          <?php endif; ?>

          <button class="login-submit" type="submit" data-busy-label="ログイン中…">ログイン</button>
        </form>

        <p class="login-links"><a href="password-forgot.<?= $pageExtension ?>">パスワードを忘れた方</a></p>
      </div>
    </main>
    <script src="assets/js/login.js"></script>
  </body>
</html>
