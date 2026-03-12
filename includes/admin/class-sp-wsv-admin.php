<?php
if (! defined('ABSPATH')) {
    exit;
}

class SP_WSV_Admin
{

    /** @var SP_WSV_Module_Manager */
    private $modules;

    /** @var string */
    private $page_hook = '';

    /** @var string */
    private $redirect_host = '';

    public function __construct(SP_WSV_Module_Manager $wsv_modules)
    {
        $this->modules = $wsv_modules;

        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_post_sp_wsv_save_modules', array($this, 'handle_save_modules'));
        // registrar ajax , dissmiss alerta comunidad
        add_action('wp_ajax_sp_wsv_dismiss_comunidad_alert', [$this, 'ajax_dismiss_alert']);
    }
    public function ajax_dismiss_alert()
    {
        check_ajax_referer('sp_wsv_dismiss_alert_nonce', 'nonce');
        update_user_meta(get_current_user_id(), 'sp_wsv_comunidad_dismissed', 1);
        wp_send_json_success();
    }

    private function get_pro_url()
    {
        return class_exists('SP_WSV_Plugin') && method_exists('SP_WSV_Plugin', 'get_pro_url') ? SP_WSV_Plugin::get_pro_url() : 'https://agenciasp.com';
    }

    private function get_settings_tabs()
    {
        $tabs = array();
        $wsv_modules = $this->modules->get_modules();

        foreach ($wsv_modules as $wsv_module) {
            if (! ($wsv_module instanceof SP_WSV_Module)) {
                continue;
            }

            if (! $wsv_module->is_active()) {
                continue;
            }

            $tab_id = sanitize_key($wsv_module->get_settings_tab_id());
            if (empty($tab_id)) {
                continue;
            }

            $title = (string) $wsv_module->get_settings_tab_title();
            if ($title === '') {
                $title = (string) $wsv_module->get_title();
            }

            $tabs[$tab_id] = array(
                'title'    => $title,
                'callback' => array($wsv_module, 'render_settings_tab'),
            );
        }

        return apply_filters('sp_wsv_settings_tabs', $tabs, $this->modules);
    }

    public function register_menu()
    {
        $icon = 'dashicons-store';
        $icon_path = SP_WSV_PATH . 'assets/img/icon-menu.svg';
        if (is_string($icon_path) && file_exists($icon_path)) {
            $icon_svg = file_get_contents($icon_path);
            if (is_string($icon_svg) && $icon_svg !== '') {
                $icon = 'data:image/svg+xml;base64,' . base64_encode($icon_svg);
            }
        }

        $this->page_hook = add_menu_page(
            __('SuperPlus', 'superplus-for-woocommerce'),
            __('SuperPlus', 'superplus-for-woocommerce'),
            'manage_options',
            'sp-wsv',
            array($this, 'render_modules_page'),
            $icon,
            55.4
        );

        add_submenu_page(
            'sp-wsv',
            __('Módulos', 'superplus-for-woocommerce'),
            __('Módulos', 'superplus-for-woocommerce'),
            'manage_options',
            'sp-wsv',
            [$this, 'render_modules_page']
        );

        $tabs = $this->get_settings_tabs();
        if (! empty($tabs)) {
            add_submenu_page(
                'sp-wsv',
                __('Configuración', 'superplus-for-woocommerce'),
                __('Configuración', 'superplus-for-woocommerce'),
                'manage_options',
                'sp-wsv-settings',
                array($this, 'render_settings_page')
            );
        }

        $menu_title = '<span class="sp-wsv-pro-btn">✦ Obtener PRO</span>';
        add_submenu_page(
            'sp-wsv',
            __('Obtener versión PRO', 'superplus-for-woocommerce'),
            $menu_title,
            'manage_options',
            'sp-wsv-get-pro',
            array($this, 'render_get_pro_page')
        );
    }

    public function enqueue_assets($hook_suffix)
    {
        wp_enqueue_style(
            'superplus-for-woocommerce',
            SP_WSV_URL . 'assets/css/superplus-for-woocommerce.css',
            array(),
            SP_WSV_VERSION
        );

        wp_enqueue_script(
            'superplus-for-woocommerce',
            SP_WSV_URL . 'assets/js/superplus-for-woocommerce.js',
            array('jquery'),
            SP_WSV_VERSION,
            true
        );
        wp_localize_script(
            'superplus-for-woocommerce',
            'SP_WSV_Admin',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('sp_wsv_admin'),
                'pro_url'  => $this->get_pro_url(),
            )
        );

        wp_add_inline_style('superplus-for-woocommerce', '
           #adminmenu .wp-submenu a[href*="sp-wsv-get-pro"] {
            margin: 8px 12px;
            padding: 6px 10px !important;
            background: linear-gradient(135deg, #1a1a6e, #b21f7a);
            color: #ffffff !important;
            font-weight: 700;
            border-radius: 6px;
            text-align: center;
            border: none !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            transition: transform 0.2s ease, filter 0.2s ease, box-shadow 0.2s ease;
        }

        #adminmenu .wp-submenu a[href*="sp-wsv-get-pro"]:visited {
            background: linear-gradient(135deg, #1a1a6e, #b21f7a);
            color: #ffffff !important;
        }

        #adminmenu .wp-submenu a[href*="sp-wsv-get-pro"]:hover,
        #adminmenu .wp-submenu a[href*="sp-wsv-get-pro"]:focus {
            filter: brightness(1.1);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            color: #ffffff !important;
            background: linear-gradient(135deg, rgb(26, 26, 110), rgb(178, 31, 122)) !important;
        }

        #adminmenu .wp-submenu li.current a[href*="sp-wsv-get-pro"],
        #adminmenu .wp-submenu li a:hover,
        #adminmenu .wp-submenu li:focus-within {
            background: linear-gradient(135deg, #1a1a6e, #b21f7a) !important;
            color: #ffffff !important;
        }

        ');

        if ((empty($this->page_hook) || $hook_suffix !== $this->page_hook)) {
            return;
        }

        wp_enqueue_style(
            'sp-wsv-admin-modules',
            SP_WSV_URL . 'assets/css/admin-modules.css',
            array(),
            SP_WSV_VERSION
        );

        wp_enqueue_script(
            'sp-wsv-admin-modules',
            SP_WSV_URL . 'assets/js/admin-modules.js',
            array('jquery'),
            SP_WSV_VERSION,
            true
        );
    }

    public function render_modules_page()
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'superplus-for-woocommerce'));
        }

        $wsv_modules = $this->modules->get_modules();

        $notice = '';
        $updated = filter_input(INPUT_GET, 'sp_wsv_updated', FILTER_SANITIZE_NUMBER_INT);
        if ((string) $updated === '1') {
            $notice = __('Cambios guardados.', 'superplus-for-woocommerce');
        }

        include SP_WSV_PATH . 'includes/admin/views/modules-page.php';
    }

    public function handle_save_modules()
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos.', 'superplus-for-woocommerce'));
        }

        check_admin_referer('sp_wsv_save_modules', 'sp_wsv_nonce');

        $posted_active = array();
        $modules = filter_input(INPUT_POST, 'modules', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $modules = is_array($modules) ? (array) wp_unslash($modules) : array();

        foreach ($modules as $wsv_module_id => $value) {
            $clean_id = sanitize_key($wsv_module_id);
            $posted_active[$clean_id] = ((string) $value === '1');
        }

        foreach ($this->modules->get_modules() as $wsv_module_id => $wsv_module) {
            $wsv_module_id = sanitize_key($wsv_module_id);

            // Locked/placeholder modules cannot be activated from CORE.
            $toggleable = (bool) $wsv_module->is_toggleable();
            if (! $toggleable) {
                $this->modules->set_active($wsv_module_id, false);
                continue;
            }

            $wsv_is_active = ! empty($posted_active[$wsv_module_id]);
            $this->modules->set_active($wsv_module_id, $wsv_is_active);
        }

        wp_safe_redirect(admin_url('admin.php?page=sp-wsv&sp_wsv_updated=1'));
        exit;
    }


    public function render_settings_page()
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'superplus-for-woocommerce'));
        }

        $tabs = $this->get_settings_tabs();

        if (empty($tabs)) {
            wp_die(esc_html__('No hay módulos activos con opciones.', 'superplus-for-woocommerce'));
        }

        $keys = array_keys($tabs);
        $default_tab = reset($keys);
        $requested_tab = sanitize_key((string) filter_input(INPUT_GET, 'tab', FILTER_UNSAFE_RAW));
        if ($requested_tab !== '' && ! isset($tabs[$requested_tab])) {
            wp_safe_redirect(admin_url('admin.php?page=sp-wsv-settings&tab=' . $default_tab));
            exit;
        }
        $active_tab = $requested_tab !== '' ? $requested_tab : $default_tab;

        include SP_WSV_PATH . 'includes/admin/views/settings-page.php';
    }

    public function filter_allowed_redirect_hosts($hosts)
    {
        if (is_string($this->redirect_host) && $this->redirect_host !== '') {
            $hosts[] = $this->redirect_host;
        }
        return $hosts;
    }


    public function render_get_pro_page()
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'superplus-for-woocommerce'));
        }

        $url = $this->get_pro_url();
        if ($url !== '') {
            $url = esc_url_raw($url);
            if ($url !== '') {
                $host = wp_parse_url($url, PHP_URL_HOST);
                if (is_string($host) && $host !== '') {
                    $this->redirect_host = $host;
                    add_filter('allowed_redirect_hosts', array($this, 'filter_allowed_redirect_hosts'));
                }
            }
            wp_safe_redirect($url);
            exit;
        }

        wp_safe_redirect(admin_url('admin.php?page=sp-wsv'));
        exit;
    }
}
