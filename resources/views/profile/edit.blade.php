@extends('layouts.app')

@section('title', 'Profile')
@section('page-title', 'Profile')

@section('content')
	<div class="row small-spacing">
		<div class="col-lg-8 col-xs-12">
			@if (session('status') === 'profile-updated')
				<div class="alert alert-success">
					Profil berhasil diperbarui.
				</div>
			@endif

			<div class="box-content">
				<h4 class="box-title">Informasi Profil</h4>
				<form method="POST" action="{{ route('profile.update') }}">
					@csrf
					@method('PATCH')

					<div class="form-group">
						<label for="name">Nama</label>
						<input class="form-control" id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required>
						@error('name') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="email">Email</label>
						<input class="form-control" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
						@error('email') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<button type="submit" class="btn btn-primary">Simpan</button>
					</div>
				</form>
			</div>

			<div class="box-content margin-top-20">
				<h4 class="box-title">Hapus Akun</h4>
				<p>Masukkan password untuk menghapus akun.</p>
				<form method="POST" action="{{ route('profile.destroy') }}">
					@csrf
					@method('DELETE')

					<div class="form-group">
						<label for="password">Password</label>
						<input class="form-control" id="password" name="password" type="password" required>
						@if ($errors->userDeletion->has('password'))
							<span class="text-danger">{{ $errors->userDeletion->first('password') }}</span>
						@endif
					</div>

					<div class="form-group">
						<button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus akun?')">Hapus Akun</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
