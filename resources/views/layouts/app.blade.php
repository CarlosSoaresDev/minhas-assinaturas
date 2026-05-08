<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ Auth::check() ? Auth::user()->theme : 'dark' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Minhas Assinaturas') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        html, body {
            overflow-x: hidden;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        /* Brute force para garantir que o azul do Bootstrap não apareça */
        html[data-bs-theme="dark"],
        html[data-bs-theme="dark"] body {
            background-color: #0b0e14 !important;
            color: #f1f5f9 !important;
        }

        [data-bs-theme="dark"] {
            --bs-body-bg: #0b0e14 !important;
            --bs-body-color: #f1f5f9 !important;
            --bs-card-bg: #111827 !important;
            --bs-border-color: #1e293b !important;
            --bs-tertiary-bg: #0f172a !important;
            --bs-secondary-bg: #1f2937 !important;
        }

        [data-bs-theme="light"] {
            --bs-body-bg: #f8fafc !important;
            --bs-body-color: #1e293b !important;
            --bs-card-bg: #ffffff !important;
            --bs-border-color: #e2e8f0 !important;
            --bs-tertiary-bg: #f1f5f9 !important;
            --bs-secondary-bg: #f8fafc !important;
        }

        body {
            font-family: 'Inter', sans-serif;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .navbar {
            background-color: var(--bs-body-bg) !important;
        }

        .navbar-brand-custom {
            font-weight: 700;
            font-size: 1.15rem;
            letter-spacing: -0.3px;
        }
        .nav-link-top {
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.5rem 1rem !important;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .nav-link-top:hover {
            background: rgba(13, 110, 253, 0.15);
        }
        .nav-link-top.active {
            background: var(--bs-primary) !important;
            color: #fff !important;
        }
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* Desktop Spacing Adjustments */
        @media (min-width: 1200px) {
            .main-container {
                padding: 2rem 5rem;
            }
            .navbar .container-fluid {
                padding-left: 5rem !important;
                padding-right: 5rem !important;
            }
        }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem 0.75rem 5rem 0.75rem; /* Aumentado padding bottom para o FAB */
            }
            .navbar-brand-custom {
                font-size: 1rem;
            }
            /* Garante que o ícone fique alinhado verticalmente */
            .d-flex.align-items-center i {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                vertical-align: middle;
            }
        }

        /* Responsive Table to Cards */
        @media (max-width: 768px) {
            .responsive-table thead {
                display: none;
            }
            .responsive-table, .responsive-table tbody, .responsive-table tr, .responsive-table td {
                display: block;
                width: 100%;
            }
            .responsive-table tr {
                margin-bottom: 1.25rem;
                border: 1px solid var(--bs-border-color);
                border-radius: 16px;
                padding: 0.5rem 0; /* Padding vertical apenas */
                background-color: var(--bs-card-bg);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                margin-left: 0.25rem;
                margin-right: 0.25rem;
                display: block;
            }
            .responsive-table td {
                text-align: left;
                padding: 0.85rem 1.25rem !important; /* Padding horizontal generoso */
                border: none;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
                width: 100% !important;
                box-sizing: border-box;
            }
            .responsive-table td:last-child {
                border-bottom: none;
                padding-top: 1.25rem !important;
            }
            .responsive-table td::before {
                content: attr(data-label);
                font-weight: 600;
                font-size: 0.7rem;
                text-transform: uppercase;
                color: #94a3b8;
                margin-right: 1rem;
            }
            /* Caso específico onde queremos que o conteúdo ocupe toda a linha se não houver label */
            .responsive-table td:not([data-label]) {
                display: block;
            }
        }
    </style>
</head>
<body class="bg-body text-body">
    <style>
        /* PADRONIZAÇÃO GLOBAL: Ícones e Textos sempre centralizados */
        .bi {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 1.1em;
            height: 1.1em;
            line-height: 1 !important;
            vertical-align: middle;
            text-align: center;
            flex-shrink: 0;
            margin: 0 !important; /* Remove qualquer margem que possa deslocar o ícone */
        }

        /* Força botões, badges e links a serem flex-containers centralizados */
        .btn, .badge, .nav-link, .navbar-brand, .list-group-item, .card-header, .input-group-text, .alert {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0.5rem;
            line-height: 1 !important;
            text-decoration: none;
        }

        /* Garante que o texto dentro desses elementos não tenha altura de linha que o empurre */
        .btn span, .badge span, .nav-link span, .navbar-brand span, .input-group-text span, .alert span, h1, h2, h3, h4, h5, h6 {
            line-height: 1 !important;
            margin: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Correção específica para botões circulares de ação */
        .rounded-circle.btn, .rounded-circle.btn-sm {
            width: 36px !important;
            height: 36px !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex-shrink: 0;
        }
        
        .rounded-circle.btn-sm {
            width: 32px !important;
            height: 32px !important;
        }

        /* Força o fundo preto em todos os níveis */
        html[data-bs-theme="dark"],
        html[data-bs-theme="dark"] body,
        html[data-bs-theme="dark"] main,
        html[data-bs-theme="dark"] .container-fluid {
            background-color: #0b0e14 !important;
        }

        /* Corrige os botões de paginação brancos - SELETOR UNIVERSAL NA AREA */
        nav[role="navigation"] *,
        .pagination * {
            background-color: transparent !important;
            color: #10b981 !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        /* Destaque para o item ativo */
        nav[role="navigation"] .active *,
        .pagination .active * {
            background-color: #10b981 !important;
            color: #ffffff !important;
        }

        /* Botões Pill que viram ícone no mobile */
        .btn-pill-responsive {
            padding-left: 1.5rem !important;
            padding-right: 1.5rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        @media (max-width: 576px) {
            .btn-pill-responsive {
                padding-left: 0.8rem !important;
                padding-right: 0.8rem !important;
                min-width: 46px;
                height: 40px;
            }
        }
    </style>

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg border-bottom shadow-sm">
        <div class="container-fluid px-3 px-lg-5">
            <!-- Brand -->
            <a class="navbar-brand navbar-brand-custom d-inline-flex align-items-center gap-2 text-body py-2" href="{{ route('dashboard') }}" style="line-height: 1;">
                <i class="bi bi-shield-check text-primary fs-4" style="transform: translateY(2px);"></i>
                <span class="ms-1">Minhas Assinaturas</span>
            </a>

            <!-- Mobile Toggle -->
            <div class="d-flex align-items-center gap-3 d-lg-none">
                <livewire:notifications.bell />
                <button class="navbar-toggler border-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <!-- Navbar Content -->
            <div class="collapse navbar-collapse" id="mainNavbar">
                <!-- Main Navigation -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1 mt-3 mt-lg-0">
                    <li class="nav-item">
                        <a class="nav-link nav-link-top {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i> Painel
                        </a>
                    </li>
                    @if(auth()->user()->hasRole('admin') && session('admin_mode', true))
                        <li class="nav-item">
                            <a class="nav-link nav-link-top {{ request()->routeIs('admin.users.*') || request()->is('usuarios') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                <i class="bi bi-people me-1"></i> Usuários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-top {{ request()->routeIs('admin.services') ? 'active' : '' }}" href="{{ route('admin.services') }}">
                                <i class="bi bi-layers me-1"></i> Serviços
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link nav-link-top dropdown-toggle {{ request()->is('admin/categorias*') || request()->is('logs*') || request()->is('admin/alertas*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-gear me-1"></i> Configurações
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark shadow border-secondary">
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('admin.categories') }}">
                                        <i class="bi bi-tags me-2"></i> Categorias
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('admin.logs') }}">
                                        <i class="bi bi-journal-text me-2"></i> Logs de Atividade
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('admin.alerts') }}">
                                        <i class="bi bi-send-check me-2"></i> Disparo de Alertas
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link nav-link-top {{ request()->routeIs('front.subscriptions.*') || request()->is('subscriptions') ? 'active' : '' }}" href="{{ route('front.subscriptions.index') }}">
                                <i class="bi bi-card-checklist me-1"></i> Assinaturas
                            </a>
                        </li>
                    @endif
                </ul>

                <!-- Right Side -->
                <ul class="navbar-nav align-items-lg-center gap-2 mb-3 mb-lg-0">
                    @if(auth()->user()->hasRole('admin'))
                        <li class="nav-item d-flex align-items-center me-2">
                            <livewire:layout.admin-mode-toggle />
                        </li>
                    @endif

                    <!-- Bell (apenas desktop) -->
                    <li class="nav-item d-none d-lg-block">
                        <livewire:notifications.bell />
                    </li>

                    <!-- Divisor vertical desktop -->
                    <li class="nav-item d-none d-lg-block">
                        <span class="text-secondary">|</span>
                    </li>

                    <!-- Minha Conta -->
                    <li class="nav-item">
                        <a class="nav-link nav-link-top {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                            <i class="bi bi-person-gear me-1"></i> Minha Conta
                        </a>
                    </li>

                    <!-- Theme Toggle -->
                    <li class="nav-item">
                        <livewire:layout.theme-toggle />
                    </li>

                    <!-- Sair -->
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button class="nav-link nav-link-top text-danger border-0 bg-transparent" type="submit">
                                <i class="bi bi-box-arrow-right me-1"></i> Sair
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-container">
        {{ $slot }}
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            // Sincroniza o tema inicial
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            applyGlobalStyles(currentTheme);

            Livewire.on('theme-updated', (event) => {
                document.documentElement.setAttribute('data-bs-theme', event.theme);
                applyGlobalStyles(event.theme);
            });
        });

        function applyGlobalStyles(theme) {
            const isDark = theme === 'dark';
            document.body.style.backgroundColor = isDark ? '#0b0e14' : '#f8fafc';

            // Força estilos em elementos que teimam em ficar azuis ou invisíveis
            let styleTag = document.getElementById('force-theme-styles');
            if (!styleTag) {
                styleTag = document.createElement('style');
                styleTag.id = 'force-theme-styles';
                document.head.appendChild(styleTag);
            }

            styleTag.innerHTML = `
                html[data-bs-theme="dark"], html[data-bs-theme="dark"] body {
                    background-color: #0b0e14 !important;
                }
                .pagination .page-link {
                    background-color: ${isDark ? '#111827' : '#ffffff'} !important;
                    color: ${isDark ? '#f1f5f9' : '#1e293b'} !important;
                    border-color: ${isDark ? '#1e293b' : '#dee2e6'} !important;
                }
                .pagination .page-item.active .page-link {
                    background-color: #10b981 !important;
                    border-color: #10b981 !important;
                    color: white !important;
                }
                .pagination .page-item.disabled .page-link {
                    background-color: ${isDark ? '#0b0e14' : '#f8fafc'} !important;
                    opacity: 0.5;
                }
            `;
        }

        // Executa imediatamente para evitar o "flash" azul
        applyGlobalStyles(document.documentElement.getAttribute('data-bs-theme') || 'dark');
    </script>
</body>
</html>
