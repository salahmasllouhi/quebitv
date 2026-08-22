<?php
/**
 * Reseller pages — one-time provisioning
 *
 * One page per language through the shared provisioner, created as drafts.
 *
 * @package Quebec_IPTV
 */

if (!defined('ABSPATH')) {
    exit;
}

define('RESELLER_PAGES_BUILD', 1);

if (!function_exists('iptv_reseller_page_definitions')) {
    /**
     * @return array<string,array{title:string,slug:string}>
     */
    function iptv_reseller_page_definitions()
    {
        return array(
            'en' => array(
                'title' => 'IPTV Provider Reseller Panel: Sell Under Your Own Name',
                'slug'  => 'iptv-provider-reseller-panel',
            ),
            'fr' => array(
                'title' => 'Fournisseur IPTV Quebec : panneau revendeur',
                'slug'  => 'fournisseur-iptv-quebec-revendeur',
            ),
        );
    }
}

if (!function_exists('iptv_reseller_fill_page')) {
    /**
     * Seed the French page's ACF copy. Only ever fills an empty field.
     *
     * @param int    $post_id
     * @param string $lang
     * @param array  $def
     * @return int
     */
    function iptv_reseller_fill_page($post_id, $lang, $def)
    {
        if ($lang === 'en' || !function_exists('update_field')) {
            return 0;
        }

        $t = iptv_reseller_translations($lang);

        if (empty($t)) {
            return 0;
        }

        $tr = function ($english) use ($t) {
            return isset($t[$english]) && $t[$english] !== '' ? $t[$english] : '';
        };

        $values = array(
            'reseller_eyebrow'        => $tr('Reseller panel'),
            'reseller_subline'        => $tr('Sell IPTV under your own name, at your own prices, from a panel that creates lines in seconds. Credits never expire and there is no monthly commitment.'),
            'reseller_cta_text'       => $tr('Apply for a panel'),
            'reseller_panel_title'    => $tr('What the panel does'),
            'reseller_panel_subtitle' => $tr('Everything you need to run subscriptions as a business rather than as a favour.'),
            'reseller_packs_title'    => $tr('Credit packs'),
            'reseller_packs_subtitle' => $tr('One credit is one month of one subscription. Larger packs cost less per credit, and nothing expires.'),
            'reseller_steps_title'    => $tr('How it works'),
            'plan_faq_title'          => $tr('Reseller questions'),
            'plan_final_title'        => $tr('Start selling this week'),
            'plan_final_text'         => $tr('Apply today, get your panel the same day, and set your own prices from the first line you create.'),
        );

        $features = array();
        foreach (iptv_reseller_panel_defaults() as $card) {
            if ($tr($card['title'])) {
                $features[] = array('title' => $tr($card['title']), 'text' => $tr($card['text']));
            }
        }
        if ($features) {
            $values['reseller_panel_features'] = $features;
        }

        $steps = array();
        foreach (iptv_reseller_steps_defaults() as $step) {
            if ($tr($step['title'])) {
                $steps[] = array('title' => $tr($step['title']), 'text' => $tr($step['text']));
            }
        }
        if ($steps) {
            $values['reseller_steps'] = $steps;
        }

        $faq = array();
        foreach (iptv_reseller_faq_defaults() as $row) {
            $q = $tr($row['q']);
            $a = $tr($row['a']);
            if ($q && $a) {
                $faq[] = array('question' => $q, 'answer' => $a);
            }
        }
        if ($faq) {
            $values['plan_faq'] = $faq;
        }

        // The packs themselves are deliberately not seeded per language. They
        // are numbers, not copy — writing them into the French page would mean
        // a price change had to be made twice, and the bundled defaults already
        // render correctly in both.

        $written = 0;

        foreach ($values as $name => $value) {
            if ($value === '' || $value === array()) {
                continue;
            }

            $existing = get_field($name, $post_id);
            if ($existing !== null && $existing !== '' && $existing !== false && $existing !== array()) {
                continue;
            }

            update_field($name, $value, $post_id);
            $written++;
        }

        return $written;
    }
}

add_action('init', function () {
    iptv_provision_once('reseller', RESELLER_PAGES_BUILD, function () {
        iptv_provision_pages(
            'reseller',
            RESELLER_PAGES_BUILD,
            iptv_reseller_page_definitions(),
            'template-reseller.php',
            'iptv_reseller_fill_page'
        );
    });
}, 20);
