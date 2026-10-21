<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\Partner;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed local verification data without replacing existing records.
     */
    public function run(): void
    {
        if (!app()->environment('local')) {
            $this->command?->warn('ローカル環境ではないため、確認用データは作成しません。');
            return;
        }

        if (Partner::query()->exists()) {
            $this->command?->warn('partners に既存データがあるため、確認用データは作成しません。');
            return;
        }

        $carriers = Carrier::query()
            ->whereIn('code', ['yamato', 'sagawa', 'yupack'])
            ->get()
            ->keyBy('code');

        if (!$carriers->has('yamato') || !$carriers->has('sagawa') || !$carriers->has('yupack')) {
            $this->command?->warn('CarrierSeeder を先に実行してください：php artisan db:seed --class=CarrierSeeder');
            return;
        }

        DB::transaction(function () use ($carriers) {
            $partner1 = Partner::factory()->create(['name' => 'くまもとギフト工房']);
            $partner2 = Partner::factory()->create(['name' => '阿蘇の贈りもの']);

            $yamatoStore = Store::factory()->for($partner1)->create([
                'carrier_id' => $carriers->get('yamato')->getKey(),
            ]);
            $yupackStore = Store::factory()->for($partner1)->create([
                'carrier_id' => $carriers->get('yupack')->getKey(),
            ]);
            $sagawaStore = Store::factory()->for($partner2)->create([
                'carrier_id' => $carriers->get('sagawa')->getKey(),
            ]);

            $yamatoProducts = Product::factory()->count(493)->for($yamatoStore)->create();
            Product::factory()->count(493)->for($yupackStore)->create();
            Product::factory()->count(30)->for($sagawaStore)->create();

            $sameUpdatedAt = now()->startOfSecond();
            foreach ($yamatoProducts->take(5) as $product) {
                $product->updated_at = $sameUpdatedAt->copy();
                $product->save();
            }
        });

        $this->command?->info(sprintf(
            'パートナー：%d件、店舗：%d件、商品：%d件',
            Partner::query()->count(),
            Store::query()->count(),
            Product::query()->count(),
        ));
    }
}
