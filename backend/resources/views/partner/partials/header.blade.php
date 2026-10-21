{{-- =======================================
 * 熊日モール Client 共通ヘッダー
 * URL: /backend/resources/views/partner/partials/header.blade.php
 * Referenced in: /backend/resources/views/layouts/partner.blade.php
 * 元: /shop-partner/includes/inc_header.php
 * Created: 2026-10-21
 * Last updated: 2026-10-21
 * ======================================= --}}
<header class="admin-header">
    <div class="box-identity">
        <span class="service-name">熊日モール</span>
        <span class="admin-name">パートナー管理画面</span>
    </div>

    <div class="box-actions">
        {{-- TODO: 公開サイトのURLが確定したらリンク先を設定 --}}
        <a href="#">サイトを確認</a>

        {{-- TODO: ログインの作業でログアウト処理を組み込む --}}
        <form action="#" method="post">
            <button type="submit">ログアウト</button>
        </form>
    </div>
</header>
