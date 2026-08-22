<?php
/**
 * M3U FAQ
 *
 * Same accordion markup and behaviour as the front page and the plan pages, so
 * it inherits the .dv2-faq-* styling rather than defining a third one. Rows
 * come from iptv_m3u_faq_items(), which m3u-schema.php reads too — one source,
 * so the accordion and the rich result cannot list different questions.
 */

$faq_title = iptv_plan_field('m3u_faq_title', m3u_str('Questions about M3U playlists'));
$items = iptv_m3u_faq_items();

if (empty($items)) {
    return;
}
?>
<section class="m3u-faq dv2-section" id="faq">
    <div class="container">

        <div class="dv2-section-head">
            <h2><?php echo esc_html($faq_title); ?></h2>
        </div>

        <div class="dv2-faq-list">
            <?php foreach ($items as $item) : ?>
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
    // One panel open at a time, matching the front page and the plan pages.
    document.querySelectorAll('.m3u-faq .dv2-faq-q').forEach(function (button) {
        button.addEventListener('click', function () {
            var item = button.parentElement;
            var willOpen = !item.classList.contains('open');

            document.querySelectorAll('.m3u-faq .dv2-faq-item.open').forEach(function (openItem) {
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
