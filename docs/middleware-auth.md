# Middleware de Autenticação Obrigatório

## Descrição
O `RequireAuthMiddleware` é um middleware customizado que garante que apenas usuários autenticados possam acessar determinadas rotas.

## Características

### ✅ Verificações Realizadas
- **Autenticação**: Verifica se o usuário está logado
- **Suporte a API**: Retorna respostas JSON para requisições de API
- **Redirecionamento**: Redireciona usuários não autenticados para o login

### 🔧 Funcionalidades
- Detecta automaticamente se é uma requisição JSON/API
- Mensagens de erro personalizadas em português
- Redirecionamento inteligente para a página de login
- Preserva a URL original para redirecionamento após login

## Como Usar

### 1. Em Rotas (Recomendado)
```php
// Aplicar a rotas específicas
Route::middleware('require.auth')->group(function () {
    Route::get('/carrinho', [CartController::class, 'index']);
    Route::post('/carrinho/adicionar', [CartController::class, 'add']);
});

// Ou em uma rota individual
Route::get('/perfil', [ProfileController::class, 'edit'])
    ->middleware('require.auth');
```

### 2. Em Controllers
```php
class ExemploController extends Controller
{
    public function __construct()
    {
        $this->middleware('require.auth');
        // ou para métodos específicos:
        $this->middleware('require.auth')->only(['create', 'store']);
        $this->middleware('require.auth')->except(['index', 'show']);
    }
}
```

### 3. Combinando com Outros Middlewares
```php
Route::middleware(['require.auth', 'throttle:60,1'])->group(function () {
    // Rotas protegidas com rate limiting
});
```

## Respostas do Middleware

### Para Requisições Web
- **Não autenticado**: Redireciona para `/login` com mensagem de erro
- **Sucesso**: Permite continuar para a rota

### Para Requisições API/JSON
```json
// Não autenticado (HTTP 401)
{
    "success": false,
    "message": "Acesso negado. Você precisa estar logado.",
    "redirect_to": "http://example.com/login"
}
```

## Instalação Realizada

### 1. Middleware Criado
✅ `/app/Http/Middleware/RequireAuthMiddleware.php`

### 2. Registrado no Bootstrap
✅ Adicionado alias `require.auth` em `/bootstrap/app.php`

### 3. Aplicado às Rotas do Carrinho
✅ Rotas do carrinho protegidas em `/routes/web.php`

## Testando o Middleware

### 1. Teste Manual
- Acesse `/carrinho` sem estar logado
- Deve redirecionar para `/login`

### 2. Teste com API
```bash
curl -X GET http://localhost:8000/carrinho \
  -H "Accept: application/json"
```

Deve retornar resposta JSON com erro 401.

## Extensões Futuras

Para adicionar mais verificações no futuro:

```php
// Verificar se conta está ativa
if ($user->status !== 'active') {
    // lógica de conta inativa
}

// Verificar se email foi verificado
if (!$user->email_verified_at) {
    // lógica de email não verificado
}

// Verificar permissions específicas
if (!$user->hasPermission('access_cart')) {
    // lógica de permissão negada
}
```
