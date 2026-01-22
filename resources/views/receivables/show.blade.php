@extends('layouts.app')

@section('title', 'Detail Piutang')
@section('page-title', 'Detail Piutang')

@section('content')
	<div class="row small-spacing">
		<div class="col-lg-6 col-xs-12">
			@if (session('status'))
				<div class="alert alert-success">
					{{ session('status') }}
				</div>
			@endif

			<div class="box-content">
				<h4 class="box-title">Informasi Piutang</h4>
				<table class="table">
					<tr>
						<th>No. Transaksi</th>
						<td>{{ $sale->reference_no }}</td>
					</tr>
					<tr>
						<th>Pelanggan</th>
						<td>{{ $sale->customer_name ?? '-' }}</td>
					</tr>
					<tr>
						<th>No. HP</th>
						<td>{{ $sale->customer_phone ?? '-' }}</td>
					</tr>
					<tr>
						<th>Lokasi</th>
						<td>{{ $sale->location?->name ?? '-' }}</td>
					</tr>
					<tr>
						<th>Total</th>
						<td>{{ number_format((float) $sale->total, 2, ',', '.') }}</td>
					</tr>
					<tr>
						<th>Dibayar</th>
						<td>{{ number_format((float) $sale->paid_total, 2, ',', '.') }}</td>
					</tr>
					<tr>
						<th>Sisa</th>
						<td>{{ number_format((float) $sale->receivable_balance, 2, ',', '.') }}</td>
					</tr>
					<tr>
						<th>Status</th>
						<td>
							<span class="label {{ $sale->payment_status === 'paid' ? 'label-success' : ($sale->payment_status === 'partial' ? 'label-warning' : 'label-default') }}">
								{{ $sale->payment_status === 'paid' ? 'Lunas' : ($sale->payment_status === 'partial' ? 'Sebagian' : 'Belum') }}
							</span>
						</td>
					</tr>
				</table>
			</div>

			<div class="box-content margin-top-20">
				<h4 class="box-title">Terima Pembayaran</h4>
				@php($isPaid = $sale->payment_status === 'paid')
				@if ($isPaid)
					<div class="alert alert-info">
						Piutang ini sudah lunas. Form pembayaran dinonaktifkan.
					</div>
				@endif
				<form method="POST" action="{{ route('receivables.store', $sale) }}">
					@csrf
					<div class="form-group">
						<label for="paid_at">Tanggal Bayar</label>
						<input id="paid_at" name="paid_at" type="date" class="form-control" value="{{ old('paid_at', now()->toDateString()) }}" {{ $isPaid ? 'disabled' : '' }}>
						@error('paid_at') <span class="text-danger">{{ $message }}</span> @enderror
					</div>
					<div class="form-group">
						<label for="method">Metode</label>
						<select id="method" name="method" class="form-control" required {{ $isPaid ? 'disabled' : '' }}>
							<option value="">Pilih metode</option>
							@foreach ($paymentMethods as $value => $label)
								<option value="{{ $value }}" {{ old('method') === $value ? 'selected' : '' }}>{{ $label }}</option>
							@endforeach
						</select>
						@error('method') <span class="text-danger">{{ $message }}</span> @enderror
					</div>
					<div class="form-group">
						<label for="amount">Jumlah</label>
						<input id="amount" name="amount" type="number" min="0.01" step="0.01" class="form-control" value="{{ old('amount', $sale->receivable_balance) }}" required {{ $isPaid ? 'disabled' : '' }}>
						@error('amount') <span class="text-danger">{{ $message }}</span> @enderror
					</div>
					<div class="form-group">
						<label for="reference_no">Referensi</label>
						<input id="reference_no" name="reference_no" type="text" class="form-control" value="{{ old('reference_no') }}" {{ $isPaid ? 'disabled' : '' }}>
						@error('reference_no') <span class="text-danger">{{ $message }}</span> @enderror
					</div>
					<div class="form-group">
						<a href="{{ route('receivables.index') }}" class="btn btn-default">Kembali</a>
						<button type="submit" class="btn btn-primary" {{ $isPaid ? 'disabled' : '' }}>Simpan Pembayaran</button>
					</div>
				</form>
			</div>
		</div>

		<div class="col-lg-6 col-xs-12">
			<div class="box-content">
				<h4 class="box-title">Riwayat Pembayaran</h4>
				<div class="table-responsive margin-top-10">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Tanggal</th>
								<th>Metode</th>
								<th>Jumlah</th>
								<th>Petugas</th>
								<th>Referensi</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($sale->settlements as $payment)
								<tr>
									<td>{{ optional($payment->paid_at)->format('d/m/Y') }}</td>
									<td>{{ strtoupper($payment->method) }}</td>
									<td>{{ number_format((float) $payment->amount, 2, ',', '.') }}</td>
									<td>{{ $payment->creator?->name ?? '-' }}</td>
									<td>{{ $payment->reference_no ?? '-' }}</td>
								</tr>
							@empty
								<tr>
									<td colspan="5" class="text-center">Belum ada pembayaran.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
@endsection
