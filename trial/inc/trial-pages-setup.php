<?php
/**
 * Trial pages — one-time provisioning
 *
 * One page per language, linked as translations of each other, through the
 * shared provisioner in inc/page-provisioner.php.
 *
 * Created as drafts, per the house rule plan-pages-setup.php set: publishing to
 * a live site is the site owner's call, not a migration's.
 *
 * @package Quebec_IPTV
 */

if (!defined('ABSPATH')) {
    exit;
}

define('TRIAL_PAGES_BUILD', 1);

if (!function_exists('iptv_trial_page_definitions')) {
    /**
     * Title and slug per language. Slugs are only applied at creation, so
     * editing this table can never move a live URL.
     *
     * @return array<string,array{title:string,slug:string}>
     */
    function iptv_trial_page_definitions()
    {
        return array(
            'en' => array(
                'title' => 'IPTV Free Trial: 24 Hours, No Card Required',
                'slug'  => '24-hour-iptv-free-trial',
            ),
            'fr' => array(
                'title' => 'Essai IPTV gratuit : 24 heures, sans carte',
                'slug'  => 'essai-iptv-gratuit-24h',
            ),
        );
    }
}

if (!function_exists('iptv_trial_fill_page')) {
    /**
     * Seed the French page's ACF copy.
     *
     * Only ever fills an empty field, so a re-run cannot overwrite wording
     * somebody has edited in wp-admin — which is the whole reason for putting
     * the copy in ACF rather than leaving it in the translation file.
     *
     * @param int    $post_id
     * @param string $lang
     * @param array  $def
     * @return int
     */
    function iptv_trial_fill_page($post_id, $lang, $def)
    {
        if ($lang === 'en' || !function_exists('update_field')) {
            return 0;
        }

        $t = iptv_trial_translations($lang);

        if (empty($t)) {
            return 0;
        }

        // Translation or nothing: writing English into a French field is worse
        // than leaving it empty, because an empty field still falls through to
        // the bundled copy at render time.
        $tr = function ($english) use ($t) {
            return isset($t[$english]) && $t[$english] !== '' ? $t[$english] : '';
        };

        $values = array(
            'plan_eyebrow'         => $tr('Free trial'),
            'trial_subline'        => $tr('The complete service for a full day — every channel, every film, every match. No card, nothing to cancel, and about a minute to set up.'),
            'trial_cta_text'       => $tr('Start my free trial'),
            'trial_terms_title'    => $tr('What the trial actually includes'),
            'trial_terms_subtitle' => $tr('The whole offer, with nothing held back for the small print.'),
            'plan_faq_title'       => $tr('Questions about the free trial'),
            'plan_final_title'     => $tr('Try it tonight'),
            'plan_final_text'      => $tr('One email address, no card, and the full service for 24 hours. If it is not for you, do nothing and it ends by itself.'),
        );

        // The hero points, and the four terms cards.
        $points = array();
        foreach (array('No card required', 'Watching in about a minute', 'The full channel list') as $p) {
            if ($tr($p)) {
                $points[] = array('text' => $tr($p));
            }
        }
        if ($points) {
            $values['plan_hero_points'] = $points;
        }

        $terms = array();
        foreach (iptv_trial_terms_defaults() as $card) {
            if ($tr($card['title'])) {
                $terms[] = array('title' => $tr($card['title']), 'text' => $tr($card['text']));
            }
        }
        if ($terms) {
            $values['trial_terms'] = $terms;
        }

        $faq = array();
        foreach (iptv_trial_faq_defaults() as $row) {
            $q = $tr($row['q']);
            $a = $tr($row['a']);
            if ($q && $a) {
                $faq[] = array('question' => $q, 'answer' => $a);
            }
        }
        if ($faq) {
            $values['plan_faq'] = $faq;
        }

        $written = 0;

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
    iptv_provision_once('trial', TRIAL_PAGES_BUILD, function () {
        iptv_provision_pages(
            'trial',
            TRIAL_PAGES_BUILD,
            iptv_trial_page_definitions(),
            'template-trial.php',
            'iptv_trial_fill_page'
        );
    });
}, 20);
