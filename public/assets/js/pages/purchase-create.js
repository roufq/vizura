(function () {
    const initPurchase = () => {
        const tableBody = document.getElementById('purchase-items');
        const addButton = document.getElementById('add-item-row');
        const template = document.getElementById('purchase-item-template');
        const subtotalDisp = document.getElementById('purchase-subtotal');
        const totalDisp = document.getElementById('purchase-total');
        const discInput = document.getElementById('discount_amount');
        const taxInput = document.getElementById('tax_amount');

        if (!tableBody || !addButton || !template) return;

        let nextIndex = tableBody.querySelectorAll('tr').length;
        const fmt = v => 'Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(v);

        function update() {
            let st = 0;
            tableBody.querySelectorAll('tr').forEach(r => {
                const q = parseFloat(r.querySelector('input[name*="[quantity]"]').value || 0);
                const c = parseFloat(r.querySelector('input[name*="[unit_cost]"]').value || 0);
                st += q * c;
            });
            const d = parseFloat(discInput.value || 0);
            const t = parseFloat(taxInput.value || 0);
            const tot = Math.max(0, st - d + t);
            if (subtotalDisp) subtotalDisp.textContent = fmt(st);
            if (totalDisp) totalDisp.textContent = fmt(tot);
        }

        addButton.addEventListener('click', () => {
            const html = template.innerHTML.replace(/__INDEX__/g, nextIndex++);
            const div = document.createElement('tbody');
            div.innerHTML = html.trim();
            tableBody.appendChild(div.firstChild);
            
            if (window.jQuery && typeof jQuery.fn.select2 === 'function') {
                const $newRow = $(tableBody.lastElementChild);
                $newRow.find('.js__select1, .select2-basic').select2({ 
                    width: '100%', 
                    dropdownAutoWidth: true,
                    dropdownParent: $newRow
                });
            }
            update();
        });

        tableBody.addEventListener('input', e => { if (e.target.tagName === 'INPUT') update(); });
        tableBody.addEventListener('click', e => {
            const btn = e.target.closest('.js-remove-row');
            if (btn && tableBody.querySelectorAll('tr').length > 1) { 
                btn.closest('tr').remove(); 
                update(); 
            }
        });

        if (discInput) discInput.addEventListener('input', update);
        if (taxInput) taxInput.addEventListener('input', update);
        
        if (window.jQuery && typeof jQuery.fn.select2 === 'function') {
            $('.js__select2, .select2-basic').each(function() {
                $(this).select2({ 
                    width: '100%', 
                    dropdownAutoWidth: true,
                    dropdownParent: $(this).parent()
                });
            });
        }
        update();
    };

    $(document).ready(initPurchase);
    document.addEventListener('turbo:load', initPurchase);
})();
