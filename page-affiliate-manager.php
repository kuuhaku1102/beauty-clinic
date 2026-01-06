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

// アクション処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // nonce検証
    if (!isset($_POST['bd_affiliate_nonce']) || !wp_verify_nonce($_POST['bd_affiliate_nonce'], 'bd_affiliate_action')) {
        wp_die('セキュリティチェックに失敗しました。');
    }
    
    $action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';
    
    // 新規追加・編集
    if ($action === 'save') {
        $id = isset($_POST['banner_id']) ? intval($_POST['banner_id']) : 0;
        $title = sanitize_text_field($_POST['title']);
        $banner_image_url = esc_url_raw($_POST['banner_image_url']);
        $affiliate_url = esc_url_raw($_POST['affiliate_url']);
        $category = sanitize_text_field($_POST['category']);
        $display_order = intval($_POST['display_order']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $data = [
            'title' => $title,
            'banner_image_url' => $banner_image_url,
            'affiliate_url' => $affiliate_url,
            'target_prefectures' => '', // 都道府県フィールドは空に
            'category' => $category,
            'display_order' => $display_order,
            'is_active' => $is_active
        ];
        
        if ($id > 0) {
            // 更新
            $wpdb->update($table_name, $data, ['id' => $id]);
            $message = 'バナーを更新しました。';
            $message_type = 'success';
        } else {
            // 新規追加
            $wpdb->insert($table_name, $data);
            $message = 'バナーを追加しました。';
            $message_type = 'success';
        }
    }
    
    // 削除
    if ($action === 'delete') {
        $id = intval($_POST['banner_id']);
        $wpdb->delete($table_name, ['id' => $id]);
        $message = 'バナーを削除しました。';
        $message_type = 'success';
    }
}

// 編集対象のバナーを取得
$edit_banner = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_banner = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $edit_id));
}

// バナー一覧を取得
$banners = $wpdb->get_results("SELECT * FROM $table_name ORDER BY display_order ASC, id DESC");

get_header();
?>

<div class="bd-affiliate-manager">
    <div class="bd-manager-wrapper">
        <!-- ヘッダー -->
        <div class="bd-manager-header">
            <div class="bd-manager-header-content">
                <h1 class="bd-manager-title">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="#c2185b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 17L12 22L22 17" stroke="#c2185b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 12L12 17L22 12" stroke="#c2185b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    アフィリエイトバナー管理
                </h1>
                <p class="bd-manager-subtitle">おすすめクリニックのバナーを管理します</p>
            </div>
        </div>
        
        <?php if (isset($message)): ?>
            <div class="bd-message bd-message-<?php echo esc_attr($message_type); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="22 4 12 14.01 9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <?php echo esc_html($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="bd-manager-grid">
            <!-- バナー登録・編集フォーム -->
            <div class="bd-manager-form-section">
                <div class="bd-section-header">
                    <h2 class="bd-section-title">
                        <?php echo $edit_banner ? '✏️ バナーを編集' : '➕ バナーを新規追加'; ?>
                    </h2>
                </div>
                
                <form method="post" class="bd-banner-form">
                    <?php wp_nonce_field('bd_affiliate_action', 'bd_affiliate_nonce'); ?>
                    <input type="hidden" name="action" value="save">
                    <?php if ($edit_banner): ?>
                        <input type="hidden" name="banner_id" value="<?php echo esc_attr($edit_banner->id); ?>">
                    <?php endif; ?>
                    
                    <div class="bd-form-grid">
                        <div class="bd-form-group">
                            <label for="title" class="bd-form-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4 7h16M4 12h16M4 17h10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                バナータイトル <span class="bd-required">*</span>
                            </label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   value="<?php echo $edit_banner ? esc_attr($edit_banner->title) : ''; ?>" 
                                   required 
                                   class="bd-input"
                                   placeholder="例: 〇〇クリニック">
                        </div>
                        
                        <div class="bd-form-group">
                            <label for="category" class="bd-form-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                カテゴリー
                            </label>
                            <select id="category" name="category" class="bd-input">
                                <option value="">すべて</option>
                                <option value="クリニック" <?php echo ($edit_banner && $edit_banner->category === 'クリニック') ? 'selected' : ''; ?>>クリニック</option>
                                <option value="脱毛" <?php echo ($edit_banner && $edit_banner->category === '脱毛') ? 'selected' : ''; ?>>脱毛</option>
                                <option value="美容整形" <?php echo ($edit_banner && $edit_banner->category === '美容整形') ? 'selected' : ''; ?>>美容整形</option>
                                <option value="美肌" <?php echo ($edit_banner && $edit_banner->category === '美肌') ? 'selected' : ''; ?>>美肌</option>
                                <option value="痩身" <?php echo ($edit_banner && $edit_banner->category === '痩身') ? 'selected' : ''; ?>>痩身</option>
                            </select>
                            <p class="bd-form-help">特定のカテゴリーのみに表示する場合は選択してください</p>
                        </div>
                    </div>
                    
                    <div class="bd-form-group">
                        <label for="banner_image_url" class="bd-form-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/>
                                <polyline points="21 15 16 10 5 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            バナー画像URL <span class="bd-required">*</span>
                        </label>
                        <input type="url" 
                               id="banner_image_url" 
                               name="banner_image_url" 
                               value="<?php echo $edit_banner ? esc_attr($edit_banner->banner_image_url) : ''; ?>" 
                               required 
                               class="bd-input"
                               placeholder="https://example.com/banner.jpg">
                        <p class="bd-form-help">💡 WordPressメディアライブラリに画像をアップロードし、URLをコピーしてください</p>
                    </div>
                    
                    <div class="bd-form-group">
                        <label for="affiliate_url" class="bd-form-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            アフィリエイトリンク <span class="bd-required">*</span>
                        </label>
                        <input type="url" 
                               id="affiliate_url" 
                               name="affiliate_url" 
                               value="<?php echo $edit_banner ? esc_attr($edit_banner->affiliate_url) : ''; ?>" 
                               required 
                               class="bd-input"
                               placeholder="https://example.com/affiliate">
                    </div>
                    
                    <div class="bd-form-grid bd-form-grid-2">
                        <div class="bd-form-group">
                            <label for="display_order" class="bd-form-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <line x1="8" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <line x1="8" y1="12" x2="21" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <line x1="8" y1="18" x2="21" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <line x1="3" y1="6" x2="3.01" y2="6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <line x1="3" y1="12" x2="3.01" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <line x1="3" y1="18" x2="3.01" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                表示順序
                            </label>
                            <input type="number" 
                                   id="display_order" 
                                   name="display_order" 
                                   value="<?php echo $edit_banner ? esc_attr($edit_banner->display_order) : '0'; ?>" 
                                   min="0" 
                                   class="bd-input">
                            <p class="bd-form-help">数字が小さいほど先に表示されます</p>
                        </div>
                        
                        <div class="bd-form-group">
                            <label class="bd-form-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <polyline points="22 4 12 14.01 9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
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
                            <p class="bd-form-help">チェックを外すとバナーが非表示になります</p>
                        </div>
                    </div>
                    
                    <div class="bd-form-actions">
                        <button type="submit" class="bd-btn bd-btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <polyline points="17 21 17 13 7 13 7 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <polyline points="7 3 7 8 15 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <?php echo $edit_banner ? '更新する' : '追加する'; ?>
                        </button>
                        <?php if ($edit_banner): ?>
                            <a href="<?php echo esc_url(get_permalink()); ?>" class="bd-btn bd-btn-secondary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                キャンセル
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- バナー一覧 -->
            <div class="bd-manager-list-section">
                <div class="bd-section-header">
                    <h2 class="bd-section-title">📋 登録済みバナー一覧</h2>
                    <span class="bd-badge"><?php echo count($banners); ?>件</span>
                </div>
                
                <?php if (empty($banners)): ?>
                    <div class="bd-empty-state">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke="#ccc" stroke-width="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5" fill="#ccc"/>
                            <polyline points="21 15 16 10 5 21" stroke="#ccc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <p>バナーが登録されていません</p>
                        <p class="bd-empty-hint">上のフォームから新しいバナーを追加してください</p>
                    </div>
                <?php else: ?>
                    <div class="bd-banners-grid">
                        <?php foreach ($banners as $banner): ?>
                            <div class="bd-banner-card">
                                <div class="bd-banner-card-image">
                                    <img src="<?php echo esc_url($banner->banner_image_url); ?>" 
                                         alt="<?php echo esc_attr($banner->title); ?>">
                                    <div class="bd-banner-card-overlay">
                                        <span class="bd-banner-id">#<?php echo esc_html($banner->id); ?></span>
                                        <?php if ($banner->is_active): ?>
                                            <span class="bd-status-badge bd-status-active">有効</span>
                                        <?php else: ?>
                                            <span class="bd-status-badge bd-status-inactive">無効</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="bd-banner-card-content">
                                    <h3 class="bd-banner-card-title"><?php echo esc_html($banner->title); ?></h3>
                                    <div class="bd-banner-card-meta">
                                        <?php if ($banner->category): ?>
                                            <span class="bd-meta-item">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                <?php echo esc_html($banner->category); ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="bd-meta-item">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <line x1="8" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <line x1="8" y1="12" x2="21" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <line x1="8" y1="18" x2="21" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                            順序: <?php echo esc_html($banner->display_order); ?>
                                        </span>
                                    </div>
                                    <div class="bd-banner-card-actions">
                                        <a href="?edit=<?php echo esc_attr($banner->id); ?>" class="bd-btn-icon bd-btn-edit" title="編集">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </a>
                                        <form method="post" style="display:inline;" onsubmit="return confirm('本当に削除しますか？');">
                                            <?php wp_nonce_field('bd_affiliate_action', 'bd_affiliate_nonce'); ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="banner_id" value="<?php echo esc_attr($banner->id); ?>">
                                            <button type="submit" class="bd-btn-icon bd-btn-delete" title="削除">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- 使用方法 -->
        <div class="bd-manager-usage-section">
            <div class="bd-section-header">
                <h2 class="bd-section-title">📖 使用方法</h2>
            </div>
            <div class="bd-usage-grid">
                <div class="bd-usage-card">
                    <div class="bd-usage-icon">1</div>
                    <h3>基本的な表示</h3>
                    <pre class="bd-code">[affiliate_banners limit="3"]</pre>
                    <p>3件のバナーを表示します</p>
                </div>
                <div class="bd-usage-card">
                    <div class="bd-usage-icon">2</div>
                    <h3>カテゴリー別表示</h3>
                    <pre class="bd-code">[affiliate_banners category="クリニック"]</pre>
                    <p>特定のカテゴリーのバナーのみ表示します</p>
                </div>
                <div class="bd-usage-card">
                    <div class="bd-usage-icon">3</div>
                    <h3>件数指定</h3>
                    <pre class="bd-code">[affiliate_banners limit="5"]</pre>
                    <p>表示件数を変更できます</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
