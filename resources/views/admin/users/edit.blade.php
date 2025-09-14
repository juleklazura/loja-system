@extends('layouts.admin')

@section('title', 'Editar Usuário')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Editar Usuário</h1>
    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informações do Usuário</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $user->name) }}" 
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $user->email) }}" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Tipo de Usuário <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" 
                                id="type" 
                                name="type" 
                                required
                                @if($user->id === auth()->id()) disabled @endif>
                            <option value="customer" {{ old('type', $user->type) === 'customer' ? 'selected' : '' }}>
                                Cliente
                            </option>
                            <option value="admin" {{ old('type', $user->type) === 'admin' ? 'selected' : '' }}>
                                Administrador
                            </option>
                        </select>
                        @if($user->id === auth()->id())
                            <div class="form-text">Você não pode alterar seu próprio tipo de usuário.</div>
                            <input type="hidden" name="type" value="{{ $user->type }}">
                        @endif
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Nova Senha</label>
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password">
                        <div class="form-text">Deixe em branco para manter a senha atual.</div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmar Nova Senha</label>
                        <input type="password" 
                               class="form-control" 
                               id="password_confirmation" 
                               name="password_confirmation">
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                        
                        @if($user->id !== auth()->id() && $user->orders->count() === 0)
                        <button type="button" 
                                class="btn btn-danger" 
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteModal">
                            <i class="fas fa-trash"></i> Excluir Usuário
                        </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- User Summary -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Resumo do Usuário</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="avatar-lg mx-auto mb-2">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <span class="text-white h5">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        </div>
                    </div>
                    <h6>{{ $user->name }}</h6>
                    <small class="text-muted">{{ $user->email }}</small>
                </div>

                <hr>

                <div class="row text-center">
                    <div class="col-6">
                        <h6>{{ $user->orders->count() }}</h6>
                        <small class="text-muted">Pedidos</small>
                    </div>
                    <div class="col-6">
                        <h6>R$ {{ format_currency($user->orders->sum('total')) }}</h6>
                        <small class="text-muted">Total Gasto</small>
                    </div>
                </div>

                <hr>

                <div class="small">
                    <p><strong>Cadastro:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Última atualização:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}</p>
                    <p>
                        <strong>Email verificado:</strong> 
                        @if($user->email_verified_at)
                            <span class="badge bg-success">Sim</span>
                        @else
                            <span class="badge bg-danger">Não</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Restrictions -->
        @if($user->id === auth()->id() || $user->orders->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Restrições</h5>
            </div>
            <div class="card-body">
                @if($user->id === auth()->id())
                <div class="alert alert-warning small">
                    <i class="fas fa-exclamation-triangle"></i>
                    Você não pode alterar seu próprio tipo de usuário ou excluir sua própria conta.
                </div>
                @endif

                @if($user->orders->count() > 0)
                <div class="alert alert-info small">
                    <i class="fas fa-info-circle"></i>
                    Este usuário possui {{ $user->orders->count() }} pedido(s) e não pode ser excluído.
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Delete Modal -->
@if($user->id !== auth()->id() && $user->orders->count() === 0)
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o usuário <strong>{{ $user->name }}</strong>?</p>
                <p class="text-danger small">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    // Password confirmation validation
    document.getElementById('password_confirmation').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmation = this.value;
        
        if (password !== confirmation && confirmation !== '') {
            this.setCustomValidity('As senhas não coincidem');
        } else {
            this.setCustomValidity('');
        }
    });

    document.getElementById('password').addEventListener('input', function() {
        const confirmation = document.getElementById('password_confirmation');
        if (confirmation.value !== '') {
            confirmation.dispatchEvent(new Event('input'));
        }
    });
</script>
@endsection
