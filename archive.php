<?php
/**
 * Archive Template
 * アーカイブページ用テンプレート
 */
get_header();
?>

<div class="bd-main-content">
    <?php if (have_posts()): ?>
        <header class="archive-header">
            <h1 class="archive-title">
                <?php
                if (is_category()) {
                    single_cat_title();
                } elseif (is_tag()) {
                    single_tag_title();
                } elseif (is_post_type_archive()) {
                    post_type_archive_title();
                } elseif (is_date()) {
                    echo get_the_date('Y年n月');
                } else {
                    echo 'アーカイブ';
                }
                ?>
            </h1>
            <?php
            $description = get_the_archive_description();
            if ($description) {
                echo '<div class="archive-description">' . $description . '</div>';
            }
            ?>
        </header>

        <div class="bd-posts-list">
            <?php while (have_posts()): the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('bd-post-card'); ?>>
                    <?php if (has_post_thumbnail()): ?>
                        <div class="bd-post-thumb">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('medium'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="bd-post-content">
                        <h2 class="bd-post-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        
                        <div class="bd-post-meta">
                            <time datetime="<?php echo get_the_date('c'); ?>">
                                <?php echo get_the_date(); ?>
                            </time>
                            <?php
                            $categories = get_the_category();
                            if ($categories) {
                                echo '<span class="bd-post-category">';
                                echo esc_html($categories[0]->name);
                                echo '</span>';
                            }
                            ?>
                        </div>
                        
                        <div class="bd-post-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                        
                        <a href="<?php the_permalink(); ?>" class="bd-read-more">続きを読む »</a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <?php
        // WordPress標準のページネーション
        the_posts_pagination([
            'mid_size' => 2,
            'prev_text' => '« 前へ',
            'next_text' => '次へ »',
            'class' => 'bd-pagination',
        ]);
        ?>

    <?php else: ?>
        <div class="no-posts">
            <p>投稿が見つかりませんでした。</p>
        </div>
    <?php endif; ?>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
