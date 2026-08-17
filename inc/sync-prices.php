<?php
/**
 * Sync Variation Prices from Main Site to Subsites
 * 
 * This utility helps ensure subsite products have correct variation prices
 * and attributes by copying them from the main site's products.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get conversion rate for subsite currency
 */
function iptv_get_subsite_rate()
{
    $blog_id = get_current_blog_id();
    $details = get_blog_details($blog_id);
    if (!$details)
        return 1;

    $path = trim($details->path, '/');
    $parts = explode('/', $path);
    $site_slug = end($parts);

    // Multisite subsite slug => its currency. The Nordic subsites are gone;
    // 'ca' is here so a Quebec subsite, if one is ever spun up, bills in CAD.
    $currency_map = array(
        'ca' => 'cad',
    );

    $currency = isset($currency_map[$site_slug]) ? $currency_map[$site_slug] : 'usd';

    if ($currency === 'usd')
        return 1;

    // Get rates from main site
    switch_to_blog(1);
    $rates = get_option('iptv_conversion_rates', array());
    restore_current_blog();

    // Default rates
    $default_rates = array(
        'cad' => 1.37,
    );

    $rate = isset($rates[$currency]) ? floatval($rates[$currency]) :
        (isset($default_rates[$currency]) ? $default_rates[$currency] : 1);

    return array('rate' => $rate, 'currency' => $currency);
}

/**
 * Apply psychological rounding
 */
function iptv_apply_rounding($price, $currency)
{
    if ($currency === 'usd' || $currency === 'eur') {
        return ceil($price) - 0.01;
    } else {
        return ceil($price / 10) * 10 - 1;
    }
}

/**
 * Sync variation prices for a product from main site
 * 
 * @param int $product_id The subsite product ID
 * @return array Result with status and message
 */
function iptv_sync_variations_from_main_site($product_id)
{
    if (!function_exists('is_multisite') || !is_multisite()) {
        return array('success' => false, 'message' => 'Not a multisite installation');
    }

    $current_blog_id = get_current_blog_id();
    if ($current_blog_id === 1) {
        return array('success' => false, 'message' => 'Already on main site');
    }

    $product = wc_get_product($product_id);
    if (!$product || !$product->is_type('variable')) {
        return array('success' => false, 'message' => 'Product not found or not variable');
    }

    $product_sku = $product->get_sku();
    if (empty($product_sku)) {
        return array('success' => false, 'message' => 'Product has no SKU');
    }

    // Get conversion rate for this subsite
    $rate_info = iptv_get_subsite_rate();
    $rate = $rate_info['rate'];
    $currency = $rate_info['currency'];

    // Switch to main site to get source product
    switch_to_blog(1);

    $main_product_id = wc_get_product_id_by_sku($product_sku);
    $main_product = $main_product_id ? wc_get_product($main_product_id) : null;

    if (!$main_product || !$main_product->is_type('variable')) {
        restore_current_blog();
        return array('success' => false, 'message' => 'Main site product not found');
    }

    // Get all variations from main site with their prices AND attributes
    $main_variations = array();
    $debug_attrs = array(); // Debug: capture attribute info
    foreach ($main_product->get_children() as $child_id) {
        $variation = wc_get_product($child_id);
        if ($variation) {
            $var_sku = $variation->get_sku();
            $attrs = $variation->get_attributes();

            // Debug: Also try getting variation data directly from post meta
            $meta_attrs = array();
            $all_meta = get_post_meta($child_id);
            foreach ($all_meta as $key => $value) {
                if (strpos($key, 'attribute_') === 0) {
                    $meta_attrs[$key] = $value[0];
                }
            }

            $main_variations[$var_sku] = array(
                'regular_price' => $variation->get_regular_price(),
                'sale_price' => $variation->get_sale_price(),
                'attributes' => $attrs,
                'meta_attributes' => $meta_attrs, // Direct from post meta
            );
            $debug_attrs[$var_sku] = array('method' => $attrs, 'meta' => $meta_attrs);
        }
    }

    // Store debug info for display
    set_transient('iptv_sync_debug_attrs', $debug_attrs, 300);

    restore_current_blog();

    // Now update subsite variations
    $updated = 0;
    $subsite_product = wc_get_product($product_id);

    foreach ($subsite_product->get_children() as $child_id) {
        $variation = wc_get_product($child_id);
        if ($variation) {
            $var_sku = $variation->get_sku();

            // Try to match by SKU
            if (isset($main_variations[$var_sku])) {
                $source = $main_variations[$var_sku];

                // Convert USD price to local currency
                $usd_price = floatval($source['regular_price']);
                $local_price = iptv_apply_rounding($usd_price * $rate, $currency);

                $variation->set_regular_price($local_price);

                if (!empty($source['sale_price'])) {
                    $usd_sale = floatval($source['sale_price']);
                    $local_sale = iptv_apply_rounding($usd_sale * $rate, $currency);
                    $variation->set_sale_price($local_sale);
                }

                $variation->save();

                // FIX: Set variation attributes correctly using direct post meta values
                // Main site uses global attributes (attribute_pa_devices), subsite uses local (attribute_devices)
                // Try meta_attributes first (direct from post meta), then fall back to get_attributes() method
                $attrs_to_use = !empty($source['meta_attributes']) ? $source['meta_attributes'] : $source['attributes'];

                if (!empty($attrs_to_use)) {
                    foreach ($attrs_to_use as $attr_key => $attr_value) {
                        // $attr_key from meta is already full key like "attribute_pa_devices"
                        // $attr_key from get_attributes() is just slug like "pa_devices"

                        // Normalize: extract the base attribute name (e.g., "devices")
                        $cleaned_key = preg_replace('/^attribute_/', '', $attr_key); // Remove "attribute_" prefix if present
                        $base_attr = preg_replace('/^pa_/', '', $cleaned_key); // Remove "pa_" prefix

                        // Set the LOCAL attribute format (attribute_devices) - this is what the subsite uses
                        update_post_meta($child_id, 'attribute_' . $base_attr, $attr_value);

                        // Also set the global format (attribute_pa_devices) just in case
                        update_post_meta($child_id, 'attribute_pa_' . $base_attr, $attr_value);
                    }
                }

                $updated++;
            }
        }
    }

    // Clear WooCommerce transients for this product
    wc_delete_product_transients($product_id);

    return array(
        'success' => true,
        'message' => "Synced $updated variations (rate: $rate, currency: $currency)",
        'updated' => $updated
    );
}

/**
 * Sync ALL products on current subsite from main site
 */
function iptv_sync_all_products_from_main_site()
{
    $results = array();

    // Get all variable products on this subsite
    $products = wc_get_products(array(
        'type' => 'variable',
        'limit' => -1,
    ));

    foreach ($products as $product) {
        $result = iptv_sync_variations_from_main_site($product->get_id());
        $results[$product->get_sku()] = $result;
    }

    return $results;
}

// Add admin notice and button for subsites
add_action('admin_notices', function () {
    if (!function_exists('is_multisite') || !is_multisite())
        return;
    if (get_current_blog_id() === 1)
        return;

    // Only show on products page
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'edit-product')
        return;

    // Check if sync was triggered
    if (isset($_GET['iptv_sync_prices']) && $_GET['iptv_sync_prices'] === '1') {
        $results = iptv_sync_all_products_from_main_site();
        $success_count = 0;
        $messages = array();
        foreach ($results as $sku => $result) {
            if ($result['success']) {
                $success_count++;
                $messages[] = "$sku: {$result['message']}";
            }
        }
        echo '<div class="notice notice-success"><p>✅ Synced ' . $success_count . ' products with local currency prices. Details: ' . implode(', ', $messages) . '</p></div>';
        // Don't return - continue to show the sync button below
    }

    // Show sync button
    $url = add_query_arg('iptv_sync_prices', '1');
    echo '<div class="notice notice-info">';
    echo '<p><strong>Subsite Pricing:</strong> Product variation prices need syncing from main site (converts to local currency). ';
    echo '<a href="' . esc_url($url) . '" class="button button-primary" onclick="return confirm(\'Sync all variation prices from main site? This will convert USD to local currency.\');">Sync Prices from Main Site</a></p>';
    echo '</div>';
});

// Also add to individual product edit screen
add_action('woocommerce_product_data_panels', function () {
    global $post;

    if (!function_exists('is_multisite') || !is_multisite())
        return;
    if (get_current_blog_id() === 1)
        return;

    $product = wc_get_product($post->ID);
    if (!$product || !$product->is_type('variable'))
        return;

    // Handle sync action
    if (isset($_GET['iptv_sync_single']) && $_GET['iptv_sync_single'] == $post->ID) {
        $result = iptv_sync_variations_from_main_site($post->ID);
        if ($result['success']) {
            echo '<div class="notice notice-success inline"><p>✅ ' . esc_html($result['message']) . '</p></div>';
        } else {
            echo '<div class="notice notice-error inline"><p>❌ ' . esc_html($result['message']) . '</p></div>';
        }
    }
});

add_action('woocommerce_product_options_pricing', function () {
    global $post;

    if (!function_exists('is_multisite') || !is_multisite())
        return;
    if (get_current_blog_id() === 1)
        return;

    $product = wc_get_product($post->ID);
    if (!$product || !$product->is_type('variable'))
        return;

    $url = add_query_arg('iptv_sync_single', $post->ID);
    echo '<p class="form-field">';
    echo '<a href="' . esc_url($url) . '" class="button" onclick="return confirm(\'Sync variation prices from main site? This will convert USD to local currency.\');">🔄 Sync Prices from Main Site</a>';
    echo '</p>';
});
