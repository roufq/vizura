@extends('layouts.app')

@section('title', 'Buat Pembelian')
@section('page-title', 'Buat Pembelian')

@section('content')
	<div class="row small-spacing">
		<div class="col-lg-10 col-xs-12">
			<div class="box-content">
				<form method="POST" action="{{ route('purchases.store') }}">
					@csrf

					<div class="row">
						<div class="col-sm-6">
							<div class="form-group">
								<label for="reference_no">No. Dokumen</label>
								<input class="form-control" id="reference_no" name="reference_no" type="text" value="{{ old('reference_no', $referenceNo) }}" required>
								@error('reference_no') <span class="text-danger">{{ $message }}</span> @enderror
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group">
								<label for="supplier_id">Supplier</label>
								<select id="supplier_id" name="supplier_id" class="form-control">
									<option value="">Pilih supplier</option>
									@foreach ($suppliers as $supplier)
										<option value="{{ $supplier->id }}" {{ (int) old('supplier_id') === $supplier->id ? 'selected' : '' }}>
											{{ $supplier->name }}
										</option>
									@endforeach
								</select>
								@error('supplier_id') <span class="text-danger">{{ $message }}</span> @enderror
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-sm-6">
							<div class="form-group">
								<label for="payment_method">Metode Pembayaran</label>
								<select id="payment_method" name="payment_method" class="form-control" required>
									<option value="payable" {{ old('payment_method', 'payable') === 'payable' ? 'selected' : '' }}>Hutang</option>
									<option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
									<option value="bank" {{ old('payment_method') === 'bank' ? 'selected' : '' }}>Bank</option>
								</select>
								@error('payment_method') <span class="text-danger">{{ $message }}</span> @enderror
							</div>
						</div>
					</div>

					<div class="table-responsive margin-top-10">
						<table class="table table-bordered">
							<thead>
								<tr>
									<th style="width:45%;">Produk</th>
									<th style="width:20%;">Qty</th>
									<th style="width:25%;">HPP</th>
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
										<td>
											<input type="number" step="0.01" class="form-control" name="items[{{ $i }}][unit_cost]" value="{{ old('items.'.$i.'.unit_cost') }}">
										</td>
									</tr>
								@endfor
							</tbody>
						</table>
					</div>
					@error('items') <span class="text-danger">{{ $message }}</span> @enderror

					<div class="row">
						<div class="col-sm-6">
							<div class="form-group">
								<label for="discount_amount">Diskon</label>
								<input class="form-control" id="discount_amount" name="discount_amount" type="number" min="0" step="0.01" value="{{ old('discount_amount', 0) }}">
								@error('discount_amount') <span class="text-danger">{{ $message }}</span> @enderror
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group">
								<label for="tax_amount">Pajak</label>
								<input class="form-control" id="tax_amount" name="tax_amount" type="number" min="0" step="0.01" value="{{ old('tax_amount', 0) }}">
								@error('tax_amount') <span class="text-danger">{{ $message }}</span> @enderror
							</div>
						</div>
					</div>

					<div class="form-group">
						<a href="{{ route('purchases.index') }}" class="btn btn-default">Batal</a>
						<button type="submit" class="btn btn-primary">Simpan Pembelian</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
