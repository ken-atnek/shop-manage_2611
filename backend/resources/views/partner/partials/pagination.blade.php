{{-- =======================================
 * 熊日モール Client 共通ページ送り
 * URL: /backend/resources/views/partner/partials/pagination.blade.php
 * Referenced in: /backend/resources/views/partner/products/index.blade.php
 * 元: /shop-partner/products.php の nav.pagination
 * Created: 2026-10-21
 * Last updated: 2026-10-21
 * ======================================= --}}
{{-- TODO: 1ページだけのときの表示は未確定。フロントの案（出さない）で仮に実装 --}}
@if ($paginator->lastPage() > 1)
    @php
        $lastPage = $paginator->lastPage();
        $currentPage = $paginator->currentPage();

        if ($lastPage <= 7) {
            $pages = range(1, $lastPage);
        } elseif ($currentPage <= 4) {
            $pages = [1, 2, 3, 4, 5, null, $lastPage];
        } elseif ($currentPage >= $lastPage - 3) {
            $pages = array_merge([1, null], range($lastPage - 4, $lastPage));
        } else {
            $pages = [1, null, $currentPage - 1, $currentPage, $currentPage + 1, null, $lastPage];
        }
    @endphp
    <nav class="pagination" aria-label="{{ $label }}">
        <ul class="pagination-list" role="list">
            @if ($currentPage > 1)
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}">
                        <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="m15 18-6-6 6-6" /></svg>
                        <span class="sr-only">前のページ</span>
                    </a>
                </li>
            @endif
            @foreach ($pages as $page)
                <li>
                    @if ($page === null)
                        <span class="pagination-ellipsis" aria-hidden="true">…</span>
                    @else
                        <a href="{{ $paginator->url($page) }}"@if ($page === $currentPage) aria-current="page"@endif>{{ $page }}<span class="sr-only">ページ</span></a>
                    @endif
                </li>
            @endforeach
            @if ($currentPage < $lastPage)
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}">
                        <svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="m9 18 6-6-6-6" /></svg>
                        <span class="sr-only">次のページ</span>
                    </a>
                </li>
            @endif
        </ul>
        <p class="page-status">{{ $currentPage }} / {{ $lastPage }}<span class="sr-only">ページ</span></p>
    </nav>
@endif
