<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('cart_items')) {
            return;
        }

        DB::table('cart_items')
            ->whereNull('user_id')
            ->delete();

        $duplicates = DB::table('cart_items')
            ->select('user_id', 'product_id', DB::raw('COUNT(*) as duplicates'), DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('user_id', 'product_id')
            ->having('duplicates', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            $items = DB::table('cart_items')
                ->where('user_id', $duplicate->user_id)
                ->where('product_id', $duplicate->product_id)
                ->orderBy('id')
                ->get(['id']);

            $keeper = $items->shift();

            if ($keeper === null) {
                continue;
            }

            DB::table('cart_items')
                ->where('id', $keeper->id)
                ->update(['quantity' => $duplicate->total_quantity]);

            DB::table('cart_items')
                ->whereIn('id', $items->pluck('id'))
                ->delete();
        }

        Schema::table('cart_items', function (Blueprint $table) {
            $table->unique(['user_id', 'product_id'], 'cart_items_user_product_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('cart_items')) {
            return;
        }

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('cart_items_user_product_unique');
        });
    }
};
