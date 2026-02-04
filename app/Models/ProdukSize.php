<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdukSize extends Model
{
    use HasFactory;

  
    protected $fillable = [
        'produk_id',
        'size',
    ];


    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class);
    }
}
