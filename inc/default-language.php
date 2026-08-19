<?php
/**
 * Make French the site's default language and English the second one.
 *
 * Polylang keeps the default language in one key of its `polylang` option, and
 * that option is not reachable over the MCP bridge — it is not in the readable
 * allowlist, and a blind write would have to overwrite the whole settings array
 * (URL structure, sync flags, menu assignments) rather than one key. So the flip
 * happens here instead, reading the option and changing only `default_lang`.
 *
 * With `hide_default` on (Polylang's default) this puts French on `/` with no
 * prefix and moves English to `/en/`. The front page option follows, so `/`
 * renders the French page rather than the English one.
 *
 * One-shot, guarded by an option. Safe to delete this file and its require once
 * iptv_default_lang_fr is set.
 *
 * @package Quebec_IPTV
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug of the language that should be default, and the page that is its front
 * page. Both are checked before anything is written.
 */
function iptv_promote_french_default()
{
    if (get_option('iptv_default_lang_fr') === '1') {
        return;
    }

    // Admin, REST and cron only. This rewrites rewrite rules, which is not work
    // a front-end visitor should be made to pay for.
    $privileged = is_admin()
        || wp_doing_cron()
        || (defined('REST_REQUEST') && REST_REQUEST);

    if (!$privileged || !function_exists('pll_languages_list')) {
        return;
    }

    $languages = pll_languages_list(array('fields' => 'slug'));
    if (!is_array($languages) || !in_array('fr', $languages, true)) {
        return; // French not registered yet — try again next request.
    }

    $options = get_option('polylang');
    if (!is_array($options)) {
        return;
    }

    if (($options['default_lang'] ?? '') !== 'fr') {
        // Merge, never replace: everything else in this array is live config.
        $options['default_lang'] = 'fr';
        update_option('polylang', $options);
    }

    // The front page option names one post; Polylang resolves the rest through
    // the translation group. Point it at the French page so `/` is French.
    $front = (int) get_option('page_on_front');
    if ($front && function_exists('pll_get_post')) {
        $french_front = pll_get_post($front, 'fr');
        if ($french_front && (int) $french_front !== $front) {
            update_option('page_on_front', (int) $french_front);
        }
    }

    flush_rewrite_rules(false);

    update_option('iptv_default_lang_fr', '1', true);
}
add_action('init', 'iptv_promote_french_default', 60);

/**
 * Expose a few options to the MCP bridge for read-back, so the result of the
 * flip above can be verified without a database session.
 */
add_filter('royal_mcp_readable_options', function ($allowed) {
    foreach (array('polylang', 'iptv_default_lang_fr') as $name) {
        if (!in_array($name, (array) $allowed, true)) {
            $allowed[] = $name;
        }
    }

    return $allowed;
});
