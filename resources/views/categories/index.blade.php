@extends('layouts.app')

@section('title', 'Kategori')
@section('page-title', 'Kategori')

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
						<p class="margin-bottom-0">Total: {{ $categories->total() }} kategori</p>
					</div>
					<div class="col-sm-6 text-right">
						<a href="{{ route('categories.create') }}" class="btn btn-success btn-sm">Tambah Kategori</a>
					</div>
				</div>

				<div class="table-responsive margin-top-20">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Nama</th>
								<th>Status</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($categories as $category)
								<tr>
									<td>{{ $category->name }}</td>
									<td>
										<span class="label {{ $category->is_active ? 'label-success' : 'label-default' }}">
											{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
										</span>
									</td>
									<td>
										<a href="{{ route('categories.edit', $category) }}" class="btn btn-xs btn-info">Edit</a>
										<form method="POST" action="{{ route('categories.destroy', $category) }}" style="display:inline" onsubmit="return confirm('Hapus kategori ini?')">
											@csrf
											@method('DELETE')
											<button type="submit" class="btn btn-xs btn-danger">Hapus</button>
										</form>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="3" class="text-center">Belum ada kategori.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>

				<div class="text-center">
					{{ $categories->links('pagination::bootstrap-4') }}
				</div>
			</div>
		</div>
	</div>
@endsection
