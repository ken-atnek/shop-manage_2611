<?php

namespace Database\Seeders;

use App\Models\Carrier;
use Illuminate\Database\Seeder;

class CarrierSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach ([
            'yamato' => 'ヤマト運輸',
            'sagawa' => '佐川急便',
            'yupack' => 'ゆうパック',
        ] as $code => $name) {
            Carrier::updateOrCreate(
                ['code' => $code],
                ['name' => $name],
            );
        }
    }
}
