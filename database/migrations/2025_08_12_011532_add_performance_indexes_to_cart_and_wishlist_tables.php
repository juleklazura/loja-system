<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Adicionar índices de performance para cart_items
        Schema::table('cart_items', function (Blueprint $table) {
            // Índice para consultas por usuário (mais frequente)
            $table->index('user_id', 'idx_cart_items_user_id');
            
            // Índice composto para consultas de usuário + produto
            $table->index(['user_id', 'product_id'], 'idx_cart_items_user_product');
            
            // Índice para consultas por produto
            $table->index('product_id', 'idx_cart_items_product_id');
        });

        // Adicionar índices de performance para wishlists
        Schema::table('wishlists', function (Blueprint $table) {
            // Índice para consultas por usuário (mais frequente)
            $table->index('user_id', 'idx_wishlists_user_id');
            
            // Índice composto para consultas de usuário + produto (para verificar duplicatas)
            $table->index(['user_id', 'product_id'], 'idx_wishlists_user_product');
            
            // Índice para consultas por produto
            $table->index('product_id', 'idx_wishlists_product_id');
            
            // Índice para consultas por data de criação
            $table->index('created_at', 'idx_wishlists_created_at');
        });

        // Adicionar índices de performance para products
        Schema::table('products', function (Blueprint $table) {
            // Índice para consultas por categoria
            if (!Schema::hasIndex('products', 'idx_products_category_id')) {
                $table->index('category_id', 'idx_products_category_id');
            }
            
            // Índice para consultas por status ativo
            if (!Schema::hasIndex('products', 'idx_products_is_active')) {
                $table->index('is_active', 'idx_products_is_active');
            }
            
            // Índice composto para consultas de produtos ativos por categoria
            if (!Schema::hasIndex('products', 'idx_products_active_category')) {
                $table->index(['is_active', 'category_id'], 'idx_products_active_category');
            }
            
            // Índice para busca por SKU
            if (!Schema::hasIndex('products', 'idx_products_sku')) {
                $table->index('sku', 'idx_products_sku');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover índices do cart_items
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex('idx_cart_items_user_id');
            $table->dropIndex('idx_cart_items_user_product');
            $table->dropIndex('idx_cart_items_product_id');
        });

        // Remover índices das wishlists
        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropIndex('idx_wishlists_user_id');
            $table->dropIndex('idx_wishlists_user_product');
            $table->dropIndex('idx_wishlists_product_id');
            $table->dropIndex('idx_wishlists_created_at');
        });

        // Remover índices dos products
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasIndex('products', 'idx_products_category_id')) {
                $table->dropIndex('idx_products_category_id');
            }
            if (Schema::hasIndex('products', 'idx_products_is_active')) {
                $table->dropIndex('idx_products_is_active');
            }
            if (Schema::hasIndex('products', 'idx_products_active_category')) {
                $table->dropIndex('idx_products_active_category');
            }
            if (Schema::hasIndex('products', 'idx_products_sku')) {
                $table->dropIndex('idx_products_sku');
            }
        });
    }
};
