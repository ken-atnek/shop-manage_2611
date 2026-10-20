<?php
// 制作確認用。業務処理は持たず、現在地と静的出力時のリンクだけ切り替える。
$currentPage = 'stores';
$pageExtension = ($staticExport ?? false) ? 'html' : 'php';

// 制作時の表示確認専用。Laravel・Bladeでは使用せず、バックエンド側の状態分岐へ置き換える。
$previewState = ($staticExport ?? false) ? '' : ($_GET['preview'] ?? '');
$isEmptyState = $previewState === 'empty';
$isErrorState = $previewState === 'error';
?>
<!doctype html>
<!-- =======================================
 * 熊日モール Client 店舗一覧
 * URL: /shop-partner/stores.<?= $pageExtension . PHP_EOL ?>
 * Referenced in: /docs/design/CLIENT_STORE_LIST.md・/index.html
 * 編集元: /shop-partner/stores.php（.htmlは必要時のみの書き出し物。Git管理外）
 * Created: 2026-10-20
 * Last updated: 2026-10-20
 * ======================================= -->
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>店舗一覧｜熊日モール パートナー管理画面</title>
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
    <link rel="stylesheet" href="assets/css/stores.css" />
  </head>

  <body class="admin-page">
    <?php require __DIR__ . '/includes/inc_header.php'; ?>
    <div class="admin-layout">
      <?php require __DIR__ . '/includes/inc_sidebar.php'; ?>
      <main class="admin-main">
        <div class="page-heading">
          <h1 id="store-list-heading">店舗一覧</h1>
          <a class="store-add" href="store-form.<?= $pageExtension ?>"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M5 12h14" /><path d="M12 5v14" /></svg>店舗を追加</a>
        </div>
        <?php if ($isErrorState): ?>
          <!-- 取得失敗は0件と区別する。再読み込み先は制作確認用の通常表示。 -->
          <div class="list-empty list-error" role="alert">
            <p class="message">店舗一覧を取得できませんでした</p>
            <p class="error-help">時間をおいて、もう一度読み込んでください。</p>
            <div class="empty-actions">
              <a class="show-all" href="stores.<?= $pageExtension ?>">再読み込み</a>
            </div>
          </div>
        <?php elseif ($isEmptyState): ?>
          <div class="list-empty">
            <p class="message">店舗がまだ登録されていません</p>
            <div class="empty-actions">
              <a class="store-add show-all" href="store-form.<?= $pageExtension ?>"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M5 12h14" /><path d="M12 5v14" /></svg>店舗を追加</a>
            </div>
          </div>
        <?php else: ?>
        <!-- 架空店舗の表示例。ID 1→2→3の登録順。商品数・更新日時は表示確認用で、Bladeへ移さない。 -->
        <!-- 店舗フォームは次の段階で制作。新規・編集へのリンク先は現在未作成。 -->
        <div class="stores-scroll">
          <table class="stores-table" aria-labelledby="store-list-heading">
            <thead>
              <tr>
                <th class="product-id" scope="col">ID</th>
                <th class="store-name" scope="col">店舗名</th>
                <th class="store-carrier" scope="col">運送会社</th>
                <th class="store-shipping" scope="col">送料</th>
                <th class="store-products" scope="col">商品数</th>
                <th class="store-updated" scope="col">更新日時</th>
                <th class="store-edit" scope="col">編集</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="product-id">1</td>
                <td class="store-name">熊日モール サンプル店舗</td>
                <td class="store-carrier">ヤマト運輸</td>
                <td class="store-shipping">サイズ別</td>
                <td class="store-products">24</td>
                <td class="store-updated"><time datetime="2026-10-20T10:30:00+09:00"><span>2026/10/20</span> <span>10:30</span></time></td>
                <td class="store-edit"><a class="edit-link" href="store-form.<?= $pageExtension ?>?id=1" aria-label="熊日モール サンプル店舗を編集" title="熊日モール サンプル店舗を編集"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" /><path d="m15 5 4 4" /></svg></a></td>
              </tr>
              <tr>
                <td class="product-id">2</td>
                <td class="store-name">熊本ギフト サンプル店舗</td>
                <td class="store-carrier">ゆうパック</td>
                <td class="store-shipping">サイズ共通</td>
                <td class="store-products">0</td>
                <td class="store-updated"><time datetime="2026-10-19T15:45:00+09:00"><span>2026/10/19</span> <span>15:45</span></time></td>
                <td class="store-edit"><a class="edit-link" href="store-form.<?= $pageExtension ?>?id=2" aria-label="熊本ギフト サンプル店舗を編集" title="熊本ギフト サンプル店舗を編集"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" /><path d="m15 5 4 4" /></svg></a></td>
              </tr>
              <tr>
                <td class="product-id">3</td>
                <td class="store-name">熊本の旬の味覚と地域の特産品を全国へお届けする 阿蘇・天草ふるさとギフト サンプル店舗</td>
                <td class="store-carrier">佐川急便</td>
                <td class="store-shipping">サイズ別</td>
                <td class="store-products">128</td>
                <td class="store-updated"><time datetime="2026-10-18T09:00:00+09:00"><span>2026/10/18</span> <span>09:00</span></time></td>
                <td class="store-edit"><a class="edit-link" href="store-form.<?= $pageExtension ?>?id=3" aria-label="熊本の旬の味覚と地域の特産品を全国へお届けする 阿蘇・天草ふるさとギフト サンプル店舗を編集" title="熊本の旬の味覚と地域の特産品を全国へお届けする 阿蘇・天草ふるさとギフト サンプル店舗を編集"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" /><path d="m15 5 4 4" /></svg></a></td>
              </tr>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </main>
    </div>
    <script src="assets/js/common.js"></script>
  </body>
</html>
