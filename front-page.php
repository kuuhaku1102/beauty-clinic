<?php
/**
 * Front Page Template
 * トップページ（フロントページ）専用テンプレート
 */
get_header();
?>

<div class="bd-front-page">
    <!-- ヒーローセクション -->
    <section class="bd-hero-section">
        <div class="bd-hero-content">
            <h1 class="bd-hero-title">美容クリニック検索</h1>
            <p class="bd-hero-description">あなたにぴったりの美容クリニックを見つけよう</p>
            <p class="bd-hero-subtitle">全国の美容クリニック情報を検索できます</p>
        </div>
    </section>
    
    <!-- クリニック検索セクション -->
    <section class="bd-search-section">
        <div class="bd-section-container">
            <?php echo do_shortcode('[beauty_clinic_search]'); ?>
        </div>
    </section>
    
    <!-- 施術別検索セクション -->
    <section class="bd-treatment-section">
        <div class="bd-section-container">
            <div class="bd-section-header">
                <h2 class="bd-section-title">施術から探す</h2>
                <p class="bd-section-description">人気の美容施術からクリニックを検索</p>
            </div>
            <?php echo do_shortcode('[beauty_treatment_categories]'); ?>
        </div>
    </section>
    
    <!-- 都道府県別検索セクション -->
    <section class="bd-prefecture-section">
        <div class="bd-section-container">
            <div class="bd-section-header">
                <h2 class="bd-section-title">地域から探す</h2>
                <p class="bd-section-description">お住まいの地域のクリニックを検索</p>
            </div>
            <?php echo do_shortcode('[beauty_prefecture_list]'); ?>
        </div>
    </section>
    
    <!-- 最新ブログ記事セクション -->
    <section class="bd-latest-posts-section">
        <div class="bd-section-container">
            <div class="bd-section-header">
                <h2 class="bd-section-title">美容コラム</h2>
                <p class="bd-section-description">美容医療に関する最新情報</p>
            </div>
            
            <?php
            $latest_posts = new WP_Query([
                'post_type' => 'post',
                'posts_per_page' => 3,
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC'
            ]);
            
            if ($latest_posts->have_posts()):
            ?>
                <div class="bd-latest-posts-grid">
                    <?php while ($latest_posts->have_posts()): $latest_posts->the_post(); ?>
                        <article class="bd-post-card">
                            <a href="<?php the_permalink(); ?>" class="bd-post-card-link">
                                <?php if (has_post_thumbnail()): ?>
                                    <div class="bd-post-thumb">
                                        <?php the_post_thumbnail('medium'); ?>
                                    </div>
                                <?php else: ?>
                                    <div class="bd-post-thumb bd-post-thumb-placeholder">
                                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M19 3H5C3.89543 3 3 3.89543 3 5V19C3 20.1046 3.89543 21 5 21H19C20.1046 21 21 20.1046 21 19V5C21 3.89543 20.1046 3 19 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M8.5 10C9.32843 10 10 9.32843 10 8.5C10 7.67157 9.32843 7 8.5 7C7.67157 7 7 7.67157 7 8.5C7 9.32843 7.67157 10 8.5 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M21 15L16 10L5 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="bd-post-content">
                                    <div class="bd-post-meta">
                                        <time class="bd-post-date" datetime="<?php echo get_the_date('c'); ?>">
                                            <?php echo get_the_date('Y.m.d'); ?>
                                        </time>
                                        <?php
                                        $categories = get_the_category();
                                        if ($categories):
                                            $category = $categories[0];
                                        ?>
                                            <span class="bd-post-category"><?php echo esc_html($category->name); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <h3 class="bd-post-title"><?php the_title(); ?></h3>
                                    
                                    <div class="bd-post-excerpt">
                                        <?php echo wp_trim_words(get_the_excerpt(), 60, '...'); ?>
                                    </div>
                                </div>
                            </a>
                        </article>
                    <?php endwhile; ?>
                </div>
                
                <div class="bd-view-all-posts">
                    <a href="<?php echo esc_url(home_url('/blog/')); ?>" class="bd-view-all-button">
                        すべての記事を見る
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            <?php
                wp_reset_postdata();
            else:
            ?>
                <div class="bd-no-posts-message">
                    <p>まだ記事が投稿されていません。</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- サイト説明セクション -->
    <section class="bd-about-section">
        <div class="bd-section-container">
            <div class="bd-about-content">
                <h2 class="bd-about-title">美容クリニックラボとは</h2>
                <p class="bd-about-text">
                    美容クリニックラボは、全国の美容クリニック情報を検索できる総合ポータルサイトです。
                    脱毛、二重整形、美肌治療、痩身など、様々な美容医療の施術内容や料金、口コミ情報を掲載しています。
                    あなたにぴったりの美容クリニックを見つけて、理想の自分を手に入れましょう。
                </p>
                <div class="bd-about-features">
                    <div class="bd-feature-item">
                        <div class="bd-feature-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3 class="bd-feature-title">簡単検索</h3>
                        <p class="bd-feature-text">施術内容や地域から簡単にクリニックを検索できます</p>
                    </div>
                    
                    <div class="bd-feature-item">
                        <div class="bd-feature-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3 class="bd-feature-title">詳細情報</h3>
                        <p class="bd-feature-text">料金、営業時間、口コミなど詳しい情報を掲載</p>
                    </div>
                    
                    <div class="bd-feature-item">
                        <div class="bd-feature-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3 class="bd-feature-title">最新情報</h3>
                        <p class="bd-feature-text">美容医療に関する最新コラムを定期更新</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php get_footer(); ?>
