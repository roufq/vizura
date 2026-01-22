@extends('layouts.app')

@section('title', 'Laporan Laba Rugi')
@section('page-title', 'Laporan Laba Rugi')

@section('content')
	<div class="row small-spacing">
		<div class="col-xs-12">
			<div class="box-content">
				<form method="GET" action="{{ route('reports.income-statement') }}" class="form-inline margin-top-10">
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
					<a href="{{ route('reports.income-statement') }}" class="btn btn-default btn-sm">Reset</a>
				</form>

				<div class="row margin-top-20">
					<div class="col-sm-3">
						<div class="box-content">
							<p class="text-muted">Pendapatan</p>
							<h4>{{ number_format($incomeTotal, 2, ',', '.') }}</h4>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="box-content">
							<p class="text-muted">HPP</p>
							<h4>{{ number_format($cogsTotal, 2, ',', '.') }}</h4>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="box-content">
							<p class="text-muted">Laba Kotor</p>
							<h4>{{ number_format($grossProfit, 2, ',', '.') }}</h4>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="box-content">
							<p class="text-muted">Biaya Operasional</p>
							<h4>{{ number_format($expenseTotal, 2, ',', '.') }}</h4>
						</div>
					</div>
				</div>

				<div class="box-content margin-top-20">
					<div class="row">
						<div class="col-sm-6">
							<p class="text-muted">Laba Bersih</p>
						</div>
						<div class="col-sm-6 text-right">
							<h3 class="margin-top-0">{{ number_format($netProfit, 2, ',', '.') }}</h3>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
