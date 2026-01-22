@extends('layouts.app')

@section('title', 'Tambah Pengguna')
@section('page-title', 'Tambah Pengguna')

@section('content')
	<div class="row small-spacing">
		<div class="col-lg-8 col-xs-12">
			<div class="box-content">
				<form method="POST" action="{{ route('users.store') }}">
					@csrf

					<div class="form-group">
						<label for="name">Nama</label>
						<input class="form-control" id="name" name="name" type="text" value="{{ old('name') }}" required>
						@error('name') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="email">Email</label>
						<input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" required>
						@error('email') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="password">Password</label>
						<input class="form-control" id="password" name="password" type="password" required>
						@error('password') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="role">Role</label>
						<select id="role" name="role" class="form-control" required>
							<option value="" disabled {{ old('role') ? '' : 'selected' }}>Pilih role</option>
							@foreach ($roles as $roleOption)
								<option value="{{ $roleOption }}" {{ old('role') === $roleOption ? 'selected' : '' }}>
									{{ $roleOption }}
								</option>
							@endforeach
						</select>
						@error('role') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="active_location_id">Lokasi Aktif</label>
						<select id="active_location_id" name="active_location_id" class="form-control" required>
							<option value="" disabled {{ old('active_location_id') ? '' : 'selected' }}>Pilih lokasi</option>
							@foreach ($locations as $location)
								<option value="{{ $location->id }}" {{ (int) old('active_location_id') === $location->id ? 'selected' : '' }}>
									{{ $location->name }}
								</option>
							@endforeach
						</select>
						@error('active_location_id') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label>Lokasi akses (khusus Manager)</label>
						<div>
							@foreach ($locations as $location)
								<div class="checkbox primary">
									<input id="location_{{ $location->id }}" name="location_ids[]" type="checkbox" value="{{ $location->id }}" {{ in_array($location->id, old('location_ids', []), true) ? 'checked' : '' }}>
									<label for="location_{{ $location->id }}">{{ $location->name }}</label>
								</div>
							@endforeach
							@error('location_ids') <span class="text-danger">{{ $message }}</span> @enderror
						</div>
					</div>

					<div class="form-group">
						<a href="{{ route('users.index') }}" class="btn btn-default">Batal</a>
						<button type="submit" class="btn btn-primary">Simpan</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
