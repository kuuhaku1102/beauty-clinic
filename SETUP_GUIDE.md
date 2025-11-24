# セットアップガイド

## TOPページと詳細ページの紐づけ設定

このテーマでは、クリニック一覧ページから詳細ページへのリンクが `/clinic/{clinic_id}` という形式で動作します。

### 必要な設定

#### 1. パーマリンク設定

WordPress管理画面で以下の設定を行ってください:

1. **管理画面 > 設定 > パーマリンク** にアクセス
2. パーマリンク構造を **「投稿名」** または **「カスタム構造」** に設定
3. 「変更を保存」ボタンをクリック

> **注意**: 「基本」設定では動作しません。必ず「投稿名」以上の設定にしてください。

#### 2. リライトルールのフラッシュ

パーマリンク設定後、以下のいずれかの方法でリライトルールをフラッシュしてください:

**方法A: 管理画面から(推奨)**
1. WordPress管理画面 > 設定 > パーマリンク
2. 何も変更せずに「変更を保存」ボタンをクリック

**方法B: ヘルパースクリプトを使用**
1. `flush-rewrite-rules.php` をブラウザで開く
   - 例: `https://yourdomain.com/wp-content/themes/beauty-directory/flush-rewrite-rules.php`
2. 実行後、ファイルを削除

**方法C: WP-CLIを使用**
```bash
wp rewrite flush
```

### URL構造

設定後、以下のURL構造で動作します:

- **トップページ(一覧)**: `https://yourdomain.com/`
- **クリニック詳細**: `https://yourdomain.com/clinic/1/`
- **検索結果**: `https://yourdomain.com/?bd_pref=東京都&bd_kw=ボトックス`
- **ページネーション**: `https://yourdomain.com/?paged=2`

### 動作確認

1. トップページにアクセス
2. クリニックカードの「詳細を見る」リンクをクリック
3. クリニック詳細ページが表示されることを確認

詳細ページが404エラーになる場合は、上記の「リライトルールのフラッシュ」を再度実行してください。

## トラブルシューティング

### 詳細ページが404エラーになる

**原因**: リライトルールが正しく登録されていない

**解決方法**:
1. 管理画面 > 設定 > パーマリンク を開く
2. 「変更を保存」をクリック(設定変更不要)
3. ページをリロードして再度確認

### クリニックカードのリンクが正しく表示されない

**原因**: データベースに `clinic_id` が存在しない

**解決方法**:
1. データベースを確認: `wp_beauty___clinics` テーブルに `clinic_id` カラムがあるか
2. データが正しく登録されているか確認

### 画像が表示されない

**原因**: 画像URLが設定されていない、またはパスが間違っている

**解決方法**:
1. `IMAGE_GUIDE.md` を参照して画像を配置
2. データベースの `image_url` または `menu_img` カラムに正しいURLを設定

## データベース確認方法

### クリニックデータの確認

```sql
-- クリニック一覧を確認
SELECT clinic_id, name, prefecture, city FROM wp_beauty___clinics LIMIT 10;

-- 特定のクリニックの詳細を確認
SELECT * FROM wp_beauty___clinics WHERE clinic_id = 1;

-- メニュー情報を確認
SELECT * FROM wp_beauty___menus WHERE clinic_id = 1;

-- 営業時間を確認
SELECT * FROM wp_beauty___hours WHERE clinic_id = 1;
```

## ファイル構成

```
beauty-clinic/
├── index.php              # TOPページ(一覧)
├── single.php             # 詳細ページ
├── archive.php            # アーカイブページ
├── page.php               # 固定ページ
├── header.php             # ヘッダー
├── footer.php             # フッター
├── sidebar.php            # サイドバー
├── functions.php          # テーマ機能
├── style.css              # スタイルシート
├── assets/
│   └── images/           # クリニック画像
│       ├── clinic-01.jpg
│       ├── clinic-02.jpg
│       ├── clinic-03.jpg
│       ├── clinic-04.jpg
│       └── clinic-05.jpg
├── SETUP_GUIDE.md        # このファイル
├── IMAGE_GUIDE.md        # 画像使用ガイド
└── IMPLEMENTATION_SUMMARY.md  # 実装サマリー
```

## サポート

問題が解決しない場合は、以下を確認してください:

1. WordPressバージョン: 5.0以降
2. PHPバージョン: 7.4以降
3. パーマリンク設定: 「投稿名」または「カスタム構造」
4. データベーステーブル: `wp_beauty___clinics`, `wp_beauty___menus`, `wp_beauty___hours` が存在するか
5. テーマの有効化: Beauty Directory テーマが有効になっているか
