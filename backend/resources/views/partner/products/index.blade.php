{{-- =======================================
 * 熊日モール Client 商品一覧（確認用）
 * URL: /backend/resources/views/partner/products/index.blade.php
 * Referenced in: /backend/resources/views/partner/products/index.blade.php
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
    {{-- TODO: 商品一覧の中身は次の作業で組み込む --}}
@endsection
