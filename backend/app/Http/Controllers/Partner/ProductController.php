<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $filters = $this->normalizeFilters($request);
        $dateRules = [
            'updated_from' => ['nullable', 'date_format:Y-m-d'],
            'updated_to' => ['nullable', 'date_format:Y-m-d'],
        ];

        if ($filters['updated_from'] !== '' && Validator::make(
            ['updated_from' => $filters['updated_from']],
            ['updated_from' => $dateRules['updated_from']],
        )->passes()) {
            $dateRules['updated_to'][] = 'after_or_equal:updated_from';
        }

        $validator = Validator::make($filters, $dateRules, [
            'updated_to.after_or_equal' => '終了日は開始日以降の日付を指定してください',
            // TODO: 形式エラーの文言はフロントに確認
            'date_format' => '日付を正しく入力してください',
        ]);
        $hasErrors = $validator->fails();
        $hasDetailConditions = $filters['publish'] !== []
            || $filters['stock'] !== []
            || $filters['updated_from'] !== ''
            || $filters['updated_to'] !== '';
        $hasConditions = !$hasErrors && ($filters['keyword'] !== '' || $hasDetailConditions);
        $detailOpen = $hasDetailConditions || $hasErrors;

        // TODO: 絞り込みはDBの作業で行う（一致方法・在庫区分などの細則が未確定）
        $sampleProducts = $this->sampleProducts();
        $perPage = 50; // 1ページ50件（SPEC確定）
        $page = LengthAwarePaginator::resolveCurrentPage();

        // TODO: DBから取得するときは paginate(50) に置き換える
        $products = new LengthAwarePaginator(
            $sampleProducts->forPage($page, $perPage)->values(),
            $sampleProducts->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );

        if ($products->currentPage() > $products->lastPage()) {
            return redirect($products->url($products->lastPage()));
        }

        $view = view('partner.products.index', [
            'products' => $products,
            'filters' => $filters,
            'hasConditions' => $hasConditions,
            'detailOpen' => $detailOpen,
        ]);

        if ($hasErrors) {
            $view->withErrors($validator);
        }

        return $view;
    }

    /**
     * @return array{keyword: string, publish: array, stock: array, updated_from: string, updated_to: string}
     */
    private function normalizeFilters(Request $request): array
    {
        $query = $request->query();
        $filters = [
            'keyword' => is_string($query['keyword'] ?? null) ? trim($query['keyword']) : '',
            'publish' => [],
            'stock' => [],
            'updated_from' => is_string($query['updated_from'] ?? null) ? $query['updated_from'] : '',
            'updated_to' => is_string($query['updated_to'] ?? null) ? $query['updated_to'] : '',
        ];

        foreach ([
            'publish' => ['public', 'private'],
            'stock' => ['available', 'low', 'empty'],
        ] as $name => $allowedValues) {
            if (is_array($query[$name] ?? null)) {
                $filters[$name] = array_values(array_filter(
                    $query[$name],
                    static fn ($value) => in_array($value, $allowedValues, true),
                ));
            }
        }

        return $filters;
    }

    /**
     * TODO: DBから取得するときに置き換える。stock_state は残りわずかの基準値が決まってから計算する
     *
     * @return Collection<int, \stdClass>
     */
    private function sampleProducts(): Collection
    {
        $products = collect([
            (object) [
                'id' => 986,
                'name' => '写真入りマグカップ',
                'price' => 2800,
                'stock' => 24,
                'stock_state' => null,
                'is_published' => true,
                'image_url' => null,
                'updated_at' => Carbon::parse('2026-10-19 14:30:00', 'Asia/Tokyo'),
            ],
            (object) [
                'id' => 985,
                'name' => '大切な記念日の写真でつくる オリジナルフォトパネル 木製スタンド付き ギフトボックス入り',
                'price' => 6800,
                'stock' => 3,
                'stock_state' => 'low',
                'is_published' => true,
                'image_url' => null,
                'updated_at' => Carbon::parse('2026-10-19 13:15:00', 'Asia/Tokyo'),
            ],
            (object) [
                'id' => 984,
                'name' => '名入れタオル',
                'price' => 1500,
                'stock' => 40,
                'stock_state' => null,
                'is_published' => false,
                'image_url' => null,
                'updated_at' => Carbon::parse('2026-10-18 16:40:00', 'Asia/Tokyo'),
            ],
            (object) [
                'id' => 983,
                'name' => 'フォトキーホルダー',
                'price' => 980,
                'stock' => 0,
                'stock_state' => 'empty',
                'is_published' => true,
                'image_url' => null,
                'updated_at' => Carbon::parse('2026-10-18 10:05:00', 'Asia/Tokyo'),
            ],
            (object) [
                'id' => 982,
                'name' => 'オリジナルラベルギフト',
                'price' => 3200,
                'stock' => 12,
                'stock_state' => null,
                'is_published' => false,
                'image_url' => null,
                'updated_at' => Carbon::parse('2026-10-17 15:20:00', 'Asia/Tokyo'),
            ],
            (object) [
                'id' => 981,
                'name' => '記念写真の卓上カレンダー',
                'price' => 2400,
                'stock' => 60,
                'stock_state' => null,
                'is_published' => true,
                'image_url' => null,
                'updated_at' => Carbon::parse('2026-10-17 09:00:00', 'Asia/Tokyo'),
            ],
            (object) [
                'id' => 980,
                'name' => '名入れ木製スマホスタンド',
                'price' => 3500,
                'stock' => null,
                'stock_state' => 'unmanaged',
                'is_published' => true,
                'image_url' => null,
                'updated_at' => Carbon::parse('2026-10-16 18:00:00', 'Asia/Tokyo'),
            ],
        ]);

        $templates = $products->all();
        $start = Carbon::parse('2026-10-16 18:00:00', 'Asia/Tokyo');

        for ($index = 0; $index < 979; $index++) {
            $product = clone $templates[$index % count($templates)];
            $product->id = 979 - $index;
            $product->updated_at = $start->copy()->subHours($index + 1);
            $products->push($product);
        }

        return $products;
    }
}
