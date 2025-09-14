@extends('layouts.admin')

@section('title', 'Editar Produto - Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Editar Produto</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Produtos</a></li>
                    <li class="breadcrumb-item active">Editar {{ safe_limit($product->name, 30) }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.products.show', $product) }}" class="btn btn-outline-info me-2">
                <i class="fas fa-eye"></i> Visualizar
            </a>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Product Information -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informações do Produto</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Nome do Produto *</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $product->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SKU *</label>
                                <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" 
                                       value="{{ old('sku', $product->sku) }}" required>
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Categoria *</label>
                                <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                    <option value="">Selecione uma categoria</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                                {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Descrição</label>
                                <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Preços</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Preço Regular *</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" step="0.01" name="price" 
                                           class="form-control @error('price') is-invalid @enderror" 
                                           value="{{ old('price', $product->price) }}" required>
                                </div>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Preço Promocional</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" step="0.01" name="promotional_price" 
                                           class="form-control @error('promotional_price') is-invalid @enderror" 
                                           value="{{ old('promotional_price', $product->promotional_price) }}">
                                </div>
                                @error('promotional_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Deixe em branco se não houver promoção</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Images -->
                @if($product->images && count($product->images) > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Imagens Atuais</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($product->images as $index => $image)
                                    <div class="col-md-3 mb-3">
                                        <div class="card">
                                            <img src="{{ asset('storage/' . $image) }}" 
                                                 alt="{{ $product->name }}" 
                                                 class="card-img-top" 
                                                 style="height: 150px; object-fit: cover;">
                                            <div class="card-body p-2">
                                                <div class="form-check">
                                                    <input type="checkbox" name="remove_images[]" value="{{ $index }}" 
                                                           class="form-check-input" id="remove_{{ $index }}">
                                                    <label class="form-check-label small" for="remove_{{ $index }}">
                                                        Remover esta imagem
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- New Images -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Adicionar Novas Imagens</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Novas Imagens</label>
                            <input type="file" name="images[]" multiple accept="image/*" 
                                   class="form-control @error('images') is-invalid @enderror">
                            @error('images')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Você pode selecionar múltiplas imagens para adicionar</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Inventory -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Estoque</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Quantidade em Estoque *</label>
                            <input type="number" name="stock_quantity" 
                                   class="form-control @error('stock_quantity') is-invalid @enderror" 
                                   value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" required>
                            @error('stock_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Estoque Mínimo</label>
                            <input type="number" name="min_stock" 
                                   class="form-control @error('min_stock') is-invalid @enderror" 
                                   value="{{ old('min_stock', $product->min_stock) }}" min="0">
                            @error('min_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Alerta quando o estoque estiver baixo</small>
                        </div>

                        <!-- Current Stock Status -->
                        <div class="alert alert-info">
                            <small>
                                <strong>Status Atual:</strong><br>
                                @if($product->stock_quantity <= 0)
                                    <span class="text-danger">Produto Esgotado</span>
                                @elseif($product->stock_quantity <= $product->min_stock)
                                    <span class="text-warning">Estoque Baixo ({{ $product->stock_quantity }} unidades)</span>
                                @else
                                    <span class="text-success">Estoque OK ({{ $product->stock_quantity }} unidades)</span>
                                @endif
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input type="hidden" name="active" value="0">
                            <input type="checkbox" name="active" value="1" 
                                   class="form-check-input @error('active') is-invalid @enderror" 
                                   id="active" {{ old('active', $product->active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">
                                Produto Ativo
                            </label>
                        </div>
                        @error('active')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Produtos inativos não aparecerão na loja</small>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informações</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th>ID:</th>
                                <td>{{ $product->id }}</td>
                            </tr>
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

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                            <a href="{{ route('admin.products.show', $product) }}" class="btn btn-outline-info">
                                <i class="fas fa-eye"></i> Visualizar
                            </a>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <hr>
                            <button type="button" class="btn btn-outline-danger" onclick="deleteProduct()">
                                <i class="fas fa-trash"></i> Excluir Produto
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
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

// Preview new images
document.querySelector('input[name="images[]"]').addEventListener('change', function(e) {
    const files = e.target.files;
    const previewContainer = document.getElementById('image-preview');
    
    if (!previewContainer) {
        const preview = document.createElement('div');
        preview.id = 'image-preview';
        preview.className = 'mt-3';
        preview.innerHTML = '<h6>Prévia das novas imagens:</h6><div class="row" id="preview-row"></div>';
        e.target.parentNode.appendChild(preview);
    }
    
    const previewRow = document.getElementById('preview-row');
    previewRow.innerHTML = '';
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const col = document.createElement('div');
            col.className = 'col-md-3 mb-2';
            col.innerHTML = `
                <img src="${e.target.result}" class="img-thumbnail" style="height: 100px; object-fit: cover;">
            `;
            previewRow.appendChild(col);
        };
        
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
