<?php
/**
 * Trial page — Polylang string registration and copy
 *
 * Same arrangement as plan/inc/plan-strings.php and m3u/inc/m3u-strings.php.
 * The terms cards and the FAQ live here as data rather than inside the sections
 * that print them, because trial-schema.php reads the same arrays — so the
 * accordion and the rich result cannot describe different things.
 *
 * @package Quebec_IPTV
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('iptv_trial_translations')) {
    /**
     * Bundled copy for this template, english => translation.
     *
     * @param string $lang Polylang language slug.
     * @return array<string,string>
     */
    function iptv_trial_translations($lang)
    {
        static $cache = array();

        if (isset($cache[$lang])) {
            return $cache[$lang];
        }

        $file = __DIR__ . '/translations/' . $lang . '.php';
        $cache[$lang] = file_exists($file) ? (array) include $file : array();

        return $cache[$lang];
    }
}

if (!function_exists('trial_str')) {
    /**
     * The current language's version of a trial string.
     *
     * Polylang's string table first so an editor can always override what the
     * theme ships, then the bundled copy, then the English default.
     *
     * @param string $default English default, which is also the lookup key.
     * @return string
     */
    function trial_str($default)
    {
        if (function_exists('pll__')) {
            $translated = pll__($default);
            if ($translated !== $default) {
                return $translated;
            }
        }

        $lang = function_exists('pll_current_language') ? pll_current_language('slug') : '';

        if ($lang && $lang !== 'en') {
            $bundled = iptv_trial_translations($lang);
            if (isset($bundled[$default]) && $bundled[$default] !== '') {
                return $bundled[$default];
            }
        }

        return $default;
    }
}

if (!function_exists('iptv_trial_terms_defaults')) {
    /**
     * The four things people actually want to know before starting a trial.
     *
     * Every one of these is an objection: what does it cost me, how long do I
     * get, is it the real product or a cut-down one, and what is the catch.
     *
     * @return array<int,array{title:string,text:string}>
     */
    function iptv_trial_terms_defaults()
    {
        return array(
            array(
                'title' => 'No card, no payment details',
                'text'  => 'Nothing to enter and nothing to cancel later. We ask for an email address so we have somewhere to send the login, and that is the whole of it.',
            ),
            array(
                'title' => 'A full 24 hours',
                'text'  => 'The clock starts when your login arrives, not when you sign up. Long enough to watch an evening of television and put it in front of the people you actually live with.',
            ),
            array(
                'title' => 'The complete service',
                'text'  => 'Not a sample. All 40,000+ live channels, all 200,000+ films and series, the full guide and the same 4K servers a paying subscriber gets.',
            ),
            array(
                'title' => 'One screen at a time',
                'text'  => 'The only limit. Trials run on a single device — enough to judge the picture and the stability, which is what you are here to do.',
            ),
        );
    }
}

if (!function_exists('iptv_trial_faq_defaults')) {
    /**
     * @return array<int,array{q:string,a:string}>
     */
    function iptv_trial_faq_defaults()
    {
        return array(
            array(
                'q' => 'Is the free trial really free?',
                'a' => 'Yes. There is no card to enter, no payment details held and nothing to cancel. When the 24 hours end, the login simply stops working unless you choose to subscribe.',
            ),
            array(
                'q' => 'How quickly does the trial start?',
                'a' => 'Usually within a minute or two. Your login details arrive by email, and the 24 hours are counted from that message rather than from when you filled in the form — so a slow delivery does not eat your trial.',
            ),
            array(
                'q' => 'Do I get the whole channel list?',
                'a' => 'Yes. The trial is the full service, not a reduced version of it: every live channel, every film and series, the complete guide and the same servers. The only difference is that it runs on one screen and lasts a day.',
            ),
            array(
                'q' => 'Which devices can I use for the trial?',
                'a' => 'Any of them — smart TVs, Android TV, Apple TV, Fire Stick, iPhone, iPad, Android, Windows, Mac, set-top boxes, Chromecast, Roku and Kodi. Use whatever you would actually watch on, since that is what you are testing.',
            ),
            array(
                'q' => 'What happens when the 24 hours are up?',
                'a' => 'Nothing at all, unless you want it to. The trial login stops working and no charge is ever made. If you liked it, pick a plan and a fresh subscription is activated in about a minute.',
            ),
            array(
                'q' => 'Can I take a second trial?',
                'a' => 'The trial is one per household, so it is worth starting it on an evening when you can actually sit down and watch. If something went wrong technically and you did not get a fair look at it, contact support and we will sort it out.',
            ),
        );
    }
}

if (!function_exists('iptv_trial_faq_items')) {
    /**
     * FAQ rows for this page: the plan_faq ACF repeater when filled, otherwise
     * the bundled defaults translated.
     *
     * The field is named plan_faq rather than trial_faq deliberately — the page
     * reuses plan/sections/plan-faq.php, which reads $plan_faq_items, and the
     * trial ACF group uses the same field names as the plan group wherever a
     * reused section reads them. It looks like a copy-paste slip and is not.
     *
     * trial-schema.php reads the same array, so the accordion and the FAQPage
     * rich result cannot list different questions.
     *
     * @return array<int,array{q:string,a:string}>
     */
    function iptv_trial_faq_items()
    {
        $rows  = iptv_plan_field('plan_faq', array());
        $items = array();

        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (!empty($row['question'])) {
                    $items[] = array(
                        'q' => $row['question'],
                        'a' => isset($row['answer']) ? $row['answer'] : '',
                    );
                }
            }
        }

        if (!empty($items)) {
            return $items;
        }

        foreach (iptv_trial_faq_defaults() as $row) {
            $items[] = array(
                'q' => trial_str($row['q']),
                'a' => trial_str($row['a']),
            );
        }

        return $items;
    }
}

add_action('init', function () {
    if (!function_exists('pll_register_string')) {
        return;
    }

    $group = 'Trial Page';

    $register = function ($name, $string, $multiline = false) use ($group) {
        pll_register_string($name, $string, $group, $multiline);
    };

    // ── Hero ─────────────────────────────────────────────────────────────────
    $register('trial_eyebrow', 'Free trial');
    $register('trial_headline', '24-hour IPTV free trial');
    $register('trial_subline', 'The complete service for a full day — every channel, every film, every match. No card, nothing to cancel, and about a minute to set up.', true);
    $register('trial_cta_text', 'Start my free trial');
    $register('trial_point_1', 'No card required');
    $register('trial_point_2', 'Watching in about a minute');
    $register('trial_point_3', 'The full channel list');
    $register('trial_label', '24-hour trial');

    // ── Terms ────────────────────────────────────────────────────────────────
    $register('trial_terms_title', 'What the trial actually includes');
    $register('trial_terms_subtitle', 'The whole offer, with nothing held back for the small print.', true);

    foreach (iptv_trial_terms_defaults() as $i => $card) {
        $n = $i + 1;
        $register("trial_term_{$n}_title", $card['title']);
        $register("trial_term_{$n}_text", $card['text'], true);
    }

    // ── FAQ ──────────────────────────────────────────────────────────────────
    $register('trial_faq_title', 'Questions about the free trial');

    foreach (iptv_trial_faq_defaults() as $i => $row) {
        $n = $i + 1;
        $register("trial_faq_{$n}_q", $row['q']);
        $register("trial_faq_{$n}_a", $row['a'], true);
    }

    // ── Closing band ─────────────────────────────────────────────────────────
    $register('trial_final_title', 'Try it tonight');
    $register('trial_final_text', 'One email address, no card, and the full service for 24 hours. If it is not for you, do nothing and it ends by itself.', true);

    // ── Schema ───────────────────────────────────────────────────────────────
    $register('trial_schema_description', 'A free 24-hour trial of the complete Quebec IPTV service: 40,000+ live channels and 200,000+ films and series in 4K, on one screen, with no card required.', true);
});
