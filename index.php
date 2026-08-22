<?php
/**
 * Blog Archive / Listing Page Template
 * Creative grid layout with featured posts
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

    <!-- Include Front Page CSS Files -->
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/variables.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/redesign-theme.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/base.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/header.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/footer.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/blog.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/responsive.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/design-v2.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/front-page/css/design-v2-sections.css">

    <style>
        /* Minimal overrides if needed */
        body {
            font-family: 'Outfit', 'Inter', -apple-system, sans-serif;
            background: var(--bg-section);
            color: var(--text-primary);
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .no-posts {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            grid-column: span 3;
        }
    </style>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <!-- Include Universal Header -->
    <?php include get_template_directory() . '/inc/universal-header.php'; ?>

    <!-- BLOG HEADER -->
    <div class="blog-header">
        <div class="container">
            <h1>Blog & News</h1>
            <p>Latest updates, guides, and streaming tips</p>
        </div>
    </div>

    <!-- BLOG CONTENT -->
    <main class="container">
        <div class="blog-grid">
            <?php
            $post_count = 0;
            if (have_posts()):
                while (have_posts()):
                    the_post();
                    $post_count++;
                    $is_featured = ($post_count === 1);
                    $categories = get_the_category();
                    $category_name = !empty($categories) ? $categories[0]->name : 'General';
                    ?>
                    <article class="post-card <?php echo $is_featured ? 'featured' : ''; ?>">
                        <?php if (has_post_thumbnail()): ?>
                            <img src="<?php the_post_thumbnail_url('large'); ?>" alt="<?php the_title_attribute(); ?>"
                                class="post-card-image">
                        <?php else: ?>
                            <div class="post-card-image"></div>
                        <?php endif; ?>

                        <div class="post-card-content">
                            <div class="post-card-meta">
                                <span class="post-card-category"><?php echo esc_html($category_name); ?></span>
                                <span><?php echo get_the_date('M j, Y'); ?></span>
                            </div>
                            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                            <p class="post-card-excerpt"><?php echo wp_trim_words(get_the_excerpt(), $is_featured ? 30 : 20); ?>
                            </p>
                            <a href="<?php the_permalink(); ?>" class="post-card-link">
                                Read More <span>→</span>
                            </a>
                        </div>
                    </article>
                    <?php
                endwhile;
            else:
                ?>
                <div class="no-posts">
                    <h2>No posts found</h2>
                    <p>Check back soon for new content!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <?php
            echo paginate_links(array(
                'prev_text' => '← Previous',
                'next_text' => 'Next →',
            ));
            ?>
        </div>
    </main>

    <!-- Universal Footer -->
    <?php include get_template_directory() . '/front-page/sections/footer.php'; ?>

    <!-- Include Currency JS -->
    <script src="<?php echo get_template_directory_uri(); ?>/front-page/js/currency.js"></script>

    <?php wp_footer(); ?>
</body>

</html>