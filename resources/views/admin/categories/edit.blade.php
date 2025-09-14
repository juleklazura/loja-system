@extends('layouts.admin')

@section('title', 'Editar Categoria - Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Editar Categoria</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categorias</a></li>
                    <li class="breadcrumb-item active">Editar {{ Str::limit($category->name, 30) }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-outline-info me-2">
                <i class="fas fa-eye"></i> Visualizar
            </a>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <form action="{{ route('admin.categories.update', $category) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Category Information -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Informações da Categoria</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Nome da Categoria *</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $category->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Nome único para identificar a categoria
                                </small>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Descrição</label>
                                <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Descrição opcional para explicar o que pertence a esta categoria
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
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
                                   id="active" {{ old('active', $category->active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">
                                Categoria Ativa
                            </label>
                        </div>
                        @error('active')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Categorias inativas não aparecerão na loja
                        </small>
                    </div>
                </div>

                <!-- Current Status -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Status Atual</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <small>
                                <strong>Produtos:</strong> {{ $category->products->count() }}<br>
                                <strong>Produtos Ativos:</strong> {{ $category->products->where('active', true)->count() }}<br>
                                @if($category->products->count() > 0)
                                    <strong>Último produto:</strong> {{ $category->products->sortByDesc('created_at')->first()->name ?? 'N/A' }}
                                @endif
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Category Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informações</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th>ID:</th>
                                <td>{{ $category->id }}</td>
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

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                            <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-outline-info">
                                <i class="fas fa-eye"></i> Visualizar
                            </a>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Cancelar
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

<!-- Warning Modal for Status Change -->
<div class="modal fade" id="statusWarningModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Atenção</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Ao desativar esta categoria, os produtos desta categoria também ficarão indisponíveis na loja.</p>
                <p><strong>Produtos afetados: {{ $category->products->where('active', true)->count() }}</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="revertStatusChange()">Cancelar</button>
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Continuar</button>
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

let originalStatus = @json($category->active);

// Show warning when deactivating category with active products
document.getElementById('active').addEventListener('change', function(e) {
    const hasActiveProducts = @json($category->products->where('active', true)->count());
    
    if (originalStatus && !e.target.checked && hasActiveProducts > 0) {
        const modal = new bootstrap.Modal(document.getElementById('statusWarningModal'));
        modal.show();
    }
});

function revertStatusChange() {
    document.getElementById('active').checked = originalStatus;
}

// Live preview
function updatePreview() {
    const name = document.querySelector('input[name="name"]').value || 'Nome da Categoria';
    const description = document.querySelector('textarea[name="description"]').value || 'Descrição da categoria...';
    const active = document.querySelector('input[name="active"]:checked') ? 'Ativa' : 'Inativa';
    
    // Update breadcrumb if needed
    const breadcrumbActive = document.querySelector('.breadcrumb .active');
    if (breadcrumbActive) {
        breadcrumbActive.textContent = `Editar ${name.substring(0, 30)}${name.length > 30 ? '...' : ''}`;
    }
}

// Update preview when fields change
document.querySelector('input[name="name"]').addEventListener('input', updatePreview);
document.querySelector('textarea[name="description"]').addEventListener('input', updatePreview);

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const name = document.querySelector('input[name="name"]').value.trim();
    
    if (!name) {
        e.preventDefault();
        alert('Por favor, informe o nome da categoria.');
        document.querySelector('input[name="name"]').focus();
        return false;
    }
    
    if (name.length < 2) {
        e.preventDefault();
        alert('O nome da categoria deve ter pelo menos 2 caracteres.');
        document.querySelector('input[name="name"]').focus();
        return false;
    }
});
</script>
@endpush
