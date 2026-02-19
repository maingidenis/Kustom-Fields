<?php
/**
 * Plugin Name: Kustom Fields
 * Description: Adds Company Name + Role fields before billing address when cart contains products from a chosen category.
 * Version: 1.2.0
 * Author: Denis Maingi
 * Requires Plugins: woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'KUSTOM_FIELDS_VERSION', '1.2.0' );
define( 'KUSTOM_FIELDS_CATEGORY', 'education-provider' );

add_action( 'init', function() {
    if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) return;

    woocommerce_register_additional_checkout_field( array(
        'id'       => 'kustom/company_name',
        'label'    => __( 'Company Name', 'kustom-fields' ),
        'location' => 'billing',
        'type'     => 'text',
        'required' => false,
        'priority' => 5,
    ) );

    woocommerce_register_additional_checkout_field( array(
        'id'       => 'kustom/role',
        'label'    => __( 'Role / Position', 'kustom-fields' ),
        'location' => 'billing',
        'type'     => 'text',
        'required' => false,
        'priority' => 6,
    ) );
} );

add_filter( 'woocommerce_checkout_fields', function( $fields ) {
    if ( empty( $fields['billing']['kustom_company_name'] ) ) {
        $fields['billing']['kustom_company_name'] = array(
            'type'     => 'text',
            'label'    => __( 'Company Name', 'kustom-fields' ),
            'required' => false,
            'class'    => array( 'form-row-wide' ),
            'priority' => 5,
        );
    }
    if ( empty( $fields['billing']['kustom_role'] ) ) {
        $fields['billing']['kustom_role'] = array(
            'type'     => 'text',
            'label'    => __( 'Role / Position', 'kustom-fields' ),
            'required' => false,
            'class'    => array( 'form-row-wide' ),
            'priority' => 6,
        );
    }
    return $fields;
} );

add_action( 'wp_enqueue_scripts', function() {
    if ( ! is_checkout() ) return;
    wp_enqueue_script(
        'kustom-fields-js',
        plugin_dir_url( __FILE__ ) . 'kustom-fields.js',
        array( 'jquery' ),
        KUSTOM_FIELDS_VERSION,
        true
    );
    wp_localize_script( 'kustom-fields-js', 'KustomFields', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'kustom_fields_nonce' ),
        'category' => KUSTOM_FIELDS_CATEGORY,
    ) );
} );

add_action( 'wp_ajax_kustom_check_cart_category',        'kustom_check_cart_category' );
add_action( 'wp_ajax_nopriv_kustom_check_cart_category', 'kustom_check_cart_category' );

function kustom_check_cart_category() {
    if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'kustom_fields_nonce' ) ) {
        wp_send_json_error( array( 'message' => 'Invalid nonce' ), 403 );
    }
    $category = sanitize_text_field( wp_unslash( $_POST['category'] ?? KUSTOM_FIELDS_CATEGORY ) );
    if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
        wp_send_json_success( array( 'has_category' => false ) );
    }
    $has = kustom_cart_has_category( $category );
    wp_send_json_success( array( 'has_category' => $has ) );
}

function kustom_cart_has_category( $category ) {
    foreach ( WC()->cart->get_cart() as $item ) {
        $pid = $item['product_id'] ?? 0;
        if ( $pid && has_term( $category, 'product_cat', $pid ) ) {
            return true;
        }
    }
    return false;
}

add_action( 'woocommerce_checkout_process', function() {
    if ( ! function_exists( 'WC' ) || ! WC()->cart ) return;
    if ( ! kustom_cart_has_category( KUSTOM_FIELDS_CATEGORY ) ) return;

    $company = trim( sanitize_text_field( wp_unslash( $_POST['kustom/company_name'] ?? $_POST['kustom_company_name'] ?? '' ) ) );
    $role    = trim( sanitize_text_field( wp_unslash( $_POST['kustom/role'] ?? $_POST['kustom_role'] ?? '' ) ) );

    if ( empty( $company ) ) wc_add_notice( __( 'Please enter your Company Name.', 'kustom-fields' ), 'error' );
    if ( empty( $role ) )    wc_add_notice( __( 'Please enter your Role / Position.', 'kustom-fields' ), 'error' );
} );

add_action( 'woocommerce_checkout_create_order', function( $order, $data ) {
    $company = sanitize_text_field( wp_unslash( $_POST['kustom/company_name'] ?? $_POST['kustom_company_name'] ?? '' ) );
    $role    = sanitize_text_field( wp_unslash( $_POST['kustom/role'] ?? $_POST['kustom_role'] ?? '' ) );
    if ( $company ) $order->update_meta_data( 'Company Name', $company );
    if ( $role )    $order->update_meta_data( 'Role / Position', $role );
}, 10, 2 );
