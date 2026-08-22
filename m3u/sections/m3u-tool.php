<?php
/**
 * The converter itself.
 *
 * Everything the old page lost is restored here in PHP, where the block editor
 * cannot reach it: the <input> (which had been stripped out entirely, leaving a
 * label and a button that operated on nothing), and the three inline SVG icons
 * (also stripped, leaving empty divs that the CSS still sized).
 *
 * There is deliberately no <form>. The value in this field is a subscription
 * URL carrying a username and password; inside a form, one Enter key would put
 * those in a query string and from there into the server's access log. m3u.js
 * binds the button and the Enter key directly instead.
 *
 * Icons follow the theme's convention — 24x24 viewBox, 1.5-1.7 stroke,
 * currentColor, aria-hidden — as used in plan/sections/plan-hero.php and
 * front-page/sections/steps.php.
 */

$title    = iptv_plan_field('m3u_tool_title', m3u_str('M3U to Xtream Codes converter'));
$subtitle = iptv_plan_field('m3u_tool_subtitle', m3u_str('Works with both URL shapes: query parameters and path-based playlists.'));

$label       = m3u_str('Your M3U playlist URL');
$placeholder = m3u_str('http://example.com:8080/get.php?username=…&password=…');
$submit      = m3u_str('Extract credentials');
$privacy     = m3u_str('Nothing you paste leaves your browser. The conversion runs entirely on this page — no request is made, and nothing is stored or logged.');

$result_title = m3u_str('Credentials extracted');
$copy_label   = m3u_str('Copy all three');

$text = iptv_m3u_tool_text();
?>
<section class="m3u-tool" id="m3u-tool">
    <div class="container">
        <div class="m3u-tool-panel" data-m3u-tool>

            <div class="m3u-tool-glow" aria-hidden="true"></div>

            <div class="m3u-tool-head">
                <h2><?php echo esc_html($title); ?></h2>
                <p><?php echo esc_html($subtitle); ?></p>
            </div>

            <div class="m3u-tool-card">

                <div class="m3u-field">
                    <label class="m3u-label" for="m3uInput"><?php echo esc_html($label); ?></label>
                    <?php
                    // inputmode=url gets the right phone keyboard; the three
                    // "off" attributes stop a browser or password manager
                    // offering to remember a string that is, functionally, a
                    // password.
                    ?>
                    <input type="text"
                        id="m3uInput"
                        class="m3u-input m3u-mono"
                        data-m3u-input
                        inputmode="url"
                        autocomplete="off"
                        autocapitalize="off"
                        autocorrect="off"
                        spellcheck="false"
                        placeholder="<?php echo esc_attr($placeholder); ?>">
                </div>

                <button type="button" class="dv2-btn dv2-btn-primary dv2-btn-lg m3u-submit" data-m3u-submit>
                    <?php echo esc_html($submit); ?>
                </button>

                <p class="m3u-error" data-m3u-error role="alert" hidden></p>

                <div class="m3u-note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <p><?php echo esc_html($privacy); ?></p>
                </div>

                <div class="m3u-result" data-m3u-result hidden>

                    <div class="m3u-result-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 6 9 17l-5-5"></path>
                        </svg>
                        <?php echo esc_html($result_title); ?>
                    </div>

                    <div class="m3u-cred">
                        <span class="m3u-cred-label"><?php echo esc_html($text['server']); ?></span>
                        <span class="m3u-cred-value m3u-mono" data-m3u-server>&ndash;</span>
                    </div>
                    <div class="m3u-cred">
                        <span class="m3u-cred-label"><?php echo esc_html($text['username']); ?></span>
                        <span class="m3u-cred-value m3u-mono" data-m3u-username>&ndash;</span>
                    </div>
                    <div class="m3u-cred">
                        <span class="m3u-cred-label"><?php echo esc_html($text['password']); ?></span>
                        <span class="m3u-cred-value m3u-mono" data-m3u-password>&ndash;</span>
                    </div>

                    <button type="button" class="dv2-btn dv2-btn-white m3u-copy" data-m3u-copy>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="9" y="9" width="13" height="13" rx="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        <span data-m3u-copy-label><?php echo esc_html($copy_label); ?></span>
                    </button>

                </div>

            </div>
        </div>
    </div>
</section>

<script>
    // Parser rules and copy for m3u.js. Printed here rather than inlined in the
    // script because the strings have to go through m3u_str() to be translated.
    window.iptvM3u = <?php echo wp_json_encode(iptv_m3u_tool_config()); ?>;
</script>
