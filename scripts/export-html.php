<?php
/* =======================================
 * 熊日モール 制作用PHPから静的HTMLを書き出す
 * URL: /scripts/export-html.php
 * Referenced in: /docs/coding/IMPLEMENTATION_REFERENCE.md
 * Created: 2026-10-17
 * Last updated: 2026-10-19
 * ======================================= */

// Web経由では書き込まず、DDEV等のPHP CLIからだけ実行する。
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$projectRoot = dirname(__DIR__);
$pages = ['shop-partner/orders', 'shop-partner/products'];

// 新しい画面は、制作を始めた時点で上の一覧へ明示的に追加する。
foreach ($pages as $page) {
    $source = $projectRoot . '/' . $page . '.php';
    $destination = $projectRoot . '/' . $page . '.html';
    $staticExport = true;

    ob_start();
    try {
        require $source;
        $html = ob_get_clean();
    } catch (Throwable $error) {
        ob_end_clean();
        fwrite(STDERR, $error->getMessage() . PHP_EOL);
        exit(1);
    }

    if (!str_starts_with($html, '<!doctype html>') || str_contains($html, '<?php') || str_contains($html, '<?=')) {
        fwrite(STDERR, '静的HTMLの出力内容を確認してください: ' . $page . PHP_EOL);
        exit(1);
    }

    // 既存と同じ場合は触らない。編集元はPHP、HTMLは出力専用とする。
    if (!is_file($destination) || file_get_contents($destination) !== $html) {
        if (file_put_contents($destination, $html) === false) {
            fwrite(STDERR, '書き出しに失敗しました: ' . $destination . PHP_EOL);
            exit(1);
        }
    }
    fwrite(STDOUT, $page . '.html を出力しました' . PHP_EOL);
}
