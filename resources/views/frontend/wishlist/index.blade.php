@extends('layouts.frontend')

@section('title', 'Lista de Desejos - ' . config('app.name'))
@section('description', 'Sua lista de produtos favoritos')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">
                    <i class="fas fa-heart text-danger"></i>
                    Minha Lista de Desejos
                </h2>
                <span class="badge bg-primary fs-6">
                    {{ $wishlistItems->count() }} item{{ $wishlistItems->count() != 1 ? 's' : '' }}
                </span>
            </div>

            @if($wishlistItems->count() > 0)
                <div class="row">
                    @foreach($wishlistItems as $item)
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card product-card h-100">
                                <!-- Product Image -->
                                <a href="{{ route('products.show', $item->product->id) }}" class="text-decoration-none">
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center position-relative overflow-hidden" style="height: 200px;">
                                        @if($item->product->images && count(json_decode($item->product->images)) > 0)
                                            @php
                                                $images = json_decode($item->product->images);
                                            @endphp
                                            <img src="{{ asset('storage/' . $images[0]) }}" 
                                                 alt="{{ $item->product->name }}" 
                                                 class="img-fluid w-100 h-100 position-absolute top-0 start-0"
                                                 style="object-fit: cover;">
                                        @else
                                            <i class="fas fa-image text-muted fa-3x"></i>
                                        @endif
                                        
                                        <!-- Remove Button -->
                                        <button onclick="removeFromWishlist({{ $item->product->id }})" 
                                                class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2"
                                                title="Remover da lista de desejos">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </a>
                                
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title">{{ $item->product->name }}</h6>
                                    <p class="card-text text-muted small flex-grow-1">
                                        {{ Str::limit($item->product->description, 80) }}
                                    </p>
                                    
                                    <!-- Price -->
                                    <div class="mb-3">
                                        @php
                                            $effectivePrice = $item->product->promotional_price && $item->product->promotional_price < $item->product->price ? $item->product->promotional_price : $item->product->price;
                                        @endphp
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="h6 text-primary mb-0">
                                                R$ {{ format_currency($effectivePrice) }}
                                            </span>
                                            @if($item->product->promotional_price && $item->product->promotional_price < $item->product->price)
                                                <small class="text-muted text-decoration-line-through">
                                                    R$ {{ format_currency($item->product->price) }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Added Date -->
                                    <small class="text-muted mb-3">
                                        <i class="fas fa-calendar-alt"></i>
                                        Adicionado em {{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}
                                    </small>
                                </div>
                                
                                <div class="card-footer bg-white border-0">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('products.show', $item->product->id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i> Ver Detalhes
                                        </a>
                                        @if($item->product->isInStock() && $item->product->active == 1)
                                            <button onclick="addToCartFromWishlist({{ $item->product->id }})" class="btn btn-success btn-sm">
                                                <i class="fas fa-cart-plus"></i> Adicionar ao Carrinho
                                            </button>
                                        @else
                                            <button class="btn btn-secondary btn-sm" disabled>
                                                <i class="fas fa-ban"></i> Produto Esgotado
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty Wishlist -->
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-heart text-muted" style="font-size: 5rem; opacity: 0.3;"></i>
                    </div>
                    <h4 class="text-muted mb-3">Sua lista de desejos está vazia</h4>
                    <p class="text-muted mb-4">
                        Explore nossos produtos e adicione seus favoritos à lista de desejos!
                    </p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag"></i> Ver Produtos
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function removeFromWishlist(productId) {
    if (!confirm('Tem certeza que deseja remover este produto da sua lista de desejos?')) {
        return;
    }
    
    fetch(`/lista-desejos/${productId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to update the list
            window.location.reload();
        } else {
            alert(data.message || 'Erro ao remover produto');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao remover produto da lista de desejos');
    });
}

function addToCartFromWishlist(productId) {
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adicionando...';
    btn.disabled = true;
    
    fetch('{{ route("cart.add") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success !== false) {
            // Show success message
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            alert.innerHTML = `
                <i class="fas fa-check-circle"></i> Produto adicionado ao carrinho!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alert);
            
            setTimeout(() => alert.remove(), 4000);
        } else {
            alert(data.message || 'Erro ao adicionar produto ao carrinho');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao adicionar produto ao carrinho');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>
@endpush
