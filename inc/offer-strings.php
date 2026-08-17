<?php
/**
 * Offer Landing Page – Polylang String Registration
 *
 * All static UI strings for the offer landing page template are registered here
 * so they appear in Languages → Translations and can be translated per language.
 *
 * Dynamic content (headline, subline, FAQ, etc.) is managed via ACF fields and
 * does NOT need to be registered here — those are entered per-language directly
 * in the page editor.
 *
 * Usage in templates: iptv_text('key', 'Default English text')
 */

add_action('init', function () {
    if (!function_exists('pll_register_string')) {
        return;
    }

    $group = 'Offer Landing Page';

    // ── Hero section ─────────────────────────────────────────────────────────
    pll_register_string('offer_hero_badge', 'Limited Time Offer', $group);
    pll_register_string('offer_hero_no_contract', 'No contract, cancel anytime', $group);
    pll_register_string('offer_hero_instant', 'Instant delivery by email', $group);

    // ── Features section ─────────────────────────────────────────────────────
    pll_register_string('offer_features_tag', 'What You Get', $group);
    pll_register_string('offer_features_title', 'Everything included in your plan', $group);
    pll_register_string('offer_feat_1', '40,000+ live channels', $group);
    pll_register_string('offer_feat_2', '200,000+ movies & series', $group);
    pll_register_string('offer_feat_3', '4K & 8K Ultra HD quality', $group);
    pll_register_string('offer_feat_4', 'Multi-language subtitles', $group);
    pll_register_string('offer_feat_5', 'Daily content updates', $group);
    pll_register_string('offer_feat_6', 'Any device — TV, phone, tablet', $group);

    // ── Steps section ────────────────────────────────────────────────────────
    pll_register_string('offer_steps_tag', 'How It Works', $group);
    pll_register_string('offer_steps_title', '3 simple steps to get started', $group);
    pll_register_string('offer_step_1_title', 'Pick Your Plan', $group);
    pll_register_string('offer_step_1_desc', "Click the button below and you'll go straight to checkout — no browsing required.", $group);
    pll_register_string('offer_step_2_title', 'Complete Checkout', $group);
    pll_register_string('offer_step_2_desc', "Enter your email and phone. That's all we need. Secure payment in seconds.", $group);
    pll_register_string('offer_step_3_title', 'Get %d Months Activated', $group);
    pll_register_string('offer_step_3_desc', 'Your subscription is activated instantly. Pay for %1$d months, enjoy %2$d months free — all in one go.', $group);

    // ── Pricing section ──────────────────────────────────────────────────────
    pll_register_string('offer_pricing_tag', 'Pricing', $group);
    pll_register_string('offer_pricing_title', 'Pay for %1$d months, get %2$d months', $group);
    pll_register_string('offer_pricing_for', 'for %d months', $group);
    pll_register_string('offer_pricing_value_label', 'Regular value', $group);
    pll_register_string('offer_pricing_guarantee', 'Secure payment · Instant delivery · No contract', $group);

    // ── ACF field defaults (used as iptv_text fallbacks when ACF isn't filled) ─
    pll_register_string('offer_headline', '12 Months, Get 3 Free', $group);
    pll_register_string('offer_subline', 'Total = 15 months. Pay for 12.', $group);
    pll_register_string('offer_cta_text', 'Get This Deal', $group);
    pll_register_string('offer_highlight_text', 'You get 3 months FREE!', $group);
    pll_register_string('offer_urgency_line', "Offer ends soon — don't miss out", $group);

    // ── FAQ section ──────────────────────────────────────────────────────────
    pll_register_string('offer_faq_tag', 'FAQ', $group);
    pll_register_string('offer_faq_title', "Any questions? We've got answers.", $group);
    pll_register_string('offer_faq_q1', 'What devices does it work on?', $group);
    pll_register_string('offer_faq_a1', 'Quebec IPTV works on Smart TVs, Android, iPhone & iPad, Amazon Firestick, MAG boxes, Windows and macOS — basically any device with an internet connection.', $group);
    pll_register_string('offer_faq_q2', 'Is there a contract or auto-renewal?', $group);
    pll_register_string('offer_faq_a2', "No contract. No auto-renewal. You pay once and your subscription runs for the full period. When it expires, it's up to you to renew.", $group);
    pll_register_string('offer_faq_q3', 'How do I get the free months?', $group);
    pll_register_string('offer_faq_a3', 'The bonus months are included automatically in this offer — no codes needed. When you purchase, your full subscription (paid + bonus months) is activated in one go.', $group);
    pll_register_string('offer_faq_q4', 'When does it activate?', $group);
    pll_register_string('offer_faq_a4', 'Instantly after payment is confirmed. Your login details are sent to your email within minutes. If there is any delay, our support team is available 24/7.', $group);
    pll_register_string('offer_faq_q5', 'Is payment secure?', $group);
    pll_register_string('offer_faq_a5', 'Yes. Payments are processed via industry-standard secure gateways with 256-bit SSL encryption. We never store your card details.', $group);
    pll_register_string('offer_faq_q6', 'Can I get support if I need help?', $group);
    pll_register_string('offer_faq_a6', 'Absolutely. Our support team is available 24/7 via email at <a href="mailto:support@quebeciptv.co">support@quebeciptv.co</a>.', $group, true);

    // ── Final CTA section ────────────────────────────────────────────────────
    pll_register_string('offer_final_cta_title', "Don't miss %d months for the price of %d", $group);
    pll_register_string('offer_final_cta_subtitle', '40,000+ channels. 200,000+ movies. Any device. Activated instantly.', $group);
});
