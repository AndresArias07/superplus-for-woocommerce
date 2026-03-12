<?php
if (! defined('ABSPATH')) {
    exit;
}

$sp_wsv_tabs = $tabs;
$sp_wsv_active_tab = $active_tab;
$dismissed = (int) get_user_meta(get_current_user_id(), 'sp_wsv_comunidad_dismissed', true);
$settings_updated = isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true';
?>
<div class="wrap sp-wsv-wrap">
    <div class="sp-wsv-settings-container">
        <h1><?php echo esc_html__('Configuración de SuperPlus para Woocommerce', 'superplus-for-woocommerce'); ?></h1>
        <?php if ($settings_updated) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html__('Cambios actualizados correctamente.', 'superplus-for-woocommerce'); ?></p>
            </div>
        <?php endif; ?>
        <?php if (!$dismissed) { ?>
            <div id="sp-comunidad-alert" class="alert-comunidad">
                <span>¡Únete a la comunidad Tribu WP! Escala tu negocio con las herramientas que tus clientes necesitan.</span>
                <a href="<?= esc_url('https://chat.whatsapp.com/DGiuqztlTlnAYalWnRTcQt?mode=gi_t') ?>" target="_blank" class="btn btn-whatsapp">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                        <path d="M12 0C5.373 0 0 5.373 0 12c0 2.124.558 4.127 1.532 5.864L.057 23.571a.75.75 0 0 0 .921.921l5.764-1.473A11.955 11.955 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.907 0-3.686-.523-5.205-1.431l-.364-.216-3.768.964.98-3.7-.236-.374A9.96 9.96 0 0 1 2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z" />
                    </svg>
                    Comunidad de WhatsApp
                </a>
                <a href="<?php echo esc_url('https://www.youtube.com/@themarketsp') ?>" target="_blank" class="btn btn-youtube">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                    </svg>
                    Canal de YouTube
                </a>
                <button type="button" class="spwsv-alert-dismiss" aria-label="Cerrar">
                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                        <path d="M1 1L9 9M9 1L1 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                    </svg>
                </button>

            </div>
        <?php } ?>
        <nav class="nav-tab-wrapper woocommerce-nav-tab-wrapper">
            <?php foreach ($sp_wsv_tabs as $sp_wsv_tab_id => $sp_wsv_tab) : ?>
                <?php
                $sp_wsv_url = add_query_arg(
                    array(
                        'page' => 'sp-wsv-settings',
                        'tab'  => $sp_wsv_tab_id,
                    ),
                    admin_url('admin.php')
                );

                $sp_wsv_classes = 'nav-tab';
                if ($sp_wsv_tab_id === $sp_wsv_active_tab) {
                    $sp_wsv_classes .= ' nav-tab-active';
                }
                ?>
                <a href="<?php echo esc_url($sp_wsv_url); ?>" class="<?php echo esc_attr($sp_wsv_classes); ?>">
                    <?php echo esc_html($sp_wsv_tab['title']); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sp-wsv-tab-panel">
            <?php call_user_func($sp_wsv_tabs[$sp_wsv_active_tab]['callback']); ?>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        jQuery('.spwsv-alert-dismiss').on('click', () => {
            jQuery('#sp-comunidad-alert').slideUp()
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'sp_wsv_dismiss_comunidad_alert',
                    nonce: '<?php echo esc_js(wp_create_nonce("sp_wsv_dismiss_alert_nonce")); ?>'
                }
            })
        })
    })
</script>