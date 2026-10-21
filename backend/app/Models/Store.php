<?php

namespace App\Models;

use Database\Factories\StoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    /** @use HasFactory<StoreFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'partner_id',
        'name',
        'postal_code',
        'address',
        'contact_name',
        'phone',
        'carrier_id',
        'size_based',
        'free_shipping_threshold_yen',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size_based' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
