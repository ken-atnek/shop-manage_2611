<?php

namespace Tests\Feature\Partner;

use App\Models\Partner;
use App\Models\Product;
use App\Models\Store;
use Database\Seeders\CarrierSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ProductIndexTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    private Store $otherStore;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CarrierSeeder::class);
        $partner = Partner::factory()->create(['id' => 1, 'name' => '確認用パートナー']);
        $otherPartner = Partner::factory()->create(['name' => '別のパートナー']);
        $this->store = Store::factory()->for($partner)->create(['name' => '確認用店舗', 'size_based' => false]);
        $this->otherStore = Store::factory()->for($otherPartner)->create(['name' => '別の店舗', 'size_based' => false]);
    }

    public function test_自分のパートナーの商品だけを表示する(): void
    {
        $attributes = [
            'name' => '自分のパートナーの商品',
            'no_stock_management' => false,
            'stock_quantity' => Product::LOW_STOCK_THRESHOLD + 1,
            'publish_status' => 'public',
            'updated_at' => '2026-10-21 12:00:00',
        ];
        $ownProduct = Product::factory()->for($this->store)->create($attributes);
        Product::factory()->for($this->otherStore)->create(array_replace($attributes, ['name' => '別のパートナー専用商品']));

        $response = $this->get(route('partner.products.index'));

        $this->assertProductIds($response, [$ownProduct->id]);
        $response->assertSee('自分のパートナーの商品')->assertDontSee('別のパートナー専用商品');
    }

    public function test_更新日時の降順で同じ日時ならIDの降順に並ぶ(): void
    {
        $products = Product::factory()->for($this->store)->state([
            'no_stock_management' => false,
            'stock_quantity' => Product::LOW_STOCK_THRESHOLD + 1,
            'publish_status' => 'public',
        ])->createMany([
            ['name' => '古い商品', 'updated_at' => '2026-10-20 23:59:59'],
            ['name' => '同日時の先の商品', 'updated_at' => '2026-10-21 12:00:00'],
            ['name' => '同日時の後の商品', 'updated_at' => '2026-10-21 12:00:00'],
        ]);

        $this->assertProductIds($this->get(route('partner.products.index')), [
            $products[2]->id, $products[1]->id, $products[0]->id,
        ]);
    }

    public function test_50件ずつページ送りして範囲外は検索条件を保って最終ページへ戻す(): void
    {
        $products = Product::factory()->for($this->store)->count(51)->create([
            'name' => 'ページ確認マグ',
            'no_stock_management' => false,
            'stock_quantity' => Product::LOW_STOCK_THRESHOLD + 1,
            'publish_status' => 'public',
            'updated_at' => '2026-10-21 12:00:00',
        ]);
        $ids = $products->sortByDesc('id')->modelKeys();

        $first = $this->get(route('partner.products.index'));
        $this->assertProductIds($first, array_slice($ids, 0, 50), 51);
        $this->assertSame(1, $first->viewData('products')->currentPage());
        $this->assertSame(2, $first->viewData('products')->lastPage());

        $second = $this->get(route('partner.products.index', ['page' => 2]));
        $this->assertProductIds($second, array_slice($ids, 50), 51);
        $this->assertSame(2, $second->viewData('products')->currentPage());

        $this->get(route('partner.products.index', ['page' => 999]))
            ->assertRedirect(route('partner.products.index', ['page' => 2]));
        $this->get(route('partner.products.index', ['keyword' => 'ページ確認', 'page' => 999]))
            ->assertRedirect(route('partner.products.index', ['keyword' => 'ページ確認', 'page' => 2]));
    }

    public function test_開始日が不正なときは形式エラーだけを出して絞り込まない(): void
    {
        $products = Product::factory()->for($this->store)->state([
            'no_stock_management' => false,
            'stock_quantity' => Product::LOW_STOCK_THRESHOLD + 1,
            'publish_status' => 'public',
            'updated_at' => '2026-10-21 12:00:00',
        ])->createMany([['name' => '検索語を含むマグ'], ['name' => '検索条件に合わないタオル']]);

        $response = $this->get(route('partner.products.index', [
            'keyword' => 'マグ',
            'publish' => ['private'],
            'stock' => ['empty'],
            'updated_from' => 'abc',
            'updated_to' => '2026-10-10',
        ]));

        $this->assertProductIds($response, [$products[1]->id, $products[0]->id]);
        $response->assertSee('日付を正しく入力してください')
            ->assertDontSee('終了日は開始日以降の日付を指定してください')
            ->assertSee('検索条件に合わないタオル')
            ->assertSee('すべてを表示');
        $this->assertFalse($response->viewData('hasConditions'));
        $this->assertTrue($response->viewData('detailOpen'));
    }

    public function test_開始日が終了日より後なら逆転エラーを出して絞り込まない(): void
    {
        $product = Product::factory()->for($this->store)->create([
            'name' => '条件に合わない商品',
            'no_stock_management' => false,
            'stock_quantity' => Product::LOW_STOCK_THRESHOLD + 1,
            'publish_status' => 'public',
            'updated_at' => '2026-10-21 12:00:00',
        ]);

        $response = $this->get(route('partner.products.index', [
            'keyword' => '該当しない検索語',
            'updated_from' => '2026-10-20',
            'updated_to' => '2026-10-10',
        ]));

        $this->assertProductIds($response, [$product->id]);
        $response->assertSee('終了日は開始日以降の日付を指定してください')
            ->assertDontSee('日付を正しく入力してください')
            ->assertSee('条件に合わない商品');
        $this->assertFalse($response->viewData('hasConditions'));
    }

    public function test_キーワードは前後の空白を除いて商品名の部分一致で検索する(): void
    {
        $products = Product::factory()->for($this->store)->state([
            'no_stock_management' => false,
            'stock_quantity' => Product::LOW_STOCK_THRESHOLD + 1,
            'publish_status' => 'public',
            'updated_at' => '2026-10-21 12:00:00',
        ])->createMany([['name' => '写真入りマグカップ'], ['name' => '名入れタオル']]);

        $response = $this->get(route('partner.products.index', ['keyword' => ' マグ ']));

        $this->assertProductIds($response, [$products[0]->id]);
        $response->assertSee('写真入りマグカップ')->assertDontSee('名入れタオル');
    }

    public function test_半角数字の検索はID完全一致と商品名部分一致のORになる(): void
    {
        $products = Product::factory()->for($this->store)->state([
            'no_stock_management' => false,
            'stock_quantity' => Product::LOW_STOCK_THRESHOLD + 1,
            'publish_status' => 'public',
            'updated_at' => '2026-10-21 12:00:00',
        ])->createMany([
            ['id' => 123, 'name' => 'ID一致の陶器'],
            ['id' => 456, 'name' => '記念123号マグ'],
            ['id' => 1234, 'name' => 'ID前方一致は対象外'],
        ]);

        $response = $this->get(route('partner.products.index', ['keyword' => '123']));

        $this->assertProductIds($response, [$products[1]->id, $products[0]->id]);
        $response->assertSee('ID一致の陶器')->assertSee('記念123号マグ')->assertDontSee('ID前方一致は対象外');
    }

    public function test_パーセントとアンダースコアは文字として検索する(): void
    {
        $products = Product::factory()->for($this->store)->state([
            'no_stock_management' => false,
            'stock_quantity' => Product::LOW_STOCK_THRESHOLD + 1,
            'publish_status' => 'public',
            'updated_at' => '2026-10-21 12:00:00',
        ])->createMany([
            ['name' => '100%果汁'],
            ['name' => '100円'],
            ['name' => '記念_セット'],
            ['name' => '記念品セット'],
        ]);

        $percent = $this->get(route('partner.products.index', ['keyword' => '100%']));
        $this->assertProductIds($percent, [$products[0]->id]);
        $percent->assertSee('100%果汁')->assertDontSee('100円');

        $underscore = $this->get(route('partner.products.index', ['keyword' => '記念_']));
        $this->assertProductIds($underscore, [$products[2]->id]);
        $underscore->assertSee('記念_セット')->assertDontSee('記念品セット');
    }

    public function test_公開状態の単独選択と両方選択で絞り込む(): void
    {
        $products = Product::factory()->for($this->store)->state([
            'no_stock_management' => false,
            'stock_quantity' => Product::LOW_STOCK_THRESHOLD + 1,
            'updated_at' => '2026-10-21 12:00:00',
        ])->createMany([
            ['name' => '公開商品', 'publish_status' => 'public'],
            ['name' => '非公開商品', 'publish_status' => 'private'],
        ]);

        $public = $this->get(route('partner.products.index', ['publish' => ['public']]));
        $this->assertProductIds($public, [$products[0]->id]);
        $public->assertDontSee('非公開商品')->assertDontSee('class="publish-state is-private"', false);

        $private = $this->get(route('partner.products.index', ['publish' => ['private']]));
        $this->assertProductIds($private, [$products[1]->id]);
        $private->assertSee('非公開商品')->assertSee('class="publish-state is-private"', false);

        $this->assertProductIds(
            $this->get(route('partner.products.index', ['publish' => ['public', 'private']])),
            [$products[1]->id, $products[0]->id],
        );
    }

    public function test_在庫区分の境界と複数選択のORを判定する(): void
    {
        $products = Product::factory()->for($this->store)->state([
            'publish_status' => 'public',
            'updated_at' => '2026-10-21 12:00:00',
        ])->createMany([
            ['name' => '在庫ゼロ', 'no_stock_management' => false, 'stock_quantity' => 0],
            ['name' => '在庫ひとつ', 'no_stock_management' => false, 'stock_quantity' => 1],
            ['name' => '在庫が基準値', 'no_stock_management' => false, 'stock_quantity' => Product::LOW_STOCK_THRESHOLD],
            ['name' => '在庫が基準値超え', 'no_stock_management' => false, 'stock_quantity' => Product::LOW_STOCK_THRESHOLD + 1],
            ['name' => '在庫管理なしの商品', 'no_stock_management' => true, 'stock_quantity' => null],
        ]);

        foreach ([
            [['empty'], [$products[0]->id]],
            [['low'], [$products[2]->id, $products[1]->id]],
            [['available'], [$products[4]->id, $products[3]->id]],
            [['low', 'empty'], [$products[2]->id, $products[1]->id, $products[0]->id]],
        ] as [$stock, $ids]) {
            $this->assertProductIds($this->get(route('partner.products.index', ['stock' => $stock])), $ids);
        }
    }

    public function test_日本時間の更新日で両端と片側指定を判定する(): void
    {
        $this->assertSame('Asia/Tokyo', config('app.timezone'));
        $products = Product::factory()->for($this->store)->state([
            'no_stock_management' => false,
            'stock_quantity' => Product::LOW_STOCK_THRESHOLD + 1,
            'publish_status' => 'public',
        ])->createMany([
            ['name' => '前日の最後', 'updated_at' => '2026-10-20 23:59:59'],
            ['name' => '当日の最初', 'updated_at' => '2026-10-21 00:00:00'],
            ['name' => '当日の最後', 'updated_at' => '2026-10-21 23:59:59'],
            ['name' => '翌日の最初', 'updated_at' => '2026-10-22 00:00:00'],
        ]);

        $sameDay = $this->get(route('partner.products.index', ['updated_from' => '2026-10-21', 'updated_to' => '2026-10-21']));
        $this->assertProductIds($sameDay, [$products[2]->id, $products[1]->id]);
        $sameDay->assertSee('当日の最初')->assertSee('当日の最後')->assertDontSee('前日の最後')->assertDontSee('翌日の最初');

        $this->assertProductIds(
            $this->get(route('partner.products.index', ['updated_to' => '2026-10-21'])),
            [$products[2]->id, $products[1]->id, $products[0]->id],
        );
        $this->assertProductIds(
            $this->get(route('partner.products.index', ['updated_from' => '2026-10-21'])),
            [$products[3]->id, $products[2]->id, $products[1]->id],
        );
    }

    public function test_キーワードと公開状態と在庫はANDで組み合わせる(): void
    {
        $products = Product::factory()->for($this->store)->state([
            'no_stock_management' => false,
            'updated_at' => '2026-10-21 12:00:00',
        ])->createMany([
            ['name' => '対象のマグ', 'publish_status' => 'private', 'stock_quantity' => Product::LOW_STOCK_THRESHOLD],
            ['name' => '名前だけ違うタオル', 'publish_status' => 'private', 'stock_quantity' => Product::LOW_STOCK_THRESHOLD],
            ['name' => '公開のマグ', 'publish_status' => 'public', 'stock_quantity' => Product::LOW_STOCK_THRESHOLD],
            ['name' => '在庫が多いマグ', 'publish_status' => 'private', 'stock_quantity' => Product::LOW_STOCK_THRESHOLD + 1],
        ]);

        $response = $this->get(route('partner.products.index', ['keyword' => 'マグ', 'publish' => ['private'], 'stock' => ['low']]));

        $this->assertProductIds($response, [$products[0]->id]);
        $response->assertSee('対象のマグ')->assertDontSee('名前だけ違うタオル')->assertDontSee('公開のマグ')->assertDontSee('在庫が多いマグ');
    }

    public function test_基準値の商品と在庫管理なしの表示が出る(): void
    {
        $products = Product::factory()->for($this->store)->state([
            'publish_status' => 'public',
            'updated_at' => '2026-10-21 12:00:00',
        ])->createMany([
            ['name' => '基準値の商品', 'no_stock_management' => false, 'stock_quantity' => Product::LOW_STOCK_THRESHOLD],
            ['name' => '管理しない商品', 'no_stock_management' => true, 'stock_quantity' => null],
        ]);

        $response = $this->get(route('partner.products.index'));

        $this->assertProductIds($response, [$products[1]->id, $products[0]->id]);
        $response->assertSee('<span class="stock-note">残りわずか</span>', false)
            ->assertSee('<span aria-hidden="true">—</span><span class="sr-only">在庫管理なし</span>', false);
    }

    /**
     * @param list<int> $ids
     */
    private function assertProductIds(TestResponse $response, array $ids, ?int $total = null): void
    {
        $response->assertOk()->assertViewIs('partner.products.index');
        $products = $response->viewData('products');
        $this->assertSame($ids, $products->getCollection()->modelKeys());
        $this->assertSame($total ?? count($ids), $products->total());
    }
}
