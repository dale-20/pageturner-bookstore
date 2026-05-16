<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->index(['category_id', 'created_at'], 'books_category_created_idx');
            $table->index('created_at', 'books_created_at_idx');
            $table->index('stock_quantity', 'books_stock_quantity_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'created_at'], 'orders_user_status_created_idx');
            $table->index(['status', 'created_at'], 'orders_status_created_idx');
            $table->index('created_at', 'orders_created_at_idx');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index(['order_id', 'book_id'], 'order_items_order_book_idx');
            $table->index(['book_id', 'order_id'], 'order_items_book_order_idx');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['book_id', 'created_at'], 'reviews_book_created_idx');
            $table->index(['user_id', 'created_at'], 'reviews_user_created_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'created_at'], 'users_role_created_idx');
        });

        Schema::table('import_logs', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'created_at'], 'import_logs_user_status_created_idx');
            $table->index(['status', 'created_at'], 'import_logs_status_created_idx');
        });

        Schema::table('export_logs', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'created_at'], 'export_logs_user_status_created_idx');
            $table->index(['status', 'created_at'], 'export_logs_status_created_idx');
        });

        $this->addCheckConstraint('books', 'books_price_non_negative_chk', 'price >= 0');
        $this->addCheckConstraint('books', 'books_stock_non_negative_chk', 'stock_quantity >= 0');
        $this->addCheckConstraint('orders', 'orders_total_non_negative_chk', 'total_amount >= 0');
        $this->addCheckConstraint('order_items', 'order_items_quantity_positive_chk', 'quantity > 0');
        $this->addCheckConstraint('order_items', 'order_items_unit_price_non_negative_chk', 'unit_price >= 0');
        $this->addCheckConstraint('reviews', 'reviews_rating_range_chk', 'rating BETWEEN 1 AND 5');
    }

    public function down(): void
    {
        $this->dropCheckConstraint('reviews', 'reviews_rating_range_chk');
        $this->dropCheckConstraint('order_items', 'order_items_unit_price_non_negative_chk');
        $this->dropCheckConstraint('order_items', 'order_items_quantity_positive_chk');
        $this->dropCheckConstraint('orders', 'orders_total_non_negative_chk');
        $this->dropCheckConstraint('books', 'books_stock_non_negative_chk');
        $this->dropCheckConstraint('books', 'books_price_non_negative_chk');

        Schema::table('export_logs', function (Blueprint $table) {
            $table->dropIndex('export_logs_status_created_idx');
            $table->dropIndex('export_logs_user_status_created_idx');
        });

        Schema::table('import_logs', function (Blueprint $table) {
            $table->dropIndex('import_logs_status_created_idx');
            $table->dropIndex('import_logs_user_status_created_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_created_idx');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('reviews_user_created_idx');
            $table->dropIndex('reviews_book_created_idx');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_book_order_idx');
            $table->dropIndex('order_items_order_book_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_created_at_idx');
            $table->dropIndex('orders_status_created_idx');
            $table->dropIndex('orders_user_status_created_idx');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex('books_stock_quantity_idx');
            $table->dropIndex('books_created_at_idx');
            $table->dropIndex('books_category_created_idx');
        });
    }

    private function addCheckConstraint(string $table, string $name, string $expression): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$name} CHECK ({$expression}) NOT VALID");
            return;
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$name} CHECK ({$expression})");
        }
    }

    private function dropCheckConstraint(string $table, string $name): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$name}");
            return;
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE {$table} DROP CHECK {$name}");
        }
    }
};
