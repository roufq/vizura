@extends('layouts.app')

@section('title', 'Supplier')
@section('page-title', 'Supplier')

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
						<p class="margin-bottom-0">Total: {{ $suppliers->total() }} supplier</p>
					</div>
					<div class="col-sm-6 text-right">
						<a href="{{ route('suppliers.create') }}" class="btn btn-success btn-sm">Tambah Supplier</a>
					</div>
				</div>

				<div class="table-responsive margin-top-20">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Nama</th>
								<th>Telepon</th>
								<th>Email</th>
								<th>Status</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($suppliers as $supplier)
								<tr>
									<td>{{ $supplier->name }}</td>
									<td>{{ $supplier->phone ?? '-' }}</td>
									<td>{{ $supplier->email ?? '-' }}</td>
									<td>
										<span class="label {{ $supplier->is_active ? 'label-success' : 'label-default' }}">
											{{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}
										</span>
									</td>
									<td>
										<a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-xs btn-info">Edit</a>
										<form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" style="display:inline" onsubmit="return confirm('Hapus supplier ini?')">
											@csrf
											@method('DELETE')
											<button type="submit" class="btn btn-xs btn-danger">Hapus</button>
										</form>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="5" class="text-center">Belum ada supplier.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>

				<div class="text-center">
					{{ $suppliers->links('pagination::bootstrap-4') }}
				</div>
			</div>
		</div>
	</div>
@endsection
