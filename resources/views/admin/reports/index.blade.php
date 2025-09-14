@extends('layouts.admin')

@section('title', 'Relatórios')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Relatórios e Análises</h1>
            <p class="text-muted">Visualize dados e estatísticas detalhadas do sistema</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir Relatório
            </button>
        </div>
    </div>

<!-- Date Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="start_date" class="form-label">Data Inicial</label>
                <input type="date" 
                       class="form-control" 
                       id="start_date" 
                       name="start_date" 
                       value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">Data Final</label>
                <input type="date" 
                       class="form-control" 
                       id="end_date" 
                       name="end_date" 
                       value="{{ $endDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.export', ['type' => 'sales', 'start_date' => $startDate, 'end_date' => $endDate]) }}">
                                    <i class="fas fa-chart-line"></i> Relatório de Vendas
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.export', ['type' => 'products', 'start_date' => $startDate, 'end_date' => $endDate]) }}">
                                    <i class="fas fa-box"></i> Relatório de Produtos
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.export', ['type' => 'customers', 'start_date' => $startDate, 'end_date' => $endDate]) }}">
                                    <i class="fas fa-users"></i> Relatório de Clientes
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Sales Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total de Pedidos</h5>
                        <h3>{{ $salesStats['total_orders'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-shopping-cart fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Receita Total</h5>
                        <h3>R$ {{ format_currency($salesStats['total_revenue']) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-dollar-sign fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Ticket Médio</h5>
                        <h3>R$ {{ format_currency($salesStats['average_order']) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-receipt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Novos Clientes</h5>
                        <h3>{{ $customerStats['new_customers'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-plus fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Status Distribution -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <h5 class="text-warning">{{ $salesStats['pending_orders'] }}</h5>
                <small>Pendentes</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <h5 class="text-info">{{ $salesStats['confirmed_orders'] }}</h5>
                <small>Confirmados</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <h5 class="text-success">{{ $salesStats['delivered_orders'] }}</h5>
                <small>Entregues</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <h5 class="text-danger">{{ $salesStats['cancelled_orders'] }}</h5>
                <small>Cancelados</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Daily Sales Chart -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Vendas por Dia</h5>
            </div>
            <div class="card-body">
                <canvas id="dailySalesChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Category Sales -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Vendas por Categoria</h5>
            </div>
            <div class="card-body">
                <canvas id="categorySalesChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Best Selling Products -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Produtos Mais Vendidos</h5>
            </div>
            <div class="card-body">
                @if($productStats['best_selling']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Vendidos</th>
                                    <th>Receita</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productStats['best_selling'] as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->total_sold }}</td>
                                    <td>R$ {{ format_currency($product->total_revenue) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-3">Nenhum produto vendido no período.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Top Customers -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Melhores Clientes</h5>
            </div>
            <div class="card-body">
                @if($customerStats['top_customers']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Pedidos</th>
                                    <th>Total Gasto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customerStats['top_customers'] as $customer)
                                <tr>
                                    <td>{{ $customer['name'] }}</td>
                                    <td>{{ $customer['total_orders'] }}</td>
                                    <td>R$ {{ format_currency($customer['total_spent']) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-3">Nenhum cliente encontrado no período.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Product Statistics -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <h4 class="text-primary">{{ $productStats['total_products'] }}</h4>
                <small>Total de Produtos</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <h4 class="text-success">{{ $productStats['active_products'] }}</h4>
                <small>Produtos Ativos</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <h4 class="text-warning">{{ $productStats['low_stock_products'] }}</h4>
                <small>Estoque Baixo</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <h4 class="text-danger">{{ $productStats['out_of_stock_products'] }}</h4>
                <small>Sem Estoque</small>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Daily Sales Chart
    const dailySalesCtx = document.getElementById('dailySalesChart').getContext('2d');
    const dailySalesData = @json($chartsData['daily_sales']);
    
    new Chart(dailySalesCtx, {
        type: 'line',
        data: {
            labels: dailySalesData.map(item => new Date(item.date).toLocaleDateString('pt-BR')),
            datasets: [{
                label: 'Receita (R$)',
                data: dailySalesData.map(item => parseFloat(item.revenue)),
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.1,
                yAxisID: 'y'
            }, {
                label: 'Pedidos',
                data: dailySalesData.map(item => parseInt(item.orders)),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Receita (R$)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Número de Pedidos'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });

    // Category Sales Chart
    const categorySalesCtx = document.getElementById('categorySalesChart').getContext('2d');
    const categorySalesData = @json($chartsData['category_sales']);
    
    new Chart(categorySalesCtx, {
        type: 'doughnut',
        data: {
            labels: categorySalesData.map(item => item.name),
            datasets: [{
                data: categorySalesData.map(item => parseFloat(item.total)),
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endsection
