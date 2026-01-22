@extends('layouts.app')

@section('title', 'Akses Lokasi Manager')
@section('page-title', 'Akses Lokasi Manager')

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
						<p class="margin-bottom-0">Total: {{ $managers->total() }} manager</p>
					</div>
					<div class="col-sm-6 text-right">
						<form method="GET" action="{{ route('manager-locations.index') }}" class="form-inline pull-right">
							<div class="form-group">
								<label for="search">Cari</label>
								<input type="text" id="search" class="form-control input-sm" name="search" value="{{ $search }}" placeholder="Nama/Email">
							</div>
							<button type="submit" class="btn btn-primary btn-sm">Cari</button>
							@if ($search !== '')
								<a href="{{ route('manager-locations.index') }}" class="btn btn-default btn-sm">Reset</a>
							@endif
						</form>
					</div>
				</div>

				<div class="table-responsive margin-top-20">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Nama</th>
								<th>Email</th>
								<th>Lokasi</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($managers as $manager)
								<tr>
									<td>{{ $manager->name }}</td>
									<td>{{ $manager->email }}</td>
									<td>
										@if ($manager->locations->isEmpty())
											<span class="text-muted">Belum diatur</span>
										@else
											{{ $manager->locations->pluck('name')->implode(', ') }}
										@endif
									</td>
									<td>
										<a href="{{ route('manager-locations.edit', $manager) }}" class="btn btn-xs btn-info">Atur Lokasi</a>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="4" class="text-center">Belum ada manager.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>

				<div class="text-center">
					{{ $managers->links('pagination::bootstrap-4') }}
				</div>
			</div>
		</div>
	</div>
@endsection
