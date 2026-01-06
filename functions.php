<?php
if (!defined('ABSPATH')) exit;

/**
 * Beauty Directory Theme Functions
 */

/**
 * テーマセットアップ
 */
function bd_theme_setup() {
    // タイトルタグサポート
    add_theme_support('title-tag');
    
    // アイキャッチ画像サポート
    add_theme_support('post-thumbnails');
    set_post_thumbnail_size(800, 600, true);
    add_image_size('bd-thumb', 400, 300, true);
    
    // HTML5サポート
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ]);
    
    // 自動フィードリンク
    add_theme_support('automatic-feed-links');
    
    // カスタムロゴ
    add_theme_support('custom-logo', [
        'height' => 100,
        'width' => 400,
        'flex-height' => true,
        'flex-width' => true,
    ]);
    
    // ナビゲーションメニュー
    register_nav_menus([
        'primary' => 'メインメニュー',
        'footer-menu' => 'フッターメニュー',
    ]);
}
add_action('after_setup_theme', 'bd_theme_setup');

/**
 * ブログ設定
 */
function bd_blog_setup() {
    // ブログページの投稿数を8件に設定
    if (is_home() || is_archive() || is_category() || is_tag()) {
        set_query_var('posts_per_page', 8);
    }
}
add_action('pre_get_posts', 'bd_blog_posts_per_page');

function bd_blog_posts_per_page($query) {
    if (!is_admin() && $query->is_main_query()) {
        if (is_home() || is_archive() || is_category() || is_tag()) {
            $query->set('posts_per_page', 8);
        }
    }
}

/**
 * ウィジェットエリア登録
 */
function bd_widgets_init() {
    register_sidebar([
        'name' => 'サイドバー',
        'id' => 'sidebar-1',
        'description' => 'サイドバーウィジェットエリア',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ]);
    
    register_sidebar([
        'name' => 'フッター1',
        'id' => 'footer-1',
        'description' => 'フッターウィジェットエリア1',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ]);
    
    register_sidebar([
        'name' => 'フッター2',
        'id' => 'footer-2',
        'description' => 'フッターウィジェットエリア2',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ]);
    
    register_sidebar([
        'name' => 'フッター3',
        'id' => 'footer-3',
        'description' => 'フッターウィジェットエリア3',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ]);
}
add_action('widgets_init', 'bd_widgets_init');

/**
 * スタイルとスクリプトの読み込み
 */
function bd_enqueue_assets() {
    wp_enqueue_style('beauty-directory-style', get_stylesheet_uri(), [], '1.1.0');
    wp_enqueue_script('bd-accordion', get_template_directory_uri() . '/assets/js/accordion.js', [], '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'bd_enqueue_assets');

/**
 * 抜粋文字数の変更
 */
function bd_excerpt_length($length) {
    return 100;
}
add_filter('excerpt_length', 'bd_excerpt_length');

/**
 * 抜粋の省略記号を変更
 */
function bd_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'bd_excerpt_more');

// クリニック投稿タイプ(必要なら後で拡張)
function bd_register_clinic_post_type() {
    register_post_type('clinic', [
        'label' => 'クリニック',
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'clinic'],
        'supports' => ['title', 'editor', 'thumbnail'],
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-heart',
    ]);
}
add_action('init', 'bd_register_clinic_post_type');

// DB helper
function bd_tables() {
    global $wpdb;
    return [
        'clinics' => $wpdb->prefix . 'beauty___clinics',
        'menus'   => $wpdb->prefix . 'beauty___menus',
        'hours'   => $wpdb->prefix . 'beauty___hours',
    ];
}

function bd_get_clinic($clinic_id) {
    global $wpdb;
    $t = bd_tables();
    return $wpdb->get_row($wpdb->prepare("
        SELECT *
        FROM {$t['clinics']}
        WHERE clinic_id = %d
    ", $clinic_id));
}

function bd_get_clinic_menus($clinic_id) {
    global $wpdb;
    $t = bd_tables();
    return $wpdb->get_results($wpdb->prepare("
        SELECT *
        FROM {$t['menus']}
        WHERE clinic_id = %d
        ORDER BY price_jpy+0 ASC
    ", $clinic_id));
}

/**
 * 他サイトのURLをフィルタリング(除外)する
 */
function bd_filter_external_url($url) {
    if (empty($url)) {
        return '';
    }
    
    // 除外するドメインリスト
    $blocked_domains = [
        'kireireport.com',
    ];
    
    foreach ($blocked_domains as $domain) {
        if (strpos($url, $domain) !== false) {
            return ''; // ブロックされたドメインの場合は空文字列を返す
        }
    }
    
    return $url;
}

function bd_get_clinic_hours($clinic_id) {
    global $wpdb;
    $t = bd_tables();
    
    // 文字化けを除外し、重複を除去して営業時間を取得
    $hours = $wpdb->get_results($wpdb->prepare("
        SELECT day, open_time, close_time, raw
        FROM {$t['hours']}
        WHERE clinic_id = %s
        AND day IN ('月','火','水','木','金','土','日')
        GROUP BY day, open_time, close_time, raw
        ORDER BY FIELD(day,'月','火','水','木','金','土','日')
    ", $clinic_id));
    
    // さらに同じ曜日の重複を除去(最初の1件のみ)
    $unique_hours = [];
    $seen_days = [];
    
    foreach ($hours as $h) {
        if (!in_array($h->day, $seen_days)) {
            $unique_hours[] = $h;
            $seen_days[] = $h->day;
        }
    }
    
    return $unique_hours;
}

/**
 * 絞り込み付き クリニック一覧ショートコード (ページネーション対応)
 * [beauty_clinic_search]
 */
function bd_shortcode_clinic_search($atts = []) {
    global $wpdb;
    $t = bd_tables();

    // 検索パラメータ
    $prefecture = isset($_GET['bd_pref']) ? sanitize_text_field($_GET['bd_pref']) : '';
    $keyword    = isset($_GET['bd_kw']) ? sanitize_text_field($_GET['bd_kw']) : '';
    $min_price  = isset($_GET['bd_min']) ? floatval($_GET['bd_min']) : 0;
    $max_price  = isset($_GET['bd_max']) ? floatval($_GET['bd_max']) : 0;

    // ページネーション設定
    $per_page = 12; // 1ページあたりの表示件数
    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($paged - 1) * $per_page;

    // 都道府県リスト
    $prefectures = $wpdb->get_col("SELECT DISTINCT prefecture FROM {$t['clinics']} WHERE prefecture <> '' ORDER BY prefecture ASC");

    // ベースクエリ
    $where = ["1=1"];
    $join  = "INNER JOIN {$t['menus']} m ON c.clinic_id = m.clinic_id"; // メニューがあるクリニックのみ
    $params = [];

    if ($prefecture !== '') {
        $where[] = "c.prefecture = %s";
        $params[] = $prefecture;
    }

    if ($keyword !== '') {
        $where[] = "m.menu_title LIKE %s";
        $params[] = '%' . $wpdb->esc_like($keyword) . '%';
    }

    if ($min_price > 0) {
        $where[] = "m.price_jpy+0 >= %f";
        $params[] = $min_price;
    }

    if ($max_price > 0) {
        $where[] = "m.price_jpy+0 <= %f";
        $params[] = $max_price;
    }

    // 総件数を取得
    $count_sql = "SELECT COUNT(DISTINCT c.clinic_id)
        FROM {$t['clinics']} c
        {$join}
        WHERE " . implode(' AND ', $where);
    $total_items = $wpdb->get_var($wpdb->prepare($count_sql, $params));
    $total_pages = ceil($total_items / $per_page);

    // データ取得 (LIMIT/OFFSET付き)
    $sql = "SELECT c.*, 
        (SELECT menu_img FROM {$t['menus']} mm WHERE mm.clinic_id = c.clinic_id AND mm.menu_img <> '' LIMIT 1) AS first_image,
        (SELECT MIN(price_jpy+0) FROM {$t['menus']} mm2 WHERE mm2.clinic_id = c.clinic_id) AS min_price
        FROM {$t['clinics']} c
        {$join}
        WHERE " . implode(' AND ', $where) . "
        GROUP BY c.clinic_id
        ORDER BY c.prefecture, c.city, c.name
        LIMIT %d OFFSET %d";

    $params[] = $per_page;
    $params[] = $offset;
    $prepared = $wpdb->prepare($sql, $params);
    $clinics = $wpdb->get_results($prepared);

    ob_start();
    ?>
    <form method="get" class="bd-filter">
      <div class="bd-filter-row">
        <label>エリア
          <select name="bd_pref">
            <option value="">すべてのエリア</option>
            <?php foreach ($prefectures as $pref): ?>
              <option value="<?php echo esc_attr($pref); ?>" <?php selected($prefecture, $pref); ?>>
                <?php echo esc_html($pref); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>

        <label>施術カテゴリー
          <select name="bd_kw">
            <option value="">すべての施術</option>
            <option value="ボトックス" <?php selected($keyword, 'ボトックス'); ?>>ボトックス</option>
            <option value="ヒアルロン酸" <?php selected($keyword, 'ヒアルロン酸'); ?>>ヒアルロン酸</option>
            <option value="ジュベルック" <?php selected($keyword, 'ジュベルック'); ?>>ジュベルック</option>
            <option value="スネコス" <?php selected($keyword, 'スネコス'); ?>>スネコス</option>
            <option value="プラセンタ" <?php selected($keyword, 'プラセンタ'); ?>>プラセンタ注射</option>
            <option value="白玉" <?php selected($keyword, '白玉'); ?>>白玉点滴</option>
            <option value="ピコ" <?php selected($keyword, 'ピコ'); ?>>ピコレーザー</option>
            <option value="シミ" <?php selected($keyword, 'シミ'); ?>>シミ取りレーザー</option>
            <option value="フォトフェイシャル" <?php selected($keyword, 'フォトフェイシャル'); ?>>フォトフェイシャル</option>
            <option value="ライムライト" <?php selected($keyword, 'ライムライト'); ?>>ライムライト</option>
            <option value="ダーマペン" <?php selected($keyword, 'ダーマペン'); ?>>ダーマペン</option>
            <option value="ピーリング" <?php selected($keyword, 'ピーリング'); ?>>ケミカルピーリング</option>
            <option value="ポテンツァ" <?php selected($keyword, 'ポテンツァ'); ?>>ポテンツァ</option>
            <option value="脱毛" <?php selected($keyword, '脱毛'); ?>>医療脱毛</option>
            <option value="二重" <?php selected($keyword, '二重'); ?>>二重整形</option>
            <option value="埋没" <?php selected($keyword, '埋没'); ?>>埋没法</option>
            <option value="切開" <?php selected($keyword, '切開'); ?>>切開法</option>
            <option value="眼瞼下垂" <?php selected($keyword, '眼瞼下垂'); ?>>眼瞼下垂</option>
            <option value="HIFU" <?php selected($keyword, 'HIFU'); ?>>HIFU(ハイフ)</option>
            <option value="糸リフト" <?php selected($keyword, '糸リフト'); ?>>糸リフト</option>
            <option value="脂肪吸引" <?php selected($keyword, '脂肪吸引'); ?>>脂肪吸引</option>
          </select>
        </label>

        <label>最低価格
          <input type="number" name="bd_min" value="<?php echo $min_price ? esc_attr($min_price) : ''; ?>" step="1000" min="0">
        </label>

        <label>最高価格
          <input type="number" name="bd_max" value="<?php echo $max_price ? esc_attr($max_price) : ''; ?>" step="1000" min="0">
        </label>
      </div>
      <div class="bd-filter-actions">
        <button type="submit" class="bd-btn">検索する</button>
      </div>
    </form>

    <div class="bd-results-info">
      <p><?php echo number_format($total_items); ?>件のクリニックが見つかりました (<?php echo $paged; ?> / <?php echo $total_pages; ?>ページ)</p>
    </div>

    <div class="bd-clinic-list">
    <?php foreach ($clinics as $c): 
        $img = bd_filter_external_url($c->first_image ?: '');
        $rating = $c->rating;
        $reviews = $c->reviews_count;
        $detail_url = home_url('/clinic/' . intval($c->clinic_id));
    ?>
      <article class="bd-clinic-card">
        <div class="bd-card-thumb">
          <?php if ($img): ?>
            <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($c->name); ?>">
          <?php else: ?>
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/no-image-placeholder.png" alt="No Image">
          <?php endif; ?>
        </div>
        <div class="bd-card-header">
          <?php if ($c->prefecture): ?>
            <span class="bd-card-pref"><?php echo esc_html($c->prefecture); ?></span>
          <?php endif; ?>
        </div>
        <div class="bd-card-body">
          <h2 class="bd-card-title">
            <a href="<?php echo esc_url($detail_url); ?>"><?php echo esc_html($c->name); ?></a>
          </h2>
          <div class="bd-card-meta">
            <?php if ($c->city): ?>
              <span><?php echo esc_html($c->city); ?></span>
            <?php endif; ?>
            <?php if ($rating): ?>
              <span class="bd-rating">★ <?php echo esc_html($rating); ?></span>
            <?php endif; ?>
            <?php if ($reviews): ?>
              <span class="bd-chip"><?php echo esc_html($reviews); ?>件口コミ</span>
            <?php endif; ?>
            <?php if ($c->station): ?>
              <span><?php echo esc_html($c->station); ?>駅周辺</span>
            <?php endif; ?>
          </div>
        </div>
        <div class="bd-card-menus">
          <?php
          $menus = $wpdb->get_results($wpdb->prepare("
              SELECT menu_title, price_jpy
              FROM {$t['menus']}
              WHERE clinic_id = %d
              ORDER BY price_jpy+0 ASC
              LIMIT 3
          ", $c->clinic_id));
          foreach ($menus as $m): ?>
            <div class="bd-card-menu-item">
              <span class="bd-card-menu-title"><?php echo esc_html($m->menu_title); ?></span>
              <?php if ($m->price_jpy !== ''): ?>
                <span class="bd-card-menu-price">
                  <?php echo number_format(floatval($m->price_jpy)); ?>円〜
                </span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
          <div style="margin-top:6px; text-align:right;">
            <a href="<?php echo esc_url($detail_url); ?>" style="font-size:12px;">詳細を見る »</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
    </div>

    <?php
    // ページネーション表示
    if ($total_pages > 1) {
        echo '<div class="bd-pagination">';
        echo bd_pagination_links($paged, $total_pages, $_GET);
        echo '</div>';
    }
    ?>
    <?php
    return ob_get_clean();
}
add_shortcode('beauty_clinic_search', 'bd_shortcode_clinic_search');

/**
 * ページネーションリンク生成関数
 */
function bd_pagination_links($current_page, $total_pages, $query_params = []) {
    $output = '';
    $range = 2; // 現在ページの前後に表示するページ数

    // 検索パラメータを保持
    $base_params = $query_params;
    unset($base_params['paged']); // pagedは個別に設定

    // 前へリンク
    if ($current_page > 1) {
        $prev_params = array_merge($base_params, ['paged' => $current_page - 1]);
        $output .= '<a href="?' . http_build_query($prev_params) . '" class="bd-page-link bd-page-prev">« 前へ</a>';
    }

    // ページ番号リンク
    for ($i = 1; $i <= $total_pages; $i++) {
        // 最初、最後、現在ページ周辺のみ表示
        if ($i == 1 || $i == $total_pages || ($i >= $current_page - $range && $i <= $current_page + $range)) {
            if ($i == $current_page) {
                $output .= '<span class="bd-page-link bd-page-current">' . $i . '</span>';
            } else {
                $page_params = array_merge($base_params, ['paged' => $i]);
                $output .= '<a href="?' . http_build_query($page_params) . '" class="bd-page-link">' . $i . '</a>';
            }
        } elseif ($i == $current_page - $range - 1 || $i == $current_page + $range + 1) {
            $output .= '<span class="bd-page-dots">...</span>';
        }
    }

    // 次へリンク
    if ($current_page < $total_pages) {
        $next_params = array_merge($base_params, ['paged' => $current_page + 1]);
        $output .= '<a href="?' . http_build_query($next_params) . '" class="bd-page-link bd-page-next">次へ »</a>';
    }

    return $output;
}

/**
 * クリニック詳細ショートコード
 * [beauty_clinic_detail clinic_id="1"]
 */
function bd_shortcode_clinic_detail($atts) {
    $atts = shortcode_atts([
        'clinic_id' => 0,
    ], $atts, 'beauty_clinic_detail');

    $clinic_id = intval($atts['clinic_id']);
    if (!$clinic_id) return '';

    $clinic = bd_get_clinic($clinic_id);
    if (!$clinic) return '<p>クリニック情報が見つかりませんでした。</p>';

    $menus = bd_get_clinic_menus($clinic_id);
    $hours = bd_get_clinic_hours($clinic_id);

    ob_start();
    ?>
    <article class="bd-clinic-detail">
      <div class="bd-clinic-header">
        <div>
          <h1><?php echo esc_html($clinic->name); ?></h1>
          <div class="bd-detail-meta">
            <?php if ($clinic->prefecture || $clinic->city): ?>
              <div><?php echo esc_html($clinic->prefecture . ' ' . $clinic->city); ?></div>
            <?php endif; ?>
            <?php if (!empty($clinic->address)): ?>
              <div><?php echo esc_html($clinic->address); ?></div>
            <?php endif; ?>
            <?php if (!empty($clinic->phone)): ?>
              <div>TEL: <?php echo esc_html($clinic->phone); ?></div>
            <?php endif; ?>
            <?php if (!empty($clinic->rating)): ?>
              <div>評価:★ <?php echo esc_html($clinic->rating); ?> / 口コミ <?php echo esc_html($clinic->reviews_count); ?>件</div>
            <?php endif; ?>
          </div>
        </div>
        <div>
          <?php
          global $wpdb;
          $t = bd_tables();
          $thumb = $wpdb->get_var($wpdb->prepare("
              SELECT menu_img FROM {$t['menus']}
              WHERE clinic_id = %d AND menu_img <> ''
              ORDER BY id ASC LIMIT 1
          ", $clinic_id));
          $thumb = bd_filter_external_url($thumb);
          if ($thumb): ?>
            <div class="bd-detail-thumb">
              <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($clinic->name); ?>">
            </div>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($hours): ?>
        <h2 class="bd-section-title">営業時間</h2>
        <ul class="bd-hours-list">
          <?php foreach ($hours as $h): ?>
            <li><?php echo esc_html($h->day . ':' . $h->raw); ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <?php if ($menus): ?>
        <h2 class="bd-section-title">施術メニュー</h2>
        <ul class="bd-menus-list">
          <?php foreach ($menus as $m): ?>
            <li>
              <span class="bd-menu-name"><?php echo esc_html($m->menu_title); ?></span>
              <?php if ($m->price_jpy !== ''): ?>
                <span class="bd-menu-price"><?php echo number_format(floatval($m->price_jpy)); ?>円〜</span>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <?php 
      $clinic_url = bd_filter_external_url($clinic->clinic_url);
      if (!empty($clinic_url)): 
      ?>
        <p style="margin-top:18px;">
          <a class="bd-btn" href="<?php echo esc_url($clinic_url); ?>" target="_blank" rel="noopener">
            公式サイトを見る
          </a>
        </p>
      <?php endif; ?>
    </article>
    <?php
    return ob_get_clean();
}
add_shortcode('beauty_clinic_detail', 'bd_shortcode_clinic_detail');


/**
 * ルーティング: /clinic/{clinic_id} で詳細ページを表示
 */
function bd_add_rewrite_rules() {
    add_rewrite_rule(
        '^clinic/([0-9]+)/?$',
        'index.php?clinic_id=$matches[1]',
        'top'
    );
}
add_action('init', 'bd_add_rewrite_rules');

/**
 * テーマ有効化時にリライトルールをフラッシュ
 */
function bd_flush_rewrite_rules() {
    bd_add_rewrite_rules();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'bd_flush_rewrite_rules');

// 管理画面でパーマリンク設定を保存した際にもフラッシュ
add_action('after_switch_theme', 'bd_flush_rewrite_rules');

function bd_add_query_vars($vars) {
    $vars[] = 'clinic_id';
    return $vars;
}
add_filter('query_vars', 'bd_add_query_vars');

/**
 * clinic_idパラメータがある場合、single.phpを強制的に読み込む
 */
function bd_template_redirect() {
    $clinic_id = get_query_var('clinic_id');
    if ($clinic_id) {
        // WordPressにこれが見つかったことを伝える
        global $wp_query;
        $wp_query->is_404 = false;
        $wp_query->is_singular = true;
        status_header(200);
        
        // single.phpを読み込む
        include(get_template_directory() . '/single.php');
        exit;
    }
}
add_action('template_redirect', 'bd_template_redirect');

/**
 * REST API: /wp-json/beauty/v1/clinics, /wp-json/beauty/v1/clinics/<id>
 */
function bd_register_rest_routes() {
    register_rest_route('beauty/v1', '/clinics', [
        'methods'  => 'GET',
        'callback' => 'bd_rest_get_clinics',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('beauty/v1', '/clinics/(?P<clinic_id>\d+)', [
        'methods'  => 'GET',
        'callback' => 'bd_rest_get_clinic',
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'bd_register_rest_routes');

function bd_rest_get_clinics(WP_REST_Request $request) {
    global $wpdb;
    $t = bd_tables();

    $prefecture = $request->get_param('prefecture');
    $keyword    = $request->get_param('keyword');

    $where = ["1=1"];
    $params = [];

    if ($prefecture) {
        $where[] = "prefecture = %s";
        $params[] = $prefecture;
    }
    if ($keyword) {
        $where[] = "name LIKE %s";
        $params[] = '%' . $wpdb->esc_like($keyword) . '%';
    }

    $sql = "SELECT * FROM {$t['clinics']} WHERE " . implode(' AND ', $where) . " ORDER BY prefecture, city";
    $prepared = $wpdb->prepare($sql, $params);
    $rows = $wpdb->get_results($prepared);

    return rest_ensure_response($rows);
}

function bd_rest_get_clinic(WP_REST_Request $request) {
    $clinic_id = intval($request['clinic_id']);
    $clinic = bd_get_clinic($clinic_id);
    if (!$clinic) {
        return new WP_Error('not_found', 'Clinic not found', ['status' => 404]);
    }
    $menus = bd_get_clinic_menus($clinic_id);
    $hours = bd_get_clinic_hours($clinic_id);

    $data = [
        'clinic' => $clinic,
        'menus'  => $menus,
        'hours'  => $hours,
    ];
    return rest_ensure_response($data);
}

/**
 * 施術別検索ボタンセクション
 */
function bd_shortcode_treatment_categories() {
    $categories = [
        '目元・二重' => [
            '二重埋没', '二重切開', '眼瞼下垂', '目尻切開', '涙袋形成', 'ROOF切除'
        ],
        '小顔・輪郭' => [
            '顎骨切り・オトガイ形成', '前顎形成', 'ボトックス注射(顎)', 'エラボトックス注射(小顔)', 
            'フェイスリフト・切開リフト', '糸リフト(スレッドリフト)', '小鼻縮小', '顎骨切り・骨切り術',
            'ルフォー骨切り術(上顎骨切り術)', 'SSRO・下顎枝矢状分割法(下顎骨切り術)',
            '上下顎骨切り術(両顎手術)ルフォー+SSRO', '顎修正(シリコンプロテーゼ)', 'ペリカン手術(顎下脂肪除去)',
            'Vライン形成(輪郭注点)', '輪郭3点', '輪郭4点', '顎プロテーゼ挿入', '頬ヒアルロン酸注射'
        ],
        '鼻' => [
            '鼻尖形成', '小鼻縮小', '鼻中隔延長', '鼻プロテーゼ', 'ヒアルロン酸注射(鼻)'
        ],
        '注入・注射' => [
            'ボトックス', 'ヒアルロン酸', 'ジュベルック', 'スネコス', 'プラセンタ注射', 
            '白玉点滴', 'リジュラン', 'ベビーコラーゲン'
        ],
        'レーザー・光治療' => [
            'ピコレーザー', 'ピコトーニング', 'ピコフラクショナル', 'シミ取りレーザー',
            'フォトフェイシャル', 'ライムライト', 'レーザートーニング'
        ],
        '美肌治療' => [
            'ダーマペン', 'ケミカルピーリング', 'ポテンツァ', 'ハイドラフェイシャル',
            'ヴェルベットスキン', 'ウーバーピール'
        ],
        'リフトアップ' => [
            'HIFU(ハイフ)', '糸リフト', 'ウルトラセルQ+', 'ウルセラ', 'サーマクール'
        ],
        '医療脱毛' => [
            '全身脱毛', 'VIO脱毛', '顔脱毛', 'ワキ脱毛', '腕脱毛', '脚脱毛', 'メンズ脱毛'
        ],
        '痩身・ボディ' => [
            '脂肪吸引', 'クールスカルプティング', '脂肪溶解注射', 'カベリン', 'BNLS', 
            'ボトックス(ふくらはぎ)', 'ボトックス(肩)'
        ]
    ];

    ob_start();
    ?>
    <div class="bd-treatment-section">
        <?php foreach ($categories as $category_name => $treatments): ?>
            <div class="bd-treatment-category">
                <h3 class="bd-treatment-category-title">
                    <span class="bd-category-icon">💉</span>
                    <?php echo esc_html($category_name); ?>
                </h3>
                <div class="bd-treatment-buttons">
                    <?php foreach ($treatments as $treatment): ?>
                        <a href="<?php echo esc_url(home_url('/?bd_kw=' . urlencode($treatment))); ?>" 
                           class="bd-treatment-btn">
                            <?php echo esc_html($treatment); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('beauty_treatment_categories', 'bd_shortcode_treatment_categories');

/**
 * 都道府県別一覧リンク
 */
function bd_shortcode_prefecture_list() {
    global $wpdb;
    $t = bd_tables();
    
    // 都道府県リストを取得
    $prefectures = $wpdb->get_col("
        SELECT DISTINCT prefecture 
        FROM {$t['clinics']} 
        WHERE prefecture <> '' 
        ORDER BY prefecture ASC
    ");
    
    // 地方別にグループ化
    $regions = [
        '北海道・東北' => ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県'],
        '関東' => ['東京都', '神奈川県', '千葉県', '埼玉県', '茨城県', '栃木県', '群馬県'],
        '中部' => ['新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県'],
        '関西' => ['三重県', '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県'],
        '中国' => ['鳥取県', '島根県', '岡山県', '広島県', '山口県'],
        '四国' => ['徳島県', '香川県', '愛媛県', '高知県'],
        '九州・沖縄' => ['福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県']
    ];
    
    ob_start();
    ?>
    <div class="bd-prefecture-list">
        <h2 class="bd-section-title">
            <span class="bd-section-icon">📍</span>
            地域から探す
        </h2>
        <div class="bd-regions">
            <?php foreach ($regions as $region_name => $region_prefs): ?>
                <div class="bd-region">
                    <h3 class="bd-region-title"><?php echo esc_html($region_name); ?></h3>
                    <div class="bd-prefecture-links">
                        <?php foreach ($region_prefs as $pref): ?>
                            <?php if (in_array($pref, $prefectures)): ?>
                                <a href="<?php echo esc_url(home_url('/?bd_pref=' . urlencode($pref))); ?>" 
                                   class="bd-prefecture-link">
                                    <?php echo esc_html($pref); ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('beauty_prefecture_list', 'bd_shortcode_prefecture_list');



/**
 * ========================================
 * アフィリエイトバナー管理機能
 * ========================================
 */

/**
 * データベーステーブルの作成
 */
function bd_create_affiliate_banners_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'affiliate_banners';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        banner_image_url TEXT NOT NULL,
        affiliate_url TEXT NOT NULL,
        affiliate_category VARCHAR(100) DEFAULT '',
        display_category VARCHAR(100) DEFAULT '',
        display_order INT(11) DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_switch_theme', 'bd_create_affiliate_banners_table');

/**
 * バナーの取得
 */
function bd_get_affiliate_banners($limit = 3, $display_category = '') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'affiliate_banners';
    
    $where = "WHERE is_active = 1";
    
    if (!empty($display_category)) {
        $where .= $wpdb->prepare(" AND (display_category = %s OR display_category = '')", $display_category);
    }
    
    $sql = "SELECT * FROM $table_name $where ORDER BY display_order ASC, id DESC LIMIT %d";
    
    return $wpdb->get_results($wpdb->prepare($sql, $limit));
}

/**
 * バナー表示用のショートコード
 */
function bd_affiliate_banners_shortcode($atts) {
    $atts = shortcode_atts([
        'limit' => 3,
        'category' => ''
    ], $atts);
    
    $banners = bd_get_affiliate_banners($atts['limit'], $atts['category']);
    
    if (empty($banners)) {
        return '';
    }
    
    ob_start();
    ?>
    <div class="bd-affiliate-banners">
        <?php foreach ($banners as $banner): ?>
            <div class="bd-affiliate-banner-card">
                <a href="<?php echo esc_url($banner->affiliate_url); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer sponsored"
                   class="bd-affiliate-banner-link">
                    <img src="<?php echo esc_url($banner->banner_image_url); ?>" 
                         alt="<?php echo esc_attr($banner->title); ?>"
                         loading="lazy"
                         decoding="async">
                    <div class="bd-affiliate-banner-title">
                        <?php echo esc_html($banner->title); ?>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('affiliate_banners', 'bd_affiliate_banners_shortcode');
