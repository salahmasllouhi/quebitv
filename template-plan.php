<?php
/**
 * Template Name: Plan Page
 * Description: One subscription length, presented and sold. Assign this template
 *              to a page, set "Plan Length" in the sidebar, and the prices,
 *              savings, checkout links, compare table and schema all follow.
 *
 * The four plan pages (1, 3, 6 and 12 months) are the same file. Nothing here
 * is hard-coded to a length — see plan/inc/plan-data.php, which derives
 * everything from the plan_months field.
 *
 * Section order:
 *   1. header        (shared, front page is the source of truth)
 *   2. plan-hero     (headline, "from" price, trust lines)
 *   3. plan-prices   (four screen counts, four buy buttons)
 *   4. plan-includes (what every plan gets — identical on all four pages)
 *   5. plan-audience (who this length suits — the per-page argument)
 *   6. steps         (shared onboarding panel)
 *   7. plan-compare  (all four plans; links the pages to each other)
 *   7b. plan-content (the_content() — long-form body copy, SEO)
 *   8. unlock        (shared device chips)
 *   9. reviews       (shared social proof)
 *  10. plan-faq      (accordion)
 *  11. plan-cta      (closing band)
 *  12. footer        (shared)
 *  13. plan-schema   (Product + FAQPage JSON-LD)
 */

get_header();

$theme_dir  = get_template_directory();
$front_dir  = $theme_dir . '/front-page';
$plan_dir   = $theme_dir . '/plan';
?>

<style>
    <?php
    // Same layers the front page loads, in the same order — design-v2 must come
    // last so it remaps the older tokens — plus the plan-only sheet.
    $css_files = array(
        $front_dir . '/css/variables.css',
        $front_dir . '/css/base.css',
        $front_dir . '/css/header.css',
        $front_dir . '/css/pricing.css',
        $front_dir . '/css/reviews.css',
        $front_dir . '/css/footer.css',
        $front_dir . '/css/responsive.css',
        $front_dir . '/css/redesign-theme.css',
        $front_dir . '/css/cta.css',
        $front_dir . '/css/design-v2.css',
        $front_dir . '/css/design-v2-sections.css',
        $plan_dir  . '/css/plan.css',
    );

    foreach ($css_files as $path) {
        if (file_exists($path)) {
            include $path;
        }
    }
    ?>
</style>

<?php // Page-wide backdrop, as on the front page. See .dv2-grid-wash. ?>
<div class="dv2-grid-wash" aria-hidden="true"></div>

<?php
include $front_dir . '/sections/header.php';

while (have_posts()) :
    the_post();

    // ── Page data ────────────────────────────────────────────────────────────
    // Resolved once, after the_post(), and passed down by scope so no section
    // re-reads ACF or the price table. $plan_from is the cheapest screen count,
    // i.e. the "from" price the hero and the closing band quote.
    $plan_months    = iptv_plan_months();
    $plan_label     = iptv_plan_label($plan_months);
    $plan_from      = iptv_plan_price($plan_months, 1);
    $plan_faq_items = iptv_plan_faq_items($plan_months);

    // Optional shared sections, switchable per page from the editor.
    $show_steps   = iptv_plan_flag('plan_show_steps', true);
    $show_devices = iptv_plan_flag('plan_show_devices', true);
    $show_reviews = iptv_plan_flag('plan_show_reviews', true);

    include $plan_dir . '/sections/plan-hero.php';
    include $plan_dir . '/sections/plan-prices.php';
    include $plan_dir . '/sections/plan-includes.php';
    include $plan_dir . '/sections/plan-audience.php';

    if ($show_steps) {
        include $front_dir . '/sections/steps.php';
    }

    include $plan_dir . '/sections/plan-compare.php';
    include $plan_dir . '/sections/plan-content.php';

    if ($show_devices) {
        include $front_dir . '/sections/unlock.php';
    }

    if ($show_reviews) {
        include $front_dir . '/sections/reviews.php';
    }

    include $plan_dir . '/sections/plan-faq.php';
    include $plan_dir . '/sections/plan-cta.php';
    include $plan_dir . '/sections/plan-schema.php';

endwhile;

include $front_dir . '/sections/footer.php';
?>

<script>
    <?php
    // Only what this page uses. No pricing.js — there is no configurator to
    // drive — and no carousels.js, which targets #sportsCarousel and
    // #brands-carousel, neither of which this page renders. currency.js stays
    // because the header and footer language switcher runs on it.
    //
    // reviews.js is here because the reviews section stopped being a pure-CSS
    // marquee: front-page/sections/reviews.php now renders a real carousel with
    // [data-review-carousel] and ‹ / › buttons that reviews.js binds. The
    // comment that used to sit here still claimed the marquee was CSS-only, so
    // the file was never added and the arrows were dead on all eight plan pages
    // — they rendered, they were focusable, and clicking them did nothing.
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
