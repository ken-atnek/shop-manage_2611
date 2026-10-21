<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

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

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
