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
 * The source is the supplied square mark — the orange Q on a white rounded
 * tile — rendered to the sizes browsers ask for. PNG rather than SVG: the mark
 * is artwork, not a shape that can be hand-written as vector, and every browser
 * that matters takes a PNG favicon.
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
    $path = get_template_directory() . '/images/favicon/favicon-32.png';
    $mt   = file_exists($path) ? filemtime($path) : 0;
    $q    = $mt ? '?v=' . $mt : '';

    printf(
        '<link rel="icon" type="image/png" sizes="32x32" href="%s">' . "\n"
        . '<link rel="icon" type="image/png" sizes="192x192" href="%s">' . "\n"
        . '<link rel="icon" type="image/png" sizes="512x512" href="%s">' . "\n"
        . '<link rel="apple-touch-icon" href="%s">' . "\n",
        esc_url($dir . '/favicon-32.png' . $q),
        esc_url($dir . '/favicon-192.png' . $q),
        esc_url($dir . '/favicon-512.png' . $q),
        esc_url($dir . '/apple-touch-icon.png' . $q)
    );
}, 99);

/**
 * Everything that is not the front end: wp-admin, the Customizer, oEmbed.
 *
 * Those read the `site_icon` option rather than wp_head, so detaching
 * wp_site_icon() above does nothing for them and the admin bar kept the old
 * upload. The option itself is out of reach from here — Royal MCP refuses to
 * write it (site_icon is not in its readable allowlist, and writes require a
 * plugin-side opt-in first), and hardcoding an attachment id would only be
 * right for this one site in the network.
 *
 * get_site_icon_url() applies this filter whether or not the option is set, and
 * has_site_icon() is just a boolean cast of its result — so answering here is
 * enough to make core believe an icon exists and to hand it the theme's file.
 * No database write, nothing to re-upload per site.
 */
add_filter('get_site_icon_url', function ($url, $size) {
    $dir = get_template_directory() . '/images/favicon';

    // Serve the smallest file that still covers the requested size, so the
    // admin bar's 32px slot does not pull the 512.
    $candidates = array(32 => 'favicon-32.png', 180 => 'apple-touch-icon.png', 192 => 'favicon-192.png', 512 => 'favicon-512.png');

    $chosen = 'favicon-512.png';
    foreach ($candidates as $w => $file) {
        if ($size <= $w) {
            $chosen = $file;
            break;
        }
    }

    if (!file_exists($dir . '/' . $chosen)) {
        return $url;
    }

    return get_template_directory_uri() . '/images/favicon/' . $chosen;
}, 10, 2);
