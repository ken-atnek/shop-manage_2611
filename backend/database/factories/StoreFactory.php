<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\Carrier;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $names = [
            '熊本中央店',
            '阿蘇ギフト店',
            '天草ものづくり店',
            '菊池セレクト店',
            '八代みやげ店',
            '玉名手しごと店',
            '人吉ギフト店',
            '水俣クラフト店',
            '宇土くらし店',
            '山鹿工芸店',
            '益城ギフト店',
            '南阿蘇セレクト店',
        ];

        return [
            'partner_id' => Partner::factory(),
            'name' => $names[fake()->numberBetween(0, count($names) - 1)],
            'postal_code' => null,
            'address' => null,
            'contact_name' => null,
            'phone' => null,
            'carrier_id' => static fn () => Carrier::query()->where('code', 'yamato')->firstOrFail()->getKey(),
            'size_based' => fake()->boolean(),
            'free_shipping_threshold_yen' => null,
        ];
    }
}
