@extends('layouts.app')

@section('title', 'Tambah Biaya Operasional')
@section('page-title', 'Tambah Biaya Operasional')

@section('content')
	<div class="row small-spacing">
		<div class="col-lg-8 col-xs-12">
			<div class="box-content">
				<form method="POST" action="{{ route('expenses.store') }}">
					@csrf

					<div class="form-group">
						<label for="reference_no">No. Referensi</label>
						<input class="form-control" id="reference_no" name="reference_no" type="text" value="{{ old('reference_no', $referenceNo) }}" required>
						@error('reference_no') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="expense_date">Tanggal</label>
						<input class="form-control" id="expense_date" name="expense_date" type="date" value="{{ old('expense_date', now()->toDateString()) }}" required>
						@error('expense_date') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="account_id">Akun Biaya</label>
						<select id="account_id" name="account_id" class="form-control js__select2" data-min-results="0" required>
							<option value="" disabled {{ old('account_id') ? '' : 'selected' }}>Pilih akun biaya</option>
							@foreach ($expenseAccounts as $account)
								<option value="{{ $account->id }}" {{ (int) old('account_id') === $account->id ? 'selected' : '' }}>
									{{ $account->code }} - {{ $account->name }}
								</option>
							@endforeach
						</select>
						@error('account_id') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="payment_account_id">Akun Pembayaran</label>
						<select id="payment_account_id" name="payment_account_id" class="form-control js__select2" data-min-results="0" required>
							<option value="" disabled {{ old('payment_account_id') ? '' : 'selected' }}>Pilih akun pembayaran</option>
							@foreach ($paymentAccounts as $account)
								<option value="{{ $account->id }}" {{ (int) old('payment_account_id') === $account->id ? 'selected' : '' }}>
									{{ $account->code }} - {{ $account->name }}
								</option>
							@endforeach
						</select>
						@error('payment_account_id') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="amount">Jumlah</label>
						<input class="form-control" id="amount" name="amount" type="number" min="0" step="0.01" value="{{ old('amount') }}" required>
						@error('amount') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="description">Keterangan</label>
						<textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
						@error('description') <span class="text-danger">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<a href="{{ route('expenses.index') }}" class="btn btn-default">Batal</a>
						<button type="submit" class="btn btn-primary">Simpan Biaya</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
