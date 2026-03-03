<?php
/**
 * Partial: Header simple (logo)
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<header class="sp-wsv-simple-header" role="banner">
    <div class="sp-wsv-simple-header-inner">
        <div class="sp-wsv-simple-logo" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
            <?php SP_WSV_Module_Checkout_Steps::render_logo(); ?>
        </div>
        <?php do_action( 'sp_wsv_simple_header_after_logo' ); ?>
    </div>
</header>
