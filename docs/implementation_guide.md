# WordPress向け自動SEOブログ生成システム 実装ガイド

> **ドキュメント概要**
> 
> 本書は、美容クリニック系サイトで開発された「自動SEOブログ生成システム」を、他のWordPressサイトに横展開（レプリケーション）するための技術ドキュメントです。AI開発者またはWebデベロッパーが本書を読むことで、システムの全体像を理解し、環境構築からカスタマイズ、運用までを行えるようになることを目的とします。

---

## 1. システムアーキテクチャ

本システムは、GitHub Actionsを起点とし、一連のPythonスクリプトを実行して高品質なSEO記事を生成、WordPressに自動投稿する仕組みです。主要なコンポーネントは以下の通りです。

```mermaid
graph TD
    subgraph GitHub Actions
        A[スケジュール実行<br>(cron: 火・木・土 10:00)] --> B{実行環境セットアップ};
    end

    subgraph Pythonスクリプト群
        B --> C[auto_blog_v3.py<br>(メインスクリプト)];
        C --> D[generate_article_v3.py<br>(記事生成エンジン)];
        C --> E[post_to_wordpress_v2.py<br>(WordPress投稿)];
        E --> F[internal_link_manager.py<br>(内部リンク自動化)];
    end

    subgraph データストア (JSON)
        G[seo_category_design.json<br>(SEO設計図)] <--> D;
        H[article_history.json<br>(投稿履歴)] <--> C;
        I[internal_links_db.json<br>(内部リンクDB)] <--> F;
    end

    subgraph 外部API
        J[Gemini API<br>(LLM)] <--> D;
        K[WordPress REST API] <--> E;
    end

    subgraph 成果物
        E --> L[WordPressサイト<br>(新規記事として投稿)];
        C --> M[GitHubリポジトリ<br>(生成データをコミット)];
    end
```

### 1.1. 処理フロー

1.  **トリガー**: GitHub Actionsがスケジュール（または手動）でワークフローを開始します。
2.  **記事生成**: `auto_blog_v3.py`が`generate_article_v3.py`を呼び出し、`seo_category_design.json`と`article_history.json`に基づいて次の記事テーマを決定します。
3.  **品質管理**: `generate_article_v3.py`がGemini APIと通信し、6ステップの品質管理プロセスを経て、SEOに最適化されたMarkdown形式の記事を生成します。
4.  **WordPress投稿**: `auto_blog_v3.py`が`post_to_wordpress_v2.py`を呼び出し、生成された記事をHTMLに変換してWordPressに投稿します。
5.  **内部リンク挿入**: `post_to_wordpress_v2.py`が`internal_link_manager.py`を呼び出し、`internal_links_db.json`を参照して、記事内に自動で内部リンクを挿入します。
6.  **データ更新**: `auto_blog_v3.py`が`article_history.json`と`internal_links_db.json`を更新し、変更をGitHubリポジトリにプッシュします。

## 2. 機能一覧

本システムが提供する主要な機能は以下の通りです。

| 機能分類 | 機能名 | 詳細説明 |
| :--- | :--- | :--- |
| **記事生成** | キーワード・テーマ自動選定 | `seo_category_design.json`に基づき、カテゴリーと記事の役割（基礎知識、比較、etc.）を自動でローテーションします。 |
| | 6ステップ品質管理 | Gemini APIを活用し、検索意図定義→見出し設計→本文生成→統合→品質ゲート→SEO最適化の6段階で高品質な記事を生成します。 |
| | YMYL準拠の安全性 | 医療・金融などのYMYL分野に対応するため、客観性、網羅性、専門性を重視したプロンプト設計になっています。 |
| **SEO最適化** | 内部リンク自動化 | 投稿済み記事データベース`internal_links_db.json`を元に、関連キーワードを自動で検出し、適切なアンカーテキストで内部リンクを挿入します。 |
| | SEOパッケージ | 生成記事ごとに、最適なタイトル案（5つ）、メタディスクリプション、FAQリスト、JSON-LD（構造化データ）を自動生成します。 |
| **WordPress連携** | 自動投稿システム | WordPressのREST API（アプリケーションパスワード認証）を利用し、カテゴリー設定、タグ設定、アイキャッチ画像設定を含めて自動投稿します。 |
| | アフィリエイトバナー管理 | WordPressの固定ページを管理画面として利用し、アフィリエイトバナーの登録・編集・削除が可能です。ショートコードで記事内に挿入できます。 |
| **自動化** | GitHub Actionsによる完全自動実行 | 毎週火・木・土の午前10時（JST）にシステム全体を自動実行し、記事生成から投稿、データ更新までを無人で行います。 |


## 3. 環境構築とファイル構成

本システムを新しい環境で動作させるための手順と、必要なファイル構成について説明します。

### 3.1. 前提条件

-   **GitHubアカウント**: ソースコード管理とGitHub Actions実行に必要です。
-   **WordPressサイト**: 記事を投稿する対象のサイト。REST APIが利用可能である必要があります。
-   **Gemini APIキー**: Google AI Studioから取得可能なAPIキー。記事生成に使用します。

### 3.2. ファイル構成

プロジェクトは以下のディレクトリ構造を基本とします。

```
beauty-clinic/
├── .github/
│   └── workflows/
│       └── auto-blog-post-v3.yml  # GitHub Actionsワークフロー
├── data/
│   ├── articles/                  # 生成された記事（JSON, Markdown）
│   ├── article_history.json       # カテゴリーローテーション履歴
│   └── internal_links_db.json     # 内部リンク用記事データベース
├── scripts/
│   ├── auto_blog_v3.py            # メインスクリプト
│   ├── generate_article_v3.py     # 記事生成エンジン
│   ├── post_to_wordpress_v2.py    # WordPress投稿スクリプト
│   └── internal_link_manager.py   # 内部リンク管理スクリプト
└── seo_category_design.json       # SEO戦略と記事テーマの設計図
```

### 3.3. 環境変数（GitHub Secrets）

GitHubリポジトリの `Settings` > `Secrets and variables` > `Actions` に、以下の4つのシークレットを登録します。

| シークレット名 | 内容 | 取得方法 |
| :--- | :--- | :--- |
| `OPENAI_API_KEY` | Gemini APIのAPIキー | Google AI Studioの「Get API key」から取得します。 |
| `WP_SITE_URL` | WordPressサイトのURL | 例: `https://example.com` |
| `WP_USER` | WordPressのユーザー名 | 投稿に使用する管理者または編集者権限のユーザー名。 |
| `WP_APP_PASSWORD` | アプリケーションパスワード | WordPressのユーザープロフィール画面で生成します（後述）。 |

### 3.4. WordPress側の設定

1.  **アプリケーションパスワードの生成**
    -   WordPressの管理画面にログインします。
    -   `ユーザー` > `プロフィール` に移動します。
    -   「アプリケーションパスワード」セクションまでスクロールします。
    -   「新しいアプリケーションパスワード名」に `GitHub_Actions` などの分かりやすい名前を入力し、「新しいアプリケーションパスワードを追加」をクリックします。
    -   表示されたパスワード（例: `abcd efgh ijkl mnop qrst uvwx`）をコピーし、GitHubの`WP_APP_PASSWORD`シークレットに登録します。**このパスワードは一度しか表示されないため、必ず控えてください。**

2.  **（オプション）アフィリエイトバナー管理機能の実装**
    -   `page-affiliate-manager.php` のような固定ページテンプレートをテーマ内に作成します。
    -   WordPressで新規固定ページを作成し、このテンプレートを適用することで、バナー管理画面として機能します。
    -   この機能は独立しているため、ブログ自動生成システムのみを利用する場合は不要です。


## 4. スクリプト詳細とカスタマイズ

各スクリプトと設定ファイルの役割、そして新しいサイトに合わせてカスタマイズする方法について解説します。

### 4.1. `seo_category_design.json` - SEO戦略の設計図

このファイルは、**本システムの最も重要な設定ファイル**です。どのようなカテゴリーで、どのような役割の記事を、どれくらいの量生成するかを定義します。

-   **`categories`**: サイトの主要カテゴリーを定義します。
    -   `id`: カテゴリーID（連番）。
    -   `slug`: WordPressのカテゴリースラッグ。
    -   `name`: カテゴリー名。
    -   `description`: カテゴリーの概要。
    -   `target_articles`: このカテゴリーで生成する目標記事数。
    -   `article_roles`: そのカテゴリー内で生成する記事の「役割」を定義します。
        -   `role`: 記事の役割名（例: `基礎知識（エース記事）`）。
        -   `title_example`: 生成される記事タイトルの例。
        -   `purpose`: この役割の記事が誰に何を伝えるためのものか。
        -   `differentiation`: 他の記事との差別化ポイント。プロンプトの重要な要素になります。
        -   `priority`: 生成される優先順位。1が最も高い。

> **【カスタマイズのポイント】**
> 新しいサイトに導入する際は、まずこのファイルの`categories`を、そのサイトのテーマに合わせて全面的に書き換える必要があります。ここでの設計が、生成されるすべての記事の品質と方向性を決定します。

### 4.2. `generate_article_v3.py` - 記事生成エンジン

Gemini APIと通信し、高品質な記事を生成する心臓部です。6つのステップで構成されています。

1.  **ステップ1: 検索意図の定義**: `seo_category_design.json`の定義に基づき、記事のゴールを明確化します。
2.  **ステップ2: 見出し構造の設計**: ゴールに沿ったH2・H3からなる見出し構成案を作成します。
3.  **ステップ3: 本文生成**: 各見出しごとに、詳細なプロンプトで本文を生成させます。
4.  **ステップ4: 全文の統合**: 生成された各セクションを統合し、自然な流れになるよう整形します。
5.  **ステップ5: 品質ゲート**: 60点満点の自動スコアリングで品質を評価。50点未満の場合は処理を中断します。
6.  **ステップ6: SEO最適化**: タイトル案、メタディスクリプション、FAQ、JSON-LDを生成します。

> **【カスタマイズのポイント】**
> - **プロンプトの調整**: `step1_define_intent`や`step3_generate_section`内のプロンプトを、サイトのトーン＆マナーや専門分野に合わせて調整することで、生成される記事の質をさらに高めることができます。
> - **品質基準の変更**: `step5_quality_gate`のスコアリング基準や合格点（`PASS_SCORE`）を変更することで、品質管理の厳格さを調整できます。

### 4.3. `internal_link_manager.py` - 内部リンク自動化

投稿済みの記事データベース（`internal_links_db.json`）を元に、本文中のキーワードに自動でリンクを挿入します。

-   **`insert_internal_links`**: メインの処理関数です。
    -   スコアが高い（関連性が高い）記事から順に、リンク挿入を試みます。
    -   1つの記事に挿入する最大リンク数を`max_links`で制御できます。
    -   すでにリンクが含まれているキーワードや、HTMLの`<a>`タグ内にあるキーワードは除外するロジックが含まれています。

> **【カスタマイズのポイント】**
> - **最大リンク数の調整**: `post_to_wordpress_v2.py`内の`create_internal_links`メソッドで呼び出す際に`max_links`の値を変更することで、1記事あたりの内部リンク数を調整できます。
> - **キーワードのマッチング**: `insert_internal_links`内の正規表現`pattern`を調整することで、単語境界の扱いなどを変更できます。

### 4.4. `.github/workflows/auto-blog-post-v3.yml` - 自動実行ワークフロー

GitHub Actionsの設定ファイルです。

-   **`on.schedule.cron`**: 実行スケジュールを定義します。デフォルトは`'0 1 * * 2,4,6'`（UTC）で、日本時間の火・木・土の午前10時に相当します。

> **【カスタマイズのポイント】**
> - **実行頻度の変更**: `cron`の値を変更することで、実行タイミングを自由に変更できます。例えば、毎日午前9時に実行する場合は`'0 0 * * *'`（UTC）とします。
> - **Python依存関係の追加**: `pip install`セクションに、もし追加で必要なライブラリがあれば追記します。
_content

## 5. トラブルシューティングとベストプラクティス

### 5.1. よくあるエラーと対処法

| エラーメッセージ | 原因 | 対処法 |
| :--- | :--- | :--- |
| `KeyError: 'differentiation'` | `seo_category_design.json`内の`article_roles`に`differentiation`フィールドが欠けている。 | すべての`article_roles`オブジェクトに`"differentiation": "具体的な差別化ポイント"`を追加します。 |
| `re.error: look-behind requires fixed-width pattern` | `internal_link_manager.py`で、Pythonの`re`モジュールがサポートしていない可変長の否定後読み正規表現を使用している。 | 正規表現を単純な単語境界マッチ（`\b`）に変更し、リンク内かどうかの判定はPythonの文字列検索（`rfind`）で行うようにロジックを修正します。 |
| `remote: Permission to ... denied to github-actions[bot].` (403 Forbidden) | GitHub Actionsのワークフローにリポジトリへの書き込み権限がない。 | `.github/workflows/auto-blog-post-v3.yml`に`permissions: contents: write`を追加します。 |
| `WordPress post failed: 401 Unauthorized` | WordPressのアプリケーションパスワードが無効、またはユーザー名が間違っている。 | WordPressでアプリケーションパスワードを再生成し、GitHubの`WP_APP_PASSWORD`と`WP_USER`シークレットを更新します。 |
| `[内部リンク自動挿入] ⚠ 内部リンクを挿入できませんでした` | 記事本文中に、`internal_links_db.json`に登録されているキーワードが見つからなかった。 | これはエラーではありません。記事が蓄積されれば自然に解決します。キーワードのマッチング条件が厳しすぎる場合は、`internal_link_manager.py`のロジックを調整します。 |

### 5.2. ベストプラクティス

-   **設計こそが最重要**: システムの出力品質は、9割が`seo_category_design.json`の設計で決まります。時間をかけて、ターゲット読者とサイトの目的に合ったカテゴリー・記事役割を設計してください。
-   **スモールスタート**: 最初からすべてのカテゴリーを定義するのではなく、まず1つの重要なカテゴリー（10〜15記事）を完璧に設計し、そのカテゴリーの記事がすべて生成されるまで様子を見ます。
-   **品質ゲートの監視**: GitHub Actionsの実行ログで、品質ゲートのスコア（例: `総合点: 53/60点`）を定期的に確認します。スコアが低い記事が続く場合は、プロンプトや`seo_category_design.json`の見直しを検討します。
-   **手動実行によるテスト**: 新しいカテゴリーを追加したり、プロンプトを大幅に変更した場合は、GitHub Actionsの`workflow_dispatch`（手動実行）を使ってすぐに結果をテストします。
-   **生成データの定期的なバックアップ**: `data`ディレクトリはリポジトリで管理されますが、万が一に備え、定期的にローカルにもバックアップを取ることを推奨します。

---

**ドキュメント作成者:** Manus AI
**作成日:** 2026年1月10日
