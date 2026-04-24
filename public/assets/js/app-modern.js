(function($) {
    const initApp = () => {
        /**
         * Initialize Select2 for all select elements
         * except those with .no-select2 class
         */
        const initSelect2 = () => {
            if (typeof $.fn.select2 === 'undefined') return;
            
            $('select:not(.no-select2)').each(function() {
                const $el = $(this);
                if (!$el.hasClass('select2-hidden-accessible')) {
                    $el.select2({
                        width: '100%',
                        placeholder: $el.attr('placeholder') || (window.VizuraConfig ? window.VizuraConfig.translations.choose : 'Choose...'),
                        allowClear: true,
                        dropdownParent: $el.parent() // Help with Turbo/Select2 collision
                    });
                }
            });
        };

        // Initial call
        initSelect2();

        // Re-init on dynamic changes (Alpine/Livewire)
        document.addEventListener('alpine:initialized', initSelect2);
        $(document).on('select2-reinit', initSelect2);

        /**
         * Sidebar Scroll Persistence
         * Saves and restores the scroll position of the sidebar
         */
        const sidebarScroll = document.getElementById('sidebar-scroll');
        if (sidebarScroll) {
            // Restore position
            const savedPosition = localStorage.getItem('sidebar-scroll-position');
            if (savedPosition) {
                sidebarScroll.scrollTop = parseInt(savedPosition, 10);
            }

            // Save position on scroll (more reliable for Turbo)
            sidebarScroll.addEventListener('scroll', () => {
                localStorage.setItem('sidebar-scroll-position', sidebarScroll.scrollTop);
            }, { passive: true });
        }
    };

    $(document).ready(initApp);
    document.addEventListener('turbo:load', initApp);

    // Page Transition Feedback
    document.addEventListener('turbo:visit', () => {
        document.documentElement.classList.add('turbo-loading');
    });
    document.addEventListener('turbo:render', () => {
        document.documentElement.classList.remove('turbo-loading');
    });
})(window.jQuery || $);
