<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'image_url'];

    protected static function booted(): void
    {
        static::saved(static function (): void {
            static::flushCatalogCache();
        });

        static::deleted(static function (): void {
            static::flushCatalogCache();
        });
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class);
    }

    private static function flushCatalogCache(): void
    {
        Cache::forget('catalog.categories');
        Cache::forget('home.categories');
    }
}
