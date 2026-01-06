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
        $target_prefectures = sanitize_textarea_field($_POST['target_prefectures']);
        $category = sanitize_text_field($_POST['category']);
        $display_order = intval($_POST['display_order']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $data = [
            'title' => $title,
            'banner_image_url' => $banner_image_url,
            'affiliate_url' => $affiliate_url,
            'target_prefectures' => $target_prefectures,
            'category' => $category,
            'display_order' => $display_order,
            'is_active' => $is_active
        ];
        
        if ($id > 0) {
            // 更新
            $wpdb->update($table_name, $data, ['id' => $id]);
            $message = 'バナーを更新しました。';
        } else {
            // 新規追加
            $wpdb->insert($table_name, $data);
            $message = 'バナーを追加しました。';
        }
    }
    
    // 削除
    if ($action === 'delete') {
        $id = intval($_POST['banner_id']);
        $wpdb->delete($table_name, ['id' => $id]);
        $message = 'バナーを削除しました。';
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
    <div class="bd-section-container">
        <h1 class="bd-manager-title">アフィリエイトバナー管理</h1>
        
        <?php if (isset($message)): ?>
            <div class="bd-message bd-message-success">
                <?php echo esc_html($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- バナー登録・編集フォーム -->
        <div class="bd-manager-form-section">
            <h2><?php echo $edit_banner ? 'バナーを編集' : 'バナーを新規追加'; ?></h2>
            <form method="post" class="bd-banner-form">
                <?php wp_nonce_field('bd_affiliate_action', 'bd_affiliate_nonce'); ?>
                <input type="hidden" name="action" value="save">
                <?php if ($edit_banner): ?>
                    <input type="hidden" name="banner_id" value="<?php echo esc_attr($edit_banner->id); ?>">
                <?php endif; ?>
                
                <div class="bd-form-row">
                    <label for="title">バナータイトル <span class="required">*</span></label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="<?php echo $edit_banner ? esc_attr($edit_banner->title) : ''; ?>" 
                           required 
                           class="bd-input">
                </div>
                
                <div class="bd-form-row">
                    <label for="banner_image_url">バナー画像URL <span class="required">*</span></label>
                    <input type="url" 
                           id="banner_image_url" 
                           name="banner_image_url" 
                           value="<?php echo $edit_banner ? esc_attr($edit_banner->banner_image_url) : ''; ?>" 
                           required 
                           class="bd-input"
                           placeholder="https://example.com/banner.jpg">
                    <p class="bd-form-help">画像をWordPressメディアライブラリにアップロードし、URLをコピーしてください</p>
                </div>
                
                <div class="bd-form-row">
                    <label for="affiliate_url">アフィリエイトリンク <span class="required">*</span></label>
                    <input type="url" 
                           id="affiliate_url" 
                           name="affiliate_url" 
                           value="<?php echo $edit_banner ? esc_attr($edit_banner->affiliate_url) : ''; ?>" 
                           required 
                           class="bd-input"
                           placeholder="https://example.com/affiliate">
                </div>
                
                <div class="bd-form-row">
                    <label for="category">カテゴリー</label>
                    <select id="category" name="category" class="bd-input">
                        <option value="">すべて</option>
                        <option value="医療脱毛" <?php echo ($edit_banner && $edit_banner->category === '医療脱毛') ? 'selected' : ''; ?>>医療脱毛</option>
                        <option value="二重整形" <?php echo ($edit_banner && $edit_banner->category === '二重整形') ? 'selected' : ''; ?>>二重整形</option>
                        <option value="美肌治療" <?php echo ($edit_banner && $edit_banner->category === '美肌治療') ? 'selected' : ''; ?>>美肌治療</option>
                        <option value="ボトックス注射" <?php echo ($edit_banner && $edit_banner->category === 'ボトックス注射') ? 'selected' : ''; ?>>ボトックス注射</option>
                        <option value="ヒアルロン酸注射" <?php echo ($edit_banner && $edit_banner->category === 'ヒアルロン酸注射') ? 'selected' : ''; ?>>ヒアルロン酸注射</option>
                    </select>
                    <p class="bd-form-help">特定のカテゴリーページのみに表示する場合は選択してください</p>
                </div>
                
                <div class="bd-form-row">
                    <label for="target_prefectures">対応都道府県</label>
                    <textarea id="target_prefectures" 
                              name="target_prefectures" 
                              class="bd-textarea"
                              placeholder="東京都,大阪府,愛知県"><?php echo $edit_banner ? esc_textarea($edit_banner->target_prefectures) : ''; ?></textarea>
                    <p class="bd-form-help">カンマ区切りで入力してください（例: 東京都,大阪府,愛知県）</p>
                </div>
                
                <div class="bd-form-row">
                    <label for="display_order">表示順序</label>
                    <input type="number" 
                           id="display_order" 
                           name="display_order" 
                           value="<?php echo $edit_banner ? esc_attr($edit_banner->display_order) : '0'; ?>" 
                           min="0" 
                           class="bd-input">
                    <p class="bd-form-help">数字が小さいほど先に表示されます（0が最優先）</p>
                </div>
                
                <div class="bd-form-row">
                    <label>
                        <input type="checkbox" 
                               name="is_active" 
                               value="1" 
                               <?php echo (!$edit_banner || $edit_banner->is_active) ? 'checked' : ''; ?>>
                        有効
                    </label>
                    <p class="bd-form-help">チェックを外すとバナーが非表示になります</p>
                </div>
                
                <div class="bd-form-actions">
                    <button type="submit" class="bd-btn bd-btn-primary">
                        <?php echo $edit_banner ? '更新' : '追加'; ?>
                    </button>
                    <?php if ($edit_banner): ?>
                        <a href="<?php echo esc_url(get_permalink()); ?>" class="bd-btn bd-btn-secondary">キャンセル</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- バナー一覧 -->
        <div class="bd-manager-list-section">
            <h2>登録済みバナー一覧</h2>
            <?php if (empty($banners)): ?>
                <p class="bd-no-data">バナーが登録されていません。</p>
            <?php else: ?>
                <div class="bd-banners-table-wrapper">
                    <table class="bd-banners-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>プレビュー</th>
                                <th>タイトル</th>
                                <th>カテゴリー</th>
                                <th>表示順序</th>
                                <th>状態</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($banners as $banner): ?>
                                <tr>
                                    <td><?php echo esc_html($banner->id); ?></td>
                                    <td>
                                        <img src="<?php echo esc_url($banner->banner_image_url); ?>" 
                                             alt="<?php echo esc_attr($banner->title); ?>"
                                             class="bd-banner-preview">
                                    </td>
                                    <td><?php echo esc_html($banner->title); ?></td>
                                    <td><?php echo esc_html($banner->category ?: 'すべて'); ?></td>
                                    <td><?php echo esc_html($banner->display_order); ?></td>
                                    <td>
                                        <span class="bd-status <?php echo $banner->is_active ? 'bd-status-active' : 'bd-status-inactive'; ?>">
                                            <?php echo $banner->is_active ? '有効' : '無効'; ?>
                                        </span>
                                    </td>
                                    <td class="bd-actions">
                                        <a href="?edit=<?php echo esc_attr($banner->id); ?>" class="bd-btn-small bd-btn-edit">編集</a>
                                        <form method="post" style="display:inline;" onsubmit="return confirm('本当に削除しますか？');">
                                            <?php wp_nonce_field('bd_affiliate_action', 'bd_affiliate_nonce'); ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="banner_id" value="<?php echo esc_attr($banner->id); ?>">
                                            <button type="submit" class="bd-btn-small bd-btn-delete">削除</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- 使用方法 -->
        <div class="bd-manager-usage-section">
            <h2>バナーの表示方法</h2>
            <p>以下のショートコードを記事やページに挿入してください：</p>
            <pre class="bd-code">[affiliate_banners limit="3"]</pre>
            <p>カテゴリー別に表示する場合：</p>
            <pre class="bd-code">[affiliate_banners limit="3" category="医療脱毛"]</pre>
        </div>
    </div>
</div>

<?php get_footer(); ?>
