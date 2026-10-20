<?php
// 制作確認用。業務処理は持たず、現在地と静的出力時のリンクだけ切り替える。
$currentPage = 'orders';
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
$listCount = ($isEmptyState || $isNoResultsState) ? 0 : 8;
// KPIは検索条件に関係なく全体の件数を表す。データなしのときだけ0件にする。
$kpiCounts = $isEmptyState ? ['all' => 0, 'new' => 0, 'waiting' => 0] : ['all' => 8, 'new' => 3, 'waiting' => 2];
$filterNote = $isNoResultsState ? '検索結果' : 'すべてを表示';
?>
<!doctype html>
<!-- =======================================
 * 熊日モール Client 受注一覧
 * URL: /shop-partner/orders.<?= $pageExtension . PHP_EOL ?>
 * Referenced in: /shop-partner/orders.<?= $pageExtension . PHP_EOL ?>
 * 編集元: /shop-partner/orders.php（.htmlは必要時のみの書き出し物。Git管理外）
 * Created: 2026-10-17
 * Last updated: 2026-10-20
 * ======================================= -->
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>受注一覧｜熊日モール パートナー管理画面</title>
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
        <!-- 表示例は固定。初期選択は「すべて」。一覧見出しの件数はKPIと連動させる（一覧は先頭3件のみ掲載）。 -->
        <ul class="kpi-list" role="list" aria-label="注文の絞り込み">
          <li>
            <button type="button" aria-pressed="true">
              <span class="label">すべて</span>
              <span class="count"><strong><?= $kpiCounts['all'] ?></strong><span class="unit">件</span></span>
            </button>
          </li>
          <li>
            <button type="button" aria-pressed="false">
              <span class="label">未着手</span>
              <span class="count"><strong><?= $kpiCounts['new'] ?></strong><span class="unit">件</span></span>
            </button>
          </li>
          <li>
            <button type="button" aria-pressed="false">
              <span class="label">発送待ち</span>
              <span class="count"><strong><?= $kpiCounts['waiting'] ?></strong><span class="unit">件</span></span>
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
              aria-expanded="<?= $isDateErrorState ? 'true' : 'false' ?>"
              aria-controls="order-search-panel"
            >
              詳細検索
            </button>
          </div>
          <div class="search-panel" id="order-search-panel"<?= $isDateErrorState ? '' : ' inert' ?>>
            <div class="search-panel-inner">
              <div class="search-fields">
                <!-- nameは表示例。バックエンドの正式な項目名を決めるものではない。 -->
                <fieldset class="form-field date-field">
                  <legend class="form-label">注文日</legend>
                  <div class="date-range">
                    <label class="sr-only" for="order-date-from">開始日</label>
                    <input class="form-input" type="date" id="order-date-from" name="date_from"<?= $isDateErrorState ? ' value="2026-10-20" aria-invalid="true" aria-describedby="order-date-error"' : '' ?> />
                    <span aria-hidden="true">〜</span>
                    <label class="sr-only" for="order-date-to">終了日</label>
                    <input class="form-input" type="date" id="order-date-to" name="date_to"<?= $isDateErrorState ? ' value="2026-10-10" aria-invalid="true" aria-describedby="order-date-error"' : '' ?> />
                  </div>
                  <?php if ($isDateErrorState): ?>
                    <!-- エラー時だけ出力する。日付範囲の判定はバックエンドで行う。 -->
                    <p class="form-error" id="order-date-error">終了日は開始日以降の日付を指定してください</p>
                  <?php endif; ?>
                </fieldset>
                <fieldset class="form-field status-field">
                  <legend class="form-label">ステータス</legend>
                  <div class="check-group">
                    <label class="form-check" for="search-status-new">
                      <input type="checkbox" id="search-status-new" name="status[]" value="new" />
                      <span>新規受付</span>
                    </label>
                    <label class="form-check" for="search-status-processing">
                      <input type="checkbox" id="search-status-processing" name="status[]" value="processing" />
                      <span>対応中</span>
                    </label>
                    <label class="form-check" for="search-status-shipped">
                      <input type="checkbox" id="search-status-shipped" name="status[]" value="shipped" />
                      <span>発送済</span>
                    </label>
                    <label class="form-check" for="search-status-return">
                      <input type="checkbox" id="search-status-return" name="status[]" value="return" />
                      <span>返品対応</span>
                    </label>
                    <label class="form-check" for="search-status-cancelled">
                      <input type="checkbox" id="search-status-cancelled" name="status[]" value="cancelled" />
                      <span>キャンセル</span>
                    </label>
                  </div>
                </fieldset>
                <div class="form-field">
                  <label class="form-label" for="order-phone">電話番号</label>
                  <input class="form-input" type="tel" id="order-phone" name="phone" />
                </div>
                <div class="form-field">
                  <label class="form-label" for="order-email">メールアドレス</label>
                  <input class="form-input" type="email" id="order-email" name="email" spellcheck="false" />
                </div>
                <div class="form-field product-field">
                  <label class="form-label" for="order-product">商品名</label>
                  <input class="form-input" type="text" id="order-product" name="product" />
                </div>
                <div class="search-actions">
                  <button class="clear-conditions" type="button">条件をクリア</button>
                  <button type="submit">この条件で検索</button>
                </div>
              </div>
            </div>
          </div>
        </form>
        <div class="list-heading" aria-live="polite" aria-atomic="true">
          <h2>注文一覧</h2>
          <span class="count"><?= $listCount ?>件</span>
          <p class="filter-note"><?= $filterNote ?></p>
        </div>

        <?php if ($isEmptyState || $isNoResultsState): ?>
          <div class="list-empty">
            <p class="message"><?= $isEmptyState ? 'まだ注文はありません' : '条件に一致する注文はありません' ?></p>
            <?php if ($isNoResultsState): ?>
              <div class="empty-actions">
                <a class="show-all" href="orders.<?= $pageExtension ?>">すべての注文を表示</a>
                <!-- 仮リンク。検索条件を解除する遷移先はバックエンド実装時に決める。 -->
                <a href="#">条件をクリア</a>
              </div>
            <?php endif; ?>
          </div>
        <?php else: ?>
        <!-- 列見出しと注文の箱をまとめる。スクロールは縦横ともページ全体で行う。 -->
        <div class="orders-scroll">
          <div class="orders-board">
            <div class="column-head" aria-hidden="true">
              <span class="column-label">注文・お届け先</span>
              <div class="product-column">
                <span class="column-label">商品</span>
                <div class="item-head">
                  <span>商品名</span>
                  <span>個数</span>
                  <span>小計</span>
                </div>
              </div>
              <div class="progress-column">
                <span class="column-label">進捗状況</span>
                <span class="column-label">操作</span>
              </div>
            </div>
            <ol class="order-list" role="list" aria-label="すべての注文">
              <li>
                <article class="order-card" aria-labelledby="order-2610170112345">
                  <div class="destination">
                    <div class="order-heading">
                      <h3 id="order-2610170112345">#2610170112345</h3>
                      <time datetime="2026-10-17T09:30">2026/10/17 09:30</time>
                    </div>
                    <div class="shipping-info" role="group" aria-label="お届け先">
                      <p class="name">佐藤 花子</p>
                      <p class="address">〒000-0000 熊本県サンプル市中央1-2-3</p>
                      <p>TEL 090-0000-0001</p>
                      <p>hanako@example.com</p>
                    </div>
                  </div>
                  <div class="order-products">
                    <table class="item-table" aria-label="注文2610170112345の商品明細と送料・合計">
                      <colgroup>
                        <col />
                        <col class="quantity" />
                        <col class="subtotal" />
                      </colgroup>
                      <thead>
                        <tr>
                          <th scope="col"><span class="sr-only">商品名</span></th>
                          <th scope="col"><span class="sr-only">個数</span></th>
                          <th scope="col"><span class="sr-only">小計</span></th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td class="item-name">写真入りマグカップ</td>
                          <td class="quantity">1</td>
                          <td class="subtotal">¥2,800</td>
                        </tr>
                        <tr>
                          <td class="item-name">名入れタオル</td>
                          <td class="quantity">1</td>
                          <td class="subtotal">¥1,500</td>
                        </tr>
                      </tbody>
                      <tfoot>
                        <tr>
                          <th scope="row" colspan="2">送料</th>
                          <td>¥800</td>
                        </tr>
                        <tr >
                          <th scope="row" colspan="2">合計（税込）</th>
                          <td class="total"><strong>¥5,100</strong></td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                  <div class="order-progress">
                    <div class="select-customer-status" data-selectbox>
                      <button
                        class="selectbox__head"
                        type="button"
                        aria-label="注文#2610170112345のステータスを変更（現在：新規受付）"
                        aria-expanded="false"
                        aria-controls="status-panel-2610170112345"
                      >
                        <span class="selectbox__value" data-selectbox-value>新規受付</span>
                        <i aria-hidden="true"></i>
                      </button>
                      <!-- 表示例の選択値。保存・送信処理は未実装。 -->
                      <input type="hidden" name="order-status[2610170112345]" value="1" data-selectbox-hidden />
                      <div class="list-wrapper" id="status-panel-2610170112345" hidden>
                        <ul class="selectbox__panel" role="radiogroup" aria-label="注文#2610170112345のステータス">
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2610170112345" value="1" id="status-2610170112345-new" checked />
                            <label for="status-2610170112345-new" class="status-new">新規受付</label>
                          </li>
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2610170112345" value="2" id="status-2610170112345-processing" />
                            <label for="status-2610170112345-processing" class="status-processing">対応中</label>
                          </li>
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2610170112345" value="3" id="status-2610170112345-shipped" />
                            <label for="status-2610170112345-shipped" class="status-shipped">発送済</label>
                          </li>
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2610170112345" value="4" id="status-2610170112345-return" />
                            <label for="status-2610170112345-return" class="status-return">返品対応</label>
                          </li>
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2610170112345" value="5" id="status-2610170112345-cancelled" />
                            <label for="status-2610170112345-cancelled" class="status-cancelled">キャンセル</label>
                          </li>
                        </ul>
                      </div>
                    </div>
                    <p class="updated"><span class="sr-only">更新日時：</span><time datetime="2026-10-17T08:40">2026/10/17 08:40</time></p>
                    <button
                      class="order-toggle has-content"
                      type="button"
                      aria-expanded="false"
                      aria-controls="order-detail-2610170112345"
                      aria-label="注文#2610170112345の詳細を開く（内容あり）"
                      title="注文#2610170112345の詳細を開く（内容あり）"
                    >
                      <svg width="22" height="18" viewBox="0 0 22 18" stroke-width="1.5" aria-hidden="true" focusable="false">
                        <rect x="1" y="1" width="20" height="16" />
                        <path d="M1 13h20" />
                        <path class="panel-chevron" d="M7 6l4 3 4-3" />
                      </svg>
                    </button>
                  </div>
                  <div class="order-detail" id="order-detail-2610170112345" inert>
                    <div class="order-detail-inner">
                      <section class="submissions" aria-labelledby="submissions-2610170112345">
                        <h4 id="submissions-2610170112345">入稿画像（注文者より）</h4>
                        <ul class="submission-products" role="list">
                          <li>
                            <p class="product-name">写真入りマグカップ</p>
                            <ul class="file-list" role="list" aria-label="写真入りマグカップの入稿画像">
                              <!-- sample/の画像は表示確認用。実際の入稿データではない。 -->
                              <li class="file-row">
                                <img src="assets/images/sample/photo_01.jpg" width="48" height="48" alt="" />
                                <span class="file-name">photo_01.jpg</span>
                                <button class="image-preview" type="button" data-image-src="assets/images/sample/photo_01.jpg" data-image-alt="写真入りマグカップの入稿画像 photo_01.jpg" data-file-name="photo_01.jpg" aria-label="photo_01.jpgを拡大表示" title="photo_01.jpgを拡大表示" aria-haspopup="dialog" aria-controls="submission-preview">
                                  <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false">
                                    <circle cx="11" cy="11" r="8" />
                                    <line x1="21" x2="16.65" y1="21" y2="16.65" />
                                    <line x1="11" x2="11" y1="8" y2="14" />
                                    <line x1="8" x2="14" y1="11" y2="11" />
                                  </svg>
                                </button>
                                <a class="file-download" href="assets/images/sample/photo_01.jpg" download="photo_01.jpg" aria-label="photo_01.jpgをダウンロード" title="photo_01.jpgをダウンロード">
                                  <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false">
                                    <path d="M12 15V3" />
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <path d="m7 10 5 5 5-5" />
                                  </svg>
                                </a>
                              </li>
                              <li class="file-row">
                                <img src="assets/images/sample/photo_02.jpg" width="48" height="48" alt="" />
                                <span class="file-name">photo_02.jpg</span>
                                <button class="image-preview" type="button" data-image-src="assets/images/sample/photo_02.jpg" data-image-alt="写真入りマグカップの入稿画像 photo_02.jpg" data-file-name="photo_02.jpg" aria-label="photo_02.jpgを拡大表示" title="photo_02.jpgを拡大表示" aria-haspopup="dialog" aria-controls="submission-preview">
                                  <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false">
                                    <circle cx="11" cy="11" r="8" />
                                    <line x1="21" x2="16.65" y1="21" y2="16.65" />
                                    <line x1="11" x2="11" y1="8" y2="14" />
                                    <line x1="8" x2="14" y1="11" y2="11" />
                                  </svg>
                                </button>
                                <a class="file-download" href="assets/images/sample/photo_02.jpg" download="photo_02.jpg" aria-label="photo_02.jpgをダウンロード" title="photo_02.jpgをダウンロード">
                                  <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false">
                                    <path d="M12 15V3" />
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <path d="m7 10 5 5 5-5" />
                                  </svg>
                                </a>
                              </li>
                            </ul>
                          </li>
                        </ul>
                      </section>
                      <div class="detail-notes">
                        <section class="customer-note" aria-labelledby="customer-note-2610170112345">
                          <h4 id="customer-note-2610170112345">備考（お客様より）</h4>
                          <p>マグカップの写真は明るめに補正をお願いします。</p>
                        </section>
                        <form class="cancel-record" method="post" action="" data-cancel-record hidden>
                          <!-- name・明細ID・送信先は表示例。正式な項目名・注文の識別・保存処理はバックエンドで決める。 -->
                          <h4>キャンセル・返品の記録</h4>
                          <div class="record-reason">
                            <label for="cancel-reason-2610170112345">理由・対応メモ<span class="sr-only">（注文#2610170112345）</span></label>
                            <textarea id="cancel-reason-2610170112345" name="cancel_reason" rows="3"></textarea>
                          </div>
                          <div class="item-note">
                            <label for="item-note-2610170112345-1">写真入りマグカップ<span class="sr-only">のメモ（注文#2610170112345）</span></label>
                            <textarea id="item-note-2610170112345-1" name="item_notes[2610170112345-1]" rows="2" maxlength="100"></textarea>
                          </div>
                          <div class="item-note">
                            <label for="item-note-2610170112345-2">名入れタオル<span class="sr-only">のメモ（注文#2610170112345）</span></label>
                            <textarea id="item-note-2610170112345-2" name="item_notes[2610170112345-2]" rows="2" maxlength="100"></textarea>
                          </div>
                          <button class="record-save" type="submit">保存<span class="sr-only">（注文#2610170112345のキャンセル・返品の記録）</span></button>
                        </form>
                      </div>
                    </div>
                  </div>
                </article>
              </li>
              <li>
                <article class="order-card" aria-labelledby="order-2026110300002">
                  <div class="destination">
                    <div class="order-heading">
                      <h3 id="order-2026110300002">#2026110300002</h3>
                      <time datetime="2026-10-17T09:15">2026/10/17 09:15</time>
                    </div>
                    <div class="shipping-info" role="group" aria-label="お届け先">
                      <p class="name">田中 健</p>
                      <p class="address">〒000-0000 熊本県サンプル市東2-3-4</p>
                      <p>TEL 090-0000-0002</p>
                      <p>ken@example.com</p>
                    </div>
                  </div>
                  <div class="order-products">
                    <table class="item-table" aria-label="注文2026110300002の商品明細と送料・合計">
                      <colgroup>
                        <col />
                        <col class="quantity" />
                        <col class="subtotal" />
                      </colgroup>
                      <thead>
                        <tr>
                          <th scope="col"><span class="sr-only">商品名</span></th>
                          <th scope="col"><span class="sr-only">個数</span></th>
                          <th scope="col"><span class="sr-only">小計</span></th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td class="item-name">オリジナルラベルギフト</td>
                          <td class="quantity">1</td>
                          <td class="subtotal">¥1,200</td>
                        </tr>
                      </tbody>
                      <tfoot>
                        <tr>
                          <th scope="row" colspan="2">送料</th>
                          <td>¥800</td>
                        </tr>
                        <tr>
                          <th scope="row" colspan="2">合計（税込）</th>
                          <td class="total"><strong>¥2,000</strong></td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                  <div class="order-progress">
                    <div class="select-customer-status" data-selectbox>
                      <button
                        class="selectbox__head status-return"
                        type="button"
                        aria-label="注文#2026110300002のステータスを変更（現在：返品対応）"
                        aria-expanded="false"
                        aria-controls="status-panel-2026110300002"
                      >
                        <span class="selectbox__value" data-selectbox-value>返品対応</span>
                        <i aria-hidden="true"></i>
                      </button>
                      <!-- 表示例の選択値。保存・送信処理は未実装。 -->
                      <input type="hidden" name="order-status[2026110300002]" value="4" data-selectbox-hidden />
                      <div class="list-wrapper" id="status-panel-2026110300002" hidden>
                        <ul class="selectbox__panel" role="radiogroup" aria-label="注文#2026110300002のステータス">
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2026110300002" value="1" id="status-2026110300002-new" />
                            <label for="status-2026110300002-new" class="status-new">新規受付</label>
                          </li>
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2026110300002" value="2" id="status-2026110300002-processing" />
                            <label for="status-2026110300002-processing" class="status-processing">対応中</label>
                          </li>
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2026110300002" value="3" id="status-2026110300002-shipped" />
                            <label for="status-2026110300002-shipped" class="status-shipped">発送済</label>
                          </li>
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2026110300002" value="4" id="status-2026110300002-return" checked />
                            <label for="status-2026110300002-return" class="status-return">返品対応</label>
                          </li>
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2026110300002" value="5" id="status-2026110300002-cancelled" />
                            <label for="status-2026110300002-cancelled" class="status-cancelled">キャンセル</label>
                          </li>
                        </ul>
                      </div>
                    </div>
                    <p class="updated"><span class="sr-only">更新日時：</span><time datetime="2026-10-17T08:25">2026/10/17 08:25</time></p>
                    <button
                      class="order-toggle has-content"
                      type="button"
                      aria-expanded="false"
                      aria-controls="order-detail-2026110300002"
                      aria-label="注文#2026110300002の詳細を開く（内容あり）"
                      title="注文#2026110300002の詳細を開く（内容あり）"
                    >
                      <svg width="22" height="18" viewBox="0 0 22 18" stroke-width="1.5" aria-hidden="true" focusable="false">
                        <rect x="1" y="1" width="20" height="16" />
                        <path d="M1 13h20" />
                        <path class="panel-chevron" d="M7 6l4 3 4-3" />
                      </svg>
                    </button>
                  </div>
                  <div class="order-detail" id="order-detail-2026110300002" inert>
                    <div class="order-detail-inner">
                      <section class="submissions" aria-labelledby="submissions-2026110300002">
                        <h4 id="submissions-2026110300002">入稿画像（注文者より）</h4>
                        <ul class="submission-products" role="list">
                          <li>
                            <p class="product-name">オリジナルラベルギフト</p>
                            <ul class="file-list" role="list" aria-label="オリジナルラベルギフトの入稿画像">
                              <!-- sample/の画像を表示確認用に流用。注文間で入稿データを共有する仕様ではない。 -->
                              <li class="file-row">
                                <img src="assets/images/sample/photo_02.jpg" width="48" height="48" alt="" />
                                <span class="file-name">photo_02.jpg</span>
                                <button class="image-preview" type="button" data-image-src="assets/images/sample/photo_02.jpg" data-image-alt="オリジナルラベルギフトの入稿画像 photo_02.jpg" data-file-name="photo_02.jpg" aria-label="photo_02.jpgを拡大表示" title="photo_02.jpgを拡大表示" aria-haspopup="dialog" aria-controls="submission-preview">
                                  <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false">
                                    <circle cx="11" cy="11" r="8" />
                                    <line x1="21" x2="16.65" y1="21" y2="16.65" />
                                    <line x1="11" x2="11" y1="8" y2="14" />
                                    <line x1="8" x2="14" y1="11" y2="11" />
                                  </svg>
                                </button>
                                <a class="file-download" href="assets/images/sample/photo_02.jpg" download="photo_02.jpg" aria-label="photo_02.jpgをダウンロード" title="photo_02.jpgをダウンロード">
                                  <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false">
                                    <path d="M12 15V3" />
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <path d="m7 10 5 5 5-5" />
                                  </svg>
                                </a>
                              </li>
                            </ul>
                          </li>
                        </ul>
                      </section>
                      <div class="detail-notes">
                        <section class="customer-note" aria-labelledby="customer-note-2026110300002">
                          <h4 id="customer-note-2026110300002">備考（お客様より）</h4>
                          <p>名入れの文字は「祝 還暦」で確定済み。</p>
                        </section>
                        <form class="cancel-record" method="post" action="" data-cancel-record>
                          <!-- name・明細ID・送信先は表示例。正式な項目名・注文の識別・保存処理はバックエンドで決める。 -->
                          <h4>キャンセル・返品の記録</h4>
                          <div class="record-reason">
                            <label for="cancel-reason-2026110300002">理由・対応メモ<span class="sr-only">（注文#2026110300002）</span></label>
                            <textarea id="cancel-reason-2026110300002" name="cancel_reason" rows="3">ラベルの印字不良のため返品対応。お客様と返送方法を確認済み。</textarea>
                          </div>
                          <div class="item-note">
                            <label for="item-note-2026110300002-1">オリジナルラベルギフト<span class="sr-only">のメモ（注文#2026110300002）</span></label>
                            <textarea id="item-note-2026110300002-1" name="item_notes[2026110300002-1]" rows="2" maxlength="100">印字部分を確認し、返品受付済み。</textarea>
                          </div>
                          <button class="record-save" type="submit">保存<span class="sr-only">（注文#2026110300002のキャンセル・返品の記録）</span></button>
                        </form>
                      </div>
                    </div>
                  </div>
                </article>
              </li>
              <li>
                <article class="order-card" aria-labelledby="order-2026110300003">
                  <div class="destination">
                    <div class="order-heading">
                      <h3 id="order-2026110300003">#2026110300003</h3>
                      <time datetime="2026-10-17T08:50">2026/10/17 08:50</time>
                    </div>
                    <div class="shipping-info" role="group" aria-label="お届け先">
                      <p class="name">鈴木 美咲</p>
                      <p class="address">〒000-0000 熊本県サンプル市西3-4-5</p>
                      <p>TEL 090-0000-0003</p>
                      <p>misaki@example.com</p>
                    </div>
                  </div>
                  <div class="order-products">
                    <table class="item-table" aria-label="注文2026110300003の商品明細と送料・合計">
                      <colgroup>
                        <col />
                        <col class="quantity" />
                        <col class="subtotal" />
                      </colgroup>
                      <thead>
                        <tr>
                          <th scope="col"><span class="sr-only">商品名</span></th>
                          <th scope="col"><span class="sr-only">個数</span></th>
                          <th scope="col"><span class="sr-only">小計</span></th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td class="item-name">名入れタオル</td>
                          <td class="quantity">2</td>
                          <td class="subtotal">¥3,000</td>
                        </tr>
                      </tbody>
                      <tfoot>
                        <tr>
                          <th scope="row" colspan="2">送料</th>
                          <td>¥800</td>
                        </tr>
                        <tr>
                          <th scope="row" colspan="2">合計（税込）</th>
                          <td class="total"><strong>¥3,800</strong></td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                  <div class="order-progress">
                    <div class="select-customer-status" data-selectbox>
                      <button
                        class="selectbox__head"
                        type="button"
                        aria-label="注文#2026110300003のステータスを変更（現在：新規受付）"
                        aria-expanded="false"
                        aria-controls="status-panel-2026110300003"
                      >
                        <span class="selectbox__value" data-selectbox-value>新規受付</span>
                        <i aria-hidden="true"></i>
                      </button>
                      <!-- 表示例の選択値。保存・送信処理は未実装。 -->
                      <input type="hidden" name="order-status[2026110300003]" value="1" data-selectbox-hidden />
                      <div class="list-wrapper" id="status-panel-2026110300003" hidden>
                        <ul class="selectbox__panel" role="radiogroup" aria-label="注文#2026110300003のステータス">
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2026110300003" value="1" id="status-2026110300003-new" checked />
                            <label for="status-2026110300003-new" class="status-new">新規受付</label>
                          </li>
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2026110300003" value="2" id="status-2026110300003-processing" />
                            <label for="status-2026110300003-processing" class="status-processing">対応中</label>
                          </li>
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2026110300003" value="3" id="status-2026110300003-shipped" />
                            <label for="status-2026110300003-shipped" class="status-shipped">発送済</label>
                          </li>
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2026110300003" value="4" id="status-2026110300003-return" />
                            <label for="status-2026110300003-return" class="status-return">返品対応</label>
                          </li>
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2026110300003" value="5" id="status-2026110300003-cancelled" />
                            <label for="status-2026110300003-cancelled" class="status-cancelled">キャンセル</label>
                          </li>
                        </ul>
                      </div>
                    </div>
                    <p class="updated"><span class="sr-only">更新日時：</span><time datetime="2026-10-17T08:10">2026/10/17 08:10</time></p>
                    <button
                      class="order-toggle"
                      type="button"
                      aria-expanded="false"
                      aria-controls="order-detail-2026110300003"
                      aria-label="注文#2026110300003の詳細を開く"
                      title="注文#2026110300003の詳細を開く"
                    >
                      <svg width="22" height="18" viewBox="0 0 22 18" stroke-width="1.5" aria-hidden="true" focusable="false">
                        <rect x="1" y="1" width="20" height="16" />
                        <path d="M1 13h20" />
                        <path class="panel-chevron" d="M7 6l4 3 4-3" />
                      </svg>
                    </button>
                  </div>
                  <div class="order-detail" id="order-detail-2026110300003" inert>
                    <div class="order-detail-inner">
                      <div class="detail-notes">
                        <p class="detail-empty">備考・入稿画像はありません</p>
                        <form class="cancel-record" method="post" action="" data-cancel-record hidden>
                          <!-- name・明細ID・送信先は表示例。正式な項目名・注文の識別・保存処理はバックエンドで決める。 -->
                          <h4>キャンセル・返品の記録</h4>
                          <div class="record-reason">
                            <label for="cancel-reason-2026110300003">理由・対応メモ<span class="sr-only">（注文#2026110300003）</span></label>
                            <textarea id="cancel-reason-2026110300003" name="cancel_reason" rows="3"></textarea>
                          </div>
                          <div class="item-note">
                            <label for="item-note-2026110300003-1">名入れタオル<span class="sr-only">のメモ（注文#2026110300003）</span></label>
                            <textarea id="item-note-2026110300003-1" name="item_notes[2026110300003-1]" rows="2" maxlength="100"></textarea>
                          </div>
                          <button class="record-save" type="submit">保存<span class="sr-only">（注文#2026110300003のキャンセル・返品の記録）</span></button>
                        </form>
                      </div>
                    </div>
                  </div>
                </article>
              </li>
            </ol>
          </div>
        </div>
        <!-- 表示確認用。実際は1ページ50件で、総件数・現在ページ・番号・URLをバックエンドが決める。通常表示の8件にページ送りは不要。 -->
        <!-- hrefとpageパラメータ名は仮。バックエンドのルーティングに合わせて置き換える。 -->
        <nav class="pagination" aria-label="注文一覧のページ">
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
    <!-- すべての入稿画像で共用する拡大表示。内容は押したボタンのdata属性から設定する。 -->
    <dialog class="image-dialog" id="submission-preview" aria-labelledby="preview-file-name">
      <div class="dialog-content">
        <h2 id="preview-file-name">入稿画像</h2>
        <img class="preview-image" alt="" />
        <button class="dialog-close" type="button" autofocus>閉じる</button>
      </div>
    </dialog>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/order.js"></script>
  </body>
</html>
