@extends('layouts.app')

@section('title', 'Pembelian')
@section('page-title', 'Pembelian')

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
						<p class="margin-bottom-0">Total: {{ $purchases->total() }} pembelian</p>
					</div>
					<div class="col-sm-6 text-right">
						<a href="{{ route('purchases.create') }}" class="btn btn-success btn-sm">Buat Pembelian</a>
					</div>
				</div>

				<div class="table-responsive margin-top-20">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>No. Dokumen</th>
								<th>Supplier</th>
								<th>Total</th>
								<th>Status</th>
								<th>Diterima</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($purchases as $purchase)
								<tr>
									<td>{{ $purchase->reference_no }}</td>
									<td>{{ $purchase->supplier?->name ?? '-' }}</td>
									<td>{{ number_format((float) $purchase->total, 2, ',', '.') }}</td>
									<td>
										<span class="label {{ $purchase->status === 'received' ? 'label-success' : 'label-default' }}">
											{{ ucfirst($purchase->status) }}
										</span>
									</td>
									<td>{{ $purchase->receiver?->name ?? '-' }}</td>
								</tr>
							@empty
								<tr>
									<td colspan="5" class="text-center">Belum ada pembelian.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>

				<div class="text-center">
					{{ $purchases->links('pagination::bootstrap-4') }}
				</div>
			</div>
		</div>
	</div>
@endsection
