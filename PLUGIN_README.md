# Beauty Clinic Lab Plugin

美容クリニック情報を **独自DBテーブル** から取得し、  
WordPress上で「クリニック一覧・詳細・メニュー・営業時間」を表示するためのプラグインです。

本プラグインは **Beauty Directory テーマ** と組み合わせて使用することを前提としています。

---

## 🔧 主な機能

- 美容クリニック情報のDB取得
- 施術メニューの紐付け表示
- 営業時間の曜日別表示
- ショートコードによる表示
- 絞り込み検索（エリア / メニュー名 / 価格）
- REST API による JSON 出力
- 独自DB構造を前提とした高速表示（$wpdb 使用）

---

## 🗄 使用しているデータベース構造

本プラグインは WordPress 標準テーブルではなく、  
以下の **独自テーブル** を使用します。

### 1. クリニック基本情報

```
wp_beauty___clinics
```

主なカラム：
- clinic_id (INT, クリニック識別子)
- name
- prefecture
- city
- station
- rating
- reviews_count
- clinic_url
- image_url
- その他 CSV 由来カラム

---

### 2. 施術メニュー

```
wp_beauty___menus
```

主なカラム：
- clinic_id (INT)
- menu_title
- price_jpy
- price_raw
- menu_img
- category_raw
- pickup_flag

---

### 3. 営業時間

```
wp_beauty___hours
```

主なカラム：
- clinic_id (INT)
- day（月 / 火 / 水 / 木 / 金 / 土 / 日）
- open_time
- close_time
- raw（例：10:00 ~ 19:00）

---

## 🔗 テーブルリレーション

```
wp_beauty___clinics.clinic_id
├─ wp_beauty___menus.clinic_id
└─ wp_beauty___hours.clinic_id
```

---

## 🧩 ショートコード一覧

### 🔍 クリニック検索・一覧表示

```
[beauty_clinic_search]
```

機能：
- 都道府県絞り込み
- メニュー名キーワード検索
- 最低 / 最高価格指定
- カード型UIで表示

---

### 🏥 クリニック詳細表示

```
[beauty_clinic_detail clinic_id="1"]
```

表示内容：
- クリニック基本情報
- 営業時間
- 施術メニュー一覧
- 公式サイトリンク

---

### 🎯 施術別カテゴリー表示

```
[beauty_treatment_categories]
```

機能：
- 施術カテゴリーボタン表示
- カテゴリー別クリニック検索

---

### 📍 都道府県別一覧表示

```
[beauty_prefecture_list]
```

機能：
- 都道府県別クリニック一覧
- 地域別検索

---

## 🌐 REST API エンドポイント

### クリニック一覧

```
GET /wp-json/beauty/v1/clinics
```

クエリ例：
```
/wp-json/beauty/v1/clinics?prefecture=東京都
/wp-json/beauty/v1/clinics?keyword=二重
```

---

### クリニック詳細

```
GET /wp-json/beauty/v1/clinics/{clinic_id}
```

レスポンス：
```json
{
  "clinic": { ... },
  "menus": [ ... ],
  "hours": [ ... ]
}
```

---

## 🚀 推奨利用環境

- WordPress 6.x 以上
- PHP 8.0 以上
- MySQL 5.7 / 8.0
- ConoHa WING / Xserver など

---

## ⚠ 注意事項

- 本プラグインは **独自DBテーブル前提** です
- データの作成・更新は CSV / GAS / 外部スクレイピングで行う想定
- 標準の WP 投稿・カスタム投稿タイプは使用しません
- テーブル名は `$wpdb->prefix` を前提にしています

---

## 👨‍💻 開発メモ

- 高速表示のため WP_Query は使用せず `$wpdb` を直接使用
- 大量データでもスケール可能
- API / テーマ / AI連携を想定した設計

---

## 📦 インストール方法

1. プラグインファイルを `/wp-content/plugins/beauty-clinic-lab/` にアップロード
2. WordPress管理画面の「プラグイン」から有効化
3. 独自DBテーブルを作成（別途SQL実行が必要）
4. テーマ「Beauty Directory」を有効化

---

## 🔄 連携テーマ

本プラグインは以下のテーマと連携して動作します：

- **Beauty Directory テーマ** (`beauty-clinic-lab`)
  - フロントページ
  - ブログアーカイブページ
  - 個別投稿ページ
  - クリニック詳細ページ

---

## 📝 ライセンス

GPL v2 or later

---

## 🙋 サポート

技術的な質問やバグ報告は、GitHubのIssuesまでお願いします。
