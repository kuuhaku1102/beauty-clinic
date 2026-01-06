<?php
/**
 * Template Name: Affiliate Manager
 * アフィリエイトバナー管理ページ
 */

// 管理者権限チェック
if (!current_user_can('manage_options')) {
    wp_die('このページにアクセスする権限がありません。');
}

global $wpdb;
$table_name = $wpdb->prefix . 'affiliate_banners';

// メッセージ変数
$message = '';
$message_type = '';

// バナーの追加・更新処理
if (isset($_POST['save_banner']) && check_admin_referer('bd_affiliate_banner_action', 'bd_affiliate_banner_nonce')) {
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
        'is_active' => $is_active
    ];
    
    if ($banner_id > 0) {
        // 更新
        $wpdb->update($table_name, $data, ['id' => $banner_id]);
        $message = 'バナーを更新しました。';
    } else {
        // 新規追加
        $wpdb->insert($table_name, $data);
        $message = 'バナーを追加しました。';
    }
    $message_type = 'success';
}

// バナーの削除処理
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && check_admin_referer('bd_delete_banner_' . $_GET['id'])) {
    $banner_id = intval($_GET['id']);
    $wpdb->delete($table_name, ['id' => $banner_id]);
    $message = 'バナーを削除しました。';
    $message_type = 'success';
}

// 編集対象のバナー取得
$edit_banner = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $banner_id = intval($_GET['id']);
    $edit_banner = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $banner_id));
}

// バナー一覧取得
$banners = $wpdb->get_results("SELECT * FROM $table_name ORDER BY display_order ASC, id DESC");

get_header();
?>

<div class="bd-affiliate-manager">
    <div class="bd-manager-wrapper">
        <!-- ヘッダー -->
        <div class="bd-manager-header">
            <div class="bd-manager-header-content">
                <h1 class="bd-manager-title">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                        <path d="M3 9H21M9 21V9" stroke="currentColor" stroke-width="2"/>
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
        
        <!-- メインコンテンツ -->
        <div class="bd-manager-grid">
            <!-- バナー登録フォーム -->
            <div class="bd-manager-form-section">
                <div class="bd-section-header">
                    <h2 class="bd-section-title">
                        <?php echo $edit_banner ? 'バナーを編集' : '新規バナー登録'; ?>
                    </h2>
                    <?php if ($edit_banner): ?>
                        <a href="<?php echo esc_url(get_permalink()); ?>" class="bd-btn bd-btn-secondary">
                            キャンセル
                        </a>
                    <?php endif; ?>
                </div>
                
                <form method="post" action="" class="bd-banner-form">
                    <?php wp_nonce_field('bd_affiliate_banner_action', 'bd_affiliate_banner_nonce'); ?>
                    <input type="hidden" name="banner_id" value="<?php echo $edit_banner ? esc_attr($edit_banner->id) : '0'; ?>">
                    
                    <div class="bd-form-grid">
                        <!-- バナータイトル -->
                        <div class="bd-form-group">
                            <label class="bd-form-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4 7h16M4 12h16M4 17h10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                バナータイトル
                            </label>
                            <input type="text" 
                                   name="title" 
                                   value="<?php echo $edit_banner ? esc_attr($edit_banner->title) : ''; ?>" 
                                   class="bd-form-input" 
                                   required 
                                   placeholder="例: 〇〇クリニック">
                        </div>
                        
                        <!-- バナー画像URL -->
                        <div class="bd-form-group">
                            <label class="bd-form-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/>
                                    <path d="M21 15l-5-5L5 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                バナー画像URL
                            </label>
                            <input type="url" 
                                   name="banner_image_url" 
                                   value="<?php echo $edit_banner ? esc_url($edit_banner->banner_image_url) : ''; ?>" 
                                   class="bd-form-input" 
                                   required 
                                   placeholder="https://example.com/banner.jpg">
                        </div>
                        
                        <!-- アフィリエイトURL -->
                        <div class="bd-form-group">
                            <label class="bd-form-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                アフィリエイトURL
                            </label>
                            <input type="url" 
                                   name="affiliate_url" 
                                   value="<?php echo $edit_banner ? esc_url($edit_banner->affiliate_url) : ''; ?>" 
                                   class="bd-form-input" 
                                   required 
                                   placeholder="https://example.com/affiliate-link">
                        </div>
                    </div>
                    
                    <div class="bd-form-grid bd-form-grid-2">
                        <!-- アフィリエイトカテゴリー -->
                        <div class="bd-form-group">
                            <label class="bd-form-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20 7h-9M14 17H5M17 7l-4-4-4 4M7 17l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                アフィリエイトカテゴリー
                            </label>
                            <select name="affiliate_category" class="bd-form-select">
                                <option value="">選択してください</option>
                                <option value="クリニック" <?php echo ($edit_banner && $edit_banner->affiliate_category === 'クリニック') ? 'selected' : ''; ?>>クリニック</option>
                                <option value="脱毛" <?php echo ($edit_banner && $edit_banner->affiliate_category === '脱毛') ? 'selected' : ''; ?>>脱毛</option>
                                <option value="美容整形" <?php echo ($edit_banner && $edit_banner->affiliate_category === '美容整形') ? 'selected' : ''; ?>>美容整形</option>
                                <option value="美肌" <?php echo ($edit_banner && $edit_banner->affiliate_category === '美肌') ? 'selected' : ''; ?>>美肌</option>
                                <option value="痩身" <?php echo ($edit_banner && $edit_banner->affiliate_category === '痩身') ? 'selected' : ''; ?>>痩身</option>
                            </select>
                            <small style="display: block; margin-top: 8px; color: #666;">バナーの種類を選択</small>
                        </div>
                        
                        <!-- 表示カテゴリー -->
                        <div class="bd-form-group">
                            <label class="bd-form-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                表示カテゴリー
                            </label>
                            <select name="display_category" class="bd-form-select">
                                <option value="">すべて表示</option>
                                <option value="医療脱毛" <?php echo ($edit_banner && $edit_banner->display_category === '医療脱毛') ? 'selected' : ''; ?>>医療脱毛</option>
                                <option value="二重整形" <?php echo ($edit_banner && $edit_banner->display_category === '二重整形') ? 'selected' : ''; ?>>二重整形</option>
                                <option value="美肌治療" <?php echo ($edit_banner && $edit_banner->display_category === '美肌治療') ? 'selected' : ''; ?>>美肌治療</option>
                                <option value="ボトックス注射" <?php echo ($edit_banner && $edit_banner->display_category === 'ボトックス注射') ? 'selected' : ''; ?>>ボトックス注射</option>
                                <option value="ヒアルロン酸注射" <?php echo ($edit_banner && $edit_banner->display_category === 'ヒアルロン酸注射') ? 'selected' : ''; ?>>ヒアルロン酸注射</option>
                            </select>
                            <small style="display: block; margin-top: 8px; color: #666;">どのカテゴリーで表示するか選択（空欄=すべて）</small>
                        </div>
                        
                        <!-- 表示順序 -->
                        <div class="bd-form-group">
                            <label class="bd-form-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <line x1="4" y1="6" x2="20" y2="6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <line x1="4" y1="12" x2="20" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <line x1="4" y1="18" x2="20" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                表示順序
                            </label>
                            <input type="number" 
                                   name="display_order" 
                                   value="<?php echo $edit_banner ? esc_attr($edit_banner->display_order) : '0'; ?>" 
                                   class="bd-form-input" 
                                   min="0" 
                                   placeholder="0">
                            <small style="display: block; margin-top: 8px; color: #666;">数字が小さいほど優先表示</small>
                        </div>
                        
                        <!-- 有効/無効 -->
                        <div class="bd-form-group">
                            <label class="bd-checkbox-label">
                                <input type="checkbox" 
                                       name="is_active" 
                                       value="1" 
                                       <?php echo ($edit_banner && $edit_banner->is_active) || !$edit_banner ? 'checked' : ''; ?>>
                                <span class="bd-checkbox-text">このバナーを有効にする</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="bd-form-actions" style="display: flex; gap: 12px; margin-top: 32px;">
                        <button type="submit" name="save_banner" class="bd-btn bd-btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <polyline points="17 21 17 13 7 13 7 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <polyline points="7 3 7 8 15 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <?php echo $edit_banner ? '更新する' : '登録する'; ?>
                        </button>
                        <?php if ($edit_banner): ?>
                            <a href="<?php echo esc_url(get_permalink()); ?>" class="bd-btn bd-btn-secondary">
                                キャンセル
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- バナー一覧 -->
            <div class="bd-manager-list-section">
                <div class="bd-section-header">
                    <h2 class="bd-section-title">登録済みバナー</h2>
                    <span class="bd-badge"><?php echo count($banners); ?>件</span>
                </div>
                
                <?php if (empty($banners)): ?>
                    <div class="bd-empty-state">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="3" width="18" height="18" rx="2" stroke="#ddd" stroke-width="2"/>
                            <path d="M3 9H21M9 21V9" stroke="#ddd" stroke-width="2"/>
                        </svg>
                        <p>まだバナーが登録されていません</p>
                        <p class="bd-empty-hint">左のフォームから新しいバナーを登録してください</p>
                    </div>
                <?php else: ?>
                    <div class="bd-banners-grid">
                        <?php foreach ($banners as $banner): ?>
                            <div class="bd-banner-card">
                                <div class="bd-banner-card-image">
                                    <img src="<?php echo esc_url($banner->banner_image_url); ?>" 
                                         alt="<?php echo esc_attr($banner->title); ?>"
                                         loading="lazy">
                                    <div class="bd-banner-card-overlay">
                                        <span class="bd-banner-id">#<?php echo esc_html($banner->id); ?></span>
                                        <span class="bd-status-badge <?php echo $banner->is_active ? 'bd-status-active' : 'bd-status-inactive'; ?>">
                                            <?php echo $banner->is_active ? '有効' : '無効'; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="bd-banner-card-content">
                                    <h3 class="bd-banner-card-title"><?php echo esc_html($banner->title); ?></h3>
                                    <div class="bd-banner-card-meta">
                                        <?php if ($banner->affiliate_category): ?>
                                            <span class="bd-meta-item">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M20 7h-9M14 17H5M17 7l-4-4-4 4M7 17l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                <?php echo esc_html($banner->affiliate_category); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($banner->display_category): ?>
                                            <span class="bd-meta-item">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/>
                                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                                </svg>
                                                <?php echo esc_html($banner->display_category); ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="bd-meta-item">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <line x1="4" y1="6" x2="20" y2="6" stroke="currentColor" stroke-width="2"/>
                                                <line x1="4" y1="12" x2="20" y2="12" stroke="currentColor" stroke-width="2"/>
                                                <line x1="4" y1="18" x2="20" y2="18" stroke="currentColor" stroke-width="2"/>
                                            </svg>
                                            順序: <?php echo esc_html($banner->display_order); ?>
                                        </span>
                                    </div>
                                    <div class="bd-banner-card-actions">
                                        <a href="<?php echo esc_url(add_query_arg(['action' => 'edit', 'id' => $banner->id])); ?>" 
                                           class="bd-btn-icon bd-btn-edit" 
                                           title="編集">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </a>
                                        <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(['action' => 'delete', 'id' => $banner->id]), 'bd_delete_banner_' . $banner->id)); ?>" 
                                           class="bd-btn-icon bd-btn-delete" 
                                           title="削除"
                                           onclick="return confirm('このバナーを削除してもよろしいですか？');">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- 使い方セクション -->
        <div class="bd-manager-usage-section">
            <div class="bd-section-header">
                <h2 class="bd-section-title">使い方ガイド</h2>
            </div>
            
            <div class="bd-usage-grid">
                <div class="bd-usage-card">
                    <div class="bd-usage-icon">1</div>
                    <h3>バナーを登録</h3>
                    <p>画像URL、アフィリエイトリンク、カテゴリーを入力して登録します</p>
                </div>
                
                <div class="bd-usage-card">
                    <div class="bd-usage-icon">2</div>
                    <h3>カテゴリーを設定</h3>
                    <p>アフィリエイトカテゴリー（種類）と表示カテゴリー（表示条件）を選択します</p>
                </div>
                
                <div class="bd-usage-card">
                    <div class="bd-usage-icon">3</div>
                    <h3>自動表示</h3>
                    <p>設定したカテゴリーの記事に自動的にバナーが表示されます</p>
                </div>
            </div>
            
            <div style="margin-top: 32px;">
                <h3 style="font-size: 18px; font-weight: 700; color: #333; margin-bottom: 16px;">ショートコード</h3>
                <p style="font-size: 14px; color: #666; margin-bottom: 12px;">以下のショートコードで任意の場所にバナーを表示できます：</p>
                <div class="bd-code">[affiliate_banners limit="3"]</div>
                <p style="font-size: 14px; color: #666; margin-top: 12px;">特定のカテゴリーのみ表示する場合：</p>
                <div class="bd-code">[affiliate_banners limit="3" category="医療脱毛"]</div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
