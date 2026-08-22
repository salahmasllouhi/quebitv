<?php
/**
 * M3U converter — Polylang string registration
 *
 * Same arrangement as plan/inc/plan-strings.php, and for the same reason: the
 * theme ships no .mo files and never calls load_theme_textdomain(), so a __()
 * in a template renders English in both languages. Everything a converter page
 * can print is registered with Polylang instead and shows up under
 * Languages → String translations.
 *
 * Usage in sections: m3u_str('Default English text')
 *
 * The FAQ and the "7 ways" list are held here as data rather than inside the
 * sections that print them, because two other files read the same arrays —
 * m3u-schema.php builds FAQPage and HowTo from them, and m3u-seo.php feeds them
 * to Rank Math. One source means the accordion, the rich result and the SEO
 * analysis can never describe different questions.
 *
 * @package Quebec_IPTV
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('iptv_m3u_translations')) {
    /**
     * Bundled copy for this template, english => translation.
     *
     * Ships with the theme so the French page reads correctly the moment it is
     * published rather than after someone works through the string table. A
     * translation entered there still wins — see m3u_str().
     *
     * @param string $lang Polylang language slug.
     * @return array<string,string>
     */
    function iptv_m3u_translations($lang)
    {
        static $cache = array();

        if (isset($cache[$lang])) {
            return $cache[$lang];
        }

        $file = __DIR__ . '/translations/' . $lang . '.php';
        $cache[$lang] = file_exists($file) ? (array) include $file : array();

        return $cache[$lang];
    }
}

if (!function_exists('m3u_str')) {
    /**
     * The current language's version of an M3U string.
     *
     * Order: a translation entered in Polylang's string table, then the copy
     * bundled with the theme, then the English default. Polylang first so an
     * editor can always override what ships; pll__() returns its input
     * unchanged when nothing has been entered, which is what makes the
     * comparison below a usable "was it translated?" test.
     *
     * @param string $default English default, which is also the lookup key.
     * @return string
     */
    function m3u_str($default)
    {
        if (function_exists('pll__')) {
            $translated = pll__($default);
            if ($translated !== $default) {
                return $translated;
            }
        }

        $lang = function_exists('pll_current_language') ? pll_current_language('slug') : '';

        if ($lang && $lang !== 'en') {
            $bundled = iptv_m3u_translations($lang);
            if (isset($bundled[$default]) && $bundled[$default] !== '') {
                return $bundled[$default];
            }
        }

        return $default;
    }
}

if (!function_exists('iptv_m3u_faq_defaults')) {
    /**
     * The five FAQ rows, lifted from the article the old page carried.
     *
     * Overridden per page by the m3u_faq ACF repeater.
     *
     * @return array<int,array{q:string,a:string}>
     */
    function iptv_m3u_faq_defaults()
    {
        return array(
            array(
                'q' => 'What is the difference between M3U and M3U8 playlist files?',
                'a' => 'The difference is character encoding and what the file is for. A plain M3U uses ASCII, which covers basic Latin characters; an M3U8 uses UTF-8, so channel names with accents and non-Latin scripts survive. M3U8 is also the format HTTP Live Streaming requires, which is what lets a stream change quality as your connection does.',
            ),
            array(
                'q' => 'Can I edit my M3U file by hand?',
                'a' => 'Yes — it is a plain text file, so Notepad, TextEdit or VS Code will all open it. You can delete channels you never watch, reorder groups, or change stream URLs. Save it as UTF-8 when you are done, or accented channel names will come back as question marks.',
            ),
            array(
                'q' => 'Why do some channels in my playlist not work?',
                'a' => 'Usually an expired stream URL, server maintenance, a regional restriction, or peak-hour load. Reload the playlist first — that fixes most of it. If the same handful of channels fail every time, the problem is on the provider’s side and worth reporting to them.',
            ),
            array(
                'q' => 'How often should I refresh my playlist?',
                'a' => 'Weekly is a good habit. Providers add channels and move stream URLs more often than people expect. Most IPTV apps can refresh on a schedule — a 24-hour interval keeps things current without hammering the server.',
            ),
            array(
                'q' => 'Is your converter safe to use with my real playlist URL?',
                'a' => 'Yes. Everything happens inside your browser: the URL you paste is never sent to us, never logged and never stored. You can confirm that by opening your browser’s network tab while you use it — there is no request. Your playlist URL contains your subscription credentials, so it is a fair thing to check on any tool that asks for one.',
            ),
        );
    }
}

if (!function_exists('iptv_m3u_howto_steps')) {
    /**
     * The seven uses, condensed to one sentence each for the HowTo graph.
     *
     * These are deliberately a summary of the article rather than a second copy
     * of it: schema that repeats the page word for word adds bytes and nothing
     * else, and the headings in the article are the canonical version.
     *
     * @return array<int,array{name:string,text:string}>
     */
    function iptv_m3u_howto_steps()
    {
        return array(
            array(
                'name' => 'Load the URL straight into a media player',
                'text' => 'Copy the playlist URL, open "Open Network Stream" in VLC, PotPlayer or IINA, and paste it. The whole channel list appears within seconds.',
            ),
            array(
                'name' => 'Convert it to Xtream Codes credentials',
                'text' => 'TiviMate, IPTV Smarters and XCIPTV all behave better with a server, username and password than with a raw playlist file. Use the converter at the top of this page to pull those three values out.',
            ),
            array(
                'name' => 'Import it into Kodi',
                'text' => 'Install the PVR IPTV Simple Client add-on, point it at the playlist URL, and live TV appears inside Kodi’s own interface with guide data and recording.',
            ),
            array(
                'name' => 'Build a shorter, custom channel list',
                'text' => 'Open the file in a text editor, delete the entries you never watch, and save it with the same extension. A shorter list loads faster and is far easier to navigate.',
            ),
            array(
                'name' => 'Set it up on a smart TV',
                'text' => 'Smart IPTV, SS IPTV and OTTPlayer all accept a playlist URL on Samsung, LG and similar sets, which turns the TV into a receiver with no extra hardware.',
            ),
            array(
                'name' => 'Use it on a phone or tablet',
                'text' => 'GSE Smart IPTV, IPTV Smarters and Perfect Player accept either the playlist URL or Xtream credentials, so the same subscription travels with you.',
            ),
            array(
                'name' => 'Manage several playlists at once',
                'text' => 'Web-based managers can merge, deduplicate and refresh multiple playlists on a schedule, which is worth it once you are maintaining more than one.',
            ),
        );
    }
}

/**
 * Register everything an M3U page can print.
 *
 * Strings that reach iptv_text() are registered here too: that helper ends in
 * pll__($default) when the front page has no field of the same name, which is
 * true of every converter-only key.
 */
add_action('init', function () {
    if (!function_exists('pll_register_string')) {
        return;
    }

    $group = 'M3U Converter';

    $register = function ($name, $string, $multiline = false) use ($group) {
        pll_register_string($name, $string, $group, $multiline);
    };

    // ── Hero ─────────────────────────────────────────────────────────────────
    $register('m3u_eyebrow', 'Free tool');
    $register('m3u_headline', 'M3U editor and playlist converter');
    $register('m3u_intro', 'Paste an M3U playlist URL and get the Xtream Codes server, username and password out of it — instantly, and without the URL ever leaving your browser.', true);
    $register('m3u_hero_cta', 'Open the converter');

    // ── The tool ─────────────────────────────────────────────────────────────
    $register('m3u_tool_title', 'M3U to Xtream Codes converter');
    $register('m3u_tool_subtitle', 'Works with both URL shapes: query parameters and path-based playlists.', true);
    $register('m3u_tool_label', 'Your M3U playlist URL');
    $register('m3u_tool_placeholder', 'http://example.com:8080/get.php?username=…&password=…');
    $register('m3u_tool_submit', 'Extract credentials');
    $register('m3u_tool_privacy', 'Nothing you paste leaves your browser. The conversion runs entirely on this page — no request is made, and nothing is stored or logged.', true);
    $register('m3u_result_title', 'Credentials extracted');
    $register('m3u_result_server', 'Server URL');
    $register('m3u_result_username', 'Username');
    $register('m3u_result_password', 'Password');
    $register('m3u_copy', 'Copy all three');
    $register('m3u_copied', 'Copied');
    $register('m3u_copy_failed', 'Could not copy — select the values and copy them by hand.');

    // Error messages. These are printed by m3u.js, which reads them out of the
    // window.iptvM3u blob rather than carrying English of its own — a JS file
    // cannot call m3u_str().
    $register('m3u_err_empty', 'Enter an M3U playlist URL first.');
    $register('m3u_err_invalid', 'That does not look like a URL. Check it and try again.');
    $register('m3u_err_nocreds', 'No username and password found in that URL. Xtream playlists usually look like /get.php?username=…&password=… or /username/password/.');

    // ── Article + TOC ────────────────────────────────────────────────────────
    $register('m3u_toc_title', 'On this page');

    // ── FAQ ──────────────────────────────────────────────────────────────────
    $register('m3u_faq_title', 'Questions about M3U playlists');

    foreach (iptv_m3u_faq_defaults() as $i => $row) {
        $n = $i + 1;
        $register("m3u_faq_{$n}_q", $row['q']);
        $register("m3u_faq_{$n}_a", $row['a'], true);
    }

    // ── HowTo steps ──────────────────────────────────────────────────────────
    foreach (iptv_m3u_howto_steps() as $i => $step) {
        $n = $i + 1;
        $register("m3u_howto_{$n}_name", $step['name']);
        $register("m3u_howto_{$n}_text", $step['text'], true);
    }

    // ── Closing band ─────────────────────────────────────────────────────────
    $register('m3u_final_title', 'A playlist is only as good as the service behind it');
    $register('m3u_final_text', 'Quebec IPTV gives you both an M3U playlist and Xtream Codes credentials, on 40,000+ channels and 200,000+ films. Try it for 24 hours without a card.', true);
    $register('m3u_final_cta', 'See plans and pricing');
    $register('m3u_final_cta_trial', 'Start a 24-hour trial');

    // ── Schema ───────────────────────────────────────────────────────────────
    $register('m3u_schema_name', 'M3U to Xtream Codes converter');
    $register('m3u_schema_description', 'A free browser-based tool that extracts the Xtream Codes server URL, username and password from any M3U playlist link. Nothing is uploaded.', true);
    $register('m3u_howto_name', 'How to use an M3U playlist');
});
