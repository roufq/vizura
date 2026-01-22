@extends('layouts.app')

@section('title', 'Produk')
@section('page-title', 'Produk')

@section('content')
	<div class="row small-spacing">
		<div class="col-xs-12">
			@if (session('status'))
				<div class="alert alert-success">
					{{ session('status') }}
				</div>
			@endif

			<div class="box-content">
				<div class="row">
					<div class="col-sm-6">
						<p class="margin-bottom-0">Total: {{ $products->total() }} produk</p>
					</div>
					<div class="col-sm-6 text-right">
						<form method="GET" action="{{ route('products.index') }}" class="form-inline pull-right">
							<div class="form-group">
								<input type="text" class="form-control input-sm" name="search" value="{{ $search }}" placeholder="Cari SKU/barcode/nama">
							</div>
							<button type="submit" class="btn btn-primary btn-sm">Cari</button>
							@if ($search !== '')
								<a href="{{ route('products.index') }}" class="btn btn-default btn-sm">Reset</a>
							@endif
							<a href="{{ route('products.create') }}" class="btn btn-success btn-sm">Tambah Produk</a>
						</form>
					</div>
				</div>

				<div class="table-responsive margin-top-20">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>SKU</th>
								<th>Barcode</th>
								<th>Nama</th>
								<th>Kategori</th>
								<th>Satuan</th>
								<th>Harga Jual</th>
								<th>HPP</th>
								<th>Pajak</th>
								<th>Status</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($products as $product)
								<tr>
									<td>{{ $product->sku }}</td>
									<td>{{ $product->barcode ?? '-' }}</td>
									<td>{{ $product->name }}</td>
									<td>{{ $product->category?->name ?? '-' }}</td>
									<td>{{ $product->unit?->name ?? '-' }}</td>
									<td>{{ number_format((float) $product->sale_price, 2, ',', '.') }}</td>
									<td>{{ number_format((float) $product->cost_price, 2, ',', '.') }}</td>
									<td>
										<span class="label {{ $product->is_taxable ? 'label-success' : 'label-default' }}">
											{{ $product->is_taxable ? 'PPN' : 'Non PPN' }}
										</span>
									</td>
									<td>
										<span class="label {{ $product->is_active ? 'label-success' : 'label-default' }}">
											{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
										</span>
									</td>
									<td>
										<a href="{{ route('products.edit', $product) }}" class="btn btn-xs btn-info">Edit</a>
										<form method="POST" action="{{ route('products.destroy', $product) }}" style="display:inline" onsubmit="return confirm('Hapus produk ini?')">
											@csrf
											@method('DELETE')
											<button type="submit" class="btn btn-xs btn-danger">Hapus</button>
										</form>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="10" class="text-center">Belum ada produk.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>

				<div class="text-center">
					{{ $products->links('pagination::bootstrap-4') }}
				</div>
			</div>
		</div>
	</div>
@endsection
