@extends('layouts.app')

@section('title', 'Penjualan (POS)')
@section('page-title', 'Penjualan (POS)')

@section('content')
	<div class="row small-spacing">
		<div class="col-xs-12">
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
				<div class="row">
					<div class="col-sm-6">
						<p class="margin-bottom-0">Total: {{ $sales->total() }} transaksi</p>
					</div>
					<div class="col-sm-6 text-right">
						<a href="{{ route('sales.create') }}" class="btn btn-success btn-sm">Transaksi Baru</a>
					</div>
				</div>

				<div class="table-responsive margin-top-20">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>No. Transaksi</th>
								<th>Tanggal</th>
								<th>Kasir</th>
								<th>Pelanggan</th>
								<th>Jenis</th>
								<th>Status</th>
								<th>Total</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($sales as $sale)
								<tr>
									<td>{{ $sale->reference_no }}</td>
									<td>{{ $sale->created_at?->format('d/m/Y H:i') }}</td>
									<td>{{ $sale->cashier?->name ?? '-' }}</td>
									<td>{{ $sale->customer_name ?? '-' }}</td>
									<td>{{ strtoupper($sale->type) }}</td>
									<td>
										<span class="label {{ $sale->status === 'posted' ? 'label-success' : ($sale->status === 'draft' ? 'label-warning' : 'label-default') }}">
											{{ ucfirst($sale->status) }}
										</span>
									</td>
									<td>{{ number_format((float) $sale->total, 2, ',', '.') }}</td>
									<td>
										@if ($sale->status === 'posted' && $sale->type === 'sale')
											<a href="{{ route('sales.receipt', $sale) }}" class="btn btn-xs btn-default">Struk</a>
											<form method="POST" action="{{ route('sales.void', $sale) }}" style="display:inline">
												@csrf
												<input type="hidden" name="void_reason" value="Void via POS">
												<button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Void transaksi ini?')">Void</button>
											</form>
											<form method="POST" action="{{ route('sales.return', $sale) }}" style="display:inline">
												@csrf
												<input type="hidden" name="return_reason" value="Retur via POS">
												<button type="submit" class="btn btn-xs btn-info" onclick="return confirm('Retur transaksi ini?')">Retur</button>
											</form>
										@elseif ($sale->status !== 'draft')
											<a href="{{ route('sales.receipt', $sale) }}" class="btn btn-xs btn-default">Struk</a>
										@else
											<a href="{{ route('sales.resume', $sale) }}" class="btn btn-xs btn-primary">Lanjutkan</a>
										@endif
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="8" class="text-center">Belum ada transaksi.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>

				<div class="text-center">
					{{ $sales->links('pagination::bootstrap-4') }}
				</div>
			</div>
		</div>
	</div>
@endsection
