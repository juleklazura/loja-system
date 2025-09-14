@extends('layouts.admin')

@section('title', 'Visualizar Categoria - Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">{{ $category->name }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categorias</a></li>
                    <li class="breadcrumb-item active">{{ Str::limit($category->name, 30) }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-primary me-2">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Category Information -->
        <div class="col-lg-8">
            <!-- Basic Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informações da Categoria</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="150">Nome:</th>
                            <td>{{ $category->name }}</td>
                        </tr>
                        <tr>
                            <th>Descrição:</th>
                            <td>
                                @if($category->description)
                                    {{ $category->description }}
                                @else
                                    <span class="text-muted">Sem descrição</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if($category->active)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i> Ativa
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times"></i> Inativa
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Total de Produtos:</th>
                            <td>
                                <span class="badge bg-info">
                                    {{ $category->products->count() }} 
                                    {{ $category->products->count() == 1 ? 'produto' : 'produtos' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Criado em:</th>
                            <td>{{ $category->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Atualizado em:</th>
                            <td>{{ $category->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Products in this Category -->
            @if($category->products->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Produtos nesta Categoria ({{ $category->products->count() }})</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produto</th>
                                        <th>SKU</th>
                                        <th>Preço</th>
                                        <th>Estoque</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($category->products->take(10) as $product)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($product->images && count($product->images) > 0)
                                                        <img src="{{ asset('storage/' . $product->images[0]) }}" 
                                                             alt="{{ $product->name }}" 
                                                             class="rounded me-2" 
                                                             width="40" height="40" 
                                                             style="object-fit: cover;">
                                                    @else
                                                        <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" 
                                                             style="width: 40px; height: 40px;">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $product->name }}</strong><br>
                                                        <small class="text-muted">ID: {{ $product->id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <code>{{ $product->sku }}</code>
                                            </td>
                                            <td>
                                                <div>
                                                    @if($product->promotional_price)
                                                        <span class="text-danger">
                                                            <strong>R$ {{ format_currency($product->promotional_price) }}</strong>
                                                        </span><br>
                                                        <small class="text-muted text-decoration-line-through">
                                                            R$ {{ format_currency($product->price) }}
                                                        </small>
                                                    @else
                                                        <strong>R$ {{ format_currency($product->price) }}</strong>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($product->stock_quantity <= 0)
                                                    <span class="badge bg-danger">Esgotado</span>
                                                @elseif($product->stock_quantity <= $product->min_stock)
                                                    <span class="badge bg-warning">Baixo ({{ $product->stock_quantity }})</span>
                                                @else
                                                    <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($product->active)
                                                    <span class="badge bg-success">Ativo</span>
                                                @else
                                                    <span class="badge bg-danger">Inativo</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.products.show', $product) }}" 
                                                       class="btn btn-sm btn-outline-info" title="Ver Produto">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.products.edit', $product) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="Editar Produto">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($category->products->count() > 10)
                            <div class="card-footer text-center">
                                <a href="{{ route('admin.products.index', ['category' => $category->id]) }}" 
                                   class="btn btn-outline-primary">
                                    Ver todos os {{ $category->products->count() }} produtos
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h5>Nenhum produto nesta categoria</h5>
                        <p class="text-muted">Esta categoria ainda não possui produtos associados.</p>
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Adicionar Produto
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Estatísticas</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $category->products->count() }}</h4>
                                <small class="text-muted">Produtos</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ $category->products->where('active', true)->count() }}</h4>
                            <small class="text-muted">Ativos</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-info">{{ $category->products->sum('stock_quantity') }}</h4>
                                <small class="text-muted">Estoque Total</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-warning">{{ $category->products->where('stock_quantity', '<=', 0)->count() }}</h4>
                            <small class="text-muted">Esgotados</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Detalhes</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th>ID:</th>
                            <td>{{ $category->id }}</td>
                        </tr>
                        <tr>
                            <th>Slug:</th>
                            <td><code>{{ Str::slug($category->name) }}</code></td>
                        </tr>
                        <tr>
                            <th>Criado:</th>
                            <td>{{ $category->created_at->diffForHumans() }}</td>
                        </tr>
                        <tr>
                            <th>Modificado:</th>
                            <td>{{ $category->updated_at->diffForHumans() }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Editar Categoria
                        </a>
                        <a href="{{ route('admin.products.create', ['category' => $category->id]) }}" class="btn btn-outline-success">
                            <i class="fas fa-plus"></i> Adicionar Produto
                        </a>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list"></i> Todas as Categorias
                        </a>
                        <hr>
                        @if($category->products->count() == 0)
                            <button type="button" class="btn btn-outline-danger" onclick="deleteCategory()">
                                <i class="fas fa-trash"></i> Excluir Categoria
                            </button>
                        @else
                            <button type="button" class="btn btn-outline-secondary" disabled title="Não é possível excluir - possui produtos">
                                <i class="fas fa-lock"></i> Categoria com Produtos
                            </button>
                        @endif
                    </div>
                </div>
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
                <p>Tem certeza que deseja excluir a categoria <strong>{{ $category->name }}</strong>?</p>
                <p class="text-danger"><strong>Esta ação não pode ser desfeita!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" style="display: inline;">
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
function deleteCategory() {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endpush
