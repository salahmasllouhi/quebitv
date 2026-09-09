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
