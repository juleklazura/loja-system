<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin - ' . config('app.name'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bun        // Notifications management
        let readNotifications = JSON.parse(localStorage.getItem('readNotifications') || '[]');
        
        function markNotificationAsRead(notificationId) {
            if (!readNotifications.includes(notificationId)) {
                readNotifications.push(notificationId);
                localStorage.setItem('readNotifications', JSON.stringify(readNotifications));
                
                // Update counter immediately
                updateNotificationCounter();
            }
        }
        
        function clearAllReadNotifications() {
            readNotifications = [];
            localStorage.removeItem('readNotifications');
            loadNotifications();
        }
        
        // Expose function globally for debugging
        window.clearNotifications = clearAllReadNotifications; <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @stack('styles')

    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f8f9fc;
        }
        
        .sidebar {
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            min-height: 100vh;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.35rem;
            margin: 0.1rem 0;
        }
        
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .main-content {
            margin-left: 250px;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
            .sidebar {
                position: fixed;
                top: 0;
                left: -250px;
                width: 250px;
                height: 100vh;
                z-index: 1000;
                transition: left 0.3s;
            }
            .sidebar.show {
                left: 0;
            }
        }
        
        .navbar-brand {
            font-weight: 600;
            color: #4e73df !important;
        }
        
        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: none;
        }
        
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        
        /* Estilos globais para paginação */
        .pagination {
            margin: 0;
        }
        
        .pagination .page-link {
            color: #4e73df;
            background-color: #fff;
            border: 1px solid #e3e6f0;
            padding: 0.5rem 0.75rem;
            margin: 0 0.125rem;
            font-size: 0.875rem;
            border-radius: 0.35rem;
            transition: all 0.15s ease-in-out;
        }
        
        .pagination .page-link:hover {
            color: #2e59d9;
            background-color: #f8f9fc;
            border-color: #d1d3e2;
            transform: translateY(-1px);
        }
        
        .pagination .page-item.active .page-link {
            background-color: #4e73df;
            border-color: #4e73df;
            color: #fff;
        }
        
        .pagination .page-item.disabled .page-link {
            color: #858796;
            background-color: #fff;
            border-color: #e3e6f0;
            cursor: not-allowed;
            opacity: 0.65;
        }
        
        .pagination-info {
            color: #5a5c69;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar position-fixed d-flex flex-column p-3" style="width: 250px;">
            <div class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <span class="fs-4 fw-bold">
                    <i class="fas fa-store me-2"></i>
                    Admin Panel
                </span>
            </div>
            <hr class="text-white">
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                        <i class="fas fa-box me-2"></i>
                        Produtos
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <i class="fas fa-tags me-2"></i>
                        Categorias
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Pedidos
                    </a>
                </li>
                                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                <i class="fas fa-users"></i> Usuários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
                                <i class="fas fa-chart-bar"></i> Relatórios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
                                <i class="fas fa-cog"></i> Configurações
                            </a>
                        </li>
            </ul>
            <hr class="text-white">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-2"></i>
                    <strong>{{ auth()->user()->name }}</strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                    <li><a class="dropdown-item" href="{{ route('home') }}" target="_blank">
                        <i class="fas fa-store me-2"></i>Ver Loja
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="fas fa-user me-2"></i>Perfil
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item" type="submit">
                                <i class="fas fa-sign-out-alt me-2"></i>Sair
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content flex-grow-1">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
                <div class="container-fluid">
                    <button class="btn btn-link d-md-none" type="button" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="navbar-nav ms-auto">
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" id="notificationDropdown">
                                <i class="fas fa-bell"></i>
                                <span class="badge bg-danger" id="notificationCount">0</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" id="notificationList" style="min-width: 350px; max-height: 400px; overflow-y: auto;">
                                <li class="dropdown-header">
                                    <i class="fas fa-spinner fa-spin"></i> Carregando notificações...
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main>
                @if(session('success'))
                    <div class="container-fluid">
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="container-fluid">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="container-fluid">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')

    <script>
        // Notifications System
        async function loadNotifications() {
            try {
                const response = await fetch('{{ route("admin.notifications.get") }}');
                const data = await response.json();
                
                // Filter out read notifications
                const unreadNotifications = data.notifications.filter((notification, index) => {
                    const notificationId = `${notification.type}-${index}`;
                    return !readNotifications.includes(notificationId);
                });
                
                // Update notification count (only unread)
                const countElement = document.getElementById('notificationCount');
                const listElement = document.getElementById('notificationList');
                
                if (unreadNotifications.length > 0) {
                    countElement.textContent = unreadNotifications.length;
                    countElement.style.display = 'inline';
                } else {
                    countElement.style.display = 'none';
                }
                
                // Update notification list (show all notifications, but mark read ones)
                if (data.notifications.length > 0) {
                    listElement.innerHTML = '';
                    
                    data.notifications.forEach((notification, index) => {
                        const notificationId = `${notification.type}-${index}`;
                        const isRead = readNotifications.includes(notificationId);
                        const listItem = document.createElement('li');
                        listItem.innerHTML = `
                            <a class="dropdown-item py-2 notification-item ${isRead ? 'text-muted' : ''}" 
                               href="${notification.url}" 
                               data-notification-id="${notificationId}"
                               data-notification-type="${notification.type}">
                                <div class="d-flex align-items-start">
                                    <div class="me-3">
                                        <i class="${notification.icon} text-${notification.color} ${isRead ? 'opacity-50' : ''}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold ${isRead ? 'text-muted' : ''}">${notification.title}</h6>
                                        <p class="mb-1 small text-muted">${notification.message}</p>
                                        <small class="text-muted">${notification.time}</small>
                                    </div>
                                    ${isRead ? '<div class="ms-2"><small class="text-success"><i class="fas fa-check"></i></small></div>' : ''}
                                </div>
                            </a>
                        `;
                        listElement.appendChild(listItem);
                    });
                    
                    // Add click event listeners to notification items
                    document.querySelectorAll('.notification-item').forEach(item => {
                        item.addEventListener('click', function(e) {
                            e.preventDefault();
                            
                            const notificationId = this.getAttribute('data-notification-id');
                            const url = this.getAttribute('href');
                            
                            // Mark notification as read
                            markNotificationAsRead(notificationId);
                            
                            // Navigate to the URL after a short delay
                            setTimeout(() => {
                                window.location.href = url;
                            }, 100);
                        });
                    });
                    
                    // Add divider and actions
                    const divider = document.createElement('li');
                    divider.innerHTML = '<hr class="dropdown-divider">';
                    listElement.appendChild(divider);
                    
                    const markAllRead = document.createElement('li');
                    markAllRead.innerHTML = '<a class="dropdown-item text-center text-success" href="#" id="markAllRead"><small><i class="fas fa-check-double"></i> Marcar todas como lidas</small></a>';
                    listElement.appendChild(markAllRead);
                    
                    // Add click event for mark all as read
                    document.getElementById('markAllRead').addEventListener('click', function(e) {
                        e.preventDefault();
                        data.notifications.forEach((notification, index) => {
                            const notificationId = `${notification.type}-${index}`;
                            markNotificationAsRead(notificationId);
                        });
                        loadNotifications(); // Reload to update the display
                    });
                } else {
                    listElement.innerHTML = '<li class="dropdown-header text-muted">Nenhuma notificação</li>';
                }
                
            } catch (error) {
                console.error('Erro ao carregar notificações:', error);
                document.getElementById('notificationList').innerHTML = '<li class="dropdown-header text-danger">Erro ao carregar</li>';
            }
        }
        
        // Notifications management
        let readNotifications = JSON.parse(localStorage.getItem('readNotifications') || '[]');
        
        function markNotificationAsRead(notificationId) {
            if (!readNotifications.includes(notificationId)) {
                readNotifications.push(notificationId);
                localStorage.setItem('readNotifications', JSON.stringify(readNotifications));
                
                // Update counter immediately
                updateNotificationCounter();
            }
        }
        
        function updateNotificationCounter() {
            // Reload notifications to update the counter
            loadNotifications();
        }
        
        // Load notifications on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadNotifications();
            
            // Reload notifications every 30 seconds
            setInterval(loadNotifications, 30000);
        });
        
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('show');
        }

        // Close sidebar on mobile when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const toggleBtn = document.querySelector('[onclick="toggleSidebar()"]');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !toggleBtn.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        });
    </script>
</body>
</html>
