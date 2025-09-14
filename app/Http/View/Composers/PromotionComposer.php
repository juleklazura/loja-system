<?php

namespace App\Http\View\Composers;

use App\Models\Product;
use Illuminate\View\View;

class PromotionComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        // Buscar TODOS os produtos que têm preço promocional ativo
        $productsWithDiscount = Product::whereNotNull('promotional_price')
            ->where('promotional_price', '<', \Illuminate\Support\Facades\DB::raw('price'))
            ->where('active', true)
            ->get();

        // Criar array de "promoções" baseado nos produtos com desconto
        $activePromotions = $productsWithDiscount->map(function ($product) {
            $discountPercentage = round((($product->price - $product->promotional_price) / $product->price) * 100);
            $discountValue = $product->price - $product->promotional_price;
            
            return (object) [
                'name' => $product->name,
                'description' => "Produto com desconto especial",
                'type' => 'percentage',
                'value' => $discountPercentage,
                'discount_amount' => $discountValue,
                'original_price' => $product->price,
                'promotional_price' => $product->promotional_price,
                'product' => $product
            ];
        });

        $view->with('activePromotions', $activePromotions);
    }
}
