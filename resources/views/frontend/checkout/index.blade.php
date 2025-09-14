@extends('layouts.frontend')

@section('title', 'Finalizar Compra - ' . config('app.name'))

@section('content')
<!-- Breadcrumb -->
<div class="bg-light py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Início</a></li>
                <li class="breadcrumb-item"><a href="{{ route('cart.index') }}">Carrinho</a></li>
                <li class="breadcrumb-item active">Finalizar Compra</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <!-- Progress Steps -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="progress-steps">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="step active">
                            <div class="step-circle">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <p class="step-label">Carrinho</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="step active">
                            <div class="step-circle">
                                <i class="fas fa-truck"></i>
                            </div>
                            <p class="step-label">Entrega</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="step active">
                            <div class="step-circle">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <p class="step-label">Pagamento</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="step">
                            <div class="step-circle">
                                <i class="fas fa-check"></i>
                            </div>
                            <p class="step-label">Confirmação</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
        @csrf
        <div class="row">
            <!-- Checkout Form -->
            <div class="col-lg-8">
                <!-- Shipping Address -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marker-alt"></i> Endereço de Entrega
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nome Completo *</label>
                                <input type="text" name="shipping_name" class="form-control" 
                                       value="{{ old('shipping_name', auth()->user()->name) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CPF *</label>
                                <input type="text" name="shipping_cpf" class="form-control" 
                                       value="{{ old('shipping_cpf') }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">CEP *</label>
                                <input type="text" name="shipping_zipcode" class="form-control" 
                                       value="{{ old('shipping_zipcode') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Endereço *</label>
                                <input type="text" name="shipping_address" class="form-control" 
                                       value="{{ old('shipping_address') }}" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Número *</label>
                                <input type="text" name="shipping_number" class="form-control" 
                                       value="{{ old('shipping_number') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Complemento</label>
                                <input type="text" name="shipping_complement" class="form-control" 
                                       value="{{ old('shipping_complement') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bairro *</label>
                                <input type="text" name="shipping_district" class="form-control" 
                                       value="{{ old('shipping_district') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cidade *</label>
                                <input type="text" name="shipping_city" class="form-control" 
                                       value="{{ old('shipping_city') }}" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Estado *</label>
                                <select name="shipping_state" class="form-select" required>
                                    <option value="">Selecione</option>
                                    <option value="AC" {{ old('shipping_state') === 'AC' ? 'selected' : '' }}>AC</option>
                                    <option value="AL" {{ old('shipping_state') === 'AL' ? 'selected' : '' }}>AL</option>
                                    <option value="AP" {{ old('shipping_state') === 'AP' ? 'selected' : '' }}>AP</option>
                                    <option value="AM" {{ old('shipping_state') === 'AM' ? 'selected' : '' }}>AM</option>
                                    <option value="BA" {{ old('shipping_state') === 'BA' ? 'selected' : '' }}>BA</option>
                                    <option value="CE" {{ old('shipping_state') === 'CE' ? 'selected' : '' }}>CE</option>
                                    <option value="DF" {{ old('shipping_state') === 'DF' ? 'selected' : '' }}>DF</option>
                                    <option value="ES" {{ old('shipping_state') === 'ES' ? 'selected' : '' }}>ES</option>
                                    <option value="GO" {{ old('shipping_state') === 'GO' ? 'selected' : '' }}>GO</option>
                                    <option value="MA" {{ old('shipping_state') === 'MA' ? 'selected' : '' }}>MA</option>
                                    <option value="MT" {{ old('shipping_state') === 'MT' ? 'selected' : '' }}>MT</option>
                                    <option value="MS" {{ old('shipping_state') === 'MS' ? 'selected' : '' }}>MS</option>
                                    <option value="MG" {{ old('shipping_state') === 'MG' ? 'selected' : '' }}>MG</option>
                                    <option value="PA" {{ old('shipping_state') === 'PA' ? 'selected' : '' }}>PA</option>
                                    <option value="PB" {{ old('shipping_state') === 'PB' ? 'selected' : '' }}>PB</option>
                                    <option value="PR" {{ old('shipping_state') === 'PR' ? 'selected' : '' }}>PR</option>
                                    <option value="PE" {{ old('shipping_state') === 'PE' ? 'selected' : '' }}>PE</option>
                                    <option value="PI" {{ old('shipping_state') === 'PI' ? 'selected' : '' }}>PI</option>
                                    <option value="RJ" {{ old('shipping_state') === 'RJ' ? 'selected' : '' }}>RJ</option>
                                    <option value="RN" {{ old('shipping_state') === 'RN' ? 'selected' : '' }}>RN</option>
                                    <option value="RS" {{ old('shipping_state') === 'RS' ? 'selected' : '' }}>RS</option>
                                    <option value="RO" {{ old('shipping_state') === 'RO' ? 'selected' : '' }}>RO</option>
                                    <option value="RR" {{ old('shipping_state') === 'RR' ? 'selected' : '' }}>RR</option>
                                    <option value="SC" {{ old('shipping_state') === 'SC' ? 'selected' : '' }}>SC</option>
                                    <option value="SP" {{ old('shipping_state') === 'SP' ? 'selected' : '' }}>SP</option>
                                    <option value="SE" {{ old('shipping_state') === 'SE' ? 'selected' : '' }}>SE</option>
                                    <option value="TO" {{ old('shipping_state') === 'TO' ? 'selected' : '' }}>TO</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Telefone *</label>
                                <input type="text" name="shipping_phone" class="form-control" 
                                       value="{{ old('shipping_phone') }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-credit-card"></i> Forma de Pagamento
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="credit_card" value="credit_card" checked>
                                    <label class="form-check-label" for="credit_card">
                                        <i class="fas fa-credit-card"></i> Cartão de Crédito
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="pix" value="pix">
                                    <label class="form-check-label" for="pix">
                                        <i class="fas fa-qrcode"></i> PIX (5% de desconto)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="bank_slip" value="bank_slip">
                                    <label class="form-check-label" for="bank_slip">
                                        <i class="fas fa-barcode"></i> Boleto Bancário (3% de desconto)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Credit Card Form -->
                        <div id="credit_card_form" class="payment-form">
                            <hr>
                            <h6>Dados do Cartão</h6>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Nome no Cartão *</label>
                                    <input type="text" name="card_name" class="form-control" 
                                           value="{{ old('card_name') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Número do Cartão *</label>
                                    <input type="text" name="card_number" class="form-control" 
                                           placeholder="0000 0000 0000 0000" value="{{ old('card_number') }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Validade *</label>
                                    <input type="text" name="card_expiry" class="form-control" 
                                           placeholder="MM/AA" value="{{ old('card_expiry') }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">CVV *</label>
                                    <input type="text" name="card_cvv" class="form-control" 
                                           placeholder="000" value="{{ old('card_cvv') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Parcelas *</label>
                                    <select name="installments" class="form-select">
                                        <option value="1">1x R$ {{ format_currency($cartTotal) }} sem juros</option>
                                        <option value="2">2x R$ {{ format_currency($cartTotal / 2) }} sem juros</option>
                                        <option value="3">3x R$ {{ format_currency($cartTotal / 3) }} sem juros</option>
                                        <option value="6">6x R$ {{ format_currency($cartTotal / 6) }} sem juros</option>
                                        <option value="12">12x R$ {{ format_currency($cartTotal / 12) }} sem juros</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- PIX Form -->
                        <div id="pix_form" class="payment-form" style="display: none;">
                            <hr>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>PIX:</strong> Após confirmar o pedido, você receberá um código PIX para pagamento.
                                O desconto de 5% será aplicado automaticamente.
                            </div>
                        </div>

                        <!-- Bank Slip Form -->
                        <div id="bank_slip_form" class="payment-form" style="display: none;">
                            <hr>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Boleto:</strong> Após confirmar o pedido, você receberá um boleto para pagamento.
                                O desconto de 3% será aplicado automaticamente.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-comment"></i> Observações do Pedido
                        </h5>
                    </div>
                    <div class="card-body">
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Observações especiais sobre seu pedido (opcional)">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 2rem;">
                    <div class="card-header">
                        <h5 class="mb-0">Resumo do Pedido</h5>
                    </div>
                    <div class="card-body">
                        <!-- Products -->
                        <div class="mb-3">
                            @foreach($cartItems as $item)
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <span class="small">{{ Str::limit($item->product->name, 25) }}</span>
                                        <br>
                                        <small class="text-muted">Qtd: {{ $item->quantity }}</small>
                                    </div>
                                    <span class="small">
                                        R$ {{ format_currency($item->product->effective_price * $item->quantity) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Totals -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Subtotal:</span>
                                <span id="subtotal">R$ {{ format_currency($cartTotal) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Frete:</span>
                                <span class="text-success">Grátis</span>
                            </div>
                            <div class="d-flex justify-content-between" id="discount-row" style="display: none;">
                                <span>Desconto:</span>
                                <span class="text-success" id="discount-amount">R$ 0,00</span>
                            </div>
                        </div>

                        <hr>

                                                <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong class="text-primary h5" id="total">R$ {{ format_currency($cartTotal) }}</strong>
                        </div>

                        <!-- Place Order Button -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check"></i> Finalizar Pedido
                            </button>
                        </div>

                        <!-- Security Info -->
                        <div class="text-center">
                            <small class="text-muted">
                                <i class="fas fa-lock"></i> 
                                Seus dados estão seguros
                            </small>
                        </div>

                        <!-- Terms -->
                        <div class="mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label small" for="terms">
                                    Aceito os <a href="#" target="_blank">termos e condições</a>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
.progress-steps .step {
    position: relative;
    margin-bottom: 1rem;
}

.progress-steps .step-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.5rem;
    font-size: 1.2rem;
}

.progress-steps .step.active .step-circle {
    background: #007bff;
    color: white;
}

.progress-steps .step-label {
    font-size: 0.9rem;
    color: #6c757d;
    margin: 0;
}

.progress-steps .step.active .step-label {
    color: #007bff;
    font-weight: 500;
}

@media (min-width: 768px) {
    .progress-steps .step::after {
        content: '';
        position: absolute;
        top: 25px;
        left: 75%;
        width: 50%;
        height: 2px;
        background: #e9ecef;
    }
    
    .progress-steps .step.active::after {
        background: #007bff;
    }
    
    .progress-steps .step:last-child::after {
        display: none;
    }
}

.payment-form {
    transition: all 0.3s ease;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const paymentForms = document.querySelectorAll('.payment-form');
    const cartTotal = {{ $cartTotal }};
    
    // Payment method change handler
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            // Hide all payment forms
            paymentForms.forEach(form => {
                form.style.display = 'none';
            });
            
            // Show selected payment form
            const selectedForm = document.getElementById(this.value + '_form');
            if (selectedForm) {
                selectedForm.style.display = 'block';
            }
            
            // Update totals based on payment method
            updateTotals(this.value);
        });
    });
    
    function updateTotals(paymentMethod) {
        const discountRow = document.getElementById('discount-row');
        const discountAmount = document.getElementById('discount-amount');
        const totalElement = document.getElementById('total');
        
        let discount = 0;
        let finalTotal = cartTotal;
        
        if (paymentMethod === 'pix') {
            discount = cartTotal * 0.05; // 5% discount
            finalTotal = cartTotal - discount;
            discountRow.style.display = 'flex';
            discountAmount.textContent = 'R$ ' + discount.toLocaleString('pt-BR', {minimumFractionDigits: 2});
        } else if (paymentMethod === 'bank_slip') {
            discount = cartTotal * 0.03; // 3% discount
            finalTotal = cartTotal - discount;
            discountRow.style.display = 'flex';
            discountAmount.textContent = 'R$ ' + discount.toLocaleString('pt-BR', {minimumFractionDigits: 2});
        } else {
            discountRow.style.display = 'none';
        }
        
        totalElement.textContent = 'R$ ' + finalTotal.toLocaleString('pt-BR', {minimumFractionDigits: 2});
    }
    
    // Form validation
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
        if (paymentMethod === 'credit_card') {
            const requiredFields = ['card_name', 'card_number', 'card_expiry', 'card_cvv'];
            let isValid = true;
            
            requiredFields.forEach(fieldName => {
                const field = document.querySelector(`input[name="${fieldName}"]`);
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor, preencha todos os dados do cartão.');
            }
        }
    });
});
</script>
@endpush
