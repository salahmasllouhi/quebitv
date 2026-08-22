<?php
/**
 * What the trial includes — four cards.
 *
 * The only genuinely trial-specific section, and the reason this page exists
 * rather than a bare checkout link: everything here is an objection answered
 * before it is raised. What does it cost me, how long do I get, is it the real
 * product, and what is the catch.
 *
 * Uses the front page's .dv2-feature-grid rather than a fourth grid of its own.
 */

$title    = iptv_plan_field('trial_terms_title', trial_str('What the trial actually includes'));
$subtitle = iptv_plan_field('trial_terms_subtitle', trial_str('The whole offer, with nothing held back for the small print.'));

$cards = array();
$rows  = iptv_plan_field('trial_terms', array());

if (is_array($rows)) {
    foreach ($rows as $row) {
        if (!empty($row['title'])) {
            $cards[] = array(
                'title' => $row['title'],
                'text'  => isset($row['text']) ? $row['text'] : '',
            );
        }
    }
}

if (empty($cards)) {
    foreach (iptv_trial_terms_defaults() as $card) {
        $cards[] = array(
            'title' => trial_str($card['title']),
            'text'  => trial_str($card['text']),
        );
    }
}

if (empty($cards)) {
    return;
}
?>
<section class="trial-terms dv2-section" id="trial-terms">
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
