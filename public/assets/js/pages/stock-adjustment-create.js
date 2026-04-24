(function($) {
    const initStockAdj = () => {
        if (!document.getElementById('adjustment-form')) return;

        // Init Select2
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2-basic').select2({
                width: '100%',
                placeholder: window.VizuraConfig?.translations?.select_manual || 'Select Product...',
                dropdownParent: $('.select2-basic').parent()
            });
        }

        const itemsBody = document.getElementById('adj-items-body');
        const emptyState = document.getElementById('empty-state');
        const productSelector = document.getElementById('product_selector');
        const scanInput = document.getElementById('scan_input');
        const searchResults = document.getElementById('search_results');
        const resultsContainer = document.getElementById('results_container');
        const addBtn = document.getElementById('add-product-btn');

        let itemIndex = (itemsBody && itemsBody.querySelectorAll('tr:not(#empty-state)').length) || 0;
        const items = new Set();
        const productMap = new Map();

        // Initialize product map from selector
        if (productSelector) {
            Array.from(productSelector.options).forEach(opt => {
                if (opt.value) {
                    productMap.set(opt.value, {
                        id: opt.value,
                        name: opt.dataset.name,
                        sku: opt.dataset.sku,
                        barcode: opt.dataset.barcode
                    });
                }
            });
        }

        // Search Logic
        let searchTimeout;
        if (scanInput) {
            scanInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.toLowerCase().trim();
                if (query.length < 2) { searchResults.classList.add('hidden'); return; }

                searchTimeout = setTimeout(() => {
                    const matches = Array.from(productMap.values()).filter(p => 
                        p.name.toLowerCase().includes(query) || 
                        p.sku.toLowerCase().includes(query) || 
                        (p.barcode && p.barcode.toLowerCase().includes(query))
                    ).slice(0, 5);
                    renderResults(matches);
                }, 200);
            });

            function renderResults(matches) {
                resultsContainer.innerHTML = '';
                if (matches.length === 0) { searchResults.classList.add('hidden'); return; }
                matches.forEach(p => {
                    const div = document.createElement('div');
                    div.className = "flex items-center justify-between p-4 hover:bg-slate-50 cursor-pointer rounded-2xl transition-all group text-left";
                    div.innerHTML = `
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-brand/10 group-hover:text-brand transition-all">
                                <i class="fa fa-cube"></i>
                            </div>
                            <div class="text-left">
                                <p class="text-sm font-bold text-slate-700">${p.name}</p>
                                <p class="text-[10px] font-black text-slate-400 uppercase leading-none mt-1">${p.sku}</p>
                            </div>
                        </div>
                        <i class="fa fa-plus-circle text-slate-200 group-hover:text-brand text-xl transition-all"></i>
                    `;
                    div.onclick = () => { addProductToList(p.id); scanInput.value = ''; searchResults.classList.add('hidden'); scanInput.focus(); };
                    resultsContainer.appendChild(div);
                });
                searchResults.classList.remove('hidden');
            }

            scanInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const firstResult = resultsContainer.querySelector('div');
                    if (firstResult) firstResult.click();
                }
            });

            document.addEventListener('click', function(e) {
                if (scanInput && !scanInput.contains(e.target) && searchResults && !searchResults.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });
        }

        if (addBtn) {
            addBtn.addEventListener('click', function() {
                const productId = $(productSelector).val();
                if (productId) {
                    addProductToList(productId);
                    $(productSelector).val(null).trigger('change');
                }
            });
        }

        function addProductToList(productId) {
            if (!productId || items.has(productId)) return;
            const p = productMap.get(productId);
            if (!p) return;
            if (emptyState && itemsBody.contains(emptyState)) emptyState.remove();

            const row = document.createElement('tr');
            row.className = "hover:bg-slate-50/50 transition-colors group";
            row.dataset.id = productId;
            row.innerHTML = `
                <td class="px-4 py-8">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-slate-400 border border-slate-100 group-hover:bg-brand/5 group-hover:text-brand transition-all">
                            <i class="fa fa-cube text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm font-black text-slate-800 leading-tight">${p.name}</p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter mt-1">${p.sku}</p>
                            <input type="hidden" name="items[${itemIndex}][product_id]" value="${productId}">
                        </div>
                    </div>
                </td>
                <td class="px-4 py-8 text-center">
                    <div class="flex items-center justify-center p-1 bg-slate-100/50 rounded-2xl w-fit mx-auto border border-slate-100 shadow-inner">
                         <button type="button" class="type-toggle h-11 px-6 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all bg-emerald-500 text-white shadow-lg shadow-emerald-500/20" data-type="plus">IN</button>
                         <button type="button" class="type-toggle h-11 px-6 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-400" data-type="minus">OUT</button>
                    </div>
                </td>
                <td class="px-4 py-8">
                    <div class="max-w-[120px] mx-auto">
                        <input type="number" step="1" name="items[${itemIndex}][quantity_delta]" class="qty-field w-full h-11 bg-slate-50 border-none rounded-xl px-4 text-center text-sm font-black text-slate-700 focus:ring-4 focus:ring-brand/10 transition-all shadow-inner" value="1" required>
                    </div>
                </td>
                <td class="px-4 py-8 text-center">
                    <button type="button" class="remove-item w-11 h-11 rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center border-none shadow-sm">
                        <i class="fa fa-trash text-sm"></i>
                    </button>
                </td>
            `;

            itemsBody.appendChild(row);
            items.add(productId);
            itemIndex++;

            const toggles = row.querySelectorAll('.type-toggle');
            const qtyInput = row.querySelector('.qty-field');

            toggles.forEach(t => t.addEventListener('click', function() {
                toggles.forEach(b => {
                    b.classList.remove('bg-emerald-500', 'bg-rose-500', 'text-white', 'shadow-lg', 'shadow-emerald-500/20', 'shadow-rose-500/20');
                    b.classList.add('text-slate-400');
                });
                this.classList.remove('text-slate-400');
                if (this.dataset.type === 'plus') {
                    this.classList.add('bg-emerald-500', 'text-white', 'shadow-lg', 'shadow-emerald-500/20');
                    if (parseFloat(qtyInput.value) < 0) qtyInput.value = Math.abs(parseFloat(qtyInput.value));
                } else {
                    this.classList.add('bg-rose-500', 'text-white', 'shadow-lg', 'shadow-rose-500/20');
                    if (parseFloat(qtyInput.value) > 0) qtyInput.value = -Math.abs(parseFloat(qtyInput.value));
                }
            }));

            qtyInput.addEventListener('input', function() {
                const isMinus = row.querySelector('.type-toggle[data-type="minus"]').classList.contains('bg-rose-500');
                let val = parseFloat(this.value || 0);
                if (isMinus && val > 0) this.value = -val;
                if (!isMinus && val < 0) this.value = Math.abs(val);
            });

            row.querySelector('.remove-item').addEventListener('click', function() {
                row.remove();
                items.delete(productId);
                if (itemsBody.children.length === 0) itemsBody.appendChild(emptyState);
            });
        }

        $('#evidence').on('change', function() {
            if (this.files && this.files[0]) {
                const name = this.files[0].name;
                const display = name.substring(0, 20) + (name.length > 20 ? '...' : '');
                $('#file-chosen').text(display);
            }
        });
    };

    $(document).ready(initStockAdj);
    document.addEventListener('turbo:load', initStockAdj);
})(window.jQuery || $);
