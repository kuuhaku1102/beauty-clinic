<?php
/**
 * Page Template
 * 固定ページ用テンプレート
 */
get_header();
?>

<div class="bd-main-content">
    <?php while (have_posts()): the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('bd-page'); ?>>
            <header class="page-header">
                <h1 class="page-title"><?php the_title(); ?></h1>
            </header>

            <?php if (has_post_thumbnail()): ?>
                <div class="page-thumbnail">
                    <?php the_post_thumbnail('large'); ?>
                </div>
            <?php endif; ?>

            <div class="page-content">
                <?php the_content(); ?>
            </div>

            <?php
            // ページリンク(<!--nextpage-->タグ使用時)
            wp_link_pages([
                'before' => '<div class="page-links">ページ: ',
                'after' => '</div>',
            ]);
            ?>
        </article>

        <?php
        // コメントが有効な場合
        if (comments_open() || get_comments_number()) {
            comments_template();
        }
        ?>
    <?php endwhile; ?>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
