<?php
/**
 * M3U converter — Rank Math content analysis
 *
 * Same problem plan/inc/plan-seo.php solves, in a smaller form. The article
 * itself lives in post_content and Rank Math reads it fine, but the hero, the
 * converter and the FAQ — roughly 700 words, and the part of the page carrying
 * the keyword most densely — come from m3u-strings.php and the template. This
 * hands Rank Math that copy too.
 *
 * Built from the same arrays the sections render from rather than by running
 * the template, and assembled in the *page's* language rather than the
 * request's: the analysis runs from wp-admin, which is English, while the page
 * being analysed may be French.
 *
 * @package Quebec_IPTV
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('iptv_m3u_is_m3u_page')) {
    /**
     * @param int|WP_Post|null $post
     * @return bool
     */
    function iptv_m3u_is_m3u_page($post)
    {
        $post = get_post($post);

        return $post
            && $post->post_type === 'page'
            && get_post_meta($post->ID, '_wp_page_template', true) === 'template-m3u.php';
    }
}

if (!function_exists('iptv_m3u_analysis_digest')) {
    /**
     * The template-driven copy of the converter page, as HTML, in its language.
     *
     * Headings come out as real <h2>/<h3> so the "focus keyword in subheading"
     * test sees the headings the page actually renders.
     *
     * @param int $post_id
     * @return string
     */
    function iptv_m3u_analysis_digest($post_id)
    {
        $lang = function_exists('pll_get_post_language')
            ? pll_get_post_language($post_id, 'slug')
            : '';

        $bundled = ($lang && $lang !== 'en' && function_exists('iptv_m3u_translations'))
            ? iptv_m3u_translations($lang)
            : array();

        // Same order m3u_str() uses, minus the Polylang lookup: this runs in an
        // admin request, where the current language is not the page's.
        $t = function ($english) use ($bundled) {
            return isset($bundled[$english]) && $bundled[$english] !== ''
                ? $bundled[$english]
                : $english;
        };

        // Prefer what the editor has actually put in the fields, then the same
        // defaults the sections would have rendered.
        $field = function ($key, $fallback = '') use ($post_id) {
            $value = function_exists('get_field') ? get_field($key, $post_id) : '';
            return ($value === null || $value === '' || $value === false) ? $fallback : $value;
        };

        $out = array();

        // ── Hero ─────────────────────────────────────────────────────────────
        $out[] = '<p>' . $field('m3u_intro', $t('Paste an M3U playlist URL and get the Xtream Codes server, username and password out of it — instantly, and without the URL ever leaving your browser.')) . '</p>';

        // ── Converter ────────────────────────────────────────────────────────
        $out[] = '<h2>' . $field('m3u_tool_title', $t('M3U to Xtream Codes converter')) . '</h2>';
        $out[] = '<p>' . $field('m3u_tool_subtitle', $t('Works with both URL shapes: query parameters and path-based playlists.')) . '</p>';
        $out[] = '<p>' . $t('Nothing you paste leaves your browser. The conversion runs entirely on this page — no request is made, and nothing is stored or logged.') . '</p>';

        // ── FAQ ──────────────────────────────────────────────────────────────
        $rows = function_exists('get_field') ? get_field('m3u_faq', $post_id) : null;

        $out[] = '<h2>' . $field('m3u_faq_title', $t('Questions about M3U playlists')) . '</h2>';

        if (is_array($rows) && !empty($rows)) {
            foreach ($rows as $row) {
                if (!empty($row['question'])) {
                    $out[] = '<h3>' . $row['question'] . '</h3>';
                    $out[] = '<p>' . (isset($row['answer']) ? $row['answer'] : '') . '</p>';
                }
            }
        } else {
            foreach (iptv_m3u_faq_defaults() as $row) {
                $out[] = '<h3>' . $t($row['q']) . '</h3>';
                $out[] = '<p>' . $t($row['a']) . '</p>';
            }
        }

        // ── Closing band ─────────────────────────────────────────────────────
        $out[] = '<h2>' . $field('m3u_final_title', $t('A playlist is only as good as the service behind it')) . '</h2>';
        $out[] = '<p>' . $field('m3u_final_text', $t('Quebec IPTV gives you both an M3U playlist and Xtream Codes credentials, on 40,000+ channels and 200,000+ films. Try it for 24 hours without a card.')) . '</p>';

        return implode("\n", $out);
    }
}

/**
 * Give Rank Math the whole page, not just the article body.
 *
 * Appended rather than substituted, so the article still leads — which is what
 * the "keyword at the beginning of the content" test measures.
 */
add_filter('rank_math/researches/post_content', function ($content, $post = null) {
    $post = get_post($post);

    if (!$post || !iptv_m3u_is_m3u_page($post)) {
        return $content;
    }

    return $content . "\n" . iptv_m3u_analysis_digest($post->ID);
}, 10, 2);

/**
 * Let the article's two references keep their followed links.
 *
 * plan-seo.php already carries the un-nofollow filter and the whitelist behind
 * an iptv_plan_followed_domains filter; this adds the two domains the converter
 * article cites rather than reimplementing any of it. The article's original
 * markup asked for rel="dofollow", which is not a real rel value and did
 * nothing — Rank Math's nofollow-everything setting rewrote both links anyway.
 */
add_filter('iptv_plan_followed_domains', function ($domains) {
    $domains[] = 'statista.com';
    $domains[] = 'developer.apple.com';

    return $domains;
});

/**
 * ...and make that filter actually fire on this template.
 *
 * The the_content filter in plan-seo.php is gated on iptv_plan_is_plan_page(),
 * so without this the whitelist above would be a list nothing consults. Rather
 * than widen that function — its name would then be a lie — the same rule is
 * applied here for M3U pages only.
 */
add_filter('the_content', function ($content) {
    if (!iptv_m3u_is_m3u_page(get_post()) || strpos($content, 'nofollow') === false) {
        return $content;
    }

    if (!function_exists('iptv_plan_followed_domains')) {
        return $content;
    }

    $domains = iptv_plan_followed_domains();

    return preg_replace_callback('/<a\b[^>]*>/i', function ($m) use ($domains) {
        $tag = $m[0];

        if (!preg_match('/href=["\']([^"\']+)["\']/i', $tag, $href)) {
            return $tag;
        }

        $host = parse_url($href[1], PHP_URL_HOST);
        if (!$host) {
            return $tag;
        }

        $allowed = false;
        foreach ($domains as $domain) {
            if ($host === $domain || substr($host, -strlen('.' . $domain)) === '.' . $domain) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            return $tag;
        }

        // Strip only the nofollow token; noopener and target are about safety,
        // not SEO, and stay.
        $tag = preg_replace_callback('/\srel=["\']([^"\']*)["\']/i', function ($r) {
            $rels = array_values(array_filter(
                preg_split('/\s+/', $r[1]),
                function ($v) { return $v !== '' && strtolower($v) !== 'nofollow'; }
            ));
            return $rels ? ' rel="' . implode(' ', $rels) . '"' : '';
        }, $tag);

        return preg_replace('/\s{2,}/', ' ', $tag);
    }, $content);
}, 99);
