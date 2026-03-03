<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once SP_WSV_PATH . 'includes/class-sp-wsv-module.php';
require_once SP_WSV_PATH . 'includes/class-sp-wsv-module-manager.php';
require_once SP_WSV_PATH . 'includes/admin/class-sp-wsv-admin.php';

final class SP_WSV_Plugin {

    private static $instance = null;

    /** @var SP_WSV_Module_Manager */
    public $wsv_modules;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function get_pro_url() {
        $url = apply_filters( 'sp_wsv_pro_upsell_url', 'https://themarketsp.com/downloads/woocommerce-superplus-pro/' );
        $url = is_string( $url ) ? trim( $url ) : '';
        if ( $url === '' ) {
            return '';
        }
        if ( ! preg_match( '#^https?://#i', $url ) ) {
            $url = 'https://' . ltrim( $url, '/' );
        }
        return $url;
    }

    private function __construct() {
        $this->modules = new SP_WSV_Module_Manager();

        add_action( 'init', array( $this, 'register_modules' ), 5 );
        add_action( 'init', array( $this, 'boot_active_modules' ), 20 );

        if ( is_admin() ) {
            new SP_WSV_Admin( $this->modules );
            add_action( 'admin_notices', array( $this, 'notice_missing_woocommerce' ) );
        }

        /**
         * Extension point for add-ons (e.g. PRO).
         */
        do_action( 'sp_wsv_after_bootstrap', $this );
    }

    public function boot_active_modules() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return;
        }
        $this->modules->boot_active_modules();
    }

    public function notice_missing_woocommerce() {
        if ( class_exists( 'WooCommerce' ) ) {
            return;
        }
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        echo '<div class="notice notice-error"><p>';
        echo esc_html__( 'SuperPlus para Woocommerce requiere WooCommerce instalado y activo.', 'superplus-for-woocommerce' );
        echo '</p></div>';
    }

    private function register_pro_skeletons() {
        require_once SP_WSV_PATH . 'includes/modules/pro-skeletons/class-sp-wsv-module-pro-skeleton.php';
        require_once SP_WSV_PATH . 'includes/modules/pro-skeletons/class-sp-wsv-pro-skeleton-definitions.php';

        $defs = SP_WSV_Pro_Skeleton_Definitions::get();

        foreach ( $defs as $id => $data ) {
            $this->modules->register(
                new SP_WSV_Module_Pro_Skeleton(
                    $id,
                    $data['title'],
                    $data['description'],
                    array(
                        'image_url'  => isset( $data['image_url'] ) ? $data['image_url'] : '',
                    )
                )
            );
        }
    }

    public function register_modules() {
        // FREE modules shipped in the CORE
        $this->modules->register_class(
            'SP_WSV_Module_Checkout_Steps',
            SP_WSV_PATH . 'includes/modules/checkout-steps/class-sp-wsv-module-checkout-steps.php'
        );

        // PRO placeholders (UI only)
        $this->register_pro_skeletons();

        /**
         * Extension point to register extra modules (e.g. PRO real modules).
         * Add-ons can override placeholders by registering the same id.
         */
        do_action( 'sp_wsv_register_modules', $this->modules );

        $this->modules->apply_extensions();
    }
}
