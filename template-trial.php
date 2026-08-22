<?php
/**
 * Template Name: Trial Page
 * Description: The 24-hour free trial. Assign this template to a page; the
 *              terms, the FAQ and the free-offer schema follow.
 *
 * Why this is not template-plan.php with plan_months = 0.
 *
 * That file's own header promises "nothing here is hard-coded to a length", and
 * a 24-hour trial breaks that promise in nine places. iptv_plan_months() clamps
 * an unknown value back to 1, and that clamp exists precisely to stop a
 * hand-edited field printing a page of zeroes. plan-prices.php would render four
 * cards at $0.00 with four buy buttons. plan-compare.php would list the trial as
 * a fifth plan. plan-seo.php would describe the wrong page. plan-hero.php would
 * pick the multi-month subline and say "The whole service for 24-hour trial."
 * And plan-schema.php skips any offer at or below zero, then returns early when
 * that leaves it with none — so the trial page would have emitted no structured
 * data at all.
 *
 * Nine conditionals to save one short file. The reuse worth having is at section
 * level, and that is what this does: of the ten sections below, eight are
 * existing files included unchanged.
 *
 * Section order:
 *   1. header        (shared)
 *   2. plan-hero     (reused; no price line, no second CTA)
 *   3. trial-terms   (new — the only trial-specific section)
 *   4. plan-includes (reused; "the trial is the full service" is the point)
 *   5. steps         (shared onboarding panel)
 *   6. unlock        (shared device chips)
 *   7. reviews       (shared social proof)
 *   8. plan-faq      (reused, fed trial questions)
 *   9. plan-cta      (reused, pointed at the trial checkout)
 *  10. footer        (shared)
 *  11. trial-schema  (free Offer + FAQPage JSON-LD)
 *
 * @package Quebec_IPTV
 */

get_header();

$theme_dir = get_template_directory();
$front_dir = $theme_dir . '/front-page';
$plan_dir  = $theme_dir . '/plan';
$trial_dir = $theme_dir . '/trial';
?>

<style>
    <?php
    // plan.css is not incidental here — the reused hero and closing band emit
    // .plan-hero-* and .plan-final-* class names, so without it this page would
    // render unstyled where it matters most. design-v2 stays last of the shared
    // layers so it remaps the older tokens.
    $css_files = array(
        $front_dir . '/css/variables.css',
        $front_dir . '/css/base.css',
        $front_dir . '/css/header.css',
        $front_dir . '/css/reviews.css',
        $front_dir . '/css/footer.css',
        $front_dir . '/css/responsive.css',
        $front_dir . '/css/redesign-theme.css',
        $front_dir . '/css/cta.css',
        $front_dir . '/css/design-v2.css',
        $front_dir . '/css/design-v2-sections.css',
        $plan_dir  . '/css/plan.css',
        $trial_dir . '/css/trial.css',
    );

    foreach ($css_files as $path) {
        if (file_exists($path)) {
            include $path;
        }
    }
    ?>
</style>

<div class="dv2-grid-wash" aria-hidden="true"></div>

<?php
include $front_dir . '/sections/header.php';

while (have_posts()) :
    the_post();

    // ── Page data ────────────────────────────────────────────────────────────
    // The reused plan sections read these three by scope. $plan_from = 0 is
    // load-bearing: plan-hero.php and plan-cta.php both gate their price block
    // on $plan_from > 0, so a zero removes the "From $x" line without either
    // file needing to know that this page is free.
    $plan_months = 0;
    $plan_label  = trial_str('24-hour trial');
    $plan_from   = 0.0;

    $plan_faq_items = iptv_trial_faq_items();

    $trial_url = iptv_config('trial_url', 'https://app.quebeciptv.co/checkout/trial');

    // Optional overrides the reused sections pick up. See the header comments in
    // plan/sections/plan-hero.php and plan-cta.php.
    $plan_hero_subline  = iptv_plan_field('trial_subline', trial_str('The complete service for a full day — every channel, every film, every match. No card, nothing to cancel, and about a minute to set up.'));
    $plan_hero_cta_url  = $trial_url;
    $plan_hero_cta_text = iptv_plan_field('trial_cta_text', trial_str('Start my free trial'));

    // No second button: the primary one already is the trial, and a page should
    // not offer to send you to itself.
    $plan_hero_secondary = null;

    $plan_cta_url = $trial_url;

    $show_steps   = iptv_plan_flag('plan_show_steps', true);
    $show_devices = iptv_plan_flag('plan_show_devices', true);
    $show_reviews = iptv_plan_flag('plan_show_reviews', true);

    include $plan_dir  . '/sections/plan-hero.php';
    include $trial_dir . '/sections/trial-terms.php';
    include $plan_dir  . '/sections/plan-includes.php';

    if ($show_steps) {
        include $front_dir . '/sections/steps.php';
    }

    if ($show_devices) {
        include $front_dir . '/sections/unlock.php';
    }

    if ($show_reviews) {
        include $front_dir . '/sections/reviews.php';
    }

    include $plan_dir  . '/sections/plan-faq.php';
    include $plan_dir  . '/sections/plan-cta.php';
    include $trial_dir . '/sections/trial-schema.php';

endwhile;

include $front_dir . '/sections/footer.php';
?>

<script>
    <?php
    // reviews.js is here because this page includes reviews.php, which renders a
    // real carousel with prev/next buttons. Leaving it out is what left those
    // arrows dead on the plan pages for as long as it did.
    $js_files = array(
        $front_dir . '/js/header.js',
        $front_dir . '/js/currency.js',
        $front_dir . '/js/reviews.js',
    );

    foreach ($js_files as $path) {
        if (file_exists($path)) {
            include $path;
        }
    }
    ?>
</script>

<?php get_footer(); ?>
