<?php
/**
 * Single Post Template
 * ブログ個別投稿ページ
 */
get_header();

while (have_posts()): the_post();
?>

<div class="bd-blog-single-page">
    <!-- パンくずリスト -->
    <nav class="bd-breadcrumb">
        <a href="<?php echo home_url('/'); ?>">ホーム</a>
        <span class="bd-breadcrumb-separator">›</span>
        <a href="<?php echo get_permalink(get_option('page_for_posts')); ?>">美容コラム</a>
        <span class="bd-breadcrumb-separator">›</span>
        <span class="bd-breadcrumb-current"><?php the_title(); ?></span>
    </nav>

    <article class="bd-blog-single">
        <!-- 記事ヘッダー -->
        <header class="bd-blog-single-header">
            <div class="bd-blog-meta">
                <time class="bd-blog-date" datetime="<?php echo get_the_date('c'); ?>">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13 2H12V1C12 0.734784 11.8946 0.48043 11.7071 0.292893C11.5196 0.105357 11.2652 0 11 0C10.7348 0 10.4804 0.105357 10.2929 0.292893C10.1054 0.48043 10 0.734784 10 1V2H6V1C6 0.734784 5.89464 0.48043 5.70711 0.292893C5.51957 0.105357 5.26522 0 5 0C4.73478 0 4.48043 0.105357 4.29289 0.292893C4.10536 0.48043 4 0.734784 4 1V2H3C2.20435 2 1.44129 2.31607 0.87868 2.87868C0.316071 3.44129 0 4.20435 0 5V13C0 13.7956 0.316071 14.5587 0.87868 15.1213C1.44129 15.6839 2.20435 16 3 16H13C13.7956 16 14.5587 15.6839 15.1213 15.1213C15.6839 14.5587 16 13.7956 16 13V5C16 4.20435 15.6839 3.44129 15.1213 2.87868C14.5587 2.31607 13.7956 2 13 2ZM14 13C14 13.2652 13.8946 13.5196 13.7071 13.7071C13.5196 13.8946 13.2652 14 13 14H3C2.73478 14 2.48043 13.8946 2.29289 13.7071C2.10536 13.5196 2 13.2652 2 13V8H14V13ZM14 6H2V5C2 4.73478 2.10536 4.48043 2.29289 4.29289C2.48043 4.10536 2.73478 4 3 4H4V5C4 5.26522 4.10536 5.51957 4.29289 5.70711C4.48043 5.89464 4.73478 6 5 6C5.26522 6 5.51957 5.89464 5.70711 5.70711C5.89464 5.51957 6 5.26522 6 5V4H10V5C10 5.26522 10.1054 5.51957 10.2929 5.70711C10.4804 5.89464 10.7348 6 11 6C11.2652 6 11.5196 5.89464 11.7071 5.70711C11.8946 5.51957 12 5.26522 12 5V4H13C13.2652 4 13.5196 4.10536 13.7071 4.29289C13.8946 4.48043 14 4.73478 14 5V6Z" fill="currentColor"/>
                    </svg>
                    <?php echo get_the_date('Y年m月d日'); ?>
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
            
            <h1 class="bd-blog-single-title"><?php the_title(); ?></h1>
            
            <?php
            $tags = get_the_tags();
            if ($tags):
            ?>
                <div class="bd-blog-tags">
                    <?php foreach ($tags as $tag): ?>
                        <a href="<?php echo get_tag_link($tag->term_id); ?>" class="bd-blog-tag">
                            #<?php echo esc_html($tag->name); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </header>
        
        <!-- アイキャッチ画像 -->
        <?php if (has_post_thumbnail()): ?>
            <div class="bd-blog-featured-image">
                <?php the_post_thumbnail('large'); ?>
            </div>
        <?php endif; ?>
        
        <!-- 記事本文 -->
        <div class="bd-blog-single-content">
            <?php the_content(); ?>
        </div>
        
        <!-- 記事フッター -->
        <footer class="bd-blog-single-footer">
            <?php if ($tags): ?>
                <div class="bd-blog-tags-footer">
                    <span class="bd-tags-label">関連タグ:</span>
                    <?php foreach ($tags as $tag): ?>
                        <a href="<?php echo get_tag_link($tag->term_id); ?>" class="bd-blog-tag">
                            #<?php echo esc_html($tag->name); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="bd-blog-share">
                <span class="bd-share-label">この記事をシェア:</span>
                <div class="bd-share-buttons">
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="bd-share-button bd-share-twitter"
                       aria-label="Twitterでシェア">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="bd-share-button bd-share-facebook"
                       aria-label="Facebookでシェア">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                    <a href="https://line.me/R/msg/text/?<?php echo urlencode(get_the_title() . ' ' . get_permalink()); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="bd-share-button bd-share-line"
                       aria-label="LINEでシェア">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.346 0 .627.285.627.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/>
                        </svg>
                    </a>
                </div>
            </div>
        </footer>
    </article>
    
    <!-- 関連記事 -->
    <?php
    $categories = get_the_category();
    if ($categories):
        $category_ids = array();
        foreach ($categories as $category) {
            $category_ids[] = $category->term_id;
        }
        
        $related_posts = new WP_Query([
            'category__in' => $category_ids,
            'post__not_in' => [get_the_ID()],
            'posts_per_page' => 3,
            'orderby' => 'rand'
        ]);
        
        if ($related_posts->have_posts()):
    ?>
        <aside class="bd-related-posts">
            <h2 class="bd-related-posts-title">関連記事</h2>
            <div class="bd-related-posts-list">
                <?php while ($related_posts->have_posts()): $related_posts->the_post(); ?>
                    <article class="bd-related-post-card">
                        <a href="<?php the_permalink(); ?>" class="bd-related-post-link">
                            <?php if (has_post_thumbnail()): ?>
                                <div class="bd-related-post-thumb">
                                    <?php the_post_thumbnail('medium'); ?>
                                </div>
                            <?php endif; ?>
                            <div class="bd-related-post-content">
                                <time class="bd-related-post-date" datetime="<?php echo get_the_date('c'); ?>">
                                    <?php echo get_the_date('Y.m.d'); ?>
                                </time>
                                <h3 class="bd-related-post-title"><?php the_title(); ?></h3>
                            </div>
                        </a>
                    </article>
                <?php endwhile; ?>
            </div>
        </aside>
    <?php
        wp_reset_postdata();
        endif;
    endif;
    ?>
    
    <!-- 記事一覧に戻るボタン -->
    <div class="bd-back-to-archive">
        <a href="<?php echo get_permalink(get_option('page_for_posts')); ?>" class="bd-back-button">
            ← 記事一覧に戻る
        </a>
    </div>
</div>

<?php
endwhile;
get_footer();
?>
