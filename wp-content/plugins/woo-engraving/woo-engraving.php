<?php
/**
 * Plugin Name: Woo Engraving
 * Description: Adds an Engraving Text field to products, cart, and orders.
 * Author: Awandha
 * Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WooEngraving {
    public function __construct() {
        // 1. Add field on product page
        add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'add_engraving_field' ] );

        // 2. Validate
        add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'validate_engraving_field' ], 10, 2 );

        // 3. Save to cart
        add_filter( 'woocommerce_add_cart_item_data', [ $this, 'save_engraving_to_cart' ], 10, 2 );

        // 4. Display in cart/checkout
        add_filter( 'woocommerce_get_item_data', [ $this, 'display_engraving_in_cart' ], 10, 2 );

        // 5. Save to order
        add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'save_engraving_to_order' ], 10, 4 );

        // 6. Show in admin order
        add_action( 'woocommerce_after_order_itemmeta', [ $this, 'display_engraving_in_admin' ], 10, 3 );

        // Add engraving cost
        add_action( 'woocommerce_cart_calculate_fees', [ $this, 'add_engraving_fee' ] );

        // Register settings
        add_filter( 'woocommerce_get_settings_products', [ $this, 'add_settings' ], 10, 2 );
    }

    // 1. Product page input
    public function add_engraving_field() {
        echo '<div class="engraving-field">
                <label for="engraving_text">Engraving Text:</label>
                <input type="text" id="engraving_text" name="engraving_text" maxlength="50" placeholder="Enter text..." />
              </div>';
    }

    // 2. Validation
    public function validate_engraving_field( $passed, $product_id ) {
        if ( isset( $_POST['engraving_text'] ) && strlen( $_POST['engraving_text'] ) > 50 ) {
            wc_add_notice( 'Engraving text must be less than 50 characters.', 'error' );
            return false;
        }
        return $passed;
    }

    // 3. Save to cart
    public function save_engraving_to_cart( $cart_item_data, $product_id ) {
        if ( isset( $_POST['engraving_text'] ) && ! empty( $_POST['engraving_text'] ) ) {
            $cart_item_data['engraving_text'] = sanitize_text_field( $_POST['engraving_text'] );
        }
        return $cart_item_data;
    }

    // 4. Display in cart/checkout
    public function display_engraving_in_cart( $item_data, $cart_item ) {
        if ( isset( $cart_item['engraving_text'] ) ) {
            $item_data[] = [
                'key'   => 'Engraving',
                'value' => $cart_item['engraving_text']
            ];
        }
        return $item_data;
    }

    // 5. Save to order
    public function save_engraving_to_order( $item, $cart_item_key, $values, $order ) {
        if ( isset( $values['engraving_text'] ) ) {
            $item->add_meta_data( 'Engraving', $values['engraving_text'], true );
        }
    }

    // 6. Show in admin order
    public function display_engraving_in_admin( $item_id, $item, $product ) {
        $engraving = $item->get_meta( 'Engraving' );
        if ( $engraving ) {
            echo '<p><strong>Engraving:</strong> ' . esc_html( $engraving ) . '</p>';
        }
    }

    // 7. Add engraving fee per item
    public function add_engraving_fee( $cart ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
        }

        $engraving_fee_per_item = get_option( 'engraving_fee_amount', 10000 ); // default Rp10,000
        $engraving_fee = 0;

        foreach ( $cart->get_cart() as $cart_item ) {
            if ( isset( $cart_item['engraving_text'] ) && ! empty( $cart_item['engraving_text'] ) ) {
                $engraving_fee += $engraving_fee_per_item * $cart_item['quantity'];
            }
        }

        if ( $engraving_fee > 0 ) {
            $cart->add_fee( __( 'Engraving Fee', 'woo-engraving' ), $engraving_fee );
        }
    }

    // 8. Add engraving fee setting
    public function add_settings( $settings, $current_section ) {
        if ( $current_section === '' ) { // General section
            $custom_settings = array();

            $custom_settings[] = array(
                'name' => __( 'Engraving Fee', 'woo-engraving' ),
                'type' => 'title',
                'desc' => __( 'Settings for engraving customization.', 'woo-engraving' ),
                'id'   => 'engraving_options',
            );

            $custom_settings[] = array(
                'name'     => __( 'Engraving Fee Amount', 'woo-engraving' ),
                'id'       => 'engraving_fee_amount',
                'type'     => 'number',
                'desc'     => __( 'Fee per item when engraving is added (in Rp).', 'woo-engraving' ),
                'default'  => '10000',
                'desc_tip' => true,
            );

            $custom_settings[] = array(
                'type' => 'sectionend',
                'id'   => 'engraving_options',
            );

            return array_merge( $settings, $custom_settings );
        }

        return $settings;
    }
}

new WooEngraving();
