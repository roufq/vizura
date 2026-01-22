@extends('layouts.app')

@section('title', 'Laporan Penjualan')
@section('page-title', 'Laporan Penjualan')

@section('content')
	<div class="row small-spacing">
		<div class="col-xs-12">
			<div class="box-content">
				<form method="GET" action="{{ route('reports.sales') }}" class="form-inline margin-top-10">
					<div class="form-group">
						<label for="start_date">Dari</label>
						<input type="date" id="start_date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="form-control input-sm">
					</div>
					<div class="form-group">
						<label for="end_date">Sampai</label>
						<input type="date" id="end_date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="form-control input-sm">
					</div>
					@if ($canSelectLocations)
						<div class="form-group">
							<label for="location_id">Lokasi</label>
							<select id="location_id" name="location_id" class="form-control input-sm">
								<option value="">Lokasi aktif</option>
								@foreach ($locations as $location)
									<option value="{{ $location->id }}" {{ (int) ($filters['location_id'] ?? 0) === $location->id ? 'selected' : '' }}>
										{{ $location->name }}
									</option>
								@endforeach
							</select>
						</div>
						@if ($canViewAll)
							<div class="form-group">
								<label class="checkbox-inline">
									<input type="checkbox" name="all_locations" value="1" {{ ($filters['all_locations'] ?? false) ? 'checked' : '' }}>
									Semua lokasi
								</label>
							</div>
						@endif
					@endif
					<div class="form-group">
						<label for="cashier_id">Kasir</label>
						<select id="cashier_id" name="cashier_id" class="form-control input-sm">
							<option value="">Semua kasir</option>
							@foreach ($cashiers as $cashier)
								<option value="{{ $cashier->id }}" {{ (int) ($filters['cashier_id'] ?? 0) === $cashier->id ? 'selected' : '' }}>
									{{ $cashier->name }}
								</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label for="method">Metode</label>
						<select id="method" name="method" class="form-control input-sm">
							<option value="">Semua metode</option>
							@foreach ($paymentMethods as $method)
								<option value="{{ $method }}" {{ ($filters['method'] ?? '') === $method ? 'selected' : '' }}>
									{{ strtoupper($method) }}
								</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label for="status">Status</label>
						<select id="status" name="status" class="form-control input-sm">
							<option value="">Semua status</option>
							@foreach (['posted' => 'Posted', 'draft' => 'Draft', 'voided' => 'Void', 'returned' => 'Retur'] as $value => $label)
								<option value="{{ $value }}" {{ ($filters['status'] ?? '') === $value ? 'selected' : '' }}>
									{{ $label }}
								</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label for="type">Tipe</label>
						<select id="type" name="type" class="form-control input-sm">
							<option value="">Semua tipe</option>
							<option value="sale" {{ ($filters['type'] ?? '') === 'sale' ? 'selected' : '' }}>Penjualan</option>
							<option value="return" {{ ($filters['type'] ?? '') === 'return' ? 'selected' : '' }}>Retur</option>
						</select>
					</div>
					<button type="submit" class="btn btn-primary btn-sm">Filter</button>
					<a href="{{ route('reports.sales') }}" class="btn btn-default btn-sm">Reset</a>
				</form>

				<div class="row margin-top-20">
					<div class="col-sm-3">
						<div class="box-content">
							<p class="text-muted">Gross</p>
							<h4>{{ number_format((float) ($summary->gross_total ?? 0), 2, ',', '.') }}</h4>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="box-content">
							<p class="text-muted">Diskon</p>
							<h4>{{ number_format((float) ($summary->discount_total ?? 0), 2, ',', '.') }}</h4>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="box-content">
							<p class="text-muted">Pajak</p>
							<h4>{{ number_format((float) ($summary->tax_total ?? 0), 2, ',', '.') }}</h4>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="box-content">
							<p class="text-muted">Nett</p>
							<h4>{{ number_format((float) ($summary->net_total ?? 0), 2, ',', '.') }}</h4>
						</div>
					</div>
				</div>

				<div class="table-responsive margin-top-20">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Tanggal</th>
								<th>No. Transaksi</th>
								<th>Kasir</th>
								<th>Metode</th>
								<th>Gross</th>
								<th>Diskon</th>
								<th>Pajak</th>
								<th>Nett</th>
								<th>Status</th>
								<th>Tipe</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($sales as $sale)
								<tr>
									<td>{{ ($sale->created_at)?->format('d/m/Y H:i') }}</td>
									<td>{{ $sale->reference_no }}</td>
									<td>{{ $sale->cashier?->name ?? '-' }}</td>
									<td>
										@php
											$methods = $sale->payments->pluck('method')->unique()->map(fn ($method) => strtoupper($method))->values();
										@endphp
										{{ $methods->isEmpty() ? '-' : $methods->implode(', ') }}
									</td>
									<td>{{ number_format((float) $sale->subtotal, 2, ',', '.') }}</td>
									<td>{{ number_format((float) $sale->order_discount, 2, ',', '.') }}</td>
									<td>{{ number_format((float) $sale->tax_amount, 2, ',', '.') }}</td>
									<td>{{ number_format((float) $sale->total, 2, ',', '.') }}</td>
									<td>{{ ucfirst($sale->status) }}</td>
									<td>{{ $sale->type === 'return' ? 'Retur' : 'Penjualan' }}</td>
								</tr>
							@empty
								<tr>
									<td colspan="10" class="text-center">Belum ada data penjualan.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>

				<div class="text-center">
					{{ $sales->links('pagination::bootstrap-4') }}
				</div>
			</div>
		</div>
	</div>
@endsection
