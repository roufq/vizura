@extends('layouts.app')

@section('title', 'Buat Penyesuaian Stok')
@section('page-title', 'Buat Penyesuaian Stok')

@section('content')
	<div class="row small-spacing">
		<div class="col-lg-8 col-xs-12">
			<div class="box-content">
				<form method="POST" action="{{ route('stock-adjustments.store') }}" enctype="multipart/form-data">
					@csrf

					@if ($canManageAll)
						<div class="form-group">
							<label for="location_id">Lokasi</label>
							<select id="location_id" name="location_id" class="form-control">
								<option value="">Pilih lokasi</option>
								@foreach ($locations as $location)
									<option value="{{ $location->id }}" {{ (int) old('location_id') === $location->id ? 'selected' : '' }}>
										{{ $location->name }}
									</option>
								@endforeach
							</select>
							@error('location_id') <span class="text-danger">{{ $message }}</span> @enderror
						</div>
					@elseif ($activeLocation)
						<p class="text-muted">Lokasi aktif: <strong>{{ $activeLocation->name }}</strong></p>
					@endif

					<div class="form-group">
						<label for="product_id">Produk</label>
						<select id="product_id" name="product_id" class="form-control js__select2" data-min-results="0" required>
							<option value="" disabled {{ old('product_id') ? '' : 'selected' }}>Pilih produk</option>
							@foreach ($products as $product)
								<option value="{{ $product->id }}" {{ (int) old('product_id', request()->integer('product_id')) === $product->id ? 'selected' : '' }}>
									{{ $product->name }} ({{ $product->sku }})
								</option>
							@endforeach
						</select>
						@error('product_id') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="quantity_delta">Jumlah Penyesuaian</label>
						<input class="form-control" id="quantity_delta" name="quantity_delta" type="number" step="0.01" value="{{ old('quantity_delta') }}" required>
						<small class="text-muted">Gunakan angka negatif untuk pengurangan.</small>
						@error('quantity_delta') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="reason">Alasan</label>
						<input class="form-control" id="reason" name="reason" type="text" value="{{ old('reason') }}" required>
						@error('reason') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="evidence">Bukti (opsional)</label>
						<input class="form-control" id="evidence" name="evidence" type="file" accept=".jpg,.jpeg,.png,.pdf">
						@error('evidence') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<a href="{{ route('stock-adjustments.index') }}" class="btn btn-default">Batal</a>
						<button type="submit" class="btn btn-primary">Simpan</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
