<?php
/**
 * iBos chatbot widget.
 *
 * @package Quebec_IPTV
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_footer', function () {
    if (is_admin()) {
        return;
    }
    ?>
    <style id="ibos-chat-position">
        /* Keep the iBos launcher above the existing Chaty contact button and
         * the sticky CTA bar. The widget reads this variable inside its
         * shadow root when it positions the launcher. */
        #ibos-chat-host {
            --bottom: calc(var(--sticky-cta-offset, 0px) + 100px) !important;
        }
    </style>
    <script>
        window.iBosChat = {
            theme: 'auto',
            teaser: true,
            agentName: 'Alex'
        };
    </script>
    <script src="https://chatbot.salahox.top/widget.js" defer></script>
    <?php
}, 20);
