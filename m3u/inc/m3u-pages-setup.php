<?php
/**
 * M3U converter — one-time provisioning
 *
 * Two jobs: convert the existing English page onto the new template, and create
 * its French translation.
 *
 * The English page is post 326, live at /en/m3u-playlist-convert-your-m3u-url
 * since early 2026. It keeps its ID, its slug, its URL, its Rank Math meta and
 * whatever backlinks it has earned — only _wp_page_template and post_content
 * change, so no redirect is needed and nothing that points at it breaks.
 *
 * Rewriting post_content is the only destructive step in this whole feature, so
 * it is guarded twice. The old body is copied to _m3u_legacy_content first, and
 * the rewrite only happens while the stored content still contains the broken
 * blob's fingerprint. Once someone edits the page in wp-admin that fingerprint
 * is gone and a re-run leaves their work alone.
 *
 * @package Quebec_IPTV
 */

if (!defined('ABSPATH')) {
    exit;
}

// Bump to re-run after changing the definitions below. Re-running is safe:
// existing pages are matched and reused, and the content guard means an edited
// page is never overwritten.
define('M3U_PAGES_BUILD', 1);

// A fragment of the old pasted stylesheet. Its presence in post_content is what
// identifies a page still carrying the broken blob — the CSS was never wrapped
// in a <style> tag, so this string is sitting in the body as literal text.
define('M3U_LEGACY_MARKER', '.m3u-tool-hero');

if (!function_exists('iptv_m3u_page_definitions')) {
    /**
     * Title and slug per language.
     *
     * The English slug is the live one and must not change — it is the URL the
     * page has been indexed at. Slugs are only applied at creation anyway, so
     * this table can never move an existing page's URL.
     *
     * @return array<string,array{title:string,slug:string}>
     */
    function iptv_m3u_page_definitions()
    {
        return array(
            'en' => array(
                'title' => 'M3U Editor: Convert Any M3U Playlist to Xtream Codes',
                'slug'  => 'm3u-playlist-convert-your-m3u-url',
            ),
            'fr' => array(
                'title' => 'Convertisseur de liste de lecture M3U IPTV vers Xtream',
                'slug'  => 'convertisseur-m3u-playlist-iptv',
            ),
        );
    }
}

if (!function_exists('iptv_m3u_fill_page')) {
    /**
     * Seed one converter page: body copy, then the French ACF fields.
     *
     * @param int    $post_id
     * @param string $lang Polylang slug.
     * @param array  $def  The definition row.
     * @return int Number of things written, for the report.
     */
    function iptv_m3u_fill_page($post_id, $lang, $def)
    {
        $written = 0;

        // ── Body ─────────────────────────────────────────────────────────────
        $seed_file = __DIR__ . '/content/' . $lang . '.php';

        if (file_exists($seed_file)) {
            $post = get_post($post_id);
            $current = $post ? $post->post_content : '';

            $is_empty  = trim(wp_strip_all_tags($current)) === '';
            $is_legacy = strpos($current, M3U_LEGACY_MARKER) !== false;

            if ($is_empty || $is_legacy) {
                if ($is_legacy) {
                    // Keep the old body before replacing it. This is recoverable
                    // from the page editor with a copy and paste, without anyone
                    // having to restore a database backup to see what was there.
                    update_post_meta($post_id, '_m3u_legacy_content', $current);
                }

                wp_update_post(array(
                    'ID'           => $post_id,
                    'post_content' => (string) include $seed_file,
                ));

                $written++;
            }
        }

        // ── Rank Math ────────────────────────────────────────────────────────
        // The English page's focus keyword carries a zero-width space after
        // "m3u playlist" — an invisible character that has been quietly failing
        // every keyword test on the page, because Rank Math compares it against
        // copy that contains the visible string and nothing else. Strip it on
        // both languages; there is no reason for U+200B to be in a keyword.
        $keyword = get_post_meta($post_id, 'rank_math_focus_keyword', true);

        if (is_string($keyword) && $keyword !== '') {
            $cleaned = str_replace("\xE2\x80\x8B", '', $keyword);
            if ($cleaned !== $keyword) {
                update_post_meta($post_id, 'rank_math_focus_keyword', $cleaned);
                $written++;
            }
        }

        // ── French ACF copy ──────────────────────────────────────────────────
        // The template already renders correctly from the bundled translations,
        // so this is about editability: with the fields filled, the French copy
        // is editable in the page editor by someone who does not touch code.
        //
        // Only ever fills an *empty* field — a re-run must not overwrite wording
        // somebody has edited in wp-admin, which is the entire point of putting
        // it in ACF.
        if ($lang === 'en' || !function_exists('update_field')) {
            return $written;
        }

        $t = iptv_m3u_translations($lang);

        if (empty($t)) {
            return $written;
        }

        // Translation or nothing. Writing English into a French field would be
        // worse than leaving it empty, because an empty field still falls
        // through to the bundled copy at render time.
        $tr = function ($english) use ($t) {
            return isset($t[$english]) && $t[$english] !== '' ? $t[$english] : '';
        };

        $values = array(
            'm3u_eyebrow'       => $tr('Free tool'),
            'm3u_intro'         => $tr('Paste an M3U playlist URL and get the Xtream Codes server, username and password out of it — instantly, and without the URL ever leaving your browser.'),
            'm3u_hero_cta'      => $tr('Open the converter'),
            'm3u_tool_title'    => $tr('M3U to Xtream Codes converter'),
            'm3u_tool_subtitle' => $tr('Works with both URL shapes: query parameters and path-based playlists.'),
            'm3u_faq_title'     => $tr('Questions about M3U playlists'),
            'm3u_final_title'   => $tr('A playlist is only as good as the service behind it'),
            'm3u_final_text'    => $tr('Quebec IPTV gives you both an M3U playlist and Xtream Codes credentials, on 40,000+ channels and 200,000+ films. Try it for 24 hours without a card.'),
        );

        $faq = array();
        foreach (iptv_m3u_faq_defaults() as $row) {
            $q = $tr($row['q']);
            $a = $tr($row['a']);
            if ($q && $a) {
                $faq[] = array('question' => $q, 'answer' => $a);
            }
        }
        if ($faq) {
            $values['m3u_faq'] = $faq;
        }

        foreach ($values as $name => $value) {
            if ($value === '' || $value === array()) {
                continue;
            }

            $existing = get_field($name, $post_id);
            if ($existing !== null && $existing !== '' && $existing !== false && $existing !== array()) {
                continue; // edited in wp-admin — leave it alone
            }

            update_field($name, $value, $post_id);
            $written++;
        }

        return $written;
    }
}

add_action('init', function () {
    iptv_provision_once('m3u', M3U_PAGES_BUILD, function () {
        iptv_provision_pages(
            'm3u',
            M3U_PAGES_BUILD,
            iptv_m3u_page_definitions(),
            'template-m3u.php',
            'iptv_m3u_fill_page'
        );
    });
}, 20);
