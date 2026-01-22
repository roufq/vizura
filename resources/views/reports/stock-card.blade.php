@extends('layouts.app')

@section('title', 'Kartu Stok')
@section('page-title', 'Kartu Stok')

@section('content')
	<div class="row small-spacing">
		<div class="col-xs-12">
			<div class="box-content">
				@if ($products->isEmpty())
					<div class="alert alert-warning">Belum ada produk untuk ditampilkan.</div>
				@else
					<form method="GET" action="{{ route('reports.stock-card') }}" class="form-inline margin-top-10">
						<div class="form-group">
							<label for="product_id">Produk</label>
							<select id="product_id" name="product_id" class="form-control input-sm js__select2" data-min-results="0" required>
								@foreach ($products as $item)
									<option value="{{ $item->id }}" {{ (int) ($filters['product_id'] ?? 0) === $item->id ? 'selected' : '' }}>
										{{ $item->name }}
									</option>
								@endforeach
							</select>
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
					@endif
					<div class="form-group">
						<label for="start_date">Dari</label>
						<input type="date" id="start_date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="form-control input-sm">
					</div>
					<div class="form-group">
						<label for="end_date">Sampai</label>
						<input type="date" id="end_date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="form-control input-sm">
					</div>
						<button type="submit" class="btn btn-primary btn-sm">Filter</button>
					</form>
				@endif

				@if ($product)
					<div class="margin-top-20">
						<strong>Produk:</strong> {{ $product->name }} ({{ $product->sku }})
						@if ($locationId)
							<span class="margin-left-20"><strong>Lokasi:</strong> {{ $locations->firstWhere('id', $locationId)?->name ?? '-' }}</span>
						@endif
					</div>
				@endif

				@if ($product)
					<div class="table-responsive margin-top-20">
						<table class="table table-striped">
							<thead>
								<tr>
									<th>Tanggal</th>
									<th>Aktivitas</th>
									<th>Referensi</th>
									<th>Catatan</th>
									<th>Masuk</th>
									<th>Keluar</th>
									<th>Saldo</th>
								</tr>
							</thead>
							<tbody>
								@forelse ($entries as $entry)
									<tr>
										<td>{{ \Carbon\Carbon::parse($entry['date'])->format('d/m/Y H:i') }}</td>
										<td>{{ ucwords(str_replace('_', ' ', $entry['type'])) }}</td>
										<td>{{ $entry['reference'] }}</td>
										<td>{{ $entry['note'] ?? '-' }}</td>
										<td>{{ number_format($entry['qty_in'], 2, ',', '.') }}</td>
										<td>{{ number_format($entry['qty_out'], 2, ',', '.') }}</td>
										<td>{{ number_format($entry['balance'], 2, ',', '.') }}</td>
									</tr>
								@empty
									<tr>
										<td colspan="7" class="text-center">Belum ada pergerakan stok.</td>
									</tr>
								@endforelse
							</tbody>
						</table>
					</div>
				@endif
			</div>
		</div>
	</div>
@endsection
