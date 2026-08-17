<?php
/**
 * Exchange rates from CurrencyFreaks
 *
 * The account is on the free tier: 1,000 requests per month. The only way to
 * blow that is to call the API from a front-end request, so nothing here ever
 * does. The flow is strictly one-directional:
 *
 *   weekly cron → CurrencyFreaks → parse → iptv_conversion_rates
 *               → rebuild iptv_price_table → purge page cache
 *
 * A visitor never reaches past the last step: the pricing section reads the
 * stored table (IPTV_Currency_Settings::get_price_table) and nothing else.
 *
 * Budget:
 *   1 scheduled fetch/week       ~4-5 requests/month   (0.5% of the quota)
 *   manual "Fetch now" clicks    throttled to 1/10min
 *   hard stop                    MONTHLY_CAP requests, then it refuses to call
 *
 * One request covers every currency — `symbols` returns them all in a single
 * response — so the cost does not grow if the disabled languages come back.
 *
 * @package Quebec_IPTV
 */

if (!defined('ABSPATH')) {
    exit;
}

class IPTV_Rates_API
{
    /** Free tier gives 1,000/month. A weekly schedule needs five, so a cap this
     *  low still leaves room for manual refreshes while making it impossible for
     *  a bug to drain the account. */
    const MONTHLY_CAP = 50;

    /** Reject a fetched rate that moves more than this from the current one.
     *  Real FX does not jump 10% overnight, so a move that large means a bad
     *  response or a rate entered in the wrong direction — either way it would
     *  reprice the whole store. */
    const MAX_DRIFT = 0.10;

    const ENDPOINT   = 'https://api.currencyfreaks.com/v2.0/rates/latest';
    const CRON_HOOK  = 'iptv_fetch_exchange_rates';
    const LOCK_KEY   = 'iptv_rates_fetch_lock';

    const OPT_KEY     = 'iptv_currencyfreaks_key';
    const OPT_ENABLED = 'iptv_rates_auto_enabled';
    const OPT_BUFFER  = 'iptv_rates_buffer_pct';
    const OPT_STATE   = 'iptv_rates_state';

    /**
     * Currencies to request, as option key => ISO code. The option keys are the
     * ones IPTV_Currency_Settings stores rates under.
     */
    private static $symbols = array(
        'eur' => 'EUR',
        'sek' => 'SEK',
        'nok' => 'NOK',
        'dkk' => 'DKK',
        'isk' => 'ISK',
    );

    public static function boot()
    {
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_action('admin_init', array(__CLASS__, 'sync_schedule'));
        add_action(self::CRON_HOOK, array(__CLASS__, 'run_scheduled'));
        add_action('admin_post_iptv_fetch_rates_now', array(__CLASS__, 'handle_manual_fetch'));
        add_action('iptv_after_conversion_rates', array(__CLASS__, 'render_panel'));
    }

    /**
     * The three settings live in the existing Pricing & Currencies form, so they
     * save with everything else on that page.
     */
    public static function register_settings()
    {
        register_setting('iptv_currency_settings', self::OPT_KEY, array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('iptv_currency_settings', self::OPT_ENABLED, array('sanitize_callback' => 'absint'));
        register_setting('iptv_currency_settings', self::OPT_BUFFER, array('sanitize_callback' => array(__CLASS__, 'sanitize_buffer')));
    }

    public static function sanitize_buffer($value)
    {
        $value = (float) $value;

        // A buffer beyond 15% stops being a hedge and starts being a price rise.
        return max(0, min(15, $value));
    }

    // ── Scheduling ───────────────────────────────────────────────────────────

    /**
     * Keep the cron event in step with the settings: scheduled while auto-update
     * is on and a key is present, cleared otherwise.
     */
    public static function sync_schedule()
    {
        $should_run = self::is_enabled();
        $scheduled  = wp_next_scheduled(self::CRON_HOOK);

        if ($should_run && !$scheduled) {
            // Monday ~03:15 site time: quiet hours, and a full week of trading
            // has settled since the last one.
            $first = strtotime('next monday 03:15', current_time('timestamp')) - (int) (get_option('gmt_offset') * HOUR_IN_SECONDS);
            wp_schedule_event($first, 'weekly', self::CRON_HOOK);
        } elseif (!$should_run && $scheduled) {
            wp_unschedule_event($scheduled, self::CRON_HOOK);
        }
    }

    public static function is_enabled()
    {
        return (bool) get_option(self::OPT_ENABLED) && '' !== trim((string) get_option(self::OPT_KEY, ''));
    }

    public static function run_scheduled()
    {
        self::fetch('cron');
    }

    // ── Fetching ─────────────────────────────────────────────────────────────

    /**
     * Fetch rates and write them to iptv_conversion_rates.
     *
     * @param string $trigger 'cron' or 'manual', recorded in the state for the UI.
     * @return array{ok:bool,message:string,changed:array}
     */
    public static function fetch($trigger = 'cron')
    {
        $state = self::get_state();

        $key = trim((string) get_option(self::OPT_KEY, ''));
        if ($key === '') {
            return self::fail($state, 'No API key saved.');
        }

        // Two overlapping cron runs would spend two requests for one result.
        if (get_transient(self::LOCK_KEY)) {
            return self::fail($state, 'A fetch is already running — skipped.', false);
        }
        set_transient(self::LOCK_KEY, 1, 2 * MINUTE_IN_SECONDS);

        // A 429 means the quota is gone; back off for a day rather than
        // spending the remaining budget discovering that again.
        if (!empty($state['cooldown_until']) && $state['cooldown_until'] > time()) {
            delete_transient(self::LOCK_KEY);
            return self::fail($state, 'Cooling down after a rate-limit response until ' . self::local_time($state['cooldown_until']) . '.', false);
        }

        if (self::calls_this_month($state) >= self::MONTHLY_CAP) {
            delete_transient(self::LOCK_KEY);
            return self::fail($state, sprintf('Monthly cap of %d requests reached — no call made.', self::MONTHLY_CAP), false);
        }

        $url = add_query_arg(
            array(
                'apikey'  => $key,
                // Base is USD by default, which is what the pricing model wants;
                // changing it is a paid feature anyway.
                'symbols' => implode(',', self::$symbols),
            ),
            self::ENDPOINT
        );

        // Counted before the response comes back: the request is spent either way.
        $state = self::count_call($state, $trigger);

        $response = wp_remote_get($url, array('timeout' => 15));
        delete_transient(self::LOCK_KEY);

        if (is_wp_error($response)) {
            return self::fail($state, 'Request failed: ' . $response->get_error_message());
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code === 429) {
            $state['cooldown_until'] = time() + DAY_IN_SECONDS;
            return self::fail($state, 'CurrencyFreaks returned 429 (quota exceeded). Paused for 24h.');
        }

        if ($code === 401 || $code === 400) {
            // A bad key will never fix itself, and every retry costs a request.
            update_option(self::OPT_ENABLED, 0);
            return self::fail($state, sprintf('CurrencyFreaks returned %d (bad or inactive API key). Auto-update switched off.', $code));
        }

        if ($code !== 200 || !is_array($body) || empty($body['rates'])) {
            return self::fail($state, sprintf('Unexpected response (HTTP %d).', $code));
        }

        return self::apply_rates($body, $state);
    }

    /**
     * Validate and store the rates from a successful response.
     */
    private static function apply_rates(array $body, array $state)
    {
        $current = get_option('iptv_conversion_rates', array());
        $buffer  = 1 + (self::sanitize_buffer(get_option(self::OPT_BUFFER, 0)) / 100);

        $new      = $current;
        $changed  = array();
        $rejected = array();

        foreach (self::$symbols as $opt_key => $iso) {
            if (!isset($body['rates'][$iso])) {
                continue;
            }

            $rate = (float) $body['rates'][$iso];
            if ($rate <= 0) {
                continue;
            }

            $rate = round($rate * $buffer, 4);
            $old  = isset($current[$opt_key]) ? (float) $current[$opt_key] : 0;

            // Drift guard, skipped on the first run when there is nothing to
            // compare against.
            if ($old > 0 && abs($rate - $old) / $old > self::MAX_DRIFT) {
                $rejected[$opt_key] = sprintf('%s %.4f → %.4f', strtoupper($opt_key), $old, $rate);
                continue;
            }

            if ($old !== $rate) {
                $changed[$opt_key] = array('from' => $old, 'to' => $rate);
            }

            $new[$opt_key] = $rate;
        }

        if ($changed) {
            // Writing the option fires update_option_iptv_conversion_rates,
            // which IPTV_Currency_Settings uses to rebuild the price table and
            // purge the page cache. That rebuild is the whole point of the
            // fetch — the front end reads the table, not these rates.
            update_option('iptv_conversion_rates', $new);
        } elseif (!IPTV_Currency_Settings::price_table_generated_at()) {
            // Rates unchanged but the table has never been built (first run).
            IPTV_Currency_Settings::rebuild_price_table();
        }

        $state['last_success']  = time();
        $state['last_error']    = '';
        $state['rates_date']    = isset($body['date']) ? sanitize_text_field($body['date']) : '';
        $state['last_changed']  = $changed;
        $state['last_rejected'] = $rejected;
        unset($state['cooldown_until']);
        self::save_state($state);

        $message = $changed
            ? sprintf('Updated %d rate(s).', count($changed))
            : 'Rates fetched — no change.';

        if ($rejected) {
            $message .= ' Rejected as implausible: ' . implode(', ', $rejected) . '.';
        }

        return array('ok' => true, 'message' => $message, 'changed' => $changed);
    }

    // ── Manual trigger ───────────────────────────────────────────────────────

    public static function handle_manual_fetch()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Not allowed.');
        }

        check_admin_referer('iptv_fetch_rates_now');

        $state = self::get_state();
        $last  = isset($state['last_fetch']) ? (int) $state['last_fetch'] : 0;

        // Clicking the button repeatedly is the one manual way to burn quota.
        if ($last && (time() - $last) < 10 * MINUTE_IN_SECONDS) {
            $result = array('ok' => false, 'message' => 'Last fetch was under 10 minutes ago — nothing to gain from another.');
        } else {
            $result = self::fetch('manual');
        }

        set_transient('iptv_rates_notice', $result, 60);

        wp_safe_redirect(admin_url('options-general.php?page=iptv-currency-settings'));
        exit;
    }

    // ── State ────────────────────────────────────────────────────────────────

    private static function get_state()
    {
        $state = get_option(self::OPT_STATE, array());

        return is_array($state) ? $state : array();
    }

    private static function save_state(array $state)
    {
        update_option(self::OPT_STATE, $state, false);
    }

    /**
     * Requests spent in the current calendar month. The counter resets by month
     * key rather than on a timer, which matches how the quota resets.
     */
    public static function calls_this_month(array $state = null)
    {
        $state = $state === null ? self::get_state() : $state;
        $month = gmdate('Y-m');

        return (isset($state['calls'][$month])) ? (int) $state['calls'][$month] : 0;
    }

    private static function count_call(array $state, $trigger)
    {
        $month = gmdate('Y-m');

        if (!isset($state['calls']) || !is_array($state['calls'])) {
            $state['calls'] = array();
        }

        $state['calls'][$month] = self::calls_this_month($state) + 1;

        // Keep the last three months for the UI, drop the rest.
        if (count($state['calls']) > 3) {
            krsort($state['calls']);
            $state['calls'] = array_slice($state['calls'], 0, 3, true);
        }

        $state['last_fetch']   = time();
        $state['last_trigger'] = $trigger;
        self::save_state($state);

        return $state;
    }

    private static function fail(array $state, $message, $persist = true)
    {
        if ($persist) {
            $state['last_error'] = $message;
            self::save_state($state);
        }

        return array('ok' => false, 'message' => $message, 'changed' => array());
    }

    private static function local_time($timestamp)
    {
        return wp_date(get_option('date_format') . ' H:i', $timestamp);
    }

    // ── Admin panel ──────────────────────────────────────────────────────────

    /**
     * Rendered inside the existing Pricing & Currencies form, right under the
     * manual rate inputs. Hooked to `iptv_after_conversion_rates`.
     */
    public static function render_panel()
    {
        $state   = self::get_state();
        $enabled = (bool) get_option(self::OPT_ENABLED);
        $key     = (string) get_option(self::OPT_KEY, '');
        $buffer  = (float) get_option(self::OPT_BUFFER, 0);
        $used    = self::calls_this_month($state);
        $next    = wp_next_scheduled(self::CRON_HOOK);

        $notice = get_transient('iptv_rates_notice');
        if ($notice) {
            delete_transient('iptv_rates_notice');
            printf(
                '<div class="notice notice-%s" style="margin:12px 0;"><p>%s</p></div>',
                empty($notice['ok']) ? 'error' : 'success',
                esc_html($notice['message'])
            );
        }
        ?>
        <div class="settings-section">
            <h2>🔄 Auto-update rates (CurrencyFreaks)</h2>

            <p style="max-width:70ch;">
                One request a week refreshes every rate at once, which is about
                <strong>5 of the free plan's 1,000 monthly requests</strong>. The API is only
                ever called from this screen or the scheduled job — never while a visitor
                loads a page. Each fetch rewrites the rates above, rebuilds the price table
                below, and clears the page cache; visitors are always served from that
                stored table.
            </p>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="iptv_rates_key">API key</label></th>
                    <td>
                        <input type="text" id="iptv_rates_key" class="regular-text"
                            name="<?php echo esc_attr(self::OPT_KEY); ?>"
                            value="<?php echo esc_attr($key); ?>" autocomplete="off" />
                        <p class="description">From your CurrencyFreaks dashboard.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Weekly update</th>
                    <td>
                        <label>
                            <input type="hidden" name="<?php echo esc_attr(self::OPT_ENABLED); ?>" value="0" />
                            <input type="checkbox" name="<?php echo esc_attr(self::OPT_ENABLED); ?>" value="1"
                                <?php checked($enabled); ?> />
                            Fetch rates once a week (Mondays, 03:15)
                        </label>
                        <p class="description">
                            While this is on, the rates above are overwritten by each fetch —
                            use the buffer below to adjust them rather than typing over them.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="iptv_rates_buffer">Buffer</label></th>
                    <td>
                        <input type="number" step="0.1" min="0" max="15" id="iptv_rates_buffer"
                            name="<?php echo esc_attr(self::OPT_BUFFER); ?>"
                            value="<?php echo esc_attr($buffer); ?>" style="width:80px;" /> %
                        <p class="description">
                            Added on top of the mid-market rate to absorb FX movement and card fees.
                            3% is a sensible starting point; 0 uses the raw rate.
                        </p>
                    </td>
                </tr>
            </table>

            <div style="background:#f6f7f7;border:1px solid #dcdcde;border-radius:4px;padding:12px 16px;margin-top:8px;">
                <p style="margin:0 0 6px;">
                    <strong>Requests used this month:</strong>
                    <?php echo (int) $used; ?> / <?php echo (int) self::MONTHLY_CAP; ?>
                    <span style="color:#646970;">(plan allows 1,000)</span>
                </p>
                <p style="margin:0 0 6px;">
                    <strong>Last fetch:</strong>
                    <?php
                    echo empty($state['last_fetch'])
                        ? 'never'
                        : esc_html(self::local_time($state['last_fetch']) . ' (' . (isset($state['last_trigger']) ? $state['last_trigger'] : '?') . ')');
                    ?>
                    &nbsp;·&nbsp;
                    <strong>Next scheduled:</strong>
                    <?php echo $next ? esc_html(self::local_time($next)) : 'not scheduled'; ?>
                </p>
                <?php if (!empty($state['rates_date'])) : ?>
                    <p style="margin:0 0 6px;"><strong>Rates dated:</strong> <?php echo esc_html($state['rates_date']); ?></p>
                <?php endif; ?>
                <?php $built = IPTV_Currency_Settings::price_table_generated_at(); ?>
                <p style="margin:0 0 6px;">
                    <strong>Price table (what visitors see) built:</strong>
                    <?php echo $built ? esc_html(self::local_time($built)) : 'never — will build on the next save or fetch'; ?>
                </p>
                <?php if (!empty($state['last_error'])) : ?>
                    <p style="margin:0 0 6px;color:#b32d2e;"><strong>Last error:</strong> <?php echo esc_html($state['last_error']); ?></p>
                <?php endif; ?>
                <?php if (!empty($state['last_rejected'])) : ?>
                    <p style="margin:0 0 6px;color:#996800;">
                        <strong>Rejected as implausible:</strong>
                        <?php echo esc_html(implode(', ', $state['last_rejected'])); ?>
                        — check them by hand.
                    </p>
                <?php endif; ?>
                <p style="margin:6px 0 0;">
                    <a class="button"
                        href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=iptv_fetch_rates_now'), 'iptv_fetch_rates_now')); ?>">
                        Fetch now
                    </a>
                    <span class="description" style="margin-left:8px;">Spends one request. Save the key first.</span>
                </p>
            </div>
        </div>
        <?php
    }
}

IPTV_Rates_API::boot();
