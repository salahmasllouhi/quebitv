<?php
/**
 * Site Config – ACF Options Page
 *
 * Holds the values that are configuration rather than copy: checkout URLs and the
 * numeric defaults the pricing configurator boots with. These used to be routed
 * through iptv_text(), which meant a URL and the number "12" were being handed to
 * a translation function.
 *
 * ACF Options for Polylang is active, so these values are stored per language —
 * which is what the checkout URLs actually want.
 *
 * Read with: iptv_config('key', 'fallback')
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('acf/init', function () {
    if (!function_exists('acf_add_options_page')) {
        return;
    }

    acf_add_options_page(array(
        'page_title'  => 'Quebec IPTV Site Config',
        'menu_title'  => 'Site Config',
        'menu_slug'   => 'quebeciptv-site-config',
        'parent_slug' => 'options-general.php',
        'capability'  => 'manage_options',
        'redirect'    => false,
        'autoload'    => true,
    ));
});

if (!function_exists('iptv_config')) {
    /**
     * Read a Site Config value, falling back to the theme default.
     *
     * Unlike iptv_text() this never touches Polylang string translations — these
     * values are configuration, not copy.
     *
     * @param string $key      Field name on the Site Config options page.
     * @param mixed  $fallback Returned when the field is empty or ACF is inactive.
     * @return mixed
     */
    function iptv_config($key, $fallback = '')
    {
        if (!function_exists('get_field')) {
            return $fallback;
        }

        $value = get_field($key, 'option');

        return ($value === null || $value === '') ? $fallback : $value;
    }
}
