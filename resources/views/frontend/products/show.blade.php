@extends('layouts.frontend')

@section('title', $product->name . ' - ' . config('app.name'))
@section('description', Str::limit($product->description, 160))

@section('content')
<!-- Breadcrumb -->
<div class="bg-light py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Início</a></li>
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Produtos</a></li>
                <li class="breadcrumb-item"><a href="{{ route('products.category', $product->category) }}">{{ $product->category->name }}</a></li>
                <li class="breadcrumb-item active">{{ $product->name }}</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Product Details -->
<div class="container py-5">
    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <!-- Main Image -->
                    <div class="text-center bg-light" style="height: 400px;">
                        @if($product->images && count($product->images) > 0)
                            <img id="mainImage" src="{{ asset('storage/' . $product->images[0]) }}" 
                                 alt="{{ $product->name }}" 
                                 class="img-fluid w-100 h-100" 
                                 style="object-fit: contain; cursor: zoom-in;"
                                 onclick="openImageModal(this.src)">
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100">
                                <i class="fas fa-image text-muted fa-5x"></i>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Thumbnail Images -->
                    @if($product->images && count($product->images) > 1)
                        <div class="p-3">
                            <div class="row g-2">
                                @foreach($product->images as $index => $image)
                                    <div class="col-3">
                                        <img src="{{ asset('storage/' . $image) }}" 
                                             alt="{{ $product->name }}" 
                                             class="img-thumbnail cursor-pointer thumbnail-image {{ $index === 0 ? 'active' : '' }}" 
                                             style="height: 80px; width: 100%; object-fit: cover; cursor: pointer; transition: opacity 0.3s ease;"
                                             onclick="changeMainImage('{{ asset('storage/' . $image) }}', this)"
                                             onmouseover="this.style.opacity='0.8'"
                                             onmouseout="this.style.opacity='1'">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-lg-6">
            <div class="mb-3">
                <h1 class="h2">{{ $product->name }}</h1>
                <div class="text-muted mb-2">
                    <small>SKU: {{ $product->sku }}</small>
                </div>
                <div class="mb-3">
                    <a href="{{ route('products.category', $product->category) }}" 
                       class="badge bg-secondary text-decoration-none">
                        <i class="fas fa-tag"></i> {{ $product->category->name }}
                    </a>
                </div>
            </div>

            @if($product->isInStock())
                <!-- Price -->
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <span class="h2 text-primary mb-0">
                            R$ {{ format_currency($product->effective_price) }}
                        </span>
                        @if($product->promotional_price && $product->promotional_price < $product->price)
                            <span class="h5 text-muted text-decoration-line-through mb-0">
                                R$ {{ format_currency($product->price) }}
                            </span>
                            <span class="badge bg-danger fs-6">
                                {{ round((($product->price - $product->promotional_price) / $product->price) * 100) }}% OFF
                            </span>
                        @endif
                    </div>
                    
                    <!-- Active Promotions -->
                    @if($product->promotions->count() > 0)
                        <div class="mt-2">
                            @foreach($product->promotions as $promotion)
                                <div class="alert alert-warning py-2 px-3 small">
                                    <i class="fas fa-fire text-danger"></i>
                                    <strong>{{ $promotion->name }}:</strong> {{ $promotion->description }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Stock Status -->
                <div class="mb-4">
                    @if($product->stock_quantity > 10)
                        <div class="alert alert-success py-2 px-3">
                            <i class="fas fa-check-circle"></i>
                            <strong>Em estoque</strong> - Pronto para envio
                        </div>
                    @else
                        <div class="alert alert-warning py-2 px-3">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Últimas {{ $product->stock_quantity }} unidades</strong> - Garante já o seu!
                        </div>
                    @endif
                </div>
            @else
                <!-- Out of Stock -->
                <div class="mb-4">
                    <div class="alert alert-danger text-center py-4">
                        <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                        <h3 class="text-danger mb-0">PRODUTO ESGOTADO</h3>
                        <p class="text-muted mt-2 mb-0">Este produto não está disponível no momento</p>
                    </div>
                </div>
            @endif

            <!-- Add to Cart Form -->
            @auth
                @if($product->isInStock())
                    <form id="addToCartForm" class="mb-4">
                        @csrf
                        <div class="row align-items-end">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Quantidade</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="changeQuantity(-1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" id="quantity" name="quantity" class="form-control text-center" 
                                           value="1" min="1" max="{{ $product->stock_quantity }}">
                                    <button type="button" class="btn btn-outline-secondary" onclick="changeQuantity(1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-8 mb-3">
                                <button type="submit" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-cart-plus"></i> Adicionar ao Carrinho
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="mb-4">
                        <button class="btn btn-secondary btn-lg w-100" disabled>
                            <i class="fas fa-times"></i> Produto Indisponível
                        </button>
                    </div>
                @endif
            @else
                <div class="mb-4">
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-sign-in-alt"></i> Faça Login para Comprar
                    </a>
                </div>
            @endauth

            <!-- Quick Actions -->
            <div class="d-flex gap-2 mb-4">
                @auth
                    <button id="wishlist-btn" 
                            class="btn btn-outline-danger" 
                            onclick="toggleWishlist({{ $product->id }})">
                        <i id="wishlist-icon" class="fas fa-heart"></i> 
                        <span id="wishlist-text">Favoritar</span>
                    </button>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-danger">
                        <i class="fas fa-heart"></i> Favoritar
                    </a>
                @endauth
                <button class="btn btn-outline-info">
                    <i class="fas fa-share-alt"></i> Compartilhar
                </button>
                <button class="btn btn-outline-secondary">
                    <i class="fas fa-question-circle"></i> Dúvidas
                </button>
            </div>

            <!-- Product Features -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Informações do Produto</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 small">
                        <div class="col-6">
                            <strong>Código:</strong><br>
                            {{ $product->sku }}
                        </div>
                        <div class="col-6">
                            <strong>Categoria:</strong><br>
                            {{ $product->category->name }}
                        </div>
                        <div class="col-6">
                            <strong>Status:</strong><br>
                            @if($product->isInStock())
                                <span class="text-success">Disponível</span>
                            @else
                                <span class="text-danger">Indisponível</span>
                            @endif
                        </div>
                        <div class="col-6">
                            <strong>Entrega:</strong><br>
                            Até 3 dias úteis
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Description & Details -->
    <div class="row mt-5">
        <div class="col-12">
            <nav>
                <div class="nav nav-tabs" id="productTabs" role="tablist">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" 
                            data-bs-target="#description" type="button" role="tab">
                        Descrição
                    </button>
                    <button class="nav-link" id="specs-tab" data-bs-toggle="tab" 
                            data-bs-target="#specs" type="button" role="tab">
                        Especificações
                    </button>
                    <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" 
                            data-bs-target="#shipping" type="button" role="tab">
                        Entrega
                    </button>
                </div>
            </nav>
            <div class="tab-content p-4 border border-top-0" id="productTabsContent">
                <div class="tab-pane fade show active" id="description" role="tabpanel">
                    <h5>Descrição do Produto</h5>
                    <p>{{ $product->description ?: 'Descrição não disponível.' }}</p>
                </div>
                <div class="tab-pane fade" id="specs" role="tabpanel">
                    <h5>Especificações Técnicas</h5>
                    <table class="table table-striped">
                        <tr>
                            <td><strong>SKU</strong></td>
                            <td>{{ $product->sku }}</td>
                        </tr>
                        <tr>
                            <td><strong>Categoria</strong></td>
                            <td>{{ $product->category->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status</strong></td>
                            <td>
                                @if($product->isInStock())
                                    <span class="badge bg-success">Disponível</span>
                                @else
                                    <span class="badge bg-danger">Indisponível</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="tab-pane fade" id="shipping" role="tabpanel">
                    <h5>Informações de Entrega</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-truck text-primary"></i> Entrega rápida em até 3 dias úteis</li>
                                <li><i class="fas fa-shield-alt text-success"></i> Produto protegido durante o transporte</li>
                                <li><i class="fas fa-undo text-info"></i> Devolução gratuita em 7 dias</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>Calcular Frete</h6>
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="00000-000">
                                        <button class="btn btn-primary" type="button">Calcular</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="mb-4">Produtos Relacionados</h3>
                <div class="row">
                    @foreach($relatedProducts as $related)
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card product-card h-100">
                                <a href="{{ route('products.show', $related) }}" class="text-decoration-none">
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center position-relative overflow-hidden" style="height: 150px;">
                                        @if($related->images && count($related->images) > 0)
                                            <img src="{{ asset('storage/' . $related->images[0]) }}" 
                                                 alt="{{ $related->name }}" 
                                                 class="img-fluid w-100 h-100 position-absolute top-0 start-0"
                                                 style="object-fit: cover; transition: transform 0.3s ease;"
                                                 onmouseover="this.style.transform='scale(1.05)'"
                                                 onmouseout="this.style.transform='scale(1)'">
                                        @else
                                            <i class="fas fa-image text-muted fa-2x"></i>
                                        @endif
                                    </div>
                                </a>
                                <div class="card-body">
                                    <h6 class="card-title">{{ Str::limit($related->name, 50) }}</h6>
                                    @if($related->isInStock())
                                        <p class="text-primary mb-0">
                                            R$ {{ format_currency($related->effective_price) }}
                                        </p>
                                    @else
                                        <p class="text-danger mb-0">
                                            <i class="fas fa-times-circle"></i> <strong>ESGOTADO</strong>
                                        </p>
                                    @endif
                                </div>
                                <div class="card-footer bg-white border-0">
                                    <a href="{{ route('products.show', $related) }}" class="btn btn-outline-primary btn-sm w-100">
                                        Ver Produto
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.thumbnail-image {
    border: 2px solid transparent;
    transition: border-color 0.3s;
}
.thumbnail-image.active {
    border-color: #007bff;
}
.cursor-pointer {
    cursor: pointer;
}
</style>
@endpush

@push('scripts')
<script>
function changeMainImage(src, element) {
    document.getElementById('mainImage').src = src;
    
    // Remove active class from all thumbnails
    document.querySelectorAll('.thumbnail-image').forEach(img => {
        img.classList.remove('active');
    });
    
    // Add active class to clicked thumbnail
    element.classList.add('active');
}

function changeQuantity(delta) {
    const quantityInput = document.getElementById('quantity');
    const currentValue = parseInt(quantityInput.value);
    const newValue = currentValue + delta;
    const max = parseInt(quantityInput.getAttribute('max'));
    
    if (newValue >= 1 && newValue <= max) {
        quantityInput.value = newValue;
    }
}

@auth
document.getElementById('addToCartForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const quantity = document.getElementById('quantity').value;
    
    fetch('{{ route("cart.add") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            product_id: {{ $product->id }},
            quantity: parseInt(quantity)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success !== false) {
            // Update cart counter
            if (data.cart_count !== undefined) {
                updateCartCounter(data.cart_count);
            }
            
            // Show success message
            showCartMessage(`${quantity} item(s) adicionado(s) ao carrinho!`);
        } else {
            showCartMessage(data.message || 'Erro ao adicionar produto ao carrinho!', false);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showCartMessage('Erro ao adicionar produto ao carrinho!', false);
    });
});
@endauth

// Wishlist functionality
@auth
function toggleWishlist(productId) {
    const btn = document.getElementById('wishlist-btn');
    const icon = document.getElementById('wishlist-icon');
    const text = document.getElementById('wishlist-text');
    
    const originalText = text.textContent;
    const originalClasses = btn.className;
    
    // Show loading state
    btn.disabled = true;
    text.textContent = 'Carregando...';
    icon.className = 'fas fa-spinner fa-spin';
    
    fetch('{{ route("wishlist.toggle") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.action === 'added') {
                btn.className = 'btn btn-danger';
                icon.className = 'fas fa-heart';
                text.textContent = 'Favoritado';
            } else {
                btn.className = 'btn btn-outline-danger';
                icon.className = 'far fa-heart';
                text.textContent = 'Favoritar';
            }
            
            // Show success message
            showCartMessage(data.message, true);
        } else {
            showCartMessage(data.message || 'Erro ao atualizar lista de desejos', false);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showCartMessage('Erro ao atualizar lista de desejos', false);
    })
    .finally(() => {
        btn.disabled = false;
    });
}

// Check initial wishlist status
document.addEventListener('DOMContentLoaded', function() {
    fetch('{{ route("wishlist.index") }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.wishlistProductIds.includes({{ $product->id }})) {
            const btn = document.getElementById('wishlist-btn');
            const icon = document.getElementById('wishlist-icon');
            const text = document.getElementById('wishlist-text');
            
            btn.className = 'btn btn-danger';
            icon.className = 'fas fa-heart';
            text.textContent = 'Favoritado';
        }
    })
    .catch(error => {
        console.error('Erro ao verificar wishlist:', error);
    });
});
@endauth

// Modal de zoom para imagens
function openImageModal(imageSrc) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $product->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="${imageSrc}" alt="{{ $product->name }}" class="img-fluid" style="max-height: 70vh;">
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    modal.addEventListener('hidden.bs.modal', () => {
        document.body.removeChild(modal);
    });
}
</script>
@endpush
