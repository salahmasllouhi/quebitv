<?php
/**
 * Text brand lockup.
 *
 * Replaces images/logo/*.png, which were a rendered "NordicTv" wordmark — no
 * find-and-replace could reach the pixels, so the old brand survived every pass
 * of the rebrand. Setting the mark in type instead means it follows the palette
 * tokens and stays correct the next time the colours move.
 *
 * "Quebec" takes the inverse text colour and "IPTV" the brand accent, matching
 * the two-tone treatment the .logo / .logo span rules in header.css were
 * written for before the image was introduced.
 *
 * @package Quebec_IPTV
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('iptv_brand_lockup')) {
    /**
     * Markup for the wordmark.
     *
     * @param string $modifier Extra class, e.g. 'brand-lockup--footer'.
     * @return string
     */
    function iptv_brand_lockup($modifier = '')
    {
        $classes = 'brand-lockup' . ($modifier ? ' ' . $modifier : '');

        // The name is one word to a screen reader; the two spans are purely a
        // colour split, so the inner text is hidden from the accessibility tree
        // and the whole lockup carries a single label.
        return sprintf(
            '<span class="%s" role="img" aria-label="%s">'
                . '<span class="brand-lockup__name" aria-hidden="true">Quebec</span>'
                . '<span class="brand-lockup__accent" aria-hidden="true">IPTV</span>'
            . '</span>',
            esc_attr($classes),
            esc_attr__('Quebec IPTV', 'my-iptv-theme')
        );
    }
}
