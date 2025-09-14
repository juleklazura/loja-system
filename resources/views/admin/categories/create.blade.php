@extends('layouts.admin')

@section('title', 'Nova Categoria - Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Nova Categoria</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categorias</a></li>
                    <li class="breadcrumb-item active">Nova Categoria</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <form action="{{ route('admin.categories.store') }}" method="POST">
        @csrf
        
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
                                       value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Nome único para identificar a categoria
                                </small>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Descrição</label>
                                <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
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
                                   id="active" {{ old('active', '1') ? 'checked' : '' }}>
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

                <!-- Help -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Ajuda</h5>
                    </div>
                    <div class="card-body">
                        <h6>Dicas para Categorias:</h6>
                        <ul class="small text-muted">
                            <li>Use nomes claros e específicos</li>
                            <li>Evite categorias muito genéricas</li>
                            <li>Pense na experiência do usuário</li>
                            <li>Mantenha uma hierarquia lógica</li>
                        </ul>
                        
                        <hr>
                        
                        <h6>Exemplos de Categorias:</h6>
                        <div class="small text-muted">
                            <span class="badge bg-light text-dark me-1">Eletrônicos</span>
                            <span class="badge bg-light text-dark me-1">Roupas</span>
                            <span class="badge bg-light text-dark me-1">Casa & Jardim</span>
                            <span class="badge bg-light text-dark me-1">Esportes</span>
                            <span class="badge bg-light text-dark me-1">Livros</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Criar Categoria
                            </button>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Prévia da Categoria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title" id="previewName">Nome da Categoria</h5>
                        <p class="card-text text-muted" id="previewDescription">Descrição da categoria...</p>
                        <span class="badge bg-success" id="previewStatus">Ativa</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Live preview
function updatePreview() {
    const name = document.querySelector('input[name="name"]').value || 'Nome da Categoria';
    const description = document.querySelector('textarea[name="description"]').value || 'Descrição da categoria...';
    const active = document.querySelector('input[name="active"]:checked') ? 'Ativa' : 'Inativa';
    
    document.getElementById('previewName').textContent = name;
    document.getElementById('previewDescription').textContent = description;
    
    const statusBadge = document.getElementById('previewStatus');
    statusBadge.textContent = active;
    statusBadge.className = `badge ${active === 'Ativa' ? 'bg-success' : 'bg-danger'}`;
}

// Update preview when fields change
document.querySelector('input[name="name"]').addEventListener('input', updatePreview);
document.querySelector('textarea[name="description"]').addEventListener('input', updatePreview);
document.querySelector('input[name="active"]').addEventListener('change', updatePreview);

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

// Auto-generate slug preview (future feature)
document.querySelector('input[name="name"]').addEventListener('input', function(e) {
    const name = e.target.value;
    const slug = name.toLowerCase()
                     .replace(/[^\w\s-]/g, '')
                     .replace(/\s+/g, '-')
                     .replace(/--+/g, '-')
                     .trim();
    
    // Could add a slug field in the future
    console.log('Slug preview:', slug);
});
</script>
@endpush
