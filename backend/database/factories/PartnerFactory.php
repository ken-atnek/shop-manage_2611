<?php

namespace Database\Factories;

use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Partner>
 */
class PartnerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $names = [
            'くまもとギフト工房',
            '肥後ものづくり舎',
            '熊本くらし商店',
            '阿蘇の贈りもの',
            '天草クラフト',
            '菊池セレクト',
            '八代みやげ企画',
            '玉名手しごと商会',
            '人吉球磨ギフト',
            '水俣ものづくり工房',
        ];

        return [
            'name' => $names[fake()->numberBetween(0, count($names) - 1)],
        ];
    }
}
