@extends('layouts.frontend')

@section('title', 'Produtos - ' . config('app.name'))
@section('description', 'Navegue por nossa coleção completa de produtos com os melhores preços.')

@section('content')
<!-- Breadcrumb -->
<div class="bg-light py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Início</a></li>
                <li class="breadcrumb-item active">Produtos</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Main Content -->
<div class="container py-4">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Filtros</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('products.index') }}">
                        <!-- Search -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Buscar</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Nome do produto..." 
                                   value="{{ request('search') }}">
                        </div>

                        <!-- Categories -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Categoria</label>
                            <select name="category_id" class="form-select">
                                <option value="">Todas as categorias</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                            {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }} ({{ $category->active_products_count }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Faixa de Preço</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="number" name="min_price" class="form-control" 
                                           placeholder="Min" step="0.01" 
                                           value="{{ request('min_price') }}">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_price" class="form-control" 
                                           placeholder="Max" step="0.01" 
                                           value="{{ request('max_price') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Availability -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" name="in_stock" value="1" class="form-check-input" 
                                       {{ request('in_stock') ? 'checked' : '' }}>
                                <label class="form-check-label">Apenas em estoque</label>
                            </div>
                        </div>

                        <!-- Promotions -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" name="on_promotion" value="1" class="form-check-input" 
                                       {{ request('on_promotion') ? 'checked' : '' }}>
                                <label class="form-check-label">Apenas em promoção</label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Categories -->
            <div class="card shadow-sm mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Categorias Populares</h6>
                </div>
                <div class="list-group list-group-flush">
                    @foreach($categories->take(6) as $category)
                        <a href="{{ route('products.category', $category) }}" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            {{ $category->name }}
                            <span class="badge bg-primary rounded-pill">
                                {{ $category->active_products_count }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-lg-9">
            <!-- Results Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1">
                        @if(request('search'))
                            Resultados para "{{ request('search') }}"
                        @elseif(request('category_id'))
                            @php $selectedCategory = $categories->find(request('category_id')) @endphp
                            {{ $selectedCategory ? $selectedCategory->name : 'Produtos' }}
                        @else
                            Todos os Produtos
                        @endif
                    </h2>
                    <small class="text-muted">{{ $products->total() }} produto(s) encontrado(s)</small>
                </div>

                <!-- Sort Options -->
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 small">Ordenar por:</label>
                    <select name="sort" class="form-select form-select-sm" style="width: auto;" onchange="updateSort(this.value)">
                        <option value="latest" {{ $sortBy === 'latest' ? 'selected' : '' }}>Mais recentes</option>
                        <option value="name" {{ $sortBy === 'name' ? 'selected' : '' }}>Nome A-Z</option>
                        <option value="price_asc" {{ $sortBy === 'price_asc' ? 'selected' : '' }}>Menor preço</option>
                        <option value="price_desc" {{ $sortBy === 'price_desc' ? 'selected' : '' }}>Maior preço</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            @if($products->count() > 0)
                <div class="row">
                    @foreach($products as $product)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card product-card h-100">
                                <!-- Product Badge -->
                                @if($product->promotional_price && $product->promotional_price < $product->price)
                                    <span class="position-absolute top-0 start-0 badge bg-danger m-2" style="z-index: 1;">
                                        {{ round((($product->price - $product->promotional_price) / $product->price) * 100) }}% OFF
                                    </span>
                                @endif

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
                                    </div>
                                </a>

                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title">{{ $product->name }}</h6>
                                    <p class="card-text text-muted small flex-grow-1">
                                        {{ safe_limit($product->description, 80) }}
                                    </p>
                                    
                                    <!-- Category -->
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-tag"></i> {{ $product->category->name }}
                                        </small>
                                    </div>

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
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Stock Status -->
                                        <div class="mb-2">
                                            @if($product->stock_quantity > 10)
                                                <small class="text-success">
                                                    <i class="fas fa-check-circle"></i> Em estoque
                                                </small>
                                            @else
                                                <small class="text-warning">
                                                    <i class="fas fa-exclamation-triangle"></i> 
                                                    Últimas {{ $product->stock_quantity }} unidades
                                                </small>
                                            @endif
                                        </div>
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
                                            <div class="d-flex gap-1">
                                                @auth
                                                    <button onclick="addToCart({{ $product->id }})" class="btn btn-success btn-sm flex-grow-1">
                                                        <i class="fas fa-cart-plus"></i> Carrinho
                                                    </button>
                                                    <button onclick="toggleWishlist({{ $product->id }}, this)" 
                                                            class="btn btn-outline-danger btn-sm wishlist-btn" 
                                                            data-product-id="{{ $product->id }}">
                                                        <i class="far fa-heart"></i>
                                                    </button>
                                                @else
                                                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm flex-grow-1">
                                                        <i class="fas fa-sign-in-alt"></i> Login para Comprar
                                                    </a>
                                                @endauth
                                            </div>
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

                <!-- Pagination -->
                <div class="mt-5">
                    {{ $products->appends(request()->query())->links() }}
                </div>
            @else
                <!-- No Products Found -->
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4>Nenhum produto encontrado</h4>
                    <p class="text-muted">Tente ajustar os filtros ou fazer uma nova busca.</p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary">
                        <i class="fas fa-refresh"></i> Ver Todos os Produtos
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateSort(sortValue) {
    const url = new URL(window.location);
    url.searchParams.set('sort', sortValue);
    window.location.href = url.toString();
}

@auth
function addToCart(productId) {
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
        console.error('Erro:', error);
        showCartMessage('Erro ao adicionar produto ao carrinho!', false);
    });
}

function toggleWishlist(productId, buttonElement) {
    const icon = buttonElement.querySelector('i');
    const originalIcon = icon.className;
    const originalClasses = buttonElement.className;
    
    // Show loading state
    buttonElement.disabled = true;
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
                buttonElement.className = 'btn btn-danger btn-sm wishlist-btn';
                icon.className = 'fas fa-heart';
            } else {
                buttonElement.className = 'btn btn-outline-danger btn-sm wishlist-btn';
                icon.className = 'far fa-heart';
            }
            
            // Show success message
            showCartMessage(data.message, true);
        } else {
            showCartMessage(data.message || 'Erro ao atualizar lista de desejos', false);
            // Restore original state
            buttonElement.className = originalClasses;
            icon.className = originalIcon;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showCartMessage('Erro ao atualizar lista de desejos', false);
        // Restore original state
        buttonElement.className = originalClasses;
        icon.className = originalIcon;
    })
    .finally(() => {
        buttonElement.disabled = false;
    });
}

// Load wishlist status on page load
document.addEventListener('DOMContentLoaded', function() {
    fetch('{{ route("wishlist.index") }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.wishlistProductIds.length > 0) {
            const wishlistButtons = document.querySelectorAll('.wishlist-btn');
            wishlistButtons.forEach(button => {
                const productId = parseInt(button.dataset.productId);
                if (data.wishlistProductIds.includes(productId)) {
                    button.className = 'btn btn-danger btn-sm wishlist-btn';
                    button.querySelector('i').className = 'fas fa-heart';
                }
            });
        }
    })
    .catch(error => {
        console.error('Erro ao carregar wishlist:', error);
    });
});
@endauth
</script>
@endpush
