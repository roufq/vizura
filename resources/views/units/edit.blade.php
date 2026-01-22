@extends('layouts.app')

@section('title', 'Edit Satuan')
@section('page-title', 'Edit Satuan')

@section('content')
	<div class="row small-spacing">
		<div class="col-lg-6 col-xs-12">
			<div class="box-content">
				<form method="POST" action="{{ route('units.update', $unit) }}">
					@csrf
					@method('PUT')

					<div class="form-group">
						<label for="name">Nama</label>
						<input class="form-control" id="name" name="name" type="text" value="{{ old('name', $unit->name) }}" required>
						@error('name') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="abbreviation">Singkatan</label>
						<input class="form-control" id="abbreviation" name="abbreviation" type="text" value="{{ old('abbreviation', $unit->abbreviation) }}">
						@error('abbreviation') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="checkbox">
						<label>
							<input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $unit->is_active) ? 'checked' : '' }}>
							Aktif
						</label>
					</div>

					<div class="form-group">
						<a href="{{ route('units.index') }}" class="btn btn-default">Batal</a>
						<button type="submit" class="btn btn-primary">Simpan Perubahan</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
