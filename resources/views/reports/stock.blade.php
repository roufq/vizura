@extends('layouts.app')

@section('title', 'Laporan Stok')
@section('page-title', 'Laporan Stok')

@section('content')
	<div class="row small-spacing">
		<div class="col-xs-12">
			<div class="box-content">
				<form method="GET" action="{{ route('reports.stock') }}" class="form-inline margin-top-10">
					<div class="form-group">
						<label for="search">Produk</label>
						<input type="text" id="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control input-sm" placeholder="SKU/Nama">
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
					<button type="submit" class="btn btn-primary btn-sm">Filter</button>
					<a href="{{ route('reports.stock') }}" class="btn btn-default btn-sm">Reset</a>
				</form>

				<div class="row margin-top-20">
					<div class="col-sm-4">
						<div class="box-content">
							<p class="text-muted">Nilai Persediaan</p>
							<h4>{{ number_format($totalValue, 2, ',', '.') }}</h4>
						</div>
					</div>
				</div>

				<div class="table-responsive margin-top-20">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>SKU</th>
								<th>Produk</th>
								<th>Lokasi</th>
								<th>Qty</th>
								<th>HPP</th>
								<th>Nilai</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($stockItems as $stockItem)
								@php
									$qty = (float) $stockItem->quantity_on_hand;
									$cost = (float) ($stockItem->product?->cost_price ?? 0);
								@endphp
								<tr>
									<td>{{ $stockItem->product?->sku ?? '-' }}</td>
									<td>{{ $stockItem->product?->name ?? '-' }}</td>
									<td>{{ $stockItem->location?->name ?? '-' }}</td>
									<td>{{ number_format($qty, 2, ',', '.') }}</td>
									<td>{{ number_format($cost, 2, ',', '.') }}</td>
									<td>{{ number_format($qty * $cost, 2, ',', '.') }}</td>
								</tr>
							@empty
								<tr>
									<td colspan="6" class="text-center">Belum ada data stok.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>

				<div class="text-center">
					{{ $stockItems->links('pagination::bootstrap-4') }}
				</div>
			</div>
		</div>
	</div>
@endsection
