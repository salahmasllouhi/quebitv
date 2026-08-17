<?php
/**
 * JSON-LD for a plan page
 *
 * A Product with one Offer per screen count, plus the FAQ. This is the reason
 * the four plans are separate pages: each one can state its own price to a
 * crawler, which a single configurator behind JS cannot.
 *
 * Prices are the same figures printed on the page, in the same currency, from
 * the same helper — a schema price that disagrees with the visible one is worse
 * than no schema at all.
 *
 * Note: Rank Math emits the page-level WebPage/Breadcrumb graph. This adds only
 * what it cannot know.
 *
 * Expects from template-plan.php:
 *   $plan_months (int)  $plan_label (string)  $plan_faq_items (array)
 */

$currency_codes = array(
    'usd' => 'USD',
    'eur' => 'EUR',
    'sek' => 'SEK',
    'nok' => 'NOK',
    'dkk' => 'DKK',
    'isk' => 'ISK',
);

$currency = iptv_plan_currency();
$code     = isset($currency_codes[$currency]) ? $currency_codes[$currency] : 'USD';

$offers = array();
$lowest = null;
$highest = null;

for ($screens = 1; $screens <= 4; $screens++) {
    $price = iptv_plan_price($plan_months, $screens);

    if ($price <= 0) {
        continue;
    }

    $lowest  = ($lowest === null) ? $price : min($lowest, $price);
    $highest = ($highest === null) ? $price : max($highest, $price);

    $offers[] = array(
        '@type'           => 'Offer',
        'name'            => $plan_label . ' — ' . iptv_plan_screens_label($screens),
        'price'           => number_format($price, 2, '.', ''),
        'priceCurrency'   => $code,
        'availability'    => 'https://schema.org/InStock',
        'url'             => iptv_plan_checkout_url($screens, $plan_months),
        'priceValidUntil' => gmdate('Y-m-d', strtotime('+1 year')),
    );
}

if (empty($offers)) {
    return;
}

$description = get_the_excerpt();
if (!$description) {
    $description = sprintf(
        /* translators: %s = plan length, e.g. "1 Month" */
        plan_str('%s Quebec IPTV subscription: 40,000+ live channels, 200,000+ movies and series in 4K and HD, on 1 to 4 screens. No contract and no auto-renew.'),
        $plan_label
    );
}

$graph = array(
    array(
        '@type'       => 'Product',
        '@id'         => get_permalink() . '#product',
        'name'        => get_the_title(),
        'description' => wp_strip_all_tags($description),
        'url'         => get_permalink(),
        'category'    => 'IPTV subscription',
        'brand'       => array(
            '@type' => 'Brand',
            'name'  => 'Quebec IPTV',
        ),
        'offers'      => array(
            '@type'         => 'AggregateOffer',
            'priceCurrency' => $code,
            'lowPrice'      => number_format($lowest, 2, '.', ''),
            'highPrice'     => number_format($highest, 2, '.', ''),
            'offerCount'    => count($offers),
            'offers'        => $offers,
        ),
    ),
);

if (has_post_thumbnail()) {
    $graph[0]['image'] = get_the_post_thumbnail_url(get_the_ID(), 'full');
}

if (!empty($plan_faq_items)) {
    $questions = array();

    foreach ($plan_faq_items as $item) {
        if (empty($item['q'])) {
            continue;
        }
        $questions[] = array(
            '@type'          => 'Question',
            'name'           => wp_strip_all_tags($item['q']),
            'acceptedAnswer' => array(
                '@type' => 'Answer',
                'text'  => wp_strip_all_tags($item['a']),
            ),
        );
    }

    if (!empty($questions)) {
        $graph[] = array(
            '@type'      => 'FAQPage',
            '@id'        => get_permalink() . '#faq',
            'mainEntity' => $questions,
        );
    }
}

$schema = array(
    '@context' => 'https://schema.org',
    '@graph'   => $graph,
);
?>
<script type="application/ld+json">
<?php echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
</script>
