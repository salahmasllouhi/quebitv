<?php
/**
 * The long-form article, plus the table of contents built from it.
 *
 * The body stays in post_content and renders through the_content(). That is a
 * deliberate split from the rest of this template, where the copy lives in
 * m3u-strings.php: this is ~2,300 words of prose with inline images, code
 * samples and outbound links, which is what a WYSIWYG is for. Holding it as a
 * PHP array would turn every typo fix into a deploy, and it would deprive Rank
 * Math of the real post_content it scores against.
 *
 * The TOC is derived from the article's own h2 ids at render time (see
 * iptv_m3u_headings) rather than maintained as a second list, because a
 * hand-kept one drifts the moment somebody edits a heading.
 */

$content = apply_filters('the_content', get_the_content());

if (trim(wp_strip_all_tags($content)) === '') {
    return;
}

$show_toc = iptv_plan_flag('m3u_show_toc', true);
$headings = $show_toc ? iptv_m3u_headings($content) : array();
$toc_title = m3u_str('On this page');
?>
<section class="m3u-article-section dv2-section">
    <div class="container">
        <div class="m3u-article">

            <?php if (count($headings) > 1) : ?>
                <?php // One heading is not a table of contents, it is a link. ?>
                <nav class="m3u-toc" aria-label="<?php echo esc_attr($toc_title); ?>">
                    <p class="m3u-toc-title"><?php echo esc_html($toc_title); ?></p>
                    <ol>
                        <?php foreach ($headings as $heading) : ?>
                            <li>
                                <a href="#<?php echo esc_attr($heading['id']); ?>">
                                    <?php echo esc_html($heading['text']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            <?php endif; ?>

            <?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content() output. ?>

        </div>
    </div>
</section>
