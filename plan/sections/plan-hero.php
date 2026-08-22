<?php
/**
 * Plan hero — two columns: copy left, image right
 *
 * Reuses the front page's .dv2-hero grid rather than defining another one, so
 * the column split, the gap and the stack-at-1024px behaviour are shared and
 * cannot drift. Only the contents of the left column are plan-specific.
 *
 * The CTA points at the price grid rather than straight at checkout: the price
 * depends on how many screens the visitor wants, and sending them to a checkout
 * for a screen count they never chose is how you get refunds.
 *
 * Expects from the including template:
 *   $plan_months (int)  $plan_label (string)  $plan_from (float)
 *
 * Optional, set by templates that are not a priced plan page — template-trial.php
 * and template-reseller.php both reuse this section:
 *
 *   $plan_hero_subline   (string)            overrides the length-derived subline
 *   $plan_hero_cta_url   (string)            primary CTA target, default '#plan-pricing'
 *   $plan_hero_cta_text  (string)            primary CTA label
 *   $plan_hero_secondary (array{url,text}|null)  the second CTA; pass null to
 *                                            remove it, which the trial page does
 *                                            because it must not link to itself
 *
 * These are isset() guards rather than parameters with defaults because adding a
 * parameter would mean touching template-plan.php, and a page selling four screen
 * counts is the only page where '#plan-pricing' is the right target. Left unset,
 * this renders exactly as it did for the four plan pages — $plan_from = 0 already
 * removes the price line, which is what a free page passes.
 */

// plan_str() inside iptv_text(): the front page has no plan_eyebrow field, so
// iptv_text() would end at pll__('IPTV Subscription') and return it unchanged —
// English on every Nordic page. Handing it the already-translated string keeps
// the front-page override working while giving the bundled copy a chance.
$hero_eyebrow = iptv_plan_field('plan_eyebrow', iptv_text('plan_eyebrow', plan_str('IPTV Subscription')));

// The page title, not a format string. Each language's title is written out in
// plan/inc/plan-pages-setup.php, so it is already correct everywhere; building
// the H1 from plan_str('%s IPTV Subscription') instead produced half-translated
// headlines like "12 måneder IPTV Subscription" until someone remembered to
// translate the format too. Overridable per page by the plan_headline field.
$hero_headline = iptv_plan_field('plan_headline', get_the_title());

if (!$hero_headline) {
    $hero_headline = sprintf(
        /* translators: %s = plan length, e.g. "1 Month" */
        plan_str('%s IPTV Subscription'),
        $plan_label
    );
}
$hero_subline = isset($plan_hero_subline) && $plan_hero_subline
    ? $plan_hero_subline
    : iptv_plan_field('plan_subline', $plan_months === 1
    ? plan_str('The whole service, one month at a time. No contract, no auto-renew — stop whenever you like.')
    : sprintf(
        /* translators: %s = plan length, e.g. "6 Months" */
        plan_str('The whole service for %s. One payment, no contract, no auto-renew.'),
        $plan_label
    ));

// Three short reassurances. ACF repeater when filled, otherwise these.
$hero_points = array();
$rows        = iptv_plan_field('plan_hero_points', array());

if (is_array($rows)) {
    foreach ($rows as $row) {
        if (!empty($row['text'])) {
            $hero_points[] = $row['text'];
        }
    }
}

if (empty($hero_points)) {
    $hero_points = array(
        plan_str('Watching in 60 seconds'),
        plan_str('No contract, no auto-renew'),
        plan_str('24/7 support'),
    );
}

$hero_cta = isset($plan_hero_cta_text) && $plan_hero_cta_text
    ? $plan_hero_cta_text
    : iptv_plan_field('plan_cta_text', iptv_text('plan_cta_text', plan_str('See prices')));

$hero_cta_url = isset($plan_hero_cta_url) && $plan_hero_cta_url ? $plan_hero_cta_url : '#plan-pricing';

// The second CTA. A page can pass null to drop it — the trial page does, because
// its own primary button already is the trial and a page should not offer to
// send you to itself.
if (array_key_exists('plan_hero_secondary', get_defined_vars())) {
    // Normalised to an array because null is the documented way to drop the
    // button, and reading ['url'] off a null is a warning on PHP 8 rather than
    // the quiet false it used to be.
    $secondary = is_array($plan_hero_secondary) ? $plan_hero_secondary : array();
} else {
    $secondary = array(
        'url'  => iptv_config('trial_url', 'https://app.quebeciptv.co/checkout/trial'),
        'text' => iptv_text('trial_cta', 'Start a 24-hour trial — no card'),
    );
}

// ── Hero image ───────────────────────────────────────────────────────────────
// Set per page from the editor. Accepts either shape the ACF field can be
// configured to return, so switching return_format later does not break it.
// Until one is attached the column holds a placeholder of the same proportions,
// so dropping the real image in does not reflow the page.
// The ID is kept alongside the URL because it is what lets WordPress build a
// srcset. Taking $hero_image['url'] alone means the *full* file: the generated
// heroes are 2400px and up to 6MB, and this column is never wider than 500px,
// so that shipped ~12x more bytes than the layout can use and wrecked LCP.
$hero_image_id  = 0;
$hero_image_url = '';
$hero_image_alt = '';
$hero_image     = iptv_plan_field('plan_hero_image', '');

if (is_array($hero_image)) {
    $hero_image_id  = !empty($hero_image['ID']) ? (int) $hero_image['ID'] : 0;
    $hero_image_url = !empty($hero_image['url']) ? $hero_image['url'] : '';
    $hero_image_alt = !empty($hero_image['alt']) ? $hero_image['alt'] : '';
} elseif (is_numeric($hero_image)) {
    $hero_image_id  = (int) $hero_image;
    $hero_image_url = wp_get_attachment_image_url($hero_image_id, 'large');
    $hero_image_alt = get_post_meta($hero_image_id, '_wp_attachment_image_alt', true);
} elseif (is_string($hero_image) && $hero_image) {
    $hero_image_url = $hero_image;
}

if ($hero_image_url && !$hero_image_alt) {
    $hero_image_alt = $hero_headline;
}
?>
<section class="plan-hero dv2-hero container">

    <div class="dv2-hero-copy plan-hero-copy">

        <p class="plan-hero-eyebrow"><?php echo esc_html($hero_eyebrow); ?></p>

        <h1 class="plan-hero-title"><?php echo esc_html($hero_headline); ?></h1>

        <p class="plan-hero-sub"><?php echo esc_html($hero_subline); ?></p>

        <?php if ($plan_from > 0) : ?>
            <p class="plan-hero-price">
                <span class="plan-hero-price-label"><?php echo esc_html(iptv_text('plan_from_label', plan_str('From'))); ?></span>
                <span class="plan-hero-price-value"><?php echo esc_html(iptv_plan_format_price($plan_from)); ?></span>
                <span class="plan-hero-price-per">
                    <?php echo esc_html($plan_months === 1
                        ? iptv_text('per_month', 'per month')
                        : sprintf(
                            /* translators: %s = plan length, e.g. "6 Months" */
                            plan_str('for %s'),
                            $plan_label
                        )); ?>
                </span>
            </p>
        <?php endif; ?>

        <div class="plan-hero-actions">
            <a href="<?php echo esc_url($hero_cta_url); ?>" class="dv2-btn dv2-btn-primary dv2-btn-lg">
                <?php echo esc_html($hero_cta); ?>
            </a>
            <?php // Same white-button treatment as the front-page hero's second CTA. ?>
            <?php if (!empty($secondary['url']) && !empty($secondary['text'])) : ?>
                <a href="<?php echo esc_url($secondary['url']); ?>" class="dv2-btn dv2-hero-link">
                    <?php echo esc_html($secondary['text']); ?>
                </a>
            <?php endif; ?>
        </div>

        <ul class="plan-hero-points">
            <?php foreach ($hero_points as $point) : ?>
                <li><?php echo esc_html($point); ?></li>
            <?php endforeach; ?>
        </ul>

    </div>

    <div class="dv2-hero-media plan-hero-media">
        <div class="dv2-hero-glow" aria-hidden="true"></div>

        <?php if ($hero_image_id) : ?>
            <?php
            // wp_get_attachment_image() rather than a hand-rolled <img>: it adds
            // srcset/sizes so the browser picks a file that fits the column, and
            // width/height so the space is reserved before it loads. 'large' is
            // 1024px, which covers the 500px column at 2x.
            echo wp_get_attachment_image($hero_image_id, 'large', false, array(
                'alt'           => $hero_image_alt,
                'sizes'         => '(max-width: 1024px) 92vw, 500px',
                'fetchpriority' => 'high',
                'decoding'      => 'async',
            ));
            ?>
        <?php elseif ($hero_image_url) : ?>
            <img src="<?php echo esc_url($hero_image_url); ?>"
                alt="<?php echo esc_attr($hero_image_alt); ?>"
                fetchpriority="high" decoding="async">
        <?php else : ?>
            <?php
            // Placeholder, not an empty column: it holds the space the real
            // image will occupy and says so, rather than leaving the hero
            // looking half-finished. Hidden from assistive tech — there is no
            // information here.
            ?>
            <div class="plan-hero-placeholder" aria-hidden="true">
                <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <path d="m21 15-5-5L5 21"></path>
                </svg>
                <span><?php echo esc_html(plan_str('Hero image')); ?></span>
            </div>
        <?php endif; ?>
    </div>

</section>
