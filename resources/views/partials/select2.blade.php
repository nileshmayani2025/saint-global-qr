{{-- Select2 searchable dropdowns, themed to match the luxury design. --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
    .select2-container{ width:100% !important; }
    .select2-container--default .select2-selection--single{
        height:auto; min-height:42px; padding:6px 12px; display:flex; align-items:center;
        background:var(--panel-2); border:1px solid var(--border); border-radius:.85rem;
        transition:border-color .2s, box-shadow .2s;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered{ color:var(--text); line-height:1.6; padding:0; }
    .select2-container--default .select2-selection--single .select2-selection__placeholder{ color:var(--muted); }
    .select2-container--default .select2-selection--single .select2-selection__arrow{ height:100%; top:0; right:10px; }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single{
        border-color:var(--teal); box-shadow:0 0 0 4px var(--ring);
    }
    .select2-dropdown{
        background:#ffffff; border:1px solid var(--border); border-radius:.9rem; overflow:hidden;
        box-shadow:var(--shadow); margin-top:6px;
    }
    .dark .select2-dropdown{ background:#0e1a2b; border-color:rgba(255,255,255,.10); }
    .select2-container--default .select2-results__option{ color:var(--text); padding:8px 12px; }
    .select2-container--default .select2-results__option--highlighted[aria-selected]{
        background:linear-gradient(135deg,#2ca0d4,#1b6d97); color:#fff;
    }
    .select2-container--default .select2-results__option[aria-selected=true]{ background:rgba(44,160,212,.14); color:var(--text); }
    .select2-search--dropdown{ padding:8px; }
    .select2-search--dropdown .select2-search__field{
        background:var(--panel-2); border:1px solid var(--border); border-radius:.6rem; color:var(--text); padding:7px 10px; outline:none;
    }
    .select2-search--dropdown .select2-search__field:focus{ border-color:var(--teal); }
    .select2-results__message{ color:var(--muted); }
</style>
<script>
    (function () {
        function initSelect2() {
            if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.select2) return;
            var $ = window.jQuery;
            // Apply to EVERY dropdown in the system (add class .no-s2 to opt an element out).
            $('select').not('.no-s2').each(function () {
                var $el = $(this);
                if ($el.data('select2')) return;
                $el.select2({
                    width: '100%',
                    placeholder: $el.find('option[value=""]').first().text() || 'Select…',
                    allowClear: false,
                    minimumResultsForSearch: 0, // always show the search box so every dropdown is clearly Select2
                });
                // Keep Alpine (x-model / x-show) in sync: Select2 fires jQuery events that
                // native addEventListener handlers miss, so re-dispatch a native change.
                $el.on('select2:select select2:unselect', function () {
                    this.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
        }
        if (document.readyState !== 'loading') initSelect2();
        else document.addEventListener('DOMContentLoaded', initSelect2);
    })();
</script>
