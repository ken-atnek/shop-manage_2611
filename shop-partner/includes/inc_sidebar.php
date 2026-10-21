<?php
/* =======================================
 * 熊日モール Client 共通サイドバー
 * URL: /shop-partner/includes/inc_sidebar.php
 * Referenced in: /shop-partner/orders.php・/shop-partner/products.php・/shop-partner/product-form.php・/shop-partner/stores.php・/shop-partner/store-form.php
 * Created: 2026-10-17
 * Last updated: 2026-10-20
 * ======================================= */
?>
      <aside class="admin-sidebar">
        <div class="sidebar-content">
          <button class="sidebar-toggle" type="button" aria-expanded="true" aria-controls="client-navigation" aria-label="メニューを畳む" title="メニューを畳む">
            <svg class="sidebar-close-icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><rect width="18" height="18" x="3" y="3" rx="2" /><path d="M9 3v18" /><path d="m16 15-3-3 3-3" /></svg>
            <svg class="sidebar-open-icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><rect width="18" height="18" x="3" y="3" rx="2" /><path d="M9 3v18" /><path d="m14 9 3 3-3 3" /></svg>
          </button>
          <nav id="client-navigation" aria-label="管理メニュー">
            <a href="orders.<?= $pageExtension ?? 'php' ?>" title="受注一覧"<?= ($currentPage ?? '') === 'orders' ? ' aria-current="page"' : '' ?>>
              <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><rect width="8" height="4" x="8" y="2" rx="1" ry="1" /><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" /><path d="M12 11h4" /><path d="M12 16h4" /><path d="M8 11h.01" /><path d="M8 16h.01" /></svg>
              <span class="nav-label">受注一覧</span>
            </a>
            <a href="products.<?= $pageExtension ?? 'php' ?>" title="商品一覧"<?= ($currentPage ?? '') === 'products' ? ' aria-current="page"' : '' ?>>
              <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z" /><path d="M12 22V12" /><polyline points="3.29 7 12 12 20.71 7" /><path d="m7.5 4.27 9 5.15" /></svg>
              <span class="nav-label">商品一覧</span>
            </a>
            <a href="product-form.<?= $pageExtension ?? 'php' ?>?preview=new" title="商品登録"<?= ($currentPage ?? '') === 'product-new' ? ' aria-current="page"' : '' ?>>
              <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M12 22V12" /><path d="M16 17h6" /><path d="M19 14v6" /><path d="M21 10.535V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.729l7 4a2 2 0 0 0 2 .001l1.675-.955" /><path d="M3.29 7 12 12l8.71-5" /><path d="m7.5 4.27 8.997 5.148" /></svg>
              <span class="nav-label">商品登録</span>
            </a>
            <a href="stores.<?= $pageExtension ?? 'php' ?>" title="店舗"<?= ($currentPage ?? '') === 'stores' ? ' aria-current="page"' : '' ?>>
              <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M15 21v-5a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v5" /><path d="M17.774 10.31a1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.451 0 1.12 1.12 0 0 0-1.548 0 2.5 2.5 0 0 1-3.452 0 1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.77-3.248l2.889-4.184A2 2 0 0 1 7 2h10a2 2 0 0 1 1.653.873l2.895 4.192a2.5 2.5 0 0 1-3.774 3.244" /><path d="M4 10.95V19a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8.05" /></svg>
              <span class="nav-label">店舗</span>
            </a>
            <a href="#" title="顧客管理">
              <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><path d="M16 3.128a4 4 0 0 1 0 7.744" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><circle cx="9" cy="7" r="4" /></svg>
              <span class="nav-label">顧客管理</span>
            </a>
            <a href="#" title="集計">
              <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M3 3v16a2 2 0 0 0 2 2h16" /><path d="M18 17V9" /><path d="M13 17V5" /><path d="M8 17v-3" /></svg>
              <span class="nav-label">集計</span>
            </a>
          </nav>
        </div>
      </aside>
