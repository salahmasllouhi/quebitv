// Currency Selector JavaScript v2
function toggleCountryDropdown() {
    const dropdown = document.getElementById('countryDropdown');
    dropdown.classList.toggle('active');
}

// Footer dropdown toggle
function toggleFooterDropdown() {
    const dropdown = document.getElementById('footerCountryDropdown');
    if (dropdown) dropdown.classList.toggle('active');
}

// Remember the language the visitor just picked, so the next visit opens in it.
// inc/language-preference.php reads this cookie and prints window.nordictvLang.
function rememberLanguageChoice(currency) {
    const cfg = window.nordictvLang;
    if (!cfg || !cfg.byCurrency) return;

    const slug = cfg.byCurrency[currency];
    if (!slug) return;

    document.cookie = cfg.cookie + '=' + encodeURIComponent(slug) +
        ';path=/;max-age=' + (cfg.days * 24 * 60 * 60) + ';samesite=lax';
}

// Where switching to `currency` should land.
//
// Prefer this page's counterpart in the chosen language — window.nordictvLangUrl
// is printed by inc/language-preference.php from Polylang, which knows each
// translation's URL and falls back to that language's front page by itself.
// Switching language from a sub-page used to drop you on the other language's
// front page rather than its counterpart, because only the language roots
// below were ever consulted.
function languageTargetUrl(currency) {
    const countryUrls = {
        usd: '/',
        cad: '/fr/'
    };

    const translated = window.nordictvLangUrl && window.nordictvLangUrl(currency);
    if (translated) return translated;

    const path = countryUrls[currency];
    return path ? window.location.origin + path : null;
}

// The preference redirect only runs on a front page, so only guard those — no
// need to hang a query string off every inner page URL. It covers the case
// where the cookie write silently failed and a stale preference would otherwise
// bounce the visitor straight back.
function withLangRedirectGuard(url) {
    let isRoot = false;
    try {
        isRoot = /^\/([a-z]{2}\/)?$/.test(new URL(url, window.location.origin).pathname);
    } catch (e) {
        isRoot = false;
    }
    if (!isRoot) return url;
    return url + (url.indexOf('?') === -1 ? '?' : '&') + 'nolangredirect=1';
}

// Redirect to the chosen language
function redirectToRegion(currency) {
    const target = languageTargetUrl(currency);
    if (!target) return;

    rememberLanguageChoice(currency);
    window.location.href = withLangRedirectGuard(target);
}

// Close dropdown when clicking outside
document.addEventListener('click', function (e) {
    const selector = document.getElementById('countrySelector');
    const dropdown = document.getElementById('countryDropdown');
    if (selector && !selector.contains(e.target)) {
        if (dropdown) dropdown.classList.remove('active');
    }

    // Footer dropdown
    const footerSelector = document.getElementById('footerCountrySelector');
    const footerDropdown = document.getElementById('footerCountryDropdown');
    if (footerSelector && !footerSelector.contains(e.target)) {
        if (footerDropdown) footerDropdown.classList.remove('active');
    }
});

// Currency data — `name` is the native language label shown in the switcher,
// `code` stays for price formatting and anything reading the currency code.
const currencyData = {
    cad: { symbol: '$', flag: '🇨🇦', code: 'CAD', name: 'Français', position: 'before' },
    usd: { symbol: '$', flag: '🇺🇸', code: 'USD', name: 'English', position: 'before' }
};

// URL mappings for each currency/country
const countryUrls = {
    usd: '/',
    cad: '/fr/'
};

// Get default currency from URL path or localStorage
// Get default currency from URL path
function getDefaultCurrency() {
    return getCurrentCurrencyFromUrl();
}

// Detect current currency from URL
function getCurrentCurrencyFromUrl() {
    const currentPath = window.location.pathname;
    if (currentPath.startsWith('/fr')) return 'cad';
    return 'usd';
}

// Update UI and prices for selected currency
function setCurrency(currency) {
    const data = currencyData[currency];
    if (!data) return;

    // Update header dropdown
    const headerFlag = document.getElementById('selectedFlag');
    const headerCode = document.getElementById('selectedCode');
    if (headerFlag) headerFlag.textContent = data.flag;
    if (headerCode) headerCode.textContent = data.name;

    // Update footer dropdown
    const footerFlag = document.getElementById('footerSelectedFlag');
    const footerCode = document.getElementById('footerSelectedCode');
    if (footerFlag) footerFlag.textContent = data.flag;
    if (footerCode) footerCode.textContent = data.name;

    document.querySelectorAll('.country-option').forEach(opt => {
        opt.classList.remove('selected');
        if (opt.dataset.currency === currency) {
            opt.classList.add('selected');
        }
    });

    window.currentCurrency = currency;
    updateAllPrices();
    localStorage.setItem('iptv_currency', currency);

    const headerDropdown = document.getElementById('countryDropdown');
    const footerDropdown = document.getElementById('footerCountryDropdown');
    if (headerDropdown) headerDropdown.classList.remove('active');
    if (footerDropdown) footerDropdown.classList.remove('active');
}

// Footer currency setter (syncs with header)
function setFooterCurrency(currency) {
    const currentCurrency = getCurrentCurrencyFromUrl();

    // Same as the header switcher: this is a deliberate choice, so record it.
    rememberLanguageChoice(currency);

    if (currency !== currentCurrency) {
        const target = languageTargetUrl(currency);
        if (target) {
            window.location.href = withLangRedirectGuard(target);
            return;
        }
    }
    setCurrency(currency);
}

// Update all prices based on selected device count and currency
function updateAllPrices() {
    if (!window.iptvPrices) return;

    const currency = window.currentCurrency || 'usd';
    const data = currencyData[currency];

    // pricing.js marks the chosen card with .active; .selected kept for safety.
    const selectedDevice = document.querySelector('.select-card.active[data-devices], .select-card.selected[data-devices]');
    let deviceKey = '1_device';
    if (selectedDevice) {
        const deviceNum = parseInt(selectedDevice.dataset.devices);
        deviceKey = deviceNum === 1 ? '1_device' : deviceNum + '_devices';
    }

    const durationMap = { '1': '1_month', '3': '3_months', '6': '6_months', '12': '12_months' };

    document.querySelectorAll('.duration-card').forEach(card => {
        const duration = card.dataset.duration;
        const durationKey = durationMap[duration];
        const priceEl = card.querySelector('.duration-price');

        if (priceEl && durationKey && window.iptvPrices[durationKey] && window.iptvPrices[durationKey][deviceKey]) {
            const price = window.iptvPrices[durationKey][deviceKey][currency];
            if (price) {
                priceEl.textContent = data.position === 'before'
                    ? data.symbol + price
                    : price + ' ' + data.symbol;
            }
        }
    });

    // Update Comparison Table "Annual Price"
    const compPriceEl = document.getElementById('comp-annual-price');
    if (compPriceEl && window.iptvPrices && window.iptvPrices['12_months'] && window.iptvPrices['12_months']['1_device']) {
        const price = window.iptvPrices['12_months']['1_device'][currency];
        if (price) {
            compPriceEl.textContent = data.position === 'before'
                ? data.symbol + price
                : price + ' ' + data.symbol;
        }
    }

    // Let the configurator re-render per-month lines, savings badges and CTA.
    if (typeof window.iptvRefreshPricing === 'function') {
        window.iptvRefreshPricing();
    }
}

// There is deliberately no browser-language auto-redirect here any more. A
// Swedish-configured browser asking for the English page gets the English page;
// only a language the visitor picked themselves ever moves them, and that is
// handled server-side in inc/language-preference.php.

// Initialize currency on page load
document.addEventListener('DOMContentLoaded', function () {

    // Set up country option click handlers with redirect
    document.querySelectorAll('.country-option').forEach(option => {
        option.addEventListener('click', function (e) {
            e.preventDefault(); // Prevent default link behavior to ensure storage save

            const currency = this.dataset.currency;

            // This is the visitor choosing — remember it for next time.
            rememberLanguageChoice(currency);

            const currentCurrency = htmlCurrentCurrency(); // Helper to safely get current
            const target = languageTargetUrl(currency);

            if (currency !== currentCurrency && target) {
                window.location.href = withLangRedirectGuard(target);
            } else {
                setCurrency(currency);
            }
        });
    });

    // Set default currency based on current URL
    const defaultCurrency = getDefaultCurrency();
    setCurrency(defaultCurrency);
});

function htmlCurrentCurrency() {
    return getCurrentCurrencyFromUrl();
}
