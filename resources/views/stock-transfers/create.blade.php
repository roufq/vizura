@extends('layouts.app')

@section('title', 'Buat Transfer Stok')
@section('page-title', 'Buat Transfer Stok')

@section('content')
	<div class="row small-spacing">
		<div class="col-lg-10 col-xs-12">
			<div class="box-content">
				<form method="POST" action="{{ route('stock-transfers.store') }}">
					@csrf

					<div class="row">
						<div class="col-sm-4">
							<div class="form-group">
								<label for="reference_no">No. Dokumen</label>
								<input class="form-control" id="reference_no" name="reference_no" type="text" value="{{ old('reference_no', $referenceNo) }}" required>
								@error('reference_no') <span class="text-danger">{{ $message }}</span> @enderror
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<label for="source_location_id">Lokasi Sumber</label>
								<select id="source_location_id" name="source_location_id" class="form-control" required>
									@if ($sourceLocation)
										<option value="{{ $sourceLocation->id }}" selected>{{ $sourceLocation->name }}</option>
									@else
										<option value="" selected disabled>Pilih lokasi</option>
									@endif
								</select>
								@error('source_location_id') <span class="text-danger">{{ $message }}</span> @enderror
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<label for="destination_location_id">Lokasi Tujuan</label>
								<select id="destination_location_id" name="destination_location_id" class="form-control" required>
									<option value="" disabled {{ old('destination_location_id') ? '' : 'selected' }}>Pilih lokasi</option>
									@foreach ($locations as $location)
										<option value="{{ $location->id }}" {{ (int) old('destination_location_id') === $location->id ? 'selected' : '' }}>
											{{ $location->name }}
										</option>
									@endforeach
								</select>
								@error('destination_location_id') <span class="text-danger">{{ $message }}</span> @enderror
							</div>
						</div>
					</div>

					<div class="table-responsive margin-top-10">
						<table class="table table-bordered">
							<thead>
								<tr>
									<th style="width:60%;">Produk</th>
									<th style="width:40%;">Qty</th>
								</tr>
							</thead>
							<tbody>
								@for ($i = 0; $i < 3; $i++)
									<tr>
										<td>
											<select name="items[{{ $i }}][product_id]" class="form-control js__select2" data-min-results="0">
												<option value="">Pilih produk</option>
												@foreach ($products as $product)
													<option value="{{ $product->id }}" {{ (int) old('items.'.$i.'.product_id') === $product->id ? 'selected' : '' }}>
														{{ $product->name }} ({{ $product->sku }})
													</option>
												@endforeach
											</select>
										</td>
										<td>
											<input type="number" step="0.01" class="form-control" name="items[{{ $i }}][quantity]" value="{{ old('items.'.$i.'.quantity') }}">
										</td>
									</tr>
								@endfor
							</tbody>
						</table>
					</div>
					@error('items') <span class="text-danger">{{ $message }}</span> @enderror

					<div class="form-group">
						<a href="{{ route('stock-transfers.index') }}" class="btn btn-default">Batal</a>
						<button type="submit" class="btn btn-primary">Simpan Transfer</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
