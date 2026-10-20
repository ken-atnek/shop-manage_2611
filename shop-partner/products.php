<?php
// 制作確認用。業務処理は持たず、現在地と静的出力時のリンクだけ切り替える。
$currentPage = 'products';
$pageExtension = ($staticExport ?? false) ? 'html' : 'php';

// 制作時の表示確認専用。Laravel・Bladeでは使用せず、バックエンド側の状態分岐へ置き換える。
$previewState = $_GET['preview'] ?? '';
if ($staticExport ?? false) {
    $previewState = '';
}
$isEmptyState = $previewState === 'empty';
$isNoResultsState = $previewState === 'no-results';
$isDateErrorState = $previewState === 'date-error';
$isPagerMiddleState = $previewState === 'pager-middle';
$listCount = ($isEmptyState || $isNoResultsState) ? 0 : 986;
$filterNote = $isNoResultsState ? '検索結果' : 'すべてを表示';
?>
<!doctype html>
<!-- =======================================
 * 熊日モール Client 商品一覧
 * URL: /shop-partner/products.<?= $pageExtension . PHP_EOL ?>
 * Referenced in: /shop-partner/products.<?= $pageExtension . PHP_EOL ?>
 * 編集元: /shop-partner/products.php（.htmlは必要時のみの書き出し物。Git管理外）
 * Created: 2026-10-19
 * Last updated: 2026-10-20
 * ======================================= -->
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>商品一覧｜熊日モール パートナー管理画面</title>
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
    <link rel="stylesheet" href="assets/css/products.css" />
  </head>

  <body class="admin-page">
    <?php require __DIR__ . '/includes/inc_header.php'; ?>
    <div class="admin-layout">
      <?php require __DIR__ . '/includes/inc_sidebar.php'; ?>
      <main class="admin-main">
        <div class="page-heading">
          <h1>商品一覧</h1>
        </div>
        <form class="search-box" action="products.<?= $pageExtension ?>" method="get" aria-label="商品の検索">
          <!-- name（keyword・publish[]・stock[]・updated_from・updated_to）は表示例。バックエンドの正式な項目名を決めるものではない。 -->
          <div class="filter-bar">
            <label class="sr-only" for="product-keyword">商品名・IDで検索</label>
            <input type="search" id="product-keyword" name="keyword" placeholder="商品名・IDで検索" />
            <button type="submit">検索</button>
            <button class="detail-toggle" type="button" aria-expanded="<?= $isDateErrorState ? 'true' : 'false' ?>" aria-controls="product-search-panel">詳細検索</button>
          </div>
          <div class="search-panel" id="product-search-panel"<?= $isDateErrorState ? '' : ' inert' ?>>
            <div class="search-panel-inner">
              <div class="search-fields">
                <fieldset class="form-field publish-field">
                  <legend class="form-label">公開状態</legend>
                  <div class="check-group">
                    <label class="form-check" for="search-publish-public"><input type="checkbox" id="search-publish-public" name="publish[]" value="public" /><span>公開</span></label>
                    <label class="form-check" for="search-publish-private"><input type="checkbox" id="search-publish-private" name="publish[]" value="private" /><span>非公開</span></label>
                  </div>
                </fieldset>
                <fieldset class="form-field stock-field">
                  <legend class="form-label">在庫の状態</legend>
                  <div class="check-group">
                    <label class="form-check" for="search-stock-available"><input type="checkbox" id="search-stock-available" name="stock[]" value="available" /><span>在庫あり</span></label>
                    <label class="form-check" for="search-stock-low"><input type="checkbox" id="search-stock-low" name="stock[]" value="low" /><span>残りわずか</span></label>
                    <label class="form-check" for="search-stock-empty"><input type="checkbox" id="search-stock-empty" name="stock[]" value="empty" /><span>在庫切れ</span></label>
                  </div>
                </fieldset>
                <fieldset class="form-field updated-field">
                  <legend class="form-label">更新日</legend>
                  <div class="date-range">
                    <label class="sr-only" for="product-updated-from">開始日</label>
                    <input class="form-input" type="date" id="product-updated-from" name="updated_from"<?= $isDateErrorState ? ' value="2026-10-20" aria-invalid="true" aria-describedby="product-date-error"' : '' ?> />
                    <span aria-hidden="true">〜</span>
                    <label class="sr-only" for="product-updated-to">終了日</label>
                    <input class="form-input" type="date" id="product-updated-to" name="updated_to"<?= $isDateErrorState ? ' value="2026-10-10" aria-invalid="true" aria-describedby="product-date-error"' : '' ?> />
                  </div>
                  <?php if ($isDateErrorState): ?>
                    <!-- エラー時だけ出力する。日付範囲の判定はバックエンドで行う。 -->
                    <p class="form-error" id="product-date-error">終了日は開始日以降の日付を指定してください</p>
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
        <div class="list-heading" aria-live="polite" aria-atomic="true">
          <h2 id="product-list-heading">商品一覧</h2>
          <span class="count"><?= $listCount ?>件</span>
          <p class="filter-note"><?= $filterNote ?></p>
        </div>
        <?php if ($isEmptyState || $isNoResultsState): ?>
          <div class="list-empty">
            <p class="message"><?= $isEmptyState ? 'まだ商品はありません' : '条件に一致する商品はありません' ?></p>
            <?php if ($isNoResultsState): ?>
              <div class="empty-actions">
                <a class="show-all" href="products.<?= $pageExtension ?>">すべての商品を表示</a>
                <!-- 仮リンク。検索条件を解除する遷移先はバックエンド実装時に決める。 -->
                <a href="#">条件をクリア</a>
              </div>
            <?php endif; ?>
          </div>
        <?php else: ?>
        <!-- 架空商品の表示例。986件のうち6件を掲載。価格は税込、更新が新しい順。写真は既存の確認用JPEGを流用。 -->
        <!-- 残りわずかの3個は表示例。全商品共通の基準値は未確定で、ここでは判定処理を持たない。 -->
        <!-- 編集のhref="#"は商品詳細が未作成の仮リンク。実際のURLはバックエンド組み込み時に差し替える。 -->
        <div class="products-scroll">
          <table class="products-table" aria-labelledby="product-list-heading">
            <thead>
              <tr>
                <th class="product-id" scope="col">ID</th>
                <th class="product-image" scope="col">画像</th>
                <th class="product-name" scope="col">商品名</th>
                <th class="product-price" scope="col">価格</th>
                <th class="product-stock" scope="col">在庫数</th>
                <th class="product-publish" scope="col">公開状態</th>
                <th class="product-updated" scope="col">更新日時</th>
                <th class="product-edit" scope="col">編集</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="product-id">986</td>
                <td class="product-image"><img src="assets/images/sample/photo_01.jpg" width="48" height="48" alt="" /></td>
                <td class="product-name">写真入りマグカップ</td>
                <td class="product-price">¥2,800</td>
                <td class="product-stock">24</td>
                <td><span class="publish-state">公開</span></td>
<td class="product-updated"><time datetime="2026-10-19T14:30:00+09:00"><span>2026/10/19</span> <span>14:30</span></time></td>
                <td class="product-edit"><a class="edit-link" href="product-form.<?= $pageExtension ?>" aria-label="写真入りマグカップを編集" title="写真入りマグカップを編集"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" /><path d="m15 5 4 4" /></svg></a></td>
              </tr>
              <tr>
                <td class="product-id">985</td>
                <td class="product-image"><img src="assets/images/sample/photo_02.jpg" width="48" height="48" alt="" /></td>
                <td class="product-name">大切な記念日の写真でつくる オリジナルフォトパネル 木製スタンド付き ギフトボックス入り</td>
                <td class="product-price">¥6,800</td>
                <td class="product-stock">3<span class="stock-note">残りわずか</span></td>
                <td><span class="publish-state">公開</span></td>
                <td class="product-updated"><time datetime="2026-10-19T13:15:00+09:00"><span>2026/10/19</span> <span>13:15</span></time></td>
                <td class="product-edit"><a class="edit-link" href="product-form.<?= $pageExtension ?>" aria-label="大切な記念日の写真でつくる オリジナルフォトパネル 木製スタンド付き ギフトボックス入りを編集" title="大切な記念日の写真でつくる オリジナルフォトパネル 木製スタンド付き ギフトボックス入りを編集"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" /><path d="m15 5 4 4" /></svg></a></td>
              </tr>
              <tr>
                <td class="product-id">984</td>
                <td class="product-image"><span class="no-image">画像なし</span></td>
                <td class="product-name">名入れタオル</td>
                <td class="product-price">¥1,500</td>
                <td class="product-stock">40</td>
                <td><span class="publish-state is-private">非公開</span></td>
                <td class="product-updated"><time datetime="2026-10-18T16:40:00+09:00"><span>2026/10/18</span> <span>16:40</span></time></td>
                <td class="product-edit"><a class="edit-link" href="product-form.<?= $pageExtension ?>" aria-label="名入れタオルを編集" title="名入れタオルを編集"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" /><path d="m15 5 4 4" /></svg></a></td>
              </tr>
              <tr>
                <td class="product-id">983</td>
                <td class="product-image"><img src="assets/images/sample/photo_01.jpg" width="48" height="48" alt="" /></td>
                <td class="product-name">フォトキーホルダー</td>
                <td class="product-price">¥980</td>
                <td class="product-stock">0<span class="stock-note">在庫切れ</span></td>
                <td><span class="publish-state">公開</span></td>
                <td class="product-updated"><time datetime="2026-10-18T10:05:00+09:00"><span>2026/10/18</span> <span>10:05</span></time></td>
                <td class="product-edit"><a class="edit-link" href="product-form.<?= $pageExtension ?>" aria-label="フォトキーホルダーを編集" title="フォトキーホルダーを編集"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" /><path d="m15 5 4 4" /></svg></a></td>
              </tr>
              <tr>
                <td class="product-id">982</td>
                <td class="product-image"><img src="assets/images/sample/photo_02.jpg" width="48" height="48" alt="" /></td>
                <td class="product-name">オリジナルラベルギフト</td>
                <td class="product-price">¥3,200</td>
                <td class="product-stock">12</td>
                <td><span class="publish-state is-private">非公開</span></td>
                <td class="product-updated"><time datetime="2026-10-17T15:20:00+09:00"><span>2026/10/17</span> <span>15:20</span></time></td>
                <td class="product-edit"><a class="edit-link" href="product-form.<?= $pageExtension ?>" aria-label="オリジナルラベルギフトを編集" title="オリジナルラベルギフトを編集"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" /><path d="m15 5 4 4" /></svg></a></td>
              </tr>
              <tr>
                <td class="product-id">981</td>
                <td class="product-image"><img src="assets/images/sample/photo_01.jpg" width="48" height="48" alt="" /></td>
                <td class="product-name">記念写真の卓上カレンダー</td>
                <td class="product-price">¥2,400</td>
                <td class="product-stock">60</td>
                <td><span class="publish-state">公開</span></td>
                <td class="product-updated"><time datetime="2026-10-17T09:00:00+09:00"><span>2026/10/17</span> <span>09:00</span></time></td>
                <td class="product-edit"><a class="edit-link" href="product-form.<?= $pageExtension ?>" aria-label="記念写真の卓上カレンダーを編集" title="記念写真の卓上カレンダーを編集"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" /><path d="m15 5 4 4" /></svg></a></td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- 表示確認用。1ページ50件・986件で20ページの例。総件数・現在ページ・番号・URLはバックエンドが決める。 -->
        <!-- hrefとpageパラメータ名は仮。バックエンドのルーティングに合わせて置き換える。 -->
        <nav class="pagination" aria-label="商品一覧のページ">
          <ul class="pagination-list" role="list">
            <?php if ($isPagerMiddleState): ?>
              <li>
                <a href="?page=9">
                  <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="m15 18-6-6 6-6" /></svg>
                  <span class="sr-only">前のページ</span>
                </a>
              </li>
              <li><a href="?page=1">1<span class="sr-only">ページ</span></a></li>
              <li><span class="pagination-ellipsis" aria-hidden="true">…</span></li>
              <li><a href="?page=9">9<span class="sr-only">ページ</span></a></li>
              <li><a href="?page=10" aria-current="page">10<span class="sr-only">ページ</span></a></li>
              <li><a href="?page=11">11<span class="sr-only">ページ</span></a></li>
              <li><span class="pagination-ellipsis" aria-hidden="true">…</span></li>
              <li><a href="?page=20">20<span class="sr-only">ページ</span></a></li>
              <li>
                <a href="?page=11">
                  <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="m9 18 6-6-6-6" /></svg>
                  <span class="sr-only">次のページ</span>
                </a>
              </li>
            <?php else: ?>
              <li><a href="?page=1" aria-current="page">1<span class="sr-only">ページ</span></a></li>
              <li><a href="?page=2">2<span class="sr-only">ページ</span></a></li>
              <li><a href="?page=3">3<span class="sr-only">ページ</span></a></li>
              <li><a href="?page=4">4<span class="sr-only">ページ</span></a></li>
              <li><a href="?page=5">5<span class="sr-only">ページ</span></a></li>
              <li><span class="pagination-ellipsis" aria-hidden="true">…</span></li>
              <li><a href="?page=20">20<span class="sr-only">ページ</span></a></li>
              <li>
                <a href="?page=2">
                  <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="m9 18 6-6-6-6" /></svg>
                  <span class="sr-only">次のページ</span>
                </a>
              </li>
            <?php endif; ?>
          </ul>
          <p class="page-status"><?= $isPagerMiddleState ? '10' : '1' ?> / 20<span class="sr-only">ページ</span></p>
        </nav>
        <?php endif; ?>
      </main>
    </div>
    <script src="assets/js/common.js"></script>
  </body>
</html>

