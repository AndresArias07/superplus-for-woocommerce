<?php
/**
 * Plantilla de página (checkout) con cabecera y footer simplificados (comunes).
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$sp_wsv_distribution_class = SP_WSV_Module_Checkout_Steps::get_distribution_class();
$sp_wsv_theme_class = SP_WSV_Module_Checkout_Steps::get_theme_class();

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'sp-wsv-simple sp-wsv-page-checkout ' . $sp_wsv_distribution_class . ' ' . $sp_wsv_theme_class ); ?>>
<?php wp_body_open(); ?>

<?php include SP_WSV_PATH . 'includes/modules/checkout-steps/templates/partials/simple-header.php'; ?>

<main class="sp-wsv-simple-main" role="main">
    <div class="sp-wsv-simple-container">
        <?php
        echo do_shortcode( '[sp_wsv_checkout]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        ?>
    </div>
</main>

<?php include SP_WSV_PATH . 'includes/modules/checkout-steps/templates/partials/simple-footer.php'; ?>

<?php wp_footer(); ?>
</body>
</html>
