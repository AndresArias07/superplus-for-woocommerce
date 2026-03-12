<?php

/**
 * Plantilla de página (carrito) con cabecera y footer simplificados (comunes).
 */
if (! defined('ABSPATH')) {
    exit;
}

$sp_wsv_distribution_class = SP_WSV_Module_Checkout_Steps::get_distribution_class();
$sp_wsv_theme_class = SP_WSV_Module_Checkout_Steps::get_theme_class();
$sp_wsv_opts = SP_WSV_Module_Checkout_Steps::get_options_static();
$sp_wsv_distribution = isset($sp_wsv_opts['distribution']) ? (string) $sp_wsv_opts['distribution'] : 'shopify';
$sp_wsv_cart_empty = false;
if (function_exists('WC') && WC()->cart) {
    if (method_exists(WC()->cart, 'get_cart')) {
        WC()->cart->get_cart();
    }
    $sp_wsv_cart_empty = WC()->cart->is_empty();
}

$sp_wsv_extra_body_class = $sp_wsv_cart_empty ? ' sp-wsv-cart-empty' : '';

?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php wp_head(); ?>
</head>

<body <?php body_class('sp-wsv-simple sp-wsv-page-cart' . $sp_wsv_extra_body_class . ' ' . $sp_wsv_distribution_class . ' ' . $sp_wsv_theme_class); ?>>
    <?php wp_body_open(); ?>

    <?php include SP_WSV_PATH . 'includes/modules/checkout-steps/templates/partials/simple-header.php'; ?>

    <main class="sp-wsv-simple-main" role="main">
        <div class="sp-wsv-simple-container">

            <?php
            $sp_wsv_cart_url     = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/');
            $sp_wsv_checkout_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/');
            ?>

            <?php if ($sp_wsv_distribution === 'minimal-steps' && ! $sp_wsv_cart_empty) : ?>
                <nav class="sp-wsv-steps-nav" aria-label="<?php echo esc_attr__('Pasos del checkout', 'superplus-for-woocommerce'); ?>">
                    <ol class="sp-wsv-steps-list">
                        <li class="sp-wsv-step-indicator is-active is-link" data-step="1">
                            <a href="<?php echo esc_url($sp_wsv_cart_url); ?>">
                                <span class="sp-wsv-step-num">1</span>
                                <span class="sp-wsv-step-label"><?php echo esc_html__('Carrito', 'superplus-for-woocommerce'); ?></span>
                            </a>
                        </li>
                        <li class="sp-wsv-step-indicator is-link" data-step="2">
                            <a href="<?php echo esc_url($sp_wsv_checkout_url); ?>">
                                <span class="sp-wsv-step-num">2</span>
                                <span class="sp-wsv-step-label"><?php echo esc_html__('Datos', 'superplus-for-woocommerce'); ?></span>
                            </a>
                        </li>
                        <li class="sp-wsv-step-indicator is-link" data-step="3">
                            <a href="<?php echo esc_url($sp_wsv_checkout_url); ?>">
                                <span class="sp-wsv-step-num">3</span>
                                <span class="sp-wsv-step-label"><?php echo esc_html__('Envío y pago', 'superplus-for-woocommerce'); ?></span>
                            </a>
                        </li>
                        <li class="sp-wsv-step-indicator is-link" data-step="4">
                            <a href="<?php echo esc_url($sp_wsv_checkout_url); ?>">
                                <span class="sp-wsv-step-num">4</span>
                                <span class="sp-wsv-step-label"><?php echo esc_html__('Resumen', 'superplus-for-woocommerce'); ?></span>
                            </a>
                        </li>
                    </ol>
                </nav>
            <?php endif; ?>

            <div class="sp-wsv-cart-notices">
                <div class="sp-wsv-cart-notices-inner">
                    <?php
                    // Render barra de precio para la version PRO - ubicación estratégica antes del contenido del carrito
                    if (defined('SP_WSV_PRO_VERSION') && class_exists('SP_WSV_Checkout_Steps_Pro')) {
                        $sp_wsv_pro_instance = new SP_WSV_Checkout_Steps_Pro();

                        if (method_exists($sp_wsv_pro_instance, 'render_checkout_price_bar_on_cart')) {
                            echo '<div class="sp-wsv-price-bar-container">';
                            $sp_wsv_pro_instance->render_checkout_price_bar_on_cart();
                            echo '</div>';
                        }
                    }
                    ?>
                    <?php
                    if (function_exists('wc_print_notices')) {
                        wc_print_notices();
                    }
                    ?>
                </div>
            </div>

            <?php

            if (have_posts()) :
                while (have_posts()) : the_post();
                    $sp_wsv_cart_html = do_shortcode('[woocommerce_cart]');
                    if (is_string($sp_wsv_cart_html) && trim($sp_wsv_cart_html) !== '') {
                        echo $sp_wsv_cart_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    } else {
                        echo '<p>' . esc_html__('No se pudo renderizar el carrito.', 'superplus-for-woocommerce') . '</p>';
                    }
                endwhile;
            endif;
            ?>

        </div>
    </main>

    <?php include SP_WSV_PATH . 'includes/modules/checkout-steps/templates/partials/simple-footer.php'; ?>

    <?php wp_footer(); ?>
</body>

</html>