@extends('layouts.admin')

@section('title', 'Pedido #' . $order->id . ' - Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Pedido #{{ $order->id }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Pedidos</a></li>
                    <li class="breadcrumb-item active">Pedido #{{ $order->id }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Order Details -->
        <div class="col-lg-8">
            <!-- Order Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informações do Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="120">Pedido:</th>
                                    <td><strong>#{{ $order->id }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Data:</th>
                                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @php
                                            $statusClasses = [
                                                'pending' => 'bg-warning',
                                                'confirmed' => 'bg-info',
                                                'shipped' => 'bg-secondary',
                                                'delivered' => 'bg-success',
                                                'cancelled' => 'bg-danger'
                                            ];
                                            $statusLabels = [
                                                'pending' => 'Pendente',
                                                'confirmed' => 'Confirmado',
                                                'shipped' => 'Enviado',
                                                'delivered' => 'Entregue',
                                                'cancelled' => 'Cancelado'
                                            ];
                                        @endphp
                                        <span class="badge {{ $statusClasses[$order->status] ?? 'bg-secondary' }}">
                                            {{ $statusLabels[$order->status] ?? $order->status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total:</th>
                                    <td><strong class="text-success">R$ {{ format_currency($order->total) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Cliente</h6>
                            <div class="border rounded p-3">
                                <strong>{{ $order->user->name }}</strong><br>
                                <small class="text-muted">{{ $order->user->email }}</small><br>
                                @if($order->user->type === 'customer')
                                    <span class="badge bg-primary">Cliente</span>
                                @else
                                    <span class="badge bg-info">{{ safe_ucfirst($order->user->type) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Itens do Pedido ({{ $order->items->count() }})</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Produto</th>
                                    <th>Preço Unit.</th>
                                    <th>Quantidade</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($item->product && $item->product->images && count($item->product->images) > 0)
                                                    <img src="{{ asset('storage/' . $item->product->images[0]) }}" 
                                                         alt="{{ $item->product_name }}" 
                                                         class="rounded me-3" 
                                                         width="50" height="50" 
                                                         style="object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <strong>{{ $item->product_name }}</strong><br>
                                                    @if($item->product)
                                                        <small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                                    @else
                                                        <small class="text-danger">Produto removido</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>R$ {{ format_currency($item->price) }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $item->quantity }}</span>
                                        </td>
                                        <td>
                                            <strong>R$ {{ format_currency($item->price * $item->quantity) }}</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">Total do Pedido:</th>
                                    <th>
                                        <strong class="text-success fs-5">R$ {{ format_currency($order->total) }}</strong>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Ações</h5>
                </div>
                <div class="card-body">
                    @if($order->status !== 'delivered' && $order->status !== 'cancelled')
                        <div class="d-grid gap-2 mb-3">
                            <button type="button" class="btn btn-primary" onclick="showStatusModal()">
                                <i class="fas fa-edit"></i> Alterar Status
                            </button>
                        </div>
                    @endif
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list"></i> Todos os Pedidos
                        </a>
                        @if($order->status === 'cancelled')
                            <button type="button" class="btn btn-outline-danger" onclick="deleteOrder()">
                                <i class="fas fa-trash"></i> Excluir Pedido
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Order Timeline -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Status do Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @php
                            $statuses = ['pending', 'confirmed', 'shipped', 'delivered'];
                            $statusLabels = [
                                'pending' => 'Pendente',
                                'confirmed' => 'Confirmado',
                                'shipped' => 'Enviado',
                                'delivered' => 'Entregue'
                            ];
                            $currentIndex = array_search($order->status, $statuses);
                        @endphp
                        
                        @foreach($statuses as $index => $status)
                            <div class="d-flex align-items-center mb-2">
                                @if($order->status === 'cancelled' && $status !== 'pending')
                                    <i class="fas fa-times-circle text-muted me-2"></i>
                                    <span class="text-muted">{{ $statusLabels[$status] }}</span>
                                @elseif($index <= $currentIndex || $order->status === $status)
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span>{{ $statusLabels[$status] }}</span>
                                    @if($status === $order->status)
                                        <span class="badge bg-success ms-2">Atual</span>
                                    @endif
                                @else
                                    <i class="fas fa-circle text-muted me-2"></i>
                                    <span class="text-muted">{{ $statusLabels[$status] }}</span>
                                @endif
                            </div>
                        @endforeach
                        
                        @if($order->status === 'cancelled')
                            <div class="d-flex align-items-center">
                                <i class="fas fa-ban text-danger me-2"></i>
                                <span class="text-danger">Cancelado</span>
                                <span class="badge bg-danger ms-2">Atual</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Resumo</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th>Itens:</th>
                            <td>{{ $order->items->count() }}</td>
                        </tr>
                        <tr>
                            <th>Quantidade Total:</th>
                            <td>{{ $order->items->sum('quantity') }}</td>
                        </tr>
                        <tr>
                            <th>Criado em:</th>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Atualizado em:</th>
                            <td>{{ $order->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr class="border-top">
                            <th>Total:</th>
                            <th class="text-success">R$ {{ format_currency($order->total) }}</th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Alterar Status do Pedido #{{ $order->id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Novo Status</label>
                        <select name="status" class="form-select" required>
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pendente</option>
                            <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>Confirmado</option>
                            <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Enviado</option>
                            <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Entregue</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <small>
                            <strong>Status Atual:</strong> 
                            {{ $statusLabels[$order->status] ?? $order->status }}
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Alterar Status</button>
                </div>
            </form>
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
                <p>Tem certeza que deseja excluir o pedido <strong>#{{ $order->id }}</strong>?</p>
                <p class="text-danger"><strong>Esta ação não pode ser desfeita!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" style="display: inline;">
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
function showStatusModal() {
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    modal.show();
}

function deleteOrder() {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endpush
