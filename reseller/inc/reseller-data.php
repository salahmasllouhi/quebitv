<?php
/**
 * Reseller page — credit packs and pricing
 *
 * Credit packs deliberately do not come from IPTV_Currency_Settings' price
 * table. That table is keyed by {duration}x{devices} and has no concept of a
 * credit, so bending it to fit would mean inventing rows that mean nothing to
 * the front-page configurator reading the same data.
 *
 * Formatting, though, goes through the plan helpers — iptv_plan_format_price()
 * and iptv_plan_currency() — so a reseller price can never render in a different
 * shape or currency from a plan price on the next page over. That is the one
 * piece of this the site cannot afford a second implementation of.
 *
 * @package Quebec_IPTV
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('iptv_reseller_pack_defaults')) {
    /**
     * The four credit packs, cheapest first.
     *
     * A credit is one month of one subscription, which is the unit every panel
     * in this market sells in. Prices step down per credit as volume rises,
     * which is the entire argument of the page.
     *
     * @return array<int,array{credits:int,price:float,popular:bool}>
     */
    function iptv_reseller_pack_defaults()
    {
        return array(
            array('credits' => 10,  'price' => 90.0,  'popular' => false),
            array('credits' => 25,  'price' => 200.0, 'popular' => true),
            array('credits' => 50,  'price' => 350.0, 'popular' => false),
            array('credits' => 100, 'price' => 600.0, 'popular' => false),
        );
    }
}

if (!function_exists('iptv_reseller_packs')) {
    /**
     * Packs for this page: the reseller_packs ACF repeater when filled,
     * otherwise the bundled defaults.
     *
     * reseller-packs.php and reseller-schema.php both read this, so the prices
     * a crawler is told and the prices a visitor sees come from one place.
     *
     * @return array<int,array{credits:int,price:float,popular:bool}>
     */
    function iptv_reseller_packs()
    {
        $rows  = iptv_plan_field('reseller_packs', array());
        $packs = array();

        if (is_array($rows)) {
            foreach ($rows as $row) {
                $credits = isset($row['credits']) ? (int) $row['credits'] : 0;
                $price   = isset($row['price']) ? (float) $row['price'] : 0.0;

                // A pack with no credits or no price is a half-filled row in the
                // editor, not an offer. Skipping it beats rendering "0 credits
                // for $0.00" on a page whose whole job is to look credible to
                // somebody about to spend money.
                if ($credits > 0 && $price > 0) {
                    $packs[] = array(
                        'credits' => $credits,
                        'price'   => $price,
                        'popular' => !empty($row['popular']),
                    );
                }
            }
        }

        return $packs ? $packs : iptv_reseller_pack_defaults();
    }
}

if (!function_exists('iptv_reseller_per_credit')) {
    /**
     * Unit price for a pack. Guarded against a zero divisor rather than trusting
     * the caller, because this runs on values an editor can type.
     *
     * @param array{credits:int,price:float} $pack
     * @return float
     */
    function iptv_reseller_per_credit($pack)
    {
        $credits = isset($pack['credits']) ? (int) $pack['credits'] : 0;

        return $credits > 0 ? ((float) $pack['price'] / $credits) : 0.0;
    }
}

if (!function_exists('iptv_reseller_checkout_url')) {
    /**
     * Where a pack's button goes.
     *
     * Built with add_query_arg() against the configured panel URL, exactly as
     * iptv_plan_checkout_url() does — the panel reads the quantity from the
     * query string, so there is no cart and no product ID to fall out of sync.
     *
     * @param int $credits
     * @return string
     */
    function iptv_reseller_checkout_url($credits)
    {
        $base = iptv_config('reseller_url', 'https://app.quebeciptv.co/reseller');

        return add_query_arg(array('credits' => (int) $credits), $base);
    }
}
