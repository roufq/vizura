@extends('layouts.app')

@section('title', 'Pelunasan Hutang')
@section('page-title', 'Pelunasan Hutang')

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
						<p class="margin-bottom-0">Total: {{ $purchases->total() }} hutang</p>
					</div>
				</div>

				<form method="GET" action="{{ route('purchases.payables.index') }}" class="margin-top-20">
					<div class="row">
						<div class="col-sm-4">
							<div class="form-group">
								<label for="search">Cari</label>
								<input id="search" name="search" class="form-control input-sm" placeholder="No. dokumen/supplier" value="{{ $filters['search'] ?? '' }}">
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
						<a href="{{ route('purchases.payables.index') }}" class="btn btn-default btn-sm">Reset</a>
					</div>
				</form>

				<div class="table-responsive margin-top-10">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>No. Dokumen</th>
								<th>Supplier</th>
								<th>Lokasi</th>
								<th>Total</th>
								<th>Dibayar</th>
								<th>Sisa</th>
								<th>Status</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($purchases as $purchase)
								<tr>
									<td>{{ $purchase->reference_no }}</td>
									<td>{{ $purchase->supplier?->name ?? '-' }}</td>
									<td>{{ $purchase->location?->name ?? '-' }}</td>
									<td>{{ number_format((float) $purchase->total, 2, ',', '.') }}</td>
									<td>{{ number_format((float) $purchase->paid_total, 2, ',', '.') }}</td>
									<td>{{ number_format((float) $purchase->payable_balance, 2, ',', '.') }}</td>
									<td>
										<span class="label {{ $purchase->payment_status === 'paid' ? 'label-success' : ($purchase->payment_status === 'partial' ? 'label-warning' : 'label-default') }}">
											{{ $purchase->payment_status === 'paid' ? 'Lunas' : ($purchase->payment_status === 'partial' ? 'Sebagian' : 'Belum') }}
										</span>
									</td>
									<td>
										<a href="{{ route('purchases.payables.show', $purchase) }}" class="btn btn-xs btn-info">Detail</a>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="8" class="text-center">Belum ada hutang.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>

				<div class="text-center">
					{{ $purchases->links('pagination::bootstrap-4') }}
				</div>
			</div>
		</div>
	</div>
@endsection
