<?php
/**
 * Plugin Name: SuperPlus for Woocommerce 
 * Description: Mejora tu tienda online con menos de diez clics. Compatible con WooCommerce.
 * Version: 1.0.1
 * Author: Agencia SP
 * Author URI: https://agenciasp.com
 * Requires Plugins: woocommerce
 * Text Domain: superplus-for-woocommerce
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SP_WSV_VERSION', '1.0.0' );
define( 'SP_WSV_PATH', plugin_dir_path( __FILE__ ) );
define( 'SP_WSV_URL', plugin_dir_url( __FILE__ ) );

require_once SP_WSV_PATH . 'includes/class-sp-wsv-plugin.php';
require_once SP_WSV_PATH . 'includes/admin/class-sp-wsv-plugin-meta.php';

function sp_wsv() {
    return SP_WSV_Plugin::instance();
}

function sp_wsv_activate() {
    $plugin = sp_wsv();
    if ( is_object( $plugin ) && method_exists( $plugin, 'register_modules' ) ) {
        $plugin->register_modules();
    }

    $active_map = array();
    if ( is_object( $plugin ) && isset( $plugin->modules ) && is_object( $plugin->modules ) && method_exists( $plugin->modules, 'get_modules' ) ) {
        foreach ( (array) $plugin->modules->get_modules() as $wsv_module_id => $wsv_module ) {
            if ( is_object( $wsv_module ) && method_exists( $wsv_module, 'is_toggleable' ) && $wsv_module->is_toggleable() ) {
                $active_map[ sanitize_key( $wsv_module_id ) ] = true;
            }
        }
    }

    update_option( 'sp_wsv_active_modules', $active_map, false );
}

register_activation_hook( __FILE__, 'sp_wsv_activate' );
add_action( 'plugins_loaded', 'sp_wsv' );

SP_WSV_Plugin_Meta::init( __FILE__ );
