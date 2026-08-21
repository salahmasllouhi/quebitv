<?php
/**
 * Brand lockup.
 *
 * The supplied mark: the orange Q and the wordmark beside it, as one image.
 *
 * This briefly went the other way. images/logo/*.png used to be a rendered
 * "NordicTv" wordmark that no find-and-replace could reach, so the lockup was
 * set in type instead — which fixed the stale brand but only ever approximated
 * the real mark, because the Q has no typographic equivalent.
 *
 * Two files, and which one to use is decided by the surface, not the caller:
 *
 *   quebec-iptv-logo.png       white wordmark, for dark bars. Both the header
 *                              (rgba(7,25,29,.78)) and the footer (--color-navy)
 *                              are dark, so this is the default.
 *   quebec-iptv-logo-dark.png  ink wordmark, for light surfaces. Pass 'light'
 *                              as $on to get it.
 *
 * The Q is the same orange in both, so only the wordmark changes.
 *
 * @package Quebec_IPTV
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('iptv_brand_lockup')) {
    /**
     * Markup for the lockup.
     *
     * @param string $modifier Extra class, e.g. 'brand-lockup--footer'.
     * @param string $on       Surface the lockup sits on: 'dark' (default) or
     *                         'light'. Picks the wordmark colour.
     * @return string
     */
    function iptv_brand_lockup($modifier = '', $on = 'dark')
    {
        $classes = 'brand-lockup' . ($modifier ? ' ' . $modifier : '');

        $file = ($on === 'light') ? 'quebec-iptv-logo-dark.png' : 'quebec-iptv-logo.png';

        // Intrinsic size of the trimmed asset. Declared so the bar does not
        // reflow once the image decodes — the header is the first thing on the
        // page and a shifting logo drags the whole nav with it.
        return sprintf(
            '<img class="%s" src="%s" alt="%s" width="961" height="186" decoding="async" />',
            esc_attr($classes),
            esc_url(get_template_directory_uri() . '/images/logo/' . $file),
            esc_attr__('Quebec IPTV', 'my-iptv-theme')
        );
    }
}
