<?php
// 制作確認用。確認コードの照合・再送などの業務処理は持たない。
$pageExtension = ($staticExport ?? false) ? 'html' : 'php';

// 制作時の表示確認専用。Laravel・Bladeでは使用せず、バックエンド側の状態分岐へ置き換える。
$previewState = ($staticExport ?? false) ? '' : ($_GET['preview'] ?? '');
$isFieldErrorState = $previewState === 'field-error';
$isCodeErrorState = $previewState === 'code-error';
$isExpiredState = $previewState === 'expired';
$isResentState = $previewState === 'resent';
$isResendReadyState = $previewState === 'resend-ready';

// 再送できるまでの残り秒数。送信から60秒。Bladeではサーバーで計算した値を出す。
$resendSeconds = match (true) {
    $isExpiredState, $isResendReadyState => 0,
    $isFieldErrorState, $isCodeErrorState => 35,
    default => 60,
};
$codeInvalid = $isFieldErrorState || $isCodeErrorState;
?>
<!doctype html>
<!-- =======================================
 * 熊日モール Master 確認コードの入力
 * URL: /shop-master/login-code.<?= $pageExtension . PHP_EOL ?>
 * Referenced in: /docs/design/COMMON_LOGIN.md・/index.html
 * Created: 2026-10-23
 * Last updated: 2026-10-23
 * ======================================= -->
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>確認コードの入力｜熊日モール 管理画面</title>
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
    <div class="login-layout">
      <header class="login-brand">
        <p class="login-identity">
          <span class="login-logo">熊日モール</span>
          <span class="admin-name">管理画面</span>
        </p>
        <p class="login-tagline">熊日モールの運営管理</p>
      </header>
      <main class="login-main">
        <div class="login-box">
          <h1>確認コードの入力</h1>

          <?php if ($isResentState): ?>
            <p class="save-status" role="status">コードを再送しました。前のコードは使えません。</p>
          <?php endif; ?>
          <?php if ($isExpiredState): ?>
            <!-- 文言は仮（2026-10-23、Ken了承）。 -->
            <div class="error-summary" role="alert">
              <p>確認コードの有効期限が切れました。コードを再送してください</p>
            </div>
          <?php endif; ?>

          <!-- 送り先の伏せ方はバックエンドで決める。表示例。 -->
          <p class="login-lead"><span class="code-destination">k***@example.jp</span> にコードを送りました。メールに記載された6桁のコードを入力してください。有効期限は10分です。</p>

          <!-- action・nameは制作例。照合・期限・回数の制限はバックエンドが組み込む。5回間違えたらログイン画面へ戻す（login.php?preview=code-failed）。 -->
          <form class="login-form" method="post" action="#" novalidate>
            <div class="form-field code-field">
              <label class="form-label" for="login-code">確認コード（6桁）</label>
              <input type="text" id="login-code" name="code" value="<?= $isFieldErrorState ? '1234' : '' ?>" inputmode="numeric" autocomplete="one-time-code" maxlength="6"<?= $codeInvalid ? ' aria-invalid="true" aria-describedby="login-code-error"' : '' ?> autofocus />
              <?php if ($isFieldErrorState): ?>
                <p class="form-error" id="login-code-error">6桁の数字で入力してください</p>
              <?php elseif ($isCodeErrorState): ?>
                <p class="form-error" id="login-code-error">確認コードが正しくありません</p>
              <?php endif; ?>
            </div>

            <button class="login-submit" type="submit" data-busy-label="確認中…">確認する</button>
          </form>

          <!-- 再送はメールを送る操作のため、リンクではなくPOSTのbutton。残り秒数はlogin-code.jsが数える。 -->
          <form class="resend-form" method="post" action="#">
            <button class="resend-button" type="submit" data-resend-seconds="<?= $resendSeconds ?>" data-busy-label="再送中…"<?= $resendSeconds > 0 ? ' disabled aria-describedby="resend-timer"' : '' ?>>コードを再送する</button>
            <span class="resend-timer" id="resend-timer"<?= $resendSeconds > 0 ? '' : ' hidden' ?>>あと<span class="resend-seconds"><?= $resendSeconds ?></span>秒で再送できます</span>
            <span class="sr-only" id="resend-notice" aria-live="polite"></span>
          </form>

          <p class="login-links"><a href="login.<?= $pageExtension ?>">ログイン画面に戻る</a></p>
        </div>
      </main>
    </div>
    <script src="assets/js/login.js"></script>
    <script src="assets/js/login-code.js"></script>
  </body>
</html>
