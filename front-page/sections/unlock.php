<?php
/**
 * Section: Supported devices (Design v2)
 * Static chip grid, driven by the comma-separated device_list setting.
 */
$devices_str = iptv_text('device_list', 'Smart TV, Android TV, Apple TV, Fire Stick, iPhone / iPad, Android Phone, Windows, Mac, Set-Top Box, Chromecast, Roku, Kodi');
$devices = array_filter(array_map('trim', explode(',', $devices_str)));
?>
<section class="devices dv2-section">
    <div class="devices-inner">
        <div class="dv2-section-head">
            <h2><?php echo esc_html(iptv_text('devices_section_title', 'Works on every screen')); ?></h2>
            <p><?php echo esc_html(iptv_text('devices_subtitle', 'Stream Quebec IPTV on any device you already own. No new hardware needed.')); ?></p>
        </div>

        <div class="dv2-device-chips">
            <?php foreach ($devices as $device) : ?>
                <span class="dv2-device-chip"><?php echo esc_html($device); ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</section>
