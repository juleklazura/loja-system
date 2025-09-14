@extends('layouts.admin')

@section('title', 'Configurações')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Configurações do Sistema</h1>
            <p class="text-muted">Gerencie as configurações e preferências do sistema</p>
        </div>
        <div class="col-md-4 text-end">
            <button type="button" class="btn btn-outline-info me-2" onclick="getSystemInfo()">
                <i class="fas fa-info-circle"></i> Info do Sistema
            </button>
            <button type="button" class="btn btn-outline-warning me-2" onclick="clearCache()">
                <i class="fas fa-broom"></i> Limpar Cache
            </button>
            <button type="button" class="btn btn-outline-success" onclick="backupDatabase()">
                <i class="fas fa-download"></i> Backup
            </button>
        </div>
    </div>

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <!-- General Settings -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Configurações Gerais</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="site_name" class="form-label">Nome do Site <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('site_name') is-invalid @enderror" 
                               id="site_name" 
                               name="site_name" 
                               value="{{ old('site_name', $settings['site_name']) }}" 
                               required>
                        @error('site_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="contact_email" class="form-label">Email de Contato <span class="text-danger">*</span></label>
                        <input type="email" 
                               class="form-control @error('contact_email') is-invalid @enderror" 
                               id="contact_email" 
                               name="contact_email" 
                               value="{{ old('contact_email', $settings['contact_email']) }}" 
                               required>
                        @error('contact_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="site_description" class="form-label">Descrição do Site</label>
                <textarea class="form-control @error('site_description') is-invalid @enderror" 
                          id="site_description" 
                          name="site_description" 
                          rows="3">{{ old('site_description', $settings['site_description']) }}</textarea>
                @error('site_description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="contact_phone" class="form-label">Telefone de Contato</label>
                        <input type="text" 
                               class="form-control @error('contact_phone') is-invalid @enderror" 
                               id="contact_phone" 
                               name="contact_phone" 
                               value="{{ old('contact_phone', $settings['contact_phone']) }}">
                        @error('contact_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="whatsapp_number" class="form-label">WhatsApp</label>
                        <input type="text" 
                               class="form-control @error('whatsapp_number') is-invalid @enderror" 
                               id="whatsapp_number" 
                               name="whatsapp_number" 
                               value="{{ old('whatsapp_number', $settings['whatsapp_number']) }}" 
                               placeholder="5511999999999">
                        @error('whatsapp_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Endereço</label>
                <textarea class="form-control @error('address') is-invalid @enderror" 
                          id="address" 
                          name="address" 
                          rows="2">{{ old('address', $settings['address']) }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <!-- Media Settings -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Mídia e Branding</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="logo" class="form-label">Logo</label>
                        <input type="file" 
                               class="form-control @error('logo') is-invalid @enderror" 
                               id="logo" 
                               name="logo" 
                               accept="image/*">
                        @if(isset($settings['logo']))
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $settings['logo']) }}" 
                                     alt="Logo atual" 
                                     class="img-thumbnail" 
                                     style="max-height: 100px;">
                            </div>
                        @endif
                        @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="favicon" class="form-label">Favicon</label>
                        <input type="file" 
                               class="form-control @error('favicon') is-invalid @enderror" 
                               id="favicon" 
                               name="favicon" 
                               accept=".ico,.png">
                        @if(isset($settings['favicon']))
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $settings['favicon']) }}" 
                                     alt="Favicon atual" 
                                     class="img-thumbnail" 
                                     style="max-height: 32px;">
                            </div>
                        @endif
                        @error('favicon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Settings -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Configurações Financeiras</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="currency" class="form-label">Moeda <span class="text-danger">*</span></label>
                        <select class="form-select @error('currency') is-invalid @enderror" 
                                id="currency" 
                                name="currency" 
                                required>
                            <option value="BRL" {{ old('currency', $settings['currency']) === 'BRL' ? 'selected' : '' }}>Real (BRL)</option>
                            <option value="USD" {{ old('currency', $settings['currency']) === 'USD' ? 'selected' : '' }}>Dólar (USD)</option>
                            <option value="EUR" {{ old('currency', $settings['currency']) === 'EUR' ? 'selected' : '' }}>Euro (EUR)</option>
                        </select>
                        @error('currency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="tax_rate" class="form-label">Taxa de Imposto (%)</label>
                        <input type="number" 
                               class="form-control @error('tax_rate') is-invalid @enderror" 
                               id="tax_rate" 
                               name="tax_rate" 
                               value="{{ old('tax_rate', $settings['tax_rate']) }}" 
                               min="0" 
                               max="100" 
                               step="0.01">
                        @error('tax_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="shipping_fee" class="form-label">Taxa de Entrega (R$)</label>
                        <input type="number" 
                               class="form-control @error('shipping_fee') is-invalid @enderror" 
                               id="shipping_fee" 
                               name="shipping_fee" 
                               value="{{ old('shipping_fee', $settings['shipping_fee']) }}" 
                               min="0" 
                               step="0.01">
                        @error('shipping_fee')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="free_shipping_min" class="form-label">Frete Grátis a partir de (R$)</label>
                        <input type="number" 
                               class="form-control @error('free_shipping_min') is-invalid @enderror" 
                               id="free_shipping_min" 
                               name="free_shipping_min" 
                               value="{{ old('free_shipping_min', $settings['free_shipping_min']) }}" 
                               min="0" 
                               step="0.01">
                        @error('free_shipping_min')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Métodos de Pagamento</label>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="payment_credit_card" 
                                   name="payment_methods[]" 
                                   value="credit_card"
                                   {{ in_array('credit_card', $settings['payment_methods']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="payment_credit_card">
                                Cartão de Crédito
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="payment_debit_card" 
                                   name="payment_methods[]" 
                                   value="debit_card"
                                   {{ in_array('debit_card', $settings['payment_methods']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="payment_debit_card">
                                Cartão de Débito
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="payment_pix" 
                                   name="payment_methods[]" 
                                   value="pix"
                                   {{ in_array('pix', $settings['payment_methods']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="payment_pix">
                                PIX
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="payment_bank_slip" 
                                   name="payment_methods[]" 
                                   value="bank_slip"
                                   {{ in_array('bank_slip', $settings['payment_methods']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="payment_bank_slip">
                                Boleto Bancário
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Settings -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Configurações do Sistema</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="order_status_emails" 
                               name="order_status_emails" 
                               value="1"
                               {{ old('order_status_emails', $settings['order_status_emails']) ? 'checked' : '' }}>
                        <label class="form-check-label" for="order_status_emails">
                            Emails de Status de Pedido
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="stock_alerts" 
                               name="stock_alerts" 
                               value="1"
                               {{ old('stock_alerts', $settings['stock_alerts']) ? 'checked' : '' }}>
                        <label class="form-check-label" for="stock_alerts">
                            Alertas de Estoque
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="maintenance_mode" 
                               name="maintenance_mode" 
                               value="1"
                               {{ old('maintenance_mode', $settings['maintenance_mode']) ? 'checked' : '' }}>
                        <label class="form-check-label" for="maintenance_mode">
                            Modo de Manutenção
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Social Media -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Redes Sociais</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="facebook_url" class="form-label">Facebook URL</label>
                        <input type="url" 
                               class="form-control @error('facebook_url') is-invalid @enderror" 
                               id="facebook_url" 
                               name="facebook_url" 
                               value="{{ old('facebook_url', $settings['facebook_url']) }}">
                        @error('facebook_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="instagram_url" class="form-label">Instagram URL</label>
                        <input type="url" 
                               class="form-control @error('instagram_url') is-invalid @enderror" 
                               id="instagram_url" 
                               name="instagram_url" 
                               value="{{ old('instagram_url', $settings['instagram_url']) }}">
                        @error('instagram_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="twitter_url" class="form-label">Twitter URL</label>
                <input type="url" 
                       class="form-control @error('twitter_url') is-invalid @enderror" 
                       id="twitter_url" 
                       name="twitter_url" 
                       value="{{ old('twitter_url', $settings['twitter_url']) }}">
                @error('twitter_url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <!-- Analytics -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Analytics e Tracking</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="analytics_code" class="form-label">Google Analytics Code</label>
                <textarea class="form-control @error('analytics_code') is-invalid @enderror" 
                          id="analytics_code" 
                          name="analytics_code" 
                          rows="3" 
                          placeholder="<!-- Google Analytics code here -->">{{ old('analytics_code', $settings['analytics_code']) }}</textarea>
                @error('analytics_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <!-- Email Test -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Teste de Email</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <input type="email" 
                           class="form-control" 
                           id="test_email" 
                           placeholder="Digite um email para teste">
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-info" onclick="testEmail()">
                        <i class="fas fa-envelope"></i> Enviar Teste
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Button -->
    <div class="text-center mb-4">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-save"></i> Salvar Configurações
        </button>
    </div>
</form>

<!-- System Info Modal -->
<div class="modal fade" id="systemInfoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Informações do Sistema</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="systemInfoContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function getSystemInfo() {
        $('#systemInfoModal').modal('show');
        
        fetch('{{ route("admin.settings.systemInfo") }}')
            .then(response => response.json())
            .then(data => {
                const content = `
                    <table class="table table-striped">
                        <tr><td><strong>Versão PHP:</strong></td><td>${data.php_version}</td></tr>
                        <tr><td><strong>Versão Laravel:</strong></td><td>${data.laravel_version}</td></tr>
                        <tr><td><strong>Servidor:</strong></td><td>${data.server_software}</td></tr>
                        <tr><td><strong>Tamanho do Banco:</strong></td><td>${data.database_size}</td></tr>
                        <tr><td><strong>Armazenamento Usado:</strong></td><td>${data.storage_used}</td></tr>
                        <tr><td><strong>Limite de Memória:</strong></td><td>${data.memory_limit}</td></tr>
                        <tr><td><strong>Upload Máximo:</strong></td><td>${data.max_upload_size}</td></tr>
                        <tr><td><strong>Fuso Horário:</strong></td><td>${data.timezone}</td></tr>
                    </table>
                `;
                document.getElementById('systemInfoContent').innerHTML = content;
            })
            .catch(error => {
                document.getElementById('systemInfoContent').innerHTML = 
                    '<div class="alert alert-danger">Erro ao carregar informações do sistema.</div>';
            });
    }

    function clearCache() {
        if (confirm('Tem certeza que deseja limpar o cache do sistema?')) {
            fetch('{{ route("admin.settings.clearCache") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Cache limpo com sucesso!');
                } else {
                    alert('Erro ao limpar cache.');
                }
            })
            .catch(() => {
                // Fallback: submit form normally
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("admin.settings.clearCache") }}';
                form.innerHTML = '@csrf';
                document.body.appendChild(form);
                form.submit();
            });
        }
    }

    function backupDatabase() {
        if (confirm('Fazer backup do banco de dados? O download será iniciado automaticamente.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.settings.backup") }}';
            form.innerHTML = '@csrf';
            document.body.appendChild(form);
            form.submit();
        }
    }

    function testEmail() {
        const email = document.getElementById('test_email').value;
        if (!email) {
            alert('Digite um email para teste.');
            return;
        }

        fetch('{{ route("admin.settings.testEmail") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ test_email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Email de teste enviado com sucesso!');
            } else {
                alert('Erro ao enviar email de teste.');
            }
        })
        .catch(() => {
            alert('Erro ao enviar email de teste.');
        });
    }
</script>
@endsection
