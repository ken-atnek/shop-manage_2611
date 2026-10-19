<?php
/* =======================================
 * 熊日モール Client 共通サイドバー
 * URL: /shop-partner/includes/inc_sidebar.php
 * Referenced in: /shop-partner/orders.php・/shop-partner/products.php
 * Created: 2026-10-17
 * Last updated: 2026-10-19
 * ======================================= */
?>
      <aside class="admin-sidebar">
        <nav aria-label="管理メニュー">
          <a href="orders.<?= $pageExtension ?? 'php' ?>"<?= ($currentPage ?? '') === 'orders' ? ' aria-current="page"' : '' ?>>受注一覧</a>
          <a href="products.<?= $pageExtension ?? 'php' ?>"<?= ($currentPage ?? '') === 'products' ? ' aria-current="page"' : '' ?>>商品一覧</a>
          <a href="#">商品登録</a>
          <a href="#">送料設定</a>
          <a href="#">顧客管理</a>
          <a href="#">集計</a>
        </nav>
      </aside>
