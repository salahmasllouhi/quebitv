<?php
/**
 * 301s for URLs that have moved
 *
 * The SEO pass renamed several page slugs so each one carries the keyword it is
 * written for. Every one of those URLs was already indexed, so each needs a
 * permanent redirect or the ranking and any backlinks go with it.
 *
 * Why this is not left to WordPress: core's wp_old_slug_redirect() reads the
 * _wp_old_slug post meta, which is only written when the slug changes through
 * wp_insert_post(). These slugs were changed over the REST API, which writes
 * post_name directly, so no _wp_old_slug row was ever created. Adding one by
 * hand did not help either — on this install the old page URL under Polylang's
 * /en/ prefix 404s before wp_old_slug_redirect() finds anything to match, since
 * the request resolves as a pagename lookup rather than the name query var that
 * function inspects.
 *
 * Why not Rank Math Pro's Redirections module, which is installed: it stores
 * rules in its own database table rather than in options, so nothing outside
 * wp-admin can write to it. A table in the theme is reviewable in the diff,
 * deploys with everything else, and cannot be lost with a plugin.
 *
 * Ordering matters. This runs on template_redirect at priority 1, before the
 * language redirect in inc/language-preference.php, so a hit on an old URL is
 * sent to its new home rather than bounced to a home page first.
 *
 * @package Quebec_IPTV
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('iptv_legacy_slug_map')) {
    /**
     * old slug => new slug.
     *
     * Slugs, not paths, so one entry covers a page whatever language prefix it
     * sits behind — and so nothing here has to know about hide_default.
     *
     * @return array<string,string>
     */
    function iptv_legacy_slug_map()
    {
        return array(
            // M3U converter: the old slug named the page but not the term it is
            // written for.
            'm3u-playlist-convert-your-m3u-url' => 'm3u-editor-playlist-to-xtream-converter',

            // English plan pages. The old slugs carried keywords chosen for the
            // Nordic market and, in one case, for Reddit — which is a poor
            // commercial term to build a product URL on.
            '1-month-iptv-service-provider'        => '1-month-iptv-encoder-subscription',
            '3-months-ip-tv-subscription'          => '3-months-best-iptv-subscription',
            '6-months-iptv-service-usa'            => '6-months-iptv-subscription',
            '12-months-best-iptv-providers-reddit' => '12-months-iptv-providers-subscription',

            // French plan pages. Only the two whose keyword was not already in
            // the slug, plus the 3-month page, where the keyword is the same two
            // words in the other order and Rank Math matches the exact phrase.
            'abonnement-iptv-3-mois'  => 'iptv-abonnement-3-mois',
            'abonnement-iptv-6-mois'  => 'abonnement-pour-iptv-6-mois',
            'abonnement-iptv-12-mois' => 'abonnement-iptv-quebec-12-mois',

            // The French converter, renamed the same day it was published so it
            // leads with the keyword. Almost certainly never crawled at the old
            // slug, but the entry costs nothing and the alternative is guessing.
            'convertisseur-m3u-playlist-iptv' => 'iptv-m3u-playlist-convertisseur-xtream',
        );
    }
}

/**
 * The orphaned WooCommerce store pages.
 *
 * Checkout moved to app.quebeciptv.co and WooCommerce was uninstalled, but the
 * three store templates and the pages assigned to them stayed behind. They are
 * not merely unused — they are fatal: template-store-checkout.php calls
 * is_wc_endpoint_url() on its twelfth line, before the class_exists('WooCommerce')
 * guard further down, so with the plugin gone the page dies with a 500 before it
 * renders anything.
 *
 * That would be tolerable if nothing pointed at them. But /en/checkout is listed
 * in the sitemap, so the one page on this site that Google is explicitly invited
 * to crawl and cannot render is a critical error page.
 *
 * Redirecting rather than repairing the templates: there is no WooCommerce to
 * repair them against, every buy button on the site already goes to the panel,
 * and a checkout that cannot take a payment is not worth keeping alive. A 301
 * also gets the URL dropped from the index, which the 500 never would.
 *
 * Guarded on WooCommerce being absent, so reinstalling the plugin restores the
 * old behaviour rather than leaving a mystery redirect behind.
 */
add_action('template_redirect', function () {
    if (class_exists('WooCommerce')) {
        return;
    }

    if (is_admin() || wp_doing_ajax() || is_feed()) {
        return;
    }

    if (!is_page_template(array(
        'template-store-checkout.php',
        'template-store-cart.php',
        'template-store-shop.php',
    ))) {
        return;
    }

    $target = function_exists('iptv_config')
        ? iptv_config('checkout_base_url', 'https://app.quebeciptv.co/checkout')
        : 'https://app.quebeciptv.co/checkout';

    // wp_redirect, not wp_safe_redirect: the destination is deliberately a
    // different host, which the safe variant would refuse and silently turn into
    // a redirect to the home page.
    wp_redirect($target, 301);
    exit;
}, 1);

/**
 * Keep them out of the sitemap too.
 *
 * A 301 stops the 500 reaching anyone, but Rank Math would go on listing the URL
 * as a page worth crawling. Excluding it is the difference between a redirect
 * Google follows once and a redirect it keeps rechecking.
 */
add_filter('rank_math/sitemap/entry', function ($url, $type, $object) {
    if (class_exists('WooCommerce') || empty($url['loc'])) {
        return $url;
    }

    if ($type !== 'post' || empty($object->ID)) {
        return $url;
    }

    $template = get_post_meta($object->ID, '_wp_page_template', true);

    $store = array(
        'template-store-checkout.php',
        'template-store-cart.php',
        'template-store-shop.php',
    );

    return in_array($template, $store, true) ? false : $url;
}, 10, 3);

/**
 * Send a request for a retired slug to its replacement.
 *
 * Only ever acts on a 404 — a live page that happens to share a slug with a map
 * key must keep working, and gating on is_404() makes that impossible to get
 * wrong. The new URL is resolved through the page itself rather than
 * string-substituted into the request URI, so the redirect lands on the correct
 * language's permalink even if the two languages use different prefixes.
 */
add_action('template_redirect', function () {
    if (!is_404()) {
        return;
    }

    $path = wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

    if (!$path) {
        return;
    }

    // The last non-empty path segment is the slug that was asked for.
    $segments = array_values(array_filter(explode('/', $path), 'strlen'));

    if (empty($segments)) {
        return;
    }

    $requested = end($segments);
    $map = iptv_legacy_slug_map();

    if (!isset($map[$requested])) {
        return;
    }

    $page = get_page_by_path($map[$requested]);

    // get_page_by_path() is filtered by Polylang to the current language. On a
    // 404 the current language is whatever the URL implied, which is the
    // language we want — but fall back to a direct lookup rather than giving up
    // if that misses, because a dead end here means a 404 on a page we know the
    // replacement for.
    if (!$page && function_exists('iptv_provisioner_page_by_slug')) {
        $id = iptv_provisioner_page_by_slug($map[$requested]);
        $page = $id ? get_post($id) : null;
    }

    if (!$page || $page->post_status !== 'publish') {
        return;
    }

    wp_safe_redirect(get_permalink($page->ID), 301);
    exit;
}, 1);
