@extends('layouts.admin')

@section('title', 'Promoções - Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Gerenciar Promoções</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Promoções</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nova Promoção
            </a>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $promotions->total() }}</h4>
                    <small>Total de Promoções</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $promotions->where('active', true)->count() }}</h4>
                    <small>Ativas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $promotions->where('active', false)->count() }}</h4>
                    <small>Inativas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $promotions->where('end_date', '>=', now())->where('start_date', '<=', now())->where('active', true)->count() }}</h4>
                    <small>Em Andamento</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Buscar por nome..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="active" class="form-select">
                        <option value="">Todos os status</option>
                        <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Ativas</option>
                        <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inativas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Promotions Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Lista de Promoções ({{ $promotions->total() }})</h5>
        </div>
        <div class="card-body p-0">
            @if($promotions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>Desconto</th>
                                <th>Período</th>
                                <th>Produtos</th>
                                <th>Status</th>
                                <th width="150">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($promotions as $promotion)
                                <tr>
                                    <td>
                                        <strong>{{ $promotion->name }}</strong>
                                        @if($promotion->description)
                                            <br><small class="text-muted">{{ safe_limit($promotion->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($promotion->discount_type === 'percentage')
                                            <span class="badge bg-success">{{ $promotion->discount_value }}%</span>
                                        @else
                                            <span class="badge bg-info">R$ {{ format_currency($promotion->discount_value) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            <strong>Início:</strong> {{ \Carbon\Carbon::parse($promotion->start_date)->format('d/m/Y') }}<br>
                                            <strong>Fim:</strong> {{ \Carbon\Carbon::parse($promotion->end_date)->format('d/m/Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $promotion->products->count() }} produtos</span>
                                    </td>
                                    <td>
                                        @php
                                            $now = now();
                                            $isActive = $promotion->active;
                                            $isInPeriod = $now->between($promotion->start_date, $promotion->end_date);
                                        @endphp
                                        
                                        @if($isActive && $isInPeriod)
                                            <span class="badge bg-success">
                                                <i class="fas fa-play"></i> Em Andamento
                                            </span>
                                        @elseif($isActive && $now < $promotion->start_date)
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock"></i> Agendada
                                            </span>
                                        @elseif($isActive && $now > $promotion->end_date)
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-stop"></i> Expirada
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-pause"></i> Inativa
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.promotions.show', $promotion) }}" 
                                               class="btn btn-sm btn-outline-info" title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.promotions.edit', $promotion) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deletePromotion({{ $promotion->id }}, '{{ addslashes($promotion->name) }}')" 
                                                    title="Excluir">
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
                    <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                    <h5>Nenhuma promoção encontrada</h5>
                    <p class="text-muted">Comece criando sua primeira promoção.</p>
                    <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Criar Promoção
                    </a>
                </div>
            @endif
        </div>
        
        @if($promotions->hasPages())
            <div class="card-footer">
                <div class="mt-4">
                    {{ $promotions->withQueryString()->links() }}
                </div>
            </div>
        @endif
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
                <p>Tem certeza que deseja excluir a promoção <strong id="promotionName"></strong>?</p>
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
function deletePromotion(id, name) {
    document.getElementById('promotionName').textContent = name;
    document.getElementById('deleteForm').action = `/admin/promotions/${id}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endpush
