<?php
/**
 * BroadcastService JSON-LD Schema Markup
 * Uses ACF fields: channel_type, channel_networks, channel_launch_year, channel_website
 */

$has_acf = function_exists('get_field');
$channel_type = $has_acf ? get_field('channel_type') : '';
$channel_networks = $has_acf ? get_field('channel_networks') : '';
$channel_launch_year = $has_acf ? get_field('channel_launch_year') : '';
$channel_website = $has_acf ? get_field('channel_website') : '';

$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'BroadcastService',
    'name' => get_the_title() . ' on Quebec IPTV',
    'description' => has_excerpt()
        ? get_the_excerpt()
        : 'Watch ' . get_the_title() . ' live stream with Quebec IPTV. No cable required.',
    'url' => get_permalink(),
    'broadcastDisplayName' => get_the_title(),
    'broadcaster' => [
        '@type' => 'Organization',
        'name' => 'Quebec IPTV',
        'url' => home_url('/'),
        'logo' => [
            '@type' => 'ImageObject',
            'url' => get_template_directory_uri() . '/images/logo/quebec-iptv-logo.svg',
        ],
    ],
    'broadcastTimezone' => 'UTC',
    'videoFormat' => '4K UHD',
];

// Channel logo (featured image)
if (has_post_thumbnail()) {
    $schema['image'] = get_the_post_thumbnail_url(get_the_ID(), 'full');
}

// Channel type → genre
if ($channel_type) {
    $schema['genre'] = $channel_type;
}

// Networks → keywords for BroadcastService
if ($channel_networks) {
    $schema['keywords'] = $channel_networks;
}

// Launch year → foundingDate on the broadcaster sub-org
if ($channel_launch_year) {
    $schema['broadcastAffiliateOf'] = [
        '@type' => 'Organization',
        'name' => get_the_title(),
        'foundingDate' => (string) $channel_launch_year,
    ];
    if ($channel_website) {
        $schema['broadcastAffiliateOf']['url'] = esc_url($channel_website);
    }
} elseif ($channel_website) {
    $schema['broadcastAffiliateOf'] = [
        '@type' => 'Organization',
        'name' => get_the_title(),
        'url' => esc_url($channel_website),
    ];
}

// Offers
$schema['offers'] = [
    '@type' => 'Offer',
    'price' => '5.83',
    'priceCurrency' => 'USD',
    'availability' => 'https://schema.org/InStock',
    'url' => home_url('/subscribe/'),
    'description' => 'Quebec IPTV subscription including ' . get_the_title() . ' and 40,000+ channels',
    'seller' => [
        '@type' => 'Organization',
        'name' => 'Quebec IPTV',
    ],
];
?>

<script type="application/ld+json">
<?php echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
</script>