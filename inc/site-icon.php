<?php
/**
 * Favicon
 *
 * The site icon was an uploaded PNG of the old NordicTv mark — the same leftover
 * inc/brand-lockup.php replaced in the header, still sitting in every browser tab
 * because uploads/2026/02/nordictv.png is pixels no find-and-replace could reach.
 *
 * Rather than swapping the upload, the mark moves into the theme for the same
 * reason the wordmark did: it is brand, so it belongs in git next to the palette
 * it is drawn from, and it deploys with the rest of the design instead of having
 * to be re-uploaded per site in the network.
 *
 * images/favicon/favicon.svg is the real icon — vector, 304 bytes, sharp at any
 * size. The PNGs are there for Safari, which still ignores SVG favicons, and for
 * the home-screen shortcut. All four are the brand accent #fc6c34 with a white
 * play triangle, which is the same fill/ink pairing the CTAs use.
 *
 * WordPress prints its own <link rel="icon"> set from the `site_icon` option at
 * priority 99 on wp_head, so that has to come off or the tab gets both and the
 * browser is free to pick the stale one.
 *
 * @package Quebec_IPTV
 */

if (!defined('ABSPATH')) {
    exit;
}

// Core's output, keyed to the `site_icon` option. Detached here rather than the
// option being cleared, so the Customizer's Site Identity panel keeps working —
// the admin favicon and anything else reading the option still resolve.
remove_action('wp_head', 'wp_site_icon', 99);

add_action('wp_head', function () {
    $dir = get_template_directory_uri() . '/images/favicon';

    // Cache-bust per deploy. Browsers hold on to a favicon far longer than they
    // hold on to CSS, which is how the old mark survived visibly past its own
    // removal. Keyed off the file's mtime rather than the theme version, which
    // has sat at 1.0.0 since the theme was created and would never move the
    // query string; a checkout restamps mtime on every deploy.
    $path = get_template_directory() . '/images/favicon/favicon.svg';
    $mt   = file_exists($path) ? filemtime($path) : 0;
    $q    = $mt ? '?v=' . $mt : '';

    printf(
        '<link rel="icon" href="%s" sizes="any">' . "\n"
        . '<link rel="icon" type="image/svg+xml" href="%s">' . "\n"
        . '<link rel="apple-touch-icon" href="%s">' . "\n",
        esc_url($dir . '/favicon-32.png' . $q),
        esc_url($dir . '/favicon.svg' . $q),
        esc_url($dir . '/apple-touch-icon.png' . $q)
    );
}, 99);
