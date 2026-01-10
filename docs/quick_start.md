# クイックスタートガイド - 30分で始める自動ブログ生成

> **このガイドの目的**
> 
> 本ガイドは、最短30分で「WordPress向け自動SEOブログ生成システム」を新しいサイトに導入し、最初の記事を生成するまでの手順を示します。詳細な技術仕様は`implementation_guide.md`を参照してください。

---

## ステップ1: GitHubリポジトリの準備（5分）

1. GitHubで新しいリポジトリを作成します（例: `my-auto-blog`）。
2. 以下のディレクトリ構造を作成します：

```
my-auto-blog/
├── .github/workflows/
├── data/
├── scripts/
└── docs/
```

3. 元のリポジトリ（`beauty-clinic`）から以下のファイルをコピーします：
   - `.github/workflows/auto-blog-post-v3.yml`
   - `scripts/auto_blog_v3.py`
   - `scripts/generate_article_v3.py`
   - `scripts/post_to_wordpress_v2.py`
   - `scripts/internal_link_manager.py`
   - `seo_category_design.json`（後で編集）

---

## ステップ2: `seo_category_design.json` の編集（10分）

このファイルが最も重要です。あなたのサイトのテーマに合わせて編集します。

### 例: 不動産投資ブログの場合

```json
{
  "seo_strategy": {
    "principle": "評価の集中・量より質・スパムポリシー完全準拠",
    "posting_frequency": "週2-3回（火・木・土）",
    "article_lifecycle": "10-30記事でカテゴリ完成 → 次カテゴリへ"
  },
  
  "categories": [
    {
      "id": 1,
      "slug": "real-estate-investment-basics",
      "name": "不動産投資の基礎",
      "description": "不動産投資の基礎知識・始め方・リスク管理",
      "search_intent": "不動産投資を検討している初心者が知りたい情報",
      "target_articles": 12,
      "article_roles": [
        {
          "role": "基礎知識（エース記事）",
          "title_example": "不動産投資とは？初心者が知るべき基礎知識と始め方",
          "purpose": "初心者向け総合ガイド",
          "differentiation": "網羅的だが分かりやすさ重視",
          "priority": 1
        },
        {
          "role": "物件選びの判断基準",
          "title_example": "失敗しない不動産投資物件の選び方｜5つのチェックポイント",
          "purpose": "比較検討段階の読者向け",
          "differentiation": "独自の評価軸・判断基準",
          "priority": 2
        }
      ]
    }
  ],
  
  "posting_schedule": {
    "frequency": "週2-3回（火・木・土の10:00）",
    "category_rotation": "1カテゴリを10-30記事で完成させてから次へ",
    "current_category": "real-estate-investment-basics",
    "progress_tracking": true
  }
}
```

**重要:** 各`article_roles`に必ず`differentiation`フィールドを含めてください。

---

## ステップ3: GitHub Secretsの設定（5分）

GitHubリポジトリの `Settings` > `Secrets and variables` > `Actions` で、以下の4つを登録します：

| シークレット名 | 値の例 |
| :--- | :--- |
| `OPENAI_API_KEY` | `AIzaSyD...` (Gemini APIキー) |
| `WP_SITE_URL` | `https://example.com` |
| `WP_USER` | `admin` |
| `WP_APP_PASSWORD` | `abcd efgh ijkl mnop qrst uvwx` |

**WordPressのアプリケーションパスワード取得方法:**
1. WordPressにログイン
2. `ユーザー` > `プロフィール`
3. 「アプリケーションパスワード」セクションで新規作成
4. 表示されたパスワードをコピー

---

## ステップ4: ワークフローに権限を追加（2分）

`.github/workflows/auto-blog-post-v3.yml` の先頭（`on:`の前）に以下を追加：

```yaml
permissions:
  contents: write
```

これにより、GitHub Actionsがリポジトリにファイルをプッシュできるようになります。

---

## ステップ5: 初回実行（5分）

1. GitHubリポジトリの `Actions` タブに移動
2. `Auto Blog Post v3.0 - 6 Steps SEO` ワークフローを選択
3. `Run workflow` ボタンをクリック
4. 実行が完了するまで待機（通常2〜3分）

---

## ステップ6: 結果の確認（3分）

1. **WordPressで記事を確認:**
   - WordPressの管理画面 > `投稿` > `投稿一覧`
   - 新しい記事が投稿されているはずです

2. **GitHub Actionsのログを確認:**
   - 実行ログで品質スコア（例: `✓ 総合点: 53/60点`）を確認
   - エラーがないかチェック

3. **生成ファイルを確認:**
   - リポジトリの`data/articles/`に、JSON形式とMarkdown形式の記事が保存されています

---

## 次のステップ

- **自動実行の設定:** ワークフローは毎週火・木・土の午前10時（JST）に自動実行されます。
- **カテゴリーの追加:** `seo_category_design.json`に新しいカテゴリーを追加することで、記事のバリエーションを増やせます。
- **プロンプトの調整:** `generate_article_v3.py`のプロンプトを編集することで、記事のトーンや専門性を調整できます。

---

**ドキュメント作成者:** Manus AI
**作成日:** 2026年1月10日
