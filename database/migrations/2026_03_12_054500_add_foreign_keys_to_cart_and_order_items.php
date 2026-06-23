<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('cart_items')) {
            if (! $this->foreignKeyExists('cart_items', 'cart_items_cart_id_foreign')) {
                Schema::table('cart_items', function (Blueprint $table): void {
                    $table->foreign('cart_id')
                        ->references('id')
                        ->on('carts')
                        ->cascadeOnDelete();
                });
            }

            if (! $this->foreignKeyExists('cart_items', 'cart_items_product_id_foreign')) {
                Schema::table('cart_items', function (Blueprint $table): void {
                    $table->foreign('product_id')
                        ->references('id')
                        ->on('products')
                        ->restrictOnDelete();
                });
            }
        }

        if (Schema::hasTable('order_items')) {
            if (! $this->foreignKeyExists('order_items', 'order_items_order_id_foreign')) {
                Schema::table('order_items', function (Blueprint $table): void {
                    $table->foreign('order_id')
                        ->references('id')
                        ->on('orders')
                        ->cascadeOnDelete();
                });
            }

            if (! $this->foreignKeyExists('order_items', 'order_items_product_id_foreign')) {
                Schema::table('order_items', function (Blueprint $table): void {
                    $table->foreign('product_id')
                        ->references('id')
                        ->on('products')
                        ->nullOnDelete();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('cart_items')) {
            if ($this->foreignKeyExists('cart_items', 'cart_items_cart_id_foreign')) {
                Schema::table('cart_items', function (Blueprint $table): void {
                    $table->dropForeign('cart_items_cart_id_foreign');
                });
            }

            if ($this->foreignKeyExists('cart_items', 'cart_items_product_id_foreign')) {
                Schema::table('cart_items', function (Blueprint $table): void {
                    $table->dropForeign('cart_items_product_id_foreign');
                });
            }
        }

        if (Schema::hasTable('order_items')) {
            if ($this->foreignKeyExists('order_items', 'order_items_order_id_foreign')) {
                Schema::table('order_items', function (Blueprint $table): void {
                    $table->dropForeign('order_items_order_id_foreign');
                });
            }

            if ($this->foreignKeyExists('order_items', 'order_items_product_id_foreign')) {
                Schema::table('order_items', function (Blueprint $table): void {
                    $table->dropForeign('order_items_product_id_foreign');
                });
            }
        }
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            return DB::table('information_schema.table_constraints')
                ->where('table_schema', 'public')
                ->where('table_name', $table)
                ->where('constraint_name', $constraint)
                ->where('constraint_type', 'FOREIGN KEY')
                ->exists();
        }

        if ($driver === 'mysql') {
            return DB::table('information_schema.table_constraints')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', $table)
                ->where('constraint_name', $constraint)
                ->where('constraint_type', 'FOREIGN KEY')
                ->exists();
        }

        return false;
    }
};
