@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
	<div class="row small-spacing">
		<div class="col-xs-12">
			<div class="box-content">
				<h4 class="box-title">Ringkasan Lokasi: {{ $locationName }}</h4>
			</div>
		</div>
	</div>

	<div class="row small-spacing">
		<div class="col-sm-6 col-lg-3 col-xs-12">
			<div class="box-content">
				<p class="text-muted">Penjualan Hari Ini</p>
				<h3>{{ number_format((float) $salesToday, 2, ',', '.') }}</h3>
			</div>
		</div>
		<div class="col-sm-6 col-lg-3 col-xs-12">
			<div class="box-content">
				<p class="text-muted">Penjualan Bulan Ini</p>
				<h3>{{ number_format((float) $salesMonth, 2, ',', '.') }}</h3>
			</div>
		</div>
		<div class="col-sm-6 col-lg-3 col-xs-12">
			<div class="box-content">
				<p class="text-muted">Transaksi Hari Ini</p>
				<h3>{{ number_format((float) $transactionsToday, 0, ',', '.') }}</h3>
			</div>
		</div>
		<div class="col-sm-6 col-lg-3 col-xs-12">
			<div class="box-content">
				<p class="text-muted">Produk Aktif</p>
				<h3>{{ number_format((float) $activeProducts, 0, ',', '.') }}</h3>
			</div>
		</div>
	</div>

	<div class="row small-spacing">
		<div class="col-lg-8 col-xs-12">
			<div class="box-content">
				<h4 class="box-title">Tren Penjualan 7 Hari</h4>
				<canvas id="sales-chart" height="120"></canvas>
			</div>
		</div>
		<div class="col-lg-4 col-xs-12">
			<div class="box-content">
				<h4 class="box-title">Stok Menipis</h4>
				<ul class="list-group">
					@forelse ($lowStocks as $stock)
						<li class="list-group-item">
							<strong>{{ $stock->product?->name ?? '-' }}</strong>
							<span class="pull-right">{{ number_format((float) $stock->quantity_on_hand, 2, ',', '.') }}</span>
						</li>
					@empty
						<li class="list-group-item text-muted">Tidak ada stok menipis.</li>
					@endforelse
				</ul>
			</div>
		</div>
	</div>

	<div class="row small-spacing">
		<div class="col-lg-6 col-xs-12">
			<div class="box-content">
				<h4 class="box-title">Produk Terlaris</h4>
				<div class="table-responsive">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Produk</th>
								<th>Qty</th>
								<th>Omzet</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($topProducts as $item)
								<tr>
									<td>{{ $item->product?->name ?? '-' }}</td>
									<td>{{ number_format((float) $item->total_qty, 2, ',', '.') }}</td>
									<td>{{ number_format((float) $item->total_sales, 2, ',', '.') }}</td>
								</tr>
							@empty
								<tr>
									<td colspan="3" class="text-center">Belum ada data penjualan.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-lg-6 col-xs-12">
			<div class="box-content">
				<h4 class="box-title">Penyesuaian Stok Terbaru</h4>
				<div class="table-responsive">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Tanggal</th>
								<th>Produk</th>
								<th>Jumlah</th>
								<th>Diminta</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($recentAdjustments as $adjustment)
								<tr>
									<td>{{ $adjustment->created_at?->format('d/m H:i') }}</td>
									<td>{{ $adjustment->product?->name ?? '-' }}</td>
									<td>{{ number_format((float) $adjustment->quantity_delta, 2, ',', '.') }}</td>
									<td>{{ $adjustment->requester?->name ?? '-' }}</td>
								</tr>
							@empty
								<tr>
									<td colspan="4" class="text-center">Belum ada penyesuaian.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

	<script src="{{ asset('assets/plugin/chart/chartjs/Chart.bundle.js') }}"></script>
	<script>
		(function () {
			var ctx = document.getElementById('sales-chart');
			if (!ctx) {
				return;
			}
			new Chart(ctx.getContext('2d'), {
				type: 'line',
				data: {
					labels: @json($labels),
					datasets: [{
						label: 'Penjualan',
						backgroundColor: 'rgba(63, 134, 242, 0.2)',
						borderColor: '#3f86f2',
						pointBackgroundColor: '#3f86f2',
						data: @json($series),
						fill: true,
						lineTension: 0.3,
					}]
				},
				options: {
					legend: { display: false },
					tooltips: { mode: 'index', intersect: false },
					scales: {
						yAxes: [{
							ticks: { beginAtZero: true }
						}]
					},
					responsive: true,
					maintainAspectRatio: false
				}
			});
		})();
	</script>
@endsection
