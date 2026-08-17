<?php
/**
 * Sport Hero Section
 * Design: centered layout (hero--centered) matching front page colors.
 * Live viewer dot + animated counter shown at top center.
 * Simple strings: Polylang String Translations via spl_str().
 * Dynamic: $sport_name (get_the_title()), sport_type, sport_tagline, sport_short_description (ACF per-post).
 */

$arrow_icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';

// ── Dynamic per-post ACF fields ─────────────────────────────────────────────
$has_acf = function_exists('get_field');
$sport_type = $has_acf ? get_field('sport_type') : '';
$sport_tagline = $has_acf ? get_field('sport_tagline') : '';
$sport_short_desc = $has_acf ? get_field('sport_short_description') : '';
$type_label = $sport_type ?: 'Live Sport';

// Subtitle priority: short_description → tagline → excerpt → template fallback
if (!empty($sport_short_desc)) {
    $hero_subtitle_html = wp_kses_post($sport_short_desc);
} elseif (!empty($sport_tagline)) {
    $hero_subtitle_html = wp_kses_post($sport_tagline);
} elseif (has_excerpt()) {
    $hero_subtitle_html = wp_kses_post(get_the_excerpt());
} else {
    $fallback_tpl = spl_str('Stream <strong>%s</strong> live with Quebec IPTV. No cable required. <strong>No blackouts. No restrictions.</strong>');
    $hero_subtitle_html = wp_kses_post(sprintf($fallback_tpl, esc_html($sport_name)));
}

// ── Polylang strings ─────────────────────────────────────────────────────────
$hero_prefix = spl_str('Watch');
$hero_suffix = spl_str('& Thousands of Live Sport Events with Quebec IPTV');
$live_badge = spl_str('· Live Now');
$savings_badge = spl_str('Save Over $1,500 Annually!');
$feature_1 = spl_str('40,000+ Channels');
$feature_2 = spl_str('PPV $0 Extra');
$feature_3 = spl_str('4K Ultra HD');
$cta_text = spl_str('Start Watching Now');
$disclaimer = spl_str('No contract • Cancel anytime • 24h trial');
$live_label = spl_str('LIVE NOW');
$live_desc = spl_str('viewers streaming right now');

// ── URL ───────────────────────────────────────────────────────────────────────
$cta_url = '#pricing';
?>

<div class="sport-hero-section">
<div class="bg-aurora"></div>

<section class="hero">
    <!-- Top Zone: Centered Content -->
    <div class="hero-top">
        <!-- Badges -->
        <div class="hero-badges-container">
            <!-- Badge 1: Sport Type -->
            <div class="hero-rating">
                <span class="live-dot" style="background:#22c55e;"></span>
                <span class="rating-text"><?php echo esc_html($type_label); ?>
                    <?php echo esc_html($live_badge); ?></span>
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

        <!-- Title: line 1 = Watch [sport name] | line 2 = Live Stream -->
        <h1 class="sport-hero-title">
            <span class="title-line-1">
                <span><?php echo esc_html(trim($hero_prefix)); ?></span><span
                    class="gradient-text"><?php echo esc_html(trim($sport_name)); ?></span>
            </span>
            <span class="title-line-2"><?php echo esc_html(trim($hero_suffix)); ?></span>
        </h1>
    </div>

    <!-- Bottom Zone: Text Left, Image Right -->
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
                <div class="sport-featured-image">
                    <?php the_post_thumbnail('large', ['alt' => esc_attr($sport_name)]); ?>
                    <div class="featured-image-overlay-text">
                        <h3 class="overlay-title"><?php echo esc_html($sport_name); ?></h3>
                        <span class="overlay-type"><?php echo esc_html($type_label); ?></span>
                    </div>
                </div>
            <?php else: ?>
                <!-- Fallback placeholder if no image is set -->
                <div class="sport-featured-image placeholder">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--border-subtle)"
                        stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 8v4l3 3" />
                    </svg>
                    <div class="featured-image-overlay-text">
                        <h3 class="overlay-title"><?php echo esc_html($sport_name); ?></h3>
                        <span class="overlay-type"><?php echo esc_html($type_label); ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
</section>
</div><!-- /.sport-hero-section -->