<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->default('demo_card');
            }

            if (! Schema::hasColumn('orders', 'payment_reference')) {
                $table->string('payment_reference')->nullable();
            }
        });

        DB::table('orders')
            ->select('id', 'payment_reference')
            ->orderBy('id')
            ->get()
            ->each(function (object $order): void {
                if ($order->payment_reference !== null) {
                    return;
                }

                DB::table('orders')
                    ->where('id', $order->id)
                    ->update([
                        'payment_reference' => sprintf('DIVA-LEGACY-%06d', $order->id),
                        'payment_method' => 'demo_card',
                    ]);
            });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'card_number')) {
                $table->dropColumn('card_number');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'card_number')) {
                $table->string('card_number')->nullable();
            }
        });
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_reference')) {
                $table->dropColumn('payment_reference');
            }

            if (Schema::hasColumn('orders', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });
    }
};
