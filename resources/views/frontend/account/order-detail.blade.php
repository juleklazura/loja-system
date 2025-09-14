@extends('layouts.frontend')

@section('title', 'Detalhes do Pedido #' . $order->id)

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5>Minha Conta</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('user.orders') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-bag me-2"></i>Meus Pedidos
                    </a>
                    <a href="{{ route('profile.edit') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-user me-2"></i>Perfil
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Detalhes do Pedido #{{ $order->id }}</h4>
                <a href="{{ route('user.orders') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Voltar aos Pedidos
                </a>
            </div>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Itens do Pedido</h5>
                        </div>
                        <div class="card-body">
                            @foreach($order->orderItems as $item)
                            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                @if($item->product->image)
                                    <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}" class="me-3" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center me-3" style="width: 80px; height: 80px; border-radius: 8px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                                
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $item->product->name }}</h6>
                                    <p class="text-muted mb-1">{{ Str::limit($item->product->description, 100) }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">
                                            Quantidade: <strong>{{ $item->quantity }}</strong>
                                        </span>
                                        <div class="text-end">
                                            <div class="text-muted small">Preço unitário: R$ {{ format_currency($item->unit_price) }}</div>
                                            <div class="fw-bold">Subtotal: R$ {{ format_currency($item->quantity * $item->unit_price) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Informações do Pedido</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted">Data do Pedido</small>
                                <div class="fw-bold">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">Status</small>
                                <div>
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
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">Total de Itens</small>
                                <div class="fw-bold">{{ $order->orderItems->sum('quantity') }} {{ $order->orderItems->sum('quantity') == 1 ? 'item' : 'itens' }}</div>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Total do Pedido</span>
                                <span class="fw-bold h5 text-primary mb-0">R$ {{ format_currency($order->total_amount) }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Dados de Entrega</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <strong>{{ $order->user->name }}</strong>
                            </div>
                            <div class="text-muted">
                                {{ $order->user->email }}
                            </div>
                            
                            @if($order->shipping_address)
                                <hr>
                                <div class="text-muted">
                                    {{ $order->shipping_address }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
