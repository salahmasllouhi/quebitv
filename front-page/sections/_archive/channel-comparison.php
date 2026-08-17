<?php
/**
 * Channel Comparison Section
 * Simple strings: Polylang String Translations via tpl_str().
 * Repeater (comp_rows): ACF Options via get_field() directly.
 * Dynamic: $channel_name
 */

$lang = function_exists('pll_current_language') ? pll_current_language() : 'en';
$post_id = 'options_' . $lang;

$comp_tag = tpl_str('Compare');
$comp_title = tpl_str('How We Compare');
$comp_subtitle = tpl_str('See how Quebec IPTV compares to cable for watching %s');
$competitor_name = tpl_str('Cable TV');
$our_name = tpl_str('Quebec IPTV');
$comp_rows = function_exists('get_field') ? get_field('tpl_comp_rows', $post_id) : [];
$comp_theirs_total_lbl = tpl_str('Annual Cost');
$comp_theirs_total = tpl_str('$1,200+');
$comp_ours_total_lbl = tpl_str('Quebec IPTV Annual Cost');
$comp_ours_total = tpl_str('$69.99');
$comp_ours_per_month = tpl_str('Just ~$5.83/month');
$savings_label = tpl_str('Your Annual Savings');
$savings_value = tpl_str('$1,100+');


// Default rows if the repeater is not yet saved
if (empty($comp_rows)) {
    $comp_rows = [
        ['row_label' => 'Monthly Cost', 'row_theirs' => '$85-150+', 'row_ours' => 'From $5.83/mo'],
        ['row_label' => 'Contract', 'row_theirs' => '12-24 months', 'row_ours' => 'No contract'],
        ['row_label' => 'Installation', 'row_theirs' => 'Technician required', 'row_ours' => 'Instant activation'],
        ['row_label' => 'Equipment', 'row_theirs' => 'Rental fees ($10+/mo)', 'row_ours' => 'Use your own devices'],
        ['row_label' => 'Channels', 'row_theirs' => '200-300 channels', 'row_ours' => '40,000+ channels'],
        ['row_label' => '4K Content', 'row_theirs' => 'Limited / Extra cost', 'row_ours' => 'Included free'],
        ['row_label' => 'DVR', 'row_theirs' => 'Extra $15-20/mo', 'row_ours' => 'Included free'],
        ['row_label' => 'Simultaneous Streams', 'row_theirs' => '1 per TV box', 'row_ours' => 'Up to 4 devices'],
    ];
}

$cn = esc_html($channel_name);
?>

<section class="comparison">
    <div class="section-header">
        <div class="section-tag"><?php echo esc_html($comp_tag); ?></div>
        <h2 class="section-title">
            <?php echo wp_kses_post($comp_title); ?>
        </h2>
        <p class="section-subtitle">
            <?php echo wp_kses_post(sprintf($comp_subtitle, $cn)); ?>
        </p>
    </div>

    <div class="comparison-grid">
        <!-- Column 1: Competitor -->
        <div class="comparison-card without">
            <div class="comparison-header">
                <div class="comparison-dot red"></div>
                <span class="comparison-label"><?php echo esc_html(strtoupper($competitor_name)); ?></span>
            </div>
            <div class="comparison-items">
                <?php foreach ($comp_rows as $row): ?>
                    <div class="comparison-item">
                        <span class="comparison-item-name"><?php echo esc_html($row['row_label']); ?></span>
                        <span class="comparison-item-value"><?php echo esc_html($row['row_theirs']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="comparison-total">
                <div class="comparison-total-label"><?php echo esc_html($comp_theirs_total_lbl); ?></div>
                <div class="comparison-total-value expensive"><?php echo esc_html($comp_theirs_total); ?></div>
            </div>
        </div>

        <!-- Column 2: Us -->
        <div class="comparison-card with">
            <div class="comparison-header">
                <div class="comparison-dot green"></div>
                <span class="comparison-label"><?php echo esc_html(strtoupper($our_name)); ?></span>
            </div>
            <div class="comparison-items">
                <?php foreach ($comp_rows as $row): ?>
                    <div class="comparison-item">
                        <span class="comparison-item-name"><?php echo esc_html($row['row_label']); ?></span>
                        <span class="comparison-item-value included"><?php echo esc_html($row['row_ours']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="comparison-total">
                <div class="comparison-total-label"><?php echo esc_html($comp_ours_total_lbl); ?></div>
                <div class="comparison-total-value cheap"><?php echo esc_html($comp_ours_total); ?></div>
                <div class="comparison-per-month"><?php echo esc_html($comp_ours_per_month); ?></div>
            </div>
        </div>
    </div>

    <!-- Savings Banner -->
    <div class="savings-banner">
        <div class="savings-label"><?php echo esc_html($savings_label); ?></div>
        <div class="savings-value"><?php echo esc_html($savings_value); ?></div>
    </div>
</section>