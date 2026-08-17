<?php
/**
 * Retire the `channel` post type.
 *
 * Like `sport`, the CPT was registered through ACF PRO's post-type UI rather
 * than a register_post_type() call, so there is nothing in this theme to delete
 * and nothing in version control that records the section being switched off.
 * Unregistering it here takes its posts off the front end and out of the Rank
 * Math sitemap while leaving the ACF definition untouched, so the section can
 * be brought back by removing this file.
 *
 * No draft migration runs here, unlike inc/sport-retire.php: all 322 channel
 * posts were already sitting on `draft` when the type was retired, so there is
 * nothing to unpublish and no need for a one-shot guarded pass.
 *
 * @package Quebec_IPTV
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Take `channel` off the front end: no single views, no archive, no sitemap.
 *
 * Priority 99 so ACF (init 5) has registered the type by the time we get here.
 */
add_action('init', function () {
    if (post_type_exists('channel')) {
        unregister_post_type('channel');
    }
}, 99);
