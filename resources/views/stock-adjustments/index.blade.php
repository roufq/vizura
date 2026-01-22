@extends('layouts.app')

@section('title', 'Penyesuaian Stok')
@section('page-title', 'Penyesuaian Stok')

@section('content')
	<div class="row small-spacing">
		<div class="col-xs-12">
			@if (session('status'))
				<div class="alert alert-success">
					{{ session('status') }}
				</div>
			@endif

			@if ($errors->any())
				<div class="alert alert-danger">
					{{ $errors->first() }}
				</div>
			@endif

			<div class="box-content">
				<form method="GET" action="{{ route('stock-adjustments.index') }}" class="form-inline margin-top-10">
					<div class="form-group">
						<label for="search">Produk</label>
						<input type="text" id="search" name="search" class="form-control input-sm" value="{{ $search }}" placeholder="Cari produk/SKU">
					</div>
					<div class="form-group">
						<label for="status">Status</label>
						<select id="status" name="status" class="form-control input-sm">
							<option value="">Semua</option>
							<option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
							<option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
						</select>
					</div>
					<div class="form-group">
						<label for="start_date">Dari</label>
						<input type="date" id="start_date" name="start_date" class="form-control input-sm" value="{{ $startDate }}">
					</div>
					<div class="form-group">
						<label for="end_date">Sampai</label>
						<input type="date" id="end_date" name="end_date" class="form-control input-sm" value="{{ $endDate }}">
					</div>
					@if ($canManageAll)
						<div class="form-group">
							<label for="location_id">Lokasi</label>
							<select id="location_id" name="location_id" class="form-control input-sm">
								<option value="">Lokasi aktif</option>
								@foreach ($locations as $location)
									<option value="{{ $location->id }}" {{ $locationId === $location->id ? 'selected' : '' }}>
										{{ $location->name }}
									</option>
								@endforeach
							</select>
						</div>
					@endif
					<button type="submit" class="btn btn-primary btn-sm">Filter</button>
					<a href="{{ route('stock-adjustments.index') }}" class="btn btn-default btn-sm">Reset</a>
				</form>

				<div class="row">
					<div class="col-sm-6">
						<p class="margin-bottom-0">Total: {{ $adjustments->total() }} penyesuaian</p>
					</div>
					<div class="col-sm-6 text-right">
						<a href="{{ route('stock-adjustments.create') }}" class="btn btn-success btn-sm">Buat Penyesuaian</a>
					</div>
				</div>

				<div class="table-responsive margin-top-20">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Tanggal</th>
								<th>Lokasi</th>
								<th>Produk</th>
								<th>Jumlah</th>
								<th>Status</th>
								<th>Diminta</th>
								<th>Disetujui</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($adjustments as $adjustment)
								<tr>
									<td>{{ $adjustment->created_at?->format('d/m/Y H:i') }}</td>
									<td>{{ $adjustment->location?->name ?? '-' }}</td>
									<td>{{ $adjustment->product?->name ?? '-' }}</td>
									<td>{{ number_format((float) $adjustment->quantity_delta, 2, ',', '.') }}</td>
									<td>
										<span class="label {{ $adjustment->status === 'approved' ? 'label-success' : 'label-warning' }}">
											{{ ucfirst($adjustment->status) }}
										</span>
									</td>
									<td>{{ $adjustment->requester?->name ?? '-' }}</td>
									<td>{{ $adjustment->approver?->name ?? '-' }}</td>
									<td>
										@if ($adjustment->status === 'pending')
											<form method="POST" action="{{ route('stock-adjustments.approve', $adjustment) }}" style="display:inline">
												@csrf
												<button type="submit" class="btn btn-xs btn-primary" onclick="return confirm('Setujui penyesuaian ini?')">Approve</button>
											</form>
											<form method="POST" action="{{ route('stock-adjustments.destroy', $adjustment) }}" style="display:inline" onsubmit="return confirm('Hapus penyesuaian ini?')">
												@csrf
												@method('DELETE')
												<button type="submit" class="btn btn-xs btn-danger">Hapus</button>
											</form>
										@else
											<span class="text-muted">-</span>
										@endif
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="8" class="text-center">Belum ada penyesuaian stok.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>

				<div class="text-center">
					{{ $adjustments->links('pagination::bootstrap-4') }}
				</div>
			</div>
		</div>
	</div>
@endsection
