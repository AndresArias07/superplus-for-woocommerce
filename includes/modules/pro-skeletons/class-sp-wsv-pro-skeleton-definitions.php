<?php
if ( ! defined( 'ABSPATH' ) ) exit;

final class SP_WSV_Pro_Skeleton_Definitions {
    public static function get() {
        return array(
            'catalog_search' => array(
                'title'       => __( 'Catálogo + buscador', 'superplus-for-woocommerce' ),
                'description' => __( 'El catálogo que toda tienda online necesita: filtros, buscador y paginado.', 'superplus-for-woocommerce' ),
                'image_url'   => defined( 'SP_WSV_URL' ) ? SP_WSV_URL . 'includes/modules/pro-skeletons/assets/img/buscador-y-filtros.webp' : '',
            ),
            'mobile_menu' => array(
                'title'       => __( 'Menú móvil', 'superplus-for-woocommerce' ),
                'description' => __( 'Con paginado real y sistema de navegación, no como los menús nativos de Wordpress.', 'superplus-for-woocommerce' ),
                'image_url'   => defined( 'SP_WSV_URL' ) ? SP_WSV_URL . 'includes/modules/pro-skeletons/assets/img/menu-movil.webp' : '',
            ),
            'woocommerce_messages' => array(
                'title'       => __( 'Mensajes de WooCommerce', 'superplus-for-woocommerce' ),
                'description' => __( 'Simplemente los hace bonitos, compatible con cualquier tienda online.', 'superplus-for-woocommerce' ),
                'image_url'   => defined( 'SP_WSV_URL' ) ? SP_WSV_URL . 'includes/modules/pro-skeletons/assets/img/preguntas-frecuentes.webp' : '',
            ),
            'multiple_addresses' => array(
                'title'       => __( 'Direcciones múltiples', 'superplus-for-woocommerce' ),
                'description' => __( 'Permite guardar múltiples direcciones en una sola cuenta. Solo para clientes registrados.', 'superplus-for-woocommerce' ),
                'image_url'   => defined( 'SP_WSV_URL' ) ? SP_WSV_URL . 'includes/modules/pro-skeletons/assets/img/multi-direccion.webp' : '',
            ),
            'recommended_badge' => array(
                'title'       => __( 'Sello de recomendado', 'superplus-for-woocommerce' ),
                'description' => __( 'Permite establecer una pequeña imagen en los productos. Visible desde el catálogo.', 'superplus-for-woocommerce' ),
                'image_url'   => defined( 'SP_WSV_URL' ) ? SP_WSV_URL . 'includes/modules/pro-skeletons/assets/img/sello-recomendado.webp' : '',
            ),
        );
    }
}
