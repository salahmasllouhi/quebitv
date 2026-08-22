<?php
/**
 * Reseller page — Polylang string registration and copy
 *
 * Same arrangement as the plan, m3u and trial equivalents. The panel features
 * and the FAQ live here as data because reseller-schema.php reads the same
 * arrays.
 *
 * @package Quebec_IPTV
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('iptv_reseller_translations')) {
    /**
     * @param string $lang Polylang language slug.
     * @return array<string,string>
     */
    function iptv_reseller_translations($lang)
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

if (!function_exists('reseller_str')) {
    /**
     * @param string $default English default, which is also the lookup key.
     * @return string
     */
    function reseller_str($default)
    {
        if (function_exists('pll__')) {
            $translated = pll__($default);
            if ($translated !== $default) {
                return $translated;
            }
        }

        $lang = function_exists('pll_current_language') ? pll_current_language('slug') : '';

        if ($lang && $lang !== 'en') {
            $bundled = iptv_reseller_translations($lang);
            if (isset($bundled[$default]) && $bundled[$default] !== '') {
                return $bundled[$default];
            }
        }

        return $default;
    }
}

if (!function_exists('iptv_reseller_panel_defaults')) {
    /**
     * What the panel does — six cards.
     *
     * Written for somebody deciding whether to run a business on this, so each
     * one is a capability they would otherwise have to ask about.
     *
     * @return array<int,array{title:string,text:string}>
     */
    function iptv_reseller_panel_defaults()
    {
        return array(
            array(
                'title' => 'Create lines in seconds',
                'text'  => 'Generate a subscription, set its length and screen count, and hand over the credentials — without emailing anyone or waiting for approval.',
            ),
            array(
                'title' => 'Renew and extend in place',
                'text'  => 'Top up an existing line rather than issuing a new one, so your customer keeps the same login and never has to reconfigure their box.',
            ),
            array(
                'title' => 'Sub-resellers under you',
                'text'  => 'Give someone their own panel with their own credit balance. Their sales come out of your allocation and you keep the margin on top.',
            ),
            array(
                'title' => 'Every output format',
                'text'  => 'M3U, Xtream Codes and MAG portal from the same line, so you can sell to whatever hardware walks through the door.',
            ),
            array(
                'title' => 'Live usage and connections',
                'text'  => 'See who is watching, on how many devices and from where. Catching a shared account early is the difference between a warning and a refund.',
            ),
            array(
                'title' => 'Credits that do not expire',
                'text'  => 'Buy in the volume that suits your cash flow, not ours. Unused credits sit in the balance until you have a customer for them.',
            ),
        );
    }
}

if (!function_exists('iptv_reseller_steps_defaults')) {
    /**
     * How it works — four steps.
     *
     * @return array<int,array{title:string,text:string}>
     */
    function iptv_reseller_steps_defaults()
    {
        return array(
            array('title' => 'Apply', 'text'  => 'Tell us where you sell and roughly how many lines you expect to run. There is no fee and no exclusivity.'),
            array('title' => 'Get your panel', 'text'  => 'Your login arrives the same day, with the panel already pointed at our servers.'),
            array('title' => 'Load credits', 'text'  => 'Pick a pack. Larger packs cost less per credit, and the balance never expires.'),
            array('title' => 'Start selling', 'text'  => 'Create lines at whatever price you set. What you charge your customers is entirely yours.'),
        );
    }
}

if (!function_exists('iptv_reseller_faq_defaults')) {
    /**
     * @return array<int,array{q:string,a:string}>
     */
    function iptv_reseller_faq_defaults()
    {
        return array(
            array(
                'q' => 'What is a credit?',
                'a' => 'One credit is one month of one subscription. A twelve-month line for one customer costs twelve credits; three one-month lines cost three. It is the unit every panel in this market works in, which makes packs easy to compare.',
            ),
            array(
                'q' => 'Do credits expire?',
                'a' => 'No. They sit in your balance until you have a customer to use them on, so buying a larger pack for the better unit price does not put you on a clock.',
            ),
            array(
                'q' => 'What price can I charge my customers?',
                'a' => 'Whatever you decide. We do not set your retail price, publish a recommended one, or compete with you for your own customers. Your margin is the difference between your credit cost and what you charge.',
            ),
            array(
                'q' => 'Is there a minimum order or a monthly commitment?',
                'a' => 'No monthly commitment and no contract. The smallest pack is the minimum, and you buy the next one when you need it.',
            ),
            array(
                'q' => 'Can I set up sub-resellers?',
                'a' => 'Yes. You can create panels under yours, each with its own credit balance drawn from your allocation, and set what they pay per credit. Their customers are theirs to manage; you keep the margin.',
            ),
            array(
                'q' => 'What support do I get?',
                'a' => 'Direct support for you as a reseller, separate from the end-customer queue — because when your customer is waiting, you are the one holding the relationship. Server status and maintenance windows are announced before they happen.',
            ),
        );
    }
}

if (!function_exists('iptv_reseller_faq_items')) {
    /**
     * FAQ rows: the plan_faq ACF repeater when filled, otherwise the bundled
     * defaults translated.
     *
     * Named plan_faq, not reseller_faq, because the page reuses
     * plan/sections/plan-faq.php and that is the field it reads. The same
     * convention runs through the trial group.
     *
     * @return array<int,array{q:string,a:string}>
     */
    function iptv_reseller_faq_items()
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

        foreach (iptv_reseller_faq_defaults() as $row) {
            $items[] = array(
                'q' => reseller_str($row['q']),
                'a' => reseller_str($row['a']),
            );
        }

        return $items;
    }
}

add_action('init', function () {
    if (!function_exists('pll_register_string')) {
        return;
    }

    $group = 'Reseller Page';

    $register = function ($name, $string, $multiline = false) use ($group) {
        pll_register_string($name, $string, $group, $multiline);
    };

    // ── Hero ─────────────────────────────────────────────────────────────────
    $register('reseller_eyebrow', 'Reseller panel');
    $register('reseller_subline', 'Sell IPTV under your own name, at your own prices, from a panel that creates lines in seconds. Credits never expire and there is no monthly commitment.', true);
    $register('reseller_cta_text', 'Apply for a panel');
    $register('reseller_secondary_cta', 'See credit prices');
    $register('reseller_label', 'reseller panel');
    $register('reseller_from_label', 'From');
    $register('reseller_per_credit', '%s per credit');

    // ── Panel features ───────────────────────────────────────────────────────
    $register('reseller_panel_title', 'What the panel does');
    $register('reseller_panel_subtitle', 'Everything you need to run subscriptions as a business rather than as a favour.', true);

    foreach (iptv_reseller_panel_defaults() as $i => $card) {
        $n = $i + 1;
        $register("reseller_panel_{$n}_title", $card['title']);
        $register("reseller_panel_{$n}_text", $card['text'], true);
    }

    // ── Credit packs ─────────────────────────────────────────────────────────
    $register('reseller_packs_title', 'Credit packs');
    $register('reseller_packs_subtitle', 'One credit is one month of one subscription. Larger packs cost less per credit, and nothing expires.', true);
    $register('reseller_credits_label', '%d credits');
    $register('reseller_pack_cta', 'Buy this pack');
    $register('reseller_popular', 'Most popular');

    // ── Steps ────────────────────────────────────────────────────────────────
    $register('reseller_steps_title', 'How it works');

    foreach (iptv_reseller_steps_defaults() as $i => $step) {
        $n = $i + 1;
        $register("reseller_step_{$n}_title", $step['title']);
        $register("reseller_step_{$n}_text", $step['text'], true);
    }

    // ── FAQ ──────────────────────────────────────────────────────────────────
    $register('reseller_faq_title', 'Reseller questions');

    foreach (iptv_reseller_faq_defaults() as $i => $row) {
        $n = $i + 1;
        $register("reseller_faq_{$n}_q", $row['q']);
        $register("reseller_faq_{$n}_a", $row['a'], true);
    }

    // ── Closing band ─────────────────────────────────────────────────────────
    $register('reseller_final_title', 'Start selling this week');
    $register('reseller_final_text', 'Apply today, get your panel the same day, and set your own prices from the first line you create.', true);

    // ── Schema ───────────────────────────────────────────────────────────────
    $register('reseller_schema_description', 'An IPTV reseller panel with instant line creation, sub-reseller accounts, M3U, Xtream Codes and MAG output, and credits that never expire. No monthly commitment.', true);
});
