<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SP_WSV_Module_Manager {

    private $wsv_modules = array();
    private $option_key = 'sp_wsv_active_modules';
    private $extenders = array();

    public function register( SP_WSV_Module $wsv_module ) {
        $id = sanitize_key( $wsv_module->get_id() );

        if ( isset( $this->modules[ $id ] ) ) {
            $existing = $this->modules[ $id ];
            $existing_p = method_exists( $existing, 'get_override_priority' ) ? (int) $existing->get_override_priority() : 10;
            $incoming_p = method_exists( $wsv_module, 'get_override_priority' ) ? (int) $wsv_module->get_override_priority() : 10;

            if ( $incoming_p < $existing_p ) {
                return $existing;
            }
        }

        $this->modules[ $id ] = $wsv_module;
        return $wsv_module;
    }

    public function register_class( $class, $file = '', $constructor_args = array() ) {
        $class = (string) $class;
        $file = (string) $file;
        $constructor_args = is_array( $constructor_args ) ? $constructor_args : array();

        if ( ! class_exists( $class, false ) && ! empty( $file ) ) {
            require_once $file;
        }

        if ( ! class_exists( $class ) ) {
            return false;
        }

        $wsv_module = null;
        if ( empty( $constructor_args ) ) {
            $wsv_module = new $class();
        } else {
            $ref = new ReflectionClass( $class );
            $wsv_module = $ref->newInstanceArgs( $constructor_args );
        }

        if ( ! ( $wsv_module instanceof SP_WSV_Module ) ) {
            return false;
        }

        $this->register( $wsv_module );
        return $wsv_module;
    }

    public function get( $wsv_module_id ) {
        $wsv_module_id = sanitize_key( $wsv_module_id );
        return isset( $this->modules[ $wsv_module_id ] ) ? $this->modules[ $wsv_module_id ] : null;
    }

    public function extend( $wsv_module_id, $callback, $priority = 10 ) {
        $wsv_module_id = sanitize_key( $wsv_module_id );
        $priority = (int) $priority;

        if ( ! isset( $this->extenders[ $wsv_module_id ] ) ) {
            $this->extenders[ $wsv_module_id ] = array();
        }

        $this->extenders[ $wsv_module_id ][] = array(
            'priority' => $priority,
            'callback' => $callback,
        );
    }

    public function apply_extensions() {
        foreach ( $this->extenders as $wsv_module_id => $extenders ) {
            if ( ! isset( $this->modules[ $wsv_module_id ] ) ) {
                continue;
            }

            usort( $extenders, function( $a, $b ) {
                $ap = isset( $a['priority'] ) ? (int) $a['priority'] : 10;
                $bp = isset( $b['priority'] ) ? (int) $b['priority'] : 10;
                if ( $ap === $bp ) return 0;
                return ( $ap < $bp ) ? -1 : 1;
            } );

            $wsv_module = $this->modules[ $wsv_module_id ];

            foreach ( $extenders as $ext ) {
                if ( empty( $ext['callback'] ) || ! is_callable( $ext['callback'] ) ) {
                    continue;
                }

                $result = call_user_func( $ext['callback'], $wsv_module, $this );
                if ( $result instanceof SP_WSV_Module ) {
                    $wsv_module = $result;
                }
            }

            $wsv_module = apply_filters( 'sp_wsv_module_instance', $wsv_module, $wsv_module_id, $this );
            $wsv_module = apply_filters( 'sp_wsv_module_instance_' . $wsv_module_id, $wsv_module, $this );

            if ( $wsv_module instanceof SP_WSV_Module ) {
                $this->modules[ $wsv_module_id ] = $wsv_module;
            }
        }

        foreach ( $this->modules as $wsv_module_id => $wsv_module ) {
            do_action( 'sp_wsv_module_ready', $wsv_module, $wsv_module_id, $this );
        }
    }

    public function get_modules() {
        return $this->modules;
    }

    public function is_active( $wsv_module_id ) {
        $active = (array) get_option( $this->option_key, array() );
        return ! empty( $active[ $wsv_module_id ] );
    }

    public function set_active( $wsv_module_id, $active ) {
        $active_map = (array) get_option( $this->option_key, array() );
        $active_map[ $wsv_module_id ] = (bool) $active;
        update_option( $this->option_key, $active_map, false );
    }

    public function boot_active_modules() {
        foreach ( $this->modules as $wsv_module ) {
            if ( $wsv_module->is_active() ) {
                $wsv_module->boot();
            }
        }
    }
}
