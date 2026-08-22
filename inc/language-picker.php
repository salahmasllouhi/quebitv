<?php
/**
 * First-visit language picker
 *
 * Asks a visitor once, on their first page view, whether they want French or
 * English — then never asks again. The answer is written to the same
 * `nordictv_lang` cookie the switcher and the front-page redirect already use,
 * so one choice drives all three.
 *
 * This deliberately adds no new preference storage. inc/language-preference.php
 * already owns the cookie, its lifetime, the currency/language pairs and the
 * per-page translated URLs, and prints all of it as window.nordictvLang. The
 * picker is a second consumer of that data, not a second source of truth.
 *
 * ── Why the markup is always printed and hidden ──────────────────────────────
 *
 * LiteSpeed serves a cached page before PHP runs, so anything decided in PHP
 * from the cookie gets baked into the cache and then handed to the wrong
 * people: the first visitor to warm the cache would decide whether every
 * subsequent visitor sees the dialog. inc/language-preference.php works around
 * that for the redirect with X-LiteSpeed-Vary, but only on the front page, and
 * this runs on every page.
 *
 * So the markup is identical for everyone — cacheable — and JavaScript decides
 * whether to reveal it by reading the cookie in the browser. Nothing about this
 * page varies by visitor, which is exactly what a page cache wants.
 *
 * ── Why it is a bottom sheet on mobile ───────────────────────────────────────
 *
 * Google treats a popup that covers the content on a mobile page arrived at
 * from search as an intrusive interstitial, and discounts the page for it. The
 * carve-out is for banners "that use a reasonable amount of screen space and are
 * easily dismissible". So on phones this is a compact sheet at the bottom of the
 * viewport that leaves the page visible and scrollable behind it, and only on
 * wider screens — where the policy does not apply — does it become a centred
 * dialog with a backdrop.
 *
 * It also carries data-nosnippet so the two words of chrome cannot end up in a
 * search result snippet.
 *
 * @package Quebec_IPTV
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('iptv_language_picker_options')) {
    /**
     * The languages to offer, in display order.
     *
     * Labels are each written in their own language, and the dialog's heading is
     * shown in both — a visitor who has not chosen yet cannot be assumed to read
     * either one, so nothing here may depend on the site's current language.
     *
     * Flags follow front-page/sections/header.php: the Canadian flag for French,
     * because Quebec's own flag has no Unicode RGI sequence, and the US flag for
     * English, which is the market that half of the site is written for.
     *
     * @return array<int,array{slug:string,label:string,flag:string}>
     */
    function iptv_language_picker_options()
    {
        // Order is this array's, not Polylang's. Polylang returns its own
        // ordering, which put English first — and French is the site's default
        // language and its primary market, so it leads.
        $known = array(
            'fr' => array('label' => 'Français', 'flag' => '🇨🇦'),
            'en' => array('label' => 'English',  'flag' => '🇺🇸'),
        );

        $active  = nordictv_lang_slugs();
        $options = array();

        foreach ($known as $slug => $meta) {
            if (!empty($active) && !in_array($slug, $active, true)) {
                continue;
            }

            $options[] = array(
                'slug'  => $slug,
                'label' => $meta['label'],
                'flag'  => $meta['flag'],
            );
        }

        return apply_filters('iptv_language_picker_options', $options);
    }
}

add_action('wp_footer', function () {
    if (is_admin() || is_feed() || is_embed() || wp_doing_ajax()) {
        return;
    }

    // Nothing to choose between.
    $options = iptv_language_picker_options();
    if (count($options) < 2) {
        return;
    }

    // Logged-in admins are editing, not browsing, and a dialog over every page
    // load while they work is just in the way. Their own preference cookie still
    // works through the switcher.
    if (is_user_logged_in() && current_user_can('manage_options')) {
        return;
    }
    ?>
<div class="iptv-langpick" data-lang-pick data-nosnippet hidden>
    <div class="iptv-langpick-backdrop" data-lang-dismiss></div>

    <?php
    // tabindex="-1" so the dialog itself can take focus when it opens. Focusing
    // the first option instead would render one language pre-selected and nudge
    // the answer, which is the one thing this dialog must not do.
    ?>
    <div class="iptv-langpick-card" role="dialog" aria-modal="true" tabindex="-1"
        aria-labelledby="iptv-langpick-title" aria-describedby="iptv-langpick-note">

        <?php
        // Both languages, because the visitor has not told us which one they
        // read yet. Marked up with lang attributes so a screen reader switches
        // voice rather than reading French with an English pronunciation.
        ?>
        <h2 class="iptv-langpick-title" id="iptv-langpick-title">
            <span lang="fr">Choisissez votre langue</span>
            <span class="iptv-langpick-sep" aria-hidden="true">·</span>
            <span lang="en">Choose your language</span>
        </h2>

        <div class="iptv-langpick-actions">
            <?php foreach ($options as $option) : ?>
                <?php
                // href is the real translated URL, so this works as an ordinary
                // link if the click handler has not attached yet, and shows a
                // sensible target on hover. ?set_lang= is the no-JavaScript path
                // that inc/language-preference.php already handles.
                $url = add_query_arg('set_lang', $option['slug'], home_url('/'));

                if (function_exists('pll_home_url')) {
                    $url = add_query_arg(
                        array('set_lang' => $option['slug'], 'nolangredirect' => 1),
                        pll_home_url($option['slug'])
                    );
                }
                ?>
                <a class="iptv-langpick-btn"
                    href="<?php echo esc_url($url); ?>"
                    hreflang="<?php echo esc_attr($option['slug']); ?>"
                    lang="<?php echo esc_attr($option['slug']); ?>"
                    data-lang-choose="<?php echo esc_attr($option['slug']); ?>">
                    <span class="iptv-langpick-flag" aria-hidden="true"><?php echo esc_html($option['flag']); ?></span>
                    <span><?php echo esc_html($option['label']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <p class="iptv-langpick-note" id="iptv-langpick-note">
            <span lang="fr">Vous pourrez changer à tout moment dans le menu.</span>
            <span class="iptv-langpick-sep" aria-hidden="true">·</span>
            <span lang="en">You can change this any time from the menu.</span>
        </p>

        <?php // Labelled in both languages for the same reason as the heading. ?>
        <button type="button" class="iptv-langpick-close" data-lang-dismiss
            aria-label="Fermer · Close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" aria-hidden="true">
                <path d="M18 6 6 18M6 6l12 12"></path>
            </svg>
        </button>

    </div>
</div>

<style>
    /* Tokens with literal fallbacks: woocommerce.php and the checkout template
       hand-roll their own <head> and do not load the design-v2 layer, so this
       cannot assume the custom properties exist. */
    .iptv-langpick[hidden] { display: none; }

    .iptv-langpick {
        position: fixed;
        inset: 0;
        z-index: 2147483000;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        /* No backdrop on phones: the sheet is a banner over a page that stays
           readable and scrollable, which is what keeps it out of Google's
           intrusive-interstitial rule. */
        pointer-events: none;
    }

    .iptv-langpick-backdrop {
        display: none;
    }

    .iptv-langpick-card {
        pointer-events: auto;
        position: relative;
        width: 100%;
        max-width: 560px;
        box-sizing: border-box;
        background: var(--dv2-surface, #ffffff);
        color: var(--dv2-ink, #07191d);
        border-top: 3px solid var(--dv2-blue, #fc6c34);
        border-radius: 18px 18px 0 0;
        padding: 20px 20px 22px;
        box-shadow: 0 -10px 40px rgba(22, 55, 63, 0.18);
        font-family: var(--dv2-font-body, 'Space Grotesk', system-ui, sans-serif);
        animation: iptvLangUp .28s ease;
    }

    @keyframes iptvLangUp {
        from { transform: translateY(100%); }
        to   { transform: translateY(0); }
    }

    .iptv-langpick-card:focus { outline: none; }

    .iptv-langpick-title {
        margin: 0 0 14px;
        padding-right: 28px;
        font-family: var(--dv2-font-display, 'Sora', system-ui, sans-serif);
        font-size: 1.02rem;
        font-weight: 700;
        line-height: 1.35;
        color: var(--dv2-ink, #07191d);
    }

    .iptv-langpick-sep {
        opacity: .45;
        margin: 0 .35em;
    }

    .iptv-langpick-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .iptv-langpick-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 13px 14px;
        border-radius: 999px;
        border: 2px solid var(--dv2-border-medium, rgba(22, 55, 63, .12));
        background: var(--dv2-surface-alt, #fff2ec);
        color: var(--dv2-ink, #07191d);
        font-size: .97rem;
        font-weight: 600;
        text-decoration: none;
        transition: background .18s ease, border-color .18s ease, transform .18s ease;
    }

    .iptv-langpick-btn:hover,
    .iptv-langpick-btn:focus-visible {
        background: var(--dv2-blue, #fc6c34);
        border-color: var(--dv2-blue, #fc6c34);
        color: #fff;
        transform: translateY(-1px);
    }

    .iptv-langpick-btn:focus-visible {
        outline: 3px solid var(--dv2-blue-soft, #fda37a);
        outline-offset: 2px;
    }

    .iptv-langpick-flag {
        font-size: 1.15rem;
        line-height: 1;
    }

    .iptv-langpick-note {
        margin: 13px 0 0;
        font-size: .8rem;
        line-height: 1.5;
        color: var(--dv2-muted, #6e9099);
        text-align: center;
    }

    .iptv-langpick-close {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 0;
        border-radius: 50%;
        background: transparent;
        color: var(--dv2-muted, #6e9099);
        cursor: pointer;
    }

    .iptv-langpick-close svg { width: 16px; height: 16px; }

    .iptv-langpick-close:hover,
    .iptv-langpick-close:focus-visible {
        background: var(--dv2-surface-alt, #fff2ec);
        color: var(--dv2-ink, #07191d);
    }

    /* Wider than a phone: a proper centred dialog. Google's interstitial rule is
       about mobile, so the backdrop is safe to introduce here. */
    @media (min-width: 720px) {
        .iptv-langpick {
            align-items: center;
        }

        .iptv-langpick-backdrop {
            display: block;
            pointer-events: auto;
            position: absolute;
            inset: 0;
            background: rgba(7, 25, 29, .55);
            animation: iptvLangFade .28s ease;
        }

        .iptv-langpick-card {
            width: calc(100% - 40px);
            border-radius: 18px;
            border-top-width: 4px;
            padding: 26px 28px 28px;
            box-shadow: 0 30px 70px rgba(7, 25, 29, .35);
            animation: iptvLangPop .28s ease;
        }

        .iptv-langpick-title { font-size: 1.16rem; }
        .iptv-langpick-btn { padding: 15px 16px; font-size: 1.02rem; }
    }

    @keyframes iptvLangFade { from { opacity: 0 } to { opacity: 1 } }
    @keyframes iptvLangPop {
        from { opacity: 0; transform: translateY(10px) scale(.98); }
        to   { opacity: 1; transform: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .iptv-langpick-card,
        .iptv-langpick-backdrop { animation: none; }
        .iptv-langpick-btn { transition: none; }
    }

    @media (max-width: 360px) {
        .iptv-langpick-actions { grid-template-columns: 1fr; }
    }
</style>

<script>
(function () {
    var root = document.querySelector('[data-lang-pick]');
    var cfg  = window.nordictvLang;

    if (!root || !cfg || !cfg.cookie) {
        return;
    }

    // "Already asked" is recorded in two places. The cookie is the real
    // preference and the only one the server reads; localStorage is a second
    // record of having asked at all, so a visitor whose cookies are blocked or
    // cleared between pages is not asked again on every single page view.
    var ASKED = 'iptvLangAsked';

    function cookieValue(name) {
        var m = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
        return m ? decodeURIComponent(m.pop()) : '';
    }

    function asked() {
        if (cookieValue(cfg.cookie)) {
            return true;
        }
        try {
            return !!window.localStorage.getItem(ASKED);
        } catch (e) {
            return false;
        }
    }

    function remember(slug) {
        // Same cookie, lifetime and attributes the switcher writes in
        // inc/universal-header.php, so the two cannot drift apart.
        document.cookie = cfg.cookie + '=' + encodeURIComponent(slug) +
            ';path=/;max-age=' + (cfg.days * 24 * 60 * 60) + ';samesite=lax' +
            (location.protocol === 'https:' ? ';secure' : '');

        try {
            window.localStorage.setItem(ASKED, slug);
        } catch (e) {}
    }

    if (asked()) {
        return;
    }

    var card = root.querySelector('.iptv-langpick-card');
    var opener = null;

    function focusable() {
        return Array.prototype.slice.call(
            card.querySelectorAll('a[href], button:not([disabled])')
        );
    }

    function onKeydown(e) {
        if (e.key === 'Escape') {
            e.preventDefault();
            dismiss();
            return;
        }

        if (e.key !== 'Tab') {
            return;
        }

        // Keep tabbing inside the dialog while it is open.
        var items = focusable();
        if (!items.length) {
            return;
        }

        var first = items[0];
        var last  = items[items.length - 1];

        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    }

    function open() {
        opener = document.activeElement;
        root.hidden = false;

        // Only lock scrolling where there is a backdrop to justify it. On a
        // phone the page behind stays scrollable, which is the difference
        // between a banner and an interstitial.
        if (window.matchMedia('(min-width: 720px)').matches) {
            document.documentElement.style.overflow = 'hidden';
        }

        document.addEventListener('keydown', onKeydown, true);

        // The dialog, not the first button: a focus ring on one language reads
        // as a recommendation. Tab moves into the options from here.
        card.focus();
    }

    function close() {
        root.hidden = true;
        document.documentElement.style.overflow = '';
        document.removeEventListener('keydown', onKeydown, true);

        if (opener && typeof opener.focus === 'function') {
            opener.focus();
        }
    }

    function dismiss() {
        // Closing without choosing still counts as an answer: the visitor has
        // told us the language they are already reading is fine. Recording it
        // is what stops the dialog reappearing on the next page.
        remember(cfg.current || '');
        close();
    }

    function choose(slug, fallbackHref) {
        remember(slug);

        if (slug === cfg.current) {
            close();
            return;
        }

        // This page's counterpart in the chosen language. Polylang falls back to
        // that language's front page when no translation exists, so this is
        // never a dead end.
        var target = (cfg.urls && cfg.urls[slug]) || fallbackHref;
        if (!target) {
            close();
            return;
        }

        // Matches the switcher: tell the front-page redirect in
        // inc/language-preference.php not to second-guess a deliberate click.
        var isRoot = false;
        try {
            isRoot = /^\/([a-z]{2}\/)?$/.test(new URL(target, location.origin).pathname);
        } catch (e) {}

        if (isRoot && target.indexOf('nolangredirect') === -1) {
            target += (target.indexOf('?') === -1 ? '?' : '&') + 'nolangredirect=1';
        }

        location.href = target;
    }

    root.addEventListener('click', function (e) {
        var pick = e.target.closest('[data-lang-choose]');
        if (pick) {
            e.preventDefault();
            choose(pick.getAttribute('data-lang-choose'), pick.getAttribute('href'));
            return;
        }

        if (e.target.closest('[data-lang-dismiss]')) {
            e.preventDefault();
            dismiss();
        }
    });

    open();
})();
</script>
    <?php
}, 20);
