<?php
// 制作確認用。DB・権限判定・顧客IDの識別は実装しない。
$currentPage = 'customers';
$pageExtension = ($staticExport ?? false) ? 'html' : 'php';
$previewState = ($staticExport ?? false) ? '' : ($_GET['preview'] ?? '');
$isSingleState = $previewState === 'single';
$isNotFoundState = $previewState === 'not-found';
$isErrorState = $previewState === 'error';

// 架空の表示例。IDは表示確認用の候補だけを受け付け、DBの識別・権限判定には使わない。
// 顧客テーブル方式と本番URLの形は、バックエンドで決めて知らせてもらう（QA No.11、2026-10-23の方針）。仮URLはcustomer-detail.php?id={顧客ID}。
$sampleCustomerIds = ['1042', '87', '615', '2308', '319', '1705'];
$requestedId = ($staticExport ?? false) ? '1042' : ($_GET['id'] ?? ($isSingleState ? '87' : '1042'));
$customerId = in_array($requestedId, $sampleCustomerIds, true) ? $requestedId : '1042';
// 各IDの基本情報・履歴は同じ固定例。実データとの連動はバックエンドで行う。
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
// destinationSameBuyerは制作確認用の判定済み値。本番の判定はバックエンドで行う。
// 購入時の「注文者と同じ住所に届ける」の選択を注文に記録する方法が望ましい。
// 記録がない場合は、その注文時点の注文者情報と届け先を比較する。基本情報（最新）とは比較しない。
$orders = [
    ['number' => '2610210112345', 'datetime' => '2026-10-21T10:30:00+09:00', 'dateLabel' => '2026/10/21 10:30', 'store' => 'サンプル店舗A', 'amount' => '¥12,800', 'status' => 'new', 'statusLabel' => '新規受付', 'destination' => $latestBuyer, 'shipping' => 800, 'items' => [
        ['name' => 'サンプル 写真入りマグカップ', 'quantity' => 2, 'subtotal' => 6000],
        ['name' => 'サンプル 長い商品名の折り返しを確認するための名入れギフトセット・サンプル限定パッケージ詰め合わせ', 'quantity' => 1, 'subtotal' => 6000],
    ]],
    ['number' => '2610200112346', 'datetime' => '2026-10-20T14:15:00+09:00', 'dateLabel' => '2026/10/20 14:15', 'store' => 'サンプル店舗B', 'amount' => '¥5,600', 'status' => 'processing', 'statusLabel' => '対応中', 'destination' => $giftRecipient, 'shipping' => 800, 'items' => [
        ['name' => 'サンプル 名入れタオルセット', 'quantity' => 2, 'subtotal' => 4800],
    ]],
    ['number' => '2609150212347', 'datetime' => '2026-09-15T09:45:00+09:00', 'dateLabel' => '2026/09/15 09:45', 'store' => 'サンプル店舗A', 'amount' => '¥24,000', 'status' => 'shipped', 'statusLabel' => '発送済', 'destination' => $longRecipient, 'shipping' => 0, 'items' => [
        ['name' => 'サンプル 記念プレート', 'quantity' => 2, 'subtotal' => 12000],
        ['name' => 'サンプル フォトフレーム', 'quantity' => 1, 'subtotal' => 6000],
        ['name' => 'サンプル ギフト用ハンカチ', 'quantity' => 3, 'subtotal' => 6000],
    ]],
    ['number' => '2608010212348', 'datetime' => '2026-08-01T16:00:00+09:00', 'dateLabel' => '2026/08/01 16:00', 'store' => 'サンプル店舗B', 'amount' => '¥3,200', 'status' => 'return', 'statusLabel' => '返品対応', 'destination' => $previousBuyer, 'shipping' => 800, 'items' => [
        ['name' => 'サンプル デザインノート', 'quantity' => 1, 'subtotal' => 2400],
    ]],
    ['number' => '2606010112349', 'datetime' => '2026-06-01T11:20:00+09:00', 'dateLabel' => '2026/06/01 11:20', 'store' => 'サンプル店舗A', 'amount' => '¥3,000', 'status' => 'cancelled', 'statusLabel' => 'キャンセル', 'destination' => $giftRecipient, 'shipping' => 800, 'items' => [
        ['name' => 'サンプル メッセージカード', 'quantity' => 2, 'subtotal' => 1400],
        ['name' => 'サンプル ギフトバッグ', 'quantity' => 1, 'subtotal' => 800],
    ]],
];

// 架空の備考・記録の表示例。注文・商品メモの保存処理や届け先の比較処理は実装しない。
$orders[0]['destinationSameBuyer'] = true;
$orders[3]['destinationSameBuyer'] = true;
$orders[0]['note'] = "表示確認用の備考です。ギフト包装はせず、商品ごとにサンプルの説明カードを同封してください。長い文章が省略されず、画面幅に応じて折り返すことを確認するための架空のご希望です。\nサンプル建物の受付窓口へお届けください。複数の商品が入る場合は、箱の中で動かないように緩衝材を入れていただけると助かります。";
$orders[1]['note'] = '表示確認用：ギフトのため、金額が分かるものは同封しないでください。';
$orders[3]['record'] = "表示確認用：表紙の印刷に不備があったため返品対応。\nお客様へ返送方法をご案内し、サンプル店舗で商品の状態を確認済みです。";
$orders[3]['items'][0]['note'] = '表紙の印刷不備を確認。返品を受け付けました。';
$orders[4]['record'] = '表示確認用：お客様のご都合によるキャンセル。発送前に受付し、返金のご案内を完了しました。';
$orders[4]['items'][0]['note'] = '制作前のためキャンセルを受け付けました。';

// 回数・合計は表示確認用の固定値。キャンセル・返品の注文を除いた値（確定）。回数の単位（店舗ごとの注文か購入単位か）は未確定。
$purchaseCount = '3';
$purchaseAmount = '¥42,400';
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
 * Last updated: 2026-10-23
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
              <a class="show-all" href="customer-detail.<?= $pageExtension ?>?id=<?= $customerId ?>">再読み込み</a>
            </div>
          </div>
        <?php else: ?>
          <section class="form-box customer-profile" aria-labelledby="customer-profile-heading">
            <?php // 表示元は従来どおり、Clientが閲覧できる注文のうち最新の注文で確定した注文者情報。 ?>
            <h2 id="customer-profile-heading"><?= htmlspecialchars($latestBuyer['name'], ENT_QUOTES, 'UTF-8') ?></h2>
            <!-- 顧客IDは箱内の氏名の下に置く配置案。位置はKenが画面で調整する。 -->
            <p class="customer-meta">顧客ID <?= $customerId ?></p>
            <div class="profile-contact">
              <p><?= htmlspecialchars($customerEmail, ENT_QUOTES, 'UTF-8') ?></p>
              <p><?= htmlspecialchars($latestBuyer['phone'], ENT_QUOTES, 'UTF-8') ?></p>
              <?php // 基本情報に住所を表示するのは制作1回目の案。 ?>
              <p>〒<?= htmlspecialchars($latestBuyer['postal'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($latestBuyer['address'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
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
              <?php $hasCancelRecord = in_array($order['status'], ['cancelled', 'return'], true) && trim($order['record'] ?? '') !== ''; ?>
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
                <!-- 受注一覧の商品明細と送料の構成を参考にした表示専用テーブル。合計は概要だけに表示する。 -->
                <table class="item-table" aria-label="注文<?= $order['number'] ?>の商品明細と送料">
                  <colgroup><col /><col class="quantity" /><col class="subtotal" /></colgroup>
                  <thead><tr><th scope="col">商品名</th><th class="quantity" scope="col">個数</th><th class="subtotal" scope="col">小計<span class="tax-note">（税込）</span></th></tr></thead>
                  <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                      <tr>
                        <td class="item-name">
                          <?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>
                          <?php if ($hasCancelRecord && trim($item['note'] ?? '') !== ''): ?>
                            <p class="item-note"><span class="sr-only">商品ごとのメモ：</span><?= htmlspecialchars($item['note'], ENT_QUOTES, 'UTF-8') ?></p>
                          <?php endif; ?>
                        </td>
                        <td class="quantity"><?= $item['quantity'] ?></td>
                        <td class="subtotal">¥<?= number_format($item['subtotal']) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                  <tfoot><tr><th scope="row" colspan="2">送料<?= $order['shipping'] === 0 ? '（送料無料）' : '' ?></th><td>¥<?= number_format($order['shipping']) ?></td></tr></tfoot>
                </table>
                <?php // 注文時点の注文者情報はDBに残す前提だが、この画面の注文履歴では表示しない。 ?>
                <?php if ($order['destinationSameBuyer'] ?? false): ?>
                  <p class="address-group same-destination">届け先：注文者と同じ</p>
                <?php else: ?>
                  <section class="address-group" aria-labelledby="destination-<?= $order['number'] ?>">
                    <h4 id="destination-<?= $order['number'] ?>">届け先</h4>
                    <div class="contact-lines">
                      <p><?= htmlspecialchars($order['destination']['name'], ENT_QUOTES, 'UTF-8') ?></p>
                      <p>〒<?= htmlspecialchars($order['destination']['postal'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($order['destination']['address'], ENT_QUOTES, 'UTF-8') ?></p>
                      <p><?= htmlspecialchars($order['destination']['phone'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                  </section>
                <?php endif; ?>
                <?php if (trim($order['note'] ?? '') !== ''): ?>
                  <section class="customer-note" aria-labelledby="customer-note-<?= $order['number'] ?>">
                    <h4 id="customer-note-<?= $order['number'] ?>">備考（お客様より）</h4>
                    <p><?= htmlspecialchars($order['note'], ENT_QUOTES, 'UTF-8') ?></p>
                  </section>
                <?php endif; ?>
                <?php if ($hasCancelRecord): ?>
                  <!-- 受注一覧と同じ記録を表示するだけ。form・textarea・保存操作・入稿画像は置かない。 -->
                  <section class="cancel-record" aria-labelledby="cancel-record-<?= $order['number'] ?>">
                    <h4 id="cancel-record-<?= $order['number'] ?>">キャンセル・返品の記録</h4>
                    <p class="record-label">理由・対応メモ</p>
                    <p class="record-reason"><?= htmlspecialchars($order['record'], ENT_QUOTES, 'UTF-8') ?></p>
                  </section>
                <?php endif; ?>
              </section>
            <?php endforeach; ?>
          </section>
        <?php endif; ?>
      </main>
    </div>
    <script src="assets/js/common.js"></script>
  </body>
</html>
