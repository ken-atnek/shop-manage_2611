<!doctype html>
{{-- =======================================
 * 熊日モール Client 共通レイアウト
 * URL: /backend/resources/views/layouts/partner.blade.php
 * Referenced in: /backend/resources/views/partner/products/index.blade.php
 * 元: /shop-partner/products.php
 * Created: 2026-10-21
 * Last updated: 2026-10-21
 * ======================================= --}}
<html lang="ja">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>@yield('title')｜熊日モール パートナー管理画面</title>
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
        <link rel="stylesheet" href="{{ asset('assets/partner/css/common.css') }}?v={{ filemtime(public_path('assets/partner/css/common.css')) }}" />
        @stack('styles')
    </head>

    <body class="admin-page">
        @include('partner.partials.header')
        <div class="admin-layout">
            @include('partner.partials.sidebar')
            <main class="admin-main">
                @yield('content')
            </main>
        </div>
        <script src="{{ asset('assets/partner/js/common.js') }}?v={{ filemtime(public_path('assets/partner/js/common.js')) }}"></script>
        @stack('scripts')
    </body>
</html>
