# クリニック画像使用ガイド

## 生成された画像

`assets/images/` ディレクトリに5種類のクリニック風画像を生成しました。

### 画像一覧

1. **clinic-01.jpg** - 受付・待合室
   - モダンでラグジュアリーな美容クリニックの受付エリア
   - ピンクとホワイトのインテリアデザイン
   - 明るく清潔感のある雰囲気

2. **clinic-02.jpg** - 施術室
   - プロフェッショナルな美容施術室
   - 清潔な白い施術ベッドと医療機器
   - 落ち着いたパステルカラー

3. **clinic-03.jpg** - カウンセリングルーム
   - エレガントなカウンセリングルーム
   - 医師のデスクと快適な患者用チェア
   - 壁に医療資格証明書
   - 温かみのある照明

4. **clinic-04.jpg** - 廊下・エントランス
   - ラグジュアリーなクリニックの廊下
   - 大理石の床とピンクのアクセントウォール
   - モダンな照明器具と植物の装飾

5. **clinic-05.jpg** - フェイシャル施術室
   - 現代的な美容クリニックのフェイシャル施術室
   - 先進的なスキンケア機器
   - 快適な施術チェア

## データベースへの画像登録方法

### 方法1: SQLで直接更新

```sql
-- クリニックのメイン画像を設定
UPDATE wp_beauty___clinics 
SET image_url = 'https://yourdomain.com/wp-content/themes/beauty-directory/assets/images/clinic-01.jpg'
WHERE clinic_id = 1;

-- メニュー画像を設定
UPDATE wp_beauty___menus 
SET menu_img = 'https://yourdomain.com/wp-content/themes/beauty-directory/assets/images/clinic-02.jpg'
WHERE clinic_id = 1 AND menu_title LIKE '%ボトックス%';
```

### 方法2: WordPressメディアライブラリにアップロード

1. WordPress管理画面 > メディア > 新規追加
2. `assets/images/` 内の画像をアップロード
3. アップロードした画像のURLをコピー
4. データベースまたは管理画面から画像URLを設定

### 方法3: PHPスクリプトで一括登録

```php
<?php
// functions.php に追加するサンプルコード
function bd_assign_sample_images() {
    global $wpdb;
    $t = bd_tables();
    
    $theme_url = get_template_directory_uri();
    $images = [
        $theme_url . '/assets/images/clinic-01.jpg',
        $theme_url . '/assets/images/clinic-02.jpg',
        $theme_url . '/assets/images/clinic-03.jpg',
        $theme_url . '/assets/images/clinic-04.jpg',
        $theme_url . '/assets/images/clinic-05.jpg',
    ];
    
    // クリニックにランダムに画像を割り当て
    $clinics = $wpdb->get_results("SELECT clinic_id FROM {$t['clinics']} LIMIT 50");
    foreach ($clinics as $clinic) {
        $random_image = $images[array_rand($images)];
        $wpdb->update(
            $t['clinics'],
            ['image_url' => $random_image],
            ['clinic_id' => $clinic->clinic_id]
        );
    }
}
// 実行: bd_assign_sample_images();
```

## 画像の配置場所

現在の画像パス: `/home/ubuntu/beauty-clinic/assets/images/`

本番環境では以下のいずれかに配置してください:

1. **テーマディレクトリ内**
   - `wp-content/themes/beauty-directory/assets/images/`
   - URL: `https://yourdomain.com/wp-content/themes/beauty-directory/assets/images/clinic-01.jpg`

2. **アップロードディレクトリ**
   - `wp-content/uploads/clinic-images/`
   - URL: `https://yourdomain.com/wp-content/uploads/clinic-images/clinic-01.jpg`

3. **CDN**
   - 画像をCDNにアップロードしてURLを使用

## 画像の表示箇所

### クリニック一覧ページ
- `first_image` カラムから取得
- カードのサムネイル画像として表示
- 画像がない場合は空白

### クリニック詳細ページ
- `clinic.image_url` を優先的に使用
- なければ `menu_img` から取得
- どちらもない場合はプレースホルダー表示

### メニュー一覧
- `menu_img` カラムから取得
- カテゴリー別に表示
- おすすめバッジ付き

## 推奨事項

1. **画像の最適化**: 本番環境では画像を圧縮してページ速度を改善
2. **レスポンシブ対応**: 異なるサイズの画像を用意してsrcsetを使用
3. **遅延読み込み**: `loading="lazy"` 属性を追加
4. **alt属性**: すべての画像に適切なalt属性を設定済み

## 注意事項

- 生成された画像はサンプルです
- 実際のクリニック写真に差し替えることを推奨
- 著作権や肖像権に注意してください
