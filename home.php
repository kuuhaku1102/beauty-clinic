<?php
/**
 * Blog Archive Template (home.php)
 * ブログ記事一覧ページ
 */
get_header();
?>

<div class="bd-blog-archive">
    <div class="bd-page-header">
        <h1 class="bd-page-title">美容コラム</h1>
        <p class="bd-page-description">美容医療に関する最新情報をお届けします</p>
    </div>
    
    <?php if (have_posts()): ?>
        <div class="bd-blog-list">
            <?php while (have_posts()): the_post(); ?>
                <article class="bd-blog-card">
                    <a href="<?php the_permalink(); ?>" class="bd-blog-card-link">
                        <?php if (has_post_thumbnail()): ?>
                            <div class="bd-blog-thumb">
                                <?php the_post_thumbnail('medium'); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="bd-blog-content">
                            <div class="bd-blog-meta">
                                <time class="bd-blog-date" datetime="<?php echo get_the_date('c'); ?>">
                                    <?php echo get_the_date('Y.m.d'); ?>
                                </time>
                                <?php
                                $categories = get_the_category();
                                if ($categories):
                                    foreach ($categories as $category):
                                ?>
                                    <span class="bd-blog-category"><?php echo esc_html($category->name); ?></span>
                                <?php
                                    endforeach;
                                endif;
                                ?>
                            </div>
                            
                            <h2 class="bd-blog-title"><?php the_title(); ?></h2>
                            
                            <div class="bd-blog-excerpt">
                                <?php echo wp_trim_words(get_the_excerpt(), 80, '...'); ?>
                            </div>
                            
                            <?php
                            $tags = get_the_tags();
                            if ($tags):
                            ?>
                                <div class="bd-blog-tags">
                                    <?php foreach (array_slice($tags, 0, 3) as $tag): ?>
                                        <span class="bd-blog-tag">#<?php echo esc_html($tag->name); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                </article>
            <?php endwhile; ?>
        </div>
        
        <?php
        // ページネーション
        $total_pages = $wp_query->max_num_pages;
        if ($total_pages > 1):
        ?>
            <nav class="bd-pagination" role="navigation" aria-label="ページネーション">
                <?php
                echo paginate_links([
                    'total' => $total_pages,
                    'current' => max(1, get_query_var('paged')),
                    'prev_text' => '← 前へ',
                    'next_text' => '次へ →',
                    'type' => 'list',
                    'mid_size' => 2,
                    'end_size' => 1
                ]);
                ?>
            </nav>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="bd-no-posts">
            <p>まだ記事が投稿されていません。</p>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
