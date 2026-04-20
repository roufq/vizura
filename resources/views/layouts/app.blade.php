<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/js/app.js') }}" defer></script>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <!-- Core Scripts (Global) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    @stack('styles')

    <style>
        [x-cloak] { display: none !important; }

        /* Select2 Premium Theme */
        .select2-container { width: 100% !important; }
        .select2-container--default .select2-selection--single {
            background-color: #f8fafc !important;
            border: 1px solid #f1f5f9 !important;
            border-radius: 12px !important;
            height: 40px !important;
            display: flex !important;
            align-items: center !important;
            transition: all 0.2s ease-in-out !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important;
            background-color: #fff !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1e293b !important;
            font-size: 11px !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.025em !important;
            padding-left: 1.25rem !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
            right: 12px !important;
        }
        .select2-dropdown {
            border: 1px solid #f1f5f9 !important;
            border-radius: 16px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05) !important;
            overflow: hidden !important;
            margin-top: 6px !important;
            z-index: 9999 !important;
        }
        .select2-search--dropdown { padding: 10px !important; }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #f1f5f9 !important;
            border-radius: 10px !important;
            background-color: #f8fafc !important;
            padding: 6px 12px !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            outline: none !important;
        }
        .select2-results__option {
            padding: 10px 16px !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            color: #64748b !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #6366f1 !important;
            color: #fff !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #f8fafc !important;
            color: #6366f1 !important;
            font-weight: 800 !important;
        }

        /* Custom Scrollbar Premium */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { 
            background: #e2e8f0; 
            border-radius: 10px; 
        }
        ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>
</head>

<body class="h-full text-slate-800 antialiased font-sans" x-data="{ sidebarOpen: false }">
    
    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition-opacity ease-linear duration-300" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 lg:hidden"
         @click="sidebarOpen = false"></div>

    <!-- Sidebar Wrapper -->
    <div class="fixed inset-y-0 left-0 w-72 bg-white border-r border-slate-200 z-50 transform transition-transform duration-300 lg:translate-x-0"
         :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        
        <!-- Logo & Header -->
        <div class="h-20 flex items-center px-8 border-b border-slate-50">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 bg-brand rounded-xl flex items-center justify-center shadow-lg shadow-brand/20">
                    <svg class="w-6 h-6 text-white" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M30 40L50 70L70 40" stroke="currentColor" stroke-width="12" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="50" cy="30" r="8" fill="currentColor"/>
                    </svg>
                </div>
                <span class="text-xl font-bold tracking-tight text-slate-900">{{ config('app.name') }}</span>
            </a>
            <button @click="sidebarOpen = false" class="ml-auto lg:hidden text-slate-400">
                <i class="fa fa-times text-lg"></i>
            </button>
        </div>

        <!-- User Profile Quick View -->
        <div class="px-6 py-8">
            <div class="bg-slate-50 rounded-2xl p-4 flex items-center gap-4 border border-slate-200/50">
                <div class="w-12 h-12 rounded-full bg-brand/10 border border-brand/20 flex items-center justify-center text-brand">
                    <i class="fa fa-user-circle text-2xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-900 truncate">{{ Auth::user()?->name }}</p>
                    <p class="text-[10px] uppercase tracking-wider text-slate-500 font-semibold">{{ Auth::user()?->roles->first()?->name ?? 'User' }}</p>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <div id="sidebar-scroll" class="px-4 pb-8 overflow-y-auto h-[calc(100vh-250px)] custom-scrollbar">
            <nav class="space-y-1">
                @php
                    $user = Auth::user();
                    $isOwner = $user?->hasRole('Owner');
                    $isManager = $user?->hasRole('Manager');
                    $isHeadStore = $user?->hasRole('HeadStore');
                    $isAdmin = $isOwner || $isManager || $isHeadStore;

                    $menuItems = [];
                    
                    // Dashboard & Active Location
                    $menuItems[] = ['route' => 'dashboard', 'icon' => 'home', 'label' => __('app.menu.dashboard')];
                    $menuItems[] = ['route' => 'locations.active', 'icon' => 'check-square-o', 'label' => __('app.menu.active_location')];

                    // Master Data (Owner/Manager/HeadStore)
                    if ($isAdmin) {
                        $menuItems[] = ['header' => __('app.menu.master_header')];
                        $menuItems[] = ['route' => 'products.index', 'icon' => 'cube', 'label' => __('app.menu.products')];
                        $menuItems[] = ['route' => 'categories.index', 'icon' => 'tags', 'label' => __('app.menu.categories')];
                        $menuItems[] = ['route' => 'units.index', 'icon' => 'balance-scale', 'label' => __('app.menu.units')];
                        $menuItems[] = ['route' => 'suppliers.index', 'icon' => 'address-book', 'label' => __('app.menu.suppliers')];
                        $menuItems[] = ['route' => 'customers.index', 'icon' => 'users', 'label' => __('app.menu.customers')];
                    }

                    // Warehouse (Owner/Manager/HeadStore)
                    if ($isAdmin) {
                        $menuItems[] = ['header' => __('app.menu.warehouse_header')];
                        $menuItems[] = ['route' => 'stock-adjustments.index', 'icon' => 'sliders', 'label' => __('app.menu.stock_adjustments')];
                        $menuItems[] = ['route' => 'stock-transfers.index', 'icon' => 'truck', 'label' => __('app.menu.stock_transfers')];
                    }

                    // Transactions
                    $menuItems[] = ['header' => __('app.menu.transaction_header')];
                    $menuItems[] = [
                        'label' => __('app.menu.sales'),
                        'icon' => 'shopping-cart',
                        'children' => [
                            ['route' => 'sales.create', 'label' => __('app.menu.pos_terminal')],
                            ['route' => 'sales.index', 'label' => __('app.menu.sales_history')],
                        ]
                    ];
                    
                    if ($isAdmin) {
                        $menuItems[] = ['route' => 'purchases.index', 'icon' => 'shopping-basket', 'label' => __('app.menu.purchases')];
                        $menuItems[] = ['route' => 'receivables.index', 'icon' => 'hand-holding-usd', 'label' => __('app.menu.receivables')];
                        $menuItems[] = ['route' => 'purchases.payables.index', 'icon' => 'credit-card', 'label' => __('app.menu.payables')];
                        $menuItems[] = ['route' => 'expenses.index', 'icon' => 'money', 'label' => __('app.menu.expenses')];
                    }

                    // Reports (Owner/Manager/HeadStore)
                    if ($isAdmin) {
                        $menuItems[] = ['header' => __('app.menu.report_header')];
                        $menuItems[] = ['route' => 'reports.sales', 'icon' => 'bar-chart', 'label' => __('app.menu.report_sales')];
                        $menuItems[] = ['route' => 'reports.stock', 'icon' => 'archive', 'label' => __('app.menu.report_stock')];
                        $menuItems[] = ['route' => 'reports.stock-card', 'icon' => 'history', 'label' => __('app.menu.report_stock_card')];
                        $menuItems[] = ['route' => 'reports.income-statement', 'icon' => 'pie-chart', 'label' => __('app.menu.report_income')];
                    }

                    // Settings
                    $menuItems[] = ['header' => __('app.menu.setting_header')];
                    if ($isOwner || $isManager) {
                        $menuItems[] = ['route' => 'users.index', 'icon' => 'users', 'label' => __('app.menu.users')];
                        $menuItems[] = ['route' => 'locations.index', 'icon' => 'map-marker', 'label' => __('app.menu.locations')];
                    }
                    if ($isOwner) {
                        $menuItems[] = ['route' => 'manager-locations.index', 'icon' => 'sitemap', 'label' => __('app.menu.manager_access')];
                    }
                    $menuItems[] = ['route' => 'profile.edit', 'icon' => 'cog', 'label' => __('app.menu.profile')];
                @endphp

                @foreach ($menuItems as $item)
                    @if (isset($item['header']))
                        <div class="pt-6 pb-2 px-4">
                            <h5 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">{{ $item['header'] }}</h5>
                        </div>
                    @elseif (isset($item['children']))
                        @php 
                            $isChildActive = false;
                            foreach ($item['children'] as $child) {
                                if (request()->routeIs($child['route'])) {
                                    $isChildActive = true;
                                    break;
                                }
                            }
                        @endphp
                        <div x-data="{ open: {{ $isChildActive ? 'true' : 'false' }} }">
                            <button @click="open = !open" 
                                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group {{ $isChildActive ? 'bg-slate-50 text-slate-900 border border-slate-100' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                                <div class="w-5 flex justify-center">
                                    <i class="fa fa-{{ $item['icon'] }} {{ $isChildActive ? 'text-brand' : 'opacity-70 group-hover:opacity-100' }}"></i>
                                </div>
                                <span class="text-sm font-medium">{{ $item['label'] }}</span>
                                <div class="ml-auto transition-transform duration-200" :class="open ? 'rotate-90' : ''">
                                    <i class="fa fa-angle-right text-[10px] opacity-40"></i>
                                </div>
                            </button>
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100" 
                                 x-transition:enter-start="transform opacity-0 scale-95" 
                                 x-transition:enter-end="transform opacity-100 scale-100" 
                                 class="pl-12 pr-4 space-y-1 mt-1 mb-2">
                                @foreach ($item['children'] as $child)
                                    @php $isSubActive = request()->routeIs($child['route']); @endphp
                                    <a href="{{ route($child['route']) }}" 
                                       class="flex items-center gap-2 py-2 text-xs font-medium transition-all {{ $isSubActive ? 'text-brand font-bold' : 'text-slate-400 hover:text-slate-700' }}">
                                        @if($isSubActive)
                                            <div class="w-1.5 h-1.5 rounded-full bg-brand"></div>
                                        @else
                                            <div class="w-1.5 h-1.5 rounded-full bg-slate-200"></div>
                                        @endif
                                        {{ $child['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        @php $isActive = request()->routeIs($item['route']); @endphp
                        <a href="{{ route($item['route']) }}" 
                           class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group {{ $isActive ? 'bg-brand/10 text-brand ring-1 ring-brand/20' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                            <div class="w-5 flex justify-center">
                                <i class="fa fa-{{ $item['icon'] }} {{ $isActive ? '' : 'opacity-70 group-hover:opacity-100' }}"></i>
                            </div>
                            <span class="text-sm font-medium">{{ $item['label'] }}</span>
                            @if ($isActive)
                                <div class="ml-auto w-1.5 h-1.5 rounded-full bg-brand"></div>
                            @endif
                        </a>
                    @endif
                @endforeach
            </nav>
        </div>


    </div>

    <!-- Main Content Area -->
    <div class="lg:pl-72 flex flex-col min-h-screen">
        
        <!-- Top Navigation -->
        <header class="h-20 bg-white/80 backdrop-blur-md sticky top-0 border-b border-slate-200 px-8 flex items-center justify-between z-30">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true" class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                    <i class="fa fa-bars"></i>
                </button>
                <h2 class="text-xl font-bold text-slate-900">@yield('page-title', __('app.dashboard'))</h2>
            </div>

            <div class="flex items-center gap-4">
                <!-- Search or Notifications could go here -->
                <div class="hidden sm:flex items-center gap-2 px-4 py-2 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-xs font-semibold text-slate-600">{{ __('app.live_server') }}</span>
                </div>

                <div class="h-10 w-px bg-slate-200 mx-2"></div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-10 h-10 flex items-center justify-center rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors" title="{{ __('app.logout') }}">
                        <i class="fa fa-power-off"></i>
                    </button>
                </form>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-8">
            <!-- Flash Messages -->
            @if (session('status'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center gap-3 text-emerald-700">
                    <i class="fa fa-check-circle"></i>
                    <p class="text-sm font-medium">{{ session('status') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border border-rose-100 rounded-2xl">
                    <div class="flex items-center gap-3 text-rose-700 mb-2">
                        <i class="fa fa-exclamation-circle"></i>
                        <p class="text-sm font-bold">{{ __('app.error_occurred') }}:</p>
                    </div>
                    <ul class="list-disc list-inside text-sm text-rose-600 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="p-8 border-t border-slate-200 flex flex-col md:flex-row justify-between items-center bg-white gap-4 text-slate-400 text-sm italic">
            <p>&copy; {{ now()->year }} {{ config('app.name') }}. {{ __('app.footer_tagline') }}</p>
            <div class="flex gap-6">
                <a href="#" class="hover:text-brand">{{ __('app.help_center') }}</a>
                <a href="#" class="hover:text-brand">{{ __('app.terms') }}</a>
                <a href="#" class="hover:text-brand">{{ __('app.privacy') }}</a>
            </div>
        </footer>
    </div>

    <!-- Legacy/Other Scripts -->
    <script src="{{ asset('assets/plugin/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/plugin/waves/waves.min.js') }}"></script>
    <script src="{{ asset('assets/plugin/sweet-alert/sweetalert.min.js') }}"></script>
    
    <script>
        (function($) {
            $(document).ready(function() {
                const initSelect2 = () => {
                    if (typeof $.fn.select2 === 'undefined') return;
                    
                    $('select:not(.no-select2)').each(function() {
                        const $el = $(this);
                        if (!$el.hasClass('select2-hidden-accessible')) {
                            $el.select2({
                                width: '100%',
                                placeholder: $el.attr('placeholder') || '{{ __('app.choose') }}...',
                                allowClear: true
                            });
                        }
                    });
                };

                initSelect2();

                // Re-init on dynamic changes (Alpine/Livewire)
                document.addEventListener('alpine:initialized', initSelect2);
                $(document).on('select2-reinit', initSelect2);

                // Sidebar Scroll Persistence
                const sidebarScroll = document.getElementById('sidebar-scroll');
                if (sidebarScroll) {
                    // Restore position
                    const savedPosition = localStorage.getItem('sidebar-scroll-position');
                    if (savedPosition) {
                        sidebarScroll.scrollTop = parseInt(savedPosition, 10);
                    }

                    // Save position before navigation
                    window.addEventListener('beforeunload', () => {
                        localStorage.setItem('sidebar-scroll-position', sidebarScroll.scrollTop);
                    });
                }
            });
        })(window.jQuery || $);
    </script>
    
    @stack('scripts')
</body>
</html>
