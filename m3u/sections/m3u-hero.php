<?php
/**
 * M3U hero — headline, intro, and a jump link to the tool.
 *
 * One column, not the plan pages' two: the thing worth looking at on this page
 * is the converter directly below, so a hero image beside the copy would only
 * push it further down. The CTA is an anchor rather than a link away, for the
 * same reason.
 */

$eyebrow  = iptv_plan_field('m3u_eyebrow', m3u_str('Free tool'));
$headline = iptv_plan_field('m3u_headline', get_the_title() ?: m3u_str('M3U editor and playlist converter'));
$intro    = iptv_plan_field('m3u_intro', m3u_str('Paste an M3U playlist URL and get the Xtream Codes server, username and password out of it — instantly, and without the URL ever leaving your browser.'));
$cta      = iptv_plan_field('m3u_hero_cta', m3u_str('Open the converter'));
?>
<section class="m3u-hero">
    <div class="container">

        <p class="m3u-hero-eyebrow"><?php echo esc_html($eyebrow); ?></p>

        <h1 class="m3u-hero-title"><?php echo esc_html($headline); ?></h1>

        <p class="m3u-hero-sub"><?php echo esc_html($intro); ?></p>

        <a href="#m3u-tool" class="dv2-btn dv2-btn-primary dv2-btn-lg">
            <?php echo esc_html($cta); ?>
        </a>

    </div>
</section>
