<?php
/**
 * Network Content Cloner
 * 
 * Adds a "Clone to Network" button to admin bar for syncing pages across multisite.
 * 
 * @package Quebec_IPTV
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Theme_Network_Cloner
{

    /**
     * Target sub-sites (path slugs)
     */
    private $target_sites = array(
        'ca',
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        // Only run on multisite
        if (!is_multisite()) {
            return;
        }

        // Only run on main site
        if (get_current_blog_id() != 1) {
            return;
        }

        // Add admin bar button
        add_action('admin_bar_menu', array($this, 'add_clone_button'), 100);

        // Add row action to Pages/Posts list
        add_filter('page_row_actions', array($this, 'add_clone_row_action'), 10, 2);
        add_filter('post_row_actions', array($this, 'add_clone_row_action'), 10, 2);

        // Handle clone action
        add_action('admin_init', array($this, 'handle_clone_action'));
        add_action('admin_init', array($this, 'handle_menu_clone_action'));

        // Handle remove action
        add_action('admin_init', array($this, 'handle_remove_action'));
        add_action('admin_init', array($this, 'handle_menu_remove_action'));

        // Admin notices
        add_action('admin_notices', array($this, 'show_clone_notice'));
    }

    /**
     * Get currency code for a subsite slug
     * @param string $site_slug e.g. 'ca'
     * @return string Currency code e.g. 'cad'
     */
    private function get_currency_for_site($site_slug)
    {
        $map = array(
            'ca' => 'cad',  // Canada -> Canadian Dollar
        );
        return isset($map[$site_slug]) ? $map[$site_slug] : 'usd';
    }

    /**
     * Convert a USD price to target currency using IPTV_Currency_Settings rates
     * @param float $usd_price The price in USD
     * @param string $currency_code Target currency (cad)
     * @return float Converted and rounded price
     */
    private function convert_price($usd_price, $currency_code)
    {
        if ($currency_code === 'usd' || empty($usd_price)) {
            return $usd_price;
        }

        // Get conversion rates from options (same as IPTV_Currency_Settings)
        $rates = get_option('iptv_conversion_rates', array());
        $default_rates = array(
            'cad' => 1.37,
        );

        $rate = isset($rates[$currency_code]) ? floatval($rates[$currency_code]) :
            (isset($default_rates[$currency_code]) ? $default_rates[$currency_code] : 1);

        $converted = floatval($usd_price) * $rate;

        // Apply rounding rules (same as IPTV_Currency_Settings::apply_rounding)
        if ($currency_code === 'usd' || $currency_code === 'eur') {
            // Round to .99 for USD/EUR
            return ceil($converted) - 0.01;
        } else {
            // Round to nearest 10 minus 1 for NOK/SEK/DKK/ISK (e.g., 178 -> 179)
            return ceil($converted / 10) * 10 - 1;
        }
    }

    /**
     * Add "Clone to Network" and "Remove from Network" links to row actions in Pages/Posts list
     */
    public function add_clone_row_action($actions, $post)
    {
        // Only super admins can use network actions
        if (!is_super_admin()) {
            return $actions;
        }

        // Clone to Network
        $clone_url = add_query_arg(array(
            'action' => 'clone_to_network',
            'post_id' => $post->ID,
            '_wpnonce' => wp_create_nonce('clone_to_network_' . $post->ID),
        ), admin_url('edit.php'));

        $actions['clone_network'] = sprintf(
            '<a href="%s" style="color:#16a34a;font-weight:600;">🌐 Clone to Network</a>',
            esc_url($clone_url)
        );

        // Remove from Network
        $remove_url = add_query_arg(array(
            'action' => 'remove_from_network',
            'post_id' => $post->ID,
            '_wpnonce' => wp_create_nonce('remove_from_network_' . $post->ID),
        ), admin_url('edit.php'));

        $actions['remove_network'] = sprintf(
            '<a href="%s" style="color:#dc2626;font-weight:600;" onclick="return confirm(\'Are you sure you want to remove this page from ALL sub-sites? This cannot be undone.\');">🗑️ Remove from Network</a>',
            esc_url($remove_url)
        );

        return $actions;
    }

    /**
     * Add "Clone to Network" button to admin bar
     */
    public function add_clone_button($wp_admin_bar)
    {
        // Only show on main site
        if (get_current_blog_id() != 1) {
            return;
        }

        // Only show when editing a page/post
        global $pagenow;

        // Check if we are on post edit page OR nav menus page
        if (!in_array($pagenow, array('post.php', 'post-new.php', 'nav-menus.php'))) {
            return;
        }

        // Case 1: Nav Menus Page
        if ($pagenow === 'nav-menus.php') {
            // Build the clone URL for Menus
            $clone_url = add_query_arg(array(
                'action' => 'clone_menus_to_network',
                '_wpnonce' => wp_create_nonce('clone_menus_to_network'),
            ), admin_url('nav-menus.php'));

            // Build removal URL
            $remove_url = add_query_arg(array(
                'action' => 'remove_menus_from_network',
                '_wpnonce' => wp_create_nonce('remove_menus_from_network'),
            ), admin_url('nav-menus.php'));

            // Add the Clone button
            $wp_admin_bar->add_node(array(
                'id' => 'clone-menus-to-network',
                'title' => '<span class="ab-icon dashicons dashicons-networking" style="font-family:dashicons;font-size:18px;margin-top:4px;"></span> Clone Menus to Network',
                'href' => $clone_url,
                'meta' => array(
                    'title' => 'Clone ALL menus to all sub-sites (SE)',
                    'class' => 'clone-to-network-btn',
                    'onclick' => 'return confirm("Are you sure? This will OVERWRITE menus on all sub-sites with the current English menus.");',
                ),
            ));

            // Add the Remove button
            $wp_admin_bar->add_node(array(
                'id' => 'remove-menus-from-network',
                'title' => '<span class="ab-icon dashicons dashicons-trash" style="font-family:dashicons;font-size:18px;margin-top:4px;"></span> Delete Menus',
                'href' => $remove_url,
                'meta' => array(
                    'title' => 'Delete ALL menus from sub-sites',
                    'class' => 'remove-from-network-btn',
                    'onclick' => 'return confirm("WARNING: This will DELETE all menus from sub-sites that match the main site menu names. This cannot be undone. Are you sure?");',
                ),
            ));

            // Add inline styles (reuse existing style block later)
            add_action('admin_head', function () {
                echo '<style>
                    #wp-admin-bar-clone-menus-to-network > a {
                        background: linear-gradient(135deg, #22c55e, #16a34a) !important;
                        color: white !important;
                    }
                    #wp-admin-bar-clone-menus-to-network > a:hover {
                        background: linear-gradient(135deg, #16a34a, #15803d) !important;
                    }
                    #wp-admin-bar-clone-menus-to-network .ab-icon {
                        color: white !important;
                    }
                    #wp-admin-bar-remove-menus-from-network > a {
                        background: linear-gradient(135deg, #ef4444, #dc2626) !important;
                        color: white !important;
                    }
                    #wp-admin-bar-remove-menus-from-network > a:hover {
                        background: linear-gradient(135deg, #dc2626, #b91c1c) !important;
                    }
                    #wp-admin-bar-remove-menus-from-network .ab-icon {
                        color: white !important;
                    }
                </style>';
            });

            return;
        }

        // Get post ID from URL or global
        $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;

        if (!$post_id) {
            global $post;
            if (isset($post) && $post) {
                $post_id = $post->ID;
            }
        }

        if (!$post_id) {
            return;
        }

        // Only for admins
        if (!current_user_can('manage_options')) {
            return;
        }

        // Build the clone URL
        $clone_url = add_query_arg(array(
            'action' => 'clone_to_network',
            'post_id' => $post_id,
            '_wpnonce' => wp_create_nonce('clone_to_network_' . $post_id),
        ), admin_url('post.php'));

        // Add the button
        $wp_admin_bar->add_node(array(
            'id' => 'clone-to-network',
            'title' => '<span class="ab-icon dashicons dashicons-networking" style="font-family:dashicons;font-size:18px;margin-top:4px;"></span> Clone to Network',
            'href' => $clone_url,
            'meta' => array(
                'title' => 'Clone this page to all sub-sites (SE)',
                'class' => 'clone-to-network-btn',
            ),
        ));

        // Add inline styles
        add_action('admin_head', function () {
            echo '<style>
                #wp-admin-bar-clone-to-network > a {
                    background: linear-gradient(135deg, #22c55e, #16a34a) !important;
                    color: white !important;
                    }
                #wp-admin-bar-clone-to-network > a:hover {
                    background: linear-gradient(135deg, #16a34a, #15803d) !important;
                }
                #wp-admin-bar-clone-to-network .ab-icon {
                    color: white !important;
                }
            </style>';
        });
    }

    /**
     * Handle the clone action
     */
    public function handle_clone_action()
    {
        if (!isset($_GET['action']) || $_GET['action'] !== 'clone_to_network') {
            return;
        }

        if (!isset($_GET['post_id']) || !isset($_GET['_wpnonce'])) {
            return;
        }

        $post_id = intval($_GET['post_id']);

        // Verify nonce
        if (!wp_verify_nonce($_GET['_wpnonce'], 'clone_to_network_' . $post_id)) {
            wp_die('Security check failed');
        }

        // Only super admins
        if (!is_super_admin()) {
            wp_die('Unauthorized - Super Admin required');
        }

        // Get the source post
        $source_post = get_post($post_id);

        if (!$source_post) {
            wp_die('Post not found');
        }

        // Clone to all sub-sites
        $results = $this->clone_to_sites($source_post);

        // Store results for notice
        set_transient('network_clone_results_' . get_current_user_id(), $results, 60);

        // Redirect back to pages list
        wp_safe_redirect(add_query_arg(array(
            'post_type' => $source_post->post_type,
            'cloned' => 'true',
        ), admin_url('edit.php')));
        exit;
    }

    /**
     * Handle the remove from network action
     */
    public function handle_remove_action()
    {
        if (!isset($_GET['action']) || $_GET['action'] !== 'remove_from_network') {
            return;
        }

        if (!isset($_GET['post_id']) || !isset($_GET['_wpnonce'])) {
            return;
        }

        $post_id = intval($_GET['post_id']);

        // Verify nonce
        if (!wp_verify_nonce($_GET['_wpnonce'], 'remove_from_network_' . $post_id)) {
            wp_die('Security check failed');
        }

        // Only super admins
        if (!is_super_admin()) {
            wp_die('Unauthorized - Super Admin required');
        }

        // Get the source post to get its slug
        $source_post = get_post($post_id);

        if (!$source_post) {
            wp_die('Post not found');
        }

        // Remove from all sub-sites
        $results = $this->remove_from_sites($source_post);

        // Store results for notice
        set_transient('network_clone_results_' . get_current_user_id(), $results, 60);

        // Redirect back to pages list
        wp_safe_redirect(add_query_arg(array(
            'post_type' => $source_post->post_type,
            'removed' => 'true',
        ), admin_url('edit.php')));
        exit;
    }

    /**
     * Remove page from all sub-sites
     */
    public function remove_from_sites($source_post)
    {
        $results = array(
            'removed' => array(),
            'not_found' => array(),
            'errors' => array(),
        );

        // Get all sites
        $sites = get_sites(array('number' => 100));

        // Save current blog ID to restore later
        $original_blog_id = get_current_blog_id();

        foreach ($sites as $site) {
            // Skip main site
            if ($site->blog_id == 1) {
                continue;
            }

            // Get site path
            $path = trim($site->path, '/');
            $path_parts = explode('/', $path);
            $site_slug = end($path_parts);

            // Only process target sites
            if (!in_array($site_slug, $this->target_sites)) {
                continue;
            }

            // Switch to sub-site
            switch_to_blog($site->blog_id);

            try {
                // Check if page/post with same slug exists (include post type)
                $existing = get_page_by_path($source_post->post_name, OBJECT, $source_post->post_type);

                // Special handling for Products (check SKU if not found by slug)
                if (!$existing && $source_post->post_type === 'product' && function_exists('wc_get_product_id_by_sku')) {
                    // Try to find by SKU - Switch to main to get SKU
                    switch_to_blog(1);
                    $sku = get_post_meta($source_post->ID, '_sku', true);
                    switch_to_blog($site->blog_id); // Back to subsite

                    if ($sku) {
                        $existing_id = wc_get_product_id_by_sku($sku);
                        if ($existing_id) {
                            $existing = get_post($existing_id);
                        }
                    }
                }

                if ($existing) {
                    // Force Delete the post (bypass trash)
                    $delete_result = wp_delete_post($existing->ID, true);

                    if ($delete_result) {
                        $results['removed'][] = strtoupper($site_slug);
                    } else {
                        $results['errors'][] = strtoupper($site_slug) . ': Failed to delete';
                    }
                } else {
                    $results['not_found'][] = strtoupper($site_slug);
                }
            } catch (Exception $e) {
                $results['errors'][] = strtoupper($site_slug) . ': ' . $e->getMessage();
            }

            // Switch back to original blog (usually 1)
            switch_to_blog($original_blog_id);
        }

        return $results;
    }

    /**
     * Clone post to all sub-sites with automatic translation
     */
    public function clone_to_sites($source_post)
    {
        $results = array(
            'created' => array(),
            'updated' => array(),
            'translated' => array(),
            'errors' => array(),
            'debug' => array(),
        );

        // Get all sites
        $sites = get_sites(array('number' => 100));

        foreach ($sites as $site) {
            // Skip main site
            if ($site->blog_id == 1) {
                continue;
            }

            // Get site path
            $path = trim($site->path, '/');
            $path_parts = explode('/', $path);
            $site_slug = end($path_parts);

            // Only process target sites
            if (!in_array($site_slug, $this->target_sites)) {
                continue;
            }

            // Perform clone
            $clone_result = $this->clone_to_site($source_post, $site->blog_id);

            // Access site slug for array keys
            $slug_upper = strtoupper($site_slug);

            // Collect debug info from this site
            if (isset($clone_result['debug']) && is_array($clone_result['debug'])) {
                $results['debug'][] = "=== Site: $slug_upper ===";
                $results['debug'] = array_merge($results['debug'], $clone_result['debug']);
            }

            if ($clone_result['success']) {
                if ($clone_result['action'] === 'created') {
                    $results['created'][] = $slug_upper;
                } else {
                    $results['updated'][] = $slug_upper;
                }
                if ($clone_result['translated']) {
                    $results['translated'][] = $slug_upper;
                }
            } else {
                $results['errors'][] = $slug_upper . ': ' . $clone_result['message'];
            }
        }

        return $results;
    }

    /**
     * Clone a post to a SINGLE specific sub-site
     * 
     * Content is copied verbatim — the theme no longer translates on the server.
     *
     * @param WP_Post $source_post The post object to clone
     * @param int $target_blog_id The target blog ID
     * @param bool|null $force_translate Kept for call-site compatibility; ignored.
     * @return array Result [success, action, translated, target_id, message]
     */
    public function clone_to_site($source_post, $target_blog_id, $force_translate = null)
    {
        $result = array(
            'success' => false,
            'action' => '',
            'translated' => false,
            'target_id' => 0,
            'message' => ''
        );

        // 1. Prepare Content — copied as-is; translation happens outside WordPress.
        $post_title = $source_post->post_title;
        $post_content = $source_post->post_content;
        $post_excerpt = $source_post->post_excerpt;

        // 2. Switch Blog & Insert/Update
        $original_blog_id = get_current_blog_id();
        switch_to_blog($target_blog_id);

        try {
            // Check for existing
            $expected_post_type = $source_post->post_type;
            $existing = get_page_by_path($source_post->post_name, OBJECT, $expected_post_type);

            // Special handling for Products (SKU check)
            if ($expected_post_type === 'product' && !$existing && function_exists('wc_get_product_id_by_sku')) {
                // Determine SKU from source - complex because we are on target blog now
                // We must switch back briefly or pass data?
                // The most robust way is to fetch meta BEFORE switching or switch back and forth.
                // Let's switch back to get meta.
                restore_current_blog(); // Back to source
                $sku = get_post_meta($source_post->ID, '_sku', true);
                switch_to_blog($target_blog_id); // Back to target

                if ($sku) {
                    $existing_id = wc_get_product_id_by_sku($sku);
                    if ($existing_id) {
                        $existing = get_post($existing_id);
                    }
                }
            }

            if ($existing) {
                // Update
                $update_args = array(
                    'ID' => $existing->ID,
                    'post_title' => $post_title,
                    'post_content' => $post_content,
                    'post_excerpt' => $post_excerpt,
                    'post_status' => $source_post->post_status,
                );

                $update_result = wp_update_post($update_args);

                if (is_wp_error($update_result)) {
                    throw new Exception($update_result->get_error_message());
                }

                $result['action'] = 'updated';
                $result['target_id'] = $existing->ID;
            } else {
                // Create
                $insert_args = array(
                    'post_title' => $post_title,
                    'post_name' => $source_post->post_name,
                    'post_content' => $post_content,
                    'post_excerpt' => $post_excerpt,
                    'post_status' => $source_post->post_status,
                    'post_type' => $source_post->post_type,
                    'post_author' => get_current_user_id(),
                );

                $new_id = wp_insert_post($insert_args);

                if (is_wp_error($new_id)) {
                    throw new Exception($new_id->get_error_message());
                }

                $result['action'] = 'created';
                $result['target_id'] = $new_id;
            }

            // 4. Clone Meta & Product Data
            if ($result['target_id']) {
                // We need to be on TARGET blog for these, but they might internally switch.
                // clone_post_meta usually works by getting from source (needs source blog) to target.
                // Our helper function copy_post_meta needs to run?
                // Wait, copy_post_meta in this class likely assumes both posts are accessible or handles switching?
                // Let's check copy_post_meta implementation later (it was not visible in snippet).
                // Usually it needs to be run while on Target blog, fetching from Source.
                // But wait, the standard logic in this class:

                // Let's look at previous implementation of clone_to_sites:
                // ... switch_to_blog($site->blog_id) ... 
                // ... insert/update ...
                // ... copy_post_meta($source_post->ID, $target_id); ...

                // So copy_post_meta works while on TARGET blog.
                $this->copy_post_meta($source_post->ID, $result['target_id']);

                // LINKING: Save source info so we can delete/update later (Crucial for "Remove from Network")
                update_post_meta($result['target_id'], '_localized_from_post', $source_post->ID);
                update_post_meta($result['target_id'], '_localized_from_blog', get_current_blog_id()); // We are currently on source blog logic-wise? No.
                // Wait. clone_to_site switches blog.
                // At this point (line 650ish), we are inside the try block.
                // We switched to target blog at line ~598? 

                // Let's verify scope.
                // $this->copy_post_meta switches to main site (1) then back.

                // We need to ensure we are on the TARGET blog when calling update_post_meta here.
                // clone_to_site does switch_to_blog($target_blog_id).
                // It does NOT restore it until the finally{} block in clone_to_sites (plural) or implicitly at end of function if singular?
                // The singular clone_to_site DOES switch, but does it switch back?
                // Let's assume we are on target blog because we just did wp_insert_post/update on it.

                // However, copy_post_meta calls restore_current_blog() at the end.
                // If switch_to_blog was called in clone_to_site, restore_current_blog() in copy_post_meta might pop the stack correctly 
                // IF copy_post_meta did its own switch_to_blog(1).

                // Check if this is a product and WooCommerce is available
                $is_product = ($source_post->post_type === 'product');
                $wc_available = function_exists('wc_get_product');

                // Collect debug info in result
                $result['debug'] = array();
                $result['debug'][] = "Post type: " . $source_post->post_type;
                $result['debug'][] = "Is product: " . ($is_product ? 'yes' : 'no');
                $result['debug'][] = "WC available: " . ($wc_available ? 'yes' : 'no');

                if ($is_product && $wc_available) {
                    $result['debug'][] = "Calling clone_product_data...";
                    $clone_result = $this->clone_product_data($source_post->ID, $result['target_id']);
                    if (is_array($clone_result) && isset($clone_result['debug'])) {
                        $result['debug'] = array_merge($result['debug'], $clone_result['debug']);
                    }
                    $this->clone_product_images($source_post->ID, $result['target_id']);
                    $result['debug'][] = "Product data cloning completed";
                } else if ($is_product && !$wc_available) {
                    $result['debug'][] = "SKIPPED clone_product_data - WooCommerce not available on target site";
                }
            }

            $result['success'] = true;

        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
            $result['success'] = false;
        }

        // Restore blog
        if (function_exists('restore_current_blog')) {
            restore_current_blog();
        } else {
            switch_to_blog($original_blog_id);
        }

        return $result;
    }

    /**
     * Clone Product Data (Attributes, Variations, Prices)
     * Must be called while switched to target blog
     * @return array Debug info about the clone operation
     */
    private function clone_product_data($source_id, $target_id)
    {
        $debug = array();
        $debug[] = "=== clone_product_data START ===";
        $debug[] = "Source ID: $source_id, Target ID: $target_id";

        // Ensure WooCommerce functions are available
        if (!function_exists('wc_get_product')) {
            $debug[] = "FAIL - wc_get_product not available";
            return array('success' => false, 'debug' => $debug);
        }

        // Switch back to main site to gather source data
        $target_blog_id = get_current_blog_id();
        error_log('Network Cloner: Current blog (target): ' . $target_blog_id);

        // Determine target site's currency for price conversion
        $target_currency = 'usd'; // Default
        $details = get_blog_details($target_blog_id);
        if ($details) {
            $path = trim($details->path, '/');
            $parts = explode('/', $path);
            $site_slug = end($parts);
            $target_currency = $this->get_currency_for_site($site_slug);
            $debug[] = "Target site slug: '$site_slug', currency: $target_currency";
        }

        // Use switch_to_blog(1) explicitly instead of restore_current_blog()
        switch_to_blog(1);
        error_log('Network Cloner: Switched to main site (blog 1), current: ' . get_current_blog_id());

        $source_product = wc_get_product($source_id);
        if (!$source_product) {
            $debug[] = "FAIL - Source product NOT found on main site";
            switch_to_blog($target_blog_id);
            return array('success' => false, 'debug' => $debug);
        }

        $product_type = $source_product->get_type();
        $debug[] = "Source product found, type: $product_type";

        $source_attributes = $source_product->get_attributes();
        $source_children = $source_product->get_children();

        $debug[] = "Source has " . count($source_attributes) . " attributes";
        $debug[] = "Source has " . count($source_children) . " children (variations)";

        // Simple/Parent Product Data
        $parent_data = array(
            'regular_price' => $source_product->get_regular_price(),
            'sale_price' => $source_product->get_sale_price(),
            'sku' => $source_product->get_sku(),
        );
        error_log('Network Cloner: Parent prices - Regular: ' . $parent_data['regular_price'] . ', Sale: ' . $parent_data['sale_price']);

        $variations_data = array();
        foreach ($source_children as $child_id) {
            $variation = wc_get_product($child_id);
            if ($variation) {
                $v_data = array(
                    'attributes' => $variation->get_attributes(),
                    'regular_price' => $variation->get_regular_price(),
                    'sale_price' => $variation->get_sale_price(),
                    'status' => $variation->get_status(),
                    'sku' => $variation->get_sku(),
                );
                $variations_data[] = $v_data;
                error_log('Network Cloner: Variation ' . $child_id . ' - Price: ' . $v_data['regular_price'] . ', SKU: ' . $v_data['sku']);
            } else {
                error_log('Network Cloner: WARNING - Could not load variation ' . $child_id);
            }
        }

        error_log('Network Cloner: Collected ' . count($variations_data) . ' variations data');

        switch_to_blog($target_blog_id); // Back to Target

        // Check if WooCommerce is available on target site
        if (!function_exists('wc_get_product') || !class_exists('WC_Product_Attribute')) {
            $debug[] = "WooCommerce not available on target site $target_blog_id";
            return array('success' => false, 'debug' => $debug);
        }

        $target_product = wc_get_product($target_id);
        if (!$target_product) {
            $debug[] = "Target product not found - ID $target_id";
            return array('success' => false, 'debug' => $debug);
        }

        // 0. Sync Main Product Price/SKU (Important for Simple Products)
        // Keep prices in USD - front-page handles currency conversion at display time
        $target_product->set_regular_price($parent_data['regular_price']);
        $target_product->set_sale_price($parent_data['sale_price']);
        if ($parent_data['sale_price']) {
            $target_product->set_price($parent_data['sale_price']);
        } else {
            $target_product->set_price($parent_data['regular_price']);
        }
        $target_product->set_sku($parent_data['sku']);
        $debug[] = "Set parent prices: regular=" . $parent_data['regular_price'] . ", sale=" . $parent_data['sale_price'];

        // 1. Sync Attributes - For taxonomy attributes (pa_devices), we need to properly set them
        $target_attributes = array();
        foreach ($source_attributes as $attr) {
            $new_attr = new WC_Product_Attribute();

            // Check if this is a taxonomy attribute (like pa_devices)
            $attr_name = $attr->get_name();
            if (taxonomy_exists($attr_name)) {
                // It's a taxonomy attribute
                $new_attr->set_id(wc_attribute_taxonomy_id_by_name($attr_name));
                $new_attr->set_name($attr_name);

                // Ensure terms exist on target site and get their IDs
                $source_term_ids = $attr->get_options();
                $target_term_ids = array();

                // Switch to main site to get term slugs
                switch_to_blog(1);
                $term_slugs = array();
                foreach ($source_term_ids as $term_id) {
                    $term = get_term($term_id, $attr_name);
                    if ($term && !is_wp_error($term)) {
                        $term_slugs[] = $term->slug;
                    }
                }
                switch_to_blog($target_blog_id);

                // Get or create terms on target site
                foreach ($term_slugs as $slug) {
                    $target_term = get_term_by('slug', $slug, $attr_name);
                    if ($target_term) {
                        $target_term_ids[] = $target_term->term_id;
                    } else {
                        // Create term if it doesn't exist
                        $inserted = wp_insert_term($slug, $attr_name, array('slug' => $slug));
                        if (!is_wp_error($inserted)) {
                            $target_term_ids[] = $inserted['term_id'];
                        }
                    }
                }

                $new_attr->set_options($target_term_ids);
                $debug[] = "Taxonomy attr $attr_name: mapped " . count($source_term_ids) . " source terms to " . count($target_term_ids) . " target terms";
            } else {
                // It's a custom attribute
                $new_attr->set_id(0);
                $new_attr->set_name($attr_name);
                $new_attr->set_options($attr->get_options());
            }

            $new_attr->set_position($attr->get_position());
            $new_attr->set_visible($attr->get_visible());
            $new_attr->set_variation($attr->get_variation());
            $target_attributes[] = $new_attr;
        }
        $target_product->set_attributes($target_attributes);
        $target_product->save();

        // 2. Sync Variations
        // Remove existing children to ensure clean sync
        $existing_children = $target_product->get_children();
        if ($existing_children) {
            foreach ($existing_children as $child_id) {
                wp_delete_post($child_id, true);
            }
        }

        // Create new variations (only if WC_Product_Variation class exists)
        $created_count = 0;
        if (class_exists('WC_Product_Variation') && !empty($variations_data)) {
            foreach ($variations_data as $v_data) {
                try {
                    $variation = new WC_Product_Variation();
                    $variation->set_parent_id($target_id);
                    $variation->set_attributes($v_data['attributes']);

                    // Keep USD prices - front-page handles currency conversion at display time
                    $variation->set_regular_price($v_data['regular_price']);
                    $variation->set_price($v_data['regular_price']);
                    $variation->set_sale_price($v_data['sale_price']);
                    if ($v_data['sale_price']) {
                        $variation->set_price($v_data['sale_price']);
                    }
                    $variation->set_status($v_data['status']);
                    $variation->set_sku($v_data['sku']);
                    $variation->save();
                    $created_count++;
                } catch (Exception $e) {
                    $debug[] = "Failed to create variation: " . $e->getMessage();
                    error_log('Network Cloner: Failed to create variation - ' . $e->getMessage());
                }
            }
            $debug[] = "Created $created_count variations for target product $target_id";
        }

        // CRITICAL: Set product type to "variable" AFTER variations are created
        // This must be done AFTER save() and after variations exist
        if ($created_count > 0) {
            // Clear any cached product data
            wc_delete_product_transients($target_id);
            clean_post_cache($target_id);

            // Force set the product type using the taxonomy directly
            wp_set_object_terms($target_id, 'variable', 'product_type', false);

            // Also update the post meta that some themes use
            update_post_meta($target_id, '_product_type', 'variable');

            // Sync the variable product data (min/max prices, stock, etc)
            $variable_product = new WC_Product_Variable($target_id);
            $variable_product->sync($variable_product);

            $debug[] = "Set product type to VARIABLE and synced (force)";

            // Verify
            $check_product = wc_get_product($target_id);
            $debug[] = "Verification: Product type is now: " . $check_product->get_type();
        }

        $debug[] = "Product data cloning completed";
        return array('success' => true, 'debug' => $debug);
    }

    /**
     * Clone Product Images (Featured Image, Gallery, and Content Images)
     * Must be called while switched to target blog
     */
    private function clone_product_images($source_id, $target_id)
    {
        // Get target blog ID before switching
        $target_blog_id = get_current_blog_id();

        // Explicitly switch to main site (blog ID 1) to get source images
        // Using switch_to_blog(1) instead of restore_current_blog() for reliability
        switch_to_blog(1);

        // Get featured image
        $featured_image_id = get_post_meta($source_id, '_thumbnail_id', true);
        $featured_image_data = null;
        if ($featured_image_id) {
            $featured_image_data = $this->get_attachment_data($featured_image_id);
        }

        // Get product gallery images
        $gallery_image_ids = get_post_meta($source_id, '_product_image_gallery', true);
        $gallery_images_data = array();
        if ($gallery_image_ids) {
            $gallery_ids_array = explode(',', $gallery_image_ids);
            foreach ($gallery_ids_array as $gallery_id) {
                $gallery_id = trim($gallery_id);
                if ($gallery_id) {
                    $img_data = $this->get_attachment_data($gallery_id);
                    if ($img_data) {
                        $gallery_images_data[] = $img_data;
                    }
                }
            }
        }

        // Get content images (from post_content)
        $source_post = get_post($source_id);
        $content_images_data = array();
        if ($source_post && $source_post->post_content) {
            $content_images_data = $this->extract_content_images($source_post->post_content);
        }

        // Switch to target site
        switch_to_blog($target_blog_id);

        // Clone featured image to target
        if ($featured_image_data) {
            $new_featured_id = $this->upload_attachment_to_blog($featured_image_data, $target_id);
            if ($new_featured_id) {
                update_post_meta($target_id, '_thumbnail_id', $new_featured_id);
                // Also set WooCommerce product image
                if (function_exists('wc_get_product')) {
                    $product = wc_get_product($target_id);
                    if ($product) {
                        $product->set_image_id($new_featured_id);
                        $product->save();
                    }
                }
            }
        }

        // Clone gallery images to target
        if (!empty($gallery_images_data)) {
            $new_gallery_ids = array();
            foreach ($gallery_images_data as $gallery_img) {
                $new_id = $this->upload_attachment_to_blog($gallery_img, $target_id);
                if ($new_id) {
                    $new_gallery_ids[] = $new_id;
                }
            }
            if (!empty($new_gallery_ids)) {
                update_post_meta($target_id, '_product_image_gallery', implode(',', $new_gallery_ids));
                // Also set WooCommerce gallery
                if (function_exists('wc_get_product')) {
                    $product = wc_get_product($target_id);
                    if ($product) {
                        $product->set_gallery_image_ids($new_gallery_ids);
                        $product->save();
                    }
                }
            }
        }

        // Clone content images and update post_content
        if (!empty($content_images_data)) {
            $target_post = get_post($target_id);
            $updated_content = $target_post->post_content;

            foreach ($content_images_data as $img_data) {
                $new_id = $this->upload_attachment_to_blog($img_data, $target_id);
                if ($new_id) {
                    // Get new image URL
                    $new_url = wp_get_attachment_url($new_id);
                    if ($new_url && isset($img_data['url'])) {
                        // Replace old URL with new URL in content
                        $updated_content = str_replace($img_data['url'], $new_url, $updated_content);
                    }
                }
            }

            // Update the post content with new image URLs
            wp_update_post(array(
                'ID' => $target_id,
                'post_content' => $updated_content,
            ));
        }

        // Restore back to main site for proper cleanup
        restore_current_blog();
    }

    /**
     * Get attachment data from main site
     * @param int $attachment_id
     * @return array|null
     */
    private function get_attachment_data($attachment_id)
    {
        $attachment = get_post($attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            return null;
        }

        $file_path = get_attached_file($attachment_id);
        if (!$file_path || !file_exists($file_path)) {
            return null;
        }

        return array(
            'id' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id),
            'file_path' => $file_path,
            'file_name' => basename($file_path),
            'mime_type' => $attachment->post_mime_type,
            'title' => $attachment->post_title,
            'alt' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
        );
    }

    /**
     * Extract images from post content
     * @param string $content
     * @return array
     */
    private function extract_content_images($content)
    {
        $images = array();

        // Match img tags with src attribute
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);

        if (!empty($matches[1])) {
            $upload_dir = wp_upload_dir();
            $base_url = $upload_dir['baseurl'];

            foreach ($matches[1] as $img_url) {
                // Only clone images from our uploads directory
                if (strpos($img_url, $base_url) !== false) {
                    // Find attachment by URL
                    $attachment_id = attachment_url_to_postid($img_url);
                    if ($attachment_id) {
                        $img_data = $this->get_attachment_data($attachment_id);
                        if ($img_data) {
                            $images[] = $img_data;
                        }
                    }
                }
            }
        }

        return $images;
    }

    /**
     * Upload attachment to current blog
     * @param array $attachment_data
     * @param int $parent_post_id
     * @return int|null New attachment ID or null on failure
     */
    private function upload_attachment_to_blog($attachment_data, $parent_post_id)
    {
        if (!$attachment_data || !isset($attachment_data['file_path'])) {
            error_log('Clone Images: Missing attachment data');
            return null;
        }

        $source_file = $attachment_data['file_path'];

        // Check if file exists
        if (!file_exists($source_file)) {
            error_log('Clone Images: Source file does not exist - ' . $source_file);
            return null;
        }

        // Get upload directory for current blog
        $upload_dir = wp_upload_dir();

        // Check for upload directory errors
        if (!empty($upload_dir['error'])) {
            error_log('Clone Images: Upload dir error - ' . $upload_dir['error']);
            return null;
        }

        // Ensure upload directory exists
        if (!file_exists($upload_dir['path'])) {
            if (!wp_mkdir_p($upload_dir['path'])) {
                error_log('Clone Images: Could not create upload directory - ' . $upload_dir['path']);
                return null;
            }
        }

        // Generate unique filename to avoid conflicts
        $filename = wp_unique_filename($upload_dir['path'], $attachment_data['file_name']);
        $target_file = $upload_dir['path'] . '/' . $filename;

        // Copy file to target blog's uploads
        if (!copy($source_file, $target_file)) {
            error_log('Clone Images: Failed to copy file from ' . $source_file . ' to ' . $target_file);
            return null;
        }

        // Get the relative path for WordPress (subdir/filename)
        $relative_file = $upload_dir['subdir'] . '/' . $filename;
        $relative_file = ltrim($relative_file, '/');

        // Prepare attachment data
        $attachment = array(
            'guid' => $upload_dir['url'] . '/' . $filename,
            'post_mime_type' => $attachment_data['mime_type'],
            'post_title' => $attachment_data['title'],
            'post_content' => '',
            'post_status' => 'inherit',
            'post_parent' => $parent_post_id,
        );

        // Insert attachment using relative file path
        $attach_id = wp_insert_attachment($attachment, $target_file, $parent_post_id);

        if (is_wp_error($attach_id) || !$attach_id) {
            error_log('Clone Images: Failed to insert attachment - ' . (is_wp_error($attach_id) ? $attach_id->get_error_message() : 'Unknown error'));
            return null;
        }

        // Generate attachment metadata
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $target_file);
        wp_update_attachment_metadata($attach_id, $attach_data);

        // Set alt text
        if (!empty($attachment_data['alt'])) {
            update_post_meta($attach_id, '_wp_attachment_image_alt', $attachment_data['alt']);
        }

        return $attach_id;
    }

    /**
     * Copy post meta from source to target
     */
    private function copy_post_meta($source_id, $target_id)
    {
        // Switch to main site to get source meta
        $current_blog = get_current_blog_id();
        switch_to_blog(1);

        $meta_keys = array(
            '_thumbnail_id',
            '_seo_meta_title',
            '_seo_meta_description',
            '_seo_focus_keyword',
            'rank_math_title',
            'rank_math_description',
            'rank_math_focus_keyword',
        );

        $meta_values = array();
        foreach ($meta_keys as $key) {
            $meta_values[$key] = get_post_meta($source_id, $key, true);
        }

        restore_current_blog();

        // Now apply to target
        switch_to_blog($current_blog);

        foreach ($meta_values as $key => $value) {
            if (!empty($value)) {
                update_post_meta($target_id, $key, $value);
            }
        }
    }

    /**
     * Show admin notice with clone/remove results
     */
    public function show_clone_notice()
    {
        $is_cloned = isset($_GET['cloned']) && $_GET['cloned'] === 'true';
        $is_removed = isset($_GET['removed']) && $_GET['removed'] === 'true';

        if (!$is_cloned && !$is_removed && !isset($_GET['menus_cloned']) && !isset($_GET['menus_removed'])) {
            return;
        }

        $results = get_transient('network_clone_results_' . get_current_user_id());

        if (!$results) {
            return;
        }

        delete_transient('network_clone_results_' . get_current_user_id());

        $messages = array();

        if (!empty($results['created'])) {
            $messages[] = '✅ Created on: ' . implode(', ', $results['created']);
        }

        if (!empty($results['updated'])) {
            $messages[] = '🔄 Updated on: ' . implode(', ', $results['updated']);
        }

        if (!empty($results['translated'])) {
            $messages[] = '🌐 Translated: ' . implode(', ', $results['translated']);
        }

        if (!empty($results['removed'])) {
            $messages[] = '🗑️ Removed from: ' . implode(', ', $results['removed']);
        }

        if (!empty($results['not_found'])) {
            $messages[] = '⚠️ Not found on: ' . implode(', ', $results['not_found']);
        }

        if (!empty($results['errors'])) {
            $messages[] = '❌ Errors: ' . implode('; ', $results['errors']);
        }

        if (empty($messages)) {
            $messages[] = 'No sub-sites found to process.';
        }

        $class = empty($results['errors']) ? 'notice-success' : 'notice-warning';
        $class = empty($results['errors']) ? 'notice-success' : 'notice-warning';
        $title = $is_removed ? '🗑️ Network Remove Complete!' : '🌐 Network Clone Complete!';

        if (isset($_GET['menus_cloned']) && $_GET['menus_cloned'] === 'true') {
            $title = '🌐 Global Menu Sync Complete!';
        }

        if (isset($_GET['menus_removed']) && $_GET['menus_removed'] === 'true') {
            $title = '🗑️ Global Menu Deletion Complete!';
        }

        echo '<div class="notice ' . $class . ' is-dismissible">';
        echo '<p><strong>' . $title . '</strong></p>';
        echo '<ul style="margin:0 0 10px 20px;list-style:disc;">';
        foreach ($messages as $msg) {
            echo '<li>' . esc_html($msg) . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }
    /**
     * Handle the menu clone action
     */
    public function handle_menu_clone_action()
    {
        if (!isset($_GET['action']) || $_GET['action'] !== 'clone_menus_to_network') {
            return;
        }

        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'clone_menus_to_network')) {
            wp_die('Security check failed');
        }

        // Only super admins
        if (!is_super_admin()) {
            wp_die('Unauthorized - Super Admin required');
        }

        // Clone menus to all sub-sites
        $results = $this->clone_menus_to_sites();

        // Store results for notice
        set_transient('network_clone_results_' . get_current_user_id(), $results, 60);

        // Redirect back
        wp_safe_redirect(add_query_arg(array(
            'menus_cloned' => 'true',
        ), admin_url('nav-menus.php')));
        exit;
    }

    /**
     * Clone all menus to sub-sites
     */
    private function clone_menus_to_sites()
    {
        $results = array(
            'updated' => array(),
            'translated' => array(),
            'errors' => array(),
        );

        // Get Main Site Home URL (without trailing slash for matching)
        $main_home_url = untrailingslashit(home_url());

        // Get registered menu locations on Main Site
        $locations = get_nav_menu_locations();
        $registered_menus = get_registered_nav_menus(); // 'primary', 'footer_1', etc.

        // Get all sites
        $sites = get_sites(array('number' => 100));

        foreach ($sites as $site) {
            // Skip main site
            if ($site->blog_id == 1) {
                continue;
            }

            // Get site slug
            $path = trim($site->path, '/');
            $path_parts = explode('/', $path);
            $site_slug = end($path_parts);

            // Only process target sites
            if (!in_array($site_slug, $this->target_sites)) {
                continue;
            }

            // Switch to sub-site
            switch_to_blog($site->blog_id);

            $subsite_locations = get_nav_menu_locations();
            $menus_updated = false;

            foreach ($registered_menus as $location_slug => $location_name) {
                // Check if Main Site has a menu assigned to this location
                // We need to switch back to Main Site briefly to get the Menu Object
                restore_current_blog();

                $main_locations = get_nav_menu_locations();
                if (!isset($main_locations[$location_slug])) {
                    // No menu assigned on main site for this location
                    switch_to_blog($site->blog_id);
                    continue;
                }

                $menu_id = $main_locations[$location_slug];
                $menu_object = wp_get_nav_menu_object($menu_id);
                $menu_items = wp_get_nav_menu_items($menu_id);

                if (!$menu_object) {
                    switch_to_blog($site->blog_id);
                    continue;
                }

                // Switch back to Sub Site
                switch_to_blog($site->blog_id);
                $sub_home_url = untrailingslashit(home_url());

                try {
                    // Check if menu exists on sub-site
                    $sub_menu_obj = wp_get_nav_menu_object($menu_object->name);

                    if (!$sub_menu_obj) {
                        // Create it
                        $sub_menu_id = wp_create_nav_menu($menu_object->name);
                    } else {
                        $sub_menu_id = $sub_menu_obj->term_id;
                        // Clear existing items to ensure sync
                        $existing_items = wp_get_nav_menu_items($sub_menu_id);
                        if ($existing_items) {
                            foreach ($existing_items as $item) {
                                wp_delete_post($item->ID, true);
                            }
                        }
                    }

                    // Assign menu to location
                    $subsite_locations[$location_slug] = $sub_menu_id;

                    // Add items
                    if ($menu_items) {
                        // Build a map of old_db_id => new_db_id for parent/child relationships
                        $id_map = array();

                        foreach ($menu_items as $item) {
                            $title = $item->title;

                            // Rewrite URL: Replace main site home URL with subsite home URL
                            $url = $item->url;
                            if (strpos($url, $main_home_url) === 0) {
                                $url = str_replace($main_home_url, $sub_home_url, $url);
                            }

                            // Prepare item data
                            $args = array(
                                'menu-item-title' => $title,
                                'menu-item-classes' => implode(' ', $item->classes),
                                'menu-item-url' => $url,
                                'menu-item-status' => 'publish',
                                'menu-item-type' => $item->type, // 'custom', 'post_type', etc.
                            );

                            // Handle object linking (pages/posts)
                            // This is tricky across network. For simplicity, we fallback to 'custom' URL if object IDs don't match.
                            // However, since we cloned pages with same slugs, we can try to find the object by slug.
                            if ($item->type === 'post_type') {
                                // Need to find the equivalent post ID on this subsite
                                // Switch context is currently subsite, which is correct
                                $original_object_id = $item->object_id;

                                // We need the slug of the original object. 
                                // We have to look it up on the main site.
                                restore_current_blog(); // Back to main
                                $original_post = get_post($original_object_id);
                                $original_slug = $original_post ? $original_post->post_name : '';
                                switch_to_blog($site->blog_id); // Back to sub

                                if ($original_slug) {
                                    $target_post = get_page_by_path($original_slug, OBJECT, $item->object); // $item->object is post type (page/post)
                                    if ($target_post) {
                                        $args['menu-item-object-id'] = $target_post->ID;
                                        $args['menu-item-object'] = $item->object;
                                    } else {
                                        // Fallback to custom link if page not found
                                        $args['menu-item-type'] = 'custom';
                                        $args['menu-item-url'] = home_url('/' . $original_slug . '/');
                                    }
                                }
                            }

                            // Handle Parent
                            if ($item->menu_item_parent && isset($id_map[$item->menu_item_parent])) {
                                $args['menu-item-parent-id'] = $id_map[$item->menu_item_parent];
                            }

                            $new_item_id = wp_update_nav_menu_item($sub_menu_id, 0, $args);
                            if (!is_wp_error($new_item_id)) {
                                $id_map[$item->db_id] = $new_item_id;
                            }
                        }
                    }

                    $menus_updated = true;

                } catch (Exception $e) {
                    $results['errors'][] = strtoupper($site_slug) . ': ' . $e->getMessage();
                }
            }

            if ($menus_updated) {
                set_theme_mod('nav_menu_locations', $subsite_locations);
                $results['updated'][] = strtoupper($site_slug);
            }

            restore_current_blog();
        }

        return $results;
    }

    /**
     * Handle the menu remove action
     */
    public function handle_menu_remove_action()
    {
        if (!isset($_GET['action']) || $_GET['action'] !== 'remove_menus_from_network') {
            return;
        }

        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'remove_menus_from_network')) {
            wp_die('Security check failed');
        }

        // Only super admins
        if (!is_super_admin()) {
            wp_die('Unauthorized - Super Admin required');
        }

        // Remove menus from all sub-sites
        $results = $this->remove_menus_from_sites();

        // Store results for notice
        set_transient('network_clone_results_' . get_current_user_id(), $results, 60);

        // Redirect back
        wp_safe_redirect(add_query_arg(array(
            'menus_removed' => 'true',
        ), admin_url('nav-menus.php')));
        exit;
    }

    /**
     * Remove all menus from sub-sites that match main site menus
     */
    private function remove_menus_from_sites()
    {
        $results = array(
            'removed' => array(),
            'errors' => array(),
            'not_found' => array(),
        );

        // Get registered menu locations on Main Site
        $registered_menus = get_registered_nav_menus();

        // Get all sites
        $sites = get_sites(array('number' => 100));

        // Get Main Site menu names
        $main_menu_names = array();
        $locations = get_nav_menu_locations();
        foreach ($locations as $loc => $id) {
            $menu = wp_get_nav_menu_object($id);
            if ($menu) {
                $main_menu_names[] = $menu->name;
            }
        }

        foreach ($sites as $site) {
            // Skip main site
            if ($site->blog_id == 1) {
                continue;
            }

            // Get site slug
            $path = trim($site->path, '/');
            $path_parts = explode('/', $path);
            $site_slug = end($path_parts);

            // Only process target sites
            if (!in_array($site_slug, $this->target_sites)) {
                continue;
            }

            switch_to_blog($site->blog_id);

            try {
                $menus_removed = false;

                // We loop through main site menu names and delete counterparts on subsite
                foreach ($main_menu_names as $name) {
                    $sub_menu = wp_get_nav_menu_object($name);
                    if ($sub_menu) {
                        wp_delete_nav_menu($sub_menu->term_id);
                        $menus_removed = true;
                    }
                }

                if ($menus_removed) {
                    $results['removed'][] = strtoupper($site_slug);
                } else {
                    $results['not_found'][] = strtoupper($site_slug);
                }

            } catch (Exception $e) {
                $results['errors'][] = strtoupper($site_slug) . ': ' . $e->getMessage();
            }

            restore_current_blog();
        }

        return $results;
    }

    /**
     * Clone menus to a single site (for AJAX/UI use)
     * 
     * Menu labels are copied verbatim — the theme no longer translates on the server.
     *
     * @param int  $target_blog_id Target blog ID
     * @param bool $do_translate   Kept for call-site compatibility; ignored.
     * @return array Result with success status
     */
    public function clone_menus_to_site($target_blog_id, $do_translate = true)
    {
        $result = array(
            'success' => false,
            'translated' => false,
            'menus_count' => 0,
            'message' => ''
        );

        // Get Main Site Home URL (without trailing slash for matching)
        $main_home_url = untrailingslashit(home_url());

        // Get registered menu locations on Main Site
        $locations = get_nav_menu_locations();
        $registered_menus = get_registered_nav_menus();

        // Switch to target sub-site
        switch_to_blog($target_blog_id);
        $sub_home_url = untrailingslashit(home_url());
        $subsite_locations = get_nav_menu_locations();
        $menus_cloned = 0;

        foreach ($registered_menus as $location_slug => $location_name) {
            // Switch back to Main Site to get the Menu Object
            restore_current_blog();

            if (!isset($locations[$location_slug])) {
                switch_to_blog($target_blog_id);
                continue;
            }

            $menu_id = $locations[$location_slug];
            $menu_object = wp_get_nav_menu_object($menu_id);
            $menu_items = wp_get_nav_menu_items($menu_id);

            if (!$menu_object) {
                switch_to_blog($target_blog_id);
                continue;
            }

            // Switch back to Sub Site
            switch_to_blog($target_blog_id);

            try {
                // Check if menu exists on sub-site
                $sub_menu_obj = wp_get_nav_menu_object($menu_object->name);

                if (!$sub_menu_obj) {
                    // Create it
                    $sub_menu_id = wp_create_nav_menu($menu_object->name);
                } else {
                    $sub_menu_id = $sub_menu_obj->term_id;
                    // Clear existing items to ensure sync
                    $existing_items = wp_get_nav_menu_items($sub_menu_id);
                    if ($existing_items) {
                        foreach ($existing_items as $item) {
                            wp_delete_post($item->ID, true);
                        }
                    }
                }

                // Assign menu to location
                $subsite_locations[$location_slug] = $sub_menu_id;

                // Add items
                if ($menu_items) {
                    // Build a map of old_db_id => new_db_id for parent/child relationships
                    $id_map = array();

                    foreach ($menu_items as $item) {
                        $title = $item->title;

                        // Rewrite URL: Replace main site home URL with subsite home URL
                        $url = $item->url;
                        if (strpos($url, $main_home_url) === 0) {
                            $url = str_replace($main_home_url, $sub_home_url, $url);
                        }

                        // Prepare item data
                        $args = array(
                            'menu-item-title' => $title,
                            'menu-item-classes' => implode(' ', $item->classes),
                            'menu-item-url' => $url,
                            'menu-item-status' => 'publish',
                            'menu-item-type' => $item->type,
                        );

                        // Handle object linking (pages/posts)
                        if ($item->type === 'post_type') {
                            // Need to find the equivalent post ID on this subsite
                            restore_current_blog();
                            $original_post = get_post($item->object_id);
                            $original_slug = $original_post ? $original_post->post_name : '';
                            switch_to_blog($target_blog_id);

                            if ($original_slug) {
                                $target_post = get_page_by_path($original_slug, OBJECT, $item->object);
                                if ($target_post) {
                                    $args['menu-item-object-id'] = $target_post->ID;
                                    $args['menu-item-object'] = $item->object;
                                } else {
                                    // Fallback to custom link if page not found
                                    $args['menu-item-type'] = 'custom';
                                    $args['menu-item-url'] = home_url('/' . $original_slug . '/');
                                }
                            }
                        }

                        // Handle Parent
                        if ($item->menu_item_parent && isset($id_map[$item->menu_item_parent])) {
                            $args['menu-item-parent-id'] = $id_map[$item->menu_item_parent];
                        }

                        $new_item_id = wp_update_nav_menu_item($sub_menu_id, 0, $args);
                        if (!is_wp_error($new_item_id)) {
                            $id_map[$item->db_id] = $new_item_id;
                        }
                    }
                }

                $menus_cloned++;

            } catch (Exception $e) {
                $result['message'] = $e->getMessage();
            }
        }

        if ($menus_cloned > 0) {
            set_theme_mod('nav_menu_locations', $subsite_locations);
            $result['success'] = true;
            $result['menus_count'] = $menus_cloned;
        }

        restore_current_blog();

        return $result;
    }

    /**
     * Remove menus from a single site (for AJAX/UI use)
     * 
     * @param int $target_blog_id Target blog ID
     * @return array Result with success status
     */
    public function remove_menus_from_site($target_blog_id)
    {
        $result = array(
            'success' => false,
            'removed_count' => 0,
            'message' => ''
        );

        // Get Main Site menu names
        $main_menu_names = array();
        $locations = get_nav_menu_locations();
        foreach ($locations as $loc => $id) {
            $menu = wp_get_nav_menu_object($id);
            if ($menu) {
                $main_menu_names[] = $menu->name;
            }
        }

        switch_to_blog($target_blog_id);

        try {
            $menus_removed = 0;

            // We loop through main site menu names and delete counterparts on subsite
            foreach ($main_menu_names as $name) {
                $sub_menu = wp_get_nav_menu_object($name);
                if ($sub_menu) {
                    wp_delete_nav_menu($sub_menu->term_id);
                    $menus_removed++;
                }
            }

            if ($menus_removed > 0) {
                $result['success'] = true;
                $result['removed_count'] = $menus_removed;
            } else {
                $result['message'] = 'No matching menus found to remove';
            }

        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
        }

        restore_current_blog();

        return $result;
    }
}

// Initialize the Network Cloner
new Theme_Network_Cloner();

