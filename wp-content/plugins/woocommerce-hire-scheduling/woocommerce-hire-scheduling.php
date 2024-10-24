<?php
/**
 * Plugin Name: WooCommerce Hire Scheduling
 * Description: Adds a hire scheduling system to WooCommerce products with dynamic pricing based on the hire duration.
 * Version: 1.0.0
 * Author: Mohibbulla Munshi
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Enqueue necessary scripts for the date picker.
add_action('wp_enqueue_scripts', 'whs_enqueue_scripts');
function whs_enqueue_scripts() {
    if (is_product()) {
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
        wp_enqueue_script('whs-hire-scheduler', plugins_url('/assets/js/hire-scheduler.js', __FILE__), array('jquery', 'jquery-ui-datepicker'), null, true);
    }
}

// Add custom hire fields to the product page.
add_action('woocommerce_before_add_to_cart_button', 'whs_add_hire_fields');
function whs_add_hire_fields() {
    echo '<div class="hire-scheduler">';
    echo '<label for="hire_start_date">Start Date: </label>';
    echo '<input type="text" id="hire_start_date" name="hire_start_date" required><br>';
    echo '<label for="hire_end_date">End Date: </label>';
    echo '<input type="text" id="hire_end_date" name="hire_end_date" required>';
    echo '</div>';
}

// Save hire dates to the cart.
add_filter('woocommerce_add_cart_item_data', 'whs_add_cart_item_data', 10, 2);
function whs_add_cart_item_data($cart_item_data, $product_id) {
    if (isset($_POST['hire_start_date']) && isset($_POST['hire_end_date'])) {
        $cart_item_data['hire_start_date'] = sanitize_text_field($_POST['hire_start_date']);
        $cart_item_data['hire_end_date'] = sanitize_text_field($_POST['hire_end_date']);
        $cart_item_data['unique_key'] = md5(microtime().rand()); // Ensure unique cart item.
    }
    return $cart_item_data;
}

// Display hire dates in the cart and checkout.
add_filter('woocommerce_get_item_data', 'whs_display_hire_dates_in_cart', 10, 2);
function whs_display_hire_dates_in_cart($item_data, $cart_item) {
    if (isset($cart_item['hire_start_date'])) {
        $item_data[] = array(
            'name' => 'Hire Start Date',
            'value' => $cart_item['hire_start_date'],
        );
    }
    if (isset($cart_item['hire_end_date'])) {
        $item_data[] = array(
            'name' => 'Hire End Date',
            'value' => $cart_item['hire_end_date'],
        );
    }
    return $item_data;
}

// Add dynamic pricing based on hire duration.
add_action('woocommerce_before_calculate_totals', 'whs_apply_dynamic_pricing');
function whs_apply_dynamic_pricing($cart) {
    foreach ($cart->get_cart() as $cart_item) {
        if (isset($cart_item['hire_start_date']) && isset($cart_item['hire_end_date'])) {
            $start_date = strtotime($cart_item['hire_start_date']);
            $end_date = strtotime($cart_item['hire_end_date']);
            $days_hired = ($end_date - $start_date) / (60 * 60 * 24);
            $base_price = $cart_item['data']->get_price();
            $new_price = $base_price * $days_hired; // Dynamic pricing calculation.
            $cart_item['data']->set_price($new_price);
        }
    }
}
