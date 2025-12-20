<?php
/**
 * Front Page Template
 * トップページ（フロントページ）専用テンプレート
 * SEO最適化済み
 */
get_header();
?>

<div class="bd-front-page">
    <!-- ヒーローセクション -->
    <section class="bd-hero-section" itemscope itemtype="https://schema.org/WebSite">
        <div class="bd-hero-content">
            <h1 class="bd-hero-title" itemprop="name">美容クリニック検索 | 全国の美容医療情報</h1>
            <p class="bd-hero-description" itemprop="description">
                脱毛・二重整形・美肌治療など、あなたにぴったりの美容クリニックを見つけよう
            </p>
            <div class="bd-hero-keywords">
                <span class="bd-keyword-tag">脱毛</span>
                <span class="bd-keyword-tag">二重整形</span>
                <span class="bd-keyword-tag">美肌治療</span>
                <span class="bd-keyword-tag">ボトックス</span>
                <span class="bd-keyword-tag">ヒアルロン酸</span>
            </div>
        </div>
    </section>
    
    <!-- クリニック検索セクション -->
    <section class="bd-search-section" aria-label="クリニック検索">
        <div class="bd-section-container">
            <div class="bd-search-intro">
                <h2 class="bd-search-title">クリニックを検索</h2>
                <p class="bd-search-description">都道府県・施術メニュー・価格から最適なクリニックを見つけられます</p>
            </div>
            <?php echo do_shortcode('[beauty_clinic_search]'); ?>
        </div>
    </section>
    
    <!-- 人気施術セクション -->
    <section class="bd-popular-treatments-section">
        <div class="bd-section-container">
            <div class="bd-section-header">
                <h2 class="bd-section-title">人気の美容施術</h2>
                <p class="bd-section-description">多くの方が選ぶ人気の施術メニュー</p>
            </div>
            <div class="bd-popular-treatments-grid">
                <article class="bd-treatment-highlight-card">
                    <div class="bd-treatment-icon">💆</div>
                    <h3 class="bd-treatment-name">医療脱毛</h3>
                    <p class="bd-treatment-desc">永久脱毛で理想の肌へ。全身・部分脱毛に対応</p>
                    <span class="bd-treatment-price">月額3,000円〜</span>
                </article>
                
                <article class="bd-treatment-highlight-card">
                    <div class="bd-treatment-icon">👁️</div>
                    <h3 class="bd-treatment-name">二重整形</h3>
                    <p class="bd-treatment-desc">埋没法・切開法で理想の目元を実現</p>
                    <span class="bd-treatment-price">29,800円〜</span>
                </article>
                
                <article class="bd-treatment-highlight-card">
                    <div class="bd-treatment-icon">✨</div>
                    <h3 class="bd-treatment-name">美肌治療</h3>
                    <p class="bd-treatment-desc">シミ・毛穴・ニキビ跡を改善</p>
                    <span class="bd-treatment-price">9,800円〜</span>
                </article>
                
                <article class="bd-treatment-highlight-card">
                    <div class="bd-treatment-icon">💉</div>
                    <h3 class="bd-treatment-name">ボトックス注射</h3>
                    <p class="bd-treatment-desc">表情ジワを改善し若々しい印象に</p>
                    <span class="bd-treatment-price">4,980円〜</span>
                </article>
            </div>
        </div>
    </section>
    
    <!-- 施術別検索セクション -->
    <section class="bd-treatment-section" aria-label="施術別検索">
        <div class="bd-section-container">
            <div class="bd-section-header">
                <h2 class="bd-section-title">施術から探す</h2>
                <p class="bd-section-description">気になる施術からクリニックを検索</p>
            </div>
            <?php echo do_shortcode('[beauty_treatment_categories]'); ?>
        </div>
    </section>
    
    <!-- 都道府県別検索セクション -->
    <section class="bd-prefecture-section" aria-label="地域別検索">
        <div class="bd-section-container">
            <div class="bd-section-header">
                <h2 class="bd-section-title">地域から探す</h2>
                <p class="bd-section-description">お住まいの地域のクリニックを検索</p>
            </div>
            <?php echo do_shortcode('[beauty_prefecture_list]'); ?>
        </div>
    </section>
    
    <!-- 最新ブログ記事セクション -->
    <section class="bd-latest-posts-section" aria-label="美容コラム">
        <div class="bd-section-container">
            <div class="bd-section-header">
                <h2 class="bd-section-title">美容コラム</h2>
                <p class="bd-section-description">美容医療の最新情報・施術解説・クリニック選びのポイント</p>
            </div>
            
            <?php
            $latest_posts = new WP_Query([
                'post_type' => 'post',
                'posts_per_page' => 5,
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC'
            ]);
            
            if ($latest_posts->have_posts()):
            ?>
                <div class="bd-latest-posts-slider-wrapper">
                    <button class="bd-slider-nav bd-slider-prev" aria-label="前へ">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    
                    <div class="bd-latest-posts-slider">
                        <?php while ($latest_posts->have_posts()): $latest_posts->the_post(); ?>
                            <article class="bd-post-slide-card" itemscope itemtype="https://schema.org/BlogPosting">
                                <a href="<?php the_permalink(); ?>" class="bd-post-slide-link" itemprop="url">
                                    <div class="bd-post-slide-content">
                                        <div class="bd-post-meta">
                                            <time class="bd-post-date" datetime="<?php echo get_the_date('c'); ?>" itemprop="datePublished">
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
                                        
                                        <h3 class="bd-post-slide-title" itemprop="headline"><?php the_title(); ?></h3>
                                    </div>
                                </a>
                            </article>
                        <?php endwhile; ?>
                    </div>
                    
                    <button class="bd-slider-nav bd-slider-next" aria-label="次へ">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const slider = document.querySelector('.bd-latest-posts-slider');
                    const prevBtn = document.querySelector('.bd-slider-prev');
                    const nextBtn = document.querySelector('.bd-slider-next');
                    
                    if (slider && prevBtn && nextBtn) {
                        const cardWidth = 280;
                        
                        nextBtn.addEventListener('click', function() {
                            slider.scrollBy({ left: cardWidth, behavior: 'smooth' });
                        });
                        
                        prevBtn.addEventListener('click', function() {
                            slider.scrollBy({ left: -cardWidth, behavior: 'smooth' });
                        });
                    }
                });
                </script>
                
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
    
    <!-- クリニック選びのポイントセクション -->
    <section class="bd-tips-section">
        <div class="bd-section-container">
            <div class="bd-section-header">
                <h2 class="bd-section-title">美容クリニックの選び方</h2>
                <p class="bd-section-description">失敗しないクリニック選びの3つのポイント</p>
            </div>
            
            <div class="bd-tips-grid">
                <article class="bd-tip-card">
                    <div class="bd-tip-number">01</div>
                    <h3 class="bd-tip-title">実績と口コミを確認</h3>
                    <p class="bd-tip-text">
                        施術実績が豊富で、実際の患者さんの口コミ評価が高いクリニックを選びましょう。
                        症例写真やビフォーアフターも重要な判断材料です。
                    </p>
                </article>
                
                <article class="bd-tip-card">
                    <div class="bd-tip-number">02</div>
                    <h3 class="bd-tip-title">料金の透明性</h3>
                    <p class="bd-tip-text">
                        明確な料金表示があり、追加費用の説明がしっかりしているクリニックが安心です。
                        無料カウンセリングで詳しく確認しましょう。
                    </p>
                </article>
                
                <article class="bd-tip-card">
                    <div class="bd-tip-number">03</div>
                    <h3 class="bd-tip-title">アフターケア体制</h3>
                    <p class="bd-tip-text">
                        施術後のフォロー体制が整っているかも重要なポイント。
                        万が一のトラブル時の対応や保証制度を確認しましょう。
                    </p>
                </article>
            </div>
        </div>
    </section>
    
    <!-- サイト説明セクション（SEO強化） -->
    <section class="bd-about-section" itemscope itemtype="https://schema.org/WebApplication">
        <div class="bd-section-container">
            <div class="bd-about-content">
                <h2 class="bd-about-title" itemprop="name">美容クリニックラボとは</h2>
                <div class="bd-about-text" itemprop="description">
                    <p>
                        美容クリニックラボは、<strong>全国の美容クリニック情報を検索できる総合ポータルサイト</strong>です。
                        医療脱毛、二重整形、美肌治療、痩身、ボトックス、ヒアルロン酸注射など、様々な美容医療の施術内容や料金、口コミ情報を掲載しています。
                    </p>
                    <p>
                        都道府県別・施術別の検索機能により、あなたにぴったりの美容クリニックを簡単に見つけることができます。
                        最新の美容医療情報や施術解説コラムも定期的に更新中です。
                    </p>
                </div>
                
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
    
    <!-- FAQ セクション（SEO強化） -->
    <section class="bd-faq-section" itemscope itemtype="https://schema.org/FAQPage">
        <div class="bd-section-container">
            <div class="bd-section-header">
                <h2 class="bd-section-title">よくある質問</h2>
                <p class="bd-section-description">美容クリニック選びでよくある疑問にお答えします</p>
            </div>
            
            <div class="bd-faq-list">
                <article class="bd-faq-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                    <h3 class="bd-faq-question" itemprop="name">美容クリニックの選び方は？</h3>
                    <div class="bd-faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                        <p itemprop="text">
                            実績・口コミ・料金の透明性・アフターケア体制を確認しましょう。無料カウンセリングで医師の説明を聞き、納得してから施術を受けることが大切です。
                        </p>
                    </div>
                </article>
                
                <article class="bd-faq-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                    <h3 class="bd-faq-question" itemprop="name">美容医療の費用相場は？</h3>
                    <div class="bd-faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                        <p itemprop="text">
                            施術内容により異なりますが、医療脱毛は月額3,000円〜、二重整形は29,800円〜、ボトックス注射は4,980円〜が一般的な相場です。
                        </p>
                    </div>
                </article>
                
                <article class="bd-faq-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                    <h3 class="bd-faq-question" itemprop="name">カウンセリングは無料ですか？</h3>
                    <div class="bd-faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                        <p itemprop="text">
                            多くのクリニックで無料カウンセリングを実施しています。複数のクリニックでカウンセリングを受けて比較検討することをおすすめします。
                        </p>
                    </div>
                </article>
            </div>
        </div>
    </section>
</div>

<?php get_footer(); ?>
