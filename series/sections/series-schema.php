<?php
/**
 * TVSeries JSON-LD Schema Markup
 * Uses ACF fields: series_genre, series_tagline, series_seasons, series_episodes, series_website
 */

$has_acf = function_exists('get_field');
$series_genre = $has_acf ? get_field('series_genre') : '';
$series_seasons = $has_acf ? get_field('series_seasons') : '';
$series_episodes = $has_acf ? get_field('series_episodes') : '';
$series_website = $has_acf ? get_field('series_website') : '';

$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'TVSeries',
    'name' => get_the_title(),
    'description' => has_excerpt()
        ? get_the_excerpt()
        : 'Watch ' . get_the_title() . ' with Quebec IPTV. All seasons and episodes available.',
    'url' => get_permalink(),
    'provider' => [
        '@type' => 'Organization',
        'name' => 'Quebec IPTV',
        'url' => home_url('/'),
        'logo' => [
            '@type' => 'ImageObject',
            'url' => get_template_directory_uri() . '/images/logo.png',
        ],
    ],
];

// Series poster (featured image)
if (has_post_thumbnail()) {
    $schema['image'] = get_the_post_thumbnail_url(get_the_ID(), 'full');
}

// Genre
if ($series_genre) {
    $schema['genre'] = $series_genre;
}

// Number of seasons
if ($series_seasons) {
    $schema['numberOfSeasons'] = (int) $series_seasons;
}

// Number of episodes
if ($series_episodes) {
    $schema['numberOfEpisodes'] = (int) $series_episodes;
}

// Official website
if ($series_website) {
    $schema['sameAs'] = esc_url($series_website);
}

// Offers
$schema['offers'] = [
    '@type' => 'Offer',
    'price' => '5.83',
    'priceCurrency' => 'USD',
    'availability' => 'https://schema.org/InStock',
    'url' => home_url('/subscribe/'),
    'description' => 'Quebec IPTV subscription including ' . get_the_title() . ' and 200,000+ movies & shows',
    'seller' => [
        '@type' => 'Organization',
        'name' => 'Quebec IPTV',
    ],
];
?>

<script type="application/ld+json">
<?php echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
</script>