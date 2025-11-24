<?php
/**
 * Flush Rewrite Rules Helper Script
 * 
 * このスクリプトをWordPressのルートディレクトリに配置し、
 * ブラウザで一度アクセスしてください。
 * 
 * 例: https://yourdomain.com/wp-content/themes/beauty-directory/flush-rewrite-rules.php
 * 
 * 実行後は削除してください。
 */

// WordPressを読み込み
require_once('../../../wp-load.php');

// 管理者権限チェック
if (!current_user_can('manage_options')) {
    wp_die('権限がありません。');
}

// リライトルールを追加
function bd_add_rewrite_rules_temp() {
    add_rewrite_rule(
        '^clinic/([0-9]+)/?$',
        'index.php?clinic_id=$matches[1]',
        'top'
    );
}
bd_add_rewrite_rules_temp();

// フラッシュ実行
flush_rewrite_rules();

echo '<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>リライトルールをフラッシュしました</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            max-width: 600px;
            margin: 100px auto;
            padding: 20px;
            text-align: center;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 20px;
            border-radius: 8px;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="success">
        <h1>✓ リライトルールをフラッシュしました</h1>
        <p>クリニック詳細ページのURLが正しく動作するようになりました。</p>
    </div>
    
    <div class="info">
        <h2>次のステップ</h2>
        <p>1. このファイル(flush-rewrite-rules.php)を削除してください</p>
        <p>2. トップページにアクセスしてクリニックカードをクリックしてください</p>
        <p>3. 詳細ページが表示されることを確認してください</p>
    </div>
    
    <a href="' . home_url('/') . '">トップページへ戻る</a>
</body>
</html>';
?>
