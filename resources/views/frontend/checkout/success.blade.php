@extends('layouts.frontend')

@section('title', 'Pedido Confirmado - ' . config('app.name'))

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Message -->
            <div class="text-center mb-5">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                </div>
                <h1 class="text-success mb-3">Pedido Confirmado!</h1>
                <p class="lead text-muted">
                    Obrigado por sua compra! Seu pedido foi recebido e está sendo processado.
                </p>
                <div class="alert alert-success">
                    <strong>Número do Pedido:</strong> #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                </div>
            </div>

            <!-- Order Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-bag"></i> Detalhes do Pedido
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Data do Pedido:</strong><br>
                            {{ $order->created_at->format('d/m/Y H:i') }}</p>
                            
                            <p><strong>Status:</strong><br>
                            <span class="badge bg-warning">{{ format_status($order->status) }}</span></p>
                            
                            <p><strong>Forma de Pagamento:</strong><br>
                            @switch($order->payment_method)
                                @case('credit_card')
                                    <i class="fas fa-credit-card"></i> Cartão de Crédito
                                    @break
                                @case('pix')
                                    <i class="fas fa-qrcode"></i> PIX
                                    @break
                                @case('bank_slip')
                                    <i class="fas fa-barcode"></i> Boleto Bancário
                                    @break
                                @default
                                    {{ $order->payment_method }}
                            @endswitch
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status do Pagamento:</strong><br>
                            <span class="badge bg-warning">{{ format_status($order->payment_status) }}</span></p>
                            
                            @if($order->discount > 0)
                                <p><strong>Desconto Aplicado:</strong><br>
                                <span class="text-success">R$ {{ format_currency($order->discount) }}</span></p>
                            @endif
                            
                            <p><strong>Total:</strong><br>
                            <span class="h5 text-primary">R$ {{ format_currency($order->total) }}</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-box"></i> Produtos
                    </h5>
                </div>
                <div class="card-body p-0">
                    @foreach($order->orderItems as $item)
                        <div class="border-bottom p-3">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="mb-1">{{ $item->product->name }}</h6>
                                    <p class="text-muted small mb-0">
                                        SKU: {{ $item->product->sku }} | 
                                        Categoria: {{ $item->product->category->name }}
                                    </p>
                                </div>
                                <div class="col-md-2 text-center">
                                    <span class="small">Qtd: {{ $item->quantity }}</span>
                                </div>
                                <div class="col-md-2 text-center">
                                    <span class="small">R$ {{ format_currency($item->price) }}</span>
                                </div>
                                <div class="col-md-2 text-end">
                                    <span class="fw-bold">R$ {{ format_currency($item->total) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Shipping Address -->
            @if($order->shipping_address)
                @php
                    $shipping = json_decode($order->shipping_address, true);
                @endphp
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marker-alt"></i> Endereço de Entrega
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>{{ $shipping['name'] ?? '' }}</strong></p>
                        <p class="mb-1">{{ $shipping['address'] ?? '' }}, {{ $shipping['number'] ?? '' }}</p>
                        @if(isset($shipping['complement']) && $shipping['complement'])
                            <p class="mb-1">{{ $shipping['complement'] }}</p>
                        @endif
                        <p class="mb-1">{{ $shipping['district'] ?? '' }} - {{ $shipping['city'] ?? '' }}/{{ $shipping['state'] ?? '' }}</p>
                        <p class="mb-1">CEP: {{ $shipping['zipcode'] ?? '' }}</p>
                        <p class="mb-0">Telefone: {{ $shipping['phone'] ?? '' }}</p>
                    </div>
                </div>
            @endif

            <!-- Payment Instructions -->
            @if($order->payment_method === 'pix')
                <div class="card mb-4">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0 text-dark">
                            <i class="fas fa-qrcode"></i> Instruções de Pagamento - PIX
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <p class="mb-2"><strong>Para finalizar sua compra:</strong></p>
                            <ol class="mb-0">
                                <li>Abra o app do seu banco</li>
                                <li>Escolha a opção PIX</li>
                                <li>Escaneie o QR Code ou copie e cole o código PIX</li>
                                <li>Confirme o pagamento</li>
                            </ol>
                        </div>
                        <div class="text-center">
                            <div class="bg-light p-4 rounded mb-3">
                                <i class="fas fa-qrcode fa-5x text-muted"></i>
                                <p class="mt-2 mb-0">QR Code PIX</p>
                            </div>
                            <p class="small text-muted">
                                Código PIX: <strong>{{ Str::random(32) }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            @elseif($order->payment_method === 'bank_slip')
                <div class="card mb-4">
                    <div class="card-header bg-info">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-barcode"></i> Instruções de Pagamento - Boleto
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <p class="mb-2"><strong>Para finalizar sua compra:</strong></p>
                            <ol class="mb-0">
                                <li>Clique no botão abaixo para baixar o boleto</li>
                                <li>Imprima o boleto ou pague pelo internet banking</li>
                                <li>O pagamento pode levar até 2 dias úteis para ser processado</li>
                            </ol>
                        </div>
                        <div class="text-center">
                            <button class="btn btn-primary btn-lg">
                                <i class="fas fa-download"></i> Baixar Boleto
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Notes -->
            @if($order->notes)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-comment"></i> Observações
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $order->notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="text-center">
                <a href="{{ route('home') }}" class="btn btn-outline-primary me-3">
                    <i class="fas fa-home"></i> Voltar ao Início
                </a>
                <a href="{{ route('products.index') }}" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Continuar Comprando
                </a>
            </div>

            <!-- Contact Info -->
            <div class="text-center mt-5">
                <hr>
                <p class="text-muted">
                    <i class="fas fa-envelope"></i> 
                    Dúvidas? Entre em contato: 
                    <a href="mailto:contato@loja.com">contato@loja.com</a> | 
                    <i class="fas fa-phone"></i> (11) 99999-9999
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
