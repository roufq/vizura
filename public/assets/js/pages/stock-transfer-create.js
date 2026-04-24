(function () {
    const tableBody = document.getElementById('transfer-items');
    const addButton = document.getElementById('add-transfer-row');
    const template = document.getElementById('transfer-item-template');

    if (!tableBody || !addButton || !template) return;

    let nextIndex = tableBody.querySelectorAll('tr').length;

    function refreshRemoveButtons() {
        const rows = tableBody.querySelectorAll('tr');
        rows.forEach(row => {
            const btn = row.querySelector('.js-remove-row');
            if (btn) btn.style.display = rows.length <= 1 ? 'none' : 'block';
        });
    }

    addButton.addEventListener('click', () => {
        const html = template.innerHTML.replace(/__INDEX__/g, nextIndex++);
        const wrapper = document.createElement('tbody');
        wrapper.innerHTML = html.trim();
        const newRow = wrapper.firstElementChild;
        tableBody.appendChild(newRow);
        
        // Initialize Select2 for the new row if using any specialized library
        if (window.jQuery && typeof jQuery.fn.select2 === 'function') {
            jQuery(newRow).find('.js__select1').select2({ width: '100%', dropdownAutoWidth: true });
        }
        
        refreshRemoveButtons();
    });

    tableBody.addEventListener('click', e => {
        const btn = e.target.closest('.js-remove-row');
        if (!btn) return;
        const rows = tableBody.querySelectorAll('tr');
        if (rows.length > 1) {
            btn.closest('tr').remove();
            refreshRemoveButtons();
        }
    });

    // Initialize existing select2 elements
    $(document).ready(function() {
        $('.js__select2').select2({ width: '100%', dropdownAutoWidth: true });
    });

    refreshRemoveButtons();
})();
