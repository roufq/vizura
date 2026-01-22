<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
	<meta name="description" content="">
	<meta name="author" content="">

	<title>@yield('title', config('app.name'))</title>

	<link rel="stylesheet" href="{{ asset('assets/styles/style.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/plugin/mCustomScrollbar/jquery.mCustomScrollbar.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/plugin/waves/waves.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/plugin/sweet-alert/sweetalert.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/plugin/percircle/css/percircle.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/plugin/chart/chartist/chartist.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/plugin/fullcalendar/fullcalendar.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/plugin/fullcalendar/fullcalendar.print.css') }}" media="print">
	<link rel="stylesheet" href="{{ asset('assets/plugin/select2/css/select2.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/color-switcher/color-switcher.min.css') }}">
</head>

<body>
<div class="main-menu">
	<header class="header">
		<a href="{{ route('dashboard') }}" class="logo">{{ config('app.name') }}</a>
		<button type="button" class="button-close fa fa-times js__menu_close"></button>
		<div class="user">
			<a href="#" class="avatar"><img src="http://placehold.it/80x80" alt=""><span class="status online"></span></a>
			<h5 class="name">
				<a href="{{ route('profile.edit') }}">{{ Auth::user()?->name ?? 'User' }}</a>
			</h5>
			<h5 class="position">{{ Auth::user()?->roles->first()?->name ?? 'Pengguna' }}</h5>
			<div class="control-wrap js__drop_down">
				<i class="fa fa-caret-down js__drop_down_button"></i>
				<div class="control-list">
					<div class="control-item"><a href="{{ route('profile.edit') }}"><i class="fa fa-user"></i> Profile</a></div>
					<div class="control-item">
						<form method="POST" action="{{ route('logout') }}">
							@csrf
							<button type="submit" class="btn btn-link"><i class="fa fa-sign-out"></i> Log out</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</header>
	<div class="content">
		<div class="navigation">
			<h5 class="title">Navigation</h5>
			<ul class="menu js__accordion">
				<li class="{{ request()->routeIs('dashboard') ? 'current' : '' }}">
					<a class="waves-effect" href="{{ route('dashboard') }}"><i class="menu-icon fa fa-home"></i><span>Dashboard</span></a>
				</li>
				<li class="{{ request()->routeIs('locations.*') ? 'current' : '' }}">
					<a class="waves-effect" href="{{ route('locations.index') }}"><i class="menu-icon fa fa-map-marker"></i><span>Lokasi</span></a>
				</li>
				<li class="{{ request()->routeIs('locations.active') ? 'current' : '' }}">
					<a class="waves-effect" href="{{ route('locations.active') }}"><i class="menu-icon fa fa-check-square-o"></i><span>Pilih Lokasi</span></a>
				</li>
				@if (Auth::user()?->hasRole('Owner') || Auth::user()?->hasRole('Manager'))
					<li class="{{ request()->routeIs('users.*') ? 'current' : '' }}">
						<a class="waves-effect" href="{{ route('users.index') }}"><i class="menu-icon fa fa-users"></i><span>Pengguna</span></a>
					</li>
				@endif
				@if (Auth::user()?->hasRole('Owner'))
					<li class="{{ request()->routeIs('manager-locations.*') ? 'current' : '' }}">
						<a class="waves-effect" href="{{ route('manager-locations.index') }}"><i class="menu-icon fa fa-sitemap"></i><span>Akses Lokasi Manager</span></a>
					</li>
				@endif
				<li class="{{ request()->routeIs('products.*') ? 'current' : '' }}">
					<a class="waves-effect" href="{{ route('products.index') }}"><i class="menu-icon fa fa-cube"></i><span>Produk</span></a>
				</li>
				<li class="{{ request()->routeIs('categories.*') ? 'current' : '' }}">
					<a class="waves-effect" href="{{ route('categories.index') }}"><i class="menu-icon fa fa-tags"></i><span>Kategori</span></a>
				</li>
				<li class="{{ request()->routeIs('units.*') ? 'current' : '' }}">
					<a class="waves-effect" href="{{ route('units.index') }}"><i class="menu-icon fa fa-balance-scale"></i><span>Satuan</span></a>
				</li>
				<li class="{{ request()->routeIs('stock-adjustments.*') ? 'current' : '' }}">
					<a class="waves-effect" href="{{ route('stock-adjustments.index') }}"><i class="menu-icon fa fa-exchange"></i><span>Penyesuaian Stok</span></a>
				</li>
				<li class="{{ request()->routeIs('stock-transfers.*') ? 'current' : '' }}">
					<a class="waves-effect" href="{{ route('stock-transfers.index') }}"><i class="menu-icon fa fa-truck"></i><span>Transfer Stok</span></a>
				</li>
				<li class="{{ request()->routeIs('purchases.*') ? 'current' : '' }}">
					<a class="waves-effect" href="{{ route('purchases.index') }}"><i class="menu-icon fa fa-shopping-cart"></i><span>Pembelian</span></a>
				</li>
				<li class="{{ request()->routeIs('purchases.payables.*') ? 'current' : '' }}">
					<a class="waves-effect" href="{{ route('purchases.payables.index') }}"><i class="menu-icon fa fa-credit-card"></i><span>Pelunasan Hutang</span></a>
				</li>
				<li class="{{ request()->routeIs('receivables.*') ? 'current' : '' }}">
					<a class="waves-effect" href="{{ route('receivables.index') }}"><i class="menu-icon fa fa-handshake-o"></i><span>Pelunasan Piutang</span></a>
				</li>
				<li class="{{ request()->routeIs('expenses.*') ? 'current' : '' }}">
					<a class="waves-effect" href="{{ route('expenses.index') }}"><i class="menu-icon fa fa-credit-card"></i><span>Biaya Operasional</span></a>
				</li>
				<li class="{{ request()->routeIs('suppliers.*') ? 'current' : '' }}">
					<a class="waves-effect" href="{{ route('suppliers.index') }}"><i class="menu-icon fa fa-address-book"></i><span>Supplier</span></a>
				</li>
				<li class="{{ request()->routeIs('sales.*') ? 'current' : '' }}">
					<a class="waves-effect" href="{{ route('sales.index') }}"><i class="menu-icon fa fa-cash-register"></i><span>Penjualan (POS)</span></a>
				</li>
				<li class="{{ request()->routeIs('reports.sales') ? 'current' : '' }}">
					<a class="waves-effect" href="{{ route('reports.sales') }}"><i class="menu-icon fa fa-line-chart"></i><span>Laporan Penjualan</span></a>
				</li>
				<li class="{{ request()->routeIs('reports.cash-up') ? 'current' : '' }}">
					<a class="waves-effect" href="{{ route('reports.cash-up') }}"><i class="menu-icon fa fa-money"></i><span>Laporan Kas Harian</span></a>
				</li>
				<li class="{{ request()->routeIs('reports.income-statement') ? 'current' : '' }}">
					<a class="waves-effect" href="{{ route('reports.income-statement') }}"><i class="menu-icon fa fa-area-chart"></i><span>Laporan Laba Rugi</span></a>
				</li>
				<li class="{{ request()->routeIs('reports.cash-flow') ? 'current' : '' }}">
					<a class="waves-effect" href="{{ route('reports.cash-flow') }}"><i class="menu-icon fa fa-exchange"></i><span>Laporan Arus Kas</span></a>
				</li>
				<li class="{{ request()->routeIs('reports.stock') ? 'current' : '' }}">
					<a class="waves-effect" href="{{ route('reports.stock') }}"><i class="menu-icon fa fa-archive"></i><span>Laporan Stok</span></a>
				</li>
				<li class="{{ request()->routeIs('reports.stock-card') ? 'current' : '' }}">
					<a class="waves-effect" href="{{ route('reports.stock-card') }}"><i class="menu-icon fa fa-book"></i><span>Kartu Stok</span></a>
				</li>
				<li class="{{ request()->routeIs('profile.edit') ? 'current' : '' }}">
					<a class="waves-effect" href="{{ route('profile.edit') }}"><i class="menu-icon fa fa-user"></i><span>Profile</span></a>
				</li>
			</ul>
		</div>
	</div>
</div>

<div class="fixed-navbar">
	<div class="pull-left">
		<button type="button" class="menu-mobile-button glyphicon glyphicon-menu-hamburger js__menu_mobile"></button>
		<h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
	</div>
	<div class="pull-right">
		<div class="ico-item">
			<a href="#" class="ico-item fa fa-search js__toggle_open" data-target="#searchform-header"></a>
			<form action="#" id="searchform-header" class="searchform js__toggle">
				<input type="search" placeholder="Search..." class="input-search">
				<button class="fa fa-search button-search" type="submit"></button>
			</form>
		</div>
		<div class="ico-item fa fa-arrows-alt js__full_screen"></div>
		<form method="POST" action="{{ route('logout') }}" class="ico-item">
			@csrf
			<button type="submit" class="fa fa-power-off js__logout"></button>
		</form>
	</div>
</div>

<div id="wrapper">
	<div class="main-content">
		@hasSection('content')
			@yield('content')
		@elseif (isset($slot))
			{{ $slot }}
		@endif
		<footer class="footer">
			<ul class="list-inline">
				<li>{{ now()->year }} {{ config('app.name') }}.</li>
				<li><a href="#">Privacy</a></li>
				<li><a href="#">Terms</a></li>
				<li><a href="#">Help</a></li>
			</ul>
		</footer>
	</div>
</div>

<!--[if lt IE 9]>
	<script src="{{ asset('assets/script/html5shiv.min.js') }}"></script>
	<script src="{{ asset('assets/script/respond.min.js') }}"></script>
<![endif]-->
<script src="{{ asset('assets/scripts/jquery.min.js') }}"></script>
<script src="{{ asset('assets/scripts/modernizr.min.js') }}"></script>
<script src="{{ asset('assets/plugin/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/plugin/mCustomScrollbar/jquery.mCustomScrollbar.concat.min.js') }}"></script>
<script src="{{ asset('assets/plugin/nprogress/nprogress.js') }}"></script>
<script src="{{ asset('assets/plugin/sweet-alert/sweetalert.min.js') }}"></script>
<script src="{{ asset('assets/plugin/waves/waves.min.js') }}"></script>
<script src="{{ asset('assets/plugin/fullscreen/jquery.fullscreen-min.js') }}"></script>
<script src="{{ asset('assets/plugin/percircle/js/percircle.js') }}"></script>
<script src="{{ asset('assets/plugin/chart/chartist/chartist.min.js') }}"></script>
<script src="{{ asset('assets/scripts/chart.chartist.init.min.js') }}"></script>
<script src="{{ asset('assets/plugin/moment/moment.js') }}"></script>
<script src="{{ asset('assets/plugin/fullcalendar/fullcalendar.min.js') }}"></script>
<script src="{{ asset('assets/scripts/fullcalendar.init.js') }}"></script>
<script src="{{ asset('assets/plugin/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/scripts/main.min.js') }}"></script>
<script src="{{ asset('assets/color-switcher/color-switcher.min.js') }}"></script>
<script>
	(function () {
		const menuContent = document.querySelector('.main-menu .content');
		if (!menuContent) {
			return;
		}

		const storageKey = 'sidebar_scroll_top';
		const savedScroll = window.localStorage.getItem(storageKey);
		if (savedScroll !== null) {
			const targetScroll = parseInt(savedScroll, 10) || 0;
			if (window.jQuery && window.jQuery.fn && window.jQuery.fn.mCustomScrollbar) {
				window.jQuery(menuContent).mCustomScrollbar('scrollTo', targetScroll);
			} else {
				menuContent.scrollTop = targetScroll;
			}
		}

		const saveScroll = () => {
			const top = menuContent.scrollTop;
			window.localStorage.setItem(storageKey, String(top));
		};

		menuContent.addEventListener('scroll', saveScroll);
		window.addEventListener('beforeunload', saveScroll);
	})();
</script>
</body>
</html>
