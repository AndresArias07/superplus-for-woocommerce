<?php

/**
 * Checkout estilo Shopify (SP WSV) - seguido, sin pasos
 */
if (! defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_before_checkout_form', $checkout); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce public hook.

// Si la tienda requiere registro y el usuario no está logueado, respetamos el comportamiento nativo.
if (! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in()) {
    echo '<p class="sp-wsv-checkout-login-required-msg">' . esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('Debes iniciar sesión para finalizar compra.', 'superplus-for-woocommerce'))) . '</p>'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce public hook.
    return;
}

if (function_exists('WC') && WC()->cart && WC()->cart->is_empty()) {
    wc_print_notice(__('Tu carrito está vacío.', 'superplus-for-woocommerce'), 'notice');
    return;
}
?>

<form name="checkout"
    method="post"
    class="checkout woocommerce-checkout sp-wsv-checkout-steps-wrapper"
    action="<?php echo esc_url(wc_get_checkout_url()); ?>"
    enctype="multipart/form-data">

    <div class="sp-wsv-checkout-layout">

        <div class="sp-wsv-checkout-main">
            <?php
            // Render price bar for PRO version
            if (defined('SP_WSV_PRO_VERSION') && class_exists('SP_WSV_Checkout_Steps_Pro')) {
                $pro_instance = new SP_WSV_Checkout_Steps_Pro();
                if (method_exists($pro_instance, 'render_checkout_price_bar_on_checkout')) {
                    $pro_instance->render_checkout_price_bar_on_checkout();
                }
            }
            ?>

            <section class="sp-wsv-step-panel is-active">
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

            <section class="sp-wsv-step-panel is-active">
                <h2 class="sp-wsv-step-title"><?php echo esc_html__('Envío, pago y resumen', 'superplus-for-woocommerce'); ?></h2>

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
            <div class="sp-wsv-checkout-sidebar-inner">
                <div class="sp-wsv-sidebar-title"><?php echo esc_html__('Tu pedido', 'superplus-for-woocommerce'); ?></div>
                <?php SP_WSV_Module_Checkout_Steps::render_sidebar_summary(true); ?>
            </div>
    </div>

    </div>


</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce public hook. 
?>