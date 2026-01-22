@extends('layouts.app')

@section('title', 'Struk Penjualan')
@section('page-title', 'Struk Penjualan')

@section('content')
	<style>
		@media print {
			body * {
				visibility: hidden;
			}
			#receipt-area, #receipt-area * {
				visibility: visible;
			}
			#receipt-area {
				position: absolute;
				left: 0;
				top: 0;
				width: 100%;
			}
			.print-actions {
				display: none !important;
			}
		}
	</style>
	<div class="row small-spacing">
		<div class="col-lg-6 col-lg-offset-3 col-xs-12">
			<div class="box-content" id="receipt-area">
				<div class="text-center">
					<h4 class="margin-bottom-0">{{ $sale->location?->name ?? config('app.name') }}</h4>
					<p class="text-muted margin-top-5">
						{{ $sale->location?->address ?? '-' }}
					</p>
					<p class="text-muted margin-top-5">
						Telp: {{ $sale->location?->phone ?? '-' }}
					</p>
				</div>

				<hr>

				<table class="table table-borderless margin-bottom-0">
					<tr>
						<td>No. Transaksi</td>
						<td class="text-right">{{ $sale->reference_no }}</td>
					</tr>
					<tr>
						<td>Tanggal</td>
						<td class="text-right">{{ $sale->posted_at?->format('d/m/Y H:i') ?? $sale->created_at?->format('d/m/Y H:i') }}</td>
					</tr>
					<tr>
						<td>Kasir</td>
						<td class="text-right">{{ $sale->cashier?->name ?? '-' }}</td>
					</tr>
					<tr>
						<td>Jenis</td>
						<td class="text-right">{{ $sale->type === 'return' ? 'Retur' : 'Penjualan' }}</td>
					</tr>
					<tr>
						<td>Status</td>
						<td class="text-right">{{ ucfirst($sale->status) }}</td>
					</tr>
				</table>

				@if ($sale->customer_name || $sale->customer_phone)
					<hr>
					<table class="table table-borderless margin-bottom-0">
						<tr>
							<td>Pelanggan</td>
							<td class="text-right">{{ $sale->customer_name ?? '-' }}</td>
						</tr>
						<tr>
							<td>No. HP</td>
							<td class="text-right">{{ $sale->customer_phone ?? '-' }}</td>
						</tr>
					</table>
				@endif

				<hr>

				<table class="table table-striped">
					<thead>
						<tr>
							<th>Item</th>
							<th class="text-right">Qty</th>
							<th class="text-right">Harga</th>
							<th class="text-right">Diskon</th>
							<th class="text-right">Subtotal</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($sale->items as $item)
							<tr>
								<td>{{ $item->product?->name ?? '-' }}</td>
								<td class="text-right">{{ number_format((float) $item->quantity, 2, ',', '.') }}</td>
								<td class="text-right">{{ number_format((float) $item->unit_price, 2, ',', '.') }}</td>
								<td class="text-right">{{ number_format((float) $item->line_discount, 2, ',', '.') }}</td>
								<td class="text-right">{{ number_format((float) $item->line_total, 2, ',', '.') }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>

				<table class="table table-borderless">
					<tr>
						<td>Subtotal</td>
						<td class="text-right">{{ number_format((float) $sale->subtotal, 2, ',', '.') }}</td>
					</tr>
					<tr>
						<td>Diskon Order</td>
						<td class="text-right">{{ number_format((float) $sale->order_discount, 2, ',', '.') }}</td>
					</tr>
					<tr>
						<td>Pajak</td>
						<td class="text-right">{{ number_format((float) $sale->tax_amount, 2, ',', '.') }}</td>
					</tr>
					<tr>
						<td>Total</td>
						<td class="text-right"><strong>{{ number_format((float) $sale->total, 2, ',', '.') }}</strong></td>
					</tr>
				</table>

				@if ($sale->payments->isNotEmpty())
					<hr>
					<table class="table table-borderless margin-bottom-0">
						@foreach ($sale->payments as $payment)
							<tr>
								<td>{{ strtoupper($payment->method) }}</td>
								<td class="text-right">{{ number_format((float) $payment->amount, 2, ',', '.') }}</td>
							</tr>
						@endforeach
						<tr>
							<td>Dibayar</td>
							<td class="text-right">{{ number_format((float) $sale->paid_total, 2, ',', '.') }}</td>
						</tr>
						<tr>
							<td>Kembalian</td>
							<td class="text-right">{{ number_format((float) $sale->change_due, 2, ',', '.') }}</td>
						</tr>
					</table>
				@endif

				@if ($sale->notes)
					<hr>
					<p class="text-muted">Catatan: {{ $sale->notes }}</p>
				@endif

				<hr>
				<p class="text-center">Terima kasih telah berbelanja.</p>
			</div>

			<div class="margin-top-20 text-center print-actions">
				<a href="{{ route('sales.index') }}" class="btn btn-default">Kembali</a>
				<button type="button" class="btn btn-primary" onclick="window.print()">Cetak Struk</button>
			</div>
		</div>
	</div>
@endsection
