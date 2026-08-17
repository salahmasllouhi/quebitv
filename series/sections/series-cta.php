<?php
/**
 * Series CTA Section
 * Simple strings: Polylang String Translations via srs_str().
 * Dynamic: $series_name
 */

$cta_title = srs_str('Ready to Watch %s?');
$cta_subtitle = srs_str('Join thousands binge-watching %s and 200,000+ movies & shows with Quebec IPTV.');
$cta_btn_text = srs_str('Get Access Now');
$cta_badge_1 = srs_str('256-bit SSL Encryption');
$cta_badge_2 = srs_str('Instant Activation');
$cta_badge_3 = srs_str('24/7 Customer Support');

// ── URL (CTA link) ───────────────────────────────────────────────────────────
$cta_btn_url = '#pricing';

$sn = esc_html($series_name);
?>

<section class="cta">
    <div class="cta-content">
        <h2>
            <?php echo wp_kses_post(sprintf($cta_title, $sn)); ?>
        </h2>
        <p>
            <?php echo wp_kses_post(sprintf($cta_subtitle, $sn)); ?>
        </p>

        <a href="<?php echo esc_url($cta_btn_url); ?>" class="btn btn-primary">
            <?php echo esc_html($cta_btn_text); ?>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
        </a>

        <div class="cta-features">
            <div class="cta-feature">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
                <?php echo esc_html($cta_badge_1); ?>
            </div>
            <div class="cta-feature">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <?php echo esc_html($cta_badge_2); ?>
            </div>
            <div class="cta-feature">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <?php echo esc_html($cta_badge_3); ?>
            </div>
        </div>
    </div>
</section>