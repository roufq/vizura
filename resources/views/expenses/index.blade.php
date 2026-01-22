@extends('layouts.app')

@section('title', 'Biaya Operasional')
@section('page-title', 'Biaya Operasional')

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
						<p class="margin-bottom-0">Total: {{ $expenses->total() }} biaya</p>
					</div>
					<div class="col-sm-6 text-right">
						<a href="{{ route('expenses.create') }}" class="btn btn-success btn-sm">Tambah Biaya</a>
					</div>
				</div>

				<form method="GET" action="{{ route('expenses.index') }}" class="margin-top-20">
					<div class="row">
						<div class="col-sm-4">
							<div class="form-group">
								<label for="search">Cari</label>
								<input id="search" name="search" class="form-control input-sm" placeholder="No. referensi/deskripsi" value="{{ $filters['search'] ?? '' }}">
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group">
								<label for="start_date">Dari</label>
								<input id="start_date" name="start_date" class="form-control input-sm" type="date" value="{{ $filters['start_date'] ?? '' }}">
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group">
								<label for="end_date">Sampai</label>
								<input id="end_date" name="end_date" class="form-control input-sm" type="date" value="{{ $filters['end_date'] ?? '' }}">
							</div>
						</div>
						@if ($canViewAll)
							<div class="col-sm-2">
								<div class="form-group">
									<label for="location_id">Lokasi</label>
									<select id="location_id" name="location_id" class="form-control input-sm">
										<option value="">Semua</option>
										@foreach ($locations as $location)
											<option value="{{ $location->id }}" {{ (int) ($filters['location_id'] ?? 0) === $location->id ? 'selected' : '' }}>
												{{ $location->name }}
											</option>
										@endforeach
									</select>
								</div>
							</div>
						@endif
					</div>
					<div class="form-group">
						<button type="submit" class="btn btn-primary btn-sm">Filter</button>
						<a href="{{ route('expenses.index') }}" class="btn btn-default btn-sm">Reset</a>
					</div>
				</form>

				<div class="table-responsive margin-top-10">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Tanggal</th>
								<th>No. Referensi</th>
								<th>Akun Biaya</th>
								<th>Metode Bayar</th>
								<th>Jumlah</th>
								<th>Lokasi</th>
								<th>Petugas</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($expenses as $expense)
								<tr>
									<td>{{ optional($expense->expense_date)->format('Y-m-d') }}</td>
									<td>{{ $expense->reference_no }}</td>
									<td>{{ $expense->account?->name ?? '-' }}</td>
									<td>{{ $expense->paymentAccount?->name ?? '-' }}</td>
									<td>{{ number_format((float) $expense->amount, 2, ',', '.') }}</td>
									<td>{{ $expense->location?->name ?? '-' }}</td>
									<td>{{ $expense->creator?->name ?? '-' }}</td>
								</tr>
							@empty
								<tr>
									<td colspan="7" class="text-center">Belum ada biaya.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>

				<div class="text-center">
					{{ $expenses->links('pagination::bootstrap-4') }}
				</div>
			</div>
		</div>
	</div>
@endsection
