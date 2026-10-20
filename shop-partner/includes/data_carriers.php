<?php
/* =======================================
 * 熊日モール Client 運送会社の規格（制作用データ）
 * URL: /shop-partner/includes/data_carriers.php
 * Referenced in: /shop-partner/product-form.php・/docs/SPEC.md・販売店画面（制作時）
 * Created: 2026-10-20
 * Last updated: 2026-10-20
 * ======================================= */

// 制作用の表示例。Bladeでは本番のデータに置き換える。
// 規格の正・公式出典・確認日はdocs/SPEC.md。「その他」だけ販売店側の設定を使う。
return [
    'yamato' => [
        'name' => 'ヤマト運輸',
        'sizes' => [
            ['size' => 60, 'maxWeightKg' => 2],
            ['size' => 80, 'maxWeightKg' => 5],
            ['size' => 100, 'maxWeightKg' => 10],
            ['size' => 120, 'maxWeightKg' => 15],
            ['size' => 140, 'maxWeightKg' => 20],
            ['size' => 160, 'maxWeightKg' => 25],
            ['size' => 180, 'maxWeightKg' => 30],
            ['size' => 200, 'maxWeightKg' => 30],
        ],
        'cool' => [
            'refrigerated' => ['maxTotalCm' => 120, 'maxWeightKg' => 15],
            'frozen' => ['maxTotalCm' => 120, 'maxWeightKg' => 15],
        ],
    ],
    'sagawa' => [
        'name' => '佐川急便',
        'sizes' => [
            ['size' => 60, 'maxWeightKg' => 2],
            ['size' => 80, 'maxWeightKg' => 5],
            ['size' => 100, 'maxWeightKg' => 10],
            ['size' => 140, 'maxWeightKg' => 20],
            ['size' => 160, 'maxWeightKg' => 30],
        ],
        'cool' => [
            'refrigerated' => ['maxTotalCm' => 140, 'maxWeightKg' => 30],
            'frozen' => ['maxTotalCm' => 140, 'maxWeightKg' => 30],
        ],
    ],
    'yupack' => [
        'name' => 'ゆうパック',
        'sizes' => [
            ['size' => 60, 'maxWeightKg' => 25],
            ['size' => 80, 'maxWeightKg' => 25],
            ['size' => 100, 'maxWeightKg' => 25],
            ['size' => 120, 'maxWeightKg' => 25],
            ['size' => 140, 'maxWeightKg' => 25],
            ['size' => 160, 'maxWeightKg' => 25],
            ['size' => 170, 'maxWeightKg' => 25],
        ],
        'cool' => [
            'refrigerated' => ['maxTotalCm' => 150, 'maxWeightKg' => 25, 'maxLongestCm' => 100],
            'frozen' => null,
        ],
    ],
    'other' => [
        'name' => 'その他',
        // 未設定の印。商品フォームへ渡す際は販売店の独自規格を結合する。
        'sizes' => [],
        'cool' => null,
    ],
];
