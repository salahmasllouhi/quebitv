<?php
/**
 * JSON-LD for the M3U converter page
 *
 * Three nodes: the tool itself, the FAQ, and the seven uses as a HowTo.
 *
 * Rank Math emits the page-level WebPage/Breadcrumb graph; this adds only what
 * it cannot know. inc/schema-identity.php owns the Organization node the
 * publisher below points at.
 *
 * A note on what these are actually worth, so nobody has to re-derive it:
 * Google retired HowTo rich results in September 2023 and restricted FAQPage to
 * government and health sites in August 2023, so neither earns a SERP feature
 * here today. They are emitted anyway because Bing still renders them, and
 * because the assistants that increasingly read this page instead of Google
 * parse structured data in preference to prose. Together they cost about a
 * kilobyte. Flip $emit_howto to false if the graph ever needs slimming — the
 * SoftwareApplication node is the one that describes the page honestly and
 * should be the last to go.
 */

$emit_howto = true;

$permalink = get_permalink();

$currency_codes = array('usd' => 'USD', 'cad' => 'CAD');
$currency = function_exists('iptv_plan_currency') ? iptv_plan_currency() : 'usd';
$code = isset($currency_codes[$currency]) ? $currency_codes[$currency] : 'USD';

$graph = array();

// ── The converter ────────────────────────────────────────────────────────────
// SoftwareApplication rather than Product: nothing here is for sale, and typing
// a free browser tool as a Product with a zero-price Offer is the kind of thing
// that gets structured data ignored wholesale.
$graph[] = array(
    '@type'               => 'SoftwareApplication',
    '@id'                 => $permalink . '#tool',
    'name'                => m3u_str('M3U to Xtream Codes converter'),
    'description'         => m3u_str('A free browser-based tool that extracts the Xtream Codes server URL, username and password from any M3U playlist link. Nothing is uploaded.'),
    'url'                 => $permalink . '#m3u-tool',
    'applicationCategory' => 'UtilitiesApplication',
    'operatingSystem'     => 'Any',
    'browserRequirements' => 'Requires JavaScript',
    'offers'              => array(
        '@type'         => 'Offer',
        'price'         => '0',
        'priceCurrency' => $code,
    ),
    'publisher'           => array('@id' => home_url('/') . '#organization'),
);

// ── FAQ ──────────────────────────────────────────────────────────────────────
$questions = array();

foreach (iptv_m3u_faq_items() as $item) {
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

// ── The seven uses ───────────────────────────────────────────────────────────
if ($emit_howto) {
    $steps = array();
    $position = 1;

    foreach (iptv_m3u_howto_steps() as $step) {
        $steps[] = array(
            '@type'    => 'HowToStep',
            'position' => $position++,
            'name'     => m3u_str($step['name']),
            'text'     => m3u_str($step['text']),
        );
    }

    if (!empty($steps)) {
        $graph[] = array(
            '@type' => 'HowTo',
            '@id'   => $permalink . '#howto',
            'name'  => m3u_str('How to use an M3U playlist'),
            'step'  => $steps,
        );
    }
}

if (empty($graph)) {
    return;
}

$schema = array(
    '@context' => 'https://schema.org',
    '@graph'   => $graph,
);
?>
<script type="application/ld+json">
<?php echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
</script>
