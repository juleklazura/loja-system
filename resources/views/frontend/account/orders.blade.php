@extends('layouts.frontend')

@section('title', 'Meus Pedidos')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5>Minha Conta</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('user.orders') }}" class="list-group-item list-group-item-action active">
                        <i class="fas fa-shopping-bag me-2"></i>Meus Pedidos
                    </a>
                    <a href="{{ route('profile.edit') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-user me-2"></i>Perfil
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Meus Pedidos</h5>
                </div>
                
                <div class="card-body">
                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Pedido #</th>
                                        <th>Data</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Itens</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                    <tr>
                                        <td>
                                            <strong>#{{ $order->id }}</strong>
                                        </td>
                                        <td>
                                            {{ $order->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td>
                                            @switch($order->status)
                                                @case('pending')
                                                    <span class="badge bg-warning">Pendente</span>
                                                    @break
                                                @case('processing')
                                                    <span class="badge bg-info">Processando</span>
                                                    @break
                                                @case('shipped')
                                                    <span class="badge bg-primary">Enviado</span>
                                                    @break
                                                @case('delivered')
                                                    <span class="badge bg-success">Entregue</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-danger">Cancelado</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ format_status($order->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <strong>R$ {{ format_currency($order->total_amount) }}</strong>
                                        </td>
                                        <td>
                                            {{ $order->orderItems->count() }} {{ $order->orderItems->count() == 1 ? 'item' : 'itens' }}
                                        </td>
                                        <td>
                                            <a href="{{ route('user.order.detail', $order) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>Ver Detalhes
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center">
                            {{ $orders->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                            <h5>Nenhum pedido encontrado</h5>
                            <p class="text-muted">Você ainda não fez nenhum pedido em nossa loja.</p>
                            <a href="{{ route('home') }}" class="btn btn-primary">
                                <i class="fas fa-shopping-cart me-2"></i>Começar a Comprar
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
