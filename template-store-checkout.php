<?php
/**
 * Template Name: Store - Checkout
 * Template Post Type: page
 * 
 * Custom simplified checkout form with Thank You page
 */

defined('ABSPATH') || exit;

// Check if this is the order-received (thank you) page
$is_thank_you_page = is_wc_endpoint_url('order-received');
$order = null;

if ($is_thank_you_page) {
    // Get the order ID from the URL
    global $wp;
    $order_id = absint($wp->query_vars['order-received']);
    $order = wc_get_order($order_id);
}

// Get cart data (only needed for checkout, not thank you page)
$cart_items = array();
$cart_total = '';
if (!$is_thank_you_page && class_exists('WooCommerce') && function_exists('WC') && WC() && WC()->cart) {
    $cart_items = WC()->cart->get_cart();
    $cart_total = WC()->cart->get_cart_total();
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right'); ?></title>
    <?php wp_head(); ?>
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/variables.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/redesign-theme.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/base.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/header.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/footer.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/responsive.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/design-v2.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/design-v2-sections.css">
    <link rel="stylesheet"
        href="<?php echo get_template_directory_uri(); ?>/front-page/css/checkout.css?v=<?php echo time(); ?>">
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <!-- Front Page Header -->
    <?php include get_template_directory() . '/inc/universal-header.php'; ?>

    <?php if ($is_thank_you_page && $order): ?>
        <!-- Thank You Page -->
        <?php include get_template_directory() . '/front-page/partials/checkout/thank-you.php'; ?>
    <?php else: ?>
        <!-- Regular Checkout Page -->
        <!-- Page Title -->
        <div class="checkout-title">
            <h1>Checkout</h1>
        </div>

        <div class="checkout-container">
            <!-- Left: Checkout Form -->
            <form id="checkout-form" class="checkout-form woocommerce-checkout" method="post"
                action="<?php echo esc_url(wc_get_checkout_url()); ?>">
                <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
                <input type="hidden" name="payment_method" id="hidden_payment_method" value="">
                <input type="hidden" name="woocommerce_checkout_place_order" value="1">

                <h2>Your Information</h2>

                <!-- Email -->
                <div class="form-group">
                    <label for="billing_email">Email Address *</label>
                    <input type="email" name="billing_email" id="billing_email" placeholder="your@email.com" required>
                </div>

                <!-- Phone with Country Code -->
                <div class="form-group">
                    <label for="billing_phone">Phone Number *</label>
                    <div class="phone-input">
                        <select name="phone_country" id="phone_country">
                            <!-- Priority: Canada & USA -->
                            <option value="+1">🇨🇦 Canada +1</option>
                            <option value="+1">🇺🇸 United States +1</option>
                            <optgroup label="──── All Countries ────">
                                <option value="+93">🇦🇫 Afghanistan +93</option>
                                <option value="+355">🇦🇱 Albania +355</option>
                                <option value="+213">🇩🇿 Algeria +213</option>
                                <option value="+376">🇦🇩 Andorra +376</option>
                                <option value="+244">🇦🇴 Angola +244</option>
                                <option value="+54">🇦🇷 Argentina +54</option>
                                <option value="+374">🇦🇲 Armenia +374</option>
                                <option value="+61">🇦🇺 Australia +61</option>
                                <option value="+43">🇦🇹 Austria +43</option>
                                <option value="+994">🇦🇿 Azerbaijan +994</option>
                                <option value="+973">🇧🇭 Bahrain +973</option>
                                <option value="+880">🇧🇩 Bangladesh +880</option>
                                <option value="+375">🇧🇾 Belarus +375</option>
                                <option value="+32">🇧🇪 Belgium +32</option>
                                <option value="+501">🇧🇿 Belize +501</option>
                                <option value="+229">🇧🇯 Benin +229</option>
                                <option value="+975">🇧🇹 Bhutan +975</option>
                                <option value="+591">🇧🇴 Bolivia +591</option>
                                <option value="+387">🇧🇦 Bosnia +387</option>
                                <option value="+267">🇧🇼 Botswana +267</option>
                                <option value="+55">🇧🇷 Brazil +55</option>
                                <option value="+673">🇧🇳 Brunei +673</option>
                                <option value="+359">🇧🇬 Bulgaria +359</option>
                                <option value="+226">🇧🇫 Burkina Faso +226</option>
                                <option value="+257">🇧🇮 Burundi +257</option>
                                <option value="+855">🇰🇭 Cambodia +855</option>
                                <option value="+237">🇨🇲 Cameroon +237</option>
                                <option value="+1">🇨🇦 Canada +1</option>
                                <option value="+238">🇨🇻 Cape Verde +238</option>
                                <option value="+236">🇨🇫 Central African Rep. +236</option>
                                <option value="+235">🇹🇩 Chad +235</option>
                                <option value="+56">🇨🇱 Chile +56</option>
                                <option value="+86">🇨🇳 China +86</option>
                                <option value="+57">🇨🇴 Colombia +57</option>
                                <option value="+269">🇰🇲 Comoros +269</option>
                                <option value="+243">🇨🇩 Congo (DRC) +243</option>
                                <option value="+242">🇨🇬 Congo (Rep.) +242</option>
                                <option value="+506">🇨🇷 Costa Rica +506</option>
                                <option value="+225">🇨🇮 Côte d'Ivoire +225</option>
                                <option value="+385">🇭🇷 Croatia +385</option>
                                <option value="+53">🇨🇺 Cuba +53</option>
                                <option value="+357">🇨🇾 Cyprus +357</option>
                                <option value="+420">🇨🇿 Czech Republic +420</option>
                                <option value="+253">🇩🇯 Djibouti +253</option>
                                <option value="+593">🇪🇨 Ecuador +593</option>
                                <option value="+20">🇪🇬 Egypt +20</option>
                                <option value="+503">🇸🇻 El Salvador +503</option>
                                <option value="+240">🇬🇶 Equatorial Guinea +240</option>
                                <option value="+291">🇪🇷 Eritrea +291</option>
                                <option value="+372">🇪🇪 Estonia +372</option>
                                <option value="+268">🇸🇿 Eswatini +268</option>
                                <option value="+251">🇪🇹 Ethiopia +251</option>
                                <option value="+679">🇫🇯 Fiji +679</option>
                                <option value="+33">🇫🇷 France +33</option>
                                <option value="+241">🇬🇦 Gabon +241</option>
                                <option value="+220">🇬🇲 Gambia +220</option>
                                <option value="+995">🇬🇪 Georgia +995</option>
                                <option value="+49">🇩🇪 Germany +49</option>
                                <option value="+233">🇬🇭 Ghana +233</option>
                                <option value="+30">🇬🇷 Greece +30</option>
                                <option value="+502">🇬🇹 Guatemala +502</option>
                                <option value="+224">🇬🇳 Guinea +224</option>
                                <option value="+245">🇬🇼 Guinea-Bissau +245</option>
                                <option value="+592">🇬🇾 Guyana +592</option>
                                <option value="+509">🇭🇹 Haiti +509</option>
                                <option value="+504">🇭🇳 Honduras +504</option>
                                <option value="+852">🇭🇰 Hong Kong +852</option>
                                <option value="+36">🇭🇺 Hungary +36</option>
                                <option value="+91">🇮🇳 India +91</option>
                                <option value="+62">🇮🇩 Indonesia +62</option>
                                <option value="+98">🇮🇷 Iran +98</option>
                                <option value="+964">🇮🇶 Iraq +964</option>
                                <option value="+353">🇮🇪 Ireland +353</option>
                                <option value="+972">🇮🇱 Israel +972</option>
                                <option value="+39">🇮🇹 Italy +39</option>
                                <option value="+1876">🇯🇲 Jamaica +1876</option>
                                <option value="+81">🇯🇵 Japan +81</option>
                                <option value="+962">🇯🇴 Jordan +962</option>
                                <option value="+7">🇰🇿 Kazakhstan +7</option>
                                <option value="+254">🇰🇪 Kenya +254</option>
                                <option value="+965">🇰🇼 Kuwait +965</option>
                                <option value="+996">🇰🇬 Kyrgyzstan +996</option>
                                <option value="+856">🇱🇦 Laos +856</option>
                                <option value="+371">🇱🇻 Latvia +371</option>
                                <option value="+961">🇱🇧 Lebanon +961</option>
                                <option value="+266">🇱🇸 Lesotho +266</option>
                                <option value="+231">🇱🇷 Liberia +231</option>
                                <option value="+218">🇱🇾 Libya +218</option>
                                <option value="+423">🇱🇮 Liechtenstein +423</option>
                                <option value="+370">🇱🇹 Lithuania +370</option>
                                <option value="+352">🇱🇺 Luxembourg +352</option>
                                <option value="+853">🇲🇴 Macau +853</option>
                                <option value="+261">🇲🇬 Madagascar +261</option>
                                <option value="+265">🇲🇼 Malawi +265</option>
                                <option value="+60">🇲🇾 Malaysia +60</option>
                                <option value="+960">🇲🇻 Maldives +960</option>
                                <option value="+223">🇲🇱 Mali +223</option>
                                <option value="+356">🇲🇹 Malta +356</option>
                                <option value="+222">🇲🇷 Mauritania +222</option>
                                <option value="+230">🇲🇺 Mauritius +230</option>
                                <option value="+52">🇲🇽 Mexico +52</option>
                                <option value="+373">🇲🇩 Moldova +373</option>
                                <option value="+377">🇲🇨 Monaco +377</option>
                                <option value="+976">🇲🇳 Mongolia +976</option>
                                <option value="+382">🇲🇪 Montenegro +382</option>
                                <option value="+212">🇲🇦 Morocco +212</option>
                                <option value="+258">🇲🇿 Mozambique +258</option>
                                <option value="+95">🇲🇲 Myanmar +95</option>
                                <option value="+264">🇳🇦 Namibia +264</option>
                                <option value="+977">🇳🇵 Nepal +977</option>
                                <option value="+31">🇳🇱 Netherlands +31</option>
                                <option value="+64">🇳🇿 New Zealand +64</option>
                                <option value="+505">🇳🇮 Nicaragua +505</option>
                                <option value="+227">🇳🇪 Niger +227</option>
                                <option value="+234">🇳🇬 Nigeria +234</option>
                                <option value="+389">🇲🇰 North Macedonia +389</option>
                                <option value="+968">🇴🇲 Oman +968</option>
                                <option value="+92">🇵🇰 Pakistan +92</option>
                                <option value="+970">🇵🇸 Palestine +970</option>
                                <option value="+507">🇵🇦 Panama +507</option>
                                <option value="+675">🇵🇬 Papua New Guinea +675</option>
                                <option value="+595">🇵🇾 Paraguay +595</option>
                                <option value="+51">🇵🇪 Peru +51</option>
                                <option value="+63">🇵🇭 Philippines +63</option>
                                <option value="+48">🇵🇱 Poland +48</option>
                                <option value="+351">🇵🇹 Portugal +351</option>
                                <option value="+974">🇶🇦 Qatar +974</option>
                                <option value="+40">🇷🇴 Romania +40</option>
                                <option value="+7">🇷🇺 Russia +7</option>
                                <option value="+250">🇷🇼 Rwanda +250</option>
                                <option value="+966">🇸🇦 Saudi Arabia +966</option>
                                <option value="+221">🇸🇳 Senegal +221</option>
                                <option value="+381">🇷🇸 Serbia +381</option>
                                <option value="+232">🇸🇱 Sierra Leone +232</option>
                                <option value="+65">🇸🇬 Singapore +65</option>
                                <option value="+421">🇸🇰 Slovakia +421</option>
                                <option value="+386">🇸🇮 Slovenia +386</option>
                                <option value="+252">🇸🇴 Somalia +252</option>
                                <option value="+27">🇿🇦 South Africa +27</option>
                                <option value="+82">🇰🇷 South Korea +82</option>
                                <option value="+211">🇸🇸 South Sudan +211</option>
                                <option value="+34">🇪🇸 Spain +34</option>
                                <option value="+94">🇱🇰 Sri Lanka +94</option>
                                <option value="+249">🇸🇩 Sudan +249</option>
                                <option value="+597">🇸🇷 Suriname +597</option>
                                <option value="+41">🇨🇭 Switzerland +41</option>
                                <option value="+963">🇸🇾 Syria +963</option>
                                <option value="+886">🇹🇼 Taiwan +886</option>
                                <option value="+992">🇹🇯 Tajikistan +992</option>
                                <option value="+255">🇹🇿 Tanzania +255</option>
                                <option value="+66">🇹🇭 Thailand +66</option>
                                <option value="+670">🇹🇱 Timor-Leste +670</option>
                                <option value="+228">🇹🇬 Togo +228</option>
                                <option value="+676">🇹🇴 Tonga +676</option>
                                <option value="+1868">🇹🇹 Trinidad & Tobago +1868</option>
                                <option value="+216">🇹🇳 Tunisia +216</option>
                                <option value="+90">🇹🇷 Turkey +90</option>
                                <option value="+993">🇹🇲 Turkmenistan +993</option>
                                <option value="+256">🇺🇬 Uganda +256</option>
                                <option value="+380">🇺🇦 Ukraine +380</option>
                                <option value="+971">🇦🇪 UAE +971</option>
                                <option value="+44">🇬🇧 United Kingdom +44</option>
                                <option value="+598">🇺🇾 Uruguay +598</option>
                                <option value="+998">🇺🇿 Uzbekistan +998</option>
                                <option value="+678">🇻🇺 Vanuatu +678</option>
                                <option value="+58">🇻🇪 Venezuela +58</option>
                                <option value="+84">🇻🇳 Vietnam +84</option>
                                <option value="+967">🇾🇪 Yemen +967</option>
                                <option value="+260">🇿🇲 Zambia +260</option>
                                <option value="+263">🇿🇼 Zimbabwe +263</option>
                            </optgroup>
                        </select>
                        <input type="tel" name="billing_phone" id="billing_phone" placeholder="Phone number" required>
                    </div>
                </div>

                <!-- Subscription Type (Radio Cards) -->
                <div class="subscription-selector">
                    <div class="subscription-option">
                        <input type="radio" name="subscription_type" id="sub_new" value="new" checked>
                        <label for="sub_new">New Subscription</label>
                    </div>
                    <div class="subscription-option">
                        <input type="radio" name="subscription_type" id="sub_renew" value="renewal">
                        <label for="sub_renew">Renew Existing</label>
                    </div>
                </div>

                <!-- Order Note -->
                <div class="form-group">
                    <label for="order_note">Order Note (Optional)</label>
                    <textarea name="order_comments" id="order_note"
                        placeholder="Add any special instructions or notes for your order..."></textarea>
                </div>

                <?php do_action('woocommerce_checkout_before_order_review'); ?>
            </form>

            <!-- Right: Order Summary -->
            <div class="order-summary">
                <h2>Order Summary</h2>

                <?php if ($cart_is_empty): ?>
                    <!-- Empty Cart State -->
                    <div class="empty-cart">
                        <svg class="empty-cart-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h2>Your cart is empty</h2>
                        <p>Browse our products and find something you love!</p>
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-home">Go to Home</a>
                    </div>
                <?php else: ?>
                    <!-- Cart Items -->
                    <?php foreach ($cart_items as $cart_item_key => $cart_item):
                        $product = $cart_item['data'];
                        $product_id = $cart_item['product_id'];
                        $quantity = $cart_item['quantity'];
                        $image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'thumbnail');
                        $remove_url = wc_get_cart_remove_url($cart_item_key);
                        ?>
                        <div class="cart-item">
                            <div class="cart-item-image">
                                <?php if ($image): ?>
                                    <img src="<?php echo esc_url($image[0]); ?>" alt="<?php echo esc_attr($product->get_name()); ?>">
                                <?php endif; ?>
                            </div>
                            <div class="cart-item-details">
                                <div class="cart-item-title"><?php echo esc_html($product->get_name()); ?></div>
                                <div class="cart-item-meta">Qty: <?php echo esc_html($quantity); ?></div>
                            </div>
                            <div class="cart-item-price"><?php echo WC()->cart->get_product_subtotal($product, $quantity); ?></div>
                            <a href="<?php echo esc_url($remove_url); ?>" class="cart-item-remove" title="Remove item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        </div>
                    <?php endforeach; ?>

                    <!-- Coupon -->
                    <div class="coupon-section">
                        <div class="coupon-input">
                            <input type="text" name="coupon_code" id="coupon_code" placeholder="Coupon code">
                            <button type="button" onclick="applyCoupon()">Apply</button>
                        </div>
                    </div>

                    <!-- Order Totals -->
                    <div class="order-totals">
                        <div class="order-total-row">
                            <span>Subtotal</span>
                            <span><?php echo WC()->cart->get_cart_subtotal(); ?></span>
                        </div>
                        <?php if (WC()->cart->get_discount_total() > 0): ?>
                            <div class="order-total-row">
                                <span>Discount</span>
                                <span>-<?php echo wc_price(WC()->cart->get_discount_total()); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="order-total-row total">
                            <span>Total</span>
                            <span><?php echo $cart_total; ?></span>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="payment-methods">
                        <h3>Payment Method</h3>
                        <?php
                        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
                        $first = true;
                        foreach ($available_gateways as $gateway):
                            $icon = $gateway->get_icon();
                            $description = $gateway->get_description();
                            ?>
                            <div class="payment-option">
                                <input type="radio" name="payment_method" id="payment_<?php echo esc_attr($gateway->id); ?>"
                                    value="<?php echo esc_attr($gateway->id); ?>" <?php echo $first ? 'checked' : ''; ?>>
                                <div class="payment-option-content">
                                    <label for="payment_<?php echo esc_attr($gateway->id); ?>">
                                        <?php if ($icon): ?>
                                            <span class="payment-icon"><?php echo $icon; ?></span>
                                        <?php endif; ?>
                                        <span class="payment-title"><?php echo esc_html($gateway->get_title()); ?></span>
                                    </label>
                                    <?php if ($description): ?>
                                        <p class="payment-description"><?php echo wp_kses_post($description); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php $first = false; endforeach; ?>
                    </div>

                    <!-- Place Order Button -->
                    <button type="button" class="place-order-btn" onclick="submitCheckout()">
                        Complete Purchase
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <script>
            // Submit checkout form via AJAX (WooCommerce way)
            function submitCheckout() {
                // Get selected payment method from radio buttons
                const selectedPayment = document.querySelector('.payment-option input[type="radio"]:checked');
                if (selectedPayment) {
                    document.getElementById('hidden_payment_method').value = selectedPayment.value;
                }

                const form = document.getElementById('checkout-form');
                const formData = new FormData(form);

                // Show loading state
                const btn = document.querySelector('.place-order-btn');
                btn.disabled = true;
                btn.textContent = 'Processing...';

                // Get the checkout URL - use form action as fallback
                const checkoutUrl = (typeof wc_checkout_params !== 'undefined' && wc_checkout_params.checkout_url)
                    ? wc_checkout_params.checkout_url
                    : form.action;

                // Submit via AJAX to WooCommerce
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: new URLSearchParams(formData).toString() + '&action=woocommerce_checkout',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.result === 'success') {
                            // Redirect to thank you page
                            window.location.href = data.redirect;
                        } else if (data.result === 'failure') {
                            // Show error messages
                            btn.disabled = false;
                            btn.textContent = 'Complete Purchase';
                            const msg = data.messages ? data.messages.replace(/<[^>]*>/g, '').trim() : 'An error occurred. Please try again.';
                            alert(msg);
                        } else {
                            // Unknown response, show error
                            btn.disabled = false;
                            btn.textContent = 'Complete Purchase';
                            console.log('Checkout response:', data);
                            alert('Unexpected response. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Checkout error:', error);
                        btn.disabled = false;
                        btn.textContent = 'Complete Purchase';
                        alert('An error occurred. Please try again.');
                    });
            }

            // Initialize payment method on page load
            document.addEventListener('DOMContentLoaded', function () {
                const firstPayment = document.querySelector('.payment-option input[type="radio"]');
                if (firstPayment) {
                    document.getElementById('hidden_payment_method').value = firstPayment.value;
                }
            });

            function applyCoupon() {
                const code = document.getElementById('coupon_code').value;
                if (code) {
                    jQuery.post(wc_checkout_params.ajax_url, {
                        action: 'apply_coupon',
                        coupon_code: code,
                        security: wc_checkout_params.apply_coupon_nonce
                    }, function () {
                        location.reload();
                    });
                }
            }

            // Handle remove item - stay on checkout page
            document.querySelectorAll('.cart-item-remove').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const url = this.getAttribute('href');
                    // Add return_to parameter to stay on checkout
                    const checkoutUrl = window.location.href.split('?')[0];
                    window.location.href = url + '&_wp_http_referer=' + encodeURIComponent(checkoutUrl);
                });
            });
        </script>

    <?php endif; ?> <!-- End checkout page conditional -->

    <!-- Front Page Footer -->
    <?php include get_template_directory() . '/front-page/sections/footer.php'; ?>

    <script
        src="<?php echo get_template_directory_uri(); ?>/front-page/js/checkout.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/front-page/js/currency.js"></script>
    <?php wp_footer(); ?>
</body>

</html>