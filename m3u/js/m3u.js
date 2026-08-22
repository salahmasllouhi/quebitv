/**
 * M3U to Xtream Codes converter.
 *
 * This lives in a real file rather than in the page body, which is the whole
 * point of the rewrite. The previous version was pasted into post_content as a
 * wp:html block, and WordPress took it apart: wptexturize turned every quote
 * into a smart quote, the backslashes were stripped out of the regex literal
 * and out of the "\n" in the clipboard string, the arrow functions came back
 * HTML-escaped as "=&gt;", and the <script> wrapper went missing so the whole
 * thing printed as visible text at the bottom of the article. None of that can
 * happen to a file the editor never sees.
 *
 * Everything runs in the browser. The URL a visitor pastes contains their
 * subscription username and password, so there is deliberately no fetch, no
 * analytics call carrying the value, and no <form> element — a stray Enter
 * inside a form would put those credentials in a query string and from there
 * into the access log. Values are written with textContent, never innerHTML.
 *
 * Copy comes from window.iptvM3u, printed by m3u/sections/m3u-tool.php, because
 * a .js file cannot call m3u_str() and hardcoding English here would leave the
 * French page showing English errors.
 */
document.addEventListener('DOMContentLoaded', function () {
    var root = document.querySelector('[data-m3u-tool]');
    if (!root) {
        return;
    }

    var cfg = window.iptvM3u || {};
    var text = cfg.text || {};

    var input = root.querySelector('[data-m3u-input]');
    var submit = root.querySelector('[data-m3u-submit]');
    var error = root.querySelector('[data-m3u-error]');
    var result = root.querySelector('[data-m3u-result]');
    var copyBtn = root.querySelector('[data-m3u-copy]');
    var copyLabel = root.querySelector('[data-m3u-copy-label]');

    var out = {
        server: root.querySelector('[data-m3u-server]'),
        username: root.querySelector('[data-m3u-username]'),
        password: root.querySelector('[data-m3u-password]')
    };

    if (!input || !submit || !result) {
        return;
    }

    // Query keys an Xtream panel might use. get.php and player_api.php both
    // use username/password, but enough panels ship shortened variants that
    // checking only the canonical pair produced "no credentials found" on URLs
    // that plainly had them.
    var USER_KEYS = cfg.userKeys || ['username', 'user', 'u'];
    var PASS_KEYS = cfg.passKeys || ['password', 'pass', 'p'];

    // Path segments that name a *kind* of stream rather than a credential.
    // This is the fix for the bug that mattered most: the old parser took the
    // first two path segments unconditionally, so
    // http://host:8080/live/john/secret/12345.ts returned username "live" and
    // password "john" — not an error, just quietly wrong credentials that look
    // right until the user tries to sign in with them.
    var STREAM_KINDS = cfg.streamKinds ||
        ['live', 'movie', 'movies', 'vod', 'series', 'timeshift', 'streaming', 'hlsr', 'play'];

    var extracted = null;

    function say(message) {
        if (!error) {
            return;
        }
        error.textContent = message;
        error.hidden = false;
        result.hidden = true;
    }

    function clearError() {
        if (error) {
            error.hidden = true;
        }
    }

    function lookup(params, keys) {
        for (var i = 0; i < keys.length; i++) {
            var value = params.get(keys[i]);
            if (value) {
                return value;
            }
        }
        return '';
    }

    /**
     * Pull server/username/password out of a playlist URL.
     * Returns null when the URL parses but carries no credentials.
     */
    function parse(raw) {
        // Panels are quoted host-first far more often than scheme-first, and
        // new URL() rejects "host:8080/get.php?..." outright. Assume http,
        // which is what an Xtream panel on a bare port is serving anyway.
        var candidate = /^[a-z][a-z0-9+.-]*:\/\//i.test(raw) ? raw : 'http://' + raw;

        var url;
        try {
            url = new URL(candidate);
        } catch (e) {
            return false; // signals "not a URL" as distinct from "no credentials"
        }

        // url.host keeps the port, which url.hostname drops. Xtream panels are
        // almost always on a non-standard port, and a server URL without it is
        // useless in the app the visitor is about to paste it into.
        var server = url.protocol + '//' + url.host;

        var username = lookup(url.searchParams, USER_KEYS);
        var password = lookup(url.searchParams, PASS_KEYS);

        if (username && password) {
            return { server: server, username: username, password: password };
        }

        var parts = url.pathname.split('/').filter(function (part) {
            return part !== '';
        });

        // Drop a leading stream-kind segment, then a trailing stream id — a
        // numeric segment or a filename with an extension.
        if (parts.length && STREAM_KINDS.indexOf(parts[0].toLowerCase()) !== -1) {
            parts.shift();
        }
        if (parts.length > 2) {
            var last = parts[parts.length - 1];
            if (/^\d+$/.test(last) || last.indexOf('.') !== -1) {
                parts.pop();
            }
        }

        if (parts.length === 2) {
            return { server: server, username: parts[0], password: parts[1] };
        }

        return null;
    }

    function show(data) {
        // textContent, not innerHTML: this string came from a text box, and
        // this page is exactly the sort that gets shared with a URL already in
        // it. Nothing here should ever be parsed as markup.
        out.server.textContent = data.server;
        out.username.textContent = data.username;
        out.password.textContent = data.password;

        extracted = data;
        clearError();
        result.hidden = false;

        if (copyBtn) {
            copyBtn.classList.remove('is-copied');
        }
        if (copyLabel) {
            copyLabel.textContent = text.copy || 'Copy all three';
        }

        result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function run() {
        var raw = (input.value || '').trim();

        if (!raw) {
            say(text.empty || 'Enter an M3U playlist URL first.');
            return;
        }

        var data = parse(raw);

        if (data === false) {
            say(text.invalid || 'That does not look like a URL.');
            return;
        }
        if (data === null) {
            say(text.nocreds || 'No username and password found in that URL.');
            return;
        }

        show(data);
    }

    submit.addEventListener('click', run);

    input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            // There is no form to submit, but preventing the default keeps a
            // stray Enter from doing anything surprising if one is ever added.
            event.preventDefault();
            run();
        }
    });

    // Typing again after an error should clear it rather than leave a stale
    // complaint sitting under a field the visitor has already corrected.
    input.addEventListener('input', clearError);

    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            if (!extracted) {
                return;
            }

            var payload = (text.server || 'Server URL') + ': ' + extracted.server + '\n'
                + (text.username || 'Username') + ': ' + extracted.username + '\n'
                + (text.password || 'Password') + ': ' + extracted.password;

            function done() {
                copyBtn.classList.add('is-copied');
                if (copyLabel) {
                    copyLabel.textContent = text.copied || 'Copied';
                }
                setTimeout(function () {
                    copyBtn.classList.remove('is-copied');
                    if (copyLabel) {
                        copyLabel.textContent = text.copy || 'Copy all three';
                    }
                }, 2000);
            }

            function fallback() {
                // execCommand is deprecated but still the only thing that works
                // without a secure context, and this page is reachable over
                // plain http on somebody's LAN often enough to matter.
                try {
                    var box = document.createElement('textarea');
                    box.value = payload;
                    box.setAttribute('readonly', '');
                    box.style.position = 'fixed';
                    box.style.opacity = '0';
                    document.body.appendChild(box);
                    box.select();
                    document.execCommand('copy');
                    document.body.removeChild(box);
                    done();
                } catch (e) {
                    say(text.copyFailed || 'Could not copy.');
                }
            }

            // navigator.clipboard is undefined entirely on insecure origins, so
            // feature-detect rather than relying on a rejected promise: calling
            // .writeText() on undefined throws synchronously and the .catch()
            // never runs.
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(payload).then(done).catch(fallback);
            } else {
                fallback();
            }
        });
    }
});
