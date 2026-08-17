<?php
/**
 * SportsOrganization JSON-LD Schema Markup
 * Uses ACF fields: sport_type, sport_networks, sport_launch_year, sport_website
 */

$has_acf = function_exists('get_field');
$sport_type = $has_acf ? get_field('sport_type') : '';
$sport_networks = $has_acf ? get_field('sport_networks') : '';
$sport_launch_year = $has_acf ? get_field('sport_launch_year') : '';
$sport_website = $has_acf ? get_field('sport_website') : '';

$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'SportsOrganization',
    'name' => get_the_title() . ' on Quebec IPTV',
    'description' => has_excerpt()
        ? get_the_excerpt()
        : 'Watch ' . get_the_title() . ' live stream with Quebec IPTV. No cable required.',
    'url' => get_permalink(),
    'sport' => get_the_title(),
    'broadcaster' => [
        '@type' => 'Organization',
        'name' => 'Quebec IPTV',
        'url' => home_url('/'),
        'logo' => [
            '@type' => 'ImageObject',
            'url' => get_template_directory_uri() . '/images/logo.png',
        ],
    ],
];

// Sport logo (featured image)
if (has_post_thumbnail()) {
    $schema['image'] = get_the_post_thumbnail_url(get_the_ID(), 'full');
}

// Sport type → sport genre
if ($sport_type) {
    $schema['genre'] = $sport_type;
}

// Networks / leagues → keywords
if ($sport_networks) {
    $schema['keywords'] = $sport_networks;
}

// Launch year → foundingDate on the sub-org
if ($sport_launch_year) {
    $schema['memberOf'] = [
        '@type' => 'SportsOrganization',
        'name' => get_the_title(),
        'foundingDate' => (string) $sport_launch_year,
    ];
    if ($sport_website) {
        $schema['memberOf']['url'] = esc_url($sport_website);
    }
} elseif ($sport_website) {
    $schema['memberOf'] = [
        '@type' => 'SportsOrganization',
        'name' => get_the_title(),
        'url' => esc_url($sport_website),
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