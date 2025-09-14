<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('description', 'Sistema de Loja Online')">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        /* ===== DESIGN SYSTEM VARIABLES ===== */
        :root {
            /* Core Colors - Brand identity and semantic meaning */
            --color-primary: #007bff;
            --color-secondary: #6c757d;
            --color-success: #28a745;
            --color-danger: #dc3545;
            --color-warning: #ffc107;
            --color-info: #17a2b8;
            --color-white: #ffffff;
            --color-dark: #212529;
            --color-light: #f8f9fa;
            
            /* Component spacing - Consistent vertical/horizontal rhythm */
            --space-xs: 0.25rem;
            --space-sm: 0.5rem;
            --space-md: 1rem;
            --space-lg: 1.5rem;
            --space-xl: 3rem;
            
            /* Animation timing - Smooth user interactions */
            --transition-fast: 0.15s ease;
            --transition-normal: 0.3s ease;
            --transition-smooth: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ===== REUSABLE COMPONENT STYLES ===== */
        
        /* Interactive elements - Hover states for better UX */
        .interactive-element {
            transition: var(--transition-normal);
            cursor: pointer;
        }
        
        .interactive-element:hover {
            transform: translateY(-1px);
        }
        
        /* Badge system - Consistent notification styling */
        .badge-base {
            position: absolute;
            top: -8px;
            right: -8px;
            color: var(--color-white);
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 11px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            transition: var(--transition-fast);
        }
        
        /* Navigation components - Consistent branding across navigation */
        .navigation-brand {
            font-weight: bold;
            font-size: 1.5rem;
            color: var(--color-primary);
            text-decoration: none;
        }

        .navigation-sticky {
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .navigation-link {
            transition: var(--transition-normal);
        }

        .navigation-link:hover {
            color: var(--color-primary) !important;
            transform: translateY(-1px);
        }

        /* ===== SPECIFIC COMPONENT IMPLEMENTATIONS ===== */
        
        /* Cart/Wishlist badges - Extends badge-base for specific use cases */
        .badge-counter {
            position: absolute;
            top: -8px;
            right: -8px;
            color: var(--color-white);
            border-radius: 50%;
            min-width: 20px;
            height: 20px;
            font-size: 11px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            transition: var(--transition-fast);
            line-height: 1;
            padding: 0;
            text-align: center;
            vertical-align: middle;
            /* Força centralização vertical perfeita */
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        /* Para números de dois dígitos ou mais, ajustar formato */
        .badge-counter[data-count]:not([data-count=""]):not([data-count="0"]) {
            padding: 0 4px;
            border-radius: 10px;
            min-width: 20px;
        }

        /* Manter circular para números de 1 dígito */
        .badge-counter[data-count="1"],
        .badge-counter[data-count="2"],
        .badge-counter[data-count="3"],
        .badge-counter[data-count="4"],
        .badge-counter[data-count="5"],
        .badge-counter[data-count="6"],
        .badge-counter[data-count="7"],
        .badge-counter[data-count="8"],
        .badge-counter[data-count="9"] {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            padding: 0;
            /* Garantir centralização perfeita para números únicos */
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 20px;
        }

        .badge-counter--cart {
            background: var(--color-danger);
        }

        .badge-counter--wishlist {
            background: #e91e63;
        }

        .badge-admin {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: var(--color-white);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            margin-left: var(--space-sm);
        }

        /* Search form - Focused user input experience */
        .search-form-container {
            width: 300px;
        }

        .search-input-field:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* Dropdown enhancement - Professional appearance */
        .dropdown-menu-enhanced {
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 0.5rem;
        }

        .dropdown-item-styled:hover {
            background-color: var(--color-primary);
            color: var(--color-white);
            transform: translateX(4px);
        }

        /* Promotions ticker - Marketing content display */
        .promotions-ticker-container {
            background: linear-gradient(90deg, var(--color-warning), #ffcd3c);
            color: var(--color-dark);
            padding: 8px 0;
            overflow: hidden;
            white-space: nowrap;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .promotions-ticker-content {
            display: inline-block;
            animation: scrollHorizontalLeft 60s linear infinite;
            font-weight: 500;
        }

        /* Pause animation on hover - Better UX for reading */
        .promotions-ticker-content:hover {
            animation-play-state: paused;
        }

        /* Horizontal scrolling animation - Smooth continuous movement */
        @keyframes scrollHorizontalLeft {
            0% { transform: translate3d(20%, 0, 0); }
            100% { transform: translate3d(-100%, 0, 0); }
        }

        .promotion-item-individual {
            margin-right: var(--space-xl);
            display: inline-block;
        }

        .promotion-visual-separator {
            margin: 0 4rem;
            display: inline-block;
            opacity: 0.7;
        }

        .promotion-icon-fire {
            color: var(--color-danger);
            margin-right: var(--space-sm);
        }

        .footer-main-section {
            background-color: #2c3e50;
            color: var(--color-white);
        }

        /* ===== RESPONSIVE DESIGN ===== */
        /* Mobile-first approach - Progressive enhancement */
        @media (max-width: 768px) {
            .search-form-container {
                width: 100%;
                margin-top: var(--space-md);
            }
            
            .navbar-nav {
                margin-top: var(--space-md);
            }
            
            /* Faster animation on mobile - Better performance */
            .promotions-ticker-container {
                padding: var(--space-sm) 0;
                font-size: 0.9rem;
            }
            
            .promotions-ticker-content {
                animation-duration: 45s;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Header -->
    <header>
        <!-- Top Bar -->
        <div class="bg-dark text-white py-1">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <small>
                            <i class="fas fa-envelope"></i> contato@loja.com
                            <span class="ms-3">
                                <i class="fas fa-phone"></i> (11) 99999-9999
                            </span>
                        </small>
                    </div>
                    <div class="col-md-6 text-end">
                        <small>
                            <a href="#" class="text-white text-decoration-none me-2">
                                <i class="fab fa-facebook"></i>
                            </a>
                            <a href="#" class="text-white text-decoration-none me-2">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="text-white text-decoration-none">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Promotions Ticker -->
        @if(isset($activePromotions) && $activePromotions->count() > 0)
        <div class="promotions-ticker-container">
            <div class="container-fluid">
                <div class="promotions-ticker-content">
                    @foreach($activePromotions as $promotionItem)
                        <span class="promotion-item-individual">
                            <i class="fas fa-fire promotion-icon-fire"></i>
                            <strong>{{ $promotionItem->name }}:</strong>
                            R$ {{ format_currency($promotionItem->original_price) }} por 
                            R$ {{ format_currency($promotionItem->promotional_price) }} 
                            ({{ $promotionItem->value }}% OFF - Economia de R$ {{ format_currency($promotionItem->discount_amount) }})
                        </span>
                    @endforeach
                    <!-- Separador visual para o loop -->
                    <span class="promotion-visual-separator">
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                    </span>
                    <!-- Segunda iteração para loop contínuo -->
                    @foreach($activePromotions as $promotionItem)
                        <span class="promotion-item-individual">
                            <i class="fas fa-fire promotion-icon-fire"></i>
                            <strong>{{ $promotionItem->name }}:</strong>
                            R$ {{ format_currency($promotionItem->original_price) }} por 
                            R$ {{ format_currency($promotionItem->promotional_price) }} 
                            ({{ $promotionItem->value }}% OFF - Economia de R$ {{ format_currency($promotionItem->discount_amount) }})
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Main Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm navigation-sticky">
            <div class="container">
                <!-- Logo -->
                <a class="navigation-brand" href="{{ route('home') }}">
                    <i class="fas fa-store"></i>
                    {{ config('app.name') }}
                </a>

                <!-- Mobile Toggle -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <!-- Search Box -->
                    <div class="mx-auto">
                        <form action="{{ route('products.index') }}" method="GET" class="d-flex search-form-container" role="search" id="searchForm">
                            <input type="text" 
                                   name="search" 
                                   class="form-control search-input-field" 
                                   placeholder="Buscar produtos..." 
                                   aria-label="Buscar produtos"
                                   value="{{ e(request('search')) }}"
                                   maxlength="100"
                                   pattern="^[a-zA-Z0-9\s\-_.()àáâãäéêëíîïóôõöúûüç]+$"
                                   title="Use apenas letras, números, espaços e caracteres básicos"
                                   data-validation="search">
                            <button type="submit" class="btn btn-primary ms-2 search-button-submit" aria-label="Buscar">
                                <i class="fas fa-search" aria-hidden="true"></i>
                            </button>
                        </form>
                    </div>

                    <!-- User Menu -->
                    <ul class="navbar-nav">
                        @auth
                            <!-- Admin Access -->
                            @if(auth()->user()->isAdmin())
                                <li class="nav-item">
                                    <a class="nav-link navigation-link" href="{{ route('admin.dashboard') }}">
                                        <i class="fas fa-cog"></i> 
                                        Admin
                                        <span class="badge-admin">ADMIN</span>
                                    </a>
                                </li>
                            @endif

                            <!-- Wishlist -->
                            <li class="nav-item position-relative">
                                <a class="nav-link navigation-link" href="{{ route('wishlist.index') }}" title="Lista de Desejos">
                                    <i class="fas fa-heart fa-lg"></i>
                                    <span class="badge-counter badge-counter--wishlist" id="wishlistCounter" data-wishlist-count data-count="{{ $wishlistItemsCount ?? 0 }}">
                                        @php
                                            // Otimização: Usar método com cache do modelo
                                            $wishlistItemsCount = \App\Models\Wishlist::getUserWishlistCount(auth()->id());
                                        @endphp
                                        {{ $wishlistItemsCount }}
                                    </span>
                                </a>
                            </li>

                            <!-- Cart -->
                            <li class="nav-item position-relative">
                                <a class="nav-link navigation-link" href="{{ route('cart.index') }}" title="Carrinho de Compras">
                                    <i class="fas fa-shopping-cart fa-lg"></i>
                                    <span class="badge-counter badge-counter--cart" id="cartCounter" data-cart-count data-count="{{ $cartItemsQuantity ?? 0 }}">
                                        @php
                                            // Otimização: Usar método com cache do modelo
                                            $cartItemsQuantity = auth()->check() ? \App\Models\CartItem::getUserCartQuantity(auth()->id()) : 0;
                                        @endphp
                                        {{ $cartItemsQuantity }}
                                    </span>
                                </a>
                            </li>

                            <!-- User Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link navigation-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user"></i>
                                    {{ auth()->user()->name }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-enhanced">
                                    <li><a class="dropdown-item dropdown-item-styled" href="{{ route('user.orders') }}">
                                        <i class="fas fa-box"></i> Meus Pedidos
                                    </a></li>
                                    <li><a class="dropdown-item dropdown-item-styled" href="{{ route('profile.edit') }}">
                                        <i class="fas fa-user-edit"></i> Meu Perfil
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item dropdown-item-styled">
                                                <i class="fas fa-sign-out-alt"></i> Sair
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @else
                            <!-- Guest Links -->
                            <li class="nav-item">
                                <a class="nav-link navigation-link" href="{{ route('login') }}">
                                    <i class="fas fa-sign-in-alt"></i> Entrar
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link navigation-link" href="{{ route('register') }}">
                                    <i class="fas fa-user-plus"></i> Cadastrar
                                </a>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show m-0" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show m-0" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer-main-section mt-5">
        <div class="container py-5">
            <div class="row">
                <!-- Company Info -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="text-primary mb-3">{{ config('app.name') }}</h5>
                    <p class="mb-3">
                        Sua loja online de confiança com os melhores produtos 
                        e preços do mercado. Qualidade e satisfação garantidas!
                    </p>
                    <div class="d-flex">
                        <a href="#" class="text-white me-3 fs-4 navigation-link">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="text-white me-3 fs-4 navigation-link">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-white me-3 fs-4 navigation-link">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="#" class="text-white fs-4 navigation-link">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="mb-3">Links Rápidos</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('home') }}" class="text-light text-decoration-none navigation-link">Início</a></li>
                        <li><a href="{{ route('products.index') }}" class="text-light text-decoration-none navigation-link">Produtos</a></li>
                        <li><a href="#" class="text-light text-decoration-none navigation-link">Sobre Nós</a></li>
                        <li><a href="#" class="text-light text-decoration-none navigation-link">Contato</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-6 col-md-12 mb-4">
                    <h5 class="mb-3">Contato</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                Rua Exemplo, 123 - São Paulo, SP
                            </div>
                            <div class="mb-2">
                                <i class="fas fa-phone me-2"></i>
                                (11) 99999-9999
                            </div>
                            <div class="mb-3">
                                <i class="fas fa-envelope me-2"></i>
                                contato@loja.com
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-2">Newsletter</h6>
                            <form class="d-flex" id="newsletterSubscriptionForm" novalidate>
                                <input type="email" 
                                       class="form-control search-input-field me-2" 
                                       placeholder="Seu e-mail" 
                                       required
                                       maxlength="150"
                                       pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                                       title="Insira um e-mail válido (exemplo: usuario@dominio.com)"
                                       data-validation="email">
                                <button class="btn btn-primary search-button-submit" type="submit">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <!-- Bottom Footer -->
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-light text-decoration-none navigation-link me-3">Política de Privacidade</a>
                    <a href="#" class="text-light text-decoration-none navigation-link me-3">Termos de Uso</a>
                    <a href="#" class="text-light text-decoration-none navigation-link">FAQ</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        /**
         * ===== USER INPUT VALIDATION & SANITIZATION SYSTEM =====
         * SECURITY PRINCIPLE: Never trust user input - validate and sanitize everything
         */
        
        // ===== INPUT VALIDATION SYSTEM =====
        
        /**
         * Input validation rules and patterns
         * WHY: Centralized validation prevents injection attacks and ensures data integrity
         */
        const ValidationRules = {
            // Search input validation - prevents XSS and SQL injection
            search: {
                pattern: /^[a-zA-Z0-9\s\-_.()àáâãäéêëíîïóôõöúûüç]{0,100}$/,
                minLength: 0,
                maxLength: 100,
                errorMessage: 'Busca deve conter apenas letras, números e caracteres básicos (máx. 100 caracteres)'
            },
            
            // Email validation - strict RFC-compliant pattern
            email: {
                pattern: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
                minLength: 5,
                maxLength: 150,
                errorMessage: 'Insira um e-mail válido (exemplo: usuario@dominio.com)'
            },
            
            // Generic text validation for future use
            text: {
                pattern: /^[a-zA-Z0-9\s\-_.àáâãäéêëíîïóôõöúûüç]{0,255}$/,
                minLength: 0,
                maxLength: 255,
                errorMessage: 'Texto inválido - use apenas caracteres alfanuméricos básicos'
            }
        };
        
        /**
         * Sanitizes user input by removing potentially dangerous characters
         * WHY: Prevents XSS attacks and code injection with comprehensive error handling
         * @param {string} input - Raw user input
         * @returns {string} - Sanitized safe string
         */
        function sanitizeInput(input) {
            try {
                // Handle null, undefined, or non-string inputs
                if (input === null || input === undefined) {
                    return '';
                }
                
                if (typeof input !== 'string') {
                    // Try to convert to string safely
                    try {
                        input = String(input);
                    } catch (conversionError) {
                        console.warn('sanitizeInput: Could not convert input to string:', conversionError);
                        return '';
                    }
                }
                
                // Check for extremely large inputs to prevent DoS
                if (input.length > 10000) {
                    console.warn('sanitizeInput: Input too large, truncating');
                    input = input.substring(0, 10000);
                }
                
                return input
                    // Remove HTML tags and potential script injections
                    .replace(/<[^>]*>/g, '')
                    // Remove potential JavaScript injections
                    .replace(/javascript:/gi, '')
                    .replace(/on\w+=/gi, '')
                    // Remove SQL injection attempts
                    .replace(/['";]/g, '')
                    // Remove potential command injections
                    .replace(/[&|;$`<>]/g, '')
                    // Normalize whitespace
                    .replace(/\s+/g, ' ')
                    .trim();
                    
            } catch (error) {
                console.error('sanitizeInput: Unexpected error during sanitization:', error);
                // Return empty string as safe fallback
                return '';
            }
        }
        
        /**
         * Validates input against specific rules with comprehensive error handling
         * WHY: Ensures data meets business requirements and security standards
         * @param {string} input - Input to validate
         * @param {string} type - Validation type (search, email, text)
         * @returns {Object} - Validation result with isValid and error message
         */
        function validateInput(input, type) {
            try {
                // Parameter validation
                if (typeof input !== 'string') {
                    return { isValid: false, error: 'Entrada deve ser um texto válido' };
                }
                
                if (!type || typeof type !== 'string') {
                    return { isValid: false, error: 'Tipo de validação não especificado' };
                }
                
                const rule = ValidationRules[type];
                
                if (!rule) {
                    console.error(`validateInput: Unknown validation type: ${type}`);
                    return { isValid: false, error: 'Tipo de validação não reconhecido' };
                }
                
                // Validate required properties of rule
                if (!rule.pattern || !rule.errorMessage || typeof rule.minLength !== 'number' || typeof rule.maxLength !== 'number') {
                    console.error(`validateInput: Invalid rule configuration for type: ${type}`);
                    return { isValid: false, error: 'Configuração de validação inválida' };
                }
                
                // Length validation with error handling
                if (input.length < rule.minLength || input.length > rule.maxLength) {
                    return { 
                        isValid: false, 
                        error: `Campo deve ter entre ${rule.minLength} e ${rule.maxLength} caracteres` 
                    };
                }
                
                // Pattern validation with error handling
                try {
                    if (!rule.pattern.test(input)) {
                        return { isValid: false, error: rule.errorMessage };
                    }
                } catch (regexError) {
                    console.error(`validateInput: Regex error for type ${type}:`, regexError);
                    return { isValid: false, error: 'Erro na validação de formato' };
                }
                
                return { isValid: true, error: null };
                
            } catch (error) {
                console.error('validateInput: Unexpected error during validation:', error);
                return { isValid: false, error: 'Erro inesperado na validação' };
            }
        }
        
        /**
         * Processes and validates form input securely with comprehensive error handling
         * WHY: Single point for all input processing prevents security gaps
         * @param {HTMLInputElement} inputElement - Input element to process
         * @returns {Object} - Processing result with sanitized value and validation
         */
        function processUserInput(inputElement) {
            try {
                // Validate input element
                if (!inputElement) {
                    console.error('processUserInput: No input element provided');
                    return {
                        original: '',
                        sanitized: '',
                        isValid: false,
                        error: 'Elemento de entrada não encontrado'
                    };
                }
                
                // Check if it's actually an input element
                if (!inputElement.value && inputElement.value !== '') {
                    console.error('processUserInput: Invalid input element - no value property');
                    return {
                        original: '',
                        sanitized: '',
                        isValid: false,
                        error: 'Elemento de entrada inválido'
                    };
                }
                
                const rawValue = inputElement.value || '';
                const validationType = inputElement.dataset?.validation || 'text';
                
                // Step 1: Sanitize input to remove dangerous content
                let sanitizedValue;
                try {
                    sanitizedValue = sanitizeInput(rawValue);
                } catch (sanitizationError) {
                    console.error('processUserInput: Sanitization failed:', sanitizationError);
                    return {
                        original: rawValue,
                        sanitized: '',
                        isValid: false,
                        error: 'Erro na sanitização dos dados'
                    };
                }
                
                // Step 2: Validate sanitized input
                let validation;
                try {
                    validation = validateInput(sanitizedValue, validationType);
                } catch (validationError) {
                    console.error('processUserInput: Validation failed:', validationError);
                    return {
                        original: rawValue,
                        sanitized: sanitizedValue,
                        isValid: false,
                        error: 'Erro na validação dos dados'
                    };
                }
                
                return {
                    original: rawValue,
                    sanitized: sanitizedValue,
                    isValid: validation.isValid,
                    error: validation.error
                };
                
            } catch (error) {
                console.error('processUserInput: Unexpected error:', error);
                return {
                    original: '',
                    sanitized: '',
                    isValid: false,
                    error: 'Erro inesperado no processamento'
                };
            }
        }
        
        /**
         * Shows validation error message to user with error handling
         * WHY: Provides clear feedback about input issues safely
         * @param {HTMLInputElement} inputElement - Input that failed validation
         * @param {string} errorMessage - Error message to display
         */
        function showValidationError(inputElement, errorMessage) {
            try {
                // Validate parameters
                if (!inputElement) {
                    console.error('showValidationError: No input element provided');
                    return;
                }
                
                if (!inputElement.parentNode) {
                    console.error('showValidationError: Input element has no parent node');
                    return;
                }
                
                if (!errorMessage || typeof errorMessage !== 'string') {
                    console.warn('showValidationError: Invalid error message, using default');
                    errorMessage = 'Entrada inválida';
                }
                
                // Sanitize error message to prevent XSS
                const safeErrorMessage = sanitizeInput(errorMessage);
                
                // Remove existing error messages safely
                try {
                    const existingError = inputElement.parentNode.querySelector('.validation-error');
                    if (existingError) {
                        existingError.remove();
                    }
                } catch (removalError) {
                    console.warn('showValidationError: Could not remove existing error:', removalError);
                }
                
                // Create new error message with error handling
                try {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'validation-error text-danger small mt-1';
                    
                    // Create icon safely
                    const iconElement = document.createElement('i');
                    iconElement.className = 'fas fa-exclamation-triangle me-1';
                    iconElement.setAttribute('aria-hidden', 'true');
                    
                    // Create message span safely
                    const messageSpan = document.createElement('span');
                    messageSpan.textContent = safeErrorMessage;
                    
                    // Assemble error div
                    errorDiv.appendChild(iconElement);
                    errorDiv.appendChild(messageSpan);
                    
                    // Add error styling to input
                    inputElement.classList.add('is-invalid');
                    
                    // Insert error message after input
                    inputElement.parentNode.appendChild(errorDiv);
                    
                } catch (creationError) {
                    console.error('showValidationError: Could not create error element:', creationError);
                    // Fallback: just add error class to input
                    try {
                        inputElement.classList.add('is-invalid');
                    } catch (fallbackError) {
                        console.error('showValidationError: Fallback failed:', fallbackError);
                    }
                    return;
                }
                
                // Remove error styling after user starts typing (with error handling)
                try {
                    inputElement.addEventListener('input', function removeError() {
                        try {
                            inputElement.classList.remove('is-invalid');
                            const errorMsg = inputElement.parentNode?.querySelector('.validation-error');
                            if (errorMsg) {
                                errorMsg.remove();
                            }
                            inputElement.removeEventListener('input', removeError);
                        } catch (cleanupError) {
                            console.warn('showValidationError: Cleanup error:', cleanupError);
                        }
                    }, { once: true });
                } catch (listenerError) {
                    console.warn('showValidationError: Could not add cleanup listener:', listenerError);
                }
                
            } catch (error) {
                console.error('showValidationError: Unexpected error:', error);
                // Minimal fallback
                try {
                    if (inputElement && inputElement.classList) {
                        inputElement.classList.add('is-invalid');
                    }
                } catch (minimalFallbackError) {
                    console.error('showValidationError: Minimal fallback failed:', minimalFallbackError);
                }
            }
        }
        
        /**
         * Handles search form validation
         * WHY: Prevents malicious search queries from reaching the server
         * @param {Event} event - Form submission event
         */
        function handleSearchFormValidation(event) {
            const form = event.target;
            const searchInput = form.querySelector('input[name="search"]');
            
            if (!searchInput) return true;
            
            const result = processUserInput(searchInput);
            
            if (!result.isValid) {
                event.preventDefault();
                showValidationError(searchInput, result.error);
                showMessage(result.error, 'warning');
                return false;
            }
            
            // Update input with sanitized value before submission
            searchInput.value = result.sanitized;
            return true;
        }
        
        // ===== COUNTER MANAGEMENT ===== (Mantido com validação adicional)
        
        /**
         * Updates counter badges with input validation and performance optimization
         * WHY: Ensures counter values are safe numbers and handles edge cases efficiently
         * @param {string} selector - CSS selector for counter elements
         * @param {number|string} count - New counter value
         */
        function updateCounterBadge(selector, count) {
            try {
                // Validate selector parameter
                if (!selector || typeof selector !== 'string') {
                    console.warn('updateCounterBadge: Invalid selector provided');
                    return;
                }
                
                // Validate and sanitize counter value with error handling
                let safeCount = 0;
                
                if (count !== null && count !== undefined) {
                    const parsedCount = parseInt(count);
                    if (!isNaN(parsedCount)) {
                        safeCount = Math.max(0, Math.min(9999, parsedCount));
                    }
                }
                
                // Use native forEach for better performance than traditional loops
                const elements = document.querySelectorAll(selector);
                
                if (elements.length === 0) {
                    console.warn(`updateCounterBadge: No elements found with selector: ${selector}`);
                    return;
                }
                
                // Batch DOM operations to minimize reflows
                elements.forEach(element => {
                    try {
                        element.textContent = safeCount;
                        element.setAttribute('data-count', safeCount);
                        element.style.display = safeCount > 0 ? 'inline-flex' : 'none';
                    } catch (elementError) {
                        console.error('updateCounterBadge: Error updating element:', elementError);
                    }
                });
                
            } catch (error) {
                console.error('updateCounterBadge: Unexpected error:', error);
                // Fallback: Try to hide counter elements efficiently
                const elements = document.querySelectorAll(selector);
                elements.forEach(el => {
                    try {
                        el.style.display = 'none';
                    } catch (fallbackError) {
                        console.error('updateCounterBadge: Fallback failed:', fallbackError);
                    }
                });
            }
        }
        
        /**
         * Updates cart counter across all instances
         * WHY: Specific wrapper for cart counter updates with validation
         */
        function updateCartCounter(count) {
            updateCounterBadge('[data-cart-count]', count);
        }
        
        /**
         * Updates wishlist counter across all instances  
         * WHY: Specific wrapper for wishlist counter updates with validation
         */
        function updateWishlistCounter(count) {
            updateCounterBadge('[data-wishlist-count]', count);
        }
        
        // ===== MESSAGE SYSTEM ===== (Mantido com sanitização adicional)
        
        /**
         * Creates alert configuration object
         * WHY: Centralized alert styling and behavior configuration
         */
        const AlertConfig = {
            TYPES: {
                success: { icon: 'check-circle', class: 'alert-success' },
                danger: { icon: 'exclamation-circle', class: 'alert-danger' },
                info: { icon: 'info-circle', class: 'alert-info' },
                warning: { icon: 'exclamation-triangle', class: 'alert-warning' }
            },
            DURATION: 4000,
            POSITION: {
                top: '20px',
                right: '20px',
                zIndex: '9999',
                maxWidth: '300px'
            }
        };
        
        /**
         * Removes existing floating alerts from DOM with performance optimization
         * WHY: Prevents alert accumulation and visual clutter, uses efficient DOM manipulation
         */
        function clearExistingAlerts() {
            // Use native forEach with optimized removal - more efficient than loops
            const alerts = document.querySelectorAll('.alert-floating');
            if (alerts.length > 0) {
                // Batch DOM operations for better performance
                alerts.forEach(alert => alert.remove());
            }
        }
        
        /**
         * Creates styled alert element with proper accessibility and input sanitization
         * WHY: Prevents XSS in alert messages
         * @param {string} message - Alert message content (will be sanitized)
         * @param {string} type - Alert type (success, danger, info, warning)
         * @returns {HTMLElement} - Configured alert element
         */
        function createAlertElement(message, type) {
            const config = AlertConfig.TYPES[type] || AlertConfig.TYPES.info;
            const alertDiv = document.createElement('div');
            
            // Sanitize message content to prevent XSS
            const safeMessage = sanitizeInput(String(message));
            
            alertDiv.className = `alert ${config.class} alert-dismissible fade show position-fixed alert-floating`;
            alertDiv.setAttribute('role', 'alert');
            alertDiv.style.cssText = `
                top: ${AlertConfig.POSITION.top}; 
                right: ${AlertConfig.POSITION.right}; 
                z-index: ${AlertConfig.POSITION.zIndex}; 
                max-width: ${AlertConfig.POSITION.maxWidth}; 
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            `;
            
            // Use textContent instead of innerHTML to prevent XSS
            alertDiv.innerHTML = `
                <i class="fas fa-${config.icon} me-2" aria-hidden="true"></i>
                <span class="alert-message"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            `;
            
            // Safely set the message content
            alertDiv.querySelector('.alert-message').textContent = safeMessage;
            
            return alertDiv;
        }
        
        /**
         * Shows temporary alert message to user with input sanitization and error handling
         * WHY: Provides secure user feedback across the application
         * @param {string} message - Message to display (will be sanitized)
         * @param {string} type - Alert type (success, danger, info, warning)
         */
        function showMessage(message, type = 'success') {
            try {
                // Validate parameters
                if (!message) {
                    console.warn('showMessage: No message provided');
                    return;
                }
                
                if (!type || typeof type !== 'string') {
                    console.warn('showMessage: Invalid type, using default');
                    type = 'success';
                }
                
                // Clear existing alerts safely
                try {
                    clearExistingAlerts();
                } catch (clearError) {
                    console.warn('showMessage: Could not clear existing alerts:', clearError);
                }
                
                // Create alert element safely
                let alertElement;
                try {
                    alertElement = createAlertElement(message, type);
                } catch (creationError) {
                    console.error('showMessage: Could not create alert element:', creationError);
                    // Fallback to browser alert
                    try {
                        alert(sanitizeInput(String(message)));
                    } catch (fallbackError) {
                        console.error('showMessage: Fallback alert failed:', fallbackError);
                    }
                    return;
                }
                
                if (!alertElement) {
                    console.error('showMessage: Alert element is null');
                    return;
                }
                
                // Append to body safely
                try {
                    document.body.appendChild(alertElement);
                } catch (appendError) {
                    console.error('showMessage: Could not append alert to body:', appendError);
                    return;
                }
                
                // Auto-remove after configured duration with error handling
                try {
                    setTimeout(() => {
                        try {
                            if (alertElement?.parentNode) {
                                alertElement.remove();
                            }
                        } catch (removeError) {
                            console.warn('showMessage: Could not auto-remove alert:', removeError);
                        }
                    }, AlertConfig.DURATION);
                } catch (timeoutError) {
                    console.warn('showMessage: Could not set timeout for auto-removal:', timeoutError);
                }
                
            } catch (error) {
                console.error('showMessage: Unexpected error:', error);
                // Last resort fallback
                try {
                    const safeMessage = String(message).substring(0, 200);
                    alert(safeMessage);
                } catch (lastResortError) {
                    console.error('showMessage: Last resort fallback failed:', lastResortError);
                }
            }
        }
        
        /**
         * Shows cart-specific feedback message with validation
         * WHY: Specialized secure feedback for cart operations
         * @param {string} message - Cart operation message
         * @param {boolean} success - Whether operation was successful
         */
        function showCartMessage(message, success = true) {
            showMessage(message, success ? 'success' : 'danger');
        }
        
        // ===== FORM HANDLERS WITH VALIDATION =====
        
        /**
         * Handles newsletter subscription with comprehensive validation
         * WHY: Prevents malicious email submissions and ensures data quality
         * @param {Event} event - Form submission event
         */
        function handleNewsletterSubmission(event) {
            event.preventDefault();
            
            const emailInput = event.target.querySelector('input[type="email"]');
            if (!emailInput) return;
            
            const result = processUserInput(emailInput);
            
            if (!result.isValid) {
                showValidationError(emailInput, result.error);
                showMessage(result.error, 'warning');
                return;
            }
            
            // Additional business logic validation
            if (result.sanitized.length < 5) {
                showValidationError(emailInput, 'E-mail muito curto');
                showMessage('E-mail deve ter pelo menos 5 caracteres', 'warning');
                return;
            }
            
            // Simulate subscription success with sanitized email
            showMessage('Obrigado por se inscrever! Em breve você receberá nossas novidades.', 'success');
            event.target.reset();
            
            // Focus back to input for accessibility
            emailInput?.focus();
        }
        
        /**
         * Initializes counter display states with validation
         * WHY: Ensures proper initial visibility of counter badges
         */
        function initializeCounters() {
            const cartCount = document.querySelector('[data-cart-count]');
            const wishlistCount = document.querySelector('[data-wishlist-count]');
            
            if (cartCount) {
                const count = parseInt(cartCount.textContent) || 0;
                updateCounterBadge('[data-cart-count]', count);
            }
            
            if (wishlistCount) {
                const count = parseInt(wishlistCount.textContent) || 0;
                updateCounterBadge('[data-wishlist-count]', count);
            }
        }
        
        /**
         * Sets up form validation event listeners with performance optimization
         * WHY: Enables comprehensive input validation across all forms efficiently
         */
        function initializeFormValidation() {
            // Cache DOM queries for better performance
            const searchForm = document.getElementById('searchForm');
            const newsletterForm = document.getElementById('newsletterSubscriptionForm');
            const validationInputs = document.querySelectorAll('input[data-validation]');
            
            // Search form validation
            if (searchForm) {
                searchForm.addEventListener('submit', handleSearchFormValidation);
            }
            
            // Newsletter form validation
            if (newsletterForm) {
                newsletterForm.addEventListener('submit', handleNewsletterSubmission);
            }
            
            // Real-time validation - use native forEach for better performance
            validationInputs.forEach(input => {
                input.addEventListener('blur', function() {
                    const result = processUserInput(this);
                    if (!result.isValid && this.value.length > 0) {
                        showValidationError(this, result.error);
                    }
                }, { passive: true }); // Use passive listeners for better performance
            });
        }
        
        // =========================================
        // PERFORMANCE & CONNECTIVITY MONITORING
        // =========================================
        
        /**
         * Monitor page load performance
         * WHY: Track performance metrics to identify slow operations
         */
        function monitorPerformance() {
            try {
                // Monitor page load time
                window.addEventListener('load', function() {
                    try {
                        if (window.performance && window.performance.timing) {
                            const timing = window.performance.timing;
                            const loadTime = timing.loadEventEnd - timing.navigationStart;
                            
                            // Log slow loads (threshold: 3 seconds)
                            if (loadTime > 3000) {
                                console.warn('Slow page load detected:', {
                                    loadTime: loadTime + 'ms',
                                    url: window.location.href
                                });
                            }
                        }
                    } catch (performanceError) {
                        console.error('Error monitoring page performance:', performanceError);
                    }
                }, { once: true, passive: true });
                
            } catch (monitoringError) {
                console.error('Error initializing performance monitoring:', monitoringError);
            }
        }
        
        /**
         * Monitor network connectivity
         * WHY: Handle offline/online states gracefully
         */
        function monitorConnectivity() {
            try {
                // Handle offline state
                window.addEventListener('offline', function() {
                    try {
                        showMessage('Conexão perdida. Algumas funcionalidades podem estar limitadas.', 'warning');
                    } catch (offlineError) {
                        console.error('Error handling offline state:', offlineError);
                    }
                }, { passive: true });
                
                // Handle online state
                window.addEventListener('online', function() {
                    try {
                        showMessage('Conexão restabelecida!', 'success');
                    } catch (onlineError) {
                        console.error('Error handling online state:', onlineError);
                    }
                }, { passive: true });
                
            } catch (connectivityError) {
                console.error('Error initializing connectivity monitoring:', connectivityError);
            }
        }
        
        /**
         * Main initialization function with comprehensive error handling
         * WHY: Ensures all security measures are properly initialized
         */
        function initializeApplication() {
            try {
                console.log('Initializing application...');
                
                // Initialize counters with error handling
                try {
                    initializeCounters();
                } catch (counterError) {
                    console.error('Failed to initialize counters:', counterError);
                    // Non-critical error, continue initialization
                }
                
                // Initialize form validation with error handling
                try {
                    initializeFormValidation();
                } catch (formError) {
                    console.error('Failed to initialize form validation:', formError);
                    // Try to initialize basic validation as fallback
                    try {
                        const searchForm = document.getElementById('searchForm');
                        if (searchForm) {
                            searchForm.addEventListener('submit', handleSearchFormValidation);
                        }
                    } catch (fallbackError) {
                        console.error('Fallback form validation failed:', fallbackError);
                    }
                }
                
                // Initialize error monitoring
                try {
                    initializeErrorMonitoring();
                } catch (monitoringError) {
                    console.error('Failed to initialize error monitoring:', monitoringError);
                }
                
                // Initialize performance monitoring
                try {
                    monitorPerformance();
                } catch (performanceError) {
                    console.error('Failed to initialize performance monitoring:', performanceError);
                }
                
                // Initialize connectivity monitoring
                try {
                    monitorConnectivity();
                } catch (connectivityError) {
                    console.error('Failed to initialize connectivity monitoring:', connectivityError);
                }
                
                console.log('Application initialization completed');
                
            } catch (error) {
                console.error('Critical error during application initialization:', error);
                
                // Show user-friendly error message
                try {
                    showMessage('Erro na inicialização da aplicação. Recarregue a página.', 'danger');
                } catch (messageError) {
                    console.error('Could not show initialization error message:', messageError);
                    // Last resort
                    alert('Erro na inicialização. Por favor, recarregue a página.');
                }
            }
        }
        
        /**
         * Initialize comprehensive error monitoring
         * WHY: Catches and handles various types of errors gracefully
         */
        function initializeErrorMonitoring() {
            // Global JavaScript error handler
            window.addEventListener('error', function(event) {
                console.error('Global JavaScript error:', {
                    message: event.message,
                    filename: event.filename,
                    lineno: event.lineno,
                    colno: event.colno,
                    error: event.error
                });
                
                // Show user-friendly error message for critical errors
                if (event.error && event.error.stack) {
                    showMessage('Ocorreu um erro inesperado. Tente novamente.', 'danger');
                }
                
                // Prevent default browser error handling for better UX
                return true;
            });
            
            // Unhandled promise rejection handler
            window.addEventListener('unhandledrejection', function(event) {
                console.error('Unhandled promise rejection:', event.reason);
                
                // Show user-friendly message for promise rejections
                showMessage('Erro de conectividade. Verifique sua conexão.', 'warning');
                
                // Prevent default browser handling
                event.preventDefault();
            });
            
            // Network connectivity monitoring
            window.addEventListener('online', function() {
                showMessage('Conexão restaurada!', 'success');
            });
            
            window.addEventListener('offline', function() {
                showMessage('Conexão perdida. Algumas funcionalidades podem não estar disponíveis.', 'warning');
            });
            
            // Page visibility change handler
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    // Page is hidden, pause non-critical operations
                    console.log('Page hidden, pausing operations');
                } else {
                    // Page is visible again, resume operations
                    console.log('Page visible, resuming operations');
                    
                    // Refresh counters when page becomes visible
                    try {
                        initializeCounters();
                    } catch (error) {
                        console.warn('Could not refresh counters on visibility change:', error);
                    }
                }
            });
        }
        
        // ===== EVENT LISTENERS =====
        document.addEventListener('DOMContentLoaded', initializeApplication);
        
        // Expose validation functions globally for external use
        window.LojaValidation = {
            sanitizeInput,
            validateInput,
            processUserInput,
            showValidationError
        };
    </script>
    
    @stack('scripts')
</body>
</html>
