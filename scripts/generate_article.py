#!/usr/bin/env python3
"""
美容クリニック系ブログ記事自動生成スクリプト
OpenAI APIを使用してSEO最適化された記事を生成
"""

import os
import json
import random
from datetime import datetime, timedelta
from openai import OpenAI

class BeautyBlogGenerator:
    def __init__(self):
        self.client = OpenAI()
        self.keywords_file = os.path.join(os.path.dirname(__file__), '..', 'seo_keywords.json')
        self.history_file = os.path.join(os.path.dirname(__file__), '..', 'data', 'keyword_history.json')
        self.keywords_data = self.load_keywords()
        self.keyword_history = self.load_history()
        
    def load_keywords(self):
        """SEOキーワードデータを読み込む"""
        with open(self.keywords_file, 'r', encoding='utf-8') as f:
            return json.load(f)
    
    def load_history(self):
        """キーワード使用履歴を読み込む"""
        if os.path.exists(self.history_file):
            with open(self.history_file, 'r', encoding='utf-8') as f:
                return json.load(f)
        return {"used_keywords": []}
    
    def save_history(self):
        """キーワード使用履歴を保存"""
        os.makedirs(os.path.dirname(self.history_file), exist_ok=True)
        with open(self.history_file, 'w', encoding='utf-8') as f:
            json.dump(self.keyword_history, f, ensure_ascii=False, indent=2)
    
    def select_keyword(self):
        """
        使用するキーワードを選択
        - 最低3日間は同じキーワードを使用しない
        - 使用頻度ルールに従う
        """
        # 過去3日間に使用されたキーワードを取得
        three_days_ago = (datetime.now() - timedelta(days=3)).isoformat()
        recent_keywords = [
            item['keyword'] for item in self.keyword_history['used_keywords']
            if item['used_at'] > three_days_ago
        ]
        
        # 全キーワードをプールに追加
        keyword_pool = []
        
        # 高頻度キーワード（重み: 3）
        for category in ['施術別キーワード', '地域キーワード']:
            if category in self.keywords_data['keywords']:
                if isinstance(self.keywords_data['keywords'][category], dict):
                    for subcategory, kws in self.keywords_data['keywords'][category].items():
                        keyword_pool.extend([(kw, 'high') for kw in kws if kw not in recent_keywords])
                else:
                    keyword_pool.extend([(kw, 'high') for kw in self.keywords_data['keywords'][category] if kw not in recent_keywords])
        
        # 中頻度キーワード（重み: 2）
        for category in ['悩み別キーワード', 'ロングテールキーワード']:
            if category in self.keywords_data['keywords']:
                keyword_pool.extend([(kw, 'medium') for kw in self.keywords_data['keywords'][category] if kw not in recent_keywords])
        
        # 低頻度キーワード（重み: 1）
        for category in ['比較・検討キーワード', '季節・トレンドキーワード']:
            if category in self.keywords_data['keywords']:
                keyword_pool.extend([(kw, 'low') for kw in self.keywords_data['keywords'][category] if kw not in recent_keywords])
        
        if not keyword_pool:
            # 全てのキーワードが最近使用された場合、ランダムに選択
            all_keywords = []
            for category, items in self.keywords_data['keywords'].items():
                if isinstance(items, dict):
                    for subcategory, kws in items.items():
                        all_keywords.extend(kws)
                elif isinstance(items, list):
                    all_keywords.extend(items)
            return random.choice(all_keywords)
        
        # 重み付けランダム選択
        weights = {'high': 3, 'medium': 2, 'low': 1}
        weighted_pool = []
        for kw, freq in keyword_pool:
            weighted_pool.extend([kw] * weights[freq])
        
        selected_keyword = random.choice(weighted_pool)
        
        # 履歴に追加
        self.keyword_history['used_keywords'].append({
            'keyword': selected_keyword,
            'used_at': datetime.now().isoformat()
        })
        
        # 履歴を30日分に制限
        thirty_days_ago = (datetime.now() - timedelta(days=30)).isoformat()
        self.keyword_history['used_keywords'] = [
            item for item in self.keyword_history['used_keywords']
            if item['used_at'] > thirty_days_ago
        ]
        
        self.save_history()
        return selected_keyword
    
    def generate_article(self, keyword):
        """
        指定されたキーワードでSEO最適化された記事を生成
        """
        # 関連キーワードを取得
        related_keywords = self.get_related_keywords(keyword)
        
        prompt = f"""
あなたは美容クリニック専門のSEOライターです。以下の条件で高品質なブログ記事を作成してください。

【メインキーワード】
{keyword}

【関連キーワード（自然に記事内に含める）】
{', '.join(related_keywords[:5])}

【記事の要件】
1. タイトル: 魅力的でSEOに最適化された40-60文字のタイトル
2. 文字数: 2000-3000文字
3. 構成: 導入 → 本文（3-4セクション） → まとめ
4. トーン: 専門的でありながら親しみやすく、読者の悩みに寄り添う
5. SEO対策: メインキーワードを自然に5-8回、関連キーワードを適切に配置
6. 信頼性: 医療広告ガイドラインを遵守し、誇大表現を避ける
7. 読みやすさ: 見出し（h2, h3）を適切に使用し、段落を短く保つ

【避けるべき表現】
- 「絶対」「必ず」などの断定的表現
- 「最高」「No.1」などの最上級表現（根拠がない場合）
- 具体的な効果を保証する表現
- 他院を貶める表現

【記事フォーマット】
記事はMarkdown形式で出力してください。以下の構造に従ってください：

# [タイトル]

## はじめに
（導入文: 200-300文字）

## [見出し1]
（本文）

### [小見出し1-1]
（詳細）

## [見出し2]
（本文）

### [小見出し2-1]
（詳細）

## [見出し3]
（本文）

## まとめ
（まとめ: 200-300文字）

記事を作成してください。
"""
        
        response = self.client.chat.completions.create(
            model="gpt-4.1-mini",
            messages=[
                {"role": "system", "content": "あなたは美容クリニック業界に精通した、SEOに強い医療ライターです。医療広告ガイドラインを遵守し、読者に価値ある情報を提供します。"},
                {"role": "user", "content": prompt}
            ],
            temperature=0.7,
            max_tokens=4000
        )
        
        article_content = response.choices[0].message.content
        
        # タイトルを抽出
        lines = article_content.split('\n')
        title = lines[0].replace('# ', '').strip()
        
        return {
            'title': title,
            'content': article_content,
            'keyword': keyword,
            'related_keywords': related_keywords,
            'generated_at': datetime.now().isoformat()
        }
    
    def get_related_keywords(self, main_keyword):
        """メインキーワードに関連するキーワードを取得"""
        related = []
        
        # 全キーワードから関連性の高いものを抽出
        for category, items in self.keywords_data['keywords'].items():
            if isinstance(items, dict):
                for subcategory, kws in items.items():
                    if main_keyword in kws:
                        # 同じカテゴリーのキーワードを追加
                        related.extend([kw for kw in kws if kw != main_keyword])
            elif isinstance(items, list):
                if main_keyword in items:
                    related.extend([kw for kw in items if kw != main_keyword])
        
        # 地域キーワードを追加
        if '地域キーワード' in self.keywords_data['keywords']:
            related.extend(random.sample(self.keywords_data['keywords']['地域キーワード'], min(2, len(self.keywords_data['keywords']['地域キーワード']))))
        
        return related[:10]  # 最大10個
    
    def save_article(self, article_data):
        """生成された記事を保存"""
        # 記事保存ディレクトリ
        articles_dir = os.path.join(os.path.dirname(__file__), '..', 'data', 'articles')
        os.makedirs(articles_dir, exist_ok=True)
        
        # ファイル名を生成（日時ベース）
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        filename = f"article_{timestamp}.json"
        filepath = os.path.join(articles_dir, filename)
        
        # 記事データを保存
        with open(filepath, 'w', encoding='utf-8') as f:
            json.dump(article_data, f, ensure_ascii=False, indent=2)
        
        # Markdown形式でも保存
        md_filename = f"article_{timestamp}.md"
        md_filepath = os.path.join(articles_dir, md_filename)
        with open(md_filepath, 'w', encoding='utf-8') as f:
            f.write(article_data['content'])
        
        print(f"記事を保存しました: {filepath}")
        print(f"Markdown: {md_filepath}")
        
        return filepath

def main():
    """メイン処理"""
    generator = BeautyBlogGenerator()
    
    # キーワードを選択
    keyword = generator.select_keyword()
    print(f"選択されたキーワード: {keyword}")
    
    # 記事を生成
    print("記事を生成中...")
    article = generator.generate_article(keyword)
    
    # 記事を保存
    filepath = generator.save_article(article)
    
    print(f"\n=== 生成完了 ===")
    print(f"タイトル: {article['title']}")
    print(f"キーワード: {article['keyword']}")
    print(f"関連キーワード: {', '.join(article['related_keywords'][:5])}")
    print(f"保存先: {filepath}")
    
    return article

if __name__ == '__main__':
    main()
