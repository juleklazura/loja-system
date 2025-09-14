@extends('layouts.admin')

@section('title', 'Gerenciar Produtos - Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Gerenciar Produtos</h1>
            <p class="text-muted">Controle e administre todos os produtos da loja</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Produto
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.products.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Buscar</label>
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Nome ou SKU" value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Categoria</label>
                                <select name="category_id" class="form-select">
                                    <option value="">Todas as categorias</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                                {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativo</option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inativo</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Estoque</label>
                                <select name="stock" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="low" {{ request('stock') === 'low' ? 'selected' : '' }}>Baixo</option>
                                    <option value="out" {{ request('stock') === 'out' ? 'selected' : '' }}>Esgotado</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-filter"></i> Filtrar
                                </button>
                                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        Lista de Produtos 
                        <span class="badge bg-secondary">{{ $products->total() }} produtos</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($products->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="60">Imagem</th>
                                        <th>Produto</th>
                                        <th>SKU</th>
                                        <th>Categoria</th>
                                        <th>Preço</th>
                                        <th>Estoque</th>
                                        <th>Status</th>
                                        <th width="120">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        <tr>
                                            <td>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    @if($product->images && count($product->images) > 0)
                                                        <img src="{{ asset('storage/' . $product->images[0]) }}" 
                                                             alt="{{ $product->name }}" 
                                                             class="img-fluid rounded" 
                                                             style="max-width: 50px; max-height: 50px; object-fit: cover;">
                                                    @else
                                                        <i class="fas fa-image text-muted"></i>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <h6 class="mb-0">{{ safe_limit($product->name, 40) }}</h6>
                                                    @if($product->description)
                                                        <small class="text-muted">{{ safe_limit($product->description, 60) }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <code>{{ $product->sku }}</code>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $product->category->name }}</span>
                                            </td>
                                            <td>
                                                <div>
                                                    @if($product->promotional_price && $product->promotional_price < $product->price)
                                                        <span class="text-success fw-bold">
                                                            R$ {{ format_currency($product->promotional_price) }}
                                                        </span>
                                                        <br>
                                                        <small class="text-muted text-decoration-line-through">
                                                            R$ {{ format_currency($product->price) }}
                                                        </small>
                                                    @else
                                                        <span class="fw-bold">
                                                            R$ {{ format_currency($product->price) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($product->stock_quantity <= 0)
                                                    <span class="badge bg-danger">Esgotado</span>
                                                @elseif($product->stock_quantity <= $product->min_stock)
                                                    <span class="badge bg-warning">{{ $product->stock_quantity }} (Baixo)</span>
                                                @else
                                                    <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($product->active)
                                                    <span class="badge bg-success">Ativo</span>
                                                @else
                                                    <span class="badge bg-secondary">Inativo</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('admin.products.show', $product) }}" 
                                                       class="btn btn-outline-info" title="Visualizar">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.products.edit', $product) }}" 
                                                       class="btn btn-outline-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            title="Excluir" onclick="deleteProduct({{ $product->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-box fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhum produto encontrado</h5>
                            <p class="text-muted">Começe criando seu primeiro produto!</p>
                            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Criar Produto
                            </a>
                        </div>
                    @endif
                </div>
                
                @if($products->hasPages())
                    <div class="card-footer">
                        <div class="mt-4">
                            {{ $products->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este produto?</p>
                <p class="text-danger"><strong>Esta ação não pode ser desfeita!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteProduct(productId) {
    const form = document.getElementById('deleteForm');
    form.action = `/admin/products/${productId}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endpush
