<?php
/**
 * The template for displaying WooCommerce pages (checkout, account, etc.)
 * 
 * Note: Single product pages use single-product.php template
 * Styled to match the Quebec IPTV theme
 */

defined('ABSPATH') || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right'); ?></title>
    <?php wp_head(); ?>
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/variables.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/base.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/header.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/footer.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/responsive.css">
    <link rel="stylesheet"
        href="<?php echo get_template_directory_uri(); ?>/front-page/css/product-page.css?v=<?php echo time(); ?>">

    <style>
        /* Fix Header Logo Size - match home page (32px height) */
        .site-header .logo img,
        .site-header .logo-img,
        .site-header img.logo-img,
        .logo-img,
        header .logo img,
        .nav-container .logo img {
            height: 40px !important;
            width: auto !important;
            max-height: 40px !important;
        }

        /* WooCommerce Content Container */
        .wc-content {
            max-width: 1000px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
        }

        /* Hide breadcrumbs */
        .woocommerce-breadcrumb {
            display: none !important;
        }
    </style>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <!-- Universal Header -->
    <?php include get_template_directory() . '/inc/universal-header.php'; ?>

    <!-- Page Content -->
    <main class="wc-content">
        <?php woocommerce_content(); ?>
    </main>

    <!-- Footer -->
    <?php include get_template_directory() . '/front-page/sections/footer.php'; ?>

    <script src="<?php echo get_template_directory_uri(); ?>/front-page/js/header.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/front-page/js/currency.js"></script>
    <?php wp_footer(); ?>
</body>

</html>