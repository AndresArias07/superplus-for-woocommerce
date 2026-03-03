<?php
if ( ! defined( 'ABSPATH' ) ) exit;

final class SP_WSV_Plugin_Meta {

    private $plugin_file;

    public static function init( $plugin_file ) {
        if ( ! is_admin() ) {
            return;
        }
        $instance = new self( $plugin_file );
        $instance->hooks();
    }

    private function __construct( $plugin_file ) {
        $this->plugin_file = (string) $plugin_file;
    }

    private function hooks() {
        $basename = plugin_basename( $this->plugin_file );
        add_filter( 'plugin_action_links_' . $basename, array( $this, 'plugin_action_links' ) );
        add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
    }

    private function get_pro_url() {
        if ( class_exists( 'SP_WSV_Plugin' ) && method_exists( 'SP_WSV_Plugin', 'get_pro_url' ) ) {
            return esc_url( SP_WSV_Plugin::get_pro_url() );
        }
        return '';
    }

    private function get_documentation_url() {
        return "";
    }

    public function plugin_action_links( $links ) {
        $links = is_array( $links ) ? $links : array();

        $settings_url = admin_url( 'admin.php?page=sp-wsv-settings' );
        $pro_url = $this->get_pro_url();

        $custom = array();
        $custom['settings'] = '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Configuración', 'superplus-for-woocommerce' ) . '</a>';
        if ( $pro_url !== '' ) {
            $custom['go_pro'] =
                '<a href="#" class="sp-wsv-go-pro-popup-trigger">' .
                esc_html__( 'Adquirir PRO', 'superplus-for-woocommerce' ) .
                '</a>';
        }

        return array_merge( $custom, $links );
    }

    public function plugin_row_meta( $links, $file ) {
        $basename = plugin_basename( $this->plugin_file );
        if ( $file !== $basename ) {
            return $links;
        }

        $links = is_array( $links ) ? $links : array();
        $site_url = $this->get_documentation_url();
        if ( $site_url !== '' ) {
            $links[] = '<a href="' . esc_url( $site_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Documentación', 'superplus-for-woocommerce' ) . '</a>';
        }
        return $links;
    }
}
