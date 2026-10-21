<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// TODO: ローカルは仮に /partner を付ける（2026-10-21、担当者決定）。サーバーではサブドメインで分ける予定。ログイン必須にするのはログインの作業で行う
Route::prefix('partner')->name('partner.')->group(function () {
    Route::view('/products', 'partner.products.index')->name('products.index');
});
