<?php
/**
 * Closing CTA
 *
 * Sends the visitor back up to the price grid rather than to a checkout, for
 * the same reason the hero does: the screen count is still theirs to pick.
 *
 * Expects from the including template:
 *   $plan_months (int)  $plan_label (string)  $plan_from (float)
 *
 * Optional, set by templates that are not a priced plan page — template-trial.php
 * and template-reseller.php both reuse this section:
 *
 *   $plan_cta_url (string)  Button target, default '#plan-pricing'.
 *
 * These are isset() guards rather than parameters with defaults because adding a
 * parameter would mean touching template-plan.php, and a page selling four screen
 * counts is the only page where '#plan-pricing' is the right target. Left unset,
 * this renders exactly as it did for the four plan pages.
 */

$cta_title = iptv_plan_field('plan_final_title', sprintf(
    /* translators: %s = plan length, e.g. "1 Month" */
    plan_str('Start your %s plan today'),
    $plan_label
));

$cta_text = iptv_plan_field('plan_final_text', $plan_from > 0
    ? sprintf(
        /* translators: %s = formatted price, e.g. "$16.99" */
        plan_str('From %s. Activated in about a minute, watchable on the TV you already own.'),
        iptv_plan_format_price($plan_from)
    )
    : plan_str('Activated in about a minute, watchable on the TV you already own.'));

$cta_button = iptv_plan_field(
    'plan_final_cta_text',
    iptv_plan_field('plan_cta_text', iptv_text('plan_cta_text', plan_str('See prices')))
);

// Where the button goes. The trial page sends people to the trial checkout and
// the reseller page to the panel signup; neither has a price grid to scroll to.
$cta_url = isset($plan_cta_url) && $plan_cta_url ? $plan_cta_url : '#plan-pricing';
?>
<section class="plan-final">
    <div class="container plan-final-inner">
        <h2 class="plan-final-title"><?php echo esc_html($cta_title); ?></h2>
        <p class="plan-final-text"><?php echo esc_html($cta_text); ?></p>
        <a href="<?php echo esc_url($cta_url); ?>" class="dv2-btn dv2-btn-white dv2-btn-lg">
            <?php echo esc_html($cta_button); ?>
        </a>
    </div>
</section>
