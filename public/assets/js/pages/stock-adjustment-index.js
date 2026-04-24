(function($) {
    const initIdx = () => {
        // Bulk Approve Handler
        $('.js-bulk-approve').off('click').on('click', function() {
            const confirmMsg = $(this).data('confirm') || 'Approve all pending data?';
            const formId = $(this).data('form');
            if (confirm(confirmMsg)) {
                $(`#${formId}`).submit();
            }
        });
    };

    $(document).ready(initIdx);
    document.addEventListener('turbo:load', initIdx);
})(window.jQuery || $);
