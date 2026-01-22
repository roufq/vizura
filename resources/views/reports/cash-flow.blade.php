@extends('layouts.app')

@section('title', 'Laporan Arus Kas')
@section('page-title', 'Laporan Arus Kas')

@section('content')
	<div class="row small-spacing">
		<div class="col-xs-12">
			<div class="box-content">
				<form method="GET" action="{{ route('reports.cash-flow') }}" class="form-inline margin-top-10">
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
					<a href="{{ route('reports.cash-flow') }}" class="btn btn-default btn-sm">Reset</a>
				</form>

				<div class="table-responsive margin-top-20">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Akun Kas</th>
								<th>Perubahan</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($cashMovements as $row)
								<tr>
									<td>{{ $row['account']->name }}</td>
									<td>{{ number_format($row['total'], 2, ',', '.') }}</td>
								</tr>
							@empty
								<tr>
									<td colspan="2" class="text-center">Belum ada data arus kas.</td>
								</tr>
							@endforelse
						</tbody>
						<tfoot>
							<tr>
								<th>Total Perubahan</th>
								<th>{{ number_format($netChange, 2, ',', '.') }}</th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>
@endsection
