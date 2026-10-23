<?php
// 制作確認用。メール送信・入力チェックなどの業務処理は持たない。
$pageExtension = ($staticExport ?? false) ? 'html' : 'php';

// Master・Clientの共通テンプレート。shop-master/password-forgot.phpから$adminType = 'master'で読み込む。
$isMaster = ($adminType ?? 'client') === 'master';
$adminName = $isMaster ? '管理画面' : 'パートナー管理画面';
$loginTagline = $isMaster ? '熊日モールの運営管理' : '受注・商品・店舗をまとめて管理';

// 制作時の表示確認専用。Laravel・Bladeでは使用せず、バックエンド側の状態分岐へ置き換える。
$previewState = ($staticExport ?? false) ? '' : ($_GET['preview'] ?? '');
$isFieldErrorState = $previewState === 'field-error';
$isSentState = $previewState === 'sent';
?>
<!doctype html>
<!-- =======================================
 * 熊日モール Master・Client共通 パスワードの再設定（申し込み・送信しました）
 * URL: /shop-partner/password-forgot.<?= $pageExtension . PHP_EOL ?>
 * Referenced in: /shop-master/password-forgot.php・/docs/design/COMMON_LOGIN.md・/index.html
 * 編集元: /shop-partner/password-forgot.php（Masterは/shop-master/password-forgot.phpから読み込む）
 * Created: 2026-10-23
 * Last updated: 2026-10-23
 * ======================================= -->
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>パスワードの再設定｜熊日モール <?= $adminName ?></title>
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
          <span class="admin-name"><?= $adminName ?></span>
        </p>
        <p class="login-tagline"><?= $loginTagline ?></p>
      </header>
      <main class="login-main">
        <div class="login-box">
          <h1>パスワードの再設定</h1>

          <?php if ($isSentState): ?>
            <!-- 登録の有無にかかわらず同じ表示にする。入力したメールアドレスも出さない。 -->
            <p class="save-status" role="status">登録があれば、再設定のメールを送りました。</p>
            <p class="login-lead">メールに記載されたリンクから、新しいパスワードを設定してください。届かない場合は、メールアドレスを確かめてもう一度お申し込みください。</p>
          <?php else: ?>
            <p class="login-lead">登録しているメールアドレスを入力してください。パスワードを再設定するためのメールを送ります。</p>

            <!-- action・nameは制作例。メール送信・入力チェックはバックエンドが組み込む。 -->
            <form class="login-form" method="post" action="#" novalidate>
              <div class="form-field">
                <label class="form-label" for="forgot-email">メールアドレス</label>
                <input type="email" id="forgot-email" name="email" value="" autocomplete="username" autocapitalize="none" spellcheck="false"<?= $isFieldErrorState ? ' aria-invalid="true" aria-describedby="forgot-email-error" autofocus' : '' ?> />
                <?php if ($isFieldErrorState): ?>
                  <p class="form-error" id="forgot-email-error">メールアドレスを入力してください</p>
                <?php endif; ?>
              </div>

              <button class="login-submit" type="submit" data-busy-label="送信中…">再設定のメールを送る</button>
            </form>
          <?php endif; ?>

          <p class="login-links"><a href="login.<?= $pageExtension ?>">ログイン画面に戻る</a></p>
        </div>
      </main>
    </div>
    <script src="assets/js/login.js"></script>
  </body>
</html>
