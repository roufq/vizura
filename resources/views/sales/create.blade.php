@extends('layouts.app')

@section('title', 'Transaksi POS')
@section('page-title', 'Transaksi POS')

@section('content')
	<div class="row small-spacing">
		<div class="col-lg-8 col-xs-12">
			<div class="box-content">
				<form method="POST" action="{{ route('sales.store') }}" id="pos-form">
					@csrf
					@if (isset($draft) && $draft)
						<input type="hidden" name="draft_id" value="{{ $draft->id }}">
					@endif

					<div class="row">
						<div class="col-sm-6">
							<div class="form-group">
								<label for="reference_no">No. Transaksi</label>
								<input class="form-control" id="reference_no" name="reference_no" type="text" value="{{ old('reference_no', $referenceNo) }}" required>
								@error('reference_no') <span class="text-danger">{{ $message }}</span> @enderror
							</div>
							@if ($location)
								<p class="text-muted">Lokasi aktif: <strong>{{ $location->name }}</strong></p>
							@endif
						</div>
						<div class="col-sm-6 text-right">
							<a class="btn btn-link btn-sm" data-toggle="collapse" href="#pos-extra" aria-expanded="false" aria-controls="pos-extra">
								Data pelanggan & catatan
							</a>
							<a class="btn btn-link btn-sm" data-toggle="collapse" href="#pos-drafts" aria-expanded="false" aria-controls="pos-drafts">
								Draft POS
							</a>
						</div>
					</div>

					<div id="pos-extra" class="collapse">
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label for="customer_name">Pelanggan</label>
									<input class="form-control" id="customer_name" name="customer_name" type="text" value="{{ old('customer_name', $draft?->customer_name) }}" placeholder="Nama pelanggan">
									@error('customer_name') <span class="text-danger">{{ $message }}</span> @enderror
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label for="customer_phone">No. HP</label>
									<input class="form-control" id="customer_phone" name="customer_phone" type="text" value="{{ old('customer_phone', $draft?->customer_phone) }}" placeholder="08xxxxxxxxx">
									@error('customer_phone') <span class="text-danger">{{ $message }}</span> @enderror
								</div>
							</div>
						</div>

						<div class="form-group">
							<label for="notes">Catatan</label>
							<textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Catatan transaksi">{{ old('notes', $draft?->notes) }}</textarea>
							@error('notes') <span class="text-danger">{{ $message }}</span> @enderror
						</div>
					</div>

					<div id="pos-drafts" class="collapse">
						<div class="table-responsive margin-top-10">
							<table class="table table-striped">
								<thead>
									<tr>
										<th>Nama</th>
										<th>Qty</th>
										<th>Total</th>
										<th>Aksi</th>
									</tr>
								</thead>
								<tbody>
									@forelse ($drafts ?? [] as $draftItem)
										<tr>
											<td>{{ $draftItem->customer_name ?? $draftItem->reference_no }}</td>
											<td>{{ number_format((float) $draftItem->items->sum('quantity'), 2, ',', '.') }}</td>
											<td>{{ number_format((float) $draftItem->total, 2, ',', '.') }}</td>
											<td>
												<button type="button" class="btn btn-xs btn-default" data-toggle="collapse" data-target="#draft-detail-{{ $draftItem->id }}" aria-expanded="false">
													Detail
												</button>
											</td>
										</tr>
										<tr class="collapse" id="draft-detail-{{ $draftItem->id }}">
											<td colspan="4">
												<div class="row">
													<div class="col-sm-6">
														<p class="margin-bottom-5"><strong>No. Transaksi:</strong> {{ $draftItem->reference_no }}</p>
														<p class="margin-bottom-5"><strong>Pelanggan:</strong> {{ $draftItem->customer_name ?? '-' }}</p>
														<p class="margin-bottom-5"><strong>No. HP:</strong> {{ $draftItem->customer_phone ?? '-' }}</p>
														<p class="margin-bottom-5"><strong>Catatan:</strong> {{ $draftItem->notes ?? '-' }}</p>
													</div>
													<div class="col-sm-6 text-right">
														<p class="margin-bottom-5"><strong>Kasir:</strong> {{ $draftItem->cashier?->name ?? '-' }}</p>
														<p class="margin-bottom-5"><strong>Dibuat:</strong> {{ $draftItem->created_at?->format('d/m/Y H:i') }}</p>
														<p class="margin-bottom-5"><strong>Status:</strong> Draft</p>
													</div>
												</div>
												<div class="table-responsive margin-top-10">
													<table class="table table-bordered">
														<thead>
															<tr>
																<th>Produk</th>
																<th>Qty</th>
																<th>Harga</th>
																<th>Diskon</th>
																<th>Subtotal</th>
															</tr>
														</thead>
														<tbody>
															@foreach ($draftItem->items as $item)
																<tr>
																	<td>{{ $item->product?->name ?? '-' }}</td>
																	<td>{{ number_format((float) $item->quantity, 2, ',', '.') }}</td>
																	<td>{{ number_format((float) $item->unit_price, 2, ',', '.') }}</td>
																	<td>{{ number_format((float) $item->line_discount, 2, ',', '.') }}</td>
																	<td>{{ number_format((float) $item->line_total, 2, ',', '.') }}</td>
																</tr>
															@endforeach
														</tbody>
													</table>
												</div>
												<div class="text-right margin-top-10">
													<a href="{{ route('sales.resume', $draftItem) }}" class="btn btn-xs btn-primary">Lanjutkan</a>
													<button type="submit" class="btn btn-xs btn-danger" form="draft-delete-{{ $draftItem->id }}" onclick="return confirm('Hapus draft ini?')">Hapus</button>
												</div>
											</td>
										</tr>
									@empty
										<tr>
											<td colspan="4" class="text-center">Belum ada draft.</td>
										</tr>
									@endforelse
								</tbody>
							</table>
						</div>
					</div>

					<div class="row margin-top-10">
						<div class="col-sm-7">
							<div class="form-group">
								<label for="scan_input">Scan Cepat (Barcode/SKU/Nama)</label>
								<div class="input-group">
									<input type="text" id="scan_input" class="form-control" placeholder="Scan barcode atau ketik SKU" autocomplete="off" autofocus>
									<span class="input-group-btn">
										<button class="btn btn-primary" type="button" id="add_by_scan">Tambah</button>
									</span>
								</div>
							</div>
						</div>
						<div class="col-sm-5">
							<div class="form-group">
								<label for="product_select">Tambah Produk Manual</label>
								<div class="input-group">
									<select id="product_select" class="form-control js__select2" data-min-results="0">
										<option value="">Pilih produk</option>
										@foreach ($products as $product)
											<option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
										@endforeach
									</select>
									<span class="input-group-btn">
										<button class="btn btn-default" type="button" id="add_manual">Tambah</button>
									</span>
								</div>
							</div>
						</div>
					</div>

					<div id="stock-warning" class="alert alert-danger" style="display:none;">
						Stok tidak cukup untuk beberapa item. Kurangi qty atau pilih produk lain.
					</div>
					<div id="cart-warning" class="alert alert-warning" style="display:none;">
						Tambahkan minimal satu item sebelum bayar.
					</div>

					<div class="table-responsive margin-top-10">
						<table class="table table-bordered">
							<thead>
								<tr>
									<th style="width:36%;">Produk</th>
									<th style="width:10%;">Qty</th>
									<th style="width:18%;">Harga</th>
									<th style="width:16%;">Diskon</th>
									<th style="width:17%;">Subtotal</th>
									<th style="width:3%;"></th>
								</tr>
							</thead>
							<tbody id="cart-body"></tbody>
						</table>
					</div>
					@error('items') <span class="text-danger">{{ $message }}</span> @enderror

					<div class="form-group">
						<a href="{{ route('sales.index') }}" class="btn btn-default">Batal</a>
						<button type="submit" name="action" value="draft" class="btn btn-warning">Simpan Draft</button>
					</div>
				</form>
			</div>
		</div>

		<div class="col-lg-4 col-xs-12">
			<div class="box-content">
				<h4 class="box-title">Ringkasan</h4>
				<table class="table">
					<tr>
						<th>Subtotal</th>
						<td class="text-right" id="subtotal_display">0</td>
					</tr>
					<tr>
						<th>Diskon Order</th>
						<td>
							<input class="form-control input-sm" id="order_discount" name="order_discount" type="number" min="0" step="0.01" value="{{ old('order_discount', $draft?->order_discount ?? 0) }}" form="pos-form">
							<div id="discount-warning" class="text-danger" style="display:none;">Diskon melebihi subtotal.</div>
							@error('order_discount') <span class="text-danger">{{ $message }}</span> @enderror
						</td>
					</tr>
					<tr>
						<th>Pajak (%)</th>
						<td>
							<input class="form-control input-sm" id="tax_rate" name="tax_rate" type="number" min="0" max="100" step="0.01" value="{{ old('tax_rate', $draftTaxRate ?? 0) }}" form="pos-form">
							<small class="text-muted">Nilai pajak: <span id="tax_amount_display">0</span></small>
							<div class="checkbox" style="margin-top:5px;">
								<label>
									<input id="is_tax_inclusive" name="is_tax_inclusive" type="checkbox" value="1" {{ old('is_tax_inclusive', $draft?->is_tax_inclusive) ? 'checked' : '' }} form="pos-form">
									Pajak inklusif
								</label>
							</div>
							@error('tax_rate') <span class="text-danger">{{ $message }}</span> @enderror
						</td>
					</tr>
					<tr>
						<th>Total</th>
						<td class="text-right"><strong id="total_display">0</strong></td>
					</tr>
					<tr>
						<th>Dibayar</th>
						<td class="text-right" id="paid_display">0</td>
					</tr>
					<tr>
						<th>Kembalian</th>
						<td class="text-right" id="change_display">0</td>
					</tr>
				</table>

				<h4 class="box-title margin-top-20">Pembayaran</h4>
				<div id="payment-rows"></div>
				<div class="margin-top-10">
					<button type="button" class="btn btn-default btn-sm" id="add_payment_row">Tambah Metode</button>
					<button type="button" class="btn btn-info btn-sm" id="set_exact_payment">Bayar Pas</button>
				</div>
				<div id="payment-warning" class="text-danger" style="display:none;">Total pembayaran masih kurang.</div>
				@error('payments') <span class="text-danger">{{ $message }}</span> @enderror

				<div class="margin-top-20">
					<button type="submit" name="action" value="post" class="btn btn-primary btn-block btn-lg" id="pay_button" form="pos-form">
						Bayar
					</button>
				</div>
			</div>
		</div>
	</div>

	@foreach ($drafts ?? [] as $draftItem)
		<form id="draft-delete-{{ $draftItem->id }}" method="POST" action="{{ route('sales.draft.destroy', $draftItem) }}" style="display:none;">
			@csrf
			@method('DELETE')
		</form>
	@endforeach

	<script>
		const products = @json($productsForJs);
		const productMap = new Map(products.map(product => [String(product.id), product]));
		const productLookup = new Map(products.map(product => [product.sku.toLowerCase(), product]));
		const productBarcodeLookup = new Map(
			products
				.filter(product => product.barcode)
				.map(product => [String(product.barcode).toLowerCase(), product])
		);
		const productNameLookup = new Map(products.map(product => [product.name.toLowerCase(), product]));

		const cartBody = document.getElementById('cart-body');
		const scanInput = document.getElementById('scan_input');
		const addScanButton = document.getElementById('add_by_scan');
		const addManualButton = document.getElementById('add_manual');
		const productSelect = document.getElementById('product_select');
		const stockWarning = document.getElementById('stock-warning');
		const cartWarning = document.getElementById('cart-warning');
		const payButton = document.getElementById('pay_button');

		const paymentRows = document.getElementById('payment-rows');
		const addPaymentRow = document.getElementById('add_payment_row');
		const setExactPayment = document.getElementById('set_exact_payment');
		const paymentWarning = document.getElementById('payment-warning');

		const subtotalDisplay = document.getElementById('subtotal_display');
		const totalDisplay = document.getElementById('total_display');
		const paidDisplay = document.getElementById('paid_display');
		const changeDisplay = document.getElementById('change_display');
		const orderDiscountInput = document.getElementById('order_discount');
		const taxRateInput = document.getElementById('tax_rate');
		const taxInclusiveInput = document.getElementById('is_tax_inclusive');
		const discountWarning = document.getElementById('discount-warning');
		const taxAmountDisplay = document.getElementById('tax_amount_display');

		const moneyFormat = value => new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);

		const cart = new Map();
		let currentTotal = 0;

		const initialItems = @json(old('items', $draftItems ?? []));
		initialItems.forEach(item => {
			if (!item || !item.product_id) {
				return;
			}
			const product = productMap.get(String(item.product_id));
			if (!product) {
				return;
			}
			const quantity = parseFloat(item.quantity || 1);
			const unitPrice = item.unit_price !== undefined ? parseFloat(item.unit_price) : product.price;
			const lineDiscount = parseFloat(item.line_discount || 0);
			addToCart(product, quantity, unitPrice, lineDiscount);
		});

		const initialPayments = @json(old('payments', []));
		if (initialPayments.length) {
			initialPayments.forEach(payment => addPaymentRowFields(payment.method || '', payment.amount || '', payment.reference_no || ''));
		} else {
			addPaymentRowFields('', '', '');
		}

		updateCartTable();
		updateTotals();

		addScanButton.addEventListener('click', () => handleScan(scanInput.value));
		scanInput.addEventListener('keydown', event => {
			if (event.key === 'Enter') {
				event.preventDefault();
				handleScan(scanInput.value);
			}
			if (event.key === 'Tab') {
				event.preventDefault();
				handleScan(scanInput.value);
			}
		});

		let scanBuffer = '';
		let scanLastTime = 0;
		let scanActive = false;

		document.addEventListener('keydown', event => {
			if (event.key === 'Shift' || event.key === 'Control' || event.key === 'Alt') {
				return;
			}

			const target = event.target;
			if (target === scanInput) {
				return;
			}
			const isEditableTarget = target instanceof HTMLElement && (
				target.tagName === 'INPUT' ||
				target.tagName === 'TEXTAREA' ||
				target.tagName === 'SELECT' ||
				target.isContentEditable
			);

			const now = Date.now();
			const delta = scanLastTime ? now - scanLastTime : null;
			if (delta !== null && delta > 200) {
				scanBuffer = '';
				scanActive = false;
			}

			if (event.key === 'Tab' || event.key === 'Enter') {
				if (scanBuffer) {
					event.preventDefault();
					handleScan(scanBuffer);
					scanBuffer = '';
					scanActive = false;
				}
				return;
			}

			if (event.key.length === 1) {
				const fastInput = delta !== null && delta < 50;
				scanActive = scanActive || fastInput || scanBuffer.length > 3;
				scanBuffer += event.key;
				if (scanActive && isEditableTarget) {
					event.preventDefault();
				}
			}

			scanLastTime = now;
		});

		addManualButton.addEventListener('click', () => {
			const productId = productSelect.value;
			if (!productId) {
				return;
			}
			const product = productMap.get(String(productId));
			if (product) {
				addToCart(product, 1, product.price, 0);
				updateCartTable();
			}
			productSelect.value = '';
			productSelect.dispatchEvent(new Event('change'));
			scanInput.focus();
		});

		addPaymentRow.addEventListener('click', () => {
			addPaymentRowFields('', '', '');
			updateTotals();
		});

		setExactPayment.addEventListener('click', () => {
			const total = currentTotal;
			const firstRow = paymentRows.querySelector('.payment-row');
			if (!firstRow) {
				addPaymentRowFields('cash', total, '');
				updateTotals();
				return;
			}
			const methodSelect = firstRow.querySelector('select');
			const amountInput = firstRow.querySelector('.payment-amount');
			if (methodSelect && !methodSelect.value) {
				methodSelect.value = 'cash';
			}
			if (amountInput) {
				amountInput.value = total.toFixed(2);
			}
			updateTotals();
		});

		orderDiscountInput.addEventListener('input', updateTotals);
		taxRateInput.addEventListener('input', updateTotals);
		taxInclusiveInput.addEventListener('change', updateTotals);

		function handleScan(value) {
			const keyword = (value || '').trim().toLowerCase();
			if (!keyword) {
				return;
			}
			let product = productBarcodeLookup.get(keyword);
			if (!product) {
				product = productLookup.get(keyword);
			}
			if (!product) {
				product = productNameLookup.get(keyword);
			}
			if (!product) {
				alert('Barcode tidak ditemukan.');
				scanInput.value = '';
				scanInput.focus();
				return;
			}
			addToCart(product, 1, product.price, 0);
			updateCartTable();
			scanInput.value = '';
			scanInput.focus();
		}

		function addToCart(product, quantity, unitPrice, lineDiscount) {
			const key = String(product.id);
			if (cart.has(key)) {
				const item = cart.get(key);
				item.quantity += quantity;
				item.unitPrice = unitPrice;
				item.lineDiscount = lineDiscount;
			} else {
				cart.set(key, {
					productId: product.id,
					name: product.name,
					sku: product.sku,
					unit: product.unit || '',
					price: product.price,
					stock: product.stock,
					block: product.block_when_out_of_stock,
					quantity: quantity,
					unitPrice: unitPrice,
					lineDiscount: lineDiscount,
				});
			}
		}

		function updateCartTable() {
			cartBody.innerHTML = '';
			let index = 0;
			cart.forEach(item => {
				const row = document.createElement('tr');
				const stockClass = item.block && item.quantity > item.stock ? 'text-danger' : '';
				row.innerHTML = `
					<td>
						<strong>${item.name}</strong><br>
						<small>${item.sku}${item.unit ? ' / ' + item.unit : ''}</small>
						<div class="text-muted stock-cell ${stockClass}">Stok: ${moneyFormat(item.stock)}</div>
						<input type="hidden" name="items[${index}][product_id]" value="${item.productId}" form="pos-form">
					</td>
					<td><input type="number" min="0.01" step="0.01" class="form-control input-sm qty-input" data-id="${item.productId}" value="${item.quantity}" form="pos-form"></td>
					<td>
						<input type="number" min="0" step="0.01" class="form-control input-sm price-input" data-id="${item.productId}" value="${item.unitPrice}" form="pos-form">
					</td>
					<td>
						<input type="number" min="0" step="0.01" class="form-control input-sm discount-input" data-id="${item.productId}" value="${item.lineDiscount}" form="pos-form">
					</td>
					<td class="text-right subtotal-cell">${moneyFormat(getLineTotal(item))}</td>
					<td><button type="button" class="btn btn-xs btn-danger remove-item" data-id="${item.productId}"><i class="fa fa-trash"></i></button></td>
					<input type="hidden" class="line-quantity" name="items[${index}][quantity]" value="${item.quantity}" form="pos-form">
					<input type="hidden" class="line-price" name="items[${index}][unit_price]" value="${item.unitPrice}" form="pos-form">
					<input type="hidden" class="line-discount" name="items[${index}][line_discount]" value="${item.lineDiscount}" form="pos-form">
				`;
				cartBody.appendChild(row);
				index += 1;
			});

			cartBody.querySelectorAll('.qty-input').forEach(input => {
				input.addEventListener('input', event => updateItemValue(event, 'quantity'));
			});
			cartBody.querySelectorAll('.price-input').forEach(input => {
				input.addEventListener('input', event => updateItemValue(event, 'unitPrice'));
			});
			cartBody.querySelectorAll('.discount-input').forEach(input => {
				input.addEventListener('input', event => updateItemValue(event, 'lineDiscount'));
			});
			cartBody.querySelectorAll('.remove-item').forEach(button => {
				button.addEventListener('click', event => {
					const id = event.currentTarget.getAttribute('data-id');
					cart.delete(String(id));
					updateCartTable();
				});
			});

			updateTotals();
		}

		function updateItemValue(event, field) {
			const input = event.target;
			const id = input.getAttribute('data-id');
			const value = parseFloat(input.value || 0);
			const item = cart.get(String(id));
			if (!item) {
				return;
			}
			item[field] = value;
			const row = input.closest('tr');
			if (row) {
				const lineTotalCell = row.querySelector('.subtotal-cell');
				const stockCell = row.querySelector('.stock-cell');
				const qtyField = row.querySelector('.line-quantity');
				const priceField = row.querySelector('.line-price');
				const discountField = row.querySelector('.line-discount');
				if (lineTotalCell) {
					lineTotalCell.textContent = moneyFormat(getLineTotal(item));
				}
				if (stockCell) {
					stockCell.classList.toggle('text-danger', item.block && item.quantity > item.stock);
				}
				if (qtyField) {
					qtyField.value = item.quantity;
				}
				if (priceField) {
					priceField.value = item.unitPrice;
				}
				if (discountField) {
					discountField.value = item.lineDiscount;
				}
			}
			updateTotals();
		}

		function getLineTotal(item) {
			const lineTotal = (item.quantity * item.unitPrice) - item.lineDiscount;
			return lineTotal > 0 ? lineTotal : 0;
		}

		function updateTotals() {
			let subtotal = 0;
			let hasStockIssue = false;
			cart.forEach(item => {
				subtotal += getLineTotal(item);
				if (item.block && item.quantity > item.stock) {
					hasStockIssue = true;
				}
			});

			const orderDiscount = parseFloat(orderDiscountInput.value || 0);
			const taxRate = parseFloat(taxRateInput.value || 0);
			const isInclusive = taxInclusiveInput.checked;

			const hasDiscountIssue = orderDiscount > subtotal;
			let total = subtotal - orderDiscount;
			total = total < 0 ? 0 : total;
			const rate = taxRate > 0 ? taxRate : 0;
			const taxAmount = rate > 0
				? (isInclusive ? (total - (total / (1 + (rate / 100)))) : (total * (rate / 100)))
				: 0;
			if (!isInclusive) {
				total += taxAmount;
			}

			let paidCash = 0;
			let receivableAmount = 0;
			let receivableInput = null;
			let hasReceivableMethod = false;

			document.querySelectorAll('.payment-row').forEach(row => {
				const method = row.querySelector('.payment-method');
				const amountInput = row.querySelector('.payment-amount');
				if (!method || !amountInput) {
					return;
				}

				const amount = parseFloat(amountInput.value || 0);
				if (method.value === 'piutang') {
					hasReceivableMethod = true;
					receivableAmount = amount;
					receivableInput = amountInput;
				} else {
					paidCash += amount;
				}
			});

			if (hasReceivableMethod && receivableInput) {
				const remaining = Math.max(0, total - paidCash);
				if (receivableAmount <= 0 && remaining > 0) {
					receivableAmount = remaining;
					receivableInput.value = remaining.toFixed(2);
				}
			}

			const paid = paidCash + (hasReceivableMethod ? receivableAmount : 0);
			const change = paidCash > total ? (paidCash - total) : 0;
			const hasPaymentIssue = cart.size > 0 && paid < total;

			currentTotal = total;
			subtotalDisplay.textContent = moneyFormat(subtotal);
			totalDisplay.textContent = moneyFormat(total);
			paidDisplay.textContent = moneyFormat(paid);
			changeDisplay.textContent = moneyFormat(change);
			taxAmountDisplay.textContent = moneyFormat(taxAmount);

			discountWarning.style.display = hasDiscountIssue ? 'block' : 'none';
			paymentWarning.style.display = hasPaymentIssue ? 'block' : 'none';

			stockWarning.style.display = hasStockIssue ? 'block' : 'none';
			cartWarning.style.display = cart.size === 0 ? 'block' : 'none';
			payButton.disabled = hasStockIssue || hasDiscountIssue || hasPaymentIssue || cart.size === 0;
		}

		function addPaymentRowFields(method, amount, reference) {
			const index = paymentRows.querySelectorAll('.payment-row').length;
			const row = document.createElement('div');
			row.className = 'row payment-row margin-bottom-10';
			row.innerHTML = `
				<div class="col-xs-5">
					<select class="form-control input-sm payment-method" name="payments[${index}][method]" form="pos-form">
						<option value="">Metode</option>
						<option value="cash" ${method === 'cash' ? 'selected' : ''}>Cash</option>
						<option value="card" ${method === 'card' ? 'selected' : ''}>Card</option>
						<option value="transfer" ${method === 'transfer' ? 'selected' : ''}>Transfer</option>
						<option value="ewallet" ${method === 'ewallet' ? 'selected' : ''}>E-Wallet</option>
						<option value="qris" ${method === 'qris' ? 'selected' : ''}>QRIS</option>
						<option value="piutang" ${method === 'piutang' ? 'selected' : ''}>Piutang</option>
					</select>
				</div>
				<div class="col-xs-4">
					<input type="number" step="0.01" min="0" class="form-control input-sm payment-amount" name="payments[${index}][amount]" value="${amount}" form="pos-form">
				</div>
				<div class="col-xs-3">
					<input type="text" class="form-control input-sm" name="payments[${index}][reference_no]" value="${reference}" placeholder="Ref" form="pos-form">
				</div>
			`;
			paymentRows.appendChild(row);
			row.querySelectorAll('input, select').forEach(input => {
				input.addEventListener('input', updateTotals);
				input.addEventListener('change', updateTotals);
			});
		}
	</script>
@endsection
