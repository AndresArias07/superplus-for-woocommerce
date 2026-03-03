<?php
if ( ! defined( 'ABSPATH' ) ) exit;

abstract class SP_WSV_Module {

    abstract public function get_id();
    abstract public function get_title();
    abstract public function get_description();

    public function get_tier() {
        return 'FREE';
    }

    public function is_locked() {
        return false;
    }

    public function is_toggleable() {
        return ! $this->is_locked();
    }

    public function get_image_url() {
        return SP_WSV_URL . 'assets/img/module-default.svg';
    }

    public function is_active() {
        if ( ! $this->is_toggleable() ) {
            return false;
        }
        return sp_wsv()->modules->is_active( $this->get_id() );
    }

    public function get_override_priority() {
        return 10;
    }

    public function get_settings_tab_id() {
        return '';
    }

    public function get_settings_tab_title() {
        return '';
    }

    public function render_settings_tab() {
        echo '';
    }

    abstract public function boot();
}
