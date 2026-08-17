<?php
/**
 * Channel FAQ Section
 * Simple strings: Polylang String Translations via tpl_str().
 * FAQ items (repeater): ACF Options via get_field() directly.
 * All hardcoded default Q&A go through tpl_str() to appear in Polylang Translations.
 * Dynamic: $channel_name (use %s in question/answer fields)
 */

$lang = function_exists('pll_current_language') ? pll_current_language() : 'en';
$post_id = 'options_' . $lang;

$faq_tag = tpl_str('FAQ');
$faq_title = tpl_str('Frequently Asked <span class="gradient-text">Questions</span>');
$faq_subtitle = tpl_str('Common questions about watching %s with Quebec IPTV');
$faq_items = function_exists('get_field') ? get_field('tpl_faq_items', $post_id) : [];

// Default FAQ items — all go through tpl_str() so they appear in Polylang Translations
if (empty($faq_items)) {
    $faq_items = [
        ['faq_q' => tpl_str('How do I watch %s with Quebec IPTV?'), 'faq_a' => tpl_str('Simply subscribe to any Quebec IPTV plan, download our app on your device, and search for %s. You\'ll be streaming in minutes.')],
        ['faq_q' => tpl_str('Is %s available in 4K?'), 'faq_a' => tpl_str('Yes! When available, %s streams in full 4K Ultra HD quality with Dolby audio support.')],
        ['faq_q' => tpl_str('Can I record shows from %s?'), 'faq_a' => tpl_str('Yes, our built-in DVR feature lets you record any program from %s and watch it later. You can also use catch-up to rewind up to 7 days.')],
        ['faq_q' => tpl_str('What devices can I watch %s on?'), 'faq_a' => tpl_str('You can watch %s on Smart TV, Android, iOS, Amazon Firestick, Roku, Apple TV, MAG boxes, Windows, Mac, and more.')],
        ['faq_q' => tpl_str('Is there a trial?'), 'faq_a' => tpl_str('Yes! All Quebec IPTV plans come with a 24h trial. You can cancel anytime during the trial period.')],
        ['faq_q' => tpl_str('Do I need a cable subscription?'), 'faq_a' => tpl_str('No! Quebec IPTV is a standalone streaming service. No cable, no satellite dish, no contracts. Just an internet connection.')],
    ];
}

$cn = esc_html($channel_name);
?>

<section class="faq" id="faq">
    <div class="faq-container">
        <div class="section-header">
            <div class="section-tag"><?php echo esc_html($faq_tag); ?></div>
            <h2 class="section-title">
                <?php echo wp_kses_post($faq_title); ?>
            </h2>
            <p class="section-subtitle">
                <?php echo wp_kses_post(sprintf($faq_subtitle, $cn)); ?>
            </p>
        </div>

        <div class="faq-list">
            <?php foreach ($faq_items as $item):
                $question = isset($item['faq_q']) ? sprintf($item['faq_q'], $cn) : '';
                $answer = isset($item['faq_a']) ? sprintf($item['faq_a'], $cn) : '';
                ?>
                <div class="faq-item">
                    <button class="faq-question">
                        <?php echo esc_html($question); ?>
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <?php echo wp_kses_post($answer); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
    document.querySelectorAll('.faq-question').forEach(button => {
        button.addEventListener('click', () => {
            const item = button.parentElement;
            item.classList.toggle('open');
        });
    });
</script>