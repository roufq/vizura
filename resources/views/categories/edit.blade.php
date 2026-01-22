@extends('layouts.app')

@section('title', 'Edit Kategori')
@section('page-title', 'Edit Kategori')

@section('content')
	<div class="row small-spacing">
		<div class="col-lg-6 col-xs-12">
			<div class="box-content">
				<form method="POST" action="{{ route('categories.update', $category) }}">
					@csrf
					@method('PUT')

					<div class="form-group">
						<label for="name">Nama</label>
						<input class="form-control" id="name" name="name" type="text" value="{{ old('name', $category->name) }}" required>
						@error('name') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="checkbox">
						<label>
							<input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
							Aktif
						</label>
					</div>

					<div class="form-group">
						<a href="{{ route('categories.index') }}" class="btn btn-default">Batal</a>
						<button type="submit" class="btn btn-primary">Simpan Perubahan</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
