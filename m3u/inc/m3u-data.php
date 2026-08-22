<?php
/**
 * M3U converter — page data
 *
 * Thin by design. The heavy lifting is in m3u-strings.php (copy) and m3u.js
 * (the parser); this is the layer that turns ACF fields and bundled defaults
 * into the arrays the sections and the schema both read.
 *
 * @package Quebec_IPTV
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('iptv_m3u_faq_items')) {
    /**
     * FAQ rows for this page.
     *
     * The m3u_faq ACF repeater when it has rows, otherwise the bundled defaults
     * run through m3u_str(). m3u-faq.php and m3u-schema.php both call this, so
     * the accordion and the FAQPage rich result cannot list different questions.
     *
     * @return array<int,array{q:string,a:string}>
     */
    function iptv_m3u_faq_items()
    {
        // iptv_plan_field() reads ACF on the *current* page with a raw post-meta
        // fallback for fields that live in acf-json/ but have not been synced
        // into the database yet. The plan_ prefix is historical — it is a
        // generic current-page reader, and duplicating it here to get a nicer
        // name would mean two copies of the same fallback logic.
        $rows = iptv_plan_field('m3u_faq', array());

        $items = array();

        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (!empty($row['question'])) {
                    $items[] = array(
                        'q' => $row['question'],
                        'a' => isset($row['answer']) ? $row['answer'] : '',
                    );
                }
            }
        }

        if (!empty($items)) {
            return $items;
        }

        foreach (iptv_m3u_faq_defaults() as $row) {
            $items[] = array(
                'q' => m3u_str($row['q']),
                'a' => m3u_str($row['a']),
            );
        }

        return $items;
    }
}

if (!function_exists('iptv_m3u_tool_text')) {
    /**
     * The strings m3u.js prints, as a plain array for wp_json_encode().
     *
     * A .js file cannot call m3u_str(), and hardcoding English inside it would
     * leave the French page showing English error messages — which is exactly
     * the class of bug this whole rebuild exists to clear. Handing the copy to
     * the script keeps one translation surface.
     *
     * @return array<string,string>
     */
    function iptv_m3u_tool_text()
    {
        return array(
            'empty'      => m3u_str('Enter an M3U playlist URL first.'),
            'invalid'    => m3u_str('That does not look like a URL. Check it and try again.'),
            'nocreds'    => m3u_str('No username and password found in that URL. Xtream playlists usually look like /get.php?username=…&password=… or /username/password/.'),
            'server'     => m3u_str('Server URL'),
            'username'   => m3u_str('Username'),
            'password'   => m3u_str('Password'),
            'copy'       => m3u_str('Copy all three'),
            'copied'     => m3u_str('Copied'),
            'copyFailed' => m3u_str('Could not copy — select the values and copy them by hand.'),
        );
    }
}

if (!function_exists('iptv_m3u_tool_config')) {
    /**
     * Parser rules, kept in PHP so they are configurable without touching JS.
     *
     * stream_kinds is the interesting one. The old parser took the first two
     * path segments of a playlist URL unconditionally, so
     * http://host:8080/live/john/secret/12345.ts produced username "live" and
     * password "john" — not an error, just wrong credentials that look
     * plausible until someone tries to sign in with them. Naming the segments
     * that describe a *kind* of stream is what lets the parser skip them.
     *
     * @return array<string,mixed>
     */
    function iptv_m3u_tool_config()
    {
        return array(
            'userKeys'    => array('username', 'user', 'u'),
            'passKeys'    => array('password', 'pass', 'p'),
            'streamKinds' => array('live', 'movie', 'movies', 'vod', 'series', 'timeshift', 'streaming', 'hlsr', 'play'),
            'text'        => iptv_m3u_tool_text(),
        );
    }
}

if (!function_exists('iptv_m3u_headings')) {
    /**
     * The article's h2 anchors, for the table of contents.
     *
     * Read out of the rendered content rather than maintained as a second list,
     * because a hand-kept TOC drifts the moment someone edits a heading — and
     * the article is deliberately editable in the block editor.
     *
     * Uses DOMDocument rather than a regex: the body is author-controlled HTML,
     * and a regex over it gets the nested-markup cases wrong. Returns an empty
     * array when the extension is missing rather than falling back to something
     * worse, which just hides the TOC.
     *
     * @param string $html Rendered post content.
     * @return array<int,array{id:string,text:string}>
     */
    function iptv_m3u_headings($html)
    {
        if (!class_exists('DOMDocument') || trim($html) === '') {
            return array();
        }

        $doc = new DOMDocument();

        // libxml complains about HTML5 elements it does not know; the parse is
        // still usable, so the errors are swallowed rather than logged.
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML(
            '<?xml encoding="utf-8" ?><div>' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $out = array();

        foreach ($doc->getElementsByTagName('h2') as $node) {
            $id = $node->getAttribute('id');
            $text = trim($node->textContent);

            // Only headings that can actually be linked to. A TOC entry
            // pointing at nothing is worse than a missing entry.
            if ($id !== '' && $text !== '') {
                $out[] = array('id' => $id, 'text' => $text);
            }
        }

        return $out;
    }
}
