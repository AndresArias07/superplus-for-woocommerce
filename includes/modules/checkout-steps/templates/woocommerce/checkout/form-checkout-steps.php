<?php

/**
 * Checkout por pasos (SP WSV)
 *
 * Este template reemplaza: checkout/form-checkout.php
 */
if (! defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_before_checkout_form', $checkout); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce public hook.

if (! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in()) {
    echo '<p class="sp-wsv-checkout-login-required-msg">' . esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('Debes iniciar sesión para finalizar compra.', 'superplus-for-woocommerce'))) . '</p>'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce public hook.
    return;
}

if (function_exists('WC') && WC()->cart && WC()->cart->is_empty()) {
    wc_print_notice(__('Tu carrito está vacío.', 'superplus-for-woocommerce'), 'notice');
    return;
}

// Render barra de precio para la version PRO
if (defined('SP_WSV_PRO_VERSION') && class_exists('SP_WSV_Checkout_Steps_Pro')) {
    $sp_wsv_pro_instance = new SP_WSV_Checkout_Steps_Pro();
    $sp_wsv_pro_instance = new SP_WSV_Checkout_Steps_Pro();
    if (method_exists($sp_wsv_pro_instance, 'render_checkout_price_bar_on_checkout')) {
        $sp_wsv_pro_instance->render_checkout_price_bar_on_checkout();
    }
}

$sp_wsv_cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/');
$sp_wsv_has_cart_page = SP_WSV_Module_Checkout_Steps::has_cart_page();
$sp_wsv_options = SP_WSV_Module_Checkout_Steps::get_options_static();
$sp_wsv_distribution = isset($sp_wsv_options['distribution']) ? (string) $sp_wsv_options['distribution'] : '';
$sp_wsv_layout = isset($sp_wsv_options['layout']) ? sanitize_key((string) $sp_wsv_options['layout']) : '';
$sp_wsv_layout = in_array($sp_wsv_layout, array('sidebar-left', 'sidebar-right'), true) ? $sp_wsv_layout : '';
$sp_wsv_layout_class = $sp_wsv_layout !== '' ? ' sp-wsv-layout-' . $sp_wsv_layout : '';
?>

<form name="checkout"
    method="post"
    class="checkout woocommerce-checkout sp-wsv-checkout-steps-wrapper"
    action="<?php echo esc_url(wc_get_checkout_url()); ?>"
    enctype="multipart/form-data"
    data-step="2">

    <?php if ($sp_wsv_distribution !== 'woocommerce-steps') : ?>
        <nav class="sp-wsv-steps-nav" aria-label="<?php echo esc_attr__('Pasos', 'superplus-for-woocommerce'); ?>">
            <ol class="sp-wsv-steps-list">
                <?php if ($sp_wsv_has_cart_page) : ?>
                    <li class="sp-wsv-step-indicator is-link" data-step="1">
                        <a href="<?php echo esc_url($sp_wsv_cart_url); ?>">
                            <span class="sp-wsv-step-num">1</span>
                            <span class="sp-wsv-step-label"><?php echo esc_html__('Carrito', 'superplus-for-woocommerce'); ?></span>
                        </a>
                    </li>
                <?php endif; ?>

                <li class="sp-wsv-step-indicator is-active is-anchor" data-step="2">
                    <a class="sp-wsv-step-anchor" href="#sp-wsv-step-2">
                        <span class="sp-wsv-step-num"><?php echo esc_html($sp_wsv_has_cart_page ? '2' : '1'); ?></span>
                        <span class="sp-wsv-step-label"><?php echo esc_html__('Datos', 'superplus-for-woocommerce'); ?></span>
                    </a>
                </li>

                <li class="sp-wsv-step-indicator is-anchor" data-step="3">
                    <a class="sp-wsv-step-anchor" href="#sp-wsv-step-3">
                        <span class="sp-wsv-step-num"><?php echo esc_html($sp_wsv_has_cart_page ? '3' : '2'); ?></span>
                        <span class="sp-wsv-step-label"><?php echo esc_html__('Envío y pago', 'superplus-for-woocommerce'); ?></span>
                    </a>
                </li>

                <li class="sp-wsv-step-indicator is-anchor" data-step="4">
                    <a class="sp-wsv-step-anchor" href="#sp-wsv-step-4">
                        <span class="sp-wsv-step-num"><?php echo esc_html($sp_wsv_has_cart_page ? '4' : '3'); ?></span>
                        <span class="sp-wsv-step-label"><?php echo esc_html__('Resumen', 'superplus-for-woocommerce'); ?></span>
                    </a>
                </li>
            </ol>
        </nav>
    <?php endif; ?>

    <div class="sp-wsv-checkout-layout<?php echo esc_attr($sp_wsv_layout_class); ?>">

        <div class="sp-wsv-checkout-main">

            <section id="sp-wsv-step-2" class="sp-wsv-step-panel is-active" data-step-panel="2">
                <!-- <h2 class="sp-wsv-step-title"><?php echo esc_html__('Datos de facturación y envío', 'superplus-for-woocommerce'); ?></h2> -->

                <?php if ($checkout->get_checkout_fields()) : ?>

                    <?php do_action('woocommerce_checkout_before_customer_details'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce public hook. 
                    ?>

                    <div id="customer_details">
                        <div class="col-1">
                            <?php do_action('woocommerce_checkout_billing'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce public hook. 
                            ?>
                        </div>

                        <div class="col-2">
                            <?php do_action('woocommerce_checkout_shipping'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce public hook. 
                            ?>
                        </div>
                    </div>

                    <?php do_action('woocommerce_checkout_after_customer_details'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce public hook. 
                    ?>

                <?php endif; ?>
            </section>

            <section class="sp-wsv-step-panel" data-step-panel="3-4">
                <span id="sp-wsv-step-3" class="sp-wsv-anchor"></span>
                <h2 class="sp-wsv-step-title sp-title-step-3"><?php echo esc_html__('Método de envío y pago', 'superplus-for-woocommerce'); ?></h2>
                <span id="sp-wsv-step-4" class="sp-wsv-anchor"></span>
                <h2 class="sp-wsv-step-title sp-title-step-4"><?php echo esc_html__('Resumen del pedido', 'superplus-for-woocommerce'); ?></h2>

                <?php
                $sp_wsv_items_count = function_exists('WC') && WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0;
                ?>
                <div class="sp-wsv-step4-summary" data-spwsv-step4-summary="1">
                    <div class="sp-wsv-step4-count">
                        <?php
                        echo esc_html(
                            sprintf(
                                /* translators: %d: número de productos en el carrito */
                                _n('%d artículo', '%d artículos', $sp_wsv_items_count, 'superplus-for-woocommerce'),
                                $sp_wsv_items_count
                            )
                        );
                        ?>
                    </div>

                    <div class="sp-wsv-step4-items">
                        <?php if (function_exists('WC') && WC()->cart) : ?>
                            <?php foreach (WC()->cart->get_cart() as $sp_wsv_cart_item) : ?>
                                <?php
                                $sp_wsv_product = isset($sp_wsv_cart_item['data']) ? $sp_wsv_cart_item['data'] : null;
                                if (! $sp_wsv_product || ! is_object($sp_wsv_product) || ! method_exists($sp_wsv_product, 'exists') || ! $sp_wsv_product->exists()) {
                                    continue;
                                }
                                $sp_wsv_qty = isset($sp_wsv_cart_item['quantity']) ? (int) $sp_wsv_cart_item['quantity'] : 1;
                                $sp_wsv_thumb = method_exists($sp_wsv_product, 'get_image') ? $sp_wsv_product->get_image('thumbnail', array('class' => 'sp-wsv-step4-thumb-img')) : '';
                                $sp_wsv_line_subtotal = WC()->cart->get_product_subtotal($sp_wsv_product, $sp_wsv_qty);
                                ?>
                                <div class="sp-wsv-step4-item">
                                    <div class="sp-wsv-step4-thumb">
                                        <?php if ($sp_wsv_thumb) : ?>
                                            <?php echo $sp_wsv_thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                                            ?>
                                        <?php endif; ?>
                                        <span class="sp-wsv-step4-qty"><?php echo (int) $sp_wsv_qty; ?></span>
                                    </div>
                                    <div class="sp-wsv-step4-item-body">
                                        <div class="sp-wsv-step4-item-title"><?php echo esc_html($sp_wsv_product->get_name()); ?></div>
                                        <div class="sp-wsv-step4-item-meta">
                                            <?php if (method_exists($sp_wsv_product, 'get_sku') && $sp_wsv_product->get_sku()) : ?>
                                                <span class="sp-wsv-step4-item-sku"><?php echo esc_html('SKU: ' . $sp_wsv_product->get_sku()); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="sp-wsv-step4-item-subtotal">
                                            <?php echo wp_kses_post($sp_wsv_line_subtotal); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="sp-wsv-step4-cards">
                        <div class="sp-wsv-step4-card sp-wsv-step4-card-address" data-spwsv-step4-address="1">
                            <div class="sp-wsv-step4-card-head">
                                <div class="sp-wsv-step4-card-title"><?php echo esc_html__('Dirección', 'superplus-for-woocommerce'); ?></div>
                                <button type="button" class="button sp-wsv-step4-card-action sp-wsv-jump-step" data-step="2">
                                    <?php echo esc_html__('Editar', 'superplus-for-woocommerce'); ?>
                                </button>
                            </div>
                            <div class="sp-wsv-step4-card-body">
                                <div class="sp-wsv-step4-card-value" data-spwsv-step4-address-value="1"></div>
                            </div>
                        </div>

                        <div class="sp-wsv-step4-card sp-wsv-step4-card-payment" data-spwsv-step4-payment="1">
                            <div class="sp-wsv-step4-card-head">
                                <div class="sp-wsv-step4-card-title"><?php echo esc_html__('Forma de pago', 'superplus-for-woocommerce'); ?></div>
                                <button type="button" class="button sp-wsv-step4-card-action sp-wsv-jump-step" data-step="3">
                                    <?php echo esc_html__('Modificar', 'superplus-for-woocommerce'); ?>
                                </button>
                            </div>
                            <div class="sp-wsv-step4-card-body">
                                <div class="sp-wsv-step4-card-value" data-spwsv-step4-payment-value="1"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php do_action('woocommerce_checkout_before_order_review'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce public hook. 
                ?>

                <div id="order_review" class="woocommerce-checkout-review-order">
                    <?php do_action('woocommerce_checkout_order_review'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce public hook. 
                    ?>
                </div>

                <?php do_action('woocommerce_checkout_after_order_review'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce public hook. 
                ?>
            </section>

        </div>

        <aside class="sp-wsv-checkout-sidebar" aria-label="<?php echo esc_attr__('Resumen del pedido', 'superplus-for-woocommerce'); ?>">
            <div class="sp-wsv-sidebar-box">
                <div class="sp-wsv-sidebar-title">
                    <?php echo $sp_wsv_distribution === 'woocommerce-steps' ? esc_html__('Resumen', 'superplus-for-woocommerce') : esc_html__('Tu pedido', 'superplus-for-woocommerce'); ?>
                </div>
                <?php SP_WSV_Module_Checkout_Steps::render_sidebar_summary(true); ?>
            </div>


            <div class="sp-wsv-sidebar-actions">
                <div class="sp-wsv-sidebar-actions-group is-active" data-step-actions="2">
                    <?php if ($sp_wsv_has_cart_page) : ?>
                        <a class="button sp-wsv-btn-secondary" href="<?php echo esc_url($sp_wsv_cart_url); ?>">
                            <?php echo esc_html__('Volver al carrito', 'superplus-for-woocommerce'); ?>
                        </a>
                    <?php endif; ?>
                    <button type="button" class="button sp-wsv-btn-primary" data-sp-wsv-next>
                        <?php echo esc_html__('Continuar', 'superplus-for-woocommerce'); ?>
                    </button>
                </div>

                <div class="sp-wsv-sidebar-actions-group" data-step-actions="3">
                    <button type="button" class="button sp-wsv-btn-secondary" data-sp-wsv-prev>
                        <?php echo esc_html__('Volver', 'superplus-for-woocommerce'); ?>
                    </button>
                    <button type="button" class="button sp-wsv-btn-primary" data-sp-wsv-next>
                        <?php echo esc_html__('Revisar pedido', 'superplus-for-woocommerce'); ?>
                    </button>
                </div>

                <div class="sp-wsv-sidebar-actions-group" data-step-actions="4">
                    <div class="sp-wsv-sidebar-actions-row">
                        <button type="button" class="button sp-wsv-btn-secondary" data-sp-wsv-prev>
                            <?php echo esc_html__('Volver', 'superplus-for-woocommerce'); ?>
                        </button>
                        <button type="button" class="button sp-wsv-btn-primary" data-sp-wsv-place-order>
                            <?php echo esc_html__('Realizar pedido', 'superplus-for-woocommerce'); ?>
                        </button>
                    </div>
                    <div class="sp-wsv-sidebar-placeorder" data-spwsv-sidebar-placeorder="1"></div>
                </div>
            </div>
        </aside>

    </div>

</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce public hook. 
?>