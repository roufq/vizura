@extends('layouts.app')

@section('title', 'Edit Lokasi')
@section('page-title', 'Edit Lokasi')

@section('content')
	<div class="row small-spacing">
		<div class="col-lg-8 col-xs-12">
			<div class="box-content">
				<form method="POST" action="{{ route('locations.update', $location) }}">
					@csrf
					@method('PUT')

					<div class="form-group">
						<label for="code">Kode</label>
						<input class="form-control" id="code" name="code" type="text" value="{{ old('code', $location->code) }}" required>
						@error('code') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="name">Nama</label>
						<input class="form-control" id="name" name="name" type="text" value="{{ old('name', $location->name) }}" required>
						@error('name') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="address">Alamat</label>
						<input class="form-control" id="address" name="address" type="text" value="{{ old('address', $location->address) }}">
						@error('address') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="phone">Telepon</label>
						<input class="form-control" id="phone" name="phone" type="text" value="{{ old('phone', $location->phone) }}">
						@error('phone') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="checkbox primary">
						<input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $location->is_active) ? 'checked' : '' }}>
						<label for="is_active">Aktif</label>
					</div>
					<div class="checkbox primary">
						<input id="toko_pusat" name="toko_pusat" type="checkbox" value="1" {{ old('toko_pusat', $location->toko_pusat) ? 'checked' : '' }}>
						<label for="toko_pusat">Toko pusat</label>
						@error('toko_pusat') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<a href="{{ route('locations.index') }}" class="btn btn-default">Batal</a>
						<button type="submit" class="btn btn-primary">Simpan Perubahan</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
