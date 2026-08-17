<?php
/**
 * Bulk Product Editor Dashboard
 *
 * Allows editing of the 4 core IPTV products (1, 3, 6, 12 Months)
 * and their variations (Devices) in a single view.
 * 
 * Also supports the Trial product.
 *
 * @package Quebec_IPTV
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class IPTV_Bulk_Editor
{
    private $duration_map = array(
        '1_month' => '1 Month Subscription',
        '3_months' => '3 Months Subscription',
        '6_months' => '6 Months Subscription',
        '12_months' => '12 Months Subscription',
    );

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_iptv_save_bulk_products', array($this, 'ajax_save_products'));
        add_action('wp_ajax_iptv_bulk_network_action', array($this, 'ajax_bulk_network_action'));
    }

    public function add_admin_menu()
    {
        add_menu_page(
            'Bulk Product Editor',
            'Bulk Editor',
            'manage_options',
            'iptv-bulk-editor',
            array($this, 'render_page'),
            'dashicons-cart',
            25
        );
    }

    public function enqueue_scripts($hook)
    {
        if ($hook !== 'toplevel_page_iptv-bulk-editor') {
            return;
        }

        // Enqueue media library for image uploads
        wp_enqueue_media();
        wp_enqueue_style('woocommerce_admin_styles');
    }

    public function render_page()
    {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">📦 Bulk Product Editor</h1>
            <button id="trigger-setup" class="page-title-action">↻ Run Setup/Reset Products</button>
            <hr class="wp-header-end">

            <div id="message-area"></div>

            <!-- Bulk Actions Bar -->
            <div class="bulk-actions-bar">
                <label><input type="checkbox" id="select-all"> Select All</label>
                <button type="button" class="button button-secondary" id="bulk-clone">🌐 Clone Selected</button>
                <button type="button" class="button button-link-delete" id="bulk-delete">🗑️ Delete Selected</button>

                <div class="progress-wrap" style="display:none;">
                    <span class="progress-text">Processing...</span>
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                </div>
            </div>

            <form id="bulk-editor-form">
                <?php
                // Render Core Products
                foreach ($this->duration_map as $sku => $title) {
                    $this->render_product_card($sku, $title);
                }

                // Render Trial Product
                $this->render_product_card('trial_24h', '24h Trial');
                ?>

                <div class="bottom-actions">
                    <button type="button" id="save-all" class="button button-primary button-hero">💾 Save All Changes</button>
                    <span class="spinner"></span>
                </div>
            </form>
        </div>

        <style>
            .bulk-actions-bar {
                background: #fff;
                padding: 10px 15px;
                border: 1px solid #ccd0d4;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                gap: 15px;
                box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
            }

            .progress-wrap {
                flex: 1;
                display: flex;
                align-items: center;
                gap: 10px;
                margin-left: auto;
            }

            .progress-bar {
                width: 200px;
                height: 10px;
                background: #eee;
                border-radius: 5px;
                overflow: hidden;
            }

            .progress-fill {
                width: 0%;
                height: 100%;
                background: #2271b1;
                transition: width 0.3s;
            }

            .product-card {
                background: #fff;
                border: 1px solid #ccd0d4;
                box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
                margin-bottom: 20px;
                padding: 0;
            }

            .product-card.selected {
                border: 2px solid #2271b1;
            }

            .product-header {
                padding: 15px;
                background: #f8f9fa;
                border-bottom: 1px solid #ccd0d4;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .product-header h2 {
                margin: 0;
                font-size: 1.2em;
                flex: 1;
            }

            .product-body {
                padding: 15px;
            }

            .variations-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px;
            }

            .variations-table th,
            .variations-table td {
                padding: 10px;
                border-bottom: 1px solid #eee;
                text-align: left;
            }

            .variations-table th {
                background: #fafafa;
                font-weight: 600;
            }

            .price-input {
                width: 80px !important;
            }

            .content-section {
                background: #f0f6fc;
                padding: 15px;
                margin-bottom: 15px;
                border-radius: 4px;
                display: flex;
                gap: 20px;
            }

            .content-left {
                flex: 0 0 150px;
                text-align: center;
            }

            .content-right {
                flex: 1;
            }

            .img-preview {
                width: 150px;
                height: 150px;
                background: #ddd;
                margin-bottom: 10px;
                object-fit: cover;
                border: 1px solid #ccc;
                display: block;
            }

            .visual-editor {
                width: 100%;
                min-height: 100px;
                border: 1px solid #8c8f94;
                border-radius: 4px;
                padding: 8px;
                background: #fff;
                margin-bottom: 10px;
                outline: none;
            }

            .visual-editor:focus {
                border-color: #2271b1;
                box-shadow: 0 0 0 1px #2271b1;
            }

            .editor-toggle-wrap {
                text-align: right;
                margin-bottom: 5px;
            }

            .seo-label {
                display: block;
                font-weight: 600;
                margin-bottom: 5px;
                font-size: 0.9em;
                color: #555;
            }

            .bottom-actions {
                position: sticky;
                bottom: 0;
                background: rgba(255, 255, 255, 0.9);
                padding: 20px;
                border-top: 1px solid #ddd;
                display: flex;
                align-items: center;
                gap: 10px;
                z-index: 99;
            }

            .network-actions {
                display: flex;
                gap: 10px;
            }
        </style>

        <script>
            jQuery(document).ready(function ($) {

                // Toggle Text/HTML
                $('.toggle-editor').on('click', function (e) {
                    e.preventDefault();
                    var btn = $(this);
                    var wrap = btn.closest('.editor-container');
                    var textarea = wrap.find('textarea');
                    var visual = wrap.find('.visual-editor');

                    if (btn.text() === 'Switch to HTML') {
                        // Switch to HTML mode (show textarea)
                        textarea.val(visual.html()); // Sync first
                        visual.hide();
                        textarea.show();
                        btn.text('Switch to Visual');
                    } else {
                        // Switch to Visual mode (show div)
                        visual.html(textarea.val());
                        textarea.hide();
                        visual.show();
                        btn.text('Switch to HTML');
                    }
                });

                // Sync Visual -> Textarea on typing
                $('.visual-editor').on('input', function () {
                    $(this).next('textarea').val($(this).html());
                });

                // Setup Trigger
                $('#trigger-setup').on('click', function (e) {
                    e.preventDefault();
                    if (confirm('This will ensure all products (including Trial) exist/reset. Continue?')) {
                        window.location.href = '<?php echo admin_url('admin.php?page=iptv-bulk-editor&setup_iptv_products=1'); ?>';
                    }
                });

                // Image Upload
                $('.upload-img-btn').on('click', function (e) {
                    e.preventDefault();
                    var btn = $(this);
                    var frame = wp.media({
                        title: 'Select Product Image',
                        multiple: false,
                        library: { type: 'image' },
                        button: { text: 'Use this image' }
                    });
                    frame.on('select', function () {
                        var attachment = frame.state().get('selection').first().toJSON();
                        btn.prev('.img-id').val(attachment.id);
                        btn.prevAll('.img-preview').attr('src', attachment.url);
                    });
                    frame.open();
                });

                $('.remove-img-btn').on('click', function (e) {
                    e.preventDefault();
                    $(this).prevAll('.img-id').val('');
                    $(this).prevAll('.img-preview').attr('src', '');
                });

                // Selection Logic
                $('#select-all').on('change', function () {
                    $('.product-select').prop('checked', $(this).prop('checked')).trigger('change');
                });

                $('.product-select').on('change', function () {
                    if ($(this).is(':checked')) {
                        $(this).closest('.product-card').addClass('selected');
                    } else {
                        $(this).closest('.product-card').removeClass('selected');
                    }
                });

                // Network Action Helper
                function performBulkAction(action, ids) {
                    var total = ids.length;
                    var current = 0;

                    if (total === 0) {
                        alert('No products selected.');
                        return;
                    }

                    if (!confirm(action === 'clone' ? 'Clone ' + total + ' products to ALL network sites? This overwrites existing data.' : 'Delete ' + total + ' products from ALL network sites?')) {
                        return;
                    }

                    $('.progress-wrap').show();
                    $('.progress-fill').css('width', '0%');
                    $('.progress-text').text('Starting...');

                    function processNext() {
                        if (current >= total) {
                            $('.progress-text').text('Completed!');
                            setTimeout(function () { $('.progress-wrap').fadeOut(); }, 2000);
                            return;
                        }

                        var id = ids[current];
                        var percent = Math.round(((current + 1) / total) * 100);
                        $('.progress-text').text('Processing ID ' + id + ' (' + (current + 1) + '/' + total + ')...');
                        $('.progress-fill').css('width', percent + '%');

                        $.post(ajaxurl, {
                            action: 'iptv_bulk_network_action',
                            sub_action: action === 'clone' ? 'clone_to_network' : 'remove_from_network',
                            post_id: id,
                            nonce: '<?php echo wp_create_nonce('iptv_bulk_net_action'); ?>'
                        }, function (response) {
                            console.log('[Clone Debug V2] Response:', response);
                            // Display debug info if available
                            if (response.success && response.data && response.data.debug && response.data.debug.length > 0) {
                                console.log('=== DEBUG INFO for ID ' + id + ' ===');
                                var debugText = response.data.debug.join('\n');
                                console.log(debugText);
                                // Also show first 500 chars in alert for visibility
                                alert('Clone Debug Info:\n' + debugText.substring(0, 500));
                            } else {
                                console.log('[Clone Debug V2] No debug array in response. Response.data:', response.data);
                            }
                            current++;
                            processNext();
                        }).fail(function (xhr, status, error) {
                            console.log('[Clone Debug V2] AJAX failed:', status, error);
                            alert('Network error on ID ' + id);
                            current++;
                            processNext();
                        });
                    }

                    processNext();
                }

                // Bulk Buttons
                $('#bulk-clone').on('click', function () {
                    var ids = $('.product-select:checked').map(function () { return $(this).val(); }).get();
                    performBulkAction('clone', ids);
                });

                $('#bulk-delete').on('click', function () {
                    var ids = $('.product-select:checked').map(function () { return $(this).val(); }).get();
                    performBulkAction('delete', ids);
                });

                // Individual Buttons (Delegate to Bulk Logic)
                $('.clone-net-btn').on('click', function (e) {
                    e.preventDefault();
                    performBulkAction('clone', [$(this).data('id')]);
                });

                $('.remove-net-btn').on('click', function (e) {
                    e.preventDefault();
                    performBulkAction('delete', [$(this).data('id')]);
                });

                // Save All
                $('#save-all').on('click', function () {
                    // Sync all visual editors first
                    $('.visual-editor').each(function () {
                        if ($(this).is(':visible')) {
                            $(this).next('textarea').val($(this).html());
                        }
                    });

                    var btn = $(this);
                    var spinner = btn.next('.spinner');
                    var data = $('#bulk-editor-form').serialize();

                    btn.prop('disabled', true);
                    spinner.addClass('is-active');

                    $.post(ajaxurl, {
                        action: 'iptv_save_bulk_products',
                        data: data,
                        nonce: '<?php echo wp_create_nonce('iptv_bulk_save'); ?>'
                    }, function (response) {
                        btn.prop('disabled', false);
                        spinner.removeClass('is-active');

                        if (response.success) {
                            $('#message-area').html('<div class="notice notice-success is-dismissible"><p>✅ Saved successfully!</p></div>');
                            $('html, body').animate({ scrollTop: 0 }, 'fast');
                        } else {
                            alert('Error: ' + response.data);
                        }
                    });
                });

                // Dynamic Warning for Sale Price 0
                $('input[name*="[simples]"]').on('input', function () {
                    var row = $(this).closest('tr');
                    var regInput = row.find('input[name*="[regular_price]"]');
                    var saleInput = row.find('input[name*="[sale_price]"]');

                    var regVal = parseFloat(regInput.val().replace(',', '.')) || 0;
                    var saleVal = saleInput.val();

                    // Check if sale price is explicitly "0" (not empty) and Reg is > 0
                    if (saleVal === '0' && regVal > 0) {
                        saleInput.css('border', '1px solid red');
                        if (row.find('.sale-warning').length === 0) {
                            saleInput.after('<div class="sale-warning" style="color:red; font-size:10px;">Free ($0)</div>');
                        }
                    } else {
                        saleInput.css('border', '');
                        row.find('.sale-warning').remove();
                    }
                });

                // Trigger once on load
                setTimeout(function () { $('input[name*="[simples]"]').trigger('input'); }, 500);

            })(jQuery);
        </script>
        <?php
    }

    private function render_product_card($sku, $title)
    {
        $product_id = wc_get_product_id_by_sku($sku);
        if (!$product_id) {
            echo '<div class="notice notice-error"><p>Product ' . esc_html($title) . ' (SKU: ' . esc_html($sku) . ') not found. Click "Run Setup" above.</p></div>';
            return;
        }

        $product = wc_get_product($product_id);
        $thumb_id = $product->get_image_id();
        $thumb_url = $thumb_id ? wp_get_attachment_url($thumb_id) : '';

        // Editor Helper
        $render_editor_field = function ($label, $name, $value, $rows = 5) {
            ?>
            <div class="editor-container">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <label class="seo-label"><?php echo esc_html($label); ?></label>
                    <button class="button button-small toggle-editor">Switch to HTML</button>
                </div>
                <!-- Visual Mode (Default) -->
                <div class="visual-editor" contenteditable="true"><?php echo $value; // Intentionally raw HTML ?></div>
                <!-- HTML Mode (Hidden) -->
                <textarea name="<?php echo esc_attr($name); ?>" rows="<?php echo $rows; ?>"
                    style="width:100%; margin-bottom:10px; display:none;"><?php echo esc_textarea($value); ?></textarea>
            </div>
            <?php
        };

        ?>
        <div class="product-card">
            <div class="product-header">
                <input type="checkbox" class="product-select" value="<?php echo $product_id; ?>">
                <h2><?php echo esc_html($title); ?> <small>(ID: <?php echo $product_id; ?>)</small></h2>
                <div class="network-actions">
                    <a href="#" class="button clone-net-btn" data-id="<?php echo $product_id; ?>">🌐 Clone to Network</a>
                    <a href="#" class="button remove-net-btn link-delete" style="color:#a00;"
                        data-id="<?php echo $product_id; ?>">🗑️ Delete from Network</a>
                </div>
            </div>
            <div class="product-body">
                <!-- Main Content & Image -->
                <div class="content-section">
                    <div class="content-left">
                        <label class="seo-label">Product Image</label>
                        <img src="<?php echo esc_url($thumb_url); ?>" class="img-preview">
                        <input type="hidden" name="products[<?php echo $product_id; ?>][image_id]"
                            value="<?php echo esc_attr($thumb_id); ?>" class="img-id">
                        <button class="button upload-img-btn">Set Image</button>
                        <?php if ($thumb_id): ?><button class="button-link remove-img-btn"
                                style="color:#a00; display:block; margin:5px auto;">Remove</button><?php endif; ?>
                    </div>
                    <div class="content-right">
                        <label class="seo-label">Product Name</label>
                        <input type="text" name="products[<?php echo $product_id; ?>][name]"
                            value="<?php echo esc_attr($product->get_name()); ?>" style="width:100%; margin-bottom:10px;">

                        <?php $render_editor_field('Description (Long)', "products[$product_id][description]", $product->get_description(), 10); ?>
                        <?php $render_editor_field('Short Description', "products[$product_id][short_description]", $product->get_short_description(), 5); ?>
                    </div>
                </div>

                <h3>📊 Pricing & Options <small style="font-weight:400; font-size:12px; color:#666;">(Note: Sale Price overrides
                        Regular Price. Clear "Sale Price" to use Regular Price.)</small></h3>
                <table class="variations-table">
                    <thead>
                        <tr>
                            <th>Variation / Type</th>
                            <th>Regular Price ($)</th>
                            <th>Sale Price ($)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $children = $product->get_children();
                        if ($children) {
                            foreach ($children as $child_id) {
                                $variation = wc_get_product($child_id);
                                $attributes = $variation->get_attributes();
                                $device_term = isset($attributes['pa_devices']) ? $attributes['pa_devices'] : 'Unknown';

                                ?>
                                <tr>
                                    <td><strong><?php echo esc_html(ucfirst($device_term)); ?> Device(s)</strong> <small>(ID:
                                            <?php echo $child_id; ?>)</small></td>
                                    <td>
                                        <input type="number" step="0.01" class="price-input"
                                            name="variations[<?php echo $child_id; ?>][regular_price]"
                                            value="<?php echo esc_attr($variation->get_regular_price()); ?>">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="price-input"
                                            name="variations[<?php echo $child_id; ?>][sale_price]"
                                            value="<?php echo esc_attr($variation->get_sale_price()); ?>">
                                    </td>
                                    <td>
                                        <select name="variations[<?php echo $child_id; ?>][status]">
                                            <option value="publish" <?php selected($variation->get_status(), 'publish'); ?>>Active
                                            </option>
                                            <option value="private" <?php selected($variation->get_status(), 'private'); ?>>Private
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <?php
                            }
                        } elseif ($product->is_type('simple')) {
                            // Simple Product (Trial)
                            ?>
                            <tr>
                                <td><strong>Simple Product</strong></td>
                                <td>
                                    <input type="number" step="0.01" class="price-input"
                                        name="simples[<?php echo $product_id; ?>][regular_price]"
                                        value="<?php echo esc_attr($product->get_regular_price()); ?>">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="price-input"
                                        name="simples[<?php echo $product_id; ?>][sale_price]"
                                        value="<?php echo esc_attr($product->get_sale_price()); ?>">
                                </td>
                                <td>
                                    <select name="simples[<?php echo $product_id; ?>][status]">
                                        <option value="publish" <?php selected($product->get_status(), 'publish'); ?>>Active
                                        </option>
                                        <option value="private" <?php selected($product->get_status(), 'private'); ?>>Private
                                        </option>
                                    </select>
                                </td>
                            </tr>
                            <?php
                        } else {
                            echo '<tr><td colspan="4">No variations found.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    public function ajax_bulk_network_action()
    {
        // Verify capabilities and nonce
        if (!current_user_can('manage_options') || !isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'iptv_bulk_net_action')) {
            wp_send_json_error('Unauthorized');
        }

        $post_id = intval($_POST['post_id']);
        $sub_action = sanitize_text_field($_POST['sub_action']);

        if (!$post_id) {
            wp_send_json_error('Invalid ID');
        }

        // Instantiate Cloner
        $cloner = new Theme_Network_Cloner();
        $source_post = get_post($post_id);

        if (!$source_post) {
            wp_send_json_error('Post not found');
        }

        $results = array();

        if ($sub_action === 'clone_to_network') {
            $results = $cloner->clone_to_sites($source_post);
        } elseif ($sub_action === 'remove_from_network') {
            $results = $cloner->remove_from_sites($source_post);
        } else {
            wp_send_json_error('Invalid action');
        }

        wp_send_json_success($results);
    }

    public function ajax_save_products()
    {
        // Check capabilities and nonce
        if (!current_user_can('manage_options') || !isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'iptv_bulk_save')) {
            wp_send_json_error('Unauthorized');
        }

        parse_str($_POST['data'], $form_data);

        // Save Main Products
        if (isset($form_data['products']) && is_array($form_data['products'])) {
            foreach ($form_data['products'] as $p_id => $p_data) {
                $product = wc_get_product($p_id);
                if ($product) {
                    if (isset($p_data['name']))
                        $product->set_name(sanitize_text_field($p_data['name']));
                    if (isset($p_data['description']))
                        $product->set_description(wp_kses_post($p_data['description']));
                    if (isset($p_data['short_description']))
                        $product->set_short_description(wp_kses_post($p_data['short_description']));

                    if (isset($p_data['image_id'])) {
                        $product->set_image_id(intval($p_data['image_id']));
                    }

                    $product->save();
                }
            }
        }

        // Save Variations
        if (isset($form_data['variations']) && is_array($form_data['variations'])) {
            foreach ($form_data['variations'] as $v_id => $v_data) {
                $variation = wc_get_product($v_id);
                if ($variation) {
                    if (isset($v_data['regular_price']))
                        $variation->set_regular_price(sanitize_text_field($v_data['regular_price']));
                    if (isset($v_data['sale_price']))
                        $variation->set_sale_price(sanitize_text_field($v_data['sale_price']));
                    if (isset($v_data['status']))
                        $variation->set_status(sanitize_text_field($v_data['status']));
                    $variation->save();
                }
            }
        }

        // Save Simple Products (Trial)
        if (isset($form_data['simples']) && is_array($form_data['simples'])) {
            foreach ($form_data['simples'] as $p_id => $s_data) {
                $product = wc_get_product($p_id);
                if ($product) {
                    $reg_price = isset($s_data['regular_price']) ? str_replace(',', '.', sanitize_text_field($s_data['regular_price'])) : '';
                    $sale_price = isset($s_data['sale_price']) ? str_replace(',', '.', sanitize_text_field($s_data['sale_price'])) : '';

                    // Fix for user confusion: Treat '0' Sale Price as "No Sale Price" (Empty)
                    if ($sale_price === '0') {
                        $sale_price = '';
                    }

                    if (isset($s_data['regular_price']))
                        $product->set_regular_price($reg_price);
                    // Always update sale price (to empty if it was 0)
                    $product->set_sale_price($sale_price);
                    if (isset($s_data['status']))
                        $product->set_status(sanitize_text_field($s_data['status']));

                    // Logic: If sale price is set (and not empty), it becomes the active price. Use Regular otherwise.
                    // Note: '0' is a valid sale price (Free).
                    $product->set_price($sale_price !== '' ? $sale_price : $reg_price);
                    $product->save();
                }
            }
        }

        wp_send_json_success('Saved');
    }
}

new IPTV_Bulk_Editor();
