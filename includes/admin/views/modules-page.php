<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div class="wrap sp-wsv-wrap">
    <div class="sp-wsv-header">
        <h1 class="sp-wsv-title"><?php echo esc_html__( 'Gestión de módulos', 'superplus-for-woocommerce' ); ?></h1>
        <p class="sp-wsv-subtitle">
            <?php echo esc_html__( 'Activa o desactiva los módulos disponibles. Los módulos desactivados no cargan hooks ni assets.', 'superplus-for-woocommerce' ); ?>
        </p>
    </div>

    <?php if ( ! empty( $notice ) ) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html( $notice ); ?></p>
        </div>
    <?php endif; ?>

    <form class="sp-wsv-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="sp_wsv_save_modules" />
        <?php wp_nonce_field( 'sp_wsv_save_modules', 'sp_wsv_nonce' ); ?>

        <div class="sp-wsv-modules-grid">
            <?php if ( empty( $wsv_modules ) ) : ?>
                <div class="sp-wsv-empty">
                    <?php echo esc_html__( 'No hay módulos registrados.', 'superplus-for-woocommerce' ); ?>
                </div>
            <?php else : ?>
                <?php foreach ( $wsv_modules as $sp_wsv_module_id => $sp_wsv_module ) : ?>
                    <?php
                    $sp_wsv_module_id  = sanitize_key( $sp_wsv_module_id );
                    $sp_wsv_is_active  = $sp_wsv_module->is_active();
                    $sp_wsv_title      = $sp_wsv_module->get_title();
                    $sp_wsv_desc       = $sp_wsv_module->get_description();
                    $sp_wsv_image_url  = method_exists( $sp_wsv_module, 'get_image_url' ) ? $sp_wsv_module->get_image_url() : '';
                    $sp_wsv_tier       = method_exists( $sp_wsv_module, 'get_tier' ) ? $sp_wsv_module->get_tier() : 'FREE';
                    $sp_wsv_locked     = method_exists( $sp_wsv_module, 'is_locked' ) ? (bool) $sp_wsv_module->is_locked() : false;
                    $sp_wsv_toggleable = method_exists( $sp_wsv_module, 'is_toggleable' ) ? (bool) $sp_wsv_module->is_toggleable() : true;
                    $sp_wsv_pro_active = defined( 'SP_WSV_PRO_VERSION' );
                    $sp_wsv_is_pro_skeleton = $sp_wsv_pro_active && class_exists( 'SP_WSV_Module_Pro_Skeleton' ) && ( $sp_wsv_module instanceof SP_WSV_Module_Pro_Skeleton );
                    if ( $sp_wsv_is_pro_skeleton ) {
                        $sp_wsv_behavior = apply_filters( 'sp_wsv_pro_skeleton_behavior_when_pro', 'coming_soon', $sp_wsv_module_id, $sp_wsv_module );
                        $sp_wsv_behavior = is_string( $sp_wsv_behavior ) ? strtolower( trim( $sp_wsv_behavior ) ) : 'coming_soon';
                        if ( $sp_wsv_behavior === 'hide' ) {
                            continue;
                        }
                    }
                    ?>
                    <div class="sp-wsv-card <?php echo $sp_wsv_is_active ? 'is-active' : ''; ?> <?php echo $sp_wsv_locked ? 'is-locked' : ''; ?>" data-module="<?php echo esc_attr( $sp_wsv_module_id ); ?>">
                        <div class="sp-wsv-card-icon">
                            <?php if ( ! empty( $sp_wsv_image_url ) ) : ?>
                                <img src="<?php echo esc_url( $sp_wsv_image_url ); ?>" alt="" />
                            <?php endif; ?>
                        </div>

                        <div class="sp-wsv-card-top">

                            <div class="sp-wsv-card-meta">
                                <div class="sp-wsv-card-title"><?php echo esc_html( $sp_wsv_title ); ?></div>
                                <div class="sp-wsv-card-id"><?php echo esc_html( $sp_wsv_module_id ); ?></div>
                            </div>

                            <div class="sp-wsv-card-badges">
                                <span class="sp-wsv-badge sp-wsv-badge-tier"><?php echo esc_html( strtoupper( (string) $sp_wsv_tier ) ); ?></span>
                                <?php if ( $sp_wsv_locked ) : ?>
                                    <span class="sp-wsv-badge sp-wsv-badge-locked"><?php echo esc_html__( 'Bloqueado', 'superplus-for-woocommerce' ); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="sp-wsv-card-desc">
                            <?php echo esc_html( $sp_wsv_desc ); ?>
                        </div>

                        <div class="sp-wsv-card-actions">
                            <?php if ( $sp_wsv_toggleable ) : ?>
                                <label class="sp-wsv-switch">
                                    <input
                                        class="sp-wsv-toggle"
                                        type="checkbox"
                                        name="modules[<?php echo esc_attr( $sp_wsv_module_id ); ?>]"
                                        value="1"
                                        <?php checked( $sp_wsv_is_active ); ?>
                                    />
                                    <span class="sp-wsv-slider" aria-hidden="true"></span>
                                    <span class="screen-reader-text">
                                        <?php echo esc_html__( 'Activar o desactivar módulo', 'superplus-for-woocommerce' ); ?>
                                    </span>
                                </label>
                            <?php else : ?>
                                <div class="sp-wsv-locked-actions">
                                    <?php if ( $sp_wsv_is_pro_skeleton ) : ?>
                                        <span class="sp-wsv-locked-text"><?php echo esc_html__( 'Próximamente…', 'superplus-for-woocommerce' ); ?></span>
                                        <button type="button" class="button button-secondary" disabled>
                                            <?php echo esc_html__( 'Próximamente…', 'superplus-for-woocommerce' ); ?>
                                        </button>
                                    <?php else : ?>
                                        <span class="sp-wsv-locked-text"><?php echo esc_html__( 'Disponible con licencia', 'superplus-for-woocommerce' ); ?></span>
                                        <a href="#" class="button button-secondary sp-wsv-go-pro-popup-trigger">
                                            <?php echo esc_html__( 'Obtener PRO', 'superplus-for-woocommerce' ); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="sp-wsv-footer">
            <?php submit_button( __( 'Guardar módulos', 'superplus-for-woocommerce' ), 'primary', 'submit', false ); ?>
        </div>

        <button type="submit" class="button button-primary sp-wsv-save-fab" disabled>
            <?php echo esc_html__( 'Guardar cambios', 'superplus-for-woocommerce' ); ?>
        </button>
    </form>
</div>
