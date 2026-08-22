<?php
/**
 * Template Name: Offer Landing Page
 * Description: Single-offer sales page with ACF-driven content. Focused on one action: click and buy.
 */
get_header();

$front_page_dir = get_template_directory() . '/front-page';
$css_dir = $front_page_dir . '/css';
$js_dir = $front_page_dir . '/js';
$sections_dir = $front_page_dir . '/sections';

// ── Resolve product & checkout URL ───────────────────────────────────────────
// Redirect all CTA buttons to the user dashboard instead of WooCommerce checkout.
$offer_checkout_url = 'https://app.quebeciptv.co/';

/*
$offer_product_id = function_exists('get_field') ? get_field('offer_product', get_the_ID()) : null;
$offer_checkout_url = '';

if ($offer_product_id && class_exists('WooCommerce')) {
    $offer_product = wc_get_product($offer_product_id);
    if ($offer_product) {
        // Use ?add-to-cart on the shop/home URL — WooCommerce handles the redirect to checkout.
        // wc_get_checkout_url() can return the homepage when Polylang hasn't mapped the checkout page,
        // so we always append add-to-cart params to home_url('/') and let WC redirect.
        if ($offer_product->is_type('variable')) {
            $data_store = WC_Data_Store::load('product');
            $variation_id = $data_store->find_matching_product_variation($offer_product, ['devices' => '1']);

            if (!$variation_id) {
                $default_attrs = $offer_product->get_default_attributes();
                if (!empty($default_attrs)) {
                    $variation_id = $data_store->find_matching_product_variation($offer_product, $default_attrs);
                }
            }
            if (!$variation_id) {
                $children = $offer_product->get_children();
                $variation_id = !empty($children) ? $children[0] : null;
            }

            if ($variation_id) {
                $offer_checkout_url = add_query_arg([
                    'add-to-cart'       => $variation_id,
                    'variation_id'      => $variation_id,
                    'attribute_devices' => '1',
                ], home_url('/'));
            } else {
                $offer_checkout_url = get_permalink($offer_product_id);
            }
        } else {
            $offer_checkout_url = add_query_arg('add-to-cart', $offer_product_id, home_url('/'));
        }
    }
}

// Fallback to #pricing anchor if no product is configured yet
if (empty($offer_checkout_url)) {
    $offer_checkout_url = '#pricing';
}
*/

// ── Multi-currency pricing data ───────────────────────────────────────────────
$offer_all_prices = [];
$offer_currency = 'usd';
$offer_currency_data = [];

if (class_exists('IPTV_Currency_Settings')) {
    // Stored table, not a live recalculation — see get_price_table().
    $offer_all_prices = IPTV_Currency_Settings::get_price_table();
    $offer_currency = IPTV_Currency_Settings::instance()->get_current_currency();

    $all_currencies = IPTV_Currency_Settings::get_currencies();
    foreach ($all_currencies as $code => $data) {
        $offer_currency_data[$code] = [
            'symbol' => $data['symbol'],
            'position' => in_array($code, ['usd', 'eur']) ? 'before' : 'after',
            'decimals' => $data['decimals'] ?? true,
        ];
    }
}

// ── ACF field values (passed as $offer_* to sections via included scope) ─────
$offer_headline = function_exists('get_field') ? get_field('offer_headline', get_the_ID()) : '';
$offer_subline = function_exists('get_field') ? get_field('offer_subline', get_the_ID()) : '';
$offer_cta_text = function_exists('get_field') ? get_field('offer_cta_text', get_the_ID()) : '';
$offer_paid_months = function_exists('get_field') ? (int) get_field('offer_paid_months', get_the_ID()) : 12;
$offer_free_months = function_exists('get_field') ? (int) get_field('offer_free_months', get_the_ID()) : 3;
$offer_highlight_text = function_exists('get_field') ? get_field('offer_highlight_text', get_the_ID()) : '';
$offer_features_rows = function_exists('get_field') ? (get_field('offer_features', get_the_ID()) ?: []) : [];
$offer_steps_rows = function_exists('get_field') ? (get_field('offer_steps', get_the_ID()) ?: []) : [];
$offer_faq_rows = function_exists('get_field') ? (get_field('offer_faq', get_the_ID()) ?: []) : [];
$offer_final_cta_text = function_exists('get_field') ? get_field('offer_final_cta_text', get_the_ID()) : '';
$offer_urgency_line = function_exists('get_field') ? get_field('offer_urgency_line', get_the_ID()) : '';

// Resolve defaults with iptv_text() fallback
if (empty($offer_headline))
    $offer_headline = iptv_text('offer_headline', '12 Months, Get 3 Free');
if (empty($offer_subline))
    $offer_subline = iptv_text('offer_subline', 'Total = 15 months. Pay for 12.');
if (empty($offer_cta_text))
    $offer_cta_text = iptv_text('offer_cta_text', 'Get This Deal');
if (empty($offer_highlight_text))
    $offer_highlight_text = iptv_text('offer_highlight_text', 'You get 3 months FREE!');
if (empty($offer_final_cta_text))
    $offer_final_cta_text = $offer_cta_text;
if (empty($offer_urgency_line))
    $offer_urgency_line = iptv_text('offer_urgency_line', 'Offer ends soon — don\'t miss out');
?>

<style>
    <?php
    $css_files = [
        'variables',
        'base',
        'header',
        'footer',
        'responsive',
        'redesign-theme',
        'offer-landing',
        'design-v2',
        'design-v2-sections',
    ];
    foreach ($css_files as $file) {
        $path = $css_dir . '/' . $file . '.css';
        if (file_exists($path))
            include $path;
    }
    ?>
</style>

<?php
// Inject pricing data and product info for JS
?>
<script>
    window.offerCheckoutUrl = '<?php echo esc_js($offer_checkout_url); ?>';
    // window.offerProductId used to be printed here. It read $offer_product_id,
    // which has been inside the commented-out WooCommerce block above since
    // checkout moved to app.quebeciptv.co — so on PHP 8 this was an "undefined
    // variable" warning on every render, and it always printed 0. No JS in the
    // theme ever read it, so it is removed rather than given a value.
    window.iptvPrices = <?php echo wp_json_encode($offer_all_prices); ?>;
    window.currentCurrency = '<?php echo esc_js($offer_currency); ?>';
    window.iptvCurrencyData = <?php echo wp_json_encode($offer_currency_data); ?>;
    window.offerPaidMonths = <?php echo (int) $offer_paid_months; ?>;
    window.offerFreeMonths = <?php echo (int) $offer_free_months; ?>;
</script>

<?php
// ── Load sections ─────────────────────────────────────────────────────────────
$sections = [
    'offer-header',  // Offer-specific header (logo + currency + CTA only)
    'offer-hero',
    'offer-features',
    'offer-steps',
    'offer-pricing',
    'offer-faq',
    'offer-cta',
    'footer',        // Reuse the universal footer
];

foreach ($sections as $section) {
    $path = $sections_dir . '/' . $section . '.php';
    if (file_exists($path)) {
        include $path;
    }
}
?>

<script>
    <?php
    $js_files = [
        'header',
        'currency',
        'offer-landing',
    ];
    foreach ($js_files as $file) {
        $path = $js_dir . '/' . $file . '.js';
        if (file_exists($path))
            include $path;
    }
    ?>
</script>

<?php get_footer(); ?>