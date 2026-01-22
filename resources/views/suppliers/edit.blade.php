@extends('layouts.app')

@section('title', 'Edit Supplier')
@section('page-title', 'Edit Supplier')

@section('content')
	<div class="row small-spacing">
		<div class="col-lg-6 col-xs-12">
			<div class="box-content">
				<form method="POST" action="{{ route('suppliers.update', $supplier) }}">
					@csrf
					@method('PUT')

					<div class="form-group">
						<label for="name">Nama</label>
						<input class="form-control" id="name" name="name" type="text" value="{{ old('name', $supplier->name) }}" required>
						@error('name') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="phone">Telepon</label>
						<input class="form-control" id="phone" name="phone" type="text" value="{{ old('phone', $supplier->phone) }}">
						@error('phone') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="email">Email</label>
						<input class="form-control" id="email" name="email" type="email" value="{{ old('email', $supplier->email) }}">
						@error('email') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="address">Alamat</label>
						<input class="form-control" id="address" name="address" type="text" value="{{ old('address', $supplier->address) }}">
						@error('address') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="checkbox">
						<label>
							<input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}>
							Aktif
						</label>
					</div>

					<div class="form-group">
						<a href="{{ route('suppliers.index') }}" class="btn btn-default">Batal</a>
						<button type="submit" class="btn btn-primary">Simpan Perubahan</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
