<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            'date_format' => '日付を正しく入力してください',
        ]);
        $hasErrors = $validator->fails();
        $hasDetailConditions = $filters['publish'] !== []
            || $filters['stock'] !== []
            || $filters['updated_from'] !== ''
            || $filters['updated_to'] !== '';
        $hasConditions = !$hasErrors && ($filters['keyword'] !== '' || $hasDetailConditions);
        $detailOpen = $hasDetailConditions || $hasErrors;

        // TODO: ⑤でログインユーザーの所属パートナーに置き換える
        $partnerId = 1;

        $query = Product::query()
            ->whereHas('store', fn ($query) => $query->where('partner_id', $partnerId));

        if (!$hasErrors) {
            $this->applyFilters($query, $filters);
        }

        // TODO: 同じ更新日時のときのid降順は仮（順序は未確定）
        $products = $query->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(50) // 1ページ50件（SPEC確定）
            ->withQueryString();

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
     * @param array{keyword: string, publish: array, stock: array, updated_from: string, updated_to: string} $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if ($filters['keyword'] !== '') {
            // LIKEの特殊文字をリテラルとして検索する（!自体もエスケープする）。
            $keyword = strtr($filters['keyword'], ['!' => '!!', '%' => '!%', '_' => '!_', '\\' => '!\\']);
            $query->where(function (Builder $keywordQuery) use ($filters, $keyword) {
                $keywordQuery->whereRaw("name LIKE ? ESCAPE '!'", ['%'.$keyword.'%']);

                if (ctype_digit($filters['keyword'])) {
                    $keywordQuery->orWhere('id', $filters['keyword']);
                }
            });
        }

        if ($filters['publish'] !== []) {
            $query->whereIn('publish_status', $filters['publish']);
        }

        if ($filters['stock'] !== []) {
            $query->where(function (Builder $stockQuery) use ($filters) {
                if (in_array('available', $filters['stock'], true)) {
                    $stockQuery->orWhere(function (Builder $availableQuery) {
                        $availableQuery->where('no_stock_management', true)
                            ->orWhere(function (Builder $managedQuery) {
                                $managedQuery->where('no_stock_management', false)
                                    ->where('stock_quantity', '>', Product::LOW_STOCK_THRESHOLD);
                            });
                    });
                }

                if (in_array('low', $filters['stock'], true)) {
                    $stockQuery->orWhere(function (Builder $lowQuery) {
                        $lowQuery->where('no_stock_management', false)
                            ->whereBetween('stock_quantity', [1, Product::LOW_STOCK_THRESHOLD]);
                    });
                }

                if (in_array('empty', $filters['stock'], true)) {
                    $stockQuery->orWhere(function (Builder $emptyQuery) {
                        $emptyQuery->where('no_stock_management', false)
                            ->where('stock_quantity', 0);
                    });
                }
            });
        }

        if ($filters['updated_from'] !== '') {
            $query->where('updated_at', '>=', Carbon::parse($filters['updated_from'], config('app.timezone'))->startOfDay());
        }

        if ($filters['updated_to'] !== '') {
            $query->where('updated_at', '<', Carbon::parse($filters['updated_to'], config('app.timezone'))->startOfDay()->addDay());
        }
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
}
