<?php
/**
 * Product Setup Utility
 * 
 * Ensures the 4 core Variable Products (1, 3, 6, 12 Months) exist
 * with their Device variations (1-4).
 * 
 * @package Quebec_IPTV
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class IPTV_Product_Setup
{
    private $duration_map = array(
        '1_month' => array('title' => '1 Month Subscription', 'months' => 1),
        '3_months' => array('title' => '3 Months Subscription', 'months' => 3),
        '6_months' => array('title' => '6 Months Subscription', 'months' => 6),
        '12_months' => array('title' => '12 Months Subscription', 'months' => 12),
    );

    private $devices_attr = 'Devices';
    private $device_terms = array('1', '2', '3', '4');

    public function __construct()
    {
        add_action('admin_init', array($this, 'check_and_create_products'));
    }

    /**
     * Check if products exist, if not create them.
     * Runs on admin_init but only if a specific flag is set or missing.
     */
    public function check_and_create_products()
    {
        // Only run if specifically requested via URL to avoid overhead
        // URL: /wp-admin/?setup_iptv_products=1
        if (!isset($_GET['setup_iptv_products']) || $_GET['setup_iptv_products'] !== '1') {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        // Ensure attribute exists
        $this->create_attributes();

        // Create Products
        foreach ($this->duration_map as $key => $data) {
            $this->create_or_update_product($key, $data);
        }

        // Create Trial
        $this->create_or_update_trial_product();

        // Add admin notice
        add_action('admin_notices', function () {
            echo '<div class="notice notice-success is-dismissible"><p>✅ IPTV Products & Variations Checked/Created.</p></div>';
        });
    }

    private function create_attributes()
    {
        $attribute_slug = 'pa_' . strtolower($this->devices_attr);

        // Register the attribute if it doesn't exist
        $attribute_id = wc_attribute_taxonomy_id_by_name($this->devices_attr);
        if (!$attribute_id) {
            $attribute_id = wc_create_attribute(array(
                'name' => $this->devices_attr,
                'slug' => strtolower($this->devices_attr),
                'type' => 'select',
                'order_by' => 'menu_order',
                'has_archives' => false,
            ));
        }

        // Register terms
        foreach ($this->device_terms as $term) {
            if (!term_exists($term, $attribute_slug)) {
                wp_insert_term($term, $attribute_slug);
            }
        }
    }

    private function create_or_update_product($key, $data)
    {
        // Check if product exists by SKU or Title
        $product_id = wc_get_product_id_by_sku($key);

        if (!$product_id) {
            // Create new Variable Product
            $product = new WC_Product_Variable();
            $product->set_name($data['title']);
            $product->set_sku($key);
            $product->set_status('publish');
            $product->set_description("Access to {$data['months']} month(s) of premium IPTV streaming.");
        } else {
            $product = wc_get_product($product_id);
            if (!$product->is_type('variable')) {
                // Convert to variable if needed, or handle error
                return;
            }
        }

        // Attributes
        $attribute = new WC_Product_Attribute();
        $attribute->set_id(wc_attribute_taxonomy_id_by_name($this->devices_attr));
        $attribute->set_name('pa_' . strtolower($this->devices_attr));
        $attribute->set_options($this->device_terms);
        $attribute->set_position(0);
        $attribute->set_visible(true);
        $attribute->set_variation(true);

        $product->set_attributes(array($attribute));
        $product_id = $product->save();

        // Variations
        $this->create_variations($product_id, $key);
    }

    private function create_variations($product_id, $duration_key)
    {
        // Get currency settings for prices
        // We rely on the existing class IPTV_Currency_Settings if available, or fallback
        if (!class_exists('IPTV_Currency_Settings')) {
            return;
        }

        $all_prices = IPTV_Currency_Settings::get_prices(); // returns ['1_month']['1_device']['usd' => ...]

        $product = wc_get_product($product_id);
        $data_store = $product->get_data_store(); // WC_Product_Data_Store_CPT

        // Existing variations
        $existing_variations = $data_store->get_child_ids($product_id);

        foreach ($this->device_terms as $term) {
            $device_key = $term . '_device' . ($term > 1 ? 's' : ''); // 1_device, 2_devices

            // Calculate regular and sale price
            $usd_price = isset($all_prices[$duration_key][$device_key]['usd'])
                ? $all_prices[$duration_key][$device_key]['usd']
                : 0;

            // Check if variation exists with this attribute
            $variation_id = $this->find_variation_by_attribute($product_id, $term);

            if (!$variation_id) {
                $variation = new WC_Product_Variation();
                $variation->set_parent_id($product_id);
                $variation->set_attributes(array('pa_' . strtolower($this->devices_attr) => $term));
                $variation->set_sku($duration_key . '-' . $term . '-dev');
            } else {
                $variation = wc_get_product($variation_id);
            }

            $variation->set_regular_price($usd_price);
            $variation->set_price($usd_price);
            $variation->set_status('publish');
            $variation->save();
        }
    }

    /**
     * Create or Update the Trial Product
     */
    private function create_or_update_trial_product()
    {
        $sku = 'trial_24h';
        $title = '24h Trial';

        $product_id = wc_get_product_id_by_sku($sku);

        if (!$product_id) {
            $product = new WC_Product_Simple(); // Simple product, no variations
            $product->set_name($title);
            $product->set_sku($sku);
            $product->set_status('publish');
            $product->set_description("Test our service for 24 hours.");
        } else {
            $product = wc_get_product($product_id);
            if (!$product->is_type('simple')) {
                return;
            }
        }

        // Set Cross Pricing (Fake Sale)
        // Example: Regular 5 EUR, Sale 0 EUR (Free trial) or just cheap
        // The user asked for "cross pricing" and "trial". 
        // Let's set a default placeholder logic, e.g., Regular 10, Sale 0.
        $product->set_regular_price('10'); // Crossed out
        $product->set_sale_price('0');      // Actual price
        $product->set_price('0');

        $product->save();
    }

    private function find_variation_by_attribute($product_id, $term_slug)
    {
        $product = wc_get_product($product_id);
        $children = $product->get_children();

        foreach ($children as $child_id) {
            $variation = wc_get_product($child_id);
            $attributes = $variation->get_attributes();
            $attr_key = 'pa_' . strtolower($this->devices_attr);

            if (isset($attributes[$attr_key]) && $attributes[$attr_key] == $term_slug) {
                return $child_id;
            }
        }
        return false;
    }
}

new IPTV_Product_Setup();
