<?php
/**
 * Shared page provisioner
 *
 * Creating a page, assigning its Polylang language and linking its translations
 * are the three steps that cannot be done safely from outside WordPress. A
 * Polylang translation group is a term in the post_translations taxonomy whose
 * *description* holds a serialized lang => post_id map; anything that sanitises
 * a term description on the way past corrupts the group silently, which rules
 * out doing this over REST or the MCP. pll_set_post_language() and
 * pll_save_post_translations() are Polylang's own API for it and can only be
 * called from inside WordPress — hence a theme-side provisioner.
 *
 * plan/inc/plan-pages-setup.php got there first and still owns the plan pages.
 * It is deliberately not ported onto this: it matches candidate pages on
 * template *and* plan_months, because it manages four pages per language, while
 * everything here manages exactly one page per language. Merging the two would
 * mean a branch in the matching logic for the benefit of one caller, on code
 * whose failure mode is a silently broken translation group across eight live
 * pages. The shared shape is the sequence, and that is what this file captures.
 *
 * Usage, from a feature's own *-pages-setup.php:
 *
 *   iptv_provision_pages('m3u', M3U_PAGES_BUILD, array(
 *       'en' => array('title' => '…', 'slug' => '…'),
 *       'fr' => array('title' => '…', 'slug' => '…'),
 *   ), 'template-m3u.php', 'iptv_m3u_fill_page');
 *
 * @package Quebec_IPTV
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('iptv_provisioner_existing_pages')) {
    /**
     * Every page already using a template, indexed by language slug.
     *
     * Straight to postmeta rather than through WP_Query, for the reason
     * plan-pages-setup.php documents: Polylang filters on parse_query and
     * 'suppress_filters' does not stop it, so a normal query would narrow to
     * the current language — and would drop the pages with no language assigned
     * at all, which are exactly the ones this has to find and adopt.
     *
     * A page with no language yet is parked under '' and claimed by whichever
     * definition wants it. That is how page 326, the existing English M3U page,
     * gets adopted rather than duplicated.
     *
     * @param string $template Template filename, e.g. 'template-m3u.php'.
     * @return array<string,int> lang slug (or '') => post ID
     */
    function iptv_provisioner_existing_pages($template)
    {
        global $wpdb;

        $found = array();

        $ids = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta}
             WHERE meta_key = '_wp_page_template' AND meta_value = %s",
            $template
        ));

        foreach ($ids as $id) {
            $post = get_post((int) $id);

            if (!$post || $post->post_type !== 'page' || $post->post_status === 'trash') {
                continue;
            }

            $lang = function_exists('pll_get_post_language')
                ? pll_get_post_language($post->ID, 'slug')
                : '';

            $found[$lang ? $lang : ''] = $post->ID;
        }

        return $found;
    }
}

if (!function_exists('iptv_provisioner_page_by_slug')) {
    /**
     * A published or draft page with this exact slug, in any language.
     *
     * Needed because the pages this adopts do not yet carry the template it
     * matches on. Page 326, the existing English M3U converter, is rendered by
     * the fallback page.php and therefore has no _wp_page_template row at all —
     * so template matching alone would miss it and happily create a duplicate
     * at m3u-playlist-convert-your-m3u-url-2, orphaning the indexed URL.
     *
     * Deliberately not get_page_by_path(): Polylang filters it to the current
     * language, which during an init-time provisioning run is not reliably the
     * language of the page being looked for.
     *
     * @param string $slug
     * @return int Post ID, or 0.
     */
    function iptv_provisioner_page_by_slug($slug)
    {
        global $wpdb;

        $id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts}
             WHERE post_name = %s AND post_type = 'page'
               AND post_status IN ('publish', 'draft', 'pending', 'private')
             ORDER BY ID ASC LIMIT 1",
            $slug
        ));

        return $id ? (int) $id : 0;
    }
}

if (!function_exists('iptv_provision_pages')) {
    /**
     * Create anything missing, assign languages, link the translation group.
     *
     * Idempotent: it matches on template plus language before creating, so
     * re-running repairs rather than duplicates. New pages are created as
     * drafts — publishing to a live site is the site owner's call, not a
     * migration's, and that is the house rule plan-pages-setup.php set.
     *
     * @param string        $key         Option namespace, e.g. 'm3u'. Writes
     *                                   iptv_{key}_pages_built and _report.
     * @param int           $build       Bump to force a re-run.
     * @param array         $definitions [lang => ['title' => …, 'slug' => …]]
     * @param string        $template    Template filename to assign and match on.
     * @param callable|null $fill        function($post_id, $lang, $definition):int
     *                                   returning how many fields it wrote.
     * @return array{created:int,reused:int,linked:int,fields:int}
     */
    function iptv_provision_pages($key, $build, array $definitions, $template, $fill = null)
    {
        $existing = iptv_provisioner_existing_pages($template);
        $summary  = array('created' => 0, 'reused' => 0, 'linked' => 0, 'fields' => 0);
        $group    = array();

        // Which language may adopt a page that has the template but no language
        // assigned. English, because every page in that state predates this
        // provisioner and the site was English-only when it was made.
        $adopter = 'en';

        foreach ($definitions as $lang => $def) {

            $post_id = isset($existing[$lang]) ? $existing[$lang] : 0;

            if (!$post_id && $lang === $adopter && !empty($existing[''])) {
                $post_id = $existing[''];
                unset($existing['']);
            }

            // Last resort before creating: a page already sitting at this exact
            // slug. This is the path that claims page 326 — it has the right
            // URL and the right content but no template meta, so neither of the
            // lookups above can see it, and creating a second page at the same
            // slug would leave the indexed one behind on the old template.
            if (!$post_id && !empty($def['slug'])) {
                $candidate = iptv_provisioner_page_by_slug($def['slug']);

                if ($candidate) {
                    $candidate_lang = function_exists('pll_get_post_language')
                        ? pll_get_post_language($candidate, 'slug')
                        : '';

                    // Only adopt it if it belongs to this language, or to no
                    // language yet. Adopting another language's page would
                    // silently retag it and break its own translation group.
                    if ($candidate_lang === '' || $candidate_lang === $lang) {
                        $post_id = $candidate;
                    }
                }
            }

            if ($post_id) {
                $summary['reused']++;
            } else {
                $post_id = wp_insert_post(array(
                    'post_title'   => $def['title'],
                    'post_name'    => $def['slug'],
                    'post_type'    => 'page',
                    'post_status'  => 'draft',
                    'post_content' => '',
                    'meta_input'   => array(
                        '_wp_page_template' => $template,
                    ),
                ), true);

                if (is_wp_error($post_id) || !$post_id) {
                    continue;
                }

                $summary['created']++;
            }

            // An adopted page has the meta already; setting it again is cheap
            // and makes the reused path and the created path agree.
            update_post_meta($post_id, '_wp_page_template', $template);

            // Language before the fill callback: that callback writes copy in
            // this page's own language, and a page Polylang does not yet know
            // about confuses anything downstream that asks about it.
            if (function_exists('pll_set_post_language')) {
                pll_set_post_language($post_id, $lang);
            }

            if (is_callable($fill)) {
                $summary['fields'] += (int) call_user_func($fill, $post_id, $lang, $def);
            }

            $group[$lang] = $post_id;
        }

        // The step that cannot be done safely from outside WordPress.
        if (function_exists('pll_save_post_translations') && count($group) > 1) {
            pll_save_post_translations($group);
            $summary['linked'] = count($group);
        }

        update_option('iptv_' . $key . '_pages_report', $summary, false);

        return $summary;
    }
}

if (!function_exists('iptv_provision_once')) {
    /**
     * Run a provisioner once per build number, on the first request after deploy.
     *
     * The flag is written *before* the work rather than after, so two requests
     * arriving together cannot both start building. If a run fails part way,
     * bumping the build number re-runs it — and because everything matches on
     * existing pages first, the retry repairs instead of duplicating.
     *
     * @param string   $key   Option namespace.
     * @param int      $build Build number.
     * @param callable $run   The work.
     * @return void
     */
    function iptv_provision_once($key, $build, $run)
    {
        $option = 'iptv_' . $key . '_pages_built';

        if ((int) get_option($option) === (int) $build) {
            return;
        }

        // Polylang has to be up: without it every page would be created with no
        // language and the group could not be linked.
        if (!function_exists('pll_set_post_language') || !function_exists('pll_save_post_translations')) {
            return;
        }

        update_option($option, (int) $build, false);

        call_user_func($run);

        // LiteSpeed caches these pages the first time they are hit, so a
        // template or copy change shipping alongside a build bump would
        // otherwise stay invisible until the cache expired on its own.
        do_action('litespeed_purge_all');
    }
}
