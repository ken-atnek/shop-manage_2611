<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        // TODO: ⑤でログインユーザーの所属パートナーに置き換える
        $partnerId = 1;

        // TODO: 絞り込みはDBの作業で行う（一致方法・在庫区分などの細則が未確定）
        // TODO: 同じ更新日時のときのid降順は仮（順序は未確定）
        $products = Product::query()
            ->whereHas('store', fn ($query) => $query->where('partner_id', $partnerId))
            ->orderByDesc('updated_at')
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
