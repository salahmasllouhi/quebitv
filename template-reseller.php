<?php
/**
 * Template Name: Reseller Page
 * Description: The reseller panel and its credit packs. Assign this template to
 *              a page; the packs, the FAQ and the Service schema follow.
 *
 * Built the same way as template-trial.php and for the same reason: the hero,
 * the FAQ accordion and the closing band already exist and work, so this file
 * supplies the four sections that are genuinely reseller-specific and reuses the
 * rest. The credit-pack grid is plan.css's price-card component with different
 * numbers in it, and the steps are the front page's journey component.
 *
 * The reviews section is off by default here. The reviews on the front page are
 * end-customer reviews about picture quality and channel counts, which read
 * wrong on a page selling a wholesale panel to a business.
 *
 * Section order:
 *   1. header           (shared)
 *   2. plan-hero        (reused; "from" price is the cheapest per-credit rate)
 *   3. reseller-panel   (what the panel does)
 *   4. reseller-packs   (credit packs — the price grid)
 *   5. reseller-steps   (apply, get the panel, load credits, sell)
 *   6. unlock           (shared device chips — what your customers can watch on)
 *   7. plan-faq         (reused, fed reseller questions)
 *   8. plan-cta         (reused, pointed at the panel application)
 *   9. footer           (shared)
 *  10. reseller-schema  (Service + AggregateOffer + FAQPage JSON-LD)
 *
 * @package Quebec_IPTV
 */

get_header();

$theme_dir    = get_template_directory();
$front_dir    = $theme_dir . '/front-page';
$plan_dir     = $theme_dir . '/plan';
$reseller_dir = $theme_dir . '/reseller';
?>

<style>
    <?php
    // plan.css is required, not incidental: the reused hero and closing band
    // emit .plan-hero-* and .plan-final-* class names, and the credit-pack grid
    // reuses .plan-price-*. design-v2 stays last of the shared layers.
    $css_files = array(
        $front_dir    . '/css/variables.css',
        $front_dir    . '/css/base.css',
        $front_dir    . '/css/header.css',
        $front_dir    . '/css/reviews.css',
        $front_dir    . '/css/footer.css',
        $front_dir    . '/css/responsive.css',
        $front_dir    . '/css/redesign-theme.css',
        $front_dir    . '/css/cta.css',
        $front_dir    . '/css/design-v2.css',
        $front_dir    . '/css/design-v2-sections.css',
        $plan_dir     . '/css/plan.css',
        $reseller_dir . '/css/reseller.css',
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
    // The reused plan sections read these three by scope.
    $plan_months = 0;
    $plan_label  = reseller_str('reseller panel');

    // The hero's "From" line quotes the best per-credit rate, which is the
    // number a reseller is actually shopping on — not the cheapest pack, which
    // is simply the smallest.
    $plan_from = 0.0;
    foreach (iptv_reseller_packs() as $pack) {
        $per = iptv_reseller_per_credit($pack);
        if ($per > 0 && ($plan_from === 0.0 || $per < $plan_from)) {
            $plan_from = $per;
        }
    }

    $plan_faq_items = iptv_reseller_faq_items();

    $reseller_url = iptv_config('reseller_url', 'https://app.quebeciptv.co/reseller');

    $plan_hero_subline  = iptv_plan_field('reseller_subline', reseller_str('Sell IPTV under your own name, at your own prices, from a panel that creates lines in seconds. Credits never expire and there is no monthly commitment.'));
    $plan_hero_cta_url  = $reseller_url;
    $plan_hero_cta_text = iptv_plan_field('reseller_cta_text', reseller_str('Apply for a panel'));

    // Second button goes down to the packs rather than off-site: somebody who
    // has not seen the prices yet is not ready for the application form.
    $plan_hero_secondary = array(
        'url'  => '#reseller-packs',
        'text' => reseller_str('See credit prices'),
    );

    $plan_cta_url = $reseller_url;

    // Off by default here, unlike the plan and trial pages. See the note above.
    $show_devices = iptv_plan_flag('plan_show_devices', true);
    $show_reviews = iptv_plan_flag('plan_show_reviews', false);

    include $plan_dir     . '/sections/plan-hero.php';
    include $reseller_dir . '/sections/reseller-panel.php';
    include $reseller_dir . '/sections/reseller-packs.php';
    include $reseller_dir . '/sections/reseller-steps.php';

    if ($show_devices) {
        include $front_dir . '/sections/unlock.php';
    }

    if ($show_reviews) {
        include $front_dir . '/sections/reviews.php';
    }

    include $plan_dir     . '/sections/plan-faq.php';
    include $plan_dir     . '/sections/plan-cta.php';
    include $reseller_dir . '/sections/reseller-schema.php';

endwhile;

include $front_dir . '/sections/footer.php';
?>

<script>
    <?php
    // reviews.js only matters when the reviews section is on, but including it
    // unconditionally costs a few hundred bytes and removes the trap that left
    // the plan pages' carousel arrows dead — the file no-ops when there is no
    // [data-review-carousel] on the page.
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
