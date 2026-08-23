<?php
/**
 * Section: Support (Design v2)
 *
 * Three contact cards. The cards themselves come from iptv_contact_cards_grid()
 * in inc/contact-cards.php, which the Contact page renders too through the
 * [nordictv_contact] shortcode — one definition, so the two cannot drift apart.
 */
$title    = iptv_text('contact_title', 'We\'re here to help');
$subtitle = iptv_text('contact_subtitle', 'Reach out anytime by email or WhatsApp. Our support team typically responds within minutes.');
?>
<section id="contact" class="contact dv2-section">
    <div class="container">
        <div class="dv2-section-head">
            <h2><?php echo esc_html($title); ?></h2>
            <p style="max-width:620px;"><?php echo esc_html($subtitle); ?></p>
        </div>

        <?php echo iptv_contact_cards_grid(); ?>
    </div>
</section>
