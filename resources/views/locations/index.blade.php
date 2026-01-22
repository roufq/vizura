@extends('layouts.app')

@section('title', 'Lokasi')
@section('page-title', 'Lokasi')

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
						<p class="margin-bottom-0">Total: {{ $locations->total() }} lokasi</p>
					</div>
					<div class="col-sm-6 text-right">
						<a href="{{ route('locations.create') }}" class="btn btn-success btn-sm">Tambah Lokasi</a>
					</div>
				</div>

				<div class="row margin-top-10">
					<div class="col-sm-12">
						<form method="GET" action="{{ route('locations.index') }}" class="form-inline">
							<div class="form-group">
								<label for="search">Cari</label>
								<input type="text" id="search" class="form-control input-sm" name="search" value="{{ $search }}" placeholder="Kode/Nama/Alamat/Telepon">
							</div>
							<button type="submit" class="btn btn-primary btn-sm">Filter</button>
							@if ($search !== '')
								<a href="{{ route('locations.index') }}" class="btn btn-default btn-sm">Reset</a>
							@endif
						</form>
					</div>
				</div>

				<div class="table-responsive margin-top-20">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Kode</th>
								<th>Nama</th>
								<th>Alamat</th>
								<th>Telepon</th>
								<th>Status</th>
								<th>Pusat</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($locations as $location)
								<tr>
									<td>{{ $location->code }}</td>
									<td>{{ $location->name }}</td>
									<td>{{ $location->address ?? '-' }}</td>
									<td>{{ $location->phone ?? '-' }}</td>
									<td>
										<span class="label {{ $location->is_active ? 'label-success' : 'label-default' }}">
											{{ $location->is_active ? 'Aktif' : 'Nonaktif' }}
										</span>
									</td>
									<td>
										@if ($location->toko_pusat)
											<span class="label label-primary">Pusat</span>
										@else
											<span class="text-muted">-</span>
										@endif
									</td>
									<td>
										<a href="{{ route('locations.edit', $location) }}" class="btn btn-xs btn-info">Edit</a>
										@if ($canSync)
											<form method="POST" action="{{ route('locations.sync-stock', $location) }}" style="display:inline" onsubmit="return confirm('Sinkronkan produk ke lokasi ini?')">
												@csrf
												<button type="submit" class="btn btn-xs btn-warning">Sync Produk</button>
											</form>
										@endif
										<form method="POST" action="{{ route('locations.destroy', $location) }}" style="display:inline" onsubmit="return confirm('Hapus lokasi ini?')">
											@csrf
											@method('DELETE')
											<button type="submit" class="btn btn-xs btn-danger">Hapus</button>
										</form>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="7" class="text-center">Belum ada lokasi.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>

				<div class="text-center">
					{{ $locations->links('pagination::bootstrap-4') }}
				</div>
			</div>
		</div>
	</div>
@endsection
