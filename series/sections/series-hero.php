<?php
/**
 * Series Hero Section
 * Design: two-column layout matching the sport hero.
 * Viewer counter shown at top center.
 * Simple strings: Polylang String Translations via srs_str().
 * Dynamic: $series_name (get_the_title()), series ACF fields per-post.
 */

$arrow_icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';

// ── Dynamic per-post ACF fields ─────────────────────────────────────────────
$has_acf = function_exists('get_field');
$series_genre = $has_acf ? get_field('series_genre') : '';
$series_tagline = $has_acf ? get_field('series_tagline') : '';
$series_desc = $has_acf ? get_field('series_short_description') : '';
$genre_label = $series_genre ?: 'TV Series';

// Subtitle priority: short_description → tagline → excerpt → template fallback
if (!empty($series_desc)) {
    $hero_subtitle_html = wp_kses_post($series_desc);
} elseif (!empty($series_tagline)) {
    $hero_subtitle_html = wp_kses_post($series_tagline);
} elseif (has_excerpt()) {
    $hero_subtitle_html = wp_kses_post(get_the_excerpt());
} else {
    $fallback_tpl = srs_str('Stream <strong>%s</strong> with Quebec IPTV. All seasons, all episodes. <strong>No restrictions.</strong>');
    $hero_subtitle_html = wp_kses_post(sprintf($fallback_tpl, esc_html($series_name)));
}

// ── Polylang strings ─────────────────────────────────────────────────────────
$hero_prefix = srs_str('Watch');
$hero_suffix = srs_str('Plus Thousands of Series with Quebec IPTV');
$streaming_badge = srs_str('· Streaming Now');
$savings_badge = srs_str('Save Over $1,500 Annually!');
$feature_1 = srs_str('200K+ Movies & Shows');
$feature_2 = srs_str('All Seasons Available');
$feature_3 = srs_str('4K Ultra HD');
$cta_text = srs_str('Start Watching Now');
$disclaimer = srs_str('No contract • Cancel anytime • 24h trial');
$counter_label = srs_str('STREAMING NOW');
$counter_desc = srs_str('viewers streaming right now');

// ── URL ───────────────────────────────────────────────────────────────────────
$cta_url = '#pricing';
?>

<div class="series-hero-section">
<div class="bg-aurora"></div>

<section class="hero">
    <div class="hero-top">
        <!-- Viewer Counter -->
        <div class="hero-live-counter">
            <span class="live-dot"></span>
            <span class="live-counter-label">
                <?php echo esc_html($counter_label); ?>
            </span>
            <span class="stat-value live-counter-value" id="liveCounter">42,537</span>
            <span class="live-counter-desc">
                <?php echo esc_html($counter_desc); ?>
            </span>
        </div>

        <!-- Badges -->
        <div class="hero-badges-container">
            <!-- Badge 1: Genre -->
            <div class="hero-rating">
                <span class="live-dot" style="background:#22c55e;"></span>
                <span class="rating-text">
                    <?php echo esc_html($genre_label); ?>
                    <?php echo esc_html($streaming_badge); ?>
                </span>
            </div>

            <!-- Badge 2: Savings -->
            <div class="hero-savings">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 6v6l4 2" />
                </svg>
                <?php echo esc_html($savings_badge); ?>
            </div>
        </div>

        <!-- Title: line 1 = Watch [series name] | line 2 = suffix -->
        <h1 class="series-hero-title">
            <span class="title-line-1">
                <span>
                    <?php echo esc_html(trim($hero_prefix)); ?>
                </span><span class="gradient-text">
                    <?php echo esc_html(trim($series_name)); ?>
                </span>
            </span>
            <span class="title-line-2">
                <?php echo esc_html(trim($hero_suffix)); ?>
            </span>
        </h1>
    </div>

    <div class="hero-bottom">
        <div class="hero-bottom-text">
            <!-- Subtitle -->
            <p class="hero-subtitle">
                <?php echo $hero_subtitle_html; ?>
            </p>

            <!-- CTA -->
            <div class="hero-actions">
                <a href="<?php echo esc_url($cta_url); ?>" class="btn btn-primary">
                    <?php echo esc_html($cta_text); ?>
                    <?php echo $arrow_icon; ?>
                </a>
            </div>

            <p class="hero-disclaimer" style="color:var(--text-muted);font-size:0.875rem;margin-top:1rem;">
                <?php echo esc_html($disclaimer); ?>
            </p>
        </div>

        <!-- Feature Image Column -->
        <div class="hero-image-column">
            <?php if (has_post_thumbnail()): ?>
                <div class="series-featured-image">
                    <?php the_post_thumbnail('large', ['alt' => esc_attr($series_name)]); ?>
                    <div class="featured-image-overlay-text">
                        <h3 class="overlay-title"><?php echo esc_html($series_name); ?></h3>
                        <span class="overlay-type"><?php echo esc_html($genre_label); ?></span>
                    </div>
                </div>
            <?php else: ?>
                <!-- Fallback placeholder if no image is set -->
                <div class="series-featured-image placeholder">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--border-subtle)"
                        stroke-width="2">
                        <rect x="2" y="2" width="20" height="20" rx="2.18" />
                        <line x1="7" y1="2" x2="7" y2="22" />
                        <line x1="17" y1="2" x2="17" y2="22" />
                        <line x1="2" y1="12" x2="22" y2="12" />
                    </svg>
                    <div class="featured-image-overlay-text">
                        <h3 class="overlay-title"><?php echo esc_html($series_name); ?></h3>
                        <span class="overlay-type"><?php echo esc_html($genre_label); ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
</section>
</div><!-- /.series-hero-section -->