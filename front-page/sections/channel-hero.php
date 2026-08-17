<?php
/**
 * Channel Hero Section
 * Design: centered layout (hero--centered) matching front page colors.
 * Live viewer dot + animated counter shown at top center.
 * Simple strings: Polylang String Translations via tpl_str().
 * Dynamic: $channel_name (get_the_title()), channel_type, channel_tagline, channel_short_description (ACF per-post).
 */

$arrow_icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';

// ── Dynamic per-post ACF fields ─────────────────────────────────────────────
$has_acf = function_exists('get_field');
$channel_type = $has_acf ? get_field('channel_type') : '';
$channel_tagline = $has_acf ? get_field('channel_tagline') : '';
$channel_short_desc = $has_acf ? get_field('channel_short_description') : '';
$type_label = $channel_type ?: 'Live TV';

// Subtitle priority: short_description → tagline → excerpt → template fallback
if (!empty($channel_short_desc)) {
    $hero_subtitle_html = wp_kses_post($channel_short_desc);
} elseif (!empty($channel_tagline)) {
    $hero_subtitle_html = wp_kses_post($channel_tagline);
} elseif (has_excerpt()) {
    $hero_subtitle_html = wp_kses_post(get_the_excerpt());
} else {
    $fallback_tpl = tpl_str('Stream <strong>%s</strong> live with Quebec IPTV. No cable required. <strong>No blackouts. No restrictions.</strong>');
    $hero_subtitle_html = wp_kses_post(sprintf($fallback_tpl, esc_html($channel_name)));
}

// ── Polylang strings ─────────────────────────────────────────────────────────
$hero_prefix = tpl_str('Watch');
$hero_suffix = tpl_str('Plus Thousands of Channels with Quebec IPTV');
$live_badge = tpl_str('· Live Now');
$savings_badge = tpl_str('Save Over $1,500 Annually!');
$feature_1 = tpl_str('40,000+ Channels');
$feature_2 = tpl_str('PPV $0 Extra');
$feature_3 = tpl_str('4K Ultra HD');
$cta_text = tpl_str('Start Watching Now');
$disclaimer = tpl_str('No contract • Cancel anytime • 24h trial');
$live_label = tpl_str('LIVE NOW');
$live_desc = tpl_str('active viewers');

// ── URL ───────────────────────────────────────────────────────────────────────
$cta_url = '#pricing';
?>

<div class="channel-hero-section">
    <div class="bg-aurora"></div>

    <section class="hero">
        <div class="hero-top">
            <!-- Live Viewer Counter -->
            <div class="hero-live-counter">
                <span class="live-dot"></span>
                <span class="live-counter-label"><?php echo esc_html($live_label); ?></span>
                <span class="stat-value live-counter-value" id="liveCounter">42,537</span>
                <span class="live-counter-desc"><?php echo esc_html($live_desc); ?></span>
            </div>

            <!-- Badges -->
            <div class="hero-badges-container">
                <!-- Badge 1: Channel Type -->
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

            <!-- Title: line 1 = Watch [channel name] | line 2 = Live Stream -->
            <h1 class="channel-hero-title">
                <span class="title-line-1">
                    <span><?php echo esc_html(trim($hero_prefix)); ?></span><span
                        class="gradient-text"><?php echo esc_html(trim($channel_name)); ?></span>
                </span>
                <span class="title-line-2"><?php echo esc_html(trim($hero_suffix)); ?></span>
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
                    <div class="channel-featured-image">
                        <?php the_post_thumbnail('large', ['alt' => esc_attr($channel_name)]); ?>
                        <div class="featured-image-overlay-text">
                            <h3 class="overlay-title"><?php echo esc_html($channel_name); ?></h3>
                            <span class="overlay-type"><?php echo esc_html($type_label); ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Fallback placeholder if no image is set -->
                    <div class="channel-featured-image placeholder">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--border-subtle)"
                            stroke-width="2">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                        <div class="featured-image-overlay-text">
                            <h3 class="overlay-title"><?php echo esc_html($channel_name); ?></h3>
                            <span class="overlay-type"><?php echo esc_html($type_label); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
    </section>
</div><!-- /.channel-hero-section -->