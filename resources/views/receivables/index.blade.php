@extends('layouts.app')

@section('title', 'Pelunasan Piutang')
@section('page-title', 'Pelunasan Piutang')

@section('content')
	<div class="row small-spacing">
		<div class="col-xs-12">
			@if (session('status'))
				<div class="alert alert-success">
					{{ session('status') }}
				</div>
			@endif

			<div class="box-content">
				<div class="row">
					<div class="col-sm-6">
						<p class="margin-bottom-0">Total: {{ $sales->total() }} piutang</p>
					</div>
				</div>

				<form method="GET" action="{{ route('receivables.index') }}" class="margin-top-20">
					<div class="row">
						<div class="col-sm-4">
							<div class="form-group">
								<label for="search">Cari</label>
								<input id="search" name="search" class="form-control input-sm" placeholder="No. transaksi/nama pelanggan" value="{{ $filters['search'] ?? '' }}">
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group">
								<label for="status">Status</label>
								<select id="status" name="status" class="form-control input-sm">
									<option value="">Semua</option>
									<option value="unpaid" {{ ($filters['status'] ?? '') === 'unpaid' ? 'selected' : '' }}>Belum dibayar</option>
									<option value="partial" {{ ($filters['status'] ?? '') === 'partial' ? 'selected' : '' }}>Sebagian</option>
									<option value="paid" {{ ($filters['status'] ?? '') === 'paid' ? 'selected' : '' }}>Lunas</option>
								</select>
							</div>
						</div>
						@if ($canSelectLocations)
							<div class="col-sm-3">
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
							</div>
							@if ($canViewAll)
								<div class="col-sm-2">
									<div class="form-group">
										<label class="checkbox-inline" style="margin-top:25px;">
											<input type="checkbox" name="all_locations" value="1" {{ ($filters['all_locations'] ?? false) ? 'checked' : '' }}>
											Semua lokasi
										</label>
									</div>
								</div>
							@endif
						@endif
					</div>
					<div class="form-group">
						<button type="submit" class="btn btn-primary btn-sm">Filter</button>
						<a href="{{ route('receivables.index') }}" class="btn btn-default btn-sm">Reset</a>
					</div>
				</form>

				<div class="table-responsive margin-top-10">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>No. Transaksi</th>
								<th>Pelanggan</th>
								<th>Lokasi</th>
								<th>Total</th>
								<th>Dibayar</th>
								<th>Sisa</th>
								<th>Status</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($sales as $sale)
								<tr>
									<td>{{ $sale->reference_no }}</td>
									<td>{{ $sale->customer_name ?? '-' }}</td>
									<td>{{ $sale->location?->name ?? '-' }}</td>
									<td>{{ number_format((float) $sale->total, 2, ',', '.') }}</td>
									<td>{{ number_format((float) $sale->paid_total, 2, ',', '.') }}</td>
									<td>{{ number_format((float) $sale->receivable_balance, 2, ',', '.') }}</td>
									<td>
										<span class="label {{ $sale->payment_status === 'paid' ? 'label-success' : ($sale->payment_status === 'partial' ? 'label-warning' : 'label-default') }}">
											{{ $sale->payment_status === 'paid' ? 'Lunas' : ($sale->payment_status === 'partial' ? 'Sebagian' : 'Belum') }}
										</span>
									</td>
									<td>
										<a href="{{ route('receivables.show', $sale) }}" class="btn btn-xs btn-info">Detail</a>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="8" class="text-center">Belum ada piutang.</td>
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
