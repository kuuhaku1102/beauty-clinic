<?php
/**
 * Single Template
 * 詳細ページ用テンプレート
 */
get_header();

// クリニック詳細ページの場合
$clinic_id = get_query_var('clinic_id');
if ($clinic_id) {
    $clinic = bd_get_clinic($clinic_id);
    if ($clinic) {
        $menus = bd_get_clinic_menus($clinic_id);
        $hours = bd_get_clinic_hours($clinic_id);
        ?>
        <div class="bd-clinic-detail-page">
            <!-- パンくずリスト -->
            <nav class="bd-breadcrumb">
                <a href="<?php echo home_url('/'); ?>">ホーム</a>
                <span class="bd-breadcrumb-separator">›</span>
                <span class="bd-breadcrumb-current"><?php echo esc_html($clinic->name); ?></span>
            </nav>

            <article class="bd-clinic-detail">
                <!-- ヘッダーセクション -->
                <div class="bd-clinic-header">
                    <div class="bd-clinic-header-content">
                        <h1 class="bd-clinic-name"><?php echo esc_html($clinic->name); ?></h1>
                        
                        <div class="bd-clinic-rating-area">
                            <?php if (!empty($clinic->rating)): ?>
                                <div class="bd-rating-display">
                                    <span class="bd-rating-stars">
                                        <?php
                                        $rating = floatval($clinic->rating);
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $rating) {
                                                echo '★';
                                            } elseif ($i - 0.5 <= $rating) {
                                                echo '☆';
                                            } else {
                                                echo '☆';
                                            }
                                        }
                                        ?>
                                    </span>
                                    <span class="bd-rating-number"><?php echo esc_html($rating); ?></span>
                                    <span class="bd-reviews-count">(<?php echo esc_html($clinic->reviews_count); ?>件の口コミ)</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="bd-clinic-location">
                            <?php if ($clinic->prefecture || $clinic->city): ?>
                                <div class="bd-location-item">
                                    <span class="bd-icon">📍</span>
                                    <span><?php echo esc_html($clinic->prefecture . ' ' . $clinic->city); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($clinic->station)): ?>
                                <div class="bd-location-item">
                                    <span class="bd-icon">🚉</span>
                                    <span><?php echo esc_html($clinic->station); ?>駅周辺</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($clinic->address)): ?>
                            <div class="bd-clinic-address">
                                <strong>住所:</strong> <?php echo esc_html($clinic->address); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($clinic->phone)): ?>
                            <div class="bd-clinic-phone">
                                <strong>電話:</strong> <a href="tel:<?php echo esc_attr(str_replace(['-', ' '], '', $clinic->phone)); ?>"><?php echo esc_html($clinic->phone); ?></a>
                            </div>
                        <?php endif; ?>

                        <?php 
                        $clinic_url = bd_filter_external_url($clinic->clinic_url);
                        if (!empty($clinic_url)): 
                        ?>
                            <div class="bd-clinic-actions">
                                <a class="bd-btn bd-btn-primary" href="<?php echo esc_url($clinic_url); ?>" target="_blank" rel="noopener">
                                    公式サイトを見る
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="bd-clinic-header-image">
                        <?php
                        global $wpdb;
                        $t = bd_tables();
                        
                        // clinic.image_url があればそれを使用、なければメニュー画像から取得
                        $image_url = !empty($clinic->image_url) ? $clinic->image_url : '';
                        if (empty($image_url)) {
                            $image_url = $wpdb->get_var($wpdb->prepare("
                                SELECT menu_img FROM {$t['menus']}
                                WHERE clinic_id = %d AND menu_img <> ''
                                ORDER BY id ASC LIMIT 1
                            ", $clinic_id));
                        }
                        
                        // 他サイトの画像を除外
                        $image_url = bd_filter_external_url($image_url);
                        
                        if ($image_url): ?>
                            <div class="bd-detail-thumb">
                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($clinic->name); ?>">
                            </div>
                        <?php else: ?>
                            <div class="bd-detail-thumb">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/no-image-placeholder.png" alt="No Image">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 営業時間セクション -->
                <?php if ($hours): ?>
                    <section class="bd-detail-section">
                        <h2 class="bd-section-title">
                            <span class="bd-section-icon">🕐</span>
                            営業時間
                        </h2>
                        <div class="bd-hours-table">
                            <?php foreach ($hours as $h): ?>
                                <div class="bd-hours-row">
                                    <div class="bd-hours-day"><?php echo esc_html($h->day); ?></div>
                                    <div class="bd-hours-time"><?php echo esc_html($h->raw); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- 施術メニューセクション -->
                <?php if ($menus): ?>
                    <section class="bd-detail-section">
                        <h2 class="bd-section-title">
                            <span class="bd-section-icon">💆</span>
                            施術メニュー・料金
                        </h2>
                        
                        <?php
                        // カテゴリー別にメニューをグループ化
                        $menu_by_category = [];
                        foreach ($menus as $m) {
                            $category = !empty($m->category_raw) ? $m->category_raw : 'その他';
                            if (!isset($menu_by_category[$category])) {
                                $menu_by_category[$category] = [];
                            }
                            $menu_by_category[$category][] = $m;
                        }
                        ?>

                        <?php foreach ($menu_by_category as $category => $category_menus): ?>
                            <div class="bd-menu-category">
                                <h3 class="bd-menu-category-title"><?php echo esc_html($category); ?></h3>
                                <div class="bd-menu-grid">
                                    <?php foreach ($category_menus as $m): 
                                        $menu_img = bd_filter_external_url($m->menu_img);
                                    ?>
                                        <div class="bd-menu-item">
                                            <?php if (!empty($menu_img)): ?>
                                                <div class="bd-menu-item-image">
                                                    <img src="<?php echo esc_url($menu_img); ?>" alt="<?php echo esc_attr($m->menu_title); ?>">
                                                    <?php if ($m->pickup_flag): ?>
                                                        <span class="bd-menu-pickup-badge">おすすめ</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="bd-menu-item-content">
                                                <h4 class="bd-menu-item-title"><?php echo esc_html($m->menu_title); ?></h4>
                                                <?php if ($m->price_jpy !== ''): ?>
                                                    <div class="bd-menu-item-price">
                                                        <?php echo number_format(floatval($m->price_jpy)); ?>円〜
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </section>
                <?php endif; ?>

                <!-- アクセス情報セクション -->
                <?php if (!empty($clinic->address)): ?>
                    <section class="bd-detail-section">
                        <h2 class="bd-section-title">
                            <span class="bd-section-icon">📍</span>
                            アクセス
                        </h2>
                        <div class="bd-access-info">
                            <p><strong>住所:</strong> <?php echo esc_html($clinic->address); ?></p>
                            <?php if (!empty($clinic->station)): ?>
                                <p><strong>最寄り駅:</strong> <?php echo esc_html($clinic->station); ?>駅</p>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- CTAセクション -->
                <section class="bd-detail-cta">
                    <div class="bd-cta-content">
                        <h3 class="bd-cta-title">ご予約・お問い合わせ</h3>
                        <p class="bd-cta-text">詳しい情報やご予約は公式サイトをご確認ください</p>
                        <div class="bd-cta-buttons">
                            <?php 
                            $clinic_url = bd_filter_external_url($clinic->clinic_url);
                            if (!empty($clinic_url)): 
                            ?>
                                <a class="bd-btn bd-btn-primary bd-btn-large" href="<?php echo esc_url($clinic_url); ?>" target="_blank" rel="noopener">
                                    公式サイトで予約する
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($clinic->phone)): ?>
                                <a class="bd-btn bd-btn-secondary bd-btn-large" href="tel:<?php echo esc_attr(str_replace(['-', ' '], '', $clinic->phone)); ?>">
                                    電話で問い合わせる
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <!-- 一覧に戻るリンク -->
                <div class="bd-back-to-list">
                    <a href="<?php echo home_url('/'); ?>" class="bd-back-link">« クリニック一覧に戻る</a>
                </div>
            </article>
        </div>
        <?php
    } else {
        echo '<div class="bd-error-message"><p>クリニック情報が見つかりませんでした。</p></div>';
    }
} else {
    // 通常の投稿詳細ページ
    ?>
    <div class="bd-main-content">
        <?php while (have_posts()): the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('bd-single-post'); ?>>
                <header class="post-header">
                    <h1 class="post-title"><?php the_title(); ?></h1>
                    <div class="post-meta">
                        <time datetime="<?php echo get_the_date('c'); ?>">
                            <?php echo get_the_date(); ?>
                        </time>
                        <?php
                        $categories = get_the_category();
                        if ($categories) {
                            echo '<span class="post-category">' . esc_html($categories[0]->name) . '</span>';
                        }
                        ?>
                    </div>
                </header>

                <?php if (has_post_thumbnail()): ?>
                    <div class="post-thumbnail">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>

                <div class="post-content">
                    <?php the_content(); ?>
                </div>

                <?php
                wp_link_pages([
                    'before' => '<div class="page-links">ページ: ',
                    'after' => '</div>',
                ]);
                ?>
            </article>

            <?php
            if (comments_open() || get_comments_number()) {
                comments_template();
            }
            ?>
        <?php endwhile; ?>
    </div>
    <?php get_sidebar(); ?>
    <?php
}

get_footer();
?>
