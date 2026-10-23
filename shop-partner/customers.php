<?php
// 制作確認用。業務処理は持たず、現在地・表示状態・静的出力時のリンクだけ切り替える。
$currentPage = 'customers';
$pageExtension = ($staticExport ?? false) ? 'html' : 'php';

// preview・表示例は制作確認専用。Laravel・Bladeでは実データと状態分岐へ置き換える。
$previewState = ($staticExport ?? false) ? '' : ($_GET['preview'] ?? '');
$isEmptyState = $previewState === 'empty';
$isNoResultsState = $previewState === 'no-results';
$isDateErrorState = $previewState === 'date-error';
$isErrorState = $previewState === 'error';
$isPagerMiddleState = $previewState === 'pager-middle';
$listCount = ($isEmptyState || $isNoResultsState) ? 0 : 986;
$filterNote = $isNoResultsState ? '検索結果' : 'すべてを表示';
$detailPath = 'customer-detail.' . $pageExtension;

// 架空の表示例。メールアドレスはexample.com、電話番号は実在しない000始まりを使う。
$customers = [
    ['id' => 1042, 'name' => 'サンプル 顧客詳細の表示確認用の購入者', 'email' => 'customer.detail@example.com', 'phone' => '000-0000-0001', 'count' => '3', 'amount' => '¥42,400', 'date' => '2026-10-21', 'dateLabel' => '2026/10/21'],
    ['id' => 87, 'name' => 'テスト 花子', 'email' => 'sample.customer.02@example.com', 'phone' => '000-0000-0002', 'count' => '1', 'amount' => '¥12,800', 'date' => '2026-10-21', 'dateLabel' => '2026/10/21'],
    ['id' => 615, 'name' => 'サンプル 顧客名がとても長い表示確認用の購入者', 'email' => 'very-long-customer-name-for-layout-check@example.com', 'phone' => '000-0000-0003', 'count' => '128', 'amount' => '¥1,234,567', 'date' => '2026-10-19', 'dateLabel' => '2026/10/19'],
    ['id' => 2308, 'name' => '見本 次郎', 'email' => 'customer.sample04@example.com', 'phone' => '000-0000-0004', 'count' => '8', 'amount' => '¥12,800', 'date' => '2026-10-18', 'dateLabel' => '2026/10/18'],
    ['id' => 319, 'name' => '架空 三郎', 'email' => 'customer.sample05@example.com', 'phone' => '000-0000-0005', 'count' => '3', 'amount' => '¥5,600', 'date' => '2026-10-17', 'dateLabel' => '2026/10/17'],
    ['id' => 1705, 'name' => '表示確認 四郎', 'email' => 'customer.sample06@example.com', 'phone' => '000-0000-0006', 'count' => '16', 'amount' => '¥204,000', 'date' => '2026-10-16', 'dateLabel' => '2026/10/16'],
];
?>
<!doctype html>
<!-- =======================================
 * 熊日モール Client 顧客一覧
 * URL: /shop-partner/customers.<?= $pageExtension . PHP_EOL ?>
 * Referenced in: /docs/design/CLIENT_CUSTOMER_LIST.md・/index.html
 * 編集元: /shop-partner/customers.php（.htmlは必要時のみの書き出し物。Git管理外）
 * Created: 2026-10-21
 * Last updated: 2026-10-23
 * ======================================= -->
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>顧客一覧｜熊日モール パートナー管理画面</title>
    <link rel="icon" href="data:," />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;600&display=swap"
      rel="stylesheet"
    />
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
    <link rel="stylesheet" href="assets/css/customers.css" />
  </head>

  <body class="admin-page">
    <?php require __DIR__ . '/includes/inc_header.php'; ?>
    <div class="admin-layout">
      <?php require __DIR__ . '/includes/inc_sidebar.php'; ?>
      <main class="admin-main">
        <div class="page-heading">
          <h1>顧客一覧</h1>
        </div>
        <form class="search-box" action="customers.<?= $pageExtension ?>" method="get" aria-label="顧客の検索">
          <!-- nameは表示例。検索方法・正式な項目名はバックエンド組み込み時に決める。 -->
          <div class="filter-bar">
            <label class="sr-only" for="customer-keyword">氏名・メールアドレス・電話番号で検索</label>
            <input type="search" id="customer-keyword" name="keyword" placeholder="氏名・メールアドレス・電話番号で検索" />
            <button type="submit">検索</button>
            <button class="detail-toggle" type="button" aria-expanded="<?= $isDateErrorState ? 'true' : 'false' ?>" aria-controls="customer-search-panel">詳細検索</button>
          </div>
          <div class="search-panel" id="customer-search-panel"<?= $isDateErrorState ? '' : ' inert' ?>>
            <div class="search-panel-inner">
              <div class="search-fields">
                <fieldset class="form-field last-purchase-field">
                  <legend class="form-label">最終購入日</legend>
                  <div class="date-range">
                    <label class="sr-only" for="customer-purchased-from">開始日</label>
                    <input class="form-input" type="date" id="customer-purchased-from" name="purchased_from"<?= $isDateErrorState ? ' value="2026-10-20" aria-invalid="true" aria-describedby="customer-date-error"' : '' ?> />
                    <span aria-hidden="true">〜</span>
                    <label class="sr-only" for="customer-purchased-to">終了日</label>
                    <input class="form-input" type="date" id="customer-purchased-to" name="purchased_to"<?= $isDateErrorState ? ' value="2026-10-10" aria-invalid="true" aria-describedby="customer-date-error"' : '' ?> />
                  </div>
                  <?php if ($isDateErrorState): ?>
                    <!-- エラー時だけ出力する。日付範囲の判定はバックエンドで行う。 -->
                    <p class="form-error" id="customer-date-error">終了日は開始日以降の日付を指定してください</p>
                  <?php endif; ?>
                </fieldset>
                <div class="search-actions">
                  <button class="clear-conditions" type="button">条件をクリア</button>
                  <button type="submit">この条件で検索</button>
                </div>
              </div>
            </div>
          </div>
        </form>

        <?php if ($isErrorState): ?>
          <!-- 取得失敗は0件と区別し、表とページ送りを出さない。 -->
          <div class="list-empty list-error" role="alert">
            <p class="message">顧客一覧を取得できませんでした</p>
            <p class="error-help">時間をおいて、もう一度読み込んでください。</p>
            <div class="empty-actions">
              <a class="show-all" href="customers.<?= $pageExtension ?>">再読み込み</a>
            </div>
          </div>
        <?php else: ?>
          <div class="list-heading" aria-live="polite" aria-atomic="true">
            <h2 id="customer-list-heading">顧客一覧</h2>
            <span class="count"><?= $listCount ?>件</span>
            <p class="filter-note"><?= $filterNote ?></p>
          </div>

          <?php if ($isEmptyState || $isNoResultsState): ?>
            <div class="list-empty">
              <p class="message"><?= $isEmptyState ? '購入者がまだいません' : '条件に一致する顧客はいません' ?></p>
              <?php if ($isNoResultsState): ?>
                <div class="empty-actions">
                  <a class="show-all" href="customers.<?= $pageExtension ?>">すべての顧客を表示</a>
                  <!-- 仮リンク。検索条件を解除する遷移先はバックエンド実装時に決める。 -->
                  <a href="#">条件をクリア</a>
                </div>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <!-- 986件のうち架空の6件を掲載。最終購入日の新しい順・1ページ50件の表示例。 -->
            <!-- 顧客IDで詳細へ進む表示例。仮URLはcustomer-detail.php?id={顧客ID}。本番のURLの形と顧客テーブル方式はバックエンドで決めて知らせてもらう（QA No.11、2026-10-23の方針）。メールアドレスはURLに入れない。 -->
            <div class="customers-scroll">
              <table class="customers-table" aria-labelledby="customer-list-heading">
                <thead>
                  <tr>
                    <th class="customer-id" scope="col">ID</th>
                    <th class="customer-name" scope="col">氏名</th>
                    <th class="customer-email" scope="col">メールアドレス</th>
                    <th class="customer-count" scope="col">購入回数</th>
                    <th class="customer-amount" scope="col">購入金額の合計<span class="tax-note">（税込）</span></th>
                    <th class="customer-purchased" scope="col">最終購入日</th>
                    <th class="customer-detail" scope="col">詳細</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($customers as $customer): ?>
                    <tr>
                      <td class="customer-id"><?= $customer['id'] ?></td>
                      <td class="customer-name"><?= htmlspecialchars($customer['name'], ENT_QUOTES, 'UTF-8') ?></td>
                      <?php
                      // Bladeでも、メールをエスケープして各部分を出力し、@の直前へwbrを入れる。
                      // 各部分の途中では折り返さず、列に収まらない部分だけ省略する。全文はtitleで確認する。
                      $emailParts = explode('@', $customer['email'], 2);
                      ?>
                      <td class="customer-email"><span class="customer-email-value" title="<?= htmlspecialchars($customer['email'], ENT_QUOTES, 'UTF-8') ?>"><span class="email-part"><?= htmlspecialchars($emailParts[0], ENT_QUOTES, 'UTF-8') ?></span><?php if (isset($emailParts[1])): ?><wbr><span class="email-part">@<?= htmlspecialchars($emailParts[1], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?></span></td>
                      <td class="customer-count"><?= htmlspecialchars($customer['count'], ENT_QUOTES, 'UTF-8') ?>回</td>
                      <td class="customer-amount"><?= htmlspecialchars($customer['amount'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td class="customer-purchased"><time datetime="<?= $customer['date'] ?>"><?= $customer['dateLabel'] ?></time></td>
                      <?php $detailLabel = $customer['name'] . 'さんの詳細'; ?>
                      <td class="customer-detail"><a class="detail-link" href="<?= $detailPath ?>?id=<?= $customer['id'] ?>" aria-label="<?= htmlspecialchars($detailLabel, ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($detailLabel, ENT_QUOTES, 'UTF-8') ?>"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" /><circle cx="12" cy="12" r="3" /></svg></a></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <!-- 表示確認用。1ページ50件・986件で20ページの例。件数・ページ・URLはバックエンドが決める。 -->
            <nav class="pagination" aria-label="顧客一覧のページ">
              <ul class="pagination-list" role="list">
                <?php if ($isPagerMiddleState): ?>
                  <li><a href="?page=9"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="m15 18-6-6 6-6" /></svg><span class="sr-only">前のページ</span></a></li>
                  <li><a href="?page=1">1<span class="sr-only">ページ</span></a></li>
                  <li><span class="pagination-ellipsis" aria-hidden="true">…</span></li>
                  <li><a href="?page=9">9<span class="sr-only">ページ</span></a></li>
                  <li><a href="?page=10" aria-current="page">10<span class="sr-only">ページ</span></a></li>
                  <li><a href="?page=11">11<span class="sr-only">ページ</span></a></li>
                  <li><span class="pagination-ellipsis" aria-hidden="true">…</span></li>
                  <li><a href="?page=20">20<span class="sr-only">ページ</span></a></li>
                  <li><a href="?page=11"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="m9 18 6-6-6-6" /></svg><span class="sr-only">次のページ</span></a></li>
                <?php else: ?>
                  <li><a href="?page=1" aria-current="page">1<span class="sr-only">ページ</span></a></li>
                  <li><a href="?page=2">2<span class="sr-only">ページ</span></a></li>
                  <li><a href="?page=3">3<span class="sr-only">ページ</span></a></li>
                  <li><a href="?page=4">4<span class="sr-only">ページ</span></a></li>
                  <li><a href="?page=5">5<span class="sr-only">ページ</span></a></li>
                  <li><span class="pagination-ellipsis" aria-hidden="true">…</span></li>
                  <li><a href="?page=20">20<span class="sr-only">ページ</span></a></li>
                  <li><a href="?page=2"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="m9 18 6-6-6-6" /></svg><span class="sr-only">次のページ</span></a></li>
                <?php endif; ?>
              </ul>
              <p class="page-status"><?= $isPagerMiddleState ? '10' : '1' ?> / 20<span class="sr-only">ページ</span></p>
            </nav>
          <?php endif; ?>
        <?php endif; ?>
      </main>
    </div>
    <script src="assets/js/common.js"></script>
  </body>
</html>
