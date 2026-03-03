(function($){
    function relocateCartNotices() {
        var host = document.querySelector('.sp-wsv-cart-notices-inner');
        if (!host) return;

        var wrappers = document.querySelectorAll('.woocommerce-notices-wrapper');
        var source = null;

        for (var i = 0; i < wrappers.length; i++) {
            var w = wrappers[i];
            if (host.contains(w)) continue;
            if (w.querySelector('.woocommerce-error, .woocommerce-message, .woocommerce-info')) {
                source = w;
                break;
            }
        }

        if (!source) return;

        host.innerHTML = '';
        host.appendChild(source);
    }

    $(function(){
        relocateCartNotices();
        $(document.body).on('updated_wc_div updated_cart_totals wc_fragments_refreshed', relocateCartNotices);
    });
})(jQuery);
