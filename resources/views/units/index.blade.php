@extends('layouts.app')

@section('title', 'Satuan')
@section('page-title', 'Satuan')

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
						<p class="margin-bottom-0">Total: {{ $units->total() }} satuan</p>
					</div>
					<div class="col-sm-6 text-right">
						<a href="{{ route('units.create') }}" class="btn btn-success btn-sm">Tambah Satuan</a>
					</div>
				</div>

				<div class="table-responsive margin-top-20">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Nama</th>
								<th>Singkatan</th>
								<th>Status</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($units as $unit)
								<tr>
									<td>{{ $unit->name }}</td>
									<td>{{ $unit->abbreviation ?? '-' }}</td>
									<td>
										<span class="label {{ $unit->is_active ? 'label-success' : 'label-default' }}">
											{{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}
										</span>
									</td>
									<td>
										<a href="{{ route('units.edit', $unit) }}" class="btn btn-xs btn-info">Edit</a>
										<form method="POST" action="{{ route('units.destroy', $unit) }}" style="display:inline" onsubmit="return confirm('Hapus satuan ini?')">
											@csrf
											@method('DELETE')
											<button type="submit" class="btn btn-xs btn-danger">Hapus</button>
										</form>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="4" class="text-center">Belum ada satuan.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>

				<div class="text-center">
					{{ $units->links('pagination::bootstrap-4') }}
				</div>
			</div>
		</div>
	</div>
@endsection
