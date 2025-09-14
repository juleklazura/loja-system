# Sistema de Logs para Debugging e Auditoria

## 📝 Visão Geral
Sistema completo de logging implementado para rastreamento, auditoria e debugging da aplicação Laravel, com canais específicos e ferramentas de visualização.

## 📁 Estrutura dos Logs

### 🔧 **Canais de Log Configurados**

#### **📋 Logs de Auditoria**
- **`audit.log`**: Ações dos usuários (90 dias)
- **`user_actions.log`**: Ações específicas dos usuários (60 dias)
- **`security.log`**: Eventos de segurança (180 dias)

#### **🛒 Logs de Negócio**
- **`cart.log`**: Operações do carrinho (14 dias)
- **`products.log`**: Ações em produtos (14 dias)

#### **⚡ Logs de Performance**
- **`performance.log`**: Métricas de performance (7 dias)
- **`database.log`**: Queries do banco (7 dias)

#### **🔌 Logs de API**
- **`api.log`**: Requisições de API (30 dias)

#### **🔐 Logs de Autenticação**
- **`auth.log`**: Login/logout e eventos auth (30 dias)

## 🎯 **AuditService - Funcionalidades**

### **👤 Ações do Usuário**
```php
use App\Services\AuditService;

// Log de ação genérica
AuditService::logUserAction('product.view', 'Usuário visualizou produto', [
    'product_id' => 123,
    'product_name' => 'iPhone 14'
]);

// Log de ação no carrinho
AuditService::logCartAction('add', [
    'product_id' => 123,
    'quantity' => 2,
    'total_value' => 2398.00
]);

// Log de ação em produto
AuditService::logProductAction('purchase', [
    'product_id' => 123,
    'buyer_id' => 456
]);
```

### **🔐 Eventos de Segurança**
```php
// Login suspeito
AuditService::logSecurityEvent('suspicious_login', 'Múltiplas tentativas de login', [
    'attempts' => 5,
    'blocked_ip' => '192.168.1.100'
]);

// Acesso negado
AuditService::logSecurityEvent('access_denied', 'Tentativa de acesso não autorizado', [
    'resource' => '/admin/users',
    'user_role' => 'customer'
]);
```

### **⚡ Performance**
```php
// Requisição lenta
$startTime = microtime(true);
// ... código ...
$executionTime = (microtime(true) - $startTime) * 1000;

AuditService::logPerformance('slow_query', $executionTime, [
    'query_type' => 'user_orders',
    'threshold' => 1000
]);
```

### **🐛 Erros**
```php
try {
    // código que pode falhar
} catch (\Exception $e) {
    AuditService::logError($e, 'payment_processing', [
        'order_id' => 123,
        'payment_method' => 'credit_card'
    ]);
}
```

## 🖥️ **Middleware de Logging**

### **RequestLoggingMiddleware**
Registra automaticamente todas as requisições:

```php
// Aplicar a rotas específicas
Route::middleware('log.requests')->group(function () {
    Route::get('/api/products', [ProductController::class, 'index']);
});

// Ou globalmente no bootstrap/app.php
$middleware->append(\App\Http\Middleware\RequestLoggingMiddleware::class);
```

#### **Dados Registrados:**
- **Requisição**: URL, método, IP, user-agent, parâmetros
- **Resposta**: Status code, tempo de execução, uso de memória
- **Segurança**: Headers e parâmetros sensíveis são mascarados

## 🔍 **Log Viewer Admin**

### **Funcionalidades:**
- ✅ **Lista todos os logs** disponíveis
- ✅ **Visualização em tempo real** com filtros
- ✅ **Busca por palavra-chave** e nível
- ✅ **Download de arquivos** de log
- ✅ **Limpeza e exclusão** de logs
- ✅ **Estatísticas detalhadas** por arquivo

### **Rotas Disponíveis:**
```php
// Lista de logs
GET /admin/logs

// Visualizar log específico
GET /admin/logs/{filename}

// Estatísticas
GET /admin/logs/stats

// Download
GET /admin/logs/{filename}/download

// Limpar log
POST /admin/logs/{filename}/clear

// Excluir log
DELETE /admin/logs/{filename}

// Busca AJAX
GET /admin/logs/search
```

### **Filtros Disponíveis:**
- **Nível**: ERROR, WARNING, INFO, DEBUG
- **Busca**: Palavra-chave no conteúdo
- **Linhas**: Número de linhas a exibir
- **Arquivo**: Log específico

## 📊 **Formato dos Logs**

### **Estrutura Padrão:**
```json
{
  "timestamp": "2025-09-06T10:30:00Z",
  "level": "INFO",
  "message": "Ação no carrinho: add",
  "context": {
    "action": "cart.add",
    "user_id": 123,
    "user_email": "user@example.com",
    "ip_address": "192.168.1.100",
    "user_agent": "Mozilla/5.0...",
    "url": "/carrinho/adicionar",
    "method": "POST",
    "data": {
      "product_id": 456,
      "quantity": 2,
      "total_value": 199.90
    },
    "session_id": "abc123..."
  }
}
```

### **Logs de Performance:**
```json
{
  "operation": "cart.index",
  "execution_time": 156.32,
  "memory_usage": 2048576,
  "peak_memory": 4194304,
  "user_id": 123,
  "url": "/carrinho",
  "method": "GET"
}
```

## 🚀 **Implementação em Controllers**

### **Exemplo Completo:**
```php
<?php

namespace App\Http\Controllers\Frontend;

use App\Services\AuditService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $startTime = microtime(true);
        
        try {
            // Processar pedido
            $order = $this->orderService->create($request->validated());
            
            // Log de auditoria
            AuditService::logUserAction('order.create', 'Pedido criado', [
                'order_id' => $order->id,
                'total_value' => $order->total,
                'items_count' => $order->items->count()
            ]);
            
            // Log de performance
            $executionTime = (microtime(true) - $startTime) * 1000;
            AuditService::logPerformance('order.create', $executionTime);
            
            return response()->json(['success' => true, 'order' => $order]);
            
        } catch (\Exception $e) {
            AuditService::logError($e, 'order.create', [
                'request_data' => $request->validated()
            ]);
            
            return response()->json(['error' => 'Erro ao criar pedido'], 500);
        }
    }
}
```

## 🔧 **Configuração Avançada**

### **Variáveis de Ambiente:**
```env
# Nível de log
LOG_LEVEL=debug

# Canal padrão
LOG_CHANNEL=stack

# Configurações específicas
LOG_AUDIT_DAYS=90
LOG_PERFORMANCE_DAYS=7
LOG_SECURITY_DAYS=180
```

### **Personalização de Canais:**
```php
// config/logging.php
'custom_channel' => [
    'driver' => 'daily',
    'path' => storage_path('logs/custom.log'),
    'level' => 'info',
    'days' => 30,
    'formatter' => \Monolog\Formatter\JsonFormatter::class,
],
```

## 📈 **Métricas e Monitoramento**

### **Estatísticas Automáticas:**
- **Volume de logs** por dia
- **Distribuição por nível** (ERROR, WARNING, INFO, DEBUG)
- **Usuários mais ativos**
- **IPs suspeitos**
- **Performance degradada**

### **Alertas Configuráveis:**
```php
// Service Provider
if ($errorCount > 100) {
    // Enviar notificação para administradores
    Mail::to('admin@loja.com')->send(new HighErrorRateAlert($errorCount));
}
```

## 🛡️ **Segurança dos Logs**

### **Dados Mascarados:**
- ✅ **Senhas** e tokens
- ✅ **Números de cartão**
- ✅ **Headers de autenticação**
- ✅ **Cookies de sessão**

### **Controle de Acesso:**
- ✅ **Apenas administradores** podem ver logs
- ✅ **Logs de auditoria** para ações nos logs
- ✅ **Retenção automática** de arquivos antigos

## 🧪 **Testando o Sistema**

### **Verificar Logs:**
```bash
# Ver logs em tempo real
tail -f storage/logs/audit.log

# Buscar por usuário específico
grep "user_id\":123" storage/logs/audit.log

# Contar erros do dia
grep "ERROR" storage/logs/laravel-$(date +%Y-%m-%d).log | wc -l
```

### **Exemplo de Uso:**
1. **Acesse** `/admin/logs` para ver todos os logs
2. **Clique** em um arquivo para visualizar
3. **Use filtros** para buscar eventos específicos
4. **Baixe logs** para análise offline

O sistema está configurado e pronto para uso! 🎉
