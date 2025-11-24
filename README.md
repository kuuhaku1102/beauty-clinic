# Beauty Directory - 美容クリニック検索サイト

美容クリニックの情報を検索・閲覧できるWordPressテーマです。

## 主な機能

### クリニック一覧ページ
- エリア、メニュー名、価格範囲での絞り込み検索
- ページネーション機能(1ページ12件表示)
- クリニックカード表示(画像、評価、口コミ数、施術メニュー)
- レスポンシブデザイン対応

### クリニック詳細ページ
- クリニック基本情報(名称、所在地、電話番号、評価)
- 営業時間表示
- 施術メニュー一覧(カテゴリー別)
- アクセス情報
- 予約・問い合わせCTA

### その他の機能
- WordPressウィジェット対応(サイドバー、フッター)
- ナビゲーションメニュー対応
- REST API対応
- カスタム投稿タイプ対応

## セットアップ

### 1. テーマのインストール

```bash
# テーマディレクトリに配置
cd /path/to/wordpress/wp-content/themes/
git clone https://github.com/kuuhaku1102/beauty-clinic.git beauty-directory
```

### 2. テーマの有効化

WordPress管理画面 > 外観 > テーマ から「Beauty Directory」を有効化

### 3. パーマリンク設定

**重要**: 詳細ページを正しく表示するために必須です。

1. 管理画面 > 設定 > パーマリンク
2. 「投稿名」を選択
3. 「変更を保存」をクリック

詳しくは `SETUP_GUIDE.md` を参照してください。

### 4. データベース準備

以下のテーブルが必要です:

- `wp_beauty___clinics` - クリニック基本情報
- `wp_beauty___menus` - 施術メニュー
- `wp_beauty___hours` - 営業時間

データベース構造の詳細は `SETUP_GUIDE.md` を参照してください。

## URL構造

- **トップページ**: `https://yourdomain.com/`
- **クリニック詳細**: `https://yourdomain.com/clinic/{clinic_id}/`
- **検索結果**: `https://yourdomain.com/?bd_pref=東京都&bd_kw=ボトックス`

## ショートコード

### クリニック検索一覧

```php
[beauty_clinic_search]
```

トップページで使用。絞り込み検索とページネーション付きの一覧を表示。

### クリニック詳細

```php
[beauty_clinic_detail clinic_id="1"]
```

特定のクリニックの詳細情報を表示。

## REST API

### クリニック一覧取得

```
GET /wp-json/beauty/v1/clinics
GET /wp-json/beauty/v1/clinics?prefecture=東京都
GET /wp-json/beauty/v1/clinics?keyword=ボトックス
```

### クリニック詳細取得

```
GET /wp-json/beauty/v1/clinics/{clinic_id}
```

レスポンス例:
```json
{
  "clinic": { ... },
  "menus": [ ... ],
  "hours": [ ... ]
}
```

## カスタマイズ

### 1ページあたりの表示件数を変更

`functions.php` の `bd_shortcode_clinic_search()` 関数内:

```php
$per_page = 12; // この値を変更
```

### カラースキームの変更

`style.css` の以下の色を変更:

- メインカラー: `#c2185b`
- アクセントカラー: `#f48fb1`, `#ce93d8`
- 背景色: `#faf7fb`

## ファイル構成

```
beauty-directory/
├── index.php              # TOPページ
├── single.php             # 詳細ページ
├── archive.php            # アーカイブページ
├── page.php               # 固定ページ
├── header.php             # ヘッダー
├── footer.php             # フッター
├── sidebar.php            # サイドバー
├── functions.php          # テーマ機能
├── style.css              # スタイルシート
├── assets/images/         # 画像ファイル
├── SETUP_GUIDE.md         # セットアップガイド
├── IMAGE_GUIDE.md         # 画像使用ガイド
└── README.md              # このファイル
```

## 技術仕様

- **WordPress**: 5.0以降
- **PHP**: 7.4以降
- **データベース**: MySQL 5.7以降 / MariaDB 10.2以降
- **ブラウザ対応**: Chrome, Firefox, Safari, Edge (最新版)

## ライセンス

このテーマは個人・商用利用可能です。

## サポート

詳細なドキュメント:
- [セットアップガイド](SETUP_GUIDE.md)
- [画像使用ガイド](IMAGE_GUIDE.md)
- [実装サマリー](IMPLEMENTATION_SUMMARY.md)

## 更新履歴

### v1.1.0 (2024-11-24)
- クリニック詳細ページの実装
- クリニック風画像5種類を追加
- ページネーション機能追加
- WordPress標準構造への移行

### v1.0.0 (初回リリース)
- 基本機能実装
