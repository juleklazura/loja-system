@extends('layouts.admin')

@section('title', $product->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">{{ $product->name }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Produtos</a></li>
                    <li class="breadcrumb-item active">{{ safe_limit($product->name, 30) }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary me-2">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Product Details -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informações Básicas</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="120">Nome:</th>
                                    <td>{{ $product->name }}</td>
                                </tr>
                                <tr>
                                    <th>SKU:</th>
                                    <td><code>{{ $product->sku }}</code></td>
                                </tr>
                                <tr>
                                    <th>Categoria:</th>
                                    <td><span class="badge bg-info">{{ $product->category->name }}</span></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($product->active)
                                            <span class="badge bg-success">Ativo</span>
                                        @else
                                            <span class="badge bg-secondary">Inativo</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="120">Preço:</th>
                                    <td class="h5 text-primary">R$ {{ format_currency($product->price) }}</td>
                                </tr>
                                @if($product->promotional_price)
                                <tr>
                                    <th>Preço Promocional:</th>
                                    <td class="h5 text-success">R$ {{ format_currency($product->promotional_price) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Criado em:</th>
                                    <td>{{ $product->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Atualizado em:</th>
                                    <td>{{ $product->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($product->description)
                        <hr>
                        <h6>Descrição:</h6>
                        <p class="text-muted">{{ $product->description }}</p>
                    @endif
                </div>
            </div>

            <!-- Images -->
            @if($product->images && count($product->images) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Imagens do Produto</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($product->images as $index => $image)
                                <div class="col-md-3 mb-3">
                                    <div class="card">
                                        <img src="{{ asset('storage/' . $image) }}" 
                                             alt="{{ $product->name }}" 
                                             class="card-img-top" 
                                             style="height: 200px; object-fit: cover;">
                                        <div class="card-body p-2 text-center">
                                            <small class="text-muted">Imagem {{ $index + 1 }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Related Products -->
            @if($relatedProducts->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Produtos Relacionados (Mesma Categoria)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($relatedProducts as $related)
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ safe_limit($related->name, 25) }}</h6>
                                            <p class="card-text">
                                                <small class="text-muted">{{ $related->sku }}</small><br>
                                                <span class="text-primary">R$ {{ format_currency($related->effective_price) }}</span>
                                            </p>
                                            <a href="{{ route('admin.products.show', $related) }}" class="btn btn-sm btn-outline-primary">
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

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Stock Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informações de Estoque</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if($product->stock_quantity <= 0)
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Produto Esgotado</strong>
                            </div>
                        @elseif($product->stock_quantity <= $product->min_stock)
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-circle"></i>
                                <strong>Estoque Baixo</strong>
                            </div>
                        @else
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <strong>Estoque OK</strong>
                            </div>
                        @endif
                    </div>

                    <table class="table table-borderless">
                        <tr>
                            <th>Quantidade Atual:</th>
                            <td class="text-end">
                                <span class="h4 text-primary">{{ $product->stock_quantity }}</span> unidades
                            </td>
                        </tr>
                        <tr>
                            <th>Estoque Mínimo:</th>
                            <td class="text-end">{{ $product->min_stock }} unidades</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td class="text-end">
                                @if($product->isInStock())
                                    <span class="badge bg-success">Disponível</span>
                                @else
                                    <span class="badge bg-danger">Indisponível</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Ações Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Editar Produto
                        </a>
                        <a href="{{ route('products.show', $product) }}" target="_blank" class="btn btn-outline-info">
                            <i class="fas fa-external-link-alt"></i> Ver na Loja
                        </a>
                        @if($product->active)
                            <form action="{{ route('admin.products.update', $product) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="active" value="0">
                                <button type="submit" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-eye-slash"></i> Desativar
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.products.update', $product) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="active" value="1">
                                <button type="submit" class="btn btn-outline-success w-100">
                                    <i class="fas fa-eye"></i> Ativar
                                </button>
                            </form>
                        @endif
                        <hr>
                        <button type="button" class="btn btn-outline-danger" onclick="deleteProduct()">
                            <i class="fas fa-trash"></i> Excluir Produto
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Estatísticas</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless small">
                        <tr>
                            <th>Vendas Totais:</th>
                            <td class="text-end">0 unidades</td>
                        </tr>
                        <tr>
                            <th>Receita Total:</th>
                            <td class="text-end">R$ 0,00</td>
                        </tr>
                        <tr>
                            <th>Última Venda:</th>
                            <td class="text-end">Nunca</td>
                        </tr>
                        <tr>
                            <th>Visualizações:</th>
                            <td class="text-end">-</td>
                        </tr>
                    </table>
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
                <p>Tem certeza que deseja excluir o produto <strong>{{ $product->name }}</strong>?</p>
                <p class="text-danger"><strong>Esta ação não pode ser desfeita!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" style="display: inline;">
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
function deleteProduct() {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endpush
