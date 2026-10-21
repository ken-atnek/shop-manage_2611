<?php
/* =======================================
 * 熊日モール Client 地方区分・都道府県（制作用データ）
 * URL: /shop-partner/includes/data_prefectures.php
 * Referenced in: /shop-partner/store-form.php・/docs/SPEC.md
 * Created: 2026-10-21
 * Last updated: 2026-10-21
 * ======================================= */

// 制作用。Bladeでは本番のデータに置き換える。
// 区分と並びの正はdocs/SPEC.md「店舗と送料の案」。コードは都道府県コードの2桁表記。
return [
    'hokkaido' => [
        'name' => '北海道',
        'prefectures' => [
            ['code' => '01', 'name' => '北海道'],
        ],
    ],
    'north-tohoku' => [
        'name' => '北東北',
        'prefectures' => [
            ['code' => '02', 'name' => '青森県'],
            ['code' => '05', 'name' => '秋田県'],
            ['code' => '03', 'name' => '岩手県'],
        ],
    ],
    'south-tohoku' => [
        'name' => '南東北',
        'prefectures' => [
            ['code' => '04', 'name' => '宮城県'],
            ['code' => '06', 'name' => '山形県'],
            ['code' => '07', 'name' => '福島県'],
        ],
    ],
    'kanto' => [
        'name' => '関東',
        'prefectures' => [
            ['code' => '08', 'name' => '茨城県'],
            ['code' => '09', 'name' => '栃木県'],
            ['code' => '10', 'name' => '群馬県'],
            ['code' => '11', 'name' => '埼玉県'],
            ['code' => '12', 'name' => '千葉県'],
            ['code' => '13', 'name' => '東京都'],
            ['code' => '14', 'name' => '神奈川県'],
            ['code' => '19', 'name' => '山梨県'],
        ],
    ],
    'shinetsu' => [
        'name' => '信越',
        'prefectures' => [
            ['code' => '20', 'name' => '長野県'],
            ['code' => '15', 'name' => '新潟県'],
        ],
    ],
    'tokai' => [
        'name' => '東海',
        'prefectures' => [
            ['code' => '22', 'name' => '静岡県'],
            ['code' => '23', 'name' => '愛知県'],
            ['code' => '21', 'name' => '岐阜県'],
            ['code' => '24', 'name' => '三重県'],
        ],
    ],
    'hokuriku' => [
        'name' => '北陸',
        'prefectures' => [
            ['code' => '16', 'name' => '富山県'],
            ['code' => '17', 'name' => '石川県'],
            ['code' => '18', 'name' => '福井県'],
        ],
    ],
    'kansai' => [
        'name' => '関西',
        'prefectures' => [
            ['code' => '26', 'name' => '京都府'],
            ['code' => '25', 'name' => '滋賀県'],
            ['code' => '29', 'name' => '奈良県'],
            ['code' => '30', 'name' => '和歌山県'],
            ['code' => '27', 'name' => '大阪府'],
            ['code' => '28', 'name' => '兵庫県'],
        ],
    ],
    'chugoku' => [
        'name' => '中国',
        'prefectures' => [
            ['code' => '33', 'name' => '岡山県'],
            ['code' => '34', 'name' => '広島県'],
            ['code' => '35', 'name' => '山口県'],
            ['code' => '31', 'name' => '鳥取県'],
            ['code' => '32', 'name' => '島根県'],
        ],
    ],
    'shikoku' => [
        'name' => '四国',
        'prefectures' => [
            ['code' => '37', 'name' => '香川県'],
            ['code' => '36', 'name' => '徳島県'],
            ['code' => '39', 'name' => '高知県'],
            ['code' => '38', 'name' => '愛媛県'],
        ],
    ],
    'north-kyushu' => [
        'name' => '北九州',
        'prefectures' => [
            ['code' => '40', 'name' => '福岡県'],
            ['code' => '41', 'name' => '佐賀県'],
            ['code' => '42', 'name' => '長崎県'],
            ['code' => '44', 'name' => '大分県'],
        ],
    ],
    'south-kyushu' => [
        'name' => '南九州',
        'prefectures' => [
            ['code' => '43', 'name' => '熊本県'],
            ['code' => '45', 'name' => '宮崎県'],
            ['code' => '46', 'name' => '鹿児島県'],
        ],
    ],
    'okinawa' => [
        'name' => '沖縄',
        'prefectures' => [
            ['code' => '47', 'name' => '沖縄県'],
        ],
    ],
];
