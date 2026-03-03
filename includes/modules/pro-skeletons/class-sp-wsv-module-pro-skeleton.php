<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SP_WSV_Module_Pro_Skeleton extends SP_WSV_Module {

    private $id;
    private $title;
    private $description;
    private $image_url;

    public function __construct( $id, $title, $description, $args = array() ) {
        $this->id = sanitize_key( $id );
        $this->title = (string) $title;
        $this->description = (string) $description;
        $this->image_url = isset( $args['image_url'] ) ? (string) $args['image_url'] : '';
    }

    public function get_id() { return $this->id; }
    public function get_title() { return $this->title; }
    public function get_description() { return $this->description; }

    public function get_tier() { return 'PRO'; }

    public function is_locked() { return true; }

    public function is_toggleable() { return false; }

    public function get_image_url() {
        return ! empty( $this->image_url ) ? $this->image_url : parent::get_image_url();
    }

    public function boot() {}
}
