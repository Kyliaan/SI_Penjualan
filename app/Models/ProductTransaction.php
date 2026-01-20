<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'booking_trx_id',
        'city',
        'post_code',
        'address',
        'quantity',
        'sub_total_amount',
        'grand_total_amount',
        'discount_amount',
        'is_paid',
        'produk_id',
        'produk_size',
        'promo_code_id',
        'proof',
    ];

    public static function generateUniqueTrxId(): string
    {
        $prefix = 'TJH';
        do {
            $randomString = $prefix . mt_rand(min: 10001, max: 99999);
        } while (self::where(column: 'booking_trx_id', operator: $randomString)->exists());
        return $randomString;
        }
    public function produk(): BelongsTo
    {
        return $this->belongsTo(related: Produk::class, foreignKey: 'produk_id');
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(related: PromoCode::class, foreignKey: 'promo_code_id');
    }

  }
