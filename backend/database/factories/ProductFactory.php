<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $names = [
            '写真入りマグカップ',
            '名入れタオル',
            'フォトキーホルダー',
            'オリジナルラベルギフト',
            '記念写真の卓上カレンダー',
            '名入れ木製スマホスタンド',
            'くまもと焼き菓子セット',
            '阿蘇のはちみつギフト',
            '天草の海産物詰め合わせ',
            'オリジナル写真トートバッグ',
            '名入れボールペン',
            '大切な記念日の写真でつくる オリジナルフォトパネル 木製スタンド付き ギフトボックス入り 名入れメッセージカードと選べるラッピング付きの特別セット',
        ];
        $temperatures = ['normal', 'cold', 'frozen'];
        $noStockManagement = fake()->boolean(10);
        $stockQuantity = null;

        if (!$noStockManagement) {
            $stockQuantity = match (fake()->numberBetween(0, 4)) {
                0 => 0,
                1 => fake()->numberBetween(1, 5),
                default => fake()->numberBetween(6, 100),
            };
        }

        $isFood = fake()->boolean();
        $now = now()->startOfSecond();
        $createdAt = $now->copy()->subSeconds(fake()->numberBetween(0, 60 * 24 * 60 * 60));
        $updatedAt = $createdAt->copy()->addSeconds(fake()->numberBetween(
            0,
            $now->getTimestamp() - $createdAt->getTimestamp(),
        ));

        return [
            'store_id' => Store::factory(),
            'name' => $names[fake()->numberBetween(0, count($names) - 1)],
            'description' => 'ローカル動作確認用の商品です。',
            'price_incl_tax_yen' => fake()->numberBetween(500, 20000),
            'tax_rate_percent' => fake()->boolean() ? 8 : 10,
            'no_stock_management' => $noStockManagement,
            'stock_quantity' => $stockQuantity,
            'temperature' => $temperatures[fake()->numberBetween(0, count($temperatures) - 1)],
            'packing_length_cm' => number_format(fake()->numberBetween(10, 1000) / 10, 1, '.', ''),
            'packing_width_cm' => number_format(fake()->numberBetween(10, 1000) / 10, 1, '.', ''),
            'packing_height_cm' => number_format(fake()->numberBetween(10, 1000) / 10, 1, '.', ''),
            'packing_weight_kg' => number_format(fake()->numberBetween(10, 2000) / 100, 2, '.', ''),
            'accepts_upload' => fake()->boolean(),
            'is_food' => $isFood,
            'food_label' => $isFood ? '原材料名：砂糖、小麦粉。内容量：1個。' : null,
            'allergens' => $isFood ? '小麦を含みます。' : null,
            'publish_status' => fake()->boolean(80) ? 'public' : 'private',
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }
}
