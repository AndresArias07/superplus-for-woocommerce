<?php
if (! defined('ABSPATH')) exit;

class SP_WSV_Module_Checkout_Steps extends SP_WSV_Module
{

    private $option_key = 'sp_wsv_checkout_steps_options';
    private $shortcode_checkout_context = false;

    public function get_id()
    {
        return 'checkout_steps';
    }

    public function get_title()
    {
        return __('Checkout Optimizado', 'superplus-for-woocommerce');
    }

    public function get_description()
    {
        return __('Un proceso de compra más claro, rápido y sin distracciones. El resumen siempre visible guía al cliente hasta el final, reduciendo abandonos y aumentando tus conversiones.', 'superplus-for-woocommerce');
    }

    public function get_image_url()
    {
        return SP_WSV_URL . 'includes/modules/checkout-steps/assets/img/finalizar-compra.png';
    }

    public function boot()
    {
        if (! class_exists('WooCommerce')) {
            return;
        }

        add_shortcode('sp_wsv_checkout', array($this, 'shortcode_checkout'));

        $opts = $this->get_options();
        $distribution = isset($opts['distribution']) ? (string) $opts['distribution'] : 'shopify';

        add_filter('woocommerce_enqueue_styles', array($this, 'filter_woocommerce_enqueue_styles'), 10, 1);
        add_filter('body_class', array($this, 'filter_body_class'), 20, 1);
        add_action('woocommerce_before_checkout_form', array($this, 'maybe_remove_default_coupon_form'), 0);
        add_action('sp_wsv_simple_header_after_logo', array($this, 'render_header_stepper'), 10);
        add_action('woocommerce_thankyou', array($this, 'render_thankyou_back_to_shop'), 30, 1);

        if ($distribution !== '') {
            add_filter('template_include', array($this, 'template_include'), 99);
            add_filter('woocommerce_locate_template', array($this, 'locate_woocommerce_template'), 10, 3);
        } else {
            add_action('woocommerce_checkout_before_customer_details', array($this, 'open_additive_layout'), 1);
            add_action('woocommerce_checkout_after_order_review', array($this, 'close_additive_layout'), 99);
        }

        add_filter('woocommerce_update_order_review_fragments', array($this, 'add_sidebar_summary_fragment'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'), 9999);

        if (is_admin()) {
            add_action('admin_init', array($this, 'register_settings'));
            add_action('admin_init', array($this, 'maybe_repair_options'), 5);
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        }
        add_action('wp', array($this, 'detect_shortcode_context'), 5);
    }

    public function filter_woocommerce_enqueue_styles($enqueue_styles)
    {
        if (is_admin() || wp_doing_ajax()) {
            return $enqueue_styles;
        }

        if (! $this->is_checkout_context() && ! $this->is_cart_context()) {
            return $enqueue_styles;
        }

        if (isset($enqueue_styles['woocommerce-smallscreen'])) {
            unset($enqueue_styles['woocommerce-smallscreen']);
        }

        return $enqueue_styles;
    }

    public function render_thankyou_back_to_shop($order_id)
    {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }

        if (! $this->is_checkout_context() || $this->is_cart_context()) {
            return;
        }

        $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/');
        if (! is_string($shop_url) || $shop_url === '') {
            $shop_url = home_url('/');
        }
?>
        <p class="sp-wsv-thankyou-back">
            <a class="button sp-wsv-btn-primary" href="<?php echo esc_url($shop_url); ?>">
                <?php echo esc_html__('Volver a la tienda', 'superplus-for-woocommerce'); ?>
            </a>
        </p>
    <?php
    }

    public function render_header_stepper()
    {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }

        if ($this->is_order_pay_context() || $this->is_order_received_context()) {
            return;
        }

        $opts = $this->get_options();
        $distribution = isset($opts['distribution']) ? (string) $opts['distribution'] : '';
        if ($distribution !== 'woocommerce-steps') {
            return;
        }

        $is_checkout = $this->is_checkout_context();
        $is_cart     = $this->is_cart_context();
        if (! $is_checkout && ! $is_cart) {
            return;
        }

        if (function_exists('WC') && WC()->cart) {
            if (method_exists(WC()->cart, 'get_cart')) {
                WC()->cart->get_cart();
            }
            if (WC()->cart->is_empty()) {
                return;
            }
        }

        $has_cart_page = self::has_cart_page();
        $cart_url     = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/');
        $checkout_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/');
    ?>
        <nav class="sp-wsv-steps-nav sp-wsv-steps-nav--header" aria-label="<?php echo esc_attr__('Pasos', 'superplus-for-woocommerce'); ?>">
            <ol class="sp-wsv-steps-list">
                <?php if ($is_cart) : ?>
                    <li class="sp-wsv-step-indicator is-active is-link" data-step="1">
                        <a href="<?php echo esc_url($cart_url); ?>">
                            <span class="sp-wsv-step-num">1</span>
                            <span class="sp-wsv-step-label"><?php echo esc_html__('Carrito', 'superplus-for-woocommerce'); ?></span>
                        </a>
                    </li>
                    <li class="sp-wsv-step-indicator is-link" data-step="2">
                        <a href="<?php echo esc_url($checkout_url . '#sp-wsv-step-2'); ?>">
                            <span class="sp-wsv-step-num">2</span>
                            <span class="sp-wsv-step-label"><?php echo esc_html__('Datos', 'superplus-for-woocommerce'); ?></span>
                        </a>
                    </li>
                    <li class="sp-wsv-step-indicator is-link" data-step="3">
                        <a href="<?php echo esc_url($checkout_url . '#sp-wsv-step-3'); ?>">
                            <span class="sp-wsv-step-num">3</span>
                            <span class="sp-wsv-step-label"><?php echo esc_html__('Envío y pago', 'superplus-for-woocommerce'); ?></span>
                        </a>
                    </li>
                    <li class="sp-wsv-step-indicator is-link" data-step="4">
                        <a href="<?php echo esc_url($checkout_url . '#sp-wsv-step-4'); ?>">
                            <span class="sp-wsv-step-num">4</span>
                            <span class="sp-wsv-step-label"><?php echo esc_html__('Resumen', 'superplus-for-woocommerce'); ?></span>
                        </a>
                    </li>
                <?php else : ?>
                    <?php if ($has_cart_page) : ?>
                        <li class="sp-wsv-step-indicator is-link" data-step="1">
                            <a href="<?php echo esc_url($cart_url); ?>">
                                <span class="sp-wsv-step-num">✓</span>
                                <span class="sp-wsv-step-label"><?php echo esc_html__('Carrito', 'superplus-for-woocommerce'); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="sp-wsv-step-indicator is-active is-anchor" data-step="2">
                        <a class="sp-wsv-step-anchor" href="#sp-wsv-step-2">
                            <span class="sp-wsv-step-num"><?php echo $has_cart_page ? '2' : '1'; ?></span>
                            <span class="sp-wsv-step-label"><?php echo esc_html__('Datos', 'superplus-for-woocommerce'); ?></span>
                        </a>
                    </li>
                    <li class="sp-wsv-step-indicator is-anchor" data-step="3">
                        <a class="sp-wsv-step-anchor" href="#sp-wsv-step-3">
                            <span class="sp-wsv-step-num"><?php echo $has_cart_page ? '3' : '2'; ?></span>
                            <span class="sp-wsv-step-label"><?php echo esc_html__('Envío y pago', 'superplus-for-woocommerce'); ?></span>
                        </a>
                    </li>
                    <li class="sp-wsv-step-indicator is-anchor" data-step="4">
                        <a class="sp-wsv-step-anchor" href="#sp-wsv-step-4">
                            <span class="sp-wsv-step-num"><?php echo $has_cart_page ? '4' : '3'; ?></span>
                            <span class="sp-wsv-step-label"><?php echo esc_html__('Resumen', 'superplus-for-woocommerce'); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
            </ol>
        </nav>
    <?php
    }

    public function maybe_remove_default_coupon_form()
    {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }
        if (! $this->is_checkout_context()) {
            return;
        }

        $opts = $this->get_options();
        $distribution = isset($opts['distribution']) ? (string) $opts['distribution'] : 'shopify';
        if (! in_array($distribution, array('shopify', 'minimal-steps'), true)) {
            return;
        }

        remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
    }

    public function detect_shortcode_context()
    {
        if (is_admin()) {
            return;
        }

        global $post;
        if (! ($post instanceof WP_Post)) {
            return;
        }

        if (has_shortcode((string) $post->post_content, 'sp_wsv_checkout')) {
            $this->shortcode_checkout_context = true;
            add_filter('woocommerce_is_checkout', '__return_true', 20);
        }
    }

    private function is_checkout_context()
    {
        return (function_exists('is_checkout') && is_checkout()) || $this->shortcode_checkout_context;
    }

    private function is_cart_context()
    {
        return function_exists('is_cart') && is_cart();
    }

    private function is_order_pay_context()
    {
        return function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-pay');
    }

    private function is_order_received_context()
    {
        return function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received');
    }

    private function is_checkout_login_required()
    {
        if (! $this->is_checkout_context() || is_user_logged_in()) {
            return false;
        }

        if (! function_exists('WC')) {
            return false;
        }

        $checkout = WC()->checkout();
        if (! $checkout || ! is_object($checkout)) {
            return false;
        }

        if (! method_exists($checkout, 'is_registration_enabled') || ! method_exists($checkout, 'is_registration_required')) {
            return false;
        }

        return ! $checkout->is_registration_enabled() && $checkout->is_registration_required();
    }

    public static function has_cart_page()
    {
        if (! function_exists('wc_get_page_id')) {
            return false;
        }
        $cart_id = (int) wc_get_page_id('cart');
        if ($cart_id <= 0) {
            return false;
        }
        $status = get_post_status($cart_id);
        return $status === 'publish';
    }

    public function shortcode_checkout()
    {
        if (! class_exists('WooCommerce')) {
            return '';
        }

        return do_shortcode('[woocommerce_checkout]');
    }

    public function filter_body_class($classes)
    {
        if (is_admin() || wp_doing_ajax()) {
            return $classes;
        }

        $is_checkout = $this->is_checkout_context();
        $is_cart     = $this->is_cart_context();
        if (! $is_checkout && ! $is_cart) {
            return $classes;
        }

        $opts = $this->get_options();
        $distribution = isset($opts['distribution']) ? (string) $opts['distribution'] : 'shopify';
        if ($distribution === '' && $is_cart) {
            return $classes;
        }

        $classes[] = 'sp-wsv-simple';
        $classes[] = $is_checkout ? 'sp-wsv-page-checkout' : 'sp-wsv-page-cart';
        if ($is_checkout && $this->is_checkout_login_required()) {
            $classes[] = 'sp-wsv-checkout-login-required';
        }

        $distribution_class = self::get_distribution_class();
        if ($distribution_class !== '') {
            $classes[] = $distribution_class;
        }

        $theme_class = self::get_theme_class();
        if ($theme_class !== '') {
            $classes[] = $theme_class;
        }

        $layout = isset($opts['layout']) ? sanitize_key((string) $opts['layout']) : '';
        if (in_array($layout, array('sidebar-left', 'sidebar-right'), true)) {
            $classes[] = 'sp-wsv-layout-' . $layout;
        }

        return $classes;
    }

    /* ============================================================
     *  Opciones / configuración
     * ============================================================ */

    public static function get_default_options()
    {
        $defaults = array(
            'distribution' => 'minimal-steps',
            'theme'      => 'light',
            'custom_css'       => '',
        );
        return (array) apply_filters('sp_wsv_checkout_default_options', $defaults);
    }

    public static function get_available_distributions()
    {
        $distributions = array(
            'minimal-steps'    => array(
                'title' => __('Minimalista por pasos', 'superplus-for-woocommerce'),
                'description' => __('Diseño minimalista con pasos secuenciales. Ideal para experiencias de compra fluidas y sin distracciones.'),

            ),
            'woocommerce-steps' => array(
                'title' => __('Woocommerce por pasos', 'superplus-for-woocommerce'),
                'description' => __('Distribución con indicador de pasos en la cabecera. Mantiene el estilo tradicional de WooCommerce pero mejorado visualmente.', 'superplus-for-woocommerce'),

            ),
        );
        return (array) apply_filters('sp_wsv_checkout_skins', $distributions);
    }

    public static function get_available_themes()
    {
        return array(
            'none'  => array(
                'title' => __('Sin estilos', 'superplus-for-woocommerce'),
            ),
            'light' => array(
                'title' => __('Ligth', 'superplus-for-woocommerce'),
            ),
            'dark'  => array(
                'title' => __('Dark', 'superplus-for-woocommerce'),
            ),
        );
    }

    public function get_options()
    {
        $saved = get_option($this->option_key, array());
        if (! is_array($saved)) {
            $saved = array();
        }
        $opts = array_merge(self::get_default_options(), $saved);

        if (empty($opts['distribution']) && isset($opts['skin'])) {
            $opts['distribution'] = (string) $opts['skin'];
        }

        $legacy = isset($opts['distribution']) ? (string) $opts['distribution'] : '';
        if ($legacy === 'woocommerce') {
            $opts['distribution'] = 'woocommerce-steps';
        } elseif ($legacy === 'shopify') {
            $opts['distribution'] = 'shopify';
        }

        $available = array_keys(self::get_available_distributions());
        $current = isset($opts['distribution']) ? (string) $opts['distribution'] : '';
        if (! in_array($current, $available, true)) {
            $opts['distribution'] = (string) self::get_default_options()['distribution'];
        }

        return $opts;
    }

    public static function get_options_static()
    {
        $instance = sp_wsv()->modules->get_modules()['checkout_steps'] ?? null;
        if ($instance instanceof self) {
            return $instance->get_options();
        }
        // Fallback por si se llama antes de instanciar (raro)
        $saved = get_option('sp_wsv_checkout_steps_options', array());
        if (! is_array($saved)) $saved = array();
        $opts = array_merge(self::get_default_options(), $saved);

        if (empty($opts['distribution']) && isset($opts['skin'])) {
            $opts['distribution'] = (string) $opts['skin'];
        }

        $legacy = isset($opts['distribution']) ? (string) $opts['distribution'] : '';
        if ($legacy === 'woocommerce') {
            $opts['distribution'] = 'woocommerce-steps';
        } elseif ($legacy === 'shopify') {
            $opts['distribution'] = 'shopify';
        }

        $available = array_keys(self::get_available_distributions());
        $current = isset($opts['distribution']) ? (string) $opts['distribution'] : '';
        if (! in_array($current, $available, true)) {
            $opts['distribution'] = (string) self::get_default_options()['distribution'];
        }

        return $opts;
    }

    public static function get_distribution_class()
    {
        $o = self::get_options_static();
        $available = array_keys(self::get_available_distributions());
        $distribution = isset($o['distribution']) ? (string) $o['distribution'] : '';
        $distribution = in_array($distribution, $available, true) ? $distribution : (string) reset($available);
        if ($distribution === '') {
            return '';
        }
        return 'sp-wsv-distribution-' . sanitize_key($distribution);
    }

    public static function get_theme_class()
    {
        $o = self::get_options_static();
        $theme = sanitize_key(isset($o['theme']) ? $o['theme'] : 'light');
        if ($theme === 'none') {
            return '';
        }
        $theme = in_array($theme, array('light', 'dark'), true) ? $theme : 'light';
        return 'sp-wsv-theme-' . $theme;
    }

    public function register_settings()
    {
        register_setting(
            'sp_wsv_checkout_steps',
            $this->option_key,
            array($this, 'sanitize_options')
        );
    }

    public function maybe_repair_options()
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $saved = get_option($this->option_key, array());
        if (! is_array($saved)) {
            $saved = array();
        }

        $opts = array_merge(self::get_default_options(), $saved);

        if (empty($opts['distribution']) && isset($opts['skin'])) {
            $opts['distribution'] = (string) $opts['skin'];
        }
        if (isset($opts['distribution']) && $opts['distribution'] === 'woocommerce') {
            $opts['distribution'] = 'woocommerce-steps';
        }

        $available = array_keys(self::get_available_distributions());
        $current = isset($opts['distribution']) ? (string) $opts['distribution'] : '';
        if (! in_array($current, $available, true)) {
            $opts['distribution'] = (string) self::get_default_options()['distribution'];
        }

        unset($opts['skin'], $opts['force_wc_checkout_content']);

        if ($opts !== $saved) {
            update_option($this->option_key, $opts, false);
        }
    }

    public function get_option_key()
    {
        return $this->option_key;
    }

    public function sanitize_options($input)
    {
        $defaults = self::get_default_options();
        $out = array();

        $available_distributions = array_keys(self::get_available_distributions());
        $distribution = '';
        if (isset($input['distribution'])) {
            $distribution = (string) $input['distribution'];
        } elseif (isset($input['skin'])) {
            $distribution = (string) $input['skin'];
        } else {
            $distribution = (string) $defaults['distribution'];
        }

        if ($distribution === 'woocommerce') {
            $distribution = 'woocommerce-steps';
        }
        $out['distribution'] = in_array($distribution, $available_distributions, true) ? $distribution : (string) $defaults['distribution'];

        $theme = isset($input['theme']) ? sanitize_key($input['theme']) : $defaults['theme'];
        $out['theme'] = in_array($theme, array('none', 'light', 'dark'), true) ? $theme : $defaults['theme'];

        $css = isset($input['custom_css']) ? (string) $input['custom_css'] : '';
        $css = str_replace(array('<', '>'), '', $css);
        $out['custom_css'] = $css;

        $out = apply_filters('sp_wsv_checkout_sanitize_options', $out, $input, $defaults, $this);
        return is_array($out) ? $out : array();
    }

    public function get_settings_tab_id()
    {
        return 'checkout';
    }

    public function get_settings_tab_title()
    {
        return __('Checkout', 'superplus-for-woocommerce');
    }

    public function render_settings_tab()
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'superplus-for-woocommerce'));
        }
        echo '<div class="spwsv-free-card__header-left"> <span class="dashicons dashicons-cart"></span><h2>' . esc_html__('Checkout', 'superplus-for-woocommerce') . '</h2></div>';
        $this->render_settings_form();
    }

    protected function render_settings_form()
    {
        $is_saved = isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true';
    ?>
        <a
            href="<?= home_url('finalizar-compra/#sp-wsv-step-2') ?>"
            target="_blank"
            type="button"
            class="button"
            <?php echo $is_saved ? '' : 'disabled'; ?>
            title="<?php echo $is_saved
                        ? esc_attr__('Abrir vista previa', 'superplus-for-woocommerce')
                        : esc_attr__('Guarda los cambios para habilitar la vista previa.', 'superplus-for-woocommerce'); ?>">

            Vista Previa
        </a>
        <?php
        $opts = $this->get_options();
        $distributions = self::get_available_distributions();
        $themes = self::get_available_themes();
        ?>

        <form method="post" action="options.php">
            <?php settings_fields('sp_wsv_checkout_steps'); ?>

            <table class="form-table" role="presentation">
                <tbody>
                    <tr class="spwsv-free-row">
                        <td colspan="2" style="padding: 0 !important;">
                            <div class="spwsv-free-card">

                                <div class="spwsv-free-card__header">
                                    <div class="spwsv-free-card__header-left">
                                        <div class="spwsv-free-card__header-icon">
                                            <span class="dashicons dashicons-layout"></span>
                                        </div>
                                        <div class="spwsv-free-card__header-info">
                                            <div class="spwsv-free-card__header-top">
                                                <strong><?php echo esc_html__('Distribución', 'superplus-for-woocommerce'); ?></strong>
                                            </div>
                                            <p><?php echo esc_html__('Define el estilo visual y el flujo de la página de finalizar compra.', 'superplus-for-woocommerce'); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="spwsv-free-card__body">
                                    <div class="spwsv-choice-grid">
                                        <?php foreach ($distributions as $distribution_id => $distribution) : ?>
                                            <?php $css_id = $distribution_id === '' ? 'default' : sanitize_key($distribution_id); ?>
                                            <?php
                                            $img_url = '';
                                            if ($distribution_id === 'minimal-steps') {
                                                $img_url = SP_WSV_URL . 'includes/modules/checkout-steps/assets/img/dist_min.png';
                                            } elseif ($distribution_id === 'woocommerce-steps') {
                                                $img_url = SP_WSV_URL . 'includes/modules/checkout-steps/assets/img/dist_woo.png';
                                            } elseif ($distribution_id === 'shopify') {
                                                $img_url = SP_WSV_URL . 'includes/modules/checkout-steps/assets/img/dist_shop.png';
                                            }
                                            ?>
                                            <label class="spwsv-choice" data-description="<?php echo esc_attr($distribution['description']); ?>">
                                                <input type="radio" name="<?php echo esc_attr($this->option_key); ?>[distribution]" value="<?php echo esc_attr($distribution_id); ?>" <?php checked((string) $opts['distribution'], (string) $distribution_id); ?> />
                                                <span class="spwsv-choice-thumb">
                                                    <?php if ($img_url) : ?>
                                                        <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($distribution['title']); ?>" />
                                                    <?php endif; ?>
                                                </span>
                                                <span class="spwsv-choice-title"><?php echo esc_html(isset($distribution['title']) ? (string) $distribution['title'] : (string) $distribution_id); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                            </div>
                        </td>
                    </tr>

                    <tr class="spwsv-free-row">
                        <td colspan="2" style="padding: 0 !important;">
                            <div class="spwsv-free-card">

                                <div class="spwsv-free-card__header">
                                    <div class="spwsv-free-card__header-left">
                                        <div class="spwsv-free-card__header-icon">
                                            <span class="dashicons dashicons-admin-customizer"></span>
                                        </div>
                                        <div class="spwsv-free-card__header-info">
                                            <div class="spwsv-free-card__header-top">
                                                <strong><?php echo esc_html__('Tema', 'superplus-for-woocommerce'); ?></strong>
                                            </div>
                                            <p><?php echo esc_html__('Cambia los colores y el contraste del diseño. "Sin estilos" usa el estilo del tema activo.', 'superplus-for-woocommerce'); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="spwsv-free-card__body">
                                    <div class="spwsv-choice-grid spwsv-choice-grid--small">
                                        <?php foreach ($themes as $theme_id => $theme) : ?>
                                            <label class="spwsv-choice">
                                                <input type="radio" name="<?php echo esc_attr($this->option_key); ?>[theme]" value="<?php echo esc_attr($theme_id); ?>" <?php checked((string) $opts['theme'], (string) $theme_id); ?> />
                                                <span class="spwsv-choice-thumb spwsv-thumb--theme-<?php echo esc_attr(sanitize_key($theme_id)); ?>" aria-hidden="true"></span>
                                                <span class="spwsv-choice-title"><?php echo esc_html((string) $theme['title']); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                            </div>
                        </td>
                    </tr>

                    <tr class="spwsv-free-row">
                        <td colspan="2" style="padding: 0 !important;">
                            <div class="spwsv-free-card">

                                <div class="spwsv-free-card__header">
                                    <div class="spwsv-free-card__header-left">
                                        <div class="spwsv-free-card__header-icon">
                                            <span class="dashicons dashicons-editor-code"></span>
                                        </div>
                                        <div class="spwsv-free-card__header-info">
                                            <div class="spwsv-free-card__header-top">
                                                <strong><?php echo esc_html__('CSS adicional (Opciones para desarrolladores)', 'superplus-for-woocommerce'); ?></strong>
                                            </div>
                                            <p><?php echo esc_html__('Escribe aquí tus ajustes de diseño y se aplicarán automáticamente, sin modificar nada del plugin.', 'superplus-for-woocommerce'); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="spwsv-free-card__body">
                                    <textarea id="spwsv_custom_css" class="spwsv-textarea" rows="10"
                                        name="<?php echo esc_attr($this->option_key); ?>[custom_css]"><?php echo esc_textarea($opts['custom_css']); ?></textarea>
                                </div>

                            </div>
                        </td>
                    </tr>
                    <?php do_action('sp_wsv_checkout_settings_rows', $this, $opts); ?>
                </tbody>
            </table>


            <div style="display:flex; gap:8px; align-items:center;">
                <?php submit_button('', 'primary', 'submit', false); ?>

                <a
                    href="<?= home_url('finalizar-compra/#sp-wsv-step-2') ?>"
                    target="_blank"
                    type="button"
                    class="button"
                    <?php echo $is_saved ? '' : 'disabled'; ?>
                    title="<?php echo $is_saved
                                ? esc_attr__('Abrir vista previa', 'superplus-for-woocommerce')
                                : esc_attr__('Guarda los cambios para habilitar la vista previa.', 'superplus-for-woocommerce'); ?>">

                    Vista Previa
                </a>
            </div>
        </form>
        <br>
        <hr />


    <?php
    }

    public function register_admin_page()
    {
        // Cuelga del menú principal del plugin (slug: sp-wsv)
        add_submenu_page(
            'sp-wsv',
            __('Finalizar compra', 'superplus-for-woocommerce'),
            __('Finalizar compra', 'superplus-for-woocommerce'),
            'manage_options',
            'sp-wsv-checkout-steps',
            array($this, 'render_admin_page')
        );
    }

    public function render_admin_page()
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'superplus-for-woocommerce'));
        }
    ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Finalizar compra', 'superplus-for-woocommerce'); ?></h1>
            <?php $this->render_settings_form(); ?>
        </div>
    <?php
    }

    /* ============================================================
     *  Assets / CSS variables
     * ============================================================ */

    public function enqueue_assets()
    {
        $is_checkout = $this->is_checkout_context();
        $is_cart     = $this->is_cart_context();

        if (! $is_checkout && ! $is_cart) {
            return;
        }

        $opts = $this->get_options();
        $distribution = isset($opts['distribution']) ? (string) $opts['distribution'] : 'shopify';
        if ($distribution === '' && $is_cart) {
            return;
        }

        wp_enqueue_style(
            'sp-wsv-checkout-base',
            SP_WSV_URL . 'includes/modules/checkout-steps/assets/css/base.css',
            array(),
            SP_WSV_VERSION
        );

        $inline = '';

        if (! empty($opts['custom_css'])) {
            $inline .= "\n" . $opts['custom_css'] . "\n";
        }

        if ($inline !== '') {
            wp_add_inline_style('sp-wsv-checkout-base', $inline);
        }

        $theme = sanitize_key(isset($opts['theme']) ? $opts['theme'] : 'light');
        if ($theme !== 'none') {
            wp_enqueue_style(
                'sp-wsv-checkout-theme-common',
                SP_WSV_URL . 'includes/modules/checkout-steps/assets/css/theme-common.css',
                array('sp-wsv-checkout-base'),
                SP_WSV_VERSION
            );

            $theme_file = $theme === 'dark' ? 'theme-dark.css' : 'theme-light.css';
            wp_enqueue_style(
                'sp-wsv-checkout-theme',
                SP_WSV_URL . 'includes/modules/checkout-steps/assets/css/' . $theme_file,
                array('sp-wsv-checkout-theme-common'),
                SP_WSV_VERSION
            );
        }

        if ($is_checkout && $distribution !== '') {
            if ($this->is_order_pay_context() || $this->is_order_received_context()) {
                return;
            }

            wp_enqueue_script(
                'sp-wsv-checkout-steps',
                SP_WSV_URL . 'includes/modules/checkout-steps/assets/js/checkout-steps.js',
                array('jquery', 'wc-checkout'),
                SP_WSV_VERSION,
                true
            );

            wp_localize_script('sp-wsv-checkout-steps', 'SP_WSV_CheckoutSteps', array(
                'cart_url' => function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/'),
                'apply_coupon_url' => class_exists('WC_AJAX') ? WC_AJAX::get_endpoint('apply_coupon') : '',
                'i18n' => array(
                    'required'                   => __('Por favor, revisa los campos obligatorios antes de continuar.', 'superplus-for-woocommerce'),
                    'choose_shipping'            => __('Por favor, elige un método de envío para continuar.', 'superplus-for-woocommerce'),
                    'choose_payment'             => __('Por favor, elige un método de pago para continuar.', 'superplus-for-woocommerce'),
                    'coupon_empty'               => __('Por favor, introduce un código de cupón.', 'superplus-for-woocommerce'),
                    'coupon_applying'            => __('Aplicando cupón…', 'superplus-for-woocommerce'),
                    'coupon_applied'             => __('Cupón aplicado.', 'superplus-for-woocommerce'),
                    'coupon_generic_error'       => __('No se pudo aplicar el cupón en este momento.', 'superplus-for-woocommerce'),
                    'coupon_generic_error_retry' => __('No se pudo aplicar el cupón. Revisa el código e inténtalo de nuevo.', 'superplus-for-woocommerce'),
                ),
            ));
        }

        if ($is_cart && $distribution !== '') {
            wp_enqueue_script(
                'sp-wsv-cart-notices-relocate',
                SP_WSV_URL . 'includes/modules/checkout-steps/assets/js/cart-notices-relocate.js',
                array('jquery'),
                SP_WSV_VERSION,
                true
            );
        }
    }

    public function enqueue_admin_assets()
    {
        if (! is_admin()) {
            return;
        }

        $page = sanitize_key((string) filter_input(INPUT_GET, 'page', FILTER_UNSAFE_RAW));
        $tab  = sanitize_key((string) filter_input(INPUT_GET, 'tab', FILTER_UNSAFE_RAW));

        if ($page !== 'sp-wsv-settings' || ($tab !== '' && $tab !== 'checkout')) {
            return;
        }

        wp_enqueue_style('sp-wsv-checkout-admin-settings', SP_WSV_URL . 'includes/modules/checkout-steps/assets/css/admin-settings.css', array(), SP_WSV_VERSION);

        if (function_exists('wp_enqueue_code_editor')) {
            $settings = wp_enqueue_code_editor(array('type' => 'text/css'));
            if (is_array($settings)) {
                wp_enqueue_script('wp-theme-plugin-editor');
                wp_enqueue_style('wp-codemirror');
                wp_add_inline_script(
                    'wp-theme-plugin-editor',
                    'jQuery(function(){ if (window.wp && wp.codeEditor) { wp.codeEditor.initialize("spwsv_custom_css", ' . wp_json_encode($settings) . '); } });'
                );
            }
        }
    }

    /* ============================================================
     *  Templates (checkout/carrito)
     * ============================================================ */

    /**
     * Página de checkout/carrito con cabecera/footer simplificados (comunes).
     */
    public function template_include($template)
    {
        if (is_admin() || wp_doing_ajax()) {
            return $template;
        }

        if (function_exists('is_checkout') && is_checkout()) {
            $custom = SP_WSV_PATH . 'includes/modules/checkout-steps/templates/page-checkout.php';
            if (file_exists($custom)) {
                return $custom;
            }
        }

        if ($this->is_cart_context()) {
            $custom = SP_WSV_PATH . 'includes/modules/checkout-steps/templates/page-cart.php';
            if (file_exists($custom)) {
                return $custom;
            }
        }

        return $template;
    }

    /**
     * Override del template del formulario de checkout de WooCommerce.
     */
    public function locate_woocommerce_template($template, $template_name, $template_path)
    {
        if (is_admin()) {
            return $template;
        }

        $opts = $this->get_options();
        $distribution = isset($opts['distribution']) ? (string) $opts['distribution'] : 'shopify';
        if ($distribution === '') {
            return $template;
        }

        if ($this->is_checkout_context() && $template_name === 'checkout/form-checkout.php') {
            if (in_array($distribution, array('minimal-steps', 'woocommerce-steps'), true)) {
                $custom = SP_WSV_PATH . 'includes/modules/checkout-steps/templates/woocommerce/checkout/form-checkout-steps.php';
                if (file_exists($custom)) {
                    return $custom;
                }
            }

            $custom = SP_WSV_PATH . 'includes/modules/checkout-steps/templates/woocommerce/checkout/form-checkout.php';
            if (file_exists($custom)) {
                return $custom;
            }
        }

        return $template;
    }

    public function open_additive_layout()
    {
        echo '<div class="sp-wsv-checkout-layout"><div class="sp-wsv-checkout-main">';
    }

    public function close_additive_layout()
    {
        echo '</div><aside class="sp-wsv-checkout-sidebar" aria-label="' . esc_attr__('Resumen del pedido', 'superplus-for-woocommerce') . '">';
        echo '<div class="sp-wsv-sidebar-box">';
        echo '<div class="sp-wsv-sidebar-title">' . esc_html__('Tu pedido', 'superplus-for-woocommerce') . '</div>';
        self::render_sidebar_summary(true);
        echo '</div></aside></div>';
    }

    /**
     * Logo (evita anidar <a> dentro de <a>).
     */
    public static function render_logo()
    {
        if (function_exists('get_custom_logo')) {
            $logo = get_custom_logo();
            if ($logo) {
                echo $logo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                return;
            }
        }

        echo '<a class="sp-wsv-site-name-link" href="' . esc_url(home_url('/')) . '">' . esc_html(get_bloginfo('name')) . '</a>';
    }

    /**
     * Añade/actualiza el fragmento AJAX del resumen lateral.
     */
    public function add_sidebar_summary_fragment($fragments)
    {
        if (is_admin()) {
            return $fragments;
        }

        ob_start();
        self::render_sidebar_summary(true); // con wrapper ID
        $fragments['#sp-wsv-sidebar-summary'] = ob_get_clean();

        return $fragments;
    }

    /**
     * Render del resumen lateral (productos + totales).
     *
     * @param bool $with_wrapper Si true, imprime <div id="sp-wsv-sidebar-summary">...</div>
     */
    public static function render_sidebar_summary($with_wrapper = false)
    {

        if (! function_exists('WC') || ! WC()->cart) {
            if ($with_wrapper) {
                echo '<div id="sp-wsv-sidebar-summary"></div>';
            }
            return;
        }

        if ($with_wrapper) {
            echo '<div id="sp-wsv-sidebar-summary">';
        }

        $o                = self::get_options_static();
        $distribution     = isset($o['distribution']) ? (string) $o['distribution'] : '';
        $use_mobile_toggle = in_array($distribution, array('shopify', 'minimal-steps'), true);

        if ($use_mobile_toggle) {
            $total_html = WC()->cart->get_total();

            echo '<div class="sp-wsv-order-summary" data-spwsv-order-summary="1">';
            echo '<button type="button" class="sp-wsv-order-summary__toggle" aria-expanded="false">';
            echo '<span class="sp-wsv-order-summary__label">' . esc_html__('Resumen del pedido', 'superplus-for-woocommerce') . '</span>';
            echo '<span class="sp-wsv-order-summary__amount">' . wp_kses_post($total_html) . '</span>';
            echo '</button>';
            echo '<div class="sp-wsv-order-summary__body">';
        }

        if (WC()->cart->is_empty()) {
            echo '<div class="sp-wsv-sidebar-empty">';
            echo '<p>' . esc_html__('Tu carrito está vacío.', 'superplus-for-woocommerce') . '</p>';
            echo '<p><a class="button" href="' . esc_url(wc_get_page_permalink('shop')) . '">' . esc_html__('Volver a la tienda', 'superplus-for-woocommerce') . '</a></p>';
            echo '</div>';

            if ($use_mobile_toggle) {
                echo '</div></div>';
            }

            if ($with_wrapper) {
                echo '</div>';
            }

            return;
        }

        if ($distribution !== 'woocommerce-steps') {
            echo '<div class="sp-wsv-sidebar-items">';

            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $product = isset($cart_item['data']) ? $cart_item['data'] : null;

                if (! $product || ! $product->exists()) {
                    continue;
                }

                $name = $product->get_name();
                $qty  = isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 1;

                $line_subtotal = WC()->cart->get_product_subtotal($product, $qty);

                $thumb = '';
                if (method_exists($product, 'get_image')) {
                    $thumb = $product->get_image(
                        'thumbnail',
                        array('class' => 'sp-wsv-sidebar-thumb-img')
                    );
                }

                echo '<div class="sp-wsv-sidebar-item">';

                echo '<div class="sp-wsv-sidebar-thumb">';
                if ($thumb) {
                    echo wp_kses_post($thumb);
                }
                echo '<span class="sp-wsv-sidebar-thumb-qty">' . (int) $qty . '</span>';
                echo '</div>';

                echo '<div class="sp-wsv-sidebar-item-body">';
                echo '<div class="sp-wsv-sidebar-item-title">' . esc_html($name) . '</div>';
                echo '<div class="sp-wsv-sidebar-item-subtotal">' . wp_kses_post($line_subtotal) . '</div>';
                echo '</div>';

                echo '</div>';
            }

            echo '</div>';
        }

        echo '<div class="sp-wsv-sidebar-totals woocommerce">';

        if (function_exists('wc_coupons_enabled') && wc_coupons_enabled()) {
            echo '<div class="sp-wsv-sidebar-coupon">';
            echo '<div class="sp-wsv-sidebar-coupon-form" data-spwsv-coupon-form="1" data-spwsv-nonce="' . esc_attr(wp_create_nonce('apply-coupon')) . '">';
            echo '<label class="screen-reader-text" for="sp-wsv-coupon-code">' . esc_html__('Cupón', 'superplus-for-woocommerce') . '</label>';
            echo '<input id="sp-wsv-coupon-code" type="text" name="coupon_code" class="input-text" placeholder="' . esc_attr__('Código de cupón', 'superplus-for-woocommerce') . '" autocomplete="off" />';
            echo '<button type="button" class="button" name="apply_coupon" value="1">' . esc_html__('Aplicar', 'superplus-for-woocommerce') . '</button>';
            echo '<div class="sp-wsv-coupon-status" aria-live="polite"></div>';
            echo '</div>';
            echo '</div>';
        }

        self::render_sidebar_totals_table();
        echo '</div>';

        if ($use_mobile_toggle) {
            echo '</div></div>';
        }

        if ($with_wrapper) {
            echo '</div>';
        }
    }

    private static function get_first_shipping_rate()
    {
        if (! function_exists('WC') || ! WC()->cart) {
            return null;
        }
        if (! WC()->cart->needs_shipping()) {
            return null;
        }
        if (! method_exists(WC(), 'shipping') || ! WC()->shipping()) {
            return null;
        }

        $packages = WC()->shipping()->get_packages();
        if (! is_array($packages) || empty($packages)) {
            return null;
        }

        $rates = isset($packages[0]['rates']) && is_array($packages[0]['rates']) ? $packages[0]['rates'] : array();
        if (empty($rates)) {
            return null;
        }

        $chosen = (WC()->session && method_exists(WC()->session, 'get')) ? WC()->session->get('chosen_shipping_methods') : array();
        $chosen_id = (is_array($chosen) && isset($chosen[0])) ? (string) $chosen[0] : '';

        if ($chosen_id !== '' && isset($rates[$chosen_id])) {
            return $rates[$chosen_id];
        }

        foreach ($rates as $rate) {
            return $rate;
        }

        return null;
    }

    private static function get_first_shipping_destination_html()
    {
        if (! function_exists('WC') || ! WC()->cart) {
            return '';
        }
        if (! method_exists(WC(), 'shipping') || ! WC()->shipping()) {
            return '';
        }

        $packages = WC()->shipping()->get_packages();
        if (! is_array($packages) || empty($packages)) {
            return '';
        }

        $destination = isset($packages[0]['destination']) && is_array($packages[0]['destination']) ? $packages[0]['destination'] : array();
        if (empty($destination)) {
            return '';
        }

        if (! method_exists(WC(), 'countries') || ! WC()->countries) {
            return '';
        }

        $formatted = WC()->countries->get_formatted_address($destination, ', ');
        if (! $formatted) {
            return '';
        }

        return wp_kses_post($formatted);
    }

    private static function render_sidebar_totals_table()
    {
        if (! function_exists('WC') || ! WC()->cart) {
            return;
        }
    ?>
        <table class="shop_table shop_table_responsive sp-wsv-sidebar-totals-table" cellspacing="0">
            <tbody>
                <tr class="cart-subtotal">
                    <th><?php echo esc_html__('Subtotal', 'superplus-for-woocommerce'); ?></th>
                    <td><?php wc_cart_totals_subtotal_html(); ?></td>
                </tr>

                <?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
                    <tr class="cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
                        <th><?php wc_cart_totals_coupon_label($coupon); ?></th>
                        <td><?php wc_cart_totals_coupon_html($coupon); ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
                    <?php $rate = self::get_first_shipping_rate(); ?>
                    <tr class="woocommerce-shipping-totals shipping">
                        <th><?php echo esc_html__('Envío', 'superplus-for-woocommerce'); ?></th>
                        <td>
                            <?php
                            if ($rate) {
                                echo wp_kses_post(wc_cart_totals_shipping_method_label($rate));
                            } else {
                                echo esc_html__('Calculando…', 'superplus-for-woocommerce');
                            }
                            ?>
                            <?php $dest = self::get_first_shipping_destination_html(); ?>
                            <?php if ($dest !== '') : ?>
                                <div class="sp-wsv-shipping-destination"><?php echo wp_kses_post($dest); ?></div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach (WC()->cart->get_fees() as $fee) : ?>
                    <tr class="fee">
                        <th><?php echo esc_html($fee->name); ?></th>
                        <td><?php wc_cart_totals_fee_html($fee); ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php
                if (wc_tax_enabled() && ! WC()->cart->display_prices_including_tax()) {
                    if ('itemized' === get_option('woocommerce_tax_total_display')) {
                        foreach (WC()->cart->get_tax_totals() as $code => $tax) {
                ?>
                            <tr class="tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
                                <th><?php echo esc_html($tax->label); ?></th>
                                <td><?php echo wp_kses_post($tax->formatted_amount); ?></td>
                            </tr>
                        <?php
                        }
                    } else {
                        ?>
                        <tr class="tax-total">
                            <th><?php echo esc_html(WC()->countries->tax_or_vat()); ?></th>
                            <td><?php wc_cart_totals_taxes_total_html(); ?></td>
                        </tr>
                <?php
                    }
                }
                ?>

                <tr class="order-total">
                    <th><?php echo esc_html__('Total', 'superplus-for-woocommerce'); ?></th>
                    <td><?php wc_cart_totals_order_total_html(); ?></td>
                </tr>
            </tbody>
        </table>
<?php
    }
}
