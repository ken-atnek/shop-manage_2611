<?php
// 制作確認用。業務処理は持たず、現在地と静的出力時のリンクだけ切り替える。
$currentPage = 'orders';
$pageExtension = ($staticExport ?? false) ? 'html' : 'php';
?>
<!doctype html>
<!-- =======================================
 * 熊日モール Client 受注一覧
 * URL: /shop-partner/orders.<?= $pageExtension . PHP_EOL ?>
 * Referenced in: /shop-partner/orders.<?= $pageExtension . PHP_EOL ?>
 * 編集元: /shop-partner/orders.php（.htmlは必要時のみの書き出し物。Git管理外）
 * Created: 2026-10-17
 * Last updated: 2026-10-17
 * ======================================= -->
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>受注一覧｜熊日モール パートナー管理画面</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="assets/css/common.css" />
    <link rel="stylesheet" href="assets/css/order.css" />
  </head>

  <body class="admin-page">
    <?php require __DIR__ . '/includes/inc_header.php'; ?>
    <div class="admin-layout">
      <?php require __DIR__ . '/includes/inc_sidebar.php'; ?>
      <main class="admin-main">
        <div class="page-heading">
          <h1>受注一覧</h1>
          <time datetime="2026-10-17">2026年10月17日</time>
        </div>
        <!-- 表示例は固定。未着手と一覧見出しの対象・件数をそろえる。 -->
        <ul class="kpi-list" role="list" aria-label="注文の絞り込み">
          <li>
            <button type="button" aria-pressed="false">
              <span class="label">すべて</span>
              <span class="count"><strong>8</strong><span class="unit">件</span></span>
            </button>
          </li>
          <li>
            <button type="button" aria-pressed="true">
              <span class="label">未着手</span>
              <span class="count"><strong>3</strong><span class="unit">件</span></span>
            </button>
          </li>
          <li>
            <button type="button" aria-pressed="false">
              <span class="label">発送待ち</span>
              <span class="count"><strong>2</strong><span class="unit">件</span></span>
            </button>
          </li>
        </ul>
        <form class="search-box" action="orders.<?= $pageExtension ?>" method="get" aria-label="注文の検索">
          <div class="filter-bar">
            <label class="sr-only" for="order-keyword">注文番号・お客様名で検索</label>
            <input
              type="search"
              id="order-keyword"
              name="keyword"
              placeholder="注文番号・お客様名で検索"
            />
            <button type="submit">検索</button>
            <button
              class="detail-toggle"
              type="button"
              aria-expanded="false"
              aria-controls="order-search-panel"
            >
              詳細検索
            </button>
          </div>
          <div class="search-panel" id="order-search-panel" hidden>
            <!-- 詳細検索の条件と開閉処理は次の段階で作る。 -->
          </div>
        </form>
        <div class="list-heading" aria-live="polite" aria-atomic="true">
          <h2>注文一覧</h2>
          <span class="count">3件</span>
          <p class="filter-note">未着手を表示</p>
        </div>
      </main>
    </div>
  </body>
</html>
