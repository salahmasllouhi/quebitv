<?php
/**
 * Template Name: M3U Converter
 * Description: The M3U to Xtream Codes converter and its article. Assign this
 *              template to a page; the tool, the FAQ and the schema follow.
 *
 * This replaces a page that had been a 31KB blob of CSS, HTML and JavaScript
 * pasted into post_content as wp:html blocks. WordPress had taken it apart:
 * the <style> and <script> wrappers were gone, so ~460 lines of CSS and ~40 of
 * JS printed as visible body text; the URL <input> had been stripped out
 * entirely, leaving a button wired to nothing; wptexturize had smart-quoted
 * every string in the script; the backslashes had been removed from its regex
 * literal and its "\n"; the arrow functions came back as "=&gt;"; and every
 * inline SVG had been deleted. The tool had not worked in a long time.
 *
 * None of that is a thing that can be fixed by pasting better markup — the
 * editor would do it again on the next save. So the CSS and JS move into files,
 * the structural copy moves into m3u/inc/m3u-strings.php, and only the article
 * prose stays in post_content, where it belongs and where Rank Math can score
 * it.
 *
 * Section order:
 *   1. header     (shared, front page is the source of truth)
 *   2. m3u-hero   (H1, intro, jump to the tool)
 *   3. m3u-tool   (the converter — the reason anyone lands here)
 *   4. m3u-article(the_content(), with a TOC built from its own h2 ids)
 *   5. m3u-faq    (accordion)
 *   6. m3u-cta    (closing band)
 *   7. footer     (shared)
 *   8. m3u-schema (SoftwareApplication + FAQPage + HowTo JSON-LD)
 *
 * @package Quebec_IPTV
 */

get_header();

$theme_dir = get_template_directory();
$front_dir = $theme_dir . '/front-page';
$m3u_dir   = $theme_dir . '/m3u';
?>

<style>
    <?php
    // The same layers the plan pages load, in the same order — design-v2 must
    // come last so it remaps the older tokens — plus this template's sheet.
    //
    // No pricing.css and no reviews.css: there is no configurator and no review
    // row on this page, and both sheets are large.
    $css_files = array(
        $front_dir . '/css/variables.css',
        $front_dir . '/css/base.css',
        $front_dir . '/css/header.css',
        $front_dir . '/css/footer.css',
        $front_dir . '/css/responsive.css',
        $front_dir . '/css/redesign-theme.css',
        $front_dir . '/css/cta.css',
        $front_dir . '/css/design-v2.css',
        $front_dir . '/css/design-v2-sections.css',
        $m3u_dir   . '/css/m3u.css',
    );

    foreach ($css_files as $path) {
        if (file_exists($path)) {
            include $path;
        }
    }
    ?>
</style>

<?php // Page-wide backdrop, as on the front page and the plan pages. ?>
<div class="dv2-grid-wash" aria-hidden="true"></div>

<?php
include $front_dir . '/sections/header.php';

while (have_posts()) :
    the_post();

    include $m3u_dir . '/sections/m3u-hero.php';
    include $m3u_dir . '/sections/m3u-tool.php';
    include $m3u_dir . '/sections/m3u-article.php';
    include $m3u_dir . '/sections/m3u-faq.php';
    include $m3u_dir . '/sections/m3u-cta.php';
    include $m3u_dir . '/sections/m3u-schema.php';

endwhile;

include $front_dir . '/sections/footer.php';
?>

<script>
    <?php
    // header.js for the nav, currency.js because the header and footer language
    // switcher runs on it, and m3u.js for the converter. No pricing.js or
    // carousels.js — neither has anything to bind to here.
    $js_files = array(
        $front_dir . '/js/header.js',
        $front_dir . '/js/currency.js',
        $m3u_dir   . '/js/m3u.js',
    );

    foreach ($js_files as $path) {
        if (file_exists($path)) {
            include $path;
        }
    }
    ?>
</script>

<?php get_footer(); ?>
