<?php
// 制作確認用。業務処理は持たず、現在地・表示状態・静的出力時のリンクだけ切り替える。
$currentPage = 'sales';
$pageExtension = ($staticExport ?? false) ? 'html' : 'php';
$salesPath = 'sales.' . $pageExtension;

// preview・表示例は制作専用。Laravel・Bladeでは実データと状態分岐へ置き換える。
$previewState = ($staticExport ?? false) ? '' : ($_GET['preview'] ?? '');
$isEmptyState = $previewState === 'empty';
$isPagerMiddleState = $previewState === 'pager-middle';
$isDateErrorState = $previewState === 'date-error';
$isErrorState = $previewState === 'error';
$isSingleStoreState = $previewState === 'single-store';
$isDateRangeState = $previewState === 'date-range';
$isFirstMonthState = $previewState === 'first-month';
$selectedMonth = $isFirstMonthState ? '2026-01' : '2026-10';
$monthHelp = '';
if ($isDateRangeState) {
    $monthHelp = '期間指定中です。月の操作は、月単位の集計に戻るための操作です。';
} elseif ($isFirstMonthState) {
    $monthHelp = '最初の注文月のため、前月は選べません。';
} elseif ($selectedMonth === '2026-10') {
    $monthHelp = '今月より先の月は選べません。';
}
$periodFrom = $isDateRangeState ? '2026-09-15' : ($isFirstMonthState ? '2026-01-01' : '2026-10-01');
$periodTo = $isDateRangeState ? '2026-10-15' : ($isFirstMonthState ? '2026-01-31' : '2026-10-22');
$periodLabel = $isDateRangeState ? '2026/09/15〜2026/10/15' : ($isFirstMonthState ? '2026/01/01〜2026/01/31' : '2026/10/01〜2026/10/22（今日まで）');
$storeValue = $isSingleStoreState ? '1' : 'all';
$storeLabel = $isSingleStoreState ? 'くまもと物産館 本店' : 'すべての店舗';
$rangeLabel = $isEmptyState ? '全 0 商品' : ($isPagerMiddleState ? '全 426 商品中 201〜250 件' : '全 426 商品中 1〜50 件');
$totalAmount = $isEmptyState ? '¥0' : '¥1,284,560';
$totalQuantity = $isEmptyState ? '0' : '1,932';
$totalOrders = $isEmptyState ? '0' : '418';

// 表示例の適用済み条件。入力中の日付ではなく、CSV・ページ送りの条件に使う。
$appliedConditions = $isDateRangeState ? ['from' => $periodFrom, 'to' => $periodTo] : ['month' => $selectedMonth];
$appliedConditions['store'] = $storeValue;
$appliedConditions['sort'] = 'sales';
$pagerQuery = http_build_query($appliedConditions);

// 架空の表示例。1ページ50件のうち10行を抜粋。途中のページも同じ商品例を使い、並び替え・集計は行わない。
$salesProducts = [
    ['name' => '阿蘇小国ジャージー牛乳 900ml', 'count' => '212', 'quantity' => '388', 'amount' => '¥194,000'],
    ['name' => '熊本県産 からし蓮根 ハーフサイズ（真空パック）', 'count' => '148', 'quantity' => '201', 'amount' => '¥160,800'],
    ['name' => '馬刺し 赤身ブロック 200g', 'count' => '96', 'quantity' => '133', 'amount' => '¥146,300'],
    ['name' => '【季節限定・数量限定】球磨川流域で育った有機栽培ほうじ茶と黒糖わらび餅の詰め合わせ 贈答用化粧箱入り（のし対応可）', 'count' => '41', 'quantity' => '47', 'amount' => '¥136,300'],
    ['name' => '天草 晩柑ゼリー 6個入り', 'count' => '118', 'quantity' => '165', 'amount' => '¥115,500'],
    ['name' => '太平燕（タイピーエン）3食セット', 'count' => '89', 'quantity' => '142', 'amount' => '¥99,400'],
    ['name' => '小代焼 マグカップ 青流し', 'count' => '37', 'quantity' => '52', 'amount' => '¥85,800'],
    ['name' => '黒糖ドーナツ棒 10本入り', 'count' => '133', 'quantity' => '310', 'amount' => '¥74,400'],
    ['name' => '肥後象嵌 しおり（桜）', 'count' => '28', 'quantity' => '34', 'amount' => '¥61,200'],
    ['name' => '山鹿灯籠 ミニチュア置物', 'count' => '19', 'quantity' => '21', 'amount' => '¥52,500'],
];
?>
<!doctype html>
<!-- =======================================
 * 熊日モール Client 集計
 * URL: /shop-partner/sales.<?= $pageExtension . PHP_EOL ?>
 * Referenced in: /docs/design/CLIENT_SALES.md・/index.html
 * 編集元: /shop-partner/sales.php（.htmlは必要時のみの書き出し物。Git管理外）
 * Created: 2026-10-22
 * Last updated: 2026-10-22
 * ======================================= -->
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>集計｜熊日モール パートナー管理画面</title>
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
    <link rel="stylesheet" href="assets/css/sales.css" />
  </head>

  <body class="admin-page">
    <?php require __DIR__ . '/includes/inc_header.php'; ?>
    <div class="admin-layout">
      <?php require __DIR__ . '/includes/inc_sidebar.php'; ?>
      <main class="admin-main sales-page">
        <div class="page-heading">
          <h1>集計</h1>
        </div>

        <section class="sales-period" aria-label="集計期間">
          <!-- name・リンク先は仮。月は変更時にGET送信するが、月・日付の判定や集計は行わない。 -->
          <div class="period-fields">
            <form class="month-form form-field" action="<?= $salesPath ?>" method="get" aria-label="月で見る">
              <label class="form-label" for="sales-month">月で見る</label>
              <input type="hidden" name="store" value="<?= $storeValue ?>" />
              <input type="hidden" name="sort" value="sales" />
              <div class="month-controls">
                <?php if ($isFirstMonthState): ?>
                  <button class="month-button" type="button" disabled aria-label="前月（最初の注文月のため選べません）" title="最初の注文月のため前月は選べません"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="m15 18-6-6 6-6" /></svg></button>
                <?php else: ?>
                  <a class="month-button" href="<?= $salesPath ?>?month=2026-09&amp;store=<?= $storeValue ?>&amp;sort=sales" aria-label="前月（2026年9月）" title="前月（2026年9月）"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="m15 18-6-6 6-6" /></svg></a>
                <?php endif; ?>
                <select id="sales-month" name="month"<?= $monthHelp !== '' ? ' aria-describedby="sales-month-help"' : '' ?> data-auto-submit>
                  <option value="2026-10"<?= !$isFirstMonthState ? ' selected' : '' ?>>2026年10月（今月）</option>
                  <option value="2026-09">2026年9月</option>
                  <option value="2026-08">2026年8月</option>
                  <option value="2026-07">2026年7月</option>
                  <option value="2026-06">2026年6月</option>
                  <option value="2026-05">2026年5月</option>
                  <option value="2026-04">2026年4月</option>
                  <option value="2026-03">2026年3月</option>
                  <option value="2026-02">2026年2月</option>
                  <option value="2026-01"<?= $isFirstMonthState ? ' selected' : '' ?>>2026年1月</option>
                </select>
                <?php if ($isFirstMonthState): ?>
                  <a class="month-button" href="<?= $salesPath ?>?month=2026-02&amp;store=<?= $storeValue ?>&amp;sort=sales" aria-label="翌月（2026年2月）" title="翌月（2026年2月）"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="m9 18 6-6-6-6" /></svg></a>
                <?php else: ?>
                  <button class="month-button" type="button" disabled aria-label="翌月（今月より先は選べません）" title="今月より先は選べません"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="m9 18 6-6-6-6" /></svg></button>
                <?php endif; ?>
              </div>
            </form>

            <form class="period-form" action="<?= $salesPath ?>" method="get" aria-label="期間を指定して集計">
              <input type="hidden" name="store" value="<?= $storeValue ?>" />
              <input type="hidden" name="sort" value="sales" />
              <fieldset class="form-field">
                <legend class="sr-only">集計する期間</legend>
                <div class="date-range">
                  <div class="date-field">
                    <label class="form-label" for="sales-from">開始日</label>
                    <input class="form-input" type="date" id="sales-from" name="from" value="<?= $isDateErrorState ? '2026-10-20' : $periodFrom ?>"<?= $isDateErrorState ? ' aria-invalid="true" aria-describedby="sales-date-error"' : '' ?> />
                  </div>
                  <span class="date-separator" aria-hidden="true">〜</span>
                  <div class="date-field">
                    <label class="form-label" for="sales-to">終了日</label>
                    <input class="form-input" type="date" id="sales-to" name="to" value="<?= $isDateErrorState ? '2026-10-10' : $periodTo ?>"<?= $isDateErrorState ? ' aria-invalid="true" aria-describedby="sales-date-error"' : '' ?> />
                  </div>
                  <button class="period-submit" type="submit">この期間で集計</button>
                  <?php if ($isDateErrorState): ?>
                    <p class="form-error" id="sales-date-error">終了日は開始日以降の日付を指定してください</p>
                  <?php endif; ?>
                </div>
              </fieldset>
            </form>
          </div>
          <p class="applied-period">集計期間：<strong><?= $periodLabel ?></strong></p>
          <?php if ($monthHelp !== ''): ?>
            <p class="period-help" id="sales-month-help"><?= $monthHelp ?></p>
          <?php endif; ?>
        </section>

        <div class="sales-toolbar">
          <?php if (!$isSingleStoreState): ?>
            <form class="store-form form-field" action="<?= $salesPath ?>" method="get" aria-label="集計する店舗">
              <?php foreach ($appliedConditions as $conditionName => $conditionValue): ?>
                <?php if ($conditionName !== 'store'): ?>
                  <input type="hidden" name="<?= $conditionName ?>" value="<?= $conditionValue ?>" />
                <?php endif; ?>
              <?php endforeach; ?>
              <label class="form-label" for="sales-store">店舗</label>
              <select id="sales-store" name="store" data-auto-submit>
                <option value="all" selected>すべての店舗</option>
                <option value="1">くまもと物産館 本店</option>
                <option value="2">阿蘇ファームショップ</option>
                <option value="3">天草マルシェ</option>
              </select>
            </form>
          <?php else: ?>
            <p class="sales-store-name">店舗：<?= $storeLabel ?></p>
          <?php endif; ?>
          <!-- CSV生成は未実装。適用済み期間・店舗を送るGETの形だけを用意する。 -->
          <form class="csv-form" action="<?= $salesPath ?>" method="get" aria-label="集計のCSV出力">
            <?php foreach ($appliedConditions as $conditionName => $conditionValue): ?>
              <input type="hidden" name="<?= $conditionName ?>" value="<?= $conditionValue ?>" />
            <?php endforeach; ?>
            <button class="csv-download" type="submit" name="download" value="csv"<?= ($isEmptyState || $isErrorState) ? ' disabled' : '' ?>>CSVをダウンロード</button>
          </form>
        </div>

        <?php if ($isErrorState): ?>
          <!-- 取得失敗を0件と区別し、未取得の合計・表・ページ送りを出さない。 -->
          <div class="list-empty list-error" role="alert">
            <p class="message">集計を取得できませんでした</p>
            <p class="error-help">時間をおいて、もう一度読み込んでください。</p>
            <div class="empty-actions">
              <a class="show-all" href="<?= $salesPath ?>?<?= htmlspecialchars($pagerQuery, ENT_QUOTES, 'UTF-8') ?>">再読み込み</a>
            </div>
          </div>
        <?php else: ?>
          <section class="sales-summary" aria-label="期間全体の合計">
            <dl class="sales-totals">
              <div><dt>売上の合計（税込）</dt><dd><?= $totalAmount ?></dd></div>
              <div><dt>販売個数の合計</dt><dd><?= $totalQuantity ?><span class="unit">個</span></dd></div>
              <div><dt>注文件数</dt><dd><?= $totalOrders ?><span class="unit">件</span></dd></div>
            </dl>
            <p class="sales-note">売上は商品代（税込）の合計です。送料、キャンセル・返品の注文は含みません。</p>
          </section>

          <section class="sales-results" aria-labelledby="sales-list-heading">
            <div class="list-heading sales-list-heading">
              <div class="sales-list-title" aria-live="polite" aria-atomic="true">
                <h2 id="sales-list-heading">商品ごとの売上</h2>
                <span class="count"><?= $rangeLabel ?></span>
              </div>
              <?php if (!$isEmptyState): ?>
                <form class="sort-form form-field" action="<?= $salesPath ?>" method="get" aria-label="商品の並び順">
                  <?php foreach ($appliedConditions as $conditionName => $conditionValue): ?>
                    <?php if ($conditionName !== 'sort'): ?>
                      <input type="hidden" name="<?= $conditionName ?>" value="<?= $conditionValue ?>" />
                    <?php endif; ?>
                  <?php endforeach; ?>
                  <label class="form-label" for="sales-sort">並び順</label>
                  <select id="sales-sort" name="sort" data-auto-submit>
                    <option value="sales" selected>売上の多い順</option>
                    <option value="quantity">販売個数の多い順</option>
                  </select>
                </form>
              <?php endif; ?>
            </div>

            <?php if ($isEmptyState): ?>
              <div class="list-empty">
                <p class="message">この期間の売上はありません</p>
              </div>
            <?php else: ?>
              <div class="sales-scroll">
                <table class="sales-table">
                  <caption class="sr-only"><?= $periodLabel ?>・<?= $storeLabel ?>の商品別集計（売上の多い順）</caption>
                  <thead>
                    <tr>
                      <th class="sales-product" scope="col">商品名</th>
                      <th class="sales-count" scope="col">購入回数</th>
                      <th class="sales-quantity" scope="col">販売個数</th>
                      <th class="sales-amount" scope="col">売上<span class="tax-note">（税込）</span></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($salesProducts as $salesProduct): ?>
                      <tr>
                        <td class="sales-product"><?= htmlspecialchars($salesProduct['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="sales-count"><?= $salesProduct['count'] ?>回</td>
                        <td class="sales-quantity"><?= $salesProduct['quantity'] ?>個</td>
                        <td class="sales-amount"><?= $salesProduct['amount'] ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <!-- 全426商品・1ページ50件・全9ページの表示例（行は10件の抜粋）。件数・ページ・URLは組み込み時に実データで生成する。 -->
              <nav class="pagination" aria-label="商品別集計のページ">
                <ul class="pagination-list" role="list">
                  <?php if ($isPagerMiddleState): ?>
                    <li><a href="<?= $salesPath ?>?<?= htmlspecialchars($pagerQuery, ENT_QUOTES, 'UTF-8') ?>&amp;page=4"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="m15 18-6-6 6-6" /></svg><span class="sr-only">前のページ</span></a></li>
                    <?php $pageNumbers = [1, null, 4, 5, 6, null, 9]; ?>
                  <?php else: ?>
                    <?php $pageNumbers = [1, 2, 3, 4, 5, null, 9]; ?>
                  <?php endif; ?>
                  <?php foreach ($pageNumbers as $pageNumber): ?>
                    <?php if ($pageNumber === null): ?>
                      <li><span class="pagination-ellipsis" aria-hidden="true">…</span></li>
                    <?php else: ?>
                      <li><a href="<?= $salesPath ?>?<?= htmlspecialchars($pagerQuery, ENT_QUOTES, 'UTF-8') ?>&amp;page=<?= $pageNumber ?>"<?= $pageNumber === ($isPagerMiddleState ? 5 : 1) ? ' aria-current="page"' : '' ?>><?= $pageNumber ?><span class="sr-only">ページ</span></a></li>
                    <?php endif; ?>
                  <?php endforeach; ?>
                  <li><a href="<?= $salesPath ?>?<?= htmlspecialchars($pagerQuery, ENT_QUOTES, 'UTF-8') ?>&amp;page=<?= $isPagerMiddleState ? 6 : 2 ?>"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="m9 18 6-6-6-6" /></svg><span class="sr-only">次のページ</span></a></li>
                </ul>
                <p class="page-status"><?= $isPagerMiddleState ? '5' : '1' ?> / 9<span class="sr-only">ページ</span></p>
              </nav>
            <?php endif; ?>
          </section>
        <?php endif; ?>
      </main>
    </div>
    <script src="assets/js/common.js"></script>
  </body>
</html>
