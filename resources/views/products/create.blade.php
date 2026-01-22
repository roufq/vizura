@extends('layouts.app')

@section('title', 'Tambah Produk')
@section('page-title', 'Tambah Produk')

@section('content')
	<div class="row small-spacing">
		<div class="col-lg-8 col-xs-12">
			<div class="box-content">
				<form method="POST" action="{{ route('products.store') }}">
					@csrf

					<div class="form-group">
						<label for="sku">SKU</label>
						<input class="form-control" id="sku" name="sku" type="text" value="{{ old('sku') }}" required>
						@error('sku') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="barcode">Barcode</label>
						<input class="form-control" id="barcode" name="barcode" type="text" value="{{ old('barcode') }}">
						@error('barcode') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="name">Nama</label>
						<input class="form-control" id="name" name="name" type="text" value="{{ old('name') }}" required>
						@error('name') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="category_id">Kategori</label>
						<select id="category_id" name="category_id" class="form-control">
							<option value="">Pilih kategori</option>
							@foreach ($categories as $category)
								<option value="{{ $category->id }}" {{ (int) old('category_id') === $category->id ? 'selected' : '' }}>
									{{ $category->name }}
								</option>
							@endforeach
						</select>
						@error('category_id') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="unit_id">Satuan</label>
						<select id="unit_id" name="unit_id" class="form-control" required>
							<option value="" disabled {{ old('unit_id') ? '' : 'selected' }}>Pilih satuan</option>
							@foreach ($units as $unit)
								<option value="{{ $unit->id }}" {{ (int) old('unit_id') === $unit->id ? 'selected' : '' }}>
									{{ $unit->name }}{{ $unit->abbreviation ? ' ('.$unit->abbreviation.')' : '' }}
								</option>
							@endforeach
						</select>
						@error('unit_id') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="row">
						<div class="col-sm-6">
							<div class="form-group">
								<label for="sale_price">Harga Jual</label>
								<input class="form-control" id="sale_price" name="sale_price" type="number" min="0" step="0.01" value="{{ old('sale_price', 0) }}" required>
								@error('sale_price') <span class="text-danger">{{ $message }}</span> @enderror
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group">
								<label for="cost_price">HPP</label>
								<input class="form-control" id="cost_price" name="cost_price" type="number" min="0" step="0.01" value="{{ old('cost_price', 0) }}" required>
								@error('cost_price') <span class="text-danger">{{ $message }}</span> @enderror
							</div>
						</div>
					</div>

					<div class="form-group">
						<label for="batch_code">Batch</label>
						<input class="form-control" id="batch_code" name="batch_code" type="text" value="{{ old('batch_code') }}">
						@error('batch_code') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="expires_at">Kadaluarsa</label>
						<input class="form-control" id="expires_at" name="expires_at" type="date" value="{{ old('expires_at') }}">
						@error('expires_at') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="checkbox primary">
						<input id="is_taxable" name="is_taxable" type="checkbox" value="1" {{ old('is_taxable') ? 'checked' : '' }}>
						<label for="is_taxable">Kena PPN</label>
					</div>

					<div class="checkbox primary">
						<input id="block_when_out_of_stock" name="block_when_out_of_stock" type="checkbox" value="1" {{ old('block_when_out_of_stock') ? 'checked' : '' }}>
						<label for="block_when_out_of_stock">Blok penjualan saat stok nol</label>
					</div>

					<div class="checkbox primary">
						<input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
						<label for="is_active">Aktif</label>
					</div>

					<div class="form-group">
						<a href="{{ route('products.index') }}" class="btn btn-default">Batal</a>
						<button type="submit" class="btn btn-primary">Simpan</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
