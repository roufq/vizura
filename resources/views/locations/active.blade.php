@extends('layouts.app')

@section('title', 'Pilih Lokasi Aktif')
@section('page-title', 'Pilih Lokasi Aktif')

@section('content')
	<div class="row small-spacing">
		<div class="col-lg-6 col-xs-12">
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
				@if ($locations->isEmpty())
					<p>Belum ada lokasi aktif. Tambahkan lokasi dulu.</p>
					<a href="{{ route('locations.create') }}" class="btn btn-primary">Tambah Lokasi</a>
				@else
					<form method="POST" action="{{ route('locations.active.update') }}">
						@csrf

						<div class="form-group">
							<label for="location_id">Lokasi</label>
							<select id="location_id" name="location_id" class="form-control" required>
								<option value="" disabled {{ old('location_id', $current) ? '' : 'selected' }}>
									Pilih lokasi
								</option>
								@foreach ($locations as $location)
									<option value="{{ $location->id }}" {{ (int) old('location_id', $current) === $location->id ? 'selected' : '' }}>
										{{ $location->name }} ({{ $location->code }})
									</option>
								@endforeach
							</select>
							@error('location_id') <span class="text-danger">{{ $message }}</span> @enderror
						</div>

						<button type="submit" class="btn btn-primary">Simpan</button>
					</form>
				@endif
			</div>
		</div>
	</div>
@endsection
