<?php
/**
 * Section: FAQ (Design v2)
 * Single-column accordion; content still comes from the ACF faq_list repeater.
 */
$faq_title    = iptv_text('faq_title', 'Frequently asked questions');
$faq_subtitle = iptv_text('faq_subtitle', 'Got questions? We have answers.');

$faq_items = [];
if (function_exists('get_field')) {
    $front_page_id = get_option('page_on_front');
    $acf_items     = $front_page_id ? get_field('faq_list', $front_page_id) : get_field('faq_list');
    if (!empty($acf_items) && is_array($acf_items)) {
        foreach ($acf_items as $row) {
            if (!empty($row['question'])) {
                $faq_items[] = ['q' => $row['question'], 'a' => isset($row['answer']) ? $row['answer'] : ''];
            }
        }
    }
}

if (empty($faq_items)) {
    $faq_items = [
        ['q' => 'What is IPTV?',                              'a' => 'IPTV delivers TV channels and on-demand content over the internet instead of traditional satellite or cable. Connect a compatible app to our servers with your login and start streaming instantly.'],
        ['q' => 'Which devices are supported?',               'a' => 'Smart TVs, Android TV, Apple TV, Fire Stick, iOS, Android, Windows, Mac, set-top boxes, Chromecast, Roku and Kodi — no new hardware needed.'],
        ['q' => 'How fast does my internet need to be?',      'a' => 'We recommend at least 15–25 Mbps for smooth HD and 4K streaming. Our adaptive bitrate adjusts automatically on slower connections.'],
        ['q' => 'Can I cancel anytime?',                      'a' => 'Yes. There are no contracts and no auto-renew — cancel whenever you like with no penalties.'],
        ['q' => 'How many devices can I use simultaneously?', 'a' => 'Depending on your plan, from 1 up to 4 devices can stream at the same time.'],
        ['q' => 'Do you offer a free trial?',                 'a' => 'Yes, a 24-hour trial is available so you can test the service before committing. Every paid plan also carries a 30-day money-back guarantee.'],
        ['q' => 'What payment methods do you accept?',        'a' => 'Visa, Mastercard, PayPal and Bitcoin, all through a secure SSL checkout.'],
        ['q' => 'What countries and languages are available?', 'a' => 'Content spans 198 countries with multi-language subtitles and full EPG support.'],
        ['q' => 'How can I contact support?',                 'a' => 'You can reach our support team 24/7 at <a href="mailto:support@quebeciptv.co">support@quebeciptv.co</a>.'],
    ];
}
?>
<section class="faq dv2-section" id="faq">
    <div class="faq-container">
        <div class="dv2-section-head">
            <h2><?php echo esc_html($faq_title); ?></h2>
            <p><?php echo esc_html($faq_subtitle); ?></p>
        </div>

        <div class="dv2-faq-list">
            <?php foreach ($faq_items as $item) :
                if (empty($item['q'])) {
                    continue;
                }
                ?>
                <div class="dv2-faq-item">
                    <button class="dv2-faq-q" type="button" aria-expanded="false">
                        <span><?php echo esc_html($item['q']); ?></span>
                        <span class="dv2-faq-icon" aria-hidden="true">›</span>
                    </button>
                    <div class="dv2-faq-a">
                        <div class="dv2-faq-a-inner"><?php echo wp_kses_post($item['a']); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
    // One panel open at a time, matching the mockup's behaviour.
    document.querySelectorAll('.dv2-faq-q').forEach(function (button) {
        button.addEventListener('click', function () {
            var item = button.parentElement;
            var willOpen = !item.classList.contains('open');

            document.querySelectorAll('.dv2-faq-item.open').forEach(function (openItem) {
                openItem.classList.remove('open');
                var q = openItem.querySelector('.dv2-faq-q');
                if (q) q.setAttribute('aria-expanded', 'false');
            });

            if (willOpen) {
                item.classList.add('open');
                button.setAttribute('aria-expanded', 'true');
            }
        });
    });
</script>
