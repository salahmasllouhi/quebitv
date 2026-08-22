<?php
/**
 * Closing band.
 *
 * The converter is a free tool that ranks on its own terms, so this is the only
 * place on the page that sells anything. Two CTAs: the pricing grid for people
 * who arrived already shopping, and the trial for the larger group who arrived
 * with a playlist from somebody else.
 *
 * Both links go through iptv_page_url() / pll_home_url(), so they resolve to
 * the current language rather than assuming a prefix.
 */

$title = iptv_plan_field('m3u_final_title', m3u_str('A playlist is only as good as the service behind it'));
$text  = iptv_plan_field('m3u_final_text', m3u_str('Quebec IPTV gives you both an M3U playlist and Xtream Codes credentials, on 40,000+ channels and 200,000+ films. Try it for 24 hours without a card.'));

$pricing_url = (function_exists('pll_home_url') ? pll_home_url() : home_url('/')) . '#pricing';
$trial_url   = iptv_config('trial_url', 'https://app.quebeciptv.co/checkout/trial');
?>
<section class="m3u-final dv2-section">
    <div class="container">
        <div class="dv2-cta-bar">

            <div>
                <h2><?php echo esc_html($title); ?></h2>
                <p><?php echo esc_html($text); ?></p>
            </div>

            <div class="dv2-hero-actions">
                <a href="<?php echo esc_url($pricing_url); ?>" class="dv2-btn dv2-btn-primary dv2-btn-lg">
                    <?php echo esc_html(m3u_str('See plans and pricing')); ?>
                </a>
                <a href="<?php echo esc_url($trial_url); ?>" class="dv2-btn dv2-hero-link">
                    <?php echo esc_html(m3u_str('Start a 24-hour trial')); ?>
                </a>
            </div>

        </div>
    </div>
</section>
