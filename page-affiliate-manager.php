<?php
/**
 * Template Name: Affiliate Manager
 * Description: アフィリエイトバナー管理ページ
 */

// 管理者権限チェック
if (!current_user_can('manage_options')) {
    wp_die('このページにアクセスする権限がありません。');
}

global $wpdb;
$table_name = $wpdb->prefix . 'affiliate_banners';

// テーブル存在確認とデバッグ情報
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
$debug_info = [];

if (!$table_exists) {
    $debug_info[] = "テーブルが存在しません: $table_name";
    // テーブルを作成
    bd_create_affiliate_banners_table();
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
    if ($table_exists) {
        $debug_info[] = "テーブルを作成しました";
    }
} else {
    $debug_info[] = "テーブルは存在します: $table_name";
}

// メッセージ
$message = '';
$message_type = '';

// バナーの保存処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bd_affiliate_nonce'])) {
    if (!wp_verify_nonce($_POST['bd_affiliate_nonce'], 'bd_affiliate_action')) {
        $message = 'セキュリティチェックに失敗しました。';
        $message_type = 'error';
    } else {
        $banner_id = isset($_POST['banner_id']) ? intval($_POST['banner_id']) : 0;
        $title = sanitize_text_field($_POST['title']);
        $banner_image_url = esc_url_raw($_POST['banner_image_url']);
        $affiliate_url = esc_url_raw($_POST['affiliate_url']);
        $affiliate_category = sanitize_text_field($_POST['affiliate_category']);
        $display_category = sanitize_text_field($_POST['display_category']);
        $display_order = intval($_POST['display_order']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $data = [
            'title' => $title,
            'banner_image_url' => $banner_image_url,
            'affiliate_url' => $affiliate_url,
            'affiliate_category' => $affiliate_category,
            'display_category' => $display_category,
            'display_order' => $display_order,
            'is_active' => $is_active,
        ];

        if ($banner_id > 0) {
            // 更新
            $result = $wpdb->update($table_name, $data, ['id' => $banner_id]);
            $message = $result !== false ? 'バナーを更新しました。' : 'バナーの更新に失敗しました。';
            $message_type = $result !== false ? 'success' : 'error';
        } else {
            // 新規追加
            $result = $wpdb->insert($table_name, $data);
            $message = $result !== false ? 'バナーを追加しました。' : 'バナーの追加に失敗しました。';
            $message_type = $result !== false ? 'success' : 'error';
        }

        if ($result !== false) {
            wp_redirect(get_permalink());
            exit;
        }
    }
}

// バナーの削除処理
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && isset($_GET['_wpnonce'])) {
    if (wp_verify_nonce($_GET['_wpnonce'], 'delete_banner_' . $_GET['id'])) {
        $banner_id = intval($_GET['id']);
        $result = $wpdb->delete($table_name, ['id' => $banner_id]);
        $message = $result !== false ? 'バナーを削除しました。' : 'バナーの削除に失敗しました。';
        $message_type = $result !== false ? 'success' : 'error';
    }
}

// 編集対象のバナー取得
$edit_banner = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $banner_id = intval($_GET['id']);
    $edit_banner = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $banner_id));
}

// バナー一覧取得
$banners = $wpdb->get_results("SELECT * FROM $table_name ORDER BY display_order ASC, id DESC");
$debug_info[] = "取得したバナー数: " . count($banners);

// 既存のアフィリエイトカテゴリーを取得
$existing_categories = $wpdb->get_col("SELECT DISTINCT affiliate_category FROM $table_name WHERE affiliate_category != '' ORDER BY affiliate_category ASC");
$debug_info[] = "既存カテゴリー数: " . count($existing_categories);

get_header();
?>

<div class="bd-manager-wrapper">
    <div class="bd-manager-header">
        <div class="bd-manager-header-content">
            <h1 class="bd-manager-title">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                    <path d="M3 9h18M9 3v18" stroke="currentColor" stroke-width="2"/>
                </svg>
                アフィリエイトバナー管理
            </h1>
            <p class="bd-manager-subtitle">バナーの登録・編集・削除を行います</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="bd-message bd-message-<?php echo esc_attr($message_type); ?>">
            <?php echo esc_html($message); ?>
        </div>
    <?php endif; ?>

    <!-- デバッグ情報（開発時のみ表示） -->
    <?php if (WP_DEBUG && !empty($debug_info)): ?>
        <div class="bd-message" style="background: #f0f0f0; border: 1px solid #ccc;">
            <strong>デバッグ情報:</strong>
            <ul style="margin: 8px 0 0; padding-left: 20px;">
                <?php foreach ($debug_info as $info): ?>
                    <li><?php echo esc_html($info); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- バナー登録フォーム（上部ワイド） -->
    <div class="bd-manager-form-section-wide">
        <div class="bd-manager-form-section">
            <div class="bd-section-header">
                <h2 class="bd-section-title">
                    <?php echo $edit_banner ? 'バナーを編集' : 'バナーを登録'; ?>
                </h2>
            </div>

            <form method="post" action="" class="bd-banner-form">
                <?php wp_nonce_field('bd_affiliate_action', 'bd_affiliate_nonce'); ?>
                <?php if ($edit_banner): ?>
                    <input type="hidden" name="banner_id" value="<?php echo esc_attr($edit_banner->id); ?>">
                <?php endif; ?>

                <div class="bd-form-grid">
                    <!-- バナータイトル -->
                    <div class="bd-form-group">
                        <label class="bd-form-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 7h16M4 12h16M4 17h10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            バナータイトル
                            <span class="required">*</span>
                        </label>
                        <input type="text" 
                               name="title" 
                               value="<?php echo $edit_banner ? esc_attr($edit_banner->title) : ''; ?>" 
                               required 
                               class="bd-form-input" 
                               placeholder="例: 〇〇クリニック 無料カウンセリング">
                    </div>

                    <!-- バナー画像URL -->
                    <div class="bd-form-group">
                        <label class="bd-form-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/>
                                <path d="M21 15l-5-5L5 21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            バナー画像URL
                            <span class="required">*</span>
                        </label>
                        <input type="url" 
                               name="banner_image_url" 
                               value="<?php echo $edit_banner ? esc_url($edit_banner->banner_image_url) : ''; ?>" 
                               required 
                               class="bd-form-input" 
                               placeholder="https://example.com/banner.jpg">
                    </div>

                    <!-- アフィリエイトリンク -->
                    <div class="bd-form-group">
                        <label class="bd-form-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            アフィリエイトリンク
                            <span class="required">*</span>
                        </label>
                        <input type="url" 
                               name="affiliate_url" 
                               value="<?php echo $edit_banner ? esc_url($edit_banner->affiliate_url) : ''; ?>" 
                               required 
                               class="bd-form-input" 
                               placeholder="https://example.com/affiliate-link">
                    </div>
                </div>
                
                <!-- アフィリエイトカテゴリー -->
                    <div class="bd-form-group">
                        <label class="bd-form-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 7h-9M14 17H5M17 7l-4-4-4 4M7 17l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            アフィリエイトカテゴリー
                        </label>
                        <input type="text" 
                               name="affiliate_category" 
                               id="affiliate_category_input"
                               value="<?php echo $edit_banner ? esc_attr($edit_banner->affiliate_category) : ''; ?>" 
                               class="bd-form-input" 
                               placeholder="例: クリニック、脱毛、美容整形、美肌、痩身">
                        <small style="display: block; margin-top: 10px; color: #666;">バナーの種類を入力（自由に追加可能）</small>
                        
                        <?php if (!empty($existing_categories)): ?>
                            <div class="bd-category-tags" style="margin-top: 12px;">
                                <small style="display: block; margin-bottom: 8px; color: #666; font-weight: 600;">既存のカテゴリーから選択:</small>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    <?php foreach ($existing_categories as $category): ?>
                                        <button type="button" 
                                                class="bd-category-tag" 
                                                data-category="<?php echo esc_attr($category); ?>"
                                                style="padding: 8px 16px; background: #fce4ec; color: #c2185b; border: 2px solid transparent; border-radius: 999px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s ease;">
                                            <?php echo esc_html($category); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                
                <!-- 表示カテゴリー -->
                    <div class="bd-form-group">
                        <label class="bd-form-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            表示カテゴリー
                        </label>
                        <select name="display_category" class="bd-form-select">
                            <option value="">すべてのページに表示</option>
                            <option value="medical-hair-removal" <?php echo ($edit_banner && $edit_banner->display_category === 'medical-hair-removal') ? 'selected' : ''; ?>>医療脱毛</option>
                            <option value="double-eyelid-surgery" <?php echo ($edit_banner && $edit_banner->display_category === 'double-eyelid-surgery') ? 'selected' : ''; ?>>二重整形</option>
                            <option value="skin-treatment" <?php echo ($edit_banner && $edit_banner->display_category === 'skin-treatment') ? 'selected' : ''; ?>>美肌治療</option>
                            <option value="botox-injection" <?php echo ($edit_banner && $edit_banner->display_category === 'botox-injection') ? 'selected' : ''; ?>>ボトックス注射</option>
                            <option value="hyaluronic-acid" <?php echo ($edit_banner && $edit_banner->display_category === 'hyaluronic-acid') ? 'selected' : ''; ?>>ヒアルロン酸注射</option>
                        </select>
                        <small style="display: block; margin-top: 8px; color: #666;">どのカテゴリーの記事に表示するか選択</small>
                    </div>

                <!-- 表示順序 -->
                    <div class="bd-form-group">
                        <label class="bd-form-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 4h18M3 12h18M3 20h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            表示順序
                        </label>
                        <input type="number" 
                               name="display_order" 
                               value="<?php echo $edit_banner ? esc_attr($edit_banner->display_order) : '0'; ?>" 
                               min="0" 
                               class="bd-form-input" 
                               placeholder="0">
                        <small style="display: block; margin-top: 8px; color: #666;">数字が小さいほど優先的に表示</small>
                    </div>

                <!-- 有効/無効 -->
                    <div class="bd-form-group">
                        <label class="bd-form-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            ステータス
                        </label>
                        <label class="bd-checkbox-label">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1" 
                                   <?php echo (!$edit_banner || $edit_banner->is_active) ? 'checked' : ''; ?>>
                            <span class="bd-checkbox-text">有効にする</span>
                        </label>
                    </div>

                <div class="bd-form-actions">
                    <button type="submit" class="bd-btn bd-btn-primary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <?php echo $edit_banner ? 'バナーを更新' : 'バナーを登録'; ?>
                    </button>
                    <?php if ($edit_banner): ?>
                        <a href="<?php echo esc_url(get_permalink()); ?>" class="bd-btn bd-btn-secondary">
                            キャンセル
                        </a>
                    <?php endif; ?>
                               </form>
        </div>
    </div>

    <!-- 登録済みバナー一覧（下部） -->
    <div class="bd-manager-list-section-wide">
        <div class="bd-manager-list-section">
            <div class="bd-section-header">
                <h2 class="bd-section-title">登録済みバナー</h2>
                <span class="bd-badge"><?php echo count($banners); ?>件</span>
            </div>

            <?php if (empty($banners)): ?>
                <div class="bd-empty-state">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="18" height="18" rx="2" stroke="#ddd" stroke-width="2"/>
                        <path d="M3 9h18M9 3v18" stroke="#ddd" stroke-width="2"/>
                    </svg>
                    <p>登録されているバナーはありません</p>
                    <p class="bd-empty-hint">左のフォームから新しいバナーを登録してください</p>
                </div>
            <?php else: ?>
                <div class="bd-banners-grid" style="display: grid; gap: 20px;">
                    <?php foreach ($banners as $banner): ?>
                        <div class="bd-banner-card" style="background: #fff; border: 2px solid #f1e4f2; border-radius: 16px; overflow: hidden; transition: all 0.2s ease;">
                            <div style="position: relative; padding-top: 40%; background: #f5f5f5;">
                                <img src="<?php echo esc_url($banner->banner_image_url); ?>" 
                                     alt="<?php echo esc_attr($banner->title); ?>"
                                     style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
                                <?php if (!$banner->is_active): ?>
                                    <div style="position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,0.7); color: #fff; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600;">
                                        無効
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="padding: 16px;">
                                <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 8px; color: #333;">
                                    <?php echo esc_html($banner->title); ?>
                                </h3>
                                <div style="display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
                                    <?php if ($banner->affiliate_category): ?>
                                        <span style="background: #fce4ec; color: #c2185b; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 600;">
                                            <?php echo esc_html($banner->affiliate_category); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($banner->display_category): ?>
                                        <span style="background: #e8f5e9; color: #2e7d32; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 600;">
                                            表示: <?php echo esc_html($banner->display_category); ?>
                                        </span>
                                    <?php endif; ?>
                                    <span style="background: #f5f5f5; color: #666; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 600;">
                                        順序: <?php echo esc_html($banner->display_order); ?>
                                    </span>
                                </div>
                                <div style="display: flex; gap: 8px;">
                                    <a href="<?php echo esc_url(add_query_arg(['action' => 'edit', 'id' => $banner->id])); ?>" 
                                       class="bd-btn bd-btn-secondary" 
                                       style="flex: 1; padding: 10px 16px; font-size: 13px; min-height: auto;">
                                        編集
                                    </a>
                                    <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(['action' => 'delete', 'id' => $banner->id]), 'delete_banner_' . $banner->id)); ?>" 
                                       class="bd-btn bd-btn-secondary" 
                                       style="flex: 1; padding: 10px 16px; font-size: 13px; min-height: auto; background: #ffebee; color: #c62828; border-color: #ffcdd2;"
                                       onclick="return confirm('本当に削除しますか？');">
                                        削除
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ショートコード使用方法 -->
    <div class="bd-shortcode-info">
        <h3 class="bd-shortcode-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8 9l-4 3 4 3M16 9l4 3-4 3M12 3l-2 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            ショートコード使用方法
        </h3>
        <div class="bd-shortcode-content">
            <p>登録したバナーをページや記事に表示するには、以下のショートコードを使用します：</p>
            
            <div class="bd-shortcode-example">
                <div class="bd-shortcode-item">
                    <strong>基本的な使用：</strong>
                    <code>[affiliate_banners]</code>
                    <p>現在のカテゴリーに対応するバナーを表示します。</p>
                </div>
                
                <div class="bd-shortcode-item">
                    <strong>カテゴリーを指定：</strong>
                    <code>[affiliate_banners category="medical-hair-removal"]</code>
                    <p>特定のカテゴリーのバナーのみを表示します。</p>
                </div>
                
                <div class="bd-shortcode-item">
                    <strong>表示数を制限：</strong>
                    <code>[affiliate_banners limit="3"]</code>
                    <p>表示するバナーの数を制限します。</p>
                </div>
                
                <div class="bd-shortcode-item">
                    <strong>組み合わせ：</strong>
                    <code>[affiliate_banners category="skin-treatment" limit="2"]</code>
                    <p>カテゴリーと表示数を同時に指定できます。</p>
                </div>
            </div>
            
            <div class="bd-shortcode-note">
                <strong>注意：</strong>
                <ul>
                    <li>バナーは「表示順序」の小さい順に表示されます</li>
                    <li>「有効にする」にチェックが入っているバナーのみ表示されます</li>
                    <li>「表示カテゴリー」が空の場合、すべてのページに表示されます</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.bd-manager-form-section-wide {
    width: 100%;
    margin-bottom: 30px;
}

.bd-manager-list-section-wide {
    width: 100%;
}

.bd-manager-list-section-wide .bd-manager-list-section {
    background: #fff;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

/* グリッドレイアウトを無効化 */
.bd-manager-grid {
    display: block;
}

/* フォームセクションをワイド表示 */
.bd-manager-form-section {
    max-width: 100%;
}

/* バナーカードをグリッド表示 */
.bd-banner-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}

@media (max-width: 768px) {
    .bd-banner-grid {
        grid-template-columns: 1fr;
    }
}

/* ショートコード説明セクション */
.bd-shortcode-info {
    background: #fff;
    border-radius: 12px;
    padding: 30px;
    margin-top: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.bd-shortcode-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 20px;
    font-weight: 700;
    color: #333;
    margin: 0 0 20px 0;
}

.bd-shortcode-title svg {
    color: #e91e63;
}

.bd-shortcode-content {
    color: #555;
    line-height: 1.8;
}

.bd-shortcode-content > p {
    margin: 0 0 20px 0;
    font-size: 15px;
}

.bd-shortcode-example {
    display: grid;
    gap: 16px;
    margin-bottom: 24px;
}

.bd-shortcode-item {
    background: #f8f9fa;
    border-left: 4px solid #e91e63;
    padding: 16px 20px;
    border-radius: 6px;
}

.bd-shortcode-item strong {
    display: block;
    color: #333;
    font-size: 14px;
    margin-bottom: 8px;
}

.bd-shortcode-item code {
    display: block;
    background: #fff;
    color: #e91e63;
    padding: 10px 14px;
    border-radius: 6px;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    margin: 8px 0;
    border: 1px solid #e0e0e0;
    word-break: break-all;
}

.bd-shortcode-item p {
    margin: 8px 0 0 0;
    font-size: 13px;
    color: #666;
}

.bd-shortcode-note {
    background: #fff3e0;
    border: 1px solid #ffb74d;
    border-radius: 8px;
    padding: 16px 20px;
}

.bd-shortcode-note strong {
    display: block;
    color: #e65100;
    font-size: 14px;
    margin-bottom: 10px;
}

.bd-shortcode-note ul {
    margin: 0;
    padding-left: 20px;
}

.bd-shortcode-note li {
    color: #666;
    font-size: 13px;
    margin: 6px 0;
}
</style>

<script>
// カテゴリータグのクリックイベント
document.addEventListener('DOMContentLoaded', function() {
    const categoryTags = document.querySelectorAll('.bd-category-tag');
    const categoryInput = document.getElementById('affiliate_category_input');
    
    categoryTags.forEach(tag => {
        tag.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            categoryInput.value = category;
            
            // ビジュアルフィードバック
            categoryTags.forEach(t => {
                t.style.borderColor = 'transparent';
                t.style.background = '#fce4ec';
            });
            this.style.borderColor = '#c2185b';
            this.style.background = '#fff';
        });
        
        // ホバーエフェクト
        tag.addEventListener('mouseenter', function() {
            if (this.style.borderColor !== 'rgb(194, 24, 91)') {
                this.style.borderColor = '#f48fb1';
                this.style.background = '#fff';
            }
        });
        
        tag.addEventListener('mouseleave', function() {
            if (this.style.borderColor !== 'rgb(194, 24, 91)') {
                this.style.borderColor = 'transparent';
                this.style.background = '#fce4ec';
            }
        });
    });
});
</script>

<?php get_footer(); ?>
