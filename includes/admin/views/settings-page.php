<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$sp_wsv_tabs = $tabs;
$sp_wsv_active_tab = $active_tab;
?>
<div class="wrap sp-wsv-wrap">
    <div class="sp-wsv-settings-container">
        <h1><?php echo esc_html__( 'Configuración de SuperPlus para Woocommerce', 'superplus-for-woocommerce' ); ?></h1>

        <nav class="nav-tab-wrapper woocommerce-nav-tab-wrapper">
            <?php foreach ( $sp_wsv_tabs as $sp_wsv_tab_id => $sp_wsv_tab ) : ?>
                <?php
                $sp_wsv_url = add_query_arg(
                    array(
                        'page' => 'sp-wsv-settings',
                        'tab'  => $sp_wsv_tab_id,
                    ),
                    admin_url( 'admin.php' )
                );

                $sp_wsv_classes = 'nav-tab';
                if ( $sp_wsv_tab_id === $sp_wsv_active_tab ) {
                    $sp_wsv_classes .= ' nav-tab-active';
                }
                ?>
                <a href="<?php echo esc_url( $sp_wsv_url ); ?>" class="<?php echo esc_attr( $sp_wsv_classes ); ?>">
                    <?php echo esc_html( $sp_wsv_tab['title'] ); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sp-wsv-tab-panel">
            <?php call_user_func( $sp_wsv_tabs[ $sp_wsv_active_tab ]['callback'] ); ?>
        </div>
    </div>
</div>
