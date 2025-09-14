@extends('layouts.frontend')

@section('title', 'Página Inicial - ' . config('app.name'))
@section('description', 'Bem-vindo à nossa loja online. Encontre os melhores produtos com os melhores preços.')

@push('styles')
<style>
/* Hero Section Styles */
.hero-section {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    padding: 100px 0;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20"><defs><radialGradient id="a" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="rgba(255,255,255,.1)"/><stop offset="100%" stop-color="rgba(255,255,255,0)"/></radialGradient></defs><rect width="100" height="20" fill="url(%23a)"/></svg>') repeat;
    opacity: 0.1;
}

.hero-section .container {
    position: relative;
    z-index: 2;
}

/* Product Cards Enhanced */
.product-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    overflow: hidden;
    position: relative;
}

.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.product-card .card-img-top {
    position: relative;
    overflow: hidden;
    background: linear-gradient(45deg, #f8f9fa 25%, transparent 25%, transparent 75%, #f8f9fa 75%, #f8f9fa), 
                linear-gradient(45deg, #f8f9fa 25%, transparent 25%, transparent 75%, #f8f9fa 75%, #f8f9fa);
    background-size: 20px 20px;
    background-position: 0 0, 10px 10px;
}

.product-card .card-img-top img {
    transition: all 0.4s ease;
    filter: brightness(1) saturate(1);
}

.product-card:hover .card-img-top img {
    transform: scale(1.05);
    filter: brightness(1.1) saturate(1.2);
}

/* Wishlist Button Styles */
.wishlist-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 10;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #fff;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
    font-size: 16px;
}

.wishlist-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.wishlist-btn.active {
    background: #dc3545;
    color: white;
    border-color: #dc3545;
}

.wishlist-btn.loading {
    pointer-events: none;
    opacity: 0.7;
}

/* Category Cards */
.category-card {
    transition: all 0.3s ease;
    border: none;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.category-card .card-body {
    padding: 2rem;
}

/* Price Display */
.price-display {
    font-family: 'Segoe UI', system-ui, sans-serif;
    font-weight: 600;
}

.original-price {
    position: relative;
}

.original-price::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 2px;
    background: #dc3545;
    transform: rotate(-5deg);
}

/* Loading Animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-spinner {
    animation: spin 1s linear infinite;
}

/* Alert Messages */
.alert-custom {
    border-radius: 10px;
    border: none;
    font-weight: 500;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-section {
        padding: 60px 0;
    }
    
    .hero-section .display-4 {
        font-size: 2rem;
    }
    
    .product-card:hover {
        transform: none;
    }
    
    .wishlist-btn {
        width: 35px;
        height: 35px;
        font-size: 14px;
    }
}

/* Smooth Animations */
* {
    scroll-behavior: smooth;
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Custom Buttons */
.btn-custom {
    border-radius: 25px;
    padding: 0.5rem 1.5rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-custom::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn-custom:hover::before {
    left: 100%;
}
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Bem-vindo à nossa loja!</h1>
                <p class="lead mb-4">
                    Descubra produtos incríveis com os melhores preços do mercado. 
                    Qualidade garantida e entrega rápida para todo o Brasil.
                </p>
                <div class="d-flex gap-3">
                    <a href="{{ route('products.index') }}" class="btn btn-light btn-lg">
                        <i class="fas fa-shopping-bag"></i> Ver Produtos
                    </a>
                    @guest
                        <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-user-plus"></i> Cadastre-se
                        </a>
                    @endguest
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <i class="fas fa-store display-1 text-white opacity-75"></i>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Explore nossas Categorias</h2>
            <p class="text-muted">Encontre exatamente o que você procura</p>
        </div>
        
        <div class="row">
            @foreach($categories as $category)
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <a href="{{ route('products.category', $category) }}" class="text-decoration-none">
                        <div class="card category-card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    @switch($category->name)
                                        @case('Eletrônicos')
                                            <i class="fas fa-laptop text-primary" style="font-size: 3rem;"></i>
                                            @break
                                        @case('Roupas')
                                            <i class="fas fa-tshirt text-success" style="font-size: 3rem;"></i>
                                            @break
                                        @case('Casa e Decoração')
                                            <i class="fas fa-home text-warning" style="font-size: 3rem;"></i>
                                            @break
                                        @case('Livros')
                                            <i class="fas fa-book text-info" style="font-size: 3rem;"></i>
                                            @break
                                        @default
                                            <i class="fas fa-tag text-secondary" style="font-size: 3rem;"></i>
                                    @endswitch
                                </div>
                                <h5 class="card-title text-dark">{{ $category->name }}</h5>
                                <p class="card-text text-muted small">
                                    {{ $category->active_products_count }} produto{{ $category->active_products_count != 1 ? 's' : '' }}
                                </p>
                                <span class="btn btn-outline-primary btn-sm">Ver Produtos</span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Produtos em Destaque</h2>
            <p class="text-muted">Os mais procurados pelos nossos clientes</p>
        </div>
        
        <div class="row">
            @foreach($featuredProducts as $product)
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card product-card h-100">
                        <!-- Product Image -->
                        <a href="{{ route('products.show', $product) }}" class="text-decoration-none">
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center position-relative overflow-hidden" style="height: 200px;">
                                @if($product->images && count($product->images) > 0)
                                    <img src="{{ asset('storage/' . $product->images[0]) }}" 
                                         alt="{{ $product->name }}" 
                                         class="img-fluid w-100 h-100 position-absolute top-0 start-0"
                                         style="object-fit: cover; transition: transform 0.3s ease;"
                                         onmouseover="this.style.transform='scale(1.05)'"
                                         onmouseout="this.style.transform='scale(1)'">
                                @else
                                    <i class="fas fa-image text-muted fa-3x"></i>
                                @endif
                                
                                @auth
                                    <!-- Wishlist Button -->
                                    <button id="wishlist-btn-{{ $product->id }}" 
                                            onclick="toggleWishlist({{ $product->id }})" 
                                            class="wishlist-btn btn btn-outline-danger btn-sm"
                                            title="Adicionar à lista de desejos">
                                        <i class="far fa-heart"></i>
                                    </button>
                                @endauth
                            </div>
                        </a>
                        
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title">{{ $product->name }}</h6>
                            <p class="card-text text-muted small flex-grow-1">
                                {{ safe_limit($product->description, 80) }}
                            </p>
                            
                            @if($product->isInStock())
                                <!-- Price -->
                                <div class="mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="h5 text-primary mb-0">
                                            R$ {{ format_currency($product->effective_price) }}
                                        </span>
                                        @if($product->promotional_price && $product->promotional_price < $product->price)
                                            <small class="text-muted text-decoration-line-through">
                                                R$ {{ format_currency($product->price) }}
                                            </small>
                                            <span class="badge bg-danger">
                                                {{ round((($product->price - $product->promotional_price) / $product->price) * 100) }}% OFF
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Stock Info -->
                                @if($product->stock_quantity <= 5)
                                    <div class="alert alert-warning py-1 px-2 small">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Últimas {{ $product->stock_quantity }} unidades!
                                    </div>
                                @endif
                            @else
                                <!-- Out of Stock -->
                                <div class="mb-3">
                                    <div class="alert alert-danger text-center py-2">
                                        <i class="fas fa-times-circle"></i>
                                        <strong>ESGOTADO</strong>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="card-footer bg-white border-0">
                            <div class="d-grid gap-2">
                                @if($product->isInStock())
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i> Ver Detalhes
                                    </a>
                                    @auth
                                        <button onclick="addToCart({{ $product->id }})" class="btn btn-success btn-sm">
                                            <i class="fas fa-cart-plus"></i> Adicionar ao Carrinho
                                        </button>
                                    @else
                                        <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-sign-in-alt"></i> Faça Login para Comprar
                                        </a>
                                    @endauth
                                @else
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-eye"></i> Ver Detalhes
                                    </a>
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
        
        <div class="text-center mt-4">
            <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-th-large"></i> Ver Todos os Produtos
            </a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-shipping-fast fa-2x"></i>
                    </div>
                    <h5>Entrega Rápida</h5>
                    <p class="text-muted">Receba seus produtos em até 2 dias úteis</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="text-center">
                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-shield-alt fa-2x"></i>
                    </div>
                    <h5>Compra Segura</h5>
                    <p class="text-muted">Seus dados protegidos com certificado SSL</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="text-center">
                    <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-undo fa-2x"></i>
                    </div>
                    <h5>Fácil Devolução</h5>
                    <p class="text-muted">7 dias para trocar ou devolver</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="text-center">
                    <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-headset fa-2x"></i>
                    </div>
                    <h5>Suporte 24/7</h5>
                    <p class="text-muted">Atendimento sempre disponível</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
@auth
<script>
// Utility functions
function showMessage(message, isSuccess = true) {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.alert-floating');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new message
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${isSuccess ? 'success' : 'danger'} alert-dismissible fade show alert-floating`;
    alertDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        if (alertDiv && alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 4000);
}

function showCartMessage(message, isSuccess = true) {
    showMessage(`<i class="fas fa-shopping-cart"></i> ${message}`, isSuccess);
}

function showWishlistMessage(message, isSuccess = true) {
    showMessage(`<i class="fas fa-heart"></i> ${message}`, isSuccess);
}

function updateCartCounter(count) {
    const cartCounters = document.querySelectorAll('[data-cart-count]');
    cartCounters.forEach(counter => {
        counter.textContent = count || 0;
        counter.style.display = count > 0 ? 'inline' : 'none';
    });
}

function updateWishlistCounter(count) {
    const wishlistCounters = document.querySelectorAll('[data-wishlist-count]');
    wishlistCounters.forEach(counter => {
        counter.textContent = count || 0;
        counter.style.display = count > 0 ? 'inline' : 'none';
    });
}

// Cart functionality
function addToCart(productId) {
    if (!productId || isNaN(parseInt(productId))) {
        showCartMessage('ID do produto inválido!', false);
        return;
    }

    const btn = event.target;
    const originalText = btn.innerHTML;
    
    // Show loading state
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adicionando...';
    btn.disabled = true;

    fetch('{{ route("cart.add") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            product_id: parseInt(productId),
            quantity: 1
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success !== false) {
            // Update cart counter
            if (data.cart_count !== undefined) {
                updateCartCounter(data.cart_count);
            }
            
            // Show success message
            showCartMessage('Produto adicionado ao carrinho!');
        } else {
            showCartMessage(data.message || 'Erro ao adicionar produto ao carrinho!', false);
        }
    })
    .catch(error => {
        console.error('Erro ao adicionar ao carrinho:', error);
        showCartMessage('Erro ao adicionar produto ao carrinho!', false);
    })
    .finally(() => {
        // Restore button state
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Wishlist functionality
function toggleWishlist(productId) {
    if (!productId || isNaN(parseInt(productId))) {
        showWishlistMessage('ID do produto inválido!', false);
        return;
    }

    const btn = document.getElementById(`wishlist-btn-${productId}`);
    if (!btn) return;
    
    const isCurrentlyInWishlist = btn.classList.contains('btn-danger');
    const icon = btn.querySelector('i');
    const originalIcon = icon.className;
    
    // Show loading state
    btn.classList.add('loading');
    icon.className = 'fas fa-spinner fa-spin';
    btn.disabled = true;

    const endpoint = '{{ route("wishlist.toggle") }}';
    const method = 'POST';
    const body = JSON.stringify({ product_id: parseInt(productId) });

    fetch(endpoint, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: body
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update button appearance
            if (isCurrentlyInWishlist) {
                // Removing from wishlist
                btn.classList.remove('btn-danger', 'active');
                btn.classList.add('btn-outline-danger');
                icon.className = 'far fa-heart';
                btn.title = 'Adicionar à lista de desejos';
                showWishlistMessage('Produto removido da lista de desejos!');
            } else {
                // Adding to wishlist
                btn.classList.remove('btn-outline-danger');
                btn.classList.add('btn-danger', 'active');
                icon.className = 'fas fa-heart';
                btn.title = 'Remover da lista de desejos';
                showWishlistMessage('Produto adicionado à lista de desejos!');
            }
            
            // Update wishlist counter
            if (data.count !== undefined) {
                updateWishlistCounter(data.count);
            }
            
            // Update localStorage for instant feedback
            const userId = {{ auth()->id() }};
            const storageKey = `wishlist_${userId}_${productId}`;
            localStorage.setItem(storageKey, isCurrentlyInWishlist ? 'false' : 'true');
            
        } else {
            showWishlistMessage(data.message || 'Erro ao atualizar lista de desejos!', false);
        }
    })
    .catch(error => {
        console.error('Erro na wishlist:', error);
        showWishlistMessage('Erro ao atualizar lista de desejos!', false);
    })
    .finally(() => {
        // Remove loading state
        btn.classList.remove('loading');
        btn.disabled = false;
        
        // If there was an error, restore original icon
        if (icon.className.includes('fa-spin')) {
            icon.className = originalIcon;
        }
    });
}

// Initialize wishlist buttons on page load
document.addEventListener('DOMContentLoaded', function() {
    // Use localStorage for instant feedback, then sync with server
    const userId = {{ auth()->id() }};
    const featuredProductIds = @json($featuredProducts->pluck('id')->toArray());
    
    // Apply localStorage state immediately
    featuredProductIds.forEach(productId => {
        const storageKey = `wishlist_${userId}_${productId}`;
        const isInWishlist = localStorage.getItem(storageKey) === 'true';
        
        if (isInWishlist) {
            const btn = document.getElementById(`wishlist-btn-${productId}`);
            if (btn) {
                btn.classList.remove('btn-outline-danger');
                btn.classList.add('btn-danger', 'active');
                btn.querySelector('i').className = 'fas fa-heart';
                btn.title = 'Remover da lista de desejos';
            }
        }
    });

    // Sync with server in background
    fetch('{{ route("wishlist.index") }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.wishlistProductIds) {
            // Update buttons based on server data
            featuredProductIds.forEach(productId => {
                const btn = document.getElementById(`wishlist-btn-${productId}`);
                const isInWishlist = data.wishlistProductIds.includes(productId);
                const storageKey = `wishlist_${userId}_${productId}`;
                
                // Update localStorage
                localStorage.setItem(storageKey, isInWishlist ? 'true' : 'false');
                
                if (btn) {
                    if (isInWishlist) {
                        btn.classList.remove('btn-outline-danger');
                        btn.classList.add('btn-danger', 'active');
                        btn.querySelector('i').className = 'fas fa-heart';
                        btn.title = 'Remover da lista de desejos';
                    } else {
                        btn.classList.remove('btn-danger', 'active');
                        btn.classList.add('btn-outline-danger');
                        btn.querySelector('i').className = 'far fa-heart';
                        btn.title = 'Adicionar à lista de desejos';
                    }
                }
            });
            
            // Update counter
            if (data.count !== undefined) {
                updateWishlistCounter(data.count);
            }
        }
    })
    .catch(error => {
        console.log('Sync da wishlist falhou, usando cache local:', error.message);
    });
});

// Prevent wishlist button from triggering product link
document.addEventListener('click', function(e) {
    if (e.target.closest('.wishlist-btn')) {
        e.preventDefault();
        e.stopPropagation();
    }
});
</script>
@endauth
@endpush
