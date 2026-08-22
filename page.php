<?php
/**
 * Template for displaying single pages
 * 
 * Uses universal header and footer for consistency
 * 
 * @package Quebec_IPTV
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    // No <title> here on purpose — see the longer note in header.php. This file
    // hand-rolls its own <head> instead of calling get_header(), and it used to
    // print a title of its own. Because functions.php declares
    // add_theme_support('title-tag'), that put two <title> tags on the page with
    // the hardcoded one first, so search engines read it and ignored Rank Math's.
    // wp_head() below prints the correct, filterable one.
    ?>
    <?php wp_head(); ?>
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/variables.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/redesign-theme.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/base.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/header.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/footer.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/responsive.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/design-v2.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/design-v2-sections.css">
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <!-- Universal Header -->
    <?php include get_template_directory() . '/inc/universal-header.php'; ?>

    <style>
        /* Page-specific styles */
        .page-header {
            background: linear-gradient(135deg, var(--color-navy) 0%, var(--color-indigo) 100%);
            padding: 8rem 0 3rem;
            text-align: center;
            margin-top: 0;
        }

        .page-header h1 {
            color: #fff;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9375rem;
        }

        .page-content {
            padding: 4rem 0;
            background: #fff;
        }

        .content-wrapper {
            max-width: 100%;
            margin: 0;
            padding: 0;
            /* Edit here to add padding, e.g. padding: 0 20px; */
        }

        .content-wrapper h1,
        .content-wrapper h2,
        .content-wrapper h3,
        .content-wrapper h4 {
            color: #0f172a;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }

        .content-wrapper h1 {
            font-size: 2rem;
        }

        .content-wrapper h2 {
            font-size: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--color-teal);
        }

        .content-wrapper h3 {
            font-size: 1.25rem;
        }

        .content-wrapper p {
            margin-bottom: 1rem;
            color: #64748b;
            line-height: 1.8;
        }

        .content-wrapper ul,
        .content-wrapper ol {
            margin-bottom: 1rem;
            padding-left: 1.5rem;
            color: #64748b;
        }

        .content-wrapper li {
            margin-bottom: 0.5rem;
        }

        .content-wrapper a {
            color: var(--color-teal);
            text-decoration: none;
        }

        .content-wrapper a:hover {
            text-decoration: underline;
        }

        .content-wrapper strong {
            color: #0f172a;
        }

        .content-wrapper blockquote {
            border-left: 4px solid var(--color-teal);
            padding-left: 1rem;
            margin: 1.5rem 0;
            background: var(--bg-section);
            padding: 1rem;
            border-radius: 0 0.5rem 0.5rem 0;
        }

        .content-wrapper table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
        }

        .content-wrapper th,
        .content-wrapper td {
            border: 1px solid #e2e8f0;
            padding: 0.75rem;
            text-align: left;
        }

        .content-wrapper th {
            background: #f8fafc;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 7rem 0 2.5rem;
            }

            .page-header h1 {
                font-size: 1.75rem;
            }
        }
    </style>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1><?php the_title(); ?></h1>
            <p><?php echo get_the_date('F j, Y'); ?></p>
        </div>
    </div>

    <!-- Page Content -->
    <main class="page-content">
        <div class="container">
            <div class="content-wrapper">
                <?php
                while (have_posts()):
                    the_post();
                    the_content();
                endwhile;
                ?>
            </div>
        </div>
    </main>

    <!-- Footer from front-page sections -->
    <?php include get_template_directory() . '/front-page/sections/footer.php'; ?>

    <script src="<?php echo get_template_directory_uri(); ?>/front-page/js/currency.js?v=<?php echo filemtime(get_template_directory() . '/front-page/js/currency.js'); ?>"></script>
    <?php wp_footer(); ?>
</body>

</html>