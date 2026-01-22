@extends('layouts.app')

@section('title', 'Transfer Stok')
@section('page-title', 'Transfer Stok')

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
						<p class="margin-bottom-0">Total: {{ $transfers->total() }} transfer</p>
					</div>
					<div class="col-sm-6 text-right">
						<a href="{{ route('stock-transfers.create') }}" class="btn btn-success btn-sm">Buat Transfer</a>
					</div>
				</div>

				<div class="table-responsive margin-top-20">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>No. Dokumen</th>
								<th>Sumber</th>
								<th>Tujuan</th>
								<th>Status</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($transfers as $transfer)
								<tr>
									<td>{{ $transfer->reference_no }}</td>
									<td>{{ $transfer->sourceLocation?->name ?? '-' }}</td>
									<td>{{ $transfer->destinationLocation?->name ?? '-' }}</td>
									<td>
										<span class="label {{ $transfer->status === 'received' ? 'label-success' : ($transfer->status === 'sent' ? 'label-info' : 'label-warning') }}">
											{{ ucfirst($transfer->status) }}
										</span>
									</td>
									<td>
										@if ($transfer->status === 'draft')
											<form method="POST" action="{{ route('stock-transfers.send', $transfer) }}" style="display:inline">
												@csrf
												<button type="submit" class="btn btn-xs btn-primary" onclick="return confirm('Kirim transfer ini?')">Kirim</button>
											</form>
											<form method="POST" action="{{ route('stock-transfers.destroy', $transfer) }}" style="display:inline" onsubmit="return confirm('Hapus transfer ini?')">
												@csrf
												@method('DELETE')
												<button type="submit" class="btn btn-xs btn-danger">Hapus</button>
											</form>
										@elseif ($transfer->status === 'sent')
											<form method="POST" action="{{ route('stock-transfers.receive', $transfer) }}" style="display:inline">
												@csrf
												<button type="submit" class="btn btn-xs btn-success" onclick="return confirm('Terima transfer ini?')">Terima</button>
											</form>
										@else
											<span class="text-muted">-</span>
										@endif
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="5" class="text-center">Belum ada transfer stok.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>

				<div class="text-center">
					{{ $transfers->links('pagination::bootstrap-4') }}
				</div>
			</div>
		</div>
	</div>
@endsection
