<?php
/**
 * JSON-LD for the reseller page
 *
 * Service with an AggregateOffer over the credit packs, plus the FAQ. Prices
 * come from iptv_reseller_packs(), the same helper reseller-packs.php renders
 * from, so the figures a crawler is given and the figures a visitor sees cannot
 * disagree — which matters more here than on a plan page, because a mismatched
 * price in a B2B result is the kind of thing that gets markup ignored.
 *
 * Rank Math emits the page-level WebPage graph; inc/schema-identity.php owns the
 * Organization node the provider below points at.
 *
 * Expects from template-reseller.php:
 *   $plan_faq_items (array)
 */

$permalink = get_permalink();

$currency_codes = array('usd' => 'USD', 'cad' => 'CAD');
$currency = function_exists('iptv_plan_currency') ? iptv_plan_currency() : 'usd';
$code = isset($currency_codes[$currency]) ? $currency_codes[$currency] : 'USD';

$packs = iptv_reseller_packs();

$offers = array();
$low = null;
$high = null;

foreach ($packs as $pack) {
    $price = (float) $pack['price'];

    if ($price <= 0) {
        continue;
    }

    $low  = ($low === null) ? $price : min($low, $price);
    $high = ($high === null) ? $price : max($high, $price);

    $offers[] = array(
        '@type'         => 'Offer',
        'name'          => sprintf(reseller_str('%d credits'), (int) $pack['credits']),
        'price'         => number_format($price, 2, '.', ''),
        'priceCurrency' => $code,
        'availability'  => 'https://schema.org/InStock',
        'url'           => iptv_reseller_checkout_url($pack['credits']),
        // Rolled forward a year, matching plan-schema.php. A priceValidUntil in
        // the past makes Google drop the offer rather than treat it as current.
        'priceValidUntil' => gmdate('Y-m-d', strtotime('+1 year')),
    );
}

$description = iptv_plan_field(
    'reseller_schema_description',
    reseller_str('An IPTV reseller panel with instant line creation, sub-reseller accounts, M3U, Xtream Codes and MAG output, and credits that never expire. No monthly commitment.')
);

$service = array(
    '@type'       => 'Service',
    '@id'         => $permalink . '#service',
    'name'        => get_the_title(),
    'serviceType' => 'IPTV reseller panel',
    'description' => $description,
    'provider'    => array('@id' => home_url('/') . '#organization'),
    'areaServed'  => array(
        array('@type' => 'Country', 'name' => 'Canada'),
        array('@type' => 'Country', 'name' => 'United States'),
    ),
);

if (!empty($offers)) {
    $service['offers'] = array(
        '@type'         => 'AggregateOffer',
        'priceCurrency' => $code,
        'lowPrice'      => number_format($low, 2, '.', ''),
        'highPrice'     => number_format($high, 2, '.', ''),
        'offerCount'    => count($offers),
        'offers'        => $offers,
    );
}

$graph = array($service);

$questions = array();

foreach ($plan_faq_items as $item) {
    if (empty($item['q'])) {
        continue;
    }
    $questions[] = array(
        '@type'          => 'Question',
        'name'           => wp_strip_all_tags($item['q']),
        'acceptedAnswer' => array('@type' => 'Answer', 'text' => wp_strip_all_tags($item['a'])),
    );
}

if (!empty($questions)) {
    $graph[] = array(
        '@type'      => 'FAQPage',
        '@id'        => $permalink . '#faq',
        'mainEntity' => $questions,
    );
}

$schema = array('@context' => 'https://schema.org', '@graph' => $graph);
?>
<script type="application/ld+json">
<?php echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
</script>
