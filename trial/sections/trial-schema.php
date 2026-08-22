<?php
/**
 * JSON-LD for the trial page
 *
 * A free Offer plus the FAQ. Rank Math emits the page-level WebPage graph and
 * inc/schema-identity.php owns the Organization node this points at.
 *
 * This is why the trial did not go through template-plan.php: plan-schema.php
 * skips any offer priced at or below zero and returns early when that leaves it
 * with none, so a trial routed through it would have emitted no schema at all.
 *
 * Expects from template-trial.php:
 *   $plan_faq_items (array)
 */

$permalink = get_permalink();

$currency_codes = array('usd' => 'USD', 'cad' => 'CAD');
$currency = function_exists('iptv_plan_currency') ? iptv_plan_currency() : 'usd';
$code = isset($currency_codes[$currency]) ? $currency_codes[$currency] : 'USD';

$description = iptv_plan_field(
    'trial_schema_description',
    trial_str('A free 24-hour trial of the complete Quebec IPTV service: 40,000+ live channels and 200,000+ films and series in 4K, on one screen, with no card required.')
);

$graph = array(
    array(
        '@type'       => 'Product',
        '@id'         => $permalink . '#trial',
        'name'        => get_the_title(),
        'description' => $description,
        'brand'       => array(
            '@type' => 'Brand',
            'name'  => get_bloginfo('name'),
        ),
        'offers'      => array(
            '@type'         => 'Offer',
            'price'         => '0',
            'priceCurrency' => $code,
            'availability'  => 'https://schema.org/InStock',
            'url'           => $permalink,
            // A trial is not a subscription that renews, and saying so in the
            // markup is the same promise the page makes in words.
            'eligibleDuration' => array(
                '@type'    => 'QuantitativeValue',
                'value'    => 24,
                'unitCode' => 'HUR',
            ),
        ),
    ),
);

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
        '@id'        => $permalink . '#faq',
        'mainEntity' => $questions,
    );
}

$schema = array(
    '@context' => 'https://schema.org',
    '@graph'   => $graph,
);
?>
<script type="application/ld+json">
<?php echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
</script>
