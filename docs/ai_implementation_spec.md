# AI実装者向け技術仕様書

> **対象読者:** AI開発者、LLMエージェント、自動化システム構築者
> 
> 本書は、「WordPress向け自動SEOブログ生成システム」を新しい環境に実装する際に、AIが参照すべき技術仕様とコード構造を記載します。

---

## 1. システム概要

本システムは、GitHub Actions上でPythonスクリプトを定期実行し、Gemini APIを使用して高品質なSEO記事を生成し、WordPress REST APIを通じて自動投稿するシステムです。

### 1.1. 技術スタック

| レイヤー | 技術 | バージョン |
| :--- | :--- | :--- |
| 実行環境 | GitHub Actions | ubuntu-latest |
| プログラミング言語 | Python | 3.11 |
| LLM API | Google Gemini API | gemini-2.0-flash-exp |
| CMS | WordPress | 6.x以上 |
| 認証方式 | WordPress Application Password | - |
| データストア | JSON (ファイルベース) | - |

### 1.2. 依存ライブラリ

```bash
pip install openai requests
```

**注意:** `openai`ライブラリは、Gemini APIへのアクセスにも使用されます（OpenAI互換API）。

---

## 2. コアコンポーネント

### 2.1. `auto_blog_v3.py` - メインオーケストレーター

**役割:** 記事生成、WordPress投稿、データ更新の全体フローを制御します。

**主要な処理フロー:**

```python
def main():
    # 1. 記事生成
    generator = ArticleGenerator()
    article = generator.generate_article()
    
    # 2. WordPress投稿
    publisher = WordPressPublisher()
    post = publisher.publish_article(article)
    
    # 3. 内部リンクDB更新
    link_manager = InternalLinkManager()
    link_manager.add_article(article, post['link'])
    
    # 4. 履歴更新
    history_manager.update_history(article['category'])
```

**環境変数:**
- `OPENAI_API_KEY`: Gemini APIキー
- `WP_SITE_URL`: WordPressサイトURL
- `WP_USER`: WordPressユーザー名
- `WP_APP_PASSWORD`: アプリケーションパスワード

---

### 2.2. `generate_article_v3.py` - 記事生成エンジン

**役割:** 6ステップの品質管理プロセスで記事を生成します。

**6ステップの詳細:**

```python
class ArticleGenerator:
    def generate_article(self):
        # Step 1: 検索意図の定義
        intent_data = self.step1_define_intent(category, role)
        
        # Step 2: 見出し構造の設計
        outline = self.step2_design_outline(intent_data)
        
        # Step 3: セクション単位での本文生成
        sections = self.step3_generate_section(outline)
        
        # Step 4: 全文の統合
        full_article = self.step4_integrate_article(sections)
        
        # Step 5: 品質ゲート（自動審査）
        quality_score = self.step5_quality_gate(full_article)
        if quality_score < PASS_SCORE:
            raise QualityError("品質基準を満たしていません")
        
        # Step 6: SEO最適化パッケージ
        seo_package = self.step6_seo_optimization(full_article)
        
        return {
            "content": full_article,
            "seo": seo_package,
            "quality_score": quality_score
        }
```

**重要なプロンプト設計原則:**

1. **YMYL準拠**: 医療・金融などの分野では、客観性と根拠を重視します。
2. **差別化の明確化**: `seo_category_design.json`の`differentiation`フィールドをプロンプトに組み込みます。
3. **構造化**: 見出し構造を先に設計し、それに沿って本文を生成します。

---

### 2.3. `post_to_wordpress_v2.py` - WordPress投稿

**役割:** 生成された記事をWordPressに投稿し、内部リンクを挿入します。

**WordPress REST API エンドポイント:**

```python
# カテゴリー取得
GET {WP_SITE_URL}/wp-json/wp/v2/categories?slug={category_slug}

# 記事投稿
POST {WP_SITE_URL}/wp-json/wp/v2/posts
Headers:
  Authorization: Basic {base64(WP_USER:WP_APP_PASSWORD)}
Body:
  {
    "title": "記事タイトル",
    "content": "<p>HTML形式の本文</p>",
    "status": "publish",
    "categories": [49],
    "meta": {
      "description": "メタディスクリプション"
    }
  }
```

**内部リンク挿入ロジック:**

```python
def create_internal_links(self, article_data):
    content = article_data['content_html']
    
    # 内部リンクマネージャーを呼び出し
    content_with_links = self.link_manager.insert_internal_links(
        content, 
        article_data, 
        max_links=5
    )
    
    return content_with_links
```

---

### 2.4. `internal_link_manager.py` - 内部リンク自動化

**役割:** 投稿済み記事データベースを管理し、関連記事へのリンクを自動挿入します。

**データ構造 (`internal_links_db.json`):**

```json
{
  "articles": [
    {
      "id": "article_20260110_125843",
      "title": "医療脱毛クリニック選びの判断基準",
      "url": "https://example.com/article-slug/",
      "category": "医療脱毛",
      "keywords": ["医療脱毛", "クリニック選び", "判断基準"],
      "published_at": "2026-01-10T12:58:43"
    }
  ]
}
```

**リンク挿入アルゴリズム:**

1. 新しい記事のカテゴリーと内容を分析
2. `internal_links_db.json`から関連記事を検索
3. スコアリング（カテゴリー一致: +10点、キーワード一致: +5点）
4. スコアが高い順に、記事本文中のキーワードにリンクを挿入
5. 最大リンク数（デフォルト: 5）に達したら終了

**正規表現パターン（修正版）:**

```python
# 単語境界でマッチング
pattern = re.compile(r'\b(' + re.escape(keyword) + r')\b', re.IGNORECASE)

# リンク内かどうかをチェック
before_text = content[:start_pos]
last_a_open = before_text.rfind('<a ')
last_a_close = before_text.rfind('</a>')

# <a>の後に</a>がない場合はリンク内なのでスキップ
if last_a_open > last_a_close:
    continue
```

---

## 3. データファイル仕様

### 3.1. `seo_category_design.json`

**必須フィールド:**

```json
{
  "categories": [
    {
      "id": 1,
      "slug": "category-slug",
      "name": "カテゴリー名",
      "description": "説明",
      "target_articles": 15,
      "article_roles": [
        {
          "role": "役割名",
          "title_example": "タイトル例",
          "purpose": "目的",
          "differentiation": "差別化ポイント",  // 必須！
          "priority": 1
        }
      ]
    }
  ]
}
```

**注意:** `differentiation`フィールドがないと`KeyError`が発生します。

### 3.2. `article_history.json`

**構造:**

```json
{
  "last_category": "medical-hair-removal",
  "last_article_date": "2026-01-10",
  "category_progress": {
    "medical-hair-removal": {
      "total_articles": 2,
      "last_priority": 2
    }
  }
}
```

**役割:** カテゴリーローテーションと記事生成履歴を管理します。

---

## 4. GitHub Actions ワークフロー

### 4.1. 必須設定

**`.github/workflows/auto-blog-post-v3.yml`:**

```yaml
name: Auto Blog Post v3.0 - 6 Steps SEO

on:
  schedule:
    - cron: '0 1 * * 2,4,6'  # UTC 1:00 = JST 10:00
  workflow_dispatch:

permissions:
  contents: write  # 重要！これがないとプッシュできない

jobs:
  generate-and-post:
    runs-on: ubuntu-latest
    
    steps:
      - name: Checkout repository
        uses: actions/checkout@v4
      
      - name: Set up Python
        uses: actions/setup-python@v5
        with:
          python-version: '3.11'
      
      - name: Install dependencies
        run: pip install openai requests
      
      - name: Generate and post article
        env:
          OPENAI_API_KEY: ${{ secrets.OPENAI_API_KEY }}
          WP_SITE_URL: ${{ secrets.WP_SITE_URL }}
          WP_USER: ${{ secrets.WP_USER }}
          WP_APP_PASSWORD: ${{ secrets.WP_APP_PASSWORD }}
        run: |
          cd scripts
          python auto_blog_v3.py
      
      - name: Commit and push article data
        run: |
          git config --local user.email "github-actions[bot]@users.noreply.github.com"
          git config --local user.name "github-actions[bot]"
          git add data/
          git diff --quiet && git diff --staged --quiet || git commit -m "Auto: 記事生成 [$(date)]"
          git push
```

---

## 5. エラーハンドリング

### 5.1. 既知のエラーと修正方法

| エラー | 原因 | 修正コード |
| :--- | :--- | :--- |
| `KeyError: 'differentiation'` | `seo_category_design.json`に`differentiation`フィールドがない | すべての`article_roles`に追加 |
| `re.error: look-behind requires fixed-width pattern` | 可変長の否定後読み正規表現を使用 | `\b`による単語境界マッチングに変更 |
| `403 Forbidden` (GitHub push) | `permissions: contents: write`がない | ワークフローファイルに追加 |
| `401 Unauthorized` (WordPress) | アプリケーションパスワードが無効 | WordPressで再生成 |

---

## 6. カスタマイズポイント

### 6.1. 新しいサイトへの適用手順

1. **`seo_category_design.json`を全面的に書き換え**
   - サイトのテーマに合わせてカテゴリーと記事役割を設計
   - 必ず`differentiation`フィールドを含める

2. **プロンプトの調整（オプション）**
   - `generate_article_v3.py`の各ステップのプロンプトを、サイトのトーンに合わせて調整

3. **品質基準の調整（オプション）**
   - `PASS_SCORE`（デフォルト: 50点）を変更

4. **実行頻度の変更（オプション）**
   - ワークフローの`cron`を変更

---

## 7. 実装チェックリスト

- [ ] GitHubリポジトリを作成
- [ ] 必要なファイルをすべてコピー
- [ ] `seo_category_design.json`を編集（`differentiation`フィールド必須）
- [ ] GitHub Secretsに4つの環境変数を登録
- [ ] ワークフローに`permissions: contents: write`を追加
- [ ] WordPressでアプリケーションパスワードを生成
- [ ] GitHub Actionsで手動実行してテスト
- [ ] WordPressで記事が投稿されたか確認
- [ ] GitHub Actionsのログで品質スコアを確認

---

**ドキュメント作成者:** Manus AI  
**対象バージョン:** v3.0  
**最終更新日:** 2026年1月10日
