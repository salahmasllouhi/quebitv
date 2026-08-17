<?php
/**
 * The template for displaying single WooCommerce products
 * 
 * This file overrides WooCommerce's default single-product.php
 * Styled to match the Quebec IPTV theme
 */

defined('ABSPATH') || exit;

get_header('shop');

$css_files = [
    'variables.css',
    'redesign-theme.css',
    'base.css',
    'header.css',
    'footer.css',
    'responsive.css',
    'product-page.css',
];
echo '<style>';
foreach ($css_files as $file) {
    $path = get_template_directory() . '/front-page/css/' . $file;
    if (file_exists($path)) {
        echo file_get_contents($path);
    }
}
echo '</style>';
?>

<!-- Universal Header -->
<?php include get_template_directory() . '/inc/universal-header.php'; ?>

<!-- Product Content -->
<main class="product-content">
    <?php
    while (have_posts()):
        the_post();
        wc_get_template_part('content', 'single-product');
    endwhile;
    ?>
</main>

<!-- Footer -->
<?php include get_template_directory() . '/front-page/sections/footer.php'; ?>

<script src="<?php echo get_template_directory_uri(); ?>/front-page/js/currency.js"></script>

<?php
get_footer('shop');