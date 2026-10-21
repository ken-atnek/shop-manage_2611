{{-- =======================================
 * 熊日モール Client 商品一覧
 * URL: /backend/resources/views/partner/products/index.blade.php
 * Referenced in: /backend/resources/views/partner/products/index.blade.php・/backend/app/Http/Controllers/Partner/ProductController.php・/backend/resources/views/partner/partials/pagination.blade.php
 * 元: /shop-partner/products.php
 * Created: 2026-10-21
 * Last updated: 2026-10-21
 * ======================================= --}}
@extends('layouts.partner')

@section('title', '商品一覧')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/partner/css/products.css') }}?v={{ filemtime(public_path('assets/partner/css/products.css')) }}" />
@endpush

@section('content')
    <div class="page-heading">
        <h1>商品一覧</h1>
    </div>
    <form class="search-box" action="{{ route('partner.products.index') }}" method="get" aria-label="商品の検索">
        {{-- TODO: 入力値の復元・日付の入力エラー・詳細パネルを開いた表示は次の作業で行う。name は表示例のまま --}}
        <div class="filter-bar">
            <label class="sr-only" for="product-keyword">商品名・IDで検索</label>
            <input type="search" id="product-keyword" name="keyword" placeholder="商品名・IDで検索" />
            <button type="submit">検索</button>
            <button class="detail-toggle" type="button" aria-expanded="false" aria-controls="product-search-panel">詳細検索</button>
        </div>
        <div class="search-panel" id="product-search-panel" inert>
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
                            <input class="form-input" type="date" id="product-updated-from" name="updated_from" />
                            <span aria-hidden="true">〜</span>
                            <label class="sr-only" for="product-updated-to">終了日</label>
                            <input class="form-input" type="date" id="product-updated-to" name="updated_to" />
                        </div>
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
        {{-- TODO: 検索中は「検索結果」と件数を出し分ける --}}
        <span class="count">{{ $products->total() }}件</span>
        <p class="filter-note">すべてを表示</p>
    </div>
    {{-- TODO: 検索結果0件の表示と全件表示・条件解除のリンクは次の作業で組み込む --}}
    @if ($products->total() === 0)
        <div class="list-empty">
            <p class="message">まだ商品はありません</p>
        </div>
    @else
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
                    @foreach ($products as $product)
                        <tr>
                            <td class="product-id">{{ $product->id }}</td>
                            <td class="product-image">
                                @if ($product->image_url)
                                    <img src="{{ $product->image_url }}" width="48" height="48" alt="" loading="lazy" />
                                @else
                                    <span class="no-image">画像なし</span>
                                @endif
                            </td>
                            <td class="product-name">{{ $product->name }}</td>
                            <td class="product-price">¥{{ number_format($product->price) }}</td>
                            <td class="product-stock">
                                @if ($product->stock_state === 'empty')
                                    {{ $product->stock }}<span class="stock-note">在庫切れ</span>
                                @elseif ($product->stock_state === 'low')
                                    {{ $product->stock }}<span class="stock-note">残りわずか</span>
                                @elseif ($product->stock_state === 'unmanaged')
                                    {{-- TODO: フロントに確認 --}}
                                    <span aria-hidden="true">—</span><span class="sr-only">在庫管理なし</span>
                                @else
                                    {{ $product->stock }}
                                @endif
                            </td>
                            <td>
                                @if ($product->is_published)
                                    <span class="publish-state">公開</span>
                                @else
                                    <span class="publish-state is-private">非公開</span>
                                @endif
                            </td>
                            {{-- TODO: 表示タイムゾーンは未確定 --}}
                            <td class="product-updated"><time datetime="{{ $product->updated_at->toIso8601String() }}"><span>{{ $product->updated_at->format('Y/m/d') }}</span> <span>{{ $product->updated_at->format('H:i') }}</span></time></td>
                            {{-- TODO: 商品フォームのBlade化のときに編集URLを設定 --}}
                            <td class="product-edit"><a class="edit-link" href="#" aria-label="{{ $product->name }}を編集" title="{{ $product->name }}を編集"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" /><path d="m15 5 4 4" /></svg></a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('partner.partials.pagination', ['paginator' => $products, 'label' => '商品一覧のページ'])
    @endif
@endsection
