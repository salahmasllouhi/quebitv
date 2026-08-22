<?php
/**
 * What the panel does — six cards.
 *
 * Reuses the front page's .dv2-feature-grid. The copy is the only new thing:
 * each card answers a question somebody deciding whether to run a business on
 * this would otherwise have to ask in a chat window before they could commit.
 */

$title    = iptv_plan_field('reseller_panel_title', reseller_str('What the panel does'));
$subtitle = iptv_plan_field('reseller_panel_subtitle', reseller_str('Everything you need to run subscriptions as a business rather than as a favour.'));

$cards = array();
$rows  = iptv_plan_field('reseller_panel_features', array());

if (is_array($rows)) {
    foreach ($rows as $row) {
        if (!empty($row['title'])) {
            $cards[] = array('title' => $row['title'], 'text' => isset($row['text']) ? $row['text'] : '');
        }
    }
}

if (empty($cards)) {
    foreach (iptv_reseller_panel_defaults() as $card) {
        $cards[] = array('title' => reseller_str($card['title']), 'text' => reseller_str($card['text']));
    }
}

if (empty($cards)) {
    return;
}
?>
<section class="reseller-panel dv2-section" id="panel">
    <div class="container">

        <div class="dv2-section-head">
            <h2><?php echo esc_html($title); ?></h2>
            <p><?php echo esc_html($subtitle); ?></p>
        </div>

        <div class="dv2-feature-grid">
            <?php foreach ($cards as $card) : ?>
                <div class="dv2-feature-card">
                    <div class="dv2-feature-card-head">
                        <h3 class="dv2-feature-card-title"><?php echo esc_html($card['title']); ?></h3>
                    </div>
                    <p class="dv2-feature-card-desc"><?php echo esc_html($card['text']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>
