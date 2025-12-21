<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package AtlasReturns\Tests
 */

// Define test constants.
define( 'ATLR_TESTING', true );
define( 'ATLR_VERSION', '2.0.0' );
define( 'ATLR_PLUGIN_FILE', dirname( __DIR__ ) . '/atlas-returns-for-woocommerce.php' );
define( 'ATLR_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
define( 'ATLR_PLUGIN_URL', 'http://example.com/wp-content/plugins/atlas-returns-for-woocommerce/' );
define( 'ATLR_PLUGIN_BASENAME', 'atlas-returns-for-woocommerce/atlas-returns-for-woocommerce.php' );
define( 'ATLR_DEV_PRO', false );
define( 'ABSPATH', dirname( __DIR__ ) . '/../../../../' );

// Load Composer autoloader.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Mock WordPress functions for unit tests.
if ( ! function_exists( 'esc_html__' ) ) {
    function esc_html__( $text, $domain = 'default' ) {
        return $text;
    }
}

if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = 'default' ) {
        return $text;
    }
}

if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }
}

if ( ! function_exists( 'wp_kses' ) ) {
    function wp_kses( $string, $allowed_html, $allowed_protocols = array() ) {
        return $string;
    }
}

if ( ! function_exists( 'get_option' ) ) {
    function get_option( $option, $default = false ) {
        $options = array(
            'atlr_shipping_cost'         => 2.00,
            'atlr_cod_fee'               => 1.00,
            'atlr_coupon_validity_days'  => 180,
            'atlr_default_payment_method' => 'cod',
        );
        return isset( $options[ $option ] ) ? $options[ $option ] : $default;
    }
}

if ( ! function_exists( 'apply_filters' ) ) {
    function apply_filters( $tag, $value, ...$args ) {
        return $value;
    }
}

if ( ! function_exists( 'do_action' ) ) {
    function do_action( $tag, ...$args ) {
        // No-op for tests.
    }
}

if ( ! function_exists( 'add_action' ) ) {
    function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
        // No-op for tests.
    }
}

if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
        // No-op for tests.
    }
}

if ( ! function_exists( 'current_user_can' ) ) {
    function current_user_can( $capability ) {
        return true;
    }
}

if ( ! function_exists( 'is_admin' ) ) {
    function is_admin() {
        return true;
    }
}

if ( ! function_exists( 'wp_create_nonce' ) ) {
    function wp_create_nonce( $action = -1 ) {
        return 'test_nonce_' . $action;
    }
}

if ( ! function_exists( 'check_ajax_referer' ) ) {
    function check_ajax_referer( $action = -1, $query_arg = false, $die = true ) {
        return true;
    }
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field( $str ) {
        return trim( strip_tags( $str ) );
    }
}

if ( ! function_exists( 'absint' ) ) {
    function absint( $maybeint ) {
        return abs( (int) $maybeint );
    }
}

if ( ! function_exists( 'wc_get_order' ) ) {
    function wc_get_order( $order_id ) {
        return null;
    }
}

if ( ! function_exists( 'wc_get_product_id_by_sku' ) ) {
    function wc_get_product_id_by_sku( $sku ) {
        return 0;
    }
}

// Load test case base classes.
require_once __DIR__ . '/TestCase.php';
