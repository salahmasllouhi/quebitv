<?php
/**
 * How it works — four steps.
 *
 * The copy is reseller-specific (apply, get the panel, load credits, sell) but
 * the component is the front page's .dv2-journey-*, so no new CSS is needed for
 * the numbered cards, the connecting bar or the responsive collapse.
 */

$title = iptv_plan_field('reseller_steps_title', reseller_str('How it works'));

$steps = array();
$rows  = iptv_plan_field('reseller_steps', array());

if (is_array($rows)) {
    foreach ($rows as $row) {
        if (!empty($row['title'])) {
            $steps[] = array('title' => $row['title'], 'text' => isset($row['text']) ? $row['text'] : '');
        }
    }
}

if (empty($steps)) {
    foreach (iptv_reseller_steps_defaults() as $step) {
        $steps[] = array('title' => reseller_str($step['title']), 'text' => reseller_str($step['text']));
    }
}

if (empty($steps)) {
    return;
}
?>
<section class="reseller-steps dv2-section" id="how-it-works">
    <div class="container">

        <div class="dv2-section-head">
            <h2><?php echo esc_html($title); ?></h2>
        </div>

        <div class="dv2-journey-cards">
            <?php foreach ($steps as $i => $step) : ?>
                <div class="dv2-journey-card">
                    <span class="dv2-journey-num"><?php echo (int) ($i + 1); ?></span>
                    <h3 class="dv2-journey-title"><?php echo esc_html($step['title']); ?></h3>
                    <p class="dv2-journey-copy"><?php echo esc_html($step['text']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>
