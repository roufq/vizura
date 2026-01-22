@extends('layouts.app')

@section('title', 'Tambah Kategori')
@section('page-title', 'Tambah Kategori')

@section('content')
	<div class="row small-spacing">
		<div class="col-lg-6 col-xs-12">
			<div class="box-content">
				<form method="POST" action="{{ route('categories.store') }}">
					@csrf

					<div class="form-group">
						<label for="name">Nama</label>
						<input class="form-control" id="name" name="name" type="text" value="{{ old('name') }}" required>
						@error('name') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="checkbox">
						<label>
							<input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
							Aktif
						</label>
					</div>

					<div class="form-group">
						<a href="{{ route('categories.index') }}" class="btn btn-default">Batal</a>
						<button type="submit" class="btn btn-primary">Simpan</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
