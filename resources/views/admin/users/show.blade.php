@extends('layouts.admin')

@section('title', 'Detalhes do Usuário')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Detalhes do Usuário</h1>
    <div>
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Editar
        </a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <!-- User Info -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar-lg mx-auto mb-3">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <span class="text-white h3">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                    </div>
                </div>
                <h4>{{ $user->name }}</h4>
                <p class="text-muted">{{ $user->email }}</p>
                
                @if($user->type === 'admin')
                    <span class="badge bg-warning mb-3">
                        <i class="fas fa-user-shield"></i> Administrador
                    </span>
                @else
                    <span class="badge bg-primary mb-3">
                        <i class="fas fa-user"></i> Cliente
                    </span>
                @endif
                
                <div class="row text-center">
                    <div class="col-4">
                        <h5>{{ $user->orders->count() }}</h5>
                        <small class="text-muted">Pedidos</small>
                    </div>
                    <div class="col-4">
                        <h5>R$ {{ format_currency($user->orders->sum('total')) }}</h5>
                        <small class="text-muted">Total Gasto</small>
                    </div>
                    <div class="col-4">
                        <h5>{{ $user->created_at->diffInDays() }}</h5>
                        <small class="text-muted">Dias</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Details -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Informações do Usuário</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>ID:</strong></td>
                        <td>{{ $user->id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tipo:</strong></td>
                        <td>
                            @if($user->type === 'admin')
                                <span class="badge bg-warning">Administrador</span>
                            @else
                                <span class="badge bg-primary">Cliente</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Cadastro:</strong></td>
                        <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Última atualização:</strong></td>
                        <td>{{ $user->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Verificado:</strong></td>
                        <td>
                            @if($user->email_verified_at)
                                <span class="badge bg-success">Sim</span>
                                <small class="text-muted d-block">{{ $user->email_verified_at->format('d/m/Y H:i') }}</small>
                            @else
                                <span class="badge bg-danger">Não</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Orders History -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Histórico de Pedidos</h5>
            </div>
            <div class="card-body">
                @if($user->orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Data</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->orders as $order)
                                <tr>
                                    <td>#{{ $order->id }}</td>
                                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        @switch($order->status)
                                            @case('pending')
                                                <span class="badge bg-warning">Pendente</span>
                                                @break
                                            @case('confirmed')
                                                <span class="badge bg-info">Confirmado</span>
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
                                        @endswitch
                                    </td>
                                    <td>R$ {{ format_currency($order->total) }}</td>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h6>Nenhum pedido encontrado</h6>
                        <p class="text-muted">Este usuário ainda não fez nenhum pedido.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Atividade Recente</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Usuário cadastrado</h6>
                            <p class="timeline-text">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    
                    @if($user->email_verified_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Email verificado</h6>
                            <p class="timeline-text">{{ $user->email_verified_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @foreach($user->orders->take(3) as $order)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Pedido #{{ $order->id }} realizado</h6>
                            <p class="timeline-text">{{ $order->created_at->format('d/m/Y H:i') }} - R$ {{ format_currency($order->total) }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    
    .timeline-marker {
        position: absolute;
        left: -22px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid #fff;
    }
    
    .timeline-title {
        margin-bottom: 5px;
        font-size: 14px;
    }
    
    .timeline-text {
        margin-bottom: 0;
        font-size: 12px;
        color: #6c757d;
    }
</style>
@endsection
