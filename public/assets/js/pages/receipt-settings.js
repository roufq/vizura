document.addEventListener('DOMContentLoaded', function() {
    const headerInput = document.getElementById('receipt_header');
    const footerInput = document.getElementById('receipt_footer');
    const taglineInput = document.getElementById('receipt_tagline');

    const headerPreview = document.getElementById('preview-header');
    const footerPreview = document.getElementById('preview-footer');
    const taglinePreview = document.getElementById('preview-tagline');

    if (!headerInput || !footerInput || !taglineInput || !headerPreview || !footerPreview || !taglinePreview) return;

    function updatePreview() {
        // Header
        const headerVal = headerInput.value.trim();
        if (headerVal) {
            headerPreview.textContent = headerVal;
            headerPreview.classList.remove('hidden');
            headerPreview.parentElement.classList.add('space-y-4'); // Add spacing when header is visible
        } else {
            headerPreview.classList.add('hidden');
        }

        // Footer
        footerPreview.textContent = footerInput.value.trim() ? footerInput.value : 'Thank You';

        // Tagline
        taglinePreview.textContent = taglineInput.value.trim() ? taglineInput.value : 'Please Come Again';
    }

    // Listen for input and change events
    ['input', 'change', 'keyup'].forEach(evt => {
        headerInput.addEventListener(evt, updatePreview);
        footerInput.addEventListener(evt, updatePreview);
        taglineInput.addEventListener(evt, updatePreview);
    });

    // Initial call
    updatePreview();
});
