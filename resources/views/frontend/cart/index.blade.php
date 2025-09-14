@extends('layouts.frontend')

@section('title', 'Carrinho de Compras - ' . config('app.name'))

@section('content')
<!-- Breadcrumb -->
<div class="bg-light py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Início</a></li>
                <li class="breadcrumb-item active">Carrinho de Compras</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-shopping-cart"></i> Carrinho de Compras
            </h1>
        </div>
    </div>

    @if($cartItems->count() > 0)
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            Seus Produtos ({{ $cartItems->count() }} {{ $cartItems->count() === 1 ? 'item' : 'itens' }})
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @foreach($cartItems as $item)
                            <div class="border-bottom p-4" data-item-id="{{ $item->id }}">
                                <div class="row align-items-center">
                                    <!-- Product Image -->
                                    <div class="col-md-2 col-sm-3 mb-3 mb-sm-0">
                                        <div class="bg-light rounded" style="height: 80px;">
                                            @if($item->product->images && count($item->product->images) > 0)
                                                <img src="{{ asset('storage/' . $item->product->images[0]) }}" 
                                                     alt="{{ $item->product->name }}" 
                                                     class="img-fluid h-100 w-100 rounded" style="object-fit: contain;">
                                            @else
                                                <div class="d-flex align-items-center justify-content-center h-100">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Product Info -->
                                    <div class="col-md-4 col-sm-5 mb-3 mb-sm-0">
                                        <h6 class="mb-1">
                                            <a href="{{ route('products.show', $item->product) }}" 
                                               class="text-decoration-none text-dark">
                                                {{ $item->product->name }}
                                            </a>
                                        </h6>
                                        <p class="text-muted small mb-1">
                                            SKU: {{ $item->product->sku }}
                                        </p>
                                        <p class="text-muted small mb-0">
                                            {{ $item->product->category->name }}
                                        </p>
                                        
                                        <!-- Stock Status -->
                                        @if($item->product->stock_quantity < $item->quantity)
                                            <div class="alert alert-warning py-1 px-2 mt-2 small">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                Estoque insuficiente ({{ $item->product->stock_quantity }} disponível)
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Quantity -->
                                    <div class="col-md-2 col-sm-2 mb-3 mb-sm-0">
                                        <div class="input-group input-group-sm">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                    onclick="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="text" class="form-control text-center" 
                                                   value="{{ $item->quantity }}" readonly>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                    onclick="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Price -->
                                    <div class="col-md-2 col-sm-2 mb-3 mb-sm-0">
                                        <div class="text-end">
                                            <p class="fw-bold mb-0">
                                                R$ {{ format_currency($item->product->effective_price * $item->quantity) }}
                                            </p>
                                            <small class="text-muted">
                                                R$ {{ format_currency($item->product->effective_price) }} cada
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Remove -->
                                    <div class="col-md-2 col-sm-12">
                                        <div class="text-end">
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                    onclick="removeItem({{ $item->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Continue Shopping -->
                <div class="mt-4">
                    <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Continuar Comprando
                    </a>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="sticky-top" style="top: 2rem;">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Resumo do Pedido</h5>
                    </div>
                    <div class="card-body">
                        <!-- Items Summary -->
                        <div class="mb-3">
                            @foreach($cartItems as $item)
                                <div class="d-flex justify-content-between align-items-center py-1">
                                    <span class="small">
                                        {{ Str::limit($item->product->name, 20) }} 
                                        <span class="text-muted">({{ $item->quantity }}x)</span>
                                    </span>
                                    <span class="small">
                                        R$ {{ format_currency($item->product->effective_price * $item->quantity) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                        
                        <hr>
                        
                        <!-- Totals -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Subtotal:</span>
                                <span>R$ {{ format_currency($cartTotal) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Frete:</span>
                                <span class="text-muted">A calcular</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Desconto:</span>
                                <span class="text-success">R$ 0,00</span>
                            </div>
                        </div>
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong class="text-primary h5">R$ {{ format_currency($cartTotal) }}</strong>
                        </div>

                        <!-- Coupon Code -->
                        <div class="mb-3">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Código do cupom">
                                <button class="btn btn-outline-secondary" type="button">
                                    Aplicar
                                </button>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <div class="d-grid">
                            <a href="{{ route('checkout.index') }}" class="btn btn-success btn-lg">
                                <i class="fas fa-credit-card"></i> Finalizar Compra
                            </a>
                        </div>

                        <!-- Security Info -->
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <i class="fas fa-lock"></i> 
                                Compra 100% segura e protegida
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Benefits -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title">Vantagens da nossa loja:</h6>
                        <ul class="list-unstyled small">
                            <li><i class="fas fa-truck text-primary"></i> Entrega rápida</li>
                            <li><i class="fas fa-shield-alt text-success"></i> Compra protegida</li>
                            <li><i class="fas fa-undo text-info"></i> Troca fácil</li>
                            <li><i class="fas fa-headset text-warning"></i> Suporte 24h</li>
                        </ul>
                    </div>
                </div>
                </div> <!-- Fecha o sticky-top aqui -->
            </div>
        </div>
    @else
        <!-- Empty Cart -->
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-shopping-cart text-muted" style="font-size: 5rem;"></i>
                    </div>
                    <h3 class="text-muted mb-3">Seu carrinho está vazio</h3>
                    <p class="text-muted mb-4">
                        Que tal dar uma olhada em nossos produtos?
                    </p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag"></i> Ver Produtos
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function updateQuantity(itemId, newQuantity) {
    if (newQuantity < 1) {
        removeItem(itemId);
        return;
    }
    
    fetch(`/carrinho/${itemId}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            quantity: newQuantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erro ao atualizar quantidade!');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao atualizar quantidade!');
    });
}

function removeItem(itemId) {
    if (confirm('Tem certeza que deseja remover este item do carrinho?')) {
        fetch(`/carrinho/${itemId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Erro ao remover item!');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao remover item!');
        });
    }
}
</script>

<style>
/* Sticky container para resumo + vantagens juntos */
.sticky-top {
    position: -webkit-sticky;
    position: sticky;
    align-self: flex-start;
}

/* Em telas menores, remover o sticky */
@media (max-width: 991.98px) {
    .sticky-top {
        position: static !important;
    }
}
</style>
@endpush
