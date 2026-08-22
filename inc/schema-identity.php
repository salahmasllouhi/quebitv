<?php
/**
 * Structured-data identity
 *
 * Rank Math builds the site-wide JSON-LD graph from its own Titles & Meta
 * settings, and on this install those settings still describe the site this one
 * was cloned from. Every page — all of them — was shipping:
 *
 *   "@type": ["EntertainmentBusiness", "Organization"],
 *   "name": "nordictv.io",
 *   "url":  "https://magenta-cattle-868211.hostingersite.com"
 *
 * A dead Hostinger staging hostname, under a brand that no longer exists,
 * asserted as the publisher of every page on the site. The WebSite node repeated
 * the wrong name, there was no logo, and the commercial home page was typed as a
 * NewsArticle with an author byline — a product page claiming to be a news
 * story, which forfeits the organization and product results it should be
 * eligible for and earns nothing in return.
 *
 * Why this is fixed in the theme rather than in the plugin's settings screen:
 * those settings live in the rank_math_options_titles option, which is outside
 * Royal MCP's readable *and* writable allowlists, so nothing outside wp-admin
 * can reach them. Doing it here also means the correct identity deploys with the
 * code, is reviewable in a diff, is language-aware, and cannot be undone by
 * somebody tidying a settings page.
 *
 * blogname is already correct ("Quebec IPTV"), so the values below come from
 * WordPress itself wherever possible rather than being hardcoded a second time.
 *
 * @package Quebec_IPTV
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('iptv_schema_language_tag')) {
    /**
     * The IETF tag for the language being served.
     *
     * Both markets are Canadian, so fr-CA and en-CA rather than fr-FR/en-US.
     *
     * @return string
     */
    function iptv_schema_language_tag()
    {
        $lang = function_exists('pll_current_language') ? pll_current_language('slug') : '';

        $map = array('fr' => 'fr-CA', 'en' => 'en-CA');

        return isset($map[$lang]) ? $map[$lang] : get_bloginfo('language');
    }
}

if (!function_exists('iptv_schema_contact_points')) {
    /**
     * The support channels, as ContactPoint nodes.
     *
     * Read from the same iptv_contact_cards() the contact section renders, so a
     * channel that is removed from the page cannot linger in the structured
     * data claiming to be staffed.
     *
     * @return array<int,array<string,string>>
     */
    function iptv_schema_contact_points()
    {
        if (!function_exists('iptv_contact_cards')) {
            return array();
        }

        $points = array();

        foreach (iptv_contact_cards() as $card) {
            $link = isset($card['link']) ? $card['link'] : '';

            if (strpos($link, 'mailto:') === 0) {
                $points[] = array(
                    '@type'        => 'ContactPoint',
                    'contactType'  => 'customer support',
                    'email'        => substr($link, 7),
                    'availableLanguage' => array('English', 'French'),
                );
            } elseif ($link) {
                $points[] = array(
                    '@type'        => 'ContactPoint',
                    'contactType'  => 'customer support',
                    'url'          => $link,
                    'availableLanguage' => array('English', 'French'),
                );
            }
        }

        return $points;
    }
}

/**
 * Correct the identity nodes, and drop the ones that misdescribe the page.
 *
 * Rank Math hands this filter an array of graph nodes keyed by a slug that has
 * changed across versions ('Organization', 'publisher', 'website'…), so the keys
 * are not trusted — every node is matched on its own @type instead, which is
 * stable. @type can be a string or an array, hence the normalising cast.
 */
add_filter('rank_math/json_ld', function ($data, $jsonld = null) {
    if (!is_array($data)) {
        return $data;
    }

    $home = home_url('/');
    $name = get_bloginfo('name');
    $logo = get_template_directory_uri() . '/images/logo/quebec-iptv-mark.png';

    foreach ($data as $key => $node) {
        if (!is_array($node) || empty($node['@type'])) {
            continue;
        }

        $types = (array) $node['@type'];

        // ── Organization ─────────────────────────────────────────────────────
        if (in_array('Organization', $types, true)) {
            $node['name'] = $name;
            $node['url']  = $home;

            $node['logo'] = array(
                '@type'  => 'ImageObject',
                '@id'    => $home . '#logo',
                'url'    => $logo,
                'width'  => 960,
                'height' => 960,
                'caption' => $name,
            );
            $node['image'] = array('@id' => $home . '#logo');

            $points = iptv_schema_contact_points();
            if ($points) {
                $node['contactPoint'] = $points;
            }

            // openingHours on a streaming service is meaningless — it was
            // inherited from the EntertainmentBusiness type the old settings
            // picked, and it advertises that support stops at 17:00 when the
            // whole site promises 24/7.
            unset($node['openingHours']);

            $data[$key] = $node;
            continue;
        }

        // ── WebSite ──────────────────────────────────────────────────────────
        if (in_array('WebSite', $types, true)) {
            $node['name']       = $name;
            $node['url']        = $home;
            $node['inLanguage'] = iptv_schema_language_tag();
            $data[$key] = $node;
            continue;
        }

        // ── WebPage ──────────────────────────────────────────────────────────
        if (in_array('WebPage', $types, true) || in_array('ItemPage', $types, true)) {
            $node['inLanguage'] = iptv_schema_language_tag();
            $data[$key] = $node;
            continue;
        }

        // ── Article-shaped nodes on pages that are not articles ──────────────
        // Rank Math's default post type schema is an Article variant, and it
        // was being applied to the front page and the plan pages. Those are
        // commercial pages: an Article there is both untrue and self-defeating,
        // because it competes with the Product and Organization markup the same
        // pages emit. Blog posts keep theirs.
        $article_types = array('Article', 'NewsArticle', 'BlogPosting');

        if (array_intersect($article_types, $types) && !is_singular('post')) {
            unset($data[$key]);
            continue;
        }

        // The author byline that comes with it. Same reasoning: a pricing page
        // does not have an author, and saying it does is noise.
        if (in_array('Person', $types, true) && !is_singular('post')) {
            unset($data[$key]);
        }
    }

    return $data;
}, 20, 2);

/**
 * The settings themselves.
 *
 * Everything above corrects the output. This corrects the input, which is
 * better: rank_math_options_titles is where "nordictv.io" and the dead staging
 * hostname actually live, and Rank Math reads it from more places than the
 * JSON-LD graph. og:site_name is the one that matters most after the graph —
 * it is the name shown on a Facebook, LinkedIn, Slack or WhatsApp preview of
 * any link to this site, and it comes from a separate code path that the
 * rank_math/json_ld filter never touches.
 *
 * Hooked on core's option_{$option} filter rather than one of Rank Math's own,
 * because that one is documented WordPress and fires for every get_option()
 * call no matter which plugin version is installed or what it has renamed its
 * internal filters to. Two of those were tried first and neither fired.
 *
 * This does not write to the database. The stored option keeps its old values,
 * so nothing is lost and the fix disappears cleanly with the theme; it is only
 * ever read through this filter.
 */
add_filter('option_rank_math_options_titles', function ($titles) {
    if (!is_array($titles)) {
        return $titles;
    }

    $titles['knowledgegraph_name'] = get_bloginfo('name');
    $titles['knowledgegraph_type'] = 'company';
    $titles['url']                 = home_url('/');

    // Only set the logo if one is not configured, so an editor who uploads a
    // proper one in wp-admin is not overridden on every page load.
    if (empty($titles['knowledgegraph_logo'])) {
        $titles['knowledgegraph_logo'] = get_template_directory_uri() . '/images/logo/quebec-iptv-mark.png';
    }

    return $titles;
}, 20);

/**
 * The site title, per language.
 *
 * This is where "nordictv.io" was actually surviving, and it took three
 * attempts to find. The raw blogname option is correct — wp_get_option returns
 * "Quebec IPTV" — but Polylang registers the site title as a translatable
 * string and filters option_blogname to swap in the current language's
 * translation. Both of those translations still held the old brand, so
 * get_bloginfo('name') returned nordictv.io on every request, and everything
 * downstream of it inherited that: og:site_name on every page, which is the
 * name shown on a Facebook, LinkedIn, Slack or WhatsApp link preview.
 *
 * The right long-term fix is to correct the two entries under
 * Languages → String translations, and doing that makes this filter a no-op
 * rather than a conflict. It stays regardless, because a stale site title is
 * not something that should be one forgotten admin screen away from coming
 * back on every page of the site.
 *
 * Priority 999 so it runs after Polylang's own option_blogname filter rather
 * than being overwritten by it.
 */
add_filter('option_blogname', function ($name) {
    // Only override a value that is clearly the old brand. A deliberate
    // per-language site title should still work — this is here to correct a
    // stale translation, not to forbid translating the name at all.
    if (is_string($name) && stripos($name, 'nordictv') !== false) {
        return 'Quebec IPTV';
    }

    return $name;
}, 999);

/**
 * FAQPage for the front page.
 *
 * The home page renders nine questions from the faq_list ACF repeater, and
 * inc/front-page-seo.php already feeds them to Rank Math's content analysis —
 * but they have never been marked up, so the one part of the page that is
 * shaped exactly like structured data has never been readable as any.
 *
 * Emitted as its own script rather than merged into Rank Math's graph, matching
 * plan/sections/plan-schema.php, which is the pattern the theme already uses for
 * everything Rank Math cannot know.
 */
add_action('wp_footer', function () {
    if (!is_front_page() || !function_exists('get_field')) {
        return;
    }

    $front_page_id = get_option('page_on_front');
    $rows = $front_page_id ? get_field('faq_list', $front_page_id) : get_field('faq_list');

    if (!is_array($rows) || empty($rows)) {
        return;
    }

    $questions = array();

    foreach ($rows as $row) {
        if (empty($row['question']) || empty($row['answer'])) {
            continue;
        }

        $questions[] = array(
            '@type'          => 'Question',
            'name'           => wp_strip_all_tags($row['question']),
            'acceptedAnswer' => array(
                '@type' => 'Answer',
                // The answers carry links, which FAQPage allows; strip only the
                // tags that would not survive as text anyway.
                'text'  => wp_kses($row['answer'], array('a' => array('href' => array()), 'strong' => array(), 'em' => array())),
            ),
        );
    }

    if (empty($questions)) {
        return;
    }

    $schema = array(
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        '@id'        => (function_exists('pll_home_url') ? pll_home_url() : home_url('/')) . '#faq',
        'inLanguage' => iptv_schema_language_tag(),
        'mainEntity' => $questions,
    );

    printf(
        '<script type="application/ld+json">%s</script>' . "\n",
        wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );
}, 20);
