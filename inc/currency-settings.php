<?php
/**
 * Multi-Currency Pricing Settings (USD as Single Source of Truth)
 * 
 * USD prices are manually set, all other currencies are auto-calculated
 * using conversion rates with psychological pricing rules.
 */

if (!defined('ABSPATH')) {
    exit;
}

class IPTV_Currency_Settings
{
    /** Where the computed price matrix is stored. See get_price_table(). */
    const PRICE_TABLE_OPTION = 'iptv_price_table';

    // Currencies (USD is base, others are calculated). CAD is the local Quebec
    // currency and pairs with French; USD stays the base and pairs with English.
    // See nordictv_lang_by_currency() in inc/language-preference.php.
    private $currencies = array(
        'usd' => array('name' => 'US Dollar', 'symbol' => '$', 'flag' => '🇺🇸', 'code' => 'USD', 'decimals' => true),
        'cad' => array('name' => 'Canadian Dollar', 'symbol' => '$', 'flag' => '🇨🇦', 'code' => 'CAD', 'decimals' => true),
    );

    private $durations = array(
        '1_month' => '1 Month',
        '3_months' => '3 Months',
        '6_months' => '6 Months',
        '12_months' => '12 Months',
    );

    private $devices = array(
        '1_device' => '1 Device',
        '2_devices' => '2 Devices',
        '3_devices' => '3 Devices',
        '4_devices' => '4 Devices',
    );

    // Default conversion rates (USD to X). Only a starting point: the live rate
    // is fetched by inc/currency-rates-api.php and stored in iptv_conversion_rates.
    private $default_rates = array(
        'cad' => 1.37,
    );

    // Default USD prices (prefilled)
    private $default_usd = array(
        '1_month' => array('1_device' => 16.99, '2_devices' => 23.99, '3_devices' => 31.99, '4_devices' => 38.99),
        '3_months' => array('1_device' => 29.99, '2_devices' => 46.99, '3_devices' => 53.99, '4_devices' => 88.99),
        '6_months' => array('1_device' => 40.99, '2_devices' => 75.99, '3_devices' => 116.99, '4_devices' => 151.99),
        '12_months' => array('1_device' => 69.99, '2_devices' => 128.99, '3_devices' => 174.99, '4_devices' => 221.99),
    );

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));

        // Anything that can change a price invalidates the stored table. These
        // all fire in wp-admin or on the REST/CLI side, never on a visitor's
        // request, which is the point of the table.
        add_action('update_option_iptv_conversion_rates', array(__CLASS__, 'rebuild_price_table_on_hook'));
        add_action('update_option_iptv_usd_prices', array(__CLASS__, 'rebuild_price_table_on_hook'));
        add_action('woocommerce_update_product', array(__CLASS__, 'rebuild_price_table_on_hook'));
        add_action('woocommerce_save_product_variation', array(__CLASS__, 'rebuild_price_table_on_hook'));

        // Price conversion for subsites is handled differently - not via filters
        // to avoid recursion issues with switch_to_blog
    }

    /**
     * Check if current site is a subsite
     */
    public function is_subsite()
    {
        if (!function_exists('is_multisite') || !is_multisite()) {
            return false;
        }
        $blog_id = function_exists('get_current_blog_id') ? get_current_blog_id() : 1;
        return $blog_id > 1;
    }

    /**
     * Get the currency for the current subsite based on URL slug
     */
    public function get_subsite_currency()
    {
        if (!$this->is_subsite()) {
            return 'usd';
        }

        $blog_id = get_current_blog_id();
        $details = get_blog_details($blog_id);
        if (!$details) {
            return 'usd';
        }

        $path = trim($details->path, '/');
        $parts = explode('/', $path);
        $site_slug = end($parts);

        // Multisite subsite slug => its currency. The Nordic subsites are gone;
        // 'ca' is here so a Quebec subsite, if one is ever spun up, bills in CAD.
        $currency_map = array(
            'ca' => 'cad',
        );

        return isset($currency_map[$site_slug]) ? $currency_map[$site_slug] : 'usd';
    }

    /**
     * Get conversion rate for a currency (from main site)
     */
    public function get_conversion_rate($currency)
    {
        // Get rates from main site
        if ($this->is_subsite()) {
            switch_to_blog(1);
        }

        $rates = get_option('iptv_conversion_rates', array());

        if ($this->is_subsite()) {
            restore_current_blog();
        }

        // Use stored rate or default
        if (!empty($rates[$currency])) {
            return floatval($rates[$currency]);
        }

        return isset($this->default_rates[$currency]) ? $this->default_rates[$currency] : 1;
    }

    /**
     * Convert USD price to local currency
     */
    public function convert_price_to_local($price, $product)
    {
        // Prevent infinite recursion
        static $converting = false;
        if ($converting) {
            return $price;
        }
        
        if (empty($price) || $price <= 0) {
            return $price;
        }

        $converting = true;
        
        $currency = $this->get_subsite_currency();
        if ($currency === 'usd') {
            $converting = false;
            return $price;
        }

        $rate = $this->get_conversion_rate($currency);
        $converted = floatval($price) * $rate;

        // Apply rounding
        $rounded = self::apply_rounding($converted, $currency);

        $converting = false;
        return $rounded;
    }

    /**
     * Convert variation prices array
     */
    public function convert_variation_price($price, $variation, $product)
    {
        return $this->convert_price_to_local($price, $product);
    }

    /**
     * Singleton Instance
     */
    public static function instance()
    {
        static $instance = null;
        if (is_null($instance)) {
            $instance = new self();
        }
        return $instance;
    }

    public function add_admin_menu()
    {
        add_options_page(
            'Pricing & Currencies',
            'Pricing & Currencies',
            'manage_options',
            'iptv-currency-settings',
            array($this, 'render_settings_page')
        );
    }

    public function register_settings()
    {
        register_setting('iptv_currency_settings', 'iptv_usd_prices');
        register_setting('iptv_currency_settings', 'iptv_conversion_rates');
    }

    public static function apply_rounding($price, $currency)
    {
        if ($currency === 'usd' || $currency === 'eur') {
            return ceil($price) - 0.01;
        } else {
            return ceil($price / 10) * 10 - 1;
        }
    }

    public static function calculate_all_prices()
    {
        $instance = self::instance();

        // ALWAYS fetch prices AND rates from main site (blog_id = 1) for consistency
        $current_blog_id = function_exists('get_current_blog_id') ? get_current_blog_id() : 1;
        $should_switch = function_exists('is_multisite') && is_multisite() && $current_blog_id > 1;

        if ($should_switch) {
            switch_to_blog(1); // Switch to main site to get USD prices AND conversion rates
        }

        // Get conversion rates FROM MAIN SITE's database
        $rates = get_option('iptv_conversion_rates', array());

        foreach ($instance->default_rates as $cur => $rate) {
            if (empty($rates[$cur]))
                $rates[$cur] = $rate;
        }

        $all_prices = array();

        // 1. Core Durations (Variable Products)
        foreach ($instance->durations as $dur_key => $dur_label) {
            $all_prices[$dur_key] = array();

            // Try to fetch product by SKU from MAIN SITE
            $product_id = function_exists('wc_get_product_id_by_sku') ? wc_get_product_id_by_sku($dur_key) : 0;
            $product = $product_id ? wc_get_product($product_id) : null;

            foreach ($instance->devices as $dev_key => $dev_label) {
                $all_prices[$dur_key][$dev_key] = array();

                $usd_price = 0;

                // Attempt to retrieve price from actual product variation
                if ($product && $product->is_type('variable')) {
                    // Extract device count from key (1_device -> 1)
                    $dev_count = intval(explode('_', $dev_key)[0]);

                    // Allow string matching for attribute
                    $children = $product->get_children();
                    if ($children) {
                        foreach ($children as $child_id) {
                            $variation = wc_get_product($child_id);
                            $attributes = $variation->get_attributes();
                            $attr_val = isset($attributes['pa_devices']) ? $attributes['pa_devices'] : '';

                            // Check if variation matches device count
                            if ($attr_val == $dev_count) {
                                $usd_price = floatval($variation->get_regular_price());
                                break;
                            }
                        }
                    }
                }

                // Fallback to default if product not found/setup
                if ($usd_price <= 0) {
                    $usd_price = isset($instance->default_usd[$dur_key][$dev_key]) ? $instance->default_usd[$dur_key][$dev_key] : 0;
                }

                // Always store USD as base
                $all_prices[$dur_key][$dev_key]['usd'] = number_format($usd_price, 2, '.', '');

                // Calculate ALL other currencies from USD
                foreach ($instance->currencies as $cur_key => $currency) {
                    if ($cur_key === 'usd')
                        continue;
                    $rate = floatval($rates[$cur_key]);
                    $converted = $usd_price * $rate;
                    $rounded = self::apply_rounding($converted, $cur_key);

                    if ($cur_key === 'eur') {
                        $all_prices[$dur_key][$dev_key][$cur_key] = number_format($rounded, 2, '.', '');
                    } else {
                        $all_prices[$dur_key][$dev_key][$cur_key] = strval(intval($rounded));
                    }
                }
            }
        }

        // Restore original blog if we switched
        if ($should_switch) {
            restore_current_blog();
        }

        // 2. Trial Product (Simple)
        $trial_sku = 'trial_24h';
        $trial_id = function_exists('wc_get_product_id_by_sku') ? wc_get_product_id_by_sku($trial_sku) : 0;
        $trial_prod = $trial_id ? wc_get_product($trial_id) : null;

        // Use regular price if set, or sale price if active? 
        // User said: "edit the price from the bulk edit". Bulk edit updates regular and sale.
        // Usually Trial is "Free" or "Cheap". We usually display the "Sale Price" if exists, or just Price.
        // Let's grab the active price.
        $trial_price_usd = $trial_prod ? floatval($trial_prod->get_price()) : 0;

        $all_prices['trial_24h'] = array('usd' => number_format($trial_price_usd, 2, '.', ''));

        foreach ($instance->currencies as $cur_key => $currency) {
            if ($cur_key === 'usd')
                continue;
            $rate = floatval($rates[$cur_key]);
            // Don't apply rounding to 0 or very small trial prices generally, but for consistency we apply it if > 0
            if ($trial_price_usd > 0) {
                $converted = $trial_price_usd * $rate;
                $rounded = self::apply_rounding($converted, $cur_key);
                if ($cur_key === 'eur') {
                    $all_prices['trial_24h'][$cur_key] = number_format($rounded, 2, '.', '');
                } else {
                    $all_prices['trial_24h'][$cur_key] = strval(intval($rounded));
                }
            } else {
                $all_prices['trial_24h'][$cur_key] = '0';
            }
        }

        return $all_prices;
    }

    public function render_settings_page()
    {
        $usd_prices = get_option('iptv_usd_prices', array());
        $rates = get_option('iptv_conversion_rates', array());

        foreach ($this->default_rates as $cur => $rate) {
            if (empty($rates[$cur]))
                $rates[$cur] = $rate;
        }

        // Calculate all prices for display
        $all_prices = self::calculate_all_prices();
        ?>
        <div class="wrap">
            <h1>💰 Pricing & Currencies</h1>
            <p><strong>USD Prices are now managed in the Bulk Editor or Products menu.</strong></p>
            <p>Here you can set conversion rates. All other currencies auto-calculate from the USD base price set on the
                products.</p>
            <a href="<?php echo admin_url('admin.php?page=iptv-bulk-editor'); ?>" class="button button-primary"
                style="margin-bottom:20px;">Go to Bulk Editor</a>

            <form method="post" action="options.php">
                <?php settings_fields('iptv_currency_settings'); ?>

                <style>
                    .settings-section {
                        background: #fff;
                        padding: 20px;
                        border: 1px solid #ddd;
                        border-radius: 8px;
                        margin-bottom: 20px;
                    }

                    .settings-section h2 {
                        margin-top: 0;
                        border-bottom: 2px solid #2271b1;
                        padding-bottom: 10px;
                    }

                    .rate-grid {
                        display: grid;
                        grid-template-columns: repeat(5, 1fr);
                        gap: 15px;
                        margin-bottom: 20px;
                    }

                    .rate-item {
                        background: #f8f9fa;
                        padding: 15px;
                        border-radius: 8px;
                        text-align: center;
                    }

                    .rate-item label {
                        display: block;
                        font-weight: 600;
                        margin-bottom: 8px;
                    }

                    .rate-item input {
                        width: 80px;
                        text-align: center;
                        padding: 8px;
                        font-size: 14px;
                    }

                    .price-table {
                        border-collapse: collapse;
                        width: 100%;
                        margin-bottom: 20px;
                    }

                    .price-table th,
                    .price-table td {
                        border: 1px solid #ddd;
                        padding: 10px;
                        text-align: center;
                    }

                    .price-table th {
                        background: #2271b1;
                        color: #fff;
                    }

                    .price-table .duration {
                        background: #f0f0f1;
                        font-weight: 600;
                        text-align: left;
                        padding-left: 15px;
                    }

                    .price-table input {
                        width: 70px;
                        text-align: center;
                        padding: 6px;
                        font-weight: 600;
                    }

                    .currency-tabs {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 5px;
                        margin-bottom: 0;
                    }

                    .currency-tab {
                        padding: 10px 15px;
                        background: #f0f0f1;
                        border: 1px solid #ddd;
                        cursor: pointer;
                        border-radius: 5px 5px 0 0;
                        border-bottom: none;
                    }

                    .currency-tab.active {
                        background: #2271b1;
                        color: #fff;
                    }

                    .currency-panel {
                        display: none;
                        background: #fff;
                        border: 1px solid #ddd;
                        padding: 15px;
                    }

                    .currency-panel.active {
                        display: block;
                    }

                    .calculated-table td {
                        background: #e8f5e9 !important;
                    }

                    .calculated-table .price-cell {
                        font-weight: 700;
                        color: #2e7d32;
                    }
                </style>

                <!-- Conversion Rates -->
                <div class="settings-section">
                    <h2>📊 Conversion Rates (1 USD = X)</h2>
                    <div class="rate-grid">
                        <?php foreach ($this->currencies as $cur_key => $currency):
                            if ($cur_key === 'usd')
                                continue;
                            ?>
                            <div class="rate-item">
                                <label><?php echo $currency['flag'] . ' ' . $currency['code']; ?></label>
                                <input type="number" step="0.01" name="iptv_conversion_rates[<?php echo $cur_key; ?>]"
                                    value="<?php echo esc_attr($rates[$cur_key]); ?>" />
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php
                // Auto-update panel (inc/currency-rates-api.php). Rendered inside
                // this form so its settings save with the rest of the page.
                do_action('iptv_after_conversion_rates', $rates);
                ?>

                <!-- USD Prices (Managed via Bulk Editor) -->
                <div class="settings-section">
                    <h2>🇺🇸 USD Prices</h2>
                    <p>Price management has been moved to the <a
                            href="<?php echo admin_url('admin.php?page=iptv-bulk-editor'); ?>">Bulk Editor</a>. Prices set there
                        are automatically used as the base for conversions below.</p>
                </div>

                <!-- All Calculated Prices (Read-Only View) -->
                <div class="settings-section">
                    <h2>💱 Calculated Prices (All Currencies)</h2>
                    <p>These are auto-calculated from USD using your conversion rates. <em>Save to update.</em></p>

                    <div class="currency-tabs">
                        <?php $first = true;
                        foreach ($this->currencies as $cur_key => $currency): ?>
                            <div class="currency-tab <?php echo $first ? 'active' : ''; ?>"
                                onclick="showCurrencyTab('<?php echo $cur_key; ?>')">
                                <?php echo $currency['flag'] . ' ' . $currency['code']; ?>
                            </div>
                            <?php $first = false; endforeach; ?>
                    </div>

                    <?php $first = true;
                    foreach ($this->currencies as $cur_key => $currency): ?>
                        <div id="panel-<?php echo $cur_key; ?>"
                            class="currency-panel calculated-table <?php echo $first ? 'active' : ''; ?>">
                            <table class="price-table">
                                <thead>
                                    <tr>
                                        <th>Duration</th>
                                        <?php foreach ($this->devices as $dev_label): ?>
                                            <th><?php echo $dev_label; ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Trial Row -->
                                    <tr>
                                        <td class="duration">24h Trial (Risk Free)</td>
                                        <td class="price-cell" onclick="alert('Trial is single device only')">
                                            <?php
                                            $price = $all_prices['trial_24h'][$cur_key];
                                            $symbol = $currency['symbol'];
                                            echo $currency['decimals'] ? $symbol . $price : $price . ' ' . $symbol;

                                            // Debug/Info: Show if purely based on Sale Price
                                            if ($cur_key === 'usd' && $trial_prod) {
                                                $reg = $trial_prod->get_regular_price();
                                                $active = $trial_prod->get_price();
                                                if (floatval($active) == 0 && floatval($reg) > 0) {
                                                    echo '<br><span style="font-size:10px; color:red; font-weight:normal;">(Sale Price: $0.00 active)</span>';
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td colspan="3" style="color:#999;font-size:12px;">(Single Device)</td>
                                    </tr>

                                    <!-- Regular Rows -->
                                    <?php foreach ($this->durations as $dur_key => $dur_label): ?>
                                        <tr>
                                            <td class="duration"><?php echo $dur_label; ?></td>
                                            <?php foreach ($this->devices as $dev_key => $dev_label):
                                                $price = $all_prices[$dur_key][$dev_key][$cur_key];
                                                $symbol = $currency['symbol'];
                                                $display = $currency['decimals'] ? $symbol . $price : $price . ' ' . $symbol;
                                                ?>
                                                <td class="price-cell"><?php echo $display; ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php $first = false; endforeach; ?>

                    <script>                 function showCurrencyTab(cur) { document.querySelectorAll('.currency-tab').forEach(t => t.classList.remove('active')); document.querySelectorAll('.currency-panel').forEach(p => p.classList.remove('active')); document.getElementById('panel-' + cur).classList.add('active'); event.target.classList.add('active'); }
                    </script>
                </div>

                <p>
                    <?php submit_button('Save All Prices', 'primary', 'submit', false); ?>
                </p>
            </form>
        </div>
        <?php
    }

    public static function get_prices()
    {
        return self::calculate_all_prices();
    }

    /**
     * The stored price table — what the front end reads.
     *
     * calculate_all_prices() is not cheap: it loads four variable products and
     * every one of their variations, then converts each cell into six
     * currencies. Doing that on every visitor's page load is wasted work, since
     * the answer only changes when a rate or a product price changes.
     *
     * So the matrix is computed once, written to an option, and read from there.
     * The rebuild hooks in __construct() keep it honest; the fallback below
     * covers the very first request after deploy, when nothing has saved yet.
     *
     * @return array Same shape as calculate_all_prices().
     */
    public static function get_price_table()
    {
        $stored = get_option(self::PRICE_TABLE_OPTION);

        if (is_array($stored) && !empty($stored['prices'])) {
            return $stored['prices'];
        }

        // First render after deploy: build it, but do not purge from a visitor's
        // request — there is no stale copy of a table that has never existed.
        return self::rebuild_price_table(false);
    }

    /**
     * Recompute the table and store it. Returns the fresh prices.
     *
     * @param bool $purge Drop the page cache afterwards. False only for the
     *                    lazy first build during a front-end render.
     */
    public static function rebuild_price_table($purge = true)
    {
        $prices = self::calculate_all_prices();

        update_option(
            self::PRICE_TABLE_OPTION,
            array(
                'prices'       => $prices,
                'rates'        => get_option('iptv_conversion_rates', array()),
                'generated_at' => time(),
            ),
            false // never autoloaded; only the pricing section asks for it
        );

        // The table is printed into the page HTML and into window.iptvPrices, so
        // a stale page cache would keep serving the old numbers.
        if ($purge) {
            do_action('litespeed_purge_all');
        }

        return $prices;
    }

    /**
     * Hook entry point. Exists so the callbacks' first argument (an old option
     * value, or a product ID) is never mistaken for the $purge flag.
     */
    public static function rebuild_price_table_on_hook()
    {
        self::rebuild_price_table(true);
    }

    /**
     * When the stored table was last built, or 0 if it never has been.
     */
    public static function price_table_generated_at()
    {
        $stored = get_option(self::PRICE_TABLE_OPTION);

        return (is_array($stored) && !empty($stored['generated_at'])) ? (int) $stored['generated_at'] : 0;
    }
    public static function get_currencies()
    {
        return self::instance()->currencies;
    }
    public static function get_durations()
    {
        return self::instance()->durations;
    }
    public static function get_devices()
    {
        return self::instance()->devices;
    }
}

// Initialize
IPTV_Currency_Settings::instance();
