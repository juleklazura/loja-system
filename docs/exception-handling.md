# Sistema de Tratamento de Exceções Específicas

## Visão Geral
Sistema robusto de tratamento de exceções personalizadas para a aplicação Laravel, organizando erros por contexto e fornecendo respostas consistentes.

## 📁 Estrutura das Exceções

### 🛒 **Exceções do Carrinho (`App\Exceptions\Cart`)**

#### `CartException` (Base)
- **Código**: 400
- **Uso**: Classe base para todas as exceções do carrinho
- **Características**: Resposta JSON para APIs, redirecionamento para web

#### `ProductNotAvailableException`
- **Código**: 422 (Unprocessable Entity)
- **Erro**: 1001
- **Uso**: Produto fora de estoque ou inativo
- **Exemplo**:
  ```php
  throw new ProductNotAvailableException('iPhone 14', 0);
  // Produto 'iPhone 14' não está disponível ou tem estoque insuficiente. Estoque disponível: 0
  ```

#### `CartItemNotFoundException`
- **Código**: 404 (Not Found)
- **Erro**: 1002
- **Uso**: Item não encontrado no carrinho
- **Exemplo**:
  ```php
  throw new CartItemNotFoundException(123);
  // Item do carrinho #123 não encontrado
  ```

#### `InvalidQuantityException`
- **Código**: 422 (Unprocessable Entity)
- **Erro**: 1003
- **Uso**: Quantidade inválida no carrinho
- **Exemplo**:
  ```php
  throw new InvalidQuantityException(-1);        // Quantidade deve ser maior que zero
  throw new InvalidQuantityException(150, 99);   // Quantidade máxima permitida: 99
  ```

### 🛍️ **Exceções de Produto (`App\Exceptions\Product`)**

#### `ProductException` (Base)
- **Código**: 400
- **Uso**: Classe base para exceções relacionadas a produtos

#### `ProductNotFoundException`
- **Código**: 404 (Not Found)
- **Erro**: 3001
- **Uso**: Produto não encontrado
- **Exemplo**:
  ```php
  throw new ProductNotFoundException(456);
  // Produto #456 não encontrado
  ```

### 🔐 **Exceções de Autenticação (`App\Exceptions\Auth`)**

#### `AuthException` (Base)
- **Código**: 401
- **Uso**: Classe base para exceções de autenticação

#### `UnauthorizedAccessException`
- **Código**: 403 (Forbidden)
- **Erro**: 2001
- **Uso**: Acesso negado a recursos
- **Exemplo**:
  ```php
  throw new UnauthorizedAccessException('carrinho');
  // Acesso negado ao recurso: carrinho
  ```

## 📊 **Sistema de Logs**

### Canais Específicos
- **`cart`**: `storage/logs/cart.log` (14 dias)
- **`products`**: `storage/logs/products.log` (14 dias)
- **`auth`**: `storage/logs/auth.log` (30 dias)

### Formato dos Logs
```json
{
  "level": "error",
  "message": "Cart Exception: Produto não disponível",
  "context": {
    "exception": "ProductNotAvailableException",
    "user_id": 123,
    "url": "/carrinho/adicionar",
    "user_agent": "Mozilla/5.0...",
    "timestamp": "2025-09-06T10:30:00Z"
  }
}
```

## 🔧 **Como Usar**

### 1. No Controller
```php
use App\Exceptions\Cart\ProductNotAvailableException;
use App\Exceptions\Cart\InvalidQuantityException;

public function add(CartAddRequest $request)
{
    try {
        $product = Product::findOrFail($request->product_id);
        
        if (!$product->is_active) {
            throw new ProductNotAvailableException($product->name, 0);
        }
        
        if ($request->quantity <= 0) {
            throw new InvalidQuantityException($request->quantity);
        }
        
        // Lógica do carrinho...
        
    } catch (ProductNotAvailableException | InvalidQuantityException $e) {
        throw $e; // Deixa o handler tratar
    } catch (\Exception $e) {
        Log::error('Erro inesperado', ['exception' => $e]);
        return response()->json(['error' => 'Erro interno'], 500);
    }
}
```

### 2. Testando Exceções
```php
// Teste de produto não disponível
curl -X POST http://localhost:8000/carrinho/adicionar \
  -H "Content-Type: application/json" \
  -d '{"product_id": 999, "quantity": 1}'

// Resposta esperada (422):
{
  "success": false,
  "error_type": "cart_error",
  "message": "Produto #999 não encontrado",
  "code": 1001
}
```

## 📝 **Respostas das Exceções**

### Para Requisições JSON/API
```json
{
  "success": false,
  "error_type": "cart_error|product_error|auth_error",
  "message": "Mensagem específica do erro",
  "code": 1001
}
```

### Para Requisições Web
- **Redirecionamento** para página anterior
- **Flash message** com erro
- **Preservação** dos dados do formulário

## 🚀 **Vantagens do Sistema**

### ✅ **Organização**
- Exceções agrupadas por contexto
- Códigos de erro únicos
- Logs separados por categoria

### ✅ **Consistência**
- Formato padronizado de resposta
- Mensagens em português
- Status codes apropriados

### ✅ **Debugging**
- Logs detalhados com contexto
- Rastreamento de usuário
- Informações de requisição

### ✅ **Manutenibilidade**
- Fácil extensão para novos tipos
- Reutilização de código
- Testes simplificados

## 🔄 **Extensões Futuras**

### Novos Tipos de Exceção
```php
// Exceções de pagamento
namespace App\Exceptions\Payment;
class PaymentFailedException extends PaymentException { }

// Exceções de pedido
namespace App\Exceptions\Order;
class OrderNotFoundException extends OrderException { }

// Exceções de usuário
namespace App\Exceptions\User;
class AccountSuspendedException extends UserException { }
```

### Melhorias Possíveis
- **Notificações**: Envio automático para Slack/Discord
- **Métricas**: Integração com sistemas de monitoramento
- **I18n**: Suporte a múltiplos idiomas
- **Rate Limiting**: Controle de frequência de erros
