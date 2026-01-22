@extends('layouts.app')

@section('title', 'Laporan Kas Harian')
@section('page-title', 'Laporan Kas Harian')

@section('content')
	<div class="row small-spacing">
		<div class="col-xs-12">
			<div class="box-content">
				<form method="GET" action="{{ route('reports.cash-up') }}" class="form-inline margin-top-10">
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
					<button type="submit" class="btn btn-primary btn-sm">Filter</button>
					<a href="{{ route('reports.cash-up') }}" class="btn btn-default btn-sm">Reset</a>
				</form>

				<div class="row margin-top-20">
					<div class="col-sm-4">
						<div class="box-content">
							<p class="text-muted">Total Penerimaan</p>
							<h4>{{ number_format($grandTotal, 2, ',', '.') }}</h4>
						</div>
					</div>
					<div class="col-sm-8">
						<div class="box-content">
							<p class="text-muted">Per Metode</p>
							<div class="row">
								@forelse ($totalsByMethod as $method => $total)
									<div class="col-sm-4">
										<strong>{{ strtoupper($method) }}</strong>
										<div>{{ number_format($total, 2, ',', '.') }}</div>
									</div>
								@empty
									<div class="col-sm-12">Belum ada data pembayaran.</div>
								@endforelse
							</div>
						</div>
					</div>
				</div>

				<div class="table-responsive margin-top-20">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Tanggal</th>
								<th>Metode</th>
								<th>Total</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($rows as $row)
								<tr>
									<td>{{ \Carbon\Carbon::parse($row->sale_date)->format('d/m/Y') }}</td>
									<td>{{ strtoupper($row->method) }}</td>
									<td>{{ number_format((float) $row->total, 2, ',', '.') }}</td>
								</tr>
							@empty
								<tr>
									<td colspan="3" class="text-center">Belum ada data kas harian.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
@endsection
