(function($) {
    const initPOS = () => {
        if (!document.getElementById('pos-form')) return;

        // 1. Core State
        const cart = new Map();
        const posConfig = window.VizuraConfig?.pos || {};
        const products = posConfig.products || [];
        const productMap = new Map(products.map(p => [String(p.id), p]));
        let currentTotal = 0;

        const STORAGE_KEY = posConfig.storageKey || 'vizura_pos_cache_default';

        // 2. Persistence Logic
        function saveState() {
            try {
                const state = {
                    cart: Array.from(cart.entries()),
                    customer_id: $('#customer-id-input').val(),
                    customer_name: $('#customer-select').val(),
                    customer_phone: $('#customer-phone').val(),
                    order_type: $('#order_type_input').val(),
                    order_discount: $('#order_discount').val(),
                    tax_rate: $('#tax_rate').val(),
                    tax_inc: $('#is_tax_inclusive').prop('checked'),
                    notes: $('textarea[name="notes"]').val(),
                    payments: Array.from(document.querySelectorAll('.payment-row')).map(row => ({
                        method: row.querySelector('.payment-method').value,
                        amount: row.querySelector('.payment-amount').value,
                        reference_no: row.querySelector('input[name*="reference_no"]').value
                    }))
                };
                localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
            } catch (e) { console.warn("Failed to save POS state", e); }
        }

        // 3. Element Selectors
        const scanInput = document.getElementById('scan_input');
        const addManualButton = document.getElementById('add_manual');
        const productSelect = $('#product_select');
        const cartBody = document.getElementById('cart-body');
        const emptyState = document.getElementById('cart-empty-state');
        const stockWarning = document.getElementById('stock-warning');
        const payButton = document.getElementById('pay_button');
        const subtotalDisplay = document.getElementById('subtotal_display');
        const totalDisplay = document.getElementById('total_display');
        const paidDisplay = document.getElementById('paid_display');
        const changeDisplay = document.getElementById('change_display');
        const orderDiscountInput = document.getElementById('order_discount');
        const taxRateInput = document.getElementById('tax_rate');
        const taxRateLabel = document.getElementById('tax_rate_val');
        const taxInclusiveInput = document.getElementById('is_tax_inclusive');
        const paymentRows = document.getElementById('payment-rows');
        const searchResults = document.getElementById('search_results');
        const resultsContainer = document.getElementById('results_container');
        const paymentWarning = document.getElementById('payment-warning');

        // 3. Helper Functions
        const moneyFormat = v => 'Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(v);

        function addToCart(p, q, up, ld) {
            const k = String(p.id);
            if (cart.has(k)) {
                cart.get(k).quantity += parseFloat(q);
            } else {
                cart.set(k, { 
                    productId: p.id, 
                    name: p.name, 
                    sku: p.sku, 
                    price: p.price, 
                    stock: p.stock, 
                    block: p.block_when_out_of_stock, 
                    quantity: parseFloat(q), 
                    unitPrice: parseFloat(up), 
                    lineDiscount: parseFloat(ld),
                    image: p.image
                });
            }
        }

        function updateCartTable() {
            if (!cartBody) return;
            cartBody.innerHTML = '';
            if (emptyState) emptyState.classList.toggle('hidden', cart.size > 0);
            
            let idx = 0;
            cart.forEach(item => {
                const row = document.createElement('tr');
                const lineTotal = Math.max(0, (item.quantity * item.unitPrice) - item.lineDiscount);
                row.className = "hover:bg-slate-50/50 transition-all border-b border-slate-50 last:border-0";
                row.innerHTML = `
                    <td class="px-8 py-4">
                        <div class="flex items-center gap-3">
                            ${item.image ? `<img src="${item.image}" class="w-8 h-8 rounded-lg object-cover">` : `<div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] text-slate-400 font-black">${item.name.charAt(0)}</div>`}
                            <div>
                                <p class="text-sm font-bold text-slate-800 leading-tight">${item.name}</p>
                                <p class="text-[10px] font-black text-slate-400 mt-1 uppercase tracking-tighter">SKU: ${item.sku} | Stok: ${item.stock}</p>
                            </div>
                        </div>
                        <input type="hidden" name="items[${idx}][product_id]" value="${item.productId}" form="pos-form">
                        <input type="hidden" name="items[${idx}][quantity]" value="${item.quantity}" form="pos-form">
                        <input type="hidden" name="items[${idx}][unit_price]" value="${item.unitPrice}" form="pos-form">
                        <input type="hidden" name="items[${idx}][line_discount]" value="${item.lineDiscount}" form="pos-form">
                    </td>
                    <td class="px-6 py-4">
                        <input type="number" step="1" class="w-16 bg-slate-50 border-none rounded-xl px-2 py-2 text-center text-sm font-black text-slate-700 qty-input" data-id="${item.productId}" value="${item.quantity}">
                    </td>
                    <td class="px-6 py-4 text-right">
                        <input type="number" step="0.01" class="w-24 bg-transparent border-none text-right text-xs font-bold text-slate-400 price-input" data-id="${item.productId}" value="${item.unitPrice}">
                    </td>
                    <td class="px-6 py-4 text-right">
                        <input type="number" step="0.01" class="w-20 bg-transparent border-none text-right text-xs font-bold text-rose-400 discount-input" data-id="${item.productId}" value="${item.lineDiscount}">
                    </td>
                    <td class="px-6 py-4 text-right font-black text-sm text-slate-700 italic">${moneyFormat(lineTotal)}</td>
                    <td class="px-8 py-4 text-center">
                        <button type="button" class="text-slate-300 hover:text-rose-600 remove-item" data-id="${item.productId}"><i class="fa fa-times-circle text-lg"></i></button>
                    </td>
                `;
                cartBody.appendChild(row);
                idx++;
            });

            // Re-bind listeners
            cartBody.querySelectorAll('.qty-input, .price-input, .discount-input').forEach(i => i.addEventListener('input', e => {
                const item = cart.get(String(e.target.dataset.id));
                if (item) {
                    if (e.target.classList.contains('qty-input')) item.quantity = parseFloat(e.target.value || 0);
                    if (e.target.classList.contains('price-input')) item.unitPrice = parseFloat(e.target.value || 0);
                    if (e.target.classList.contains('discount-input')) item.lineDiscount = parseFloat(e.target.value || 0);
                    updateTotals();
                    const subtotalCell = e.target.closest('tr').querySelector('td:nth-last-child(2)');
                    const lt = Math.max(0, (item.quantity * item.unitPrice) - item.lineDiscount);
                    subtotalCell.textContent = moneyFormat(lt);
                }
            }));

            cartBody.querySelectorAll('.remove-item').forEach(b => b.addEventListener('click', e => { 
                cart.delete(String(e.currentTarget.dataset.id)); 
                updateCartTable();
            }));
            syncBrowserBadges();
            updateTotals();
            saveState();
        }

        function syncBrowserBadges() {
            // Reset all to default
            $('.qty-badge').addClass('hidden').find('span:last-child').text('0');
            $('.grid-minus-container').addClass('hidden');
            $('.product-item').removeClass('border-brand ring-2 ring-brand/10 shadow-xl shadow-brand/10').addClass('border-slate-100 shadow-sm');
            $('.btn-grid-add').removeClass('bg-brand text-white border-brand').addClass('bg-slate-50 text-slate-400 border-slate-100');
            
            cart.forEach((item, id) => {
                const badge = $(`.qty-badge[data-id="${id}"]`);
                const card = $(`.product-item[data-id="${id}"]`);
                const minusContainer = $(`.grid-minus-container[data-id="${id}"]`);
                const plusBtn = $(`.btn-grid-add[data-id="${id}"]`);
                
                if (badge.length) {
                    badge.removeClass('hidden').find('span:last-child').text(item.quantity);
                }
                if (card.length) {
                    card.removeClass('border-slate-100 shadow-sm').addClass('border-brand ring-2 ring-brand/10 shadow-xl shadow-brand/10');
                }
                if (minusContainer.length && item.quantity > 0) {
                    minusContainer.removeClass('hidden');
                }
                if (plusBtn.length) {
                    plusBtn.removeClass('bg-slate-50 text-slate-400 border-slate-100').addClass('bg-brand text-white border-brand');
                }
            });
        }

        function updateTotals() {
            let st = 0; let si = false;
            cart.forEach(i => { 
                st += Math.max(0, (i.quantity * i.unitPrice) - i.lineDiscount); 
                if (i.block && i.quantity > i.stock) si = true; 
            });

            const od = parseFloat(orderDiscountInput?.value || 0);
            const tr = parseFloat(taxRateInput?.value || 0);
            const inc = taxInclusiveInput?.checked;
            
            currentTotal = Math.max(0, st - od);
            const tx = tr > 0 ? (inc ? (currentTotal - (currentTotal / (1 + (tr/100)))) : (currentTotal * (tr/100))) : 0;
            if (!inc) currentTotal += tx;

            let paid = 0;
            document.querySelectorAll('.payment-amount').forEach(ai => {
                paid += parseFloat(ai.value || 0);
            });

            if (subtotalDisplay) subtotalDisplay.textContent = moneyFormat(st);
            if (totalDisplay) totalDisplay.textContent = moneyFormat(currentTotal);
            if (paidDisplay) paidDisplay.textContent = moneyFormat(paid);
            if (changeDisplay) changeDisplay.textContent = moneyFormat(Math.max(0, paid - currentTotal));
            if (stockWarning) stockWarning.classList.toggle('hidden', !si);
            if (paymentWarning) paymentWarning.classList.toggle('hidden', paid >= currentTotal || cart.size === 0);
            if (payButton) payButton.disabled = si || od > st || paid < currentTotal || cart.size === 0;
            saveState();
        }

        function handleScan(val) {
            const kw = (val || '').trim();
            if (!kw) return;

            // Priority 1: Search in loaded product map
            let p = Array.from(productMap.values()).find(item => item.barcode === kw || item.sku === kw);
            if (p) {
                addToCart(p, 1, p.price, 0);
                updateCartTable();
                if (scanInput) scanInput.value = '';
                return;
            }

            // Priority 2: Fetch from server
            fetch(`${posConfig.routes.search}?query=${encodeURIComponent(kw)}`)
                .then(res => res.json())
                .then(data => {
                    const found = data.find(item => item.barcode === kw || item.sku === kw);
                    if (!found) {
                        if (window.swal) swal("Oops!", posConfig.translations.productNotFound, "error");
                        else alert(posConfig.translations.productNotFound);
                        if (scanInput) { scanInput.value = ''; scanInput.focus(); }
                        return;
                    }
                    productMap.set(String(found.id), found);
                    addToCart(found, 1, found.price, 0);
                    updateCartTable();
                    if (scanInput) { scanInput.value = ''; scanInput.focus(); }
                });
        }

        // Select2 Initialization
        const initSelects = () => {
             productSelect.select2({ 
                placeholder: posConfig.translations.selectProduct, 
                width: '100%',
                containerCssClass: 'vizura-select2-container',
                dropdownParent: productSelect.parent()
            });

            $('.select2-customer').select2({
                placeholder: posConfig.translations.customerPlaceholder,
                tags: true,
                width: '100%',
                allowClear: true,
                containerCssClass: 'vizura-select2-container',
                dropdownParent: $('.select2-customer').parent(),
                createTag: function (params) {
                    var term = $.trim(params.term);
                    if (term === '') { return null; }
                    return { id: term, text: term, newTag: true };
                }
            }).on('change', function(e) {
                const selected = $(this).select2('data')[0];
                const phoneInput = document.getElementById('customer-phone');
                const idInput = document.getElementById('customer-id-input');
                
                if (selected && selected.element) {
                    const phone = $(selected.element).data('phone');
                    const id = $(selected.element).data('id');
                    if (phoneInput) phoneInput.value = phone || '';
                    if (idInput) idInput.value = id || '';
                } else if (selected && selected.newTag) {
                    if (idInput) idInput.value = '';
                } else {
                    if (idInput) idInput.value = '';
                }
            });
        };

        if (typeof $.fn.select2 !== 'undefined') initSelects();

        let searchTimeout = null;
        if (scanInput) {
            scanInput.addEventListener('input', e => {
                clearTimeout(searchTimeout);
                const val = e.target.value.trim();
                if (val.length < 2) { searchResults.classList.add('hidden'); return; }
                searchTimeout = setTimeout(() => {
                    fetch(`${posConfig.routes.search}?query=${encodeURIComponent(val)}`)
                        .then(res => res.json())
                        .then(data => {
                            if (!data.length) { searchResults.classList.add('hidden'); return; }
                            renderSearchResults(data);
                        });
                }, 300);
            });

            scanInput.addEventListener('keydown', e => { 
                if (e.key === 'Enter') { 
                    e.preventDefault(); 
                    const firstResult = searchResults.querySelector('div:first-child');
                    if (firstResult && !searchResults.classList.contains('hidden')) firstResult.click();
                    else handleScan(scanInput.value); 
                } 
            });
        }

        function renderSearchResults(data) {
            resultsContainer.innerHTML = '';
            searchResults.classList.remove('hidden');
            data.forEach(p => {
                const item = document.createElement('div');
                item.className = "flex items-center justify-between p-4 hover:bg-slate-50 cursor-pointer rounded-2xl transition-all group text-left";
                item.innerHTML = `
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-brand/10 group-hover:text-brand transition-all overflow-hidden">
                            ${p.image ? `<img src="${p.image}" class="w-full h-full object-cover">` : `<i class="fa fa-cube"></i>`}
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-bold text-slate-700">${p.name}</p>
                            <p class="text-[10px] font-black text-slate-400 uppercase leading-none mt-1">${p.sku} | Stok: ${p.stock}</p>
                        </div>
                    </div>
                    <div class="text-right">
                       <p class="text-sm font-black text-brand">${moneyFormat(p.price)}</p>
                    </div>
                `;
                item.onclick = () => {
                    productMap.set(String(p.id), p);
                    addToCart(p, 1, p.price, 0); 
                    updateCartTable();
                    searchResults.classList.add('hidden');
                    if (scanInput) { scanInput.value = ''; scanInput.focus(); }
                };
                resultsContainer.appendChild(item);
            });
        }

        $('.order-type-btn').on('click', function() {
            $('.order-type-btn').removeClass('bg-white shadow-sm text-brand border border-slate-100').addClass('text-slate-400 hover:text-slate-600');
            $(this).addClass('bg-white shadow-sm text-brand border border-slate-100').removeClass('text-slate-400 hover:text-slate-600');
            $('#order_type_input').val($(this).data('value'));
        });

        $('.tax-mode-btn').on('click', function() {
            $('.tax-mode-btn').removeClass('bg-white shadow-sm text-brand border border-slate-100').addClass('text-slate-400 hover:text-slate-600');
            $(this).addClass('bg-white shadow-sm text-brand border border-slate-100').removeClass('text-slate-400 hover:text-slate-600');
            if (taxRateInput) taxRateInput.value = $(this).data('rate');
            if (taxInclusiveInput) taxInclusiveInput.checked = $(this).data('inc') === 1;
            if (taxRateLabel) taxRateLabel.textContent = $(this).data('rate');
            updateTotals();
        });

        const browserContainer = document.getElementById('product-browser-container');
        if (document.getElementById('toggle-grid-view')) {
            document.getElementById('toggle-grid-view').onclick = () => {
                browserContainer?.classList.remove('hidden');
                syncBrowserBadges();
            };
        }
        $('.close-browser-trigger').on('click', () => browserContainer?.classList.add('hidden'));

        $('.cat-filter-btn').on('click', function() {
            $('.cat-filter-btn').removeClass('bg-brand text-white shadow-xl shadow-brand/20').addClass('text-slate-500 hover:bg-white hover:text-brand border border-transparent hover:border-slate-200 hover:shadow-sm');
            $(this).addClass('bg-brand text-white shadow-xl shadow-brand/20').removeClass('text-slate-500 hover:bg-white hover:text-brand border border-transparent hover:border-slate-200 hover:shadow-sm');
            filterGrid();
        });

        const browserSearchInput = document.getElementById('browser-search');
        if (browserSearchInput) browserSearchInput.addEventListener('input', filterGrid);

        function filterGrid() {
            const catId = $('.cat-filter-btn.bg-brand').data('id');
            const query = (browserSearchInput?.value || '').toLowerCase().trim();
            $('.product-item').each(function() {
                const itemCat = $(this).data('category');
                const itemName = String($(this).data('name') || '').toLowerCase();
                const matchesCat = (catId === 'all' || itemCat == catId);
                const matchesQuery = !query || itemName.includes(query);
                if (matchesCat && matchesQuery) $(this).removeClass('hidden').addClass('flex');
                else $(this).addClass('hidden').removeClass('flex');
            });
        }

        $(document).on('click', '.btn-grid-add', function(e) {
            e.stopPropagation();
            const p = productMap.get(String($(this).data('id')));
            if (p) { addToCart(p, 1, p.price, 0); updateCartTable(); }
        });

        $(document).on('click', '.btn-grid-reduce', function(e) {
            e.stopPropagation();
            const pid = String($(this).data('id'));
            const item = cart.get(pid);
            if (item) {
                if (item.quantity > 1) item.quantity -= 1;
                else cart.delete(pid);
                updateCartTable();
            }
        });

        $('.product-item').on('click', function(e) {
            if ($(e.target).closest('button').length) return;
            const p = productMap.get(String($(this).data('id')));
            if (p) { 
                addToCart(p, 1, p.price, 0); 
                updateCartTable();
                const $card = $(this);
                $card.addClass('scale-95 opacity-80');
                setTimeout(() => $card.removeClass('scale-95 opacity-80'), 100);
            }
        });

        if (addManualButton) {
            addManualButton.addEventListener('click', () => {
                const pid = productSelect.val();
                if (!pid) return;
                const p = productMap.get(String(pid));
                if (p) { addToCart(p, 1, p.price, 0); updateCartTable(); productSelect.val(null).trigger('change'); }
                if (scanInput) scanInput.focus();
            });
        }

        if (orderDiscountInput) orderDiscountInput.addEventListener('input', updateTotals);
        if (taxRateInput) taxRateInput.addEventListener('input', () => { if (taxRateLabel) taxRateLabel.textContent = taxRateInput.value; updateTotals(); });
        
        function addPaymentRowFields(m, a, r) {
            if (!paymentRows) return;
            const idx = paymentRows.querySelectorAll('.payment-row').length;
            const row = document.createElement('div');
            row.className = 'payment-row grid grid-cols-[85px_1fr_65px_25px] gap-2 mb-3 items-center';
            row.innerHTML = `
                <select class="payment-method h-12 bg-slate-50 border-none rounded-xl px-2 text-[9px] font-black uppercase tracking-tighter text-slate-500 focus:ring-4 focus:ring-brand/10 transition-all cursor-pointer" name="payments[${idx}][method]" form="pos-form">
                    <option value="cash" ${m === 'cash' ? 'selected' : ''}>CASH</option>
                    <option value="card" ${m === 'card' ? 'selected' : ''}>CARD</option>
                    <option value="transfer" ${m === 'transfer' ? 'selected' : ''}>TRF</option>
                    <option value="qris" ${m === 'qris' ? 'selected' : ''}>QRIS</option>
                    <option value="piutang" ${m === 'piutang' ? 'selected' : ''}>PIUTANG</option>
                </select>
                <div class="relative">
                    <input type="number" step="0.01" class="payment-amount w-full h-12 bg-slate-50 border-none rounded-xl px-4 text-sm font-black text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all placeholder:text-slate-300" name="payments[${idx}][amount]" value="${a}" placeholder="0" form="pos-form">
                </div>
                <input type="text" class="w-full h-12 bg-slate-50 border-none rounded-xl px-2 text-[9px] font-bold text-slate-400 focus:ring-4 focus:ring-brand/10 transition-all text-center" name="payments[${idx}][reference_no]" value="${r}" placeholder="REF" form="pos-form">
                <button type="button" class="remove-payment-row text-slate-300 hover:text-rose-500 transition-colors flex items-center justify-center">
                    <i class="fa fa-times-circle text-lg"></i>
                </button>
            `;
            paymentRows.appendChild(row);
            row.querySelectorAll('input, select').forEach(i => i.addEventListener('input', updateTotals));
            row.querySelector('.remove-payment-row').addEventListener('click', () => {
                if (paymentRows.querySelectorAll('.payment-row').length > 1) { row.remove(); updateTotals(); }
            });
        }

        $('#add_payment_row').on('click', () => { addPaymentRowFields('', '', ''); updateTotals(); });
        $('#set_exact_payment').on('click', () => {
            const firstRow = paymentRows?.querySelector('.payment-row');
            if (!firstRow) addPaymentRowFields('cash', currentTotal, '');
            else firstRow.querySelector('.payment-amount').value = currentTotal.toFixed(2);
            updateTotals();
        });

        // Load Cache/Initial
        const initialItems = posConfig.initialItems || [];
        const initialPayments = posConfig.initialPayments || [];
        const cachedData = localStorage.getItem(STORAGE_KEY);
        let hasLoaded = false;

        if (cachedData && initialItems.length === 0 && initialPayments.length === 0) {
            const s = JSON.parse(cachedData);
            if (s.cart) s.cart.forEach(([k, v]) => cart.set(k, v));
            setTimeout(() => {
                if (s.customer_name) $('#customer-select').val(s.customer_name).trigger('change');
                if (s.order_type) $(`.order-type-btn[data-value="${s.order_type}"]`).click();
                if (s.tax_rate) { $('#tax_rate').val(s.tax_rate).trigger('input'); if (s.tax_inc) $('#is_tax_inclusive').prop('checked', true); }
                if (s.order_discount) $('#order_discount').val(s.order_discount).trigger('input');
                if (s.notes) $('textarea[name="notes"]').val(s.notes);
                if (s.payments?.length) { paymentRows.innerHTML = ''; s.payments.forEach(p => addPaymentRowFields(p.method, p.amount, p.reference_no)); }
                updateTotals();
            }, 300);
            hasLoaded = true;
        }

        if (!hasLoaded) {
            initialItems.forEach(i => { const p = productMap.get(String(i.product_id)); if (p) addToCart(p, i.quantity, i.unit_price || p.price, i.line_discount || 0); });
            if (initialPayments.length) initialPayments.forEach(p => addPaymentRowFields(p.method, p.amount, p.reference_no));
            else addPaymentRowFields('cash', '', '');
        }

        $('#pos-form').on('submit', () => { saveState(); localStorage.removeItem(STORAGE_KEY); });
        $('.js-reset-pos').on('click', () => { if (confirm(posConfig.translations.confirmReset || 'Reset POS?')) { localStorage.removeItem(STORAGE_KEY); location.reload(); } });
        
        updateCartTable();
    };

    $(document).ready(initPOS);
    document.addEventListener('turbo:load', initPOS);
})(window.jQuery || $);
