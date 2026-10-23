<?php
/* =======================================
 * 熊日モール Master パスワードの再設定（申し込み・送信しました）
 * URL: /shop-master/password-forgot.php
 * Referenced in: /docs/design/COMMON_LOGIN.md・/index.html
 * Created: 2026-10-23
 * Last updated: 2026-10-23
 * ======================================= */

// 画面はClientと共通。Master用の表記・項目に切り替えて、Clientのテンプレートを読む。
$adminType = 'master';
require __DIR__ . '/../shop-partner/password-forgot.php';
