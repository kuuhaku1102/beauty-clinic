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

function bd_get_clinic_hours($clinic_id) {
    global $wpdb;
    $t = bd_tables();
    return $wpdb->get_results($wpdb->prepare("
        SELECT *
        FROM {$t['hours']}
        WHERE clinic_id = %d
        ORDER BY FIELD(day,'月','火','水','木','金','土','日')
    ", $clinic_id));
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
    $join  = "";
    $params = [];

    if ($prefecture !== '') {
        $where[] = "c.prefecture = %s";
        $params[] = $prefecture;
    }

    if ($keyword !== '' || $min_price > 0 || $max_price > 0) {
        $join = "LEFT JOIN {$t['menus']} m ON c.clinic_id = m.clinic_id";
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

        <label>メニュー名
          <input type="text" name="bd_kw" value="<?php echo esc_attr($keyword); ?>" placeholder="例:二重・ボトックス">
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
        $img = $c->first_image ?: '';
        $rating = $c->rating;
        $reviews = $c->reviews_count;
        $detail_url = home_url('/clinic/' . intval($c->clinic_id));
    ?>
      <article class="bd-clinic-card">
        <div class="bd-card-thumb">
          <?php if ($img): ?>
            <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($c->name); ?>">
          <?php endif; ?>
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

      <?php if (!empty($clinic->clinic_url)): ?>
        <p style="margin-top:18px;">
          <a class="bd-btn" href="<?php echo esc_url($clinic->clinic_url); ?>" target="_blank" rel="noopener">
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
