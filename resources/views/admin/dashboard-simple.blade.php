@extends('layouts.admin')

@section('title', 'Dashboard Simples')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">Dashboard Simples</h1>
            <p class="text-muted">Visão simplificada do sistema</p>
        </div>
    </div>

    <div class="row mb-4">
        @php
            $stats = [
                ['label' => 'Total de Produtos', 'value' => $totalProducts ?? 0, 'icon' => 'fa-box', 'color' => 'primary'],
                ['label' => 'Total de Categorias', 'value' => $totalCategories ?? 0, 'icon' => 'fa-tags', 'color' => 'success'],
                ['label' => 'Total de Pedidos', 'value' => $totalOrders ?? 0, 'icon' => 'fa-shopping-cart', 'color' => 'info'],
                ['label' => 'Total de Usuários', 'value' => $totalUsers ?? 0, 'icon' => 'fa-users', 'color' => 'warning']
            ];
        @endphp

        @foreach($stats as $stat)
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-{{ $stat['color'] }} shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-{{ $stat['color'] }} text-uppercase mb-1">
                                    {{ $stat['label'] }}
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stat['value'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas {{ $stat['icon'] }} fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ações Rápidas</h6>
                </div>
                <div class="card-body">
                    @php
                        $actions = [
                            ['route' => 'admin.products.create', 'icon' => 'fa-plus', 'label' => 'Novo Produto', 'class' => 'btn-success'],
                            ['route' => 'admin.categories.create', 'icon' => 'fa-tags', 'label' => 'Nova Categoria', 'class' => 'btn-primary'],
                            ['route' => 'admin.orders.index', 'icon' => 'fa-shopping-cart', 'label' => 'Ver Pedidos', 'class' => 'btn-info'],
                            ['route' => 'home', 'icon' => 'fa-store', 'label' => 'Ver Loja', 'class' => 'btn-warning', 'target' => '_blank']
                        ];
                    @endphp

                    <div class="row">
                        @foreach($actions as $action)
                            <div class="col-md-3 mb-3">
                                <a href="{{ route($action['route']) }}" 
                                   class="btn {{ $action['class'] }} btn-lg w-100"
                                   @isset($action['target']) target="{{ $action['target'] }}" @endisset>
                                    <i class="fas {{ $action['icon'] }}"></i><br>
                                    {{ $action['label'] }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.text-gray-800 { color: #5a5c69 !important; }
.text-gray-300 { color: #dddfeb !important; }
</style>
@endpush
