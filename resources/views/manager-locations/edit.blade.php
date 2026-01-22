@extends('layouts.app')

@section('title', 'Atur Lokasi Manager')
@section('page-title', 'Atur Lokasi Manager')

@section('content')
	<div class="row small-spacing">
		<div class="col-lg-8 col-xs-12">
			<div class="box-content">
				<h4 class="box-title">{{ $manager->name }} ({{ $manager->email }})</h4>

				<form method="POST" action="{{ route('manager-locations.update', $manager) }}">
					@csrf
					@method('PUT')

					<div class="form-group">
						<label>Lokasi yang diizinkan</label>
						<div>
							@forelse ($locations as $location)
								<div class="checkbox primary">
									<input id="location_{{ $location->id }}" name="location_ids[]" type="checkbox" value="{{ $location->id }}" {{ in_array($location->id, $selected, true) ? 'checked' : '' }}>
									<label for="location_{{ $location->id }}">{{ $location->name }}</label>
								</div>
							@empty
								<p class="text-muted">Belum ada lokasi aktif.</p>
							@endforelse
							@error('location_ids') <span class="text-danger">{{ $message }}</span> @enderror
						</div>
					</div>

					<div class="form-group">
						<a href="{{ route('manager-locations.index') }}" class="btn btn-default">Batal</a>
						<button type="submit" class="btn btn-primary">Simpan</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
