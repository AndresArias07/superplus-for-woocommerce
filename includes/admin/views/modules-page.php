<?php
if (! defined('ABSPATH')) {
    exit;
}

$dismissed = (int) get_user_meta(get_current_user_id(), 'sp_wsv_comunidad_dismissed', true);
?>
<div class="wrap sp-wsv-wrap">
    <?php if (!$dismissed) { ?>
        <div id="sp-comunidad-alert" class="alert-comunidad">
            <span>¡Únete a la comunidad Tribu WP! Construye la solucion que tu cliente necesita.</span>
            <a href="<?= esc_url('https://chat.whatsapp.com/DGiuqztlTlnAYalWnRTcQt?mode=gi_t') ?>" target="_blank" class="btn btn-whatsapp">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                    <path d="M12 0C5.373 0 0 5.373 0 12c0 2.124.558 4.127 1.532 5.864L.057 23.571a.75.75 0 0 0 .921.921l5.764-1.473A11.955 11.955 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.907 0-3.686-.523-5.205-1.431l-.364-.216-3.768.964.98-3.7-.236-.374A9.96 9.96 0 0 1 2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z" />
                </svg>
                Comunidad de WhatsApp
            </a>
            <a href="<?= esc_url('https://www.youtube.com/@themarketsp') ?>" target="_blank" class="btn btn-youtube">
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

    <div class="sp-wsv-header">
        <h1 class="sp-wsv-title"><?php echo esc_html__('Gestión de módulos', 'superplus-for-woocommerce'); ?></h1>
        <p class="sp-wsv-subtitle">
            <?php echo esc_html__('Activa o desactiva los módulos disponibles y gestionalos a tu gusto.', 'superplus-for-woocommerce'); ?>
        </p>
    </div>

    <?php if (! empty($notice)) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($notice); ?></p>
        </div>
    <?php endif; ?>

    <form class="sp-wsv-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="sp_wsv_save_modules" />
        <?php wp_nonce_field('sp_wsv_save_modules', 'sp_wsv_nonce'); ?>

        <div class="sp-wsv-modules-grid">
            <?php if (empty($wsv_modules)) : ?>
                <div class="sp-wsv-empty">
                    <?php echo esc_html__('No hay módulos registrados.', 'superplus-for-woocommerce'); ?>
                </div>
            <?php else : ?>
                <?php foreach ($wsv_modules as $sp_wsv_raw_id => $sp_wsv_module) : ?>
                    <?php
                    $sp_wsv_module_id  = sanitize_key($sp_wsv_raw_id);
                    $sp_wsv_is_active  = $sp_wsv_module->is_active();
                    $sp_wsv_title      = $sp_wsv_module->get_title();
                    $sp_wsv_desc       = $sp_wsv_module->get_description();
                    $sp_wsv_image_url  = method_exists($sp_wsv_module, 'get_image_url') ? $sp_wsv_module->get_image_url() : '';
                    $sp_wsv_tier       = method_exists($sp_wsv_module, 'get_tier') ? $sp_wsv_module->get_tier() : 'FREE';
                    $sp_wsv_locked     = method_exists($sp_wsv_module, 'is_locked') ? (bool) $sp_wsv_module->is_locked() : false;
                    $sp_wsv_toggleable = method_exists($sp_wsv_module, 'is_toggleable') ? (bool) $sp_wsv_module->is_toggleable() : true;
                    ?>

                    <div class="sp-wsv-card <?php echo esc_attr($sp_wsv_is_active ? 'is-active' : ''); ?> <?php echo esc_attr($sp_wsv_locked ? 'is-locked' : ''); ?>" data-module="<?php echo esc_attr($sp_wsv_module_id); ?>">
                        <div class="sp-wsv-card-icon">
                            <?php if (! empty($sp_wsv_image_url)) : ?>
                                <img src="<?php echo esc_url($sp_wsv_image_url); ?>" alt="" />
                            <?php endif; ?>
                        </div>

                        <div class="sp-wsv-card-top">
                            <div class="sp-wsv-card-meta">
                                <div class="sp-wsv-card-title"><?php echo esc_html($sp_wsv_title); ?></div>
                                <div class="sp-wsv-card-id"><?php echo esc_html($sp_wsv_module_id); ?></div>
                            </div>

                            <div class="sp-wsv-card-badges">
                                <span class="sp-wsv-badge sp-wsv-badge-tier"><?php echo esc_html(strtoupper((string) $sp_wsv_tier)); ?></span>
                                <?php if ($sp_wsv_locked) : ?>
                                    <span class="sp-wsv-badge sp-wsv-badge-locked"><?php echo esc_html__('En desarrollo', 'superplus-for-woocommerce'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="sp-wsv-card-desc">
                            <?php echo esc_html($sp_wsv_desc); ?>
                        </div>

                        <div class="sp-wsv-card-actions">
                            <?php if ($sp_wsv_toggleable) : ?>
                                <label class="sp-wsv-switch">
                                    <input
                                        class="sp-wsv-toggle"
                                        type="checkbox"
                                        name="modules[<?php echo esc_attr($sp_wsv_module_id); ?>]"
                                        value="1"
                                        <?php checked($sp_wsv_is_active); ?> />
                                    <span class="sp-wsv-slider" aria-hidden="true"></span>
                                    <span class="screen-reader-text">
                                        <?php esc_html_e('Activar o desactivar módulo', 'superplus-for-woocommerce'); ?>
                                    </span>
                                </label>
                            <?php else : ?>
                                <button type="button" class="button button-secondary" disabled>
                                    <?php esc_html_e('Próximamente…', 'superplus-for-woocommerce'); ?>
                                </button>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (! class_exists('SP_WSV_Pro_Plugin')) : ?>
            <div class="sp-wsv-pro-header">
                <span class="sp-wsv-pro-header-icon">🔒</span>
                <div>
                    <h2 class="sp-wsv-title2"><?php esc_html_e('Módulos PRO', 'superplus-for-woocommerce'); ?></h2>
                    <p class="sp-wsv-pro-header-desc"><?php esc_html_e('Amplía tu tienda con módulos avanzados.', 'superplus-for-woocommerce'); ?></p>
                </div>
                <a href="https://themarketsp.com/downloads/woocommerce-superplus-pro/" target="_blank" rel="noopener noreferrer" class="sp-wsv-pro-header-btn">
                    <?php esc_html_e('Obtener PRO', 'superplus-for-woocommerce'); ?>
                </a>
            </div>

            <div class="sp-wsv-modules-grid">

                <div class="sp-wsv-card is-locked">
                    <div class="sp-wsv-card-icon">
                        <img src="<?php echo esc_url(SP_WSV_URL . 'includes/modules/assets/img/busqueda-inteligente.png'); ?>" alt="" />
                    </div>
                    <div class="sp-wsv-card-top">
                        <div class="sp-wsv-card-meta">
                            <div class="sp-wsv-card-title"><?php esc_html_e('Búsqueda Inteligente', 'superplus-for-woocommerce'); ?></div>
                            <div class="sp-wsv-card-id"></div>
                        </div>
                        <div class="sp-wsv-card-badges">
                            <span class="sp-wsv-badge sp-wsv-badge-tier">PRO</span>
                        </div>
                    </div>
                    <div class="sp-wsv-card-desc"><?php esc_html_e('Encuentra cualquier producto al instante. Filtra por precio sin esperas ni recargas, para que tus clientes siempre encuentren lo que buscan.', 'superplus-for-woocommerce'); ?></div>

                </div>

                <div class="sp-wsv-card is-locked">
                    <div class="sp-wsv-card-icon">
                        <img src="<?php echo esc_url(SP_WSV_URL . 'includes/modules/assets/img/menu-movil.png'); ?>" alt="" />
                    </div>
                    <div class="sp-wsv-card-top">
                        <div class="sp-wsv-card-meta">
                            <div class="sp-wsv-card-title"><?php esc_html_e('Menú móvil', 'superplus-for-woocommerce'); ?></div>
                            <div class="sp-wsv-card-id"></div>
                        </div>
                        <div class="sp-wsv-card-badges">
                            <span class="sp-wsv-badge sp-wsv-badge-tier">PRO</span>
                        </div>
                    </div>
                    <div class="sp-wsv-card-desc"><?php esc_html_e('Lleva la experiencia móvil al siguiente nivel. Un menú rápido, ordenado por categorías y adaptado al diseño de tu tienda, listo en minutos.', 'superplus-for-woocommerce'); ?></div>

                </div>

                <div class="sp-wsv-card is-locked">
                    <div class="sp-wsv-card-icon">
                        <img src="<?php echo esc_url(SP_WSV_URL . 'includes/modules/assets/img/preguntas-frecuentes.png'); ?>" alt="" />
                    </div>
                    <div class="sp-wsv-card-top">
                        <div class="sp-wsv-card-meta">
                            <div class="sp-wsv-card-title"><?php esc_html_e('Mensajes de WooCommerce', 'superplus-for-woocommerce'); ?></div>
                            <div class="sp-wsv-card-id"></div>
                        </div>
                        <div class="sp-wsv-card-badges">
                            <span class="sp-wsv-badge sp-wsv-badge-tier">PRO</span>
                        </div>
                    </div>
                    <div class="sp-wsv-card-desc"><?php esc_html_e('Los embellece con un diseño atractivo, compatible con cualquier tienda online.', 'superplus-for-woocommerce'); ?></div>

                </div>

                <div class="sp-wsv-card is-locked">
                    <div class="sp-wsv-card-icon">
                        <img src="<?php echo esc_url(SP_WSV_URL . 'includes/modules/assets/img/multi-direccion.webp'); ?>" alt="" />
                    </div>
                    <div class="sp-wsv-card-top">
                        <div class="sp-wsv-card-meta">
                            <div class="sp-wsv-card-title"><?php esc_html_e('Direcciones múltiples', 'superplus-for-woocommerce'); ?></div>
                            <div class="sp-wsv-card-id"></div>
                        </div>
                        <div class="sp-wsv-card-badges">
                            <span class="sp-wsv-badge sp-wsv-badge-tier">PRO</span>
                            <span class="sp-wsv-badge"><?php esc_html_e('En desarrollo', 'superplus-for-woocommerce'); ?></span>
                        </div>
                    </div>
                    <div class="sp-wsv-card-desc"><?php esc_html_e('Permite guardar múltiples direcciones en una sola cuenta. Solo para clientes registrados.', 'superplus-for-woocommerce'); ?></div>
                    <div class="sp-wsv-card-actions">
                        <button type="button" class="button button-secondary" disabled>
                            <?php esc_html_e('Próximamente…', 'superplus-for-woocommerce'); ?>
                        </button>
                    </div>
                </div>

                <div class="sp-wsv-card is-locked">
                    <div class="sp-wsv-card-icon">
                        <img src="<?php echo esc_url(SP_WSV_URL . 'includes/modules/assets/img/sello-recomendado.webp'); ?>" alt="" />
                    </div>

                    <div class="sp-wsv-card-top">
                        <div class="sp-wsv-card-meta">
                            <div class="sp-wsv-card-title"><?php esc_html_e('Sello de recomendado', 'superplus-for-woocommerce'); ?></div>
                            <div class="sp-wsv-card-id"></div>
                        </div>
                        <div class="sp-wsv-card-badges">
                            <span class="sp-wsv-badge sp-wsv-badge-tier">PRO</span>
                            <span class="sp-wsv-badge"><?php esc_html_e('En desarrollo', 'superplus-for-woocommerce'); ?></span>
                        </div>
                    </div>

                    <div class="sp-wsv-card-desc"><?php esc_html_e('Permite establecer una pequeña imagen en los productos. Visible desde el catálogo.', 'superplus-for-woocommerce'); ?></div>
                    <div class="sp-wsv-card-actions">
                        <button type="button" class="button button-secondary" disabled>
                            <?php esc_html_e('Próximamente…', 'superplus-for-woocommerce'); ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>




        <div class="sp-wsv-footer">
            <?php submit_button(__('Guardar módulos', 'superplus-for-woocommerce'), 'primary', 'submit', false); ?>
        </div>

        <button type="submit" class="button button-primary sp-wsv-save-fab" disabled>
            <?php echo esc_html__('Guardar cambios', 'superplus-for-woocommerce'); ?>
        </button>
    </form>
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
                    nonce: '<?php echo wp_create_nonce("sp_wsv_dismiss_alert_nonce"); ?>'
                }
            })
        })
    })
</script>