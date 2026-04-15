<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Support\CartCounter;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
    ];

    protected static function booted(): void
    {
        static::saved(static function (CartItem $cartItem): void {
            CartCounter::forgetForUserId($cartItem->user_id);
        });

        static::deleted(static function (CartItem $cartItem): void {
            CartCounter::forgetForUserId($cartItem->user_id);
        });
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
