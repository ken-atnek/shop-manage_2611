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

        <!-- 列見出しと注文の箱を同じ領域で横スクロールさせる。 -->
        <div class="orders-scroll" role="region" aria-label="注文一覧（横スクロール対応）" tabindex="0">
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
              <span class="column-label">進捗状況</span>
              <span class="column-label">操作</span>
            </div>
            <ol class="order-list" role="list" aria-label="未着手の注文">
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
                        aria-haspopup="true"
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
                            <input type="radio" name="order-status-choice-2610170112345" value="1" id="status-2610170112345-registered" checked />
                            <label for="status-2610170112345-registered" class="status-registered">新規受付</label>
                          </li>
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2610170112345" value="2" id="status-2610170112345-preparing" />
                            <label for="status-2610170112345-preparing" class="status-preparing">対応中</label>
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
                    <p class="updated"><time datetime="2026-10-17T08:40">2026/10/17 08:40</time></p>
                  </div>
                  <div class="order-actions">
                    <button
                      class="order-toggle has-content"
                      type="button"
                      aria-expanded="false"
                      aria-controls="order-detail-2610170112345"
                      aria-label="注文#2610170112345のメモ・入稿画像を開く（メモ・入稿画像あり）"
                      title="注文#2610170112345のメモ・入稿画像を開く（メモ・入稿画像あり）"
                    >
                      <svg width="22" height="18" viewBox="0 0 22 18" stroke-width="1.5" aria-hidden="true" focusable="false">
                        <rect x="1" y="1" width="20" height="16" />
                        <path d="M1 13h20" />
                        <path class="panel-chevron" d="M7 6l4 3 4-3" />
                      </svg>
                    </button>
                  </div>
                  <div class="order-detail" id="order-detail-2610170112345" hidden>
                    <!-- メモ・入稿画像の中身はステップ5で作る。 -->
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
                        class="selectbox__head"
                        type="button"
                        aria-label="注文#2026110300002のステータスを変更（現在：新規受付）"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-controls="status-panel-2026110300002"
                      >
                        <span class="selectbox__value" data-selectbox-value>新規受付</span>
                        <i aria-hidden="true"></i>
                      </button>
                      <!-- 表示例の選択値。保存・送信処理は未実装。 -->
                      <input type="hidden" name="order-status[2026110300002]" value="1" data-selectbox-hidden />
                      <div class="list-wrapper" id="status-panel-2026110300002" hidden>
                        <ul class="selectbox__panel" role="radiogroup" aria-label="注文#2026110300002のステータス">
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2026110300002" value="1" id="status-2026110300002-registered" checked />
                            <label for="status-2026110300002-registered" class="status-registered">新規受付</label>
                          </li>
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2026110300002" value="2" id="status-2026110300002-preparing" />
                            <label for="status-2026110300002-preparing" class="status-preparing">対応中</label>
                          </li>
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2026110300002" value="3" id="status-2026110300002-shipped" />
                            <label for="status-2026110300002-shipped" class="status-shipped">発送済</label>
                          </li>
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2026110300002" value="4" id="status-2026110300002-return" />
                            <label for="status-2026110300002-return" class="status-return">返品対応</label>
                          </li>
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2026110300002" value="5" id="status-2026110300002-cancelled" />
                            <label for="status-2026110300002-cancelled" class="status-cancelled">キャンセル</label>
                          </li>
                        </ul>
                      </div>
                    </div>
                    <p class="updated"><time datetime="2026-10-17T08:25">2026/10/17 08:25</time></p>
                  </div>
                  <div class="order-actions">
                    <button
                      class="order-toggle has-content"
                      type="button"
                      aria-expanded="false"
                      aria-controls="order-detail-2026110300002"
                      aria-label="注文#2026110300002のメモ・入稿画像を開く（メモ・入稿画像あり）"
                      title="注文#2026110300002のメモ・入稿画像を開く（メモ・入稿画像あり）"
                    >
                      <svg width="22" height="18" viewBox="0 0 22 18" stroke-width="1.5" aria-hidden="true" focusable="false">
                        <rect x="1" y="1" width="20" height="16" />
                        <path d="M1 13h20" />
                        <path class="panel-chevron" d="M7 6l4 3 4-3" />
                      </svg>
                    </button>
                  </div>
                  <div class="order-detail" id="order-detail-2026110300002" hidden>
                    <!-- メモ・入稿画像の中身はステップ5で作る。 -->
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
                        aria-haspopup="true"
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
                            <input type="radio" name="order-status-choice-2026110300003" value="1" id="status-2026110300003-registered" checked />
                            <label for="status-2026110300003-registered" class="status-registered">新規受付</label>
                          </li>
                          <li role="presentation">
                            <input type="radio" name="order-status-choice-2026110300003" value="2" id="status-2026110300003-preparing" />
                            <label for="status-2026110300003-preparing" class="status-preparing">対応中</label>
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
                    <p class="updated"><time datetime="2026-10-17T08:10">2026/10/17 08:10</time></p>
                  </div>
                  <div class="order-actions">
                    <button
                      class="order-toggle"
                      type="button"
                      aria-expanded="false"
                      aria-controls="order-detail-2026110300003"
                      aria-label="注文#2026110300003のメモ・入稿画像を開く"
                      title="注文#2026110300003のメモ・入稿画像を開く"
                    >
                      <svg width="22" height="18" viewBox="0 0 22 18" stroke-width="1.5" aria-hidden="true" focusable="false">
                        <rect x="1" y="1" width="20" height="16" />
                        <path d="M1 13h20" />
                        <path class="panel-chevron" d="M7 6l4 3 4-3" />
                      </svg>
                    </button>
                  </div>
                  <div class="order-detail" id="order-detail-2026110300003" hidden>
                    <!-- メモ・入稿画像の中身はステップ5で作る。 -->
                  </div>
                </article>
              </li>
            </ol>
          </div>
        </div>
      </main>
    </div>
    <script src="assets/js/order.js"></script>
  </body>
</html>
