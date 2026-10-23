<?php
/* =======================================
 * 熊日モール Client 共通ヘッダー
 * URL: /shop-partner/includes/inc_header.php
 * Referenced in: /shop-partner/orders.php・/shop-partner/products.php・/shop-partner/product-form.php・/shop-partner/stores.php・/shop-partner/store-form.php・/shop-partner/customers.php・/shop-partner/customer-detail.php・/shop-partner/sales.php
 * Created: 2026-10-17
 * Last updated: 2026-10-23
 * ======================================= */

// パートナー名は表示例。Bladeではログイン中のパートナー名を出す。
$partnerAdminName = '熊日メディアコム 管理画面';
?>
    <header class="admin-header">
      <div class="box-identity">
        <span class="service-name">熊日モール</span>
        <span class="admin-name" title="<?= htmlspecialchars($partnerAdminName, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($partnerAdminName, ENT_QUOTES, 'UTF-8') ?></span>
      </div>

      <div class="box-actions">
        <a href="#">サイトを確認</a>

        <form action="#" method="post">
          <button type="submit">ログアウト</button>
        </form>
      </div>
    </header>
