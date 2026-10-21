<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    // 残りわずかの基準値（全商品共通、QA No.13）
    public const LOW_STOCK_THRESHOLD = 5;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'store_id',
        'name',
        'description',
        'price_incl_tax_yen',
        'tax_rate_percent',
        'no_stock_management',
        'stock_quantity',
        'temperature',
        'packing_length_cm',
        'packing_width_cm',
        'packing_height_cm',
        'packing_weight_kg',
        'accepts_upload',
        'is_food',
        'food_label',
        'allergens',
        'publish_status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'no_stock_management' => 'boolean',
            'packing_length_cm' => 'decimal:1',
            'packing_width_cm' => 'decimal:1',
            'packing_height_cm' => 'decimal:1',
            'packing_weight_kg' => 'decimal:2',
            'accepts_upload' => 'boolean',
            'is_food' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected function stockState(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if ($this->no_stock_management) {
                    return 'unmanaged';
                }

                if ($this->stock_quantity !== null && (int) $this->stock_quantity === 0) {
                    return 'empty';
                }

                if ($this->stock_quantity >= 1 && $this->stock_quantity <= self::LOW_STOCK_THRESHOLD) {
                    return 'low';
                }

                return null;
            },
        );
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
