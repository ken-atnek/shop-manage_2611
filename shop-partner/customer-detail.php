<?php
// 制作確認用。DB・権限判定・顧客IDの識別は実装しない。
$currentPage = 'customers';
$pageExtension = ($staticExport ?? false) ? 'html' : 'php';
$previewState = ($staticExport ?? false) ? '' : ($_GET['preview'] ?? '');
$isSingleState = $previewState === 'single';
$isNotFoundState = $previewState === 'not-found';
$isErrorState = $previewState === 'error';

// すべて架空の表示例。一覧の実データやIDには連動していない。
$latestBuyer = [
    'name' => 'サンプル 顧客詳細の表示確認用の購入者',
    'postal' => '000-0000',
    'address' => '架空県サンプル市サンプル町1-2-3 サンプル建物101号室',
    'phone' => '000-0000-0001',
];
$previousBuyer = [
    'name' => 'サンプル 顧客詳細の表示確認用の購入者',
    'postal' => '000-0000',
    'address' => '架空県見本市旧サンプル町4-5-6 旧サンプル建物202号室',
    'phone' => '000-0000-0011',
];
$giftRecipient = [
    'name' => 'ギフト 見本花子',
    'postal' => '000-0000',
    'address' => '架空県見本市ギフト町7-8-9 見本建物303号室',
    'phone' => '000-0000-0022',
];
$longRecipient = [
    'name' => '表示確認 長い住所の届け先',
    'postal' => '000-0000',
    'address' => '架空県サンプル市長い住所の折り返しを確認するためのサンプル町10-11-12 サンプル共同配送センター第二別館サンプルギフト受付窓口・表示確認用の長い建物名12345号室',
    'phone' => '000-0000-0033',
];
$customerEmail = 'customer.detail@example.com';
$orders = [
    ['number' => '2610210112345', 'datetime' => '2026-10-21T10:30:00+09:00', 'dateLabel' => '2026/10/21 10:30', 'store' => 'サンプル店舗A', 'amount' => '¥12,800', 'status' => 'new', 'statusLabel' => '新規受付', 'buyer' => $latestBuyer, 'destination' => $latestBuyer],
    ['number' => '2610200112346', 'datetime' => '2026-10-20T14:15:00+09:00', 'dateLabel' => '2026/10/20 14:15', 'store' => 'サンプル店舗B', 'amount' => '¥5,600', 'status' => 'processing', 'statusLabel' => '対応中', 'buyer' => $latestBuyer, 'destination' => $giftRecipient],
    ['number' => '2609150212347', 'datetime' => '2026-09-15T09:45:00+09:00', 'dateLabel' => '2026/09/15 09:45', 'store' => 'サンプル店舗A', 'amount' => '¥24,000', 'status' => 'shipped', 'statusLabel' => '発送済', 'buyer' => $previousBuyer, 'destination' => $longRecipient],
    ['number' => '2608010212348', 'datetime' => '2026-08-01T16:00:00+09:00', 'dateLabel' => '2026/08/01 16:00', 'store' => 'サンプル店舗B', 'amount' => '¥3,200', 'status' => 'return', 'statusLabel' => '返品対応', 'buyer' => $previousBuyer, 'destination' => $previousBuyer],
    ['number' => '2606010112349', 'datetime' => '2026-06-01T11:20:00+09:00', 'dateLabel' => '2026/06/01 11:20', 'store' => 'サンプル店舗A', 'amount' => '¥3,000', 'status' => 'cancelled', 'statusLabel' => 'キャンセル', 'buyer' => $previousBuyer, 'destination' => $giftRecipient],
];

// 回数・合計は表示確認用の固定値。キャンセル・返品の集計対象や回数の単位を確定するものではない。
$purchaseCount = '5';
$purchaseAmount = '¥48,600';
if ($isSingleState) {
    $orders = [$orders[0]];
    $purchaseCount = '1';
    $purchaseAmount = '¥12,800';
}
?>
<!doctype html>
<!-- =======================================
 * 熊日モール Client 顧客詳細
 * URL: /shop-partner/customer-detail.<?= $pageExtension . PHP_EOL ?>
 * Referenced in: /docs/design/CLIENT_CUSTOMER_DETAIL.md・/shop-partner/customers.php・/index.html
 * 編集元: /shop-partner/customer-detail.php（.htmlは必要時のみの書き出し物。Git管理外）
 * Created: 2026-10-21
 * Last updated: 2026-10-21
 * ======================================= -->
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>顧客詳細｜熊日モール パートナー管理画面</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;600&display=swap" rel="stylesheet" />
    <!-- 初期描画前に共通サイドバーの状態を反映。1194pxはcommon.scssの$tbwとそろえる。 -->
    <script>
      (() => {
        let collapsed = window.matchMedia("(max-width: 1194px)").matches;
        try {
          collapsed = collapsed || localStorage.getItem("kumanichi-client-sidebar") === "collapsed";
        } catch {
          // localStorageが使えなくても初期表示を継続する。
        }
        document.documentElement.classList.toggle("sidebar-collapsed", collapsed);
      })();
    </script>
    <link rel="stylesheet" href="assets/css/common.css" />
    <link rel="stylesheet" href="assets/css/customer-detail.css" />
  </head>
  <body class="admin-page">
    <?php require __DIR__ . '/includes/inc_header.php'; ?>
    <div class="admin-layout">
      <?php require __DIR__ . '/includes/inc_sidebar.php'; ?>
      <main class="admin-main customer-detail-page">
        <div class="page-heading">
          <h1>顧客詳細</h1>
          <a class="back-link" href="customers.<?= $pageExtension ?>">顧客一覧へ戻る</a>
        </div>

        <?php if ($isNotFoundState): ?>
          <?php // 表示案。存在しないIDと権限外のIDを同じ表示にする。実際の権限判定・HTTP応答はバックエンドで決める。 ?>
          <div class="list-empty customer-not-found">
            <p class="message">顧客が見つかりませんでした</p>
            <div class="empty-actions">
              <a class="show-all" href="customers.<?= $pageExtension ?>">顧客一覧へ戻る</a>
            </div>
          </div>
        <?php elseif ($isErrorState): ?>
          <div class="list-empty list-error" role="alert">
            <p class="message">顧客詳細を取得できませんでした</p>
            <p class="error-help">時間をおいて、もう一度読み込んでください。</p>
            <div class="empty-actions">
              <a class="show-all" href="customer-detail.<?= $pageExtension ?>">再読み込み</a>
            </div>
          </div>
        <?php else: ?>
          <section class="form-box customer-profile" aria-labelledby="customer-profile-heading">
            <h2 id="customer-profile-heading">基本情報（最新）</h2>
            <p class="profile-help">閲覧できる注文のうち、最新の注文で確定した注文者情報です。</p>
            <dl class="profile-list">
              <div><dt>氏名</dt><dd><?= htmlspecialchars($latestBuyer['name'], ENT_QUOTES, 'UTF-8') ?></dd></div>
              <div><dt>メールアドレス</dt><dd><?= htmlspecialchars($customerEmail, ENT_QUOTES, 'UTF-8') ?></dd></div>
              <div><dt>電話番号</dt><dd><?= htmlspecialchars($latestBuyer['phone'], ENT_QUOTES, 'UTF-8') ?></dd></div>
              <?php // 基本情報に住所を表示するのは制作1回目の案。 ?>
              <div><dt>住所</dt><dd><span class="postal-code">〒<?= htmlspecialchars($latestBuyer['postal'], ENT_QUOTES, 'UTF-8') ?></span><?= htmlspecialchars($latestBuyer['address'], ENT_QUOTES, 'UTF-8') ?></dd></div>
            </dl>
            <!-- 集計表示の位置・構成は案。対象となる注文はSPECの閲覧範囲に従う。 -->
            <dl class="purchase-summary">
              <div><dt>購入回数</dt><dd><?= $purchaseCount ?>回</dd></div>
              <div><dt>購入金額の合計<span class="tax-note">（税込）</span></dt><dd><?= $purchaseAmount ?></dd></div>
              <div><dt>最終購入日</dt><dd><time datetime="2026-10-21">2026/10/21</time></dd></div>
            </dl>
          </section>

          <section class="customer-history" aria-labelledby="customer-history-heading">
            <h2 id="customer-history-heading">注文履歴</h2>
            <?php foreach ($orders as $order): ?>
              <section class="form-box history-order" aria-labelledby="history-<?= $order['number'] ?>">
                <div class="history-heading">
                  <h3 id="history-<?= $order['number'] ?>">#<?= $order['number'] ?></h3>
                  <!-- 表示専用。受注一覧と同じ状態名・色を使い、選択操作・JSのdata属性は付けない。 -->
                  <dl class="order-status"><dt class="sr-only">発送状況</dt><dd><span class="shipping-status status-<?= $order['status'] ?>"><?= $order['statusLabel'] ?></span></dd></dl>
                </div>
                <dl class="order-summary">
                  <div><dt>注文日時</dt><dd><time datetime="<?= $order['datetime'] ?>"><?= $order['dateLabel'] ?></time></dd></div>
                  <div><dt>店舗</dt><dd><?= htmlspecialchars($order['store'], ENT_QUOTES, 'UTF-8') ?></dd></div>
                  <div class="order-amount"><dt>合計金額<span class="tax-note">（税込）</span></dt><dd><?= $order['amount'] ?></dd></div>
                </dl>
                <div class="order-addresses">
                  <?php foreach (['buyer' => '注文者情報（その注文時点）', 'destination' => '届け先'] as $group => $heading): ?>
                    <section class="address-group" aria-labelledby="<?= $group ?>-<?= $order['number'] ?>">
                      <h4 id="<?= $group ?>-<?= $order['number'] ?>"><?= $heading ?></h4>
                      <div class="contact-lines">
                        <p><?= htmlspecialchars($order[$group]['name'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p>〒<?= htmlspecialchars($order[$group]['postal'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($order[$group]['address'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p><?= htmlspecialchars($order[$group]['phone'], ENT_QUOTES, 'UTF-8') ?></p>
                      </div>
                    </section>
                  <?php endforeach; ?>
                </div>
                <?php // 仮URL。orders.php?keyword={注文番号}で開く案。顧客IDと本番URL・検索の契約は未確定。 ?>
                <p class="order-actions"><a href="orders.<?= $pageExtension ?>?keyword=<?= rawurlencode($order['number']) ?>">受注一覧で見る<span class="sr-only">（注文番号 <?= $order['number'] ?>）</span></a></p>
              </section>
            <?php endforeach; ?>
          </section>
        <?php endif; ?>
      </main>
    </div>
    <script src="assets/js/common.js"></script>
  </body>
</html>
