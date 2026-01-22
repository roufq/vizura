@extends('layouts.app')

@section('title', 'Pengguna')
@section('page-title', 'Pengguna')

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
				<div class="row">
					<div class="col-sm-6">
						<p class="margin-bottom-0">Total: {{ $users->total() }} user</p>
					</div>
					<div class="col-sm-6 text-right">
						<a href="{{ route('users.create') }}" class="btn btn-success btn-sm">Tambah User</a>
					</div>
				</div>

				<form method="GET" action="{{ route('users.index') }}" class="form-inline margin-top-10">
					<div class="form-group">
						<label for="search">Cari</label>
						<input type="text" id="search" class="form-control input-sm" name="search" value="{{ $search }}" placeholder="Nama/Email">
					</div>
					<div class="form-group">
						<label for="role">Role</label>
						<select id="role" name="role" class="form-control input-sm">
							<option value="">Semua role</option>
							@foreach (['Owner', 'Manager', 'KepalaToko', 'Kasir'] as $roleOption)
								<option value="{{ $roleOption }}" {{ $role === $roleOption ? 'selected' : '' }}>
									{{ $roleOption }}
								</option>
							@endforeach
						</select>
					</div>
					<button type="submit" class="btn btn-primary btn-sm">Filter</button>
					<a href="{{ route('users.index') }}" class="btn btn-default btn-sm">Reset</a>
				</form>

				<div class="table-responsive margin-top-20">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Nama</th>
								<th>Email</th>
								<th>Role</th>
								<th>Lokasi Aktif</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($users as $user)
								<tr>
									<td>{{ $user->name }}</td>
									<td>{{ $user->email }}</td>
									<td>{{ $user->roles->pluck('name')->implode(', ') ?: '-' }}</td>
									<td>{{ $user->activeLocation?->name ?? '-' }}</td>
									<td>
										<a href="{{ route('users.edit', $user) }}" class="btn btn-xs btn-info">Edit</a>
										<form method="POST" action="{{ route('users.destroy', $user) }}" style="display:inline" onsubmit="return confirm('Hapus user ini?')">
											@csrf
											@method('DELETE')
											<button type="submit" class="btn btn-xs btn-danger">Hapus</button>
										</form>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="5" class="text-center">Belum ada user.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>

				<div class="text-center">
					{{ $users->links('pagination::bootstrap-4') }}
				</div>
			</div>
		</div>
	</div>
@endsection
