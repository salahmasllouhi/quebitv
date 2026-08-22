<?php
/**
 * Credit packs — the price grid.
 *
 * Reuses .plan-price-grid and .plan-price-card wholesale from plan/css/plan.css:
 * the four-across layout, the lift on the popular card, the flag, and the
 * two-up-then-one-up collapse are all solved there and there is no reason for
 * this page to solve them again slightly differently.
 *
 * Prices are formatted through iptv_plan_format_price(), so a credit price and
 * a subscription price can never render in different shapes or currencies.
 */

$title    = iptv_plan_field('reseller_packs_title', reseller_str('Credit packs'));
$subtitle = iptv_plan_field('reseller_packs_subtitle', reseller_str('One credit is one month of one subscription. Larger packs cost less per credit, and nothing expires.'));

$packs = iptv_reseller_packs();

if (empty($packs)) {
    return;
}
?>
<section class="reseller-packs dv2-section" id="reseller-packs">
    <div class="container">

        <div class="dv2-section-head">
            <h2><?php echo esc_html($title); ?></h2>
            <p><?php echo esc_html($subtitle); ?></p>
        </div>

        <div class="plan-price-grid">
            <?php foreach ($packs as $pack) : ?>
                <div class="plan-price-card<?php echo !empty($pack['popular']) ? ' plan-price-card--popular' : ''; ?>">

                    <?php if (!empty($pack['popular'])) : ?>
                        <span class="plan-price-flag"><?php echo esc_html(reseller_str('Most popular')); ?></span>
                    <?php endif; ?>

                    <h3 class="plan-price-screens">
                        <?php printf(
                            /* translators: %d = number of credits */
                            esc_html(reseller_str('%d credits')),
                            (int) $pack['credits']
                        ); ?>
                    </h3>

                    <p class="plan-price-amount">
                        <?php echo esc_html(iptv_plan_format_price($pack['price'])); ?>
                    </p>

                    <p class="plan-price-per">
                        <?php printf(
                            /* translators: %s = formatted price, e.g. "$8.00" */
                            esc_html(reseller_str('%s per credit')),
                            esc_html(iptv_plan_format_price(iptv_reseller_per_credit($pack)))
                        ); ?>
                    </p>

                    <a class="plan-price-btn dv2-btn dv2-btn-primary"
                        href="<?php echo esc_url(iptv_reseller_checkout_url($pack['credits'])); ?>">
                        <?php echo esc_html(reseller_str('Buy this pack')); ?>
                    </a>

                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>
