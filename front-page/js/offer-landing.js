// Offer Landing Page JavaScript
// Handles: currency-aware pricing display, CTA button links, FAQ accordion
(function () {

    // ── Currency helpers (mirrors pricing.js pattern) ────────────────────────
    function getCurrencyData() {
        // currency.js publishes the real table; this is only a floor. It listed
        // the retired Nordic currencies, which is harmless while it is never
        // reached but is exactly the kind of stale copy that broke pricing.js.
        var data = window.iptvCurrencyData || {
            cad: { symbol: '$', position: 'before', decimals: true },
            usd: { symbol: '$', position: 'before', decimals: true }
        };
        var code = window.iptvPriceCurrency || 'usd';
        return data[code] || data['usd'];
    }

    function formatPrice(price) {
        var cd = getCurrencyData();
        var formatted = cd.decimals ? price.toFixed(2) : Math.round(price).toString();
        return cd.position === 'before'
            ? cd.symbol + formatted
            : formatted + '\u00a0' + cd.symbol; // non-breaking space before suffix symbol
    }

    // ── Pricing display update ────────────────────────────────────────────────
    function updateOfferPricing() {
        if (!window.iptvPrices) return;

        var currency = window.iptvPriceCurrency || 'usd';
        var paidMonths = window.offerPaidMonths || 12;
        var freeMonths = window.offerFreeMonths || 3;
        var totalMonths = paidMonths + freeMonths;

        // Map paid months to the nearest iptvPrices duration key
        var durationMap = { 1: '1_month', 3: '3_months', 6: '6_months', 12: '12_months' };
        var durationKey = durationMap[paidMonths];

        // Use 1-device pricing as the reference price for single-offer display
        var actualPrice = 0;
        if (durationKey && window.iptvPrices[durationKey] && window.iptvPrices[durationKey]['1_device']) {
            actualPrice = parseFloat(window.iptvPrices[durationKey]['1_device'][currency]) || 0;
        }

        // Full value = 1-month rate × total months (what it would cost without the deal)
        var oneMonthPrice = 0;
        if (window.iptvPrices['1_month'] && window.iptvPrices['1_month']['1_device']) {
            oneMonthPrice = parseFloat(window.iptvPrices['1_month']['1_device'][currency]) || 0;
        }
        var fullValue = oneMonthPrice * totalMonths;

        // Update actual price element (only if it's the JS-managed placeholder)
        var actualEl = document.getElementById('offer-price-actual');
        if (actualEl && actualEl.classList.contains('offer-pricing__amount--js') && actualPrice > 0) {
            actualEl.textContent = formatPrice(actualPrice);
        }

        // Update strikethrough full value
        var fullEl = document.getElementById('offer-price-full');
        if (fullEl && fullValue > 0) {
            fullEl.textContent = formatPrice(fullValue);
        }
    }

    // ── CTA buttons: ensure all point to the correct checkout URL ────────────
    function updateCtaButtons() {
        var url = window.offerCheckoutUrl;
        if (!url || url === '#pricing') return;

        document.querySelectorAll('.offer-cta-btn').forEach(function (btn) {
            btn.setAttribute('href', url);
        });
    }

    // ── Currency change listener ──────────────────────────────────────────────
    // currency.js dispatches 'iptv:currencyChanged' with detail.currency after redirect/selection.
    // We listen here in case the page is served with a currency param already applied.
    document.addEventListener('iptv:currencyChanged', function (e) {
        if (e.detail && e.detail.currency) {
            window.currentCurrency = e.detail.currency;
        }
        updateOfferPricing();
        updateCtaButtons();
    });

    // ── Init ─────────────────────────────────────────────────────────────────
    updateOfferPricing();
    updateCtaButtons();

})();
