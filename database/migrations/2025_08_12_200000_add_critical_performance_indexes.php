<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations for dashboard performance optimization.
     */
    public function up(): void
    {
        // Add indexes for dashboard statistics and performance
        
        // Orders table optimizations
        if (!$this->indexExists('orders', 'idx_orders_created_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index('created_at', 'idx_orders_created_at');
                $table->index(['user_id', 'created_at'], 'idx_orders_user_created');
                $table->index(['status', 'created_at'], 'idx_orders_status_created');
            });
        }

        // Products table optimizations  
        if (!$this->indexExists('products', 'idx_products_stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index('stock_quantity', 'idx_products_stock');
                $table->index(['category_id', 'stock_quantity'], 'idx_products_category_stock');
                $table->index(['created_at', 'id'], 'idx_products_created');
            });
        }

        // Categories table optimizations
        if (!$this->indexExists('categories', 'idx_categories_name')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->index('name', 'idx_categories_name');
            });
        }

        // Users table optimizations
        if (!$this->indexExists('users', 'idx_users_created')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('created_at', 'idx_users_created');
                $table->index(['user_type', 'created_at'], 'idx_users_type_created');
            });
        }

        // Composite index for revenue calculations
        if (!$this->indexExists('orders', 'idx_orders_date_amount')) {
            DB::statement('CREATE INDEX idx_orders_date_amount ON orders (DATE(created_at), total_amount)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_created_at');
            $table->dropIndex('idx_orders_user_created');
            $table->dropIndex('idx_orders_status_created');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_stock');
            $table->dropIndex('idx_products_category_stock');
            $table->dropIndex('idx_products_created');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_created');
            $table->dropIndex('idx_users_type_created');
        });

        DB::statement('DROP INDEX IF EXISTS idx_orders_date_amount ON orders');
    }

    /**
     * Check if an index exists on a table.
     */
    private function indexExists(string $table, string $index): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]);
            return count($indexes) > 0;
        } catch (\Exception $e) {
            // If table doesn't exist or error occurs, return false
            return false;
        }
    }
};
