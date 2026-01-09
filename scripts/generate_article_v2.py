#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
2026年SEO完全準拠 記事生成スクリプト v2.0
- 役割明確化
- 差別化
- 重複防止
- 評価の集中
"""

import os
import json
import datetime
from openai import OpenAI

class SEOArticleGenerator:
    def __init__(self):
        self.client = OpenAI()
        self.model = "gpt-4.1-mini"
        
        # カテゴリ設計を読み込み
        with open('../seo_category_design.json', 'r', encoding='utf-8') as f:
            self.seo_design = json.load(f)
        
        # 記事履歴を読み込み
        self.history_file = '../data/article_history.json'
        self.load_history()
    
    def load_history(self):
        """記事生成履歴を読み込み"""
        if os.path.exists(self.history_file):
            with open(self.history_file, 'r', encoding='utf-8') as f:
                self.history = json.load(f)
        else:
            self.history = {
                'articles': [],
                'current_category': 'medical-hair-removal',
                'category_progress': {}
            }
    
    def save_history(self):
        """記事生成履歴を保存"""
        os.makedirs(os.path.dirname(self.history_file), exist_ok=True)
        with open(self.history_file, 'w', encoding='utf-8') as f:
            json.dump(self.history, f, ensure_ascii=False, indent=2)
    
    def get_current_category(self):
        """現在のカテゴリを取得（ローテーション方式）"""
        current_slug = self.history.get('current_category', 'medical-hair-removal')
        
        for category in self.seo_design['categories']:
            if category['slug'] == current_slug:
                return category
        
        return self.seo_design['categories'][0]
    
    def switch_to_next_category_rotation(self):
        """次のカテゴリにローテーション（毎回切り替え）"""
        current_slug = self.history['current_category']
        categories = self.seo_design['categories']
        
        for i, cat in enumerate(categories):
            if cat['slug'] == current_slug:
                next_index = (i + 1) % len(categories)
                self.history['current_category'] = categories[next_index]['slug']
                print(f"→ 次のカテゴリ: {categories[next_index]['name']}")
                return
    
    def get_next_article_role(self, category):
        """次に生成する記事の役割を取得"""
        category_slug = category['slug']
        
        # カテゴリの進捗を確認
        if category_slug not in self.history['category_progress']:
            self.history['category_progress'][category_slug] = {
                'completed_roles': [],
                'article_count': 0
            }
        
        progress = self.history['category_progress'][category_slug]
        completed_roles = set(progress['completed_roles'])
        
        # 優先順位順に未完了の役割を探す
        for role in category['article_roles']:
            role_key = f"{role['role']}_{role['priority']}"
            if role_key not in completed_roles:
                return role
        
        # すべての役割が完了している場合は次のカテゴリへ
        return None
    
    def switch_to_next_category(self):
        """次のカテゴリに切り替え"""
        current_slug = self.history['current_category']
        categories = self.seo_design['categories']
        
        for i, cat in enumerate(categories):
            if cat['slug'] == current_slug:
                next_index = (i + 1) % len(categories)
                self.history['current_category'] = categories[next_index]['slug']
                print(f"✓ カテゴリ完了: {cat['name']}")
                print(f"→ 次のカテゴリ: {categories[next_index]['name']}")
                return
    
    def check_differentiation(self, category, role, title):
        """既存記事との差別化をチェック"""
        category_slug = category['slug']
        
        # 同カテゴリの既存記事を取得
        existing_articles = [
            art for art in self.history['articles']
            if art.get('category') == category_slug
        ]
        
        # タイトルの類似度チェック（簡易版）
        for article in existing_articles:
            existing_title = article.get('title', '')
            # 8割以上の単語が重複している場合は警告
            title_words = set(title.split())
            existing_words = set(existing_title.split())
            if len(title_words) > 0:
                overlap = len(title_words & existing_words) / len(title_words)
                if overlap > 0.8:
                    return False, f"既存記事と類似: {existing_title}"
        
        return True, "OK"
    
    def generate_article(self):
        """SEO準拠の記事を生成"""
        print("="*60)
        print("2026年SEO準拠 記事生成システム v2.0")
        print("="*60)
        
        # 現在のカテゴリを取得
        category = self.get_current_category()
        print(f"\n現在のカテゴリ: {category['name']}")
        
        # 次の記事役割を取得
        role = self.get_next_article_role(category)
        
        if role is None:
            print(f"✓ カテゴリ「{category['name']}」の記事が完了しました")
            self.switch_to_next_category()
            self.save_history()
            
            # 次のカテゴリで再実行
            category = self.get_current_category()
            role = self.get_next_article_role(category)
        
        print(f"\n記事の役割: {role['role']}")
        print(f"優先順位: {role['priority']}")
        print(f"差別化ポイント: {role['differentiation']}")
        
        # プロンプト作成
        prompt = self._create_prompt(category, role)
        
        # AI生成
        print("\n✓ AI記事生成中...")
        response = self.client.chat.completions.create(
            model=self.model,
            messages=[
                {
                    "role": "system",
                    "content": self._get_system_prompt()
                },
                {
                    "role": "user",
                    "content": prompt
                }
            ],
            temperature=0.7,
            max_tokens=4000
        )
        
        content = response.choices[0].message.content
        
        # 記事情報を抽出
        article_data = self._parse_article(content, category, role)
        
        # 差別化チェック
        is_ok, message = self.check_differentiation(
            category, role, article_data['title']
        )
        
        if not is_ok:
            print(f"\n⚠ 差別化チェック失敗: {message}")
            print("記事生成を中止します")
            return None
        
        # 履歴に追加
        self.history['articles'].append({
            'title': article_data['title'],
            'category': category['slug'],
            'role': role['role'],
            'priority': role['priority'],
            'generated_at': datetime.datetime.now().isoformat(),
            'word_count': len(article_data['content'])
        })
        
        # カテゴリ進捗を更新
        category_slug = category['slug']
        role_key = f"{role['role']}_{role['priority']}"
        self.history['category_progress'][category_slug]['completed_roles'].append(role_key)
        self.history['category_progress'][category_slug]['article_count'] += 1
        
        # 次回のためにカテゴリをローテーション
        self.switch_to_next_category_rotation()
        
        # 履歴を保存
        self.save_history()
        
        print(f"\n✓ 記事生成完了")
        print(f"  タイトル: {article_data['title']}")
        print(f"  文字数: {len(article_data['content'])}文字")
        print(f"  カテゴリ進捗: {self.history['category_progress'][category_slug]['article_count']}/{category['target_articles']}記事")
        
        return article_data
    
    def _get_system_prompt(self):
        """システムプロンプト"""
        return """あなたは2026年のGoogle検索アルゴリズムとスパムポリシーを理解した、
WordPressメディア／アフィリエイト専門のSEO編集長です。

以下の制約を厳守してください：

【記事作成ルール】
- 1記事 = 1明確な役割
- 同カテゴリ内で他記事と役割が被らない
- 構成テンプレートの完全一致は禁止
- 「おすすめ◯選」の乱発は禁止
- 単なる要約・言い換え記事は禁止

【AI生成コンテンツの条件】
- 他記事との差別化ポイントを明示
- 実体験風ではなく「判断・分析・比較」を重視
- 結論を急がず、根拠を構造的に提示
- 検索意図を1つに絞る（詰め込み禁止）
- 「SEO用文章」に見える表現は禁止

【出力フォーマット】
以下の形式で出力してください：

---TITLE---
（記事タイトル）

---META---
この記事の役割: （役割説明）
狙う検索意図: （検索意図）
差別化ポイント: （差別化ポイント）

---CONTENT---
（記事本文・HTML形式）
"""
    
    def _create_prompt(self, category, role):
        """記事生成プロンプト"""
        # 既存記事のタイトルリスト
        existing_titles = [
            art['title'] for art in self.history['articles']
            if art.get('category') == category['slug']
        ]
        
        existing_titles_text = "\n".join(f"- {title}" for title in existing_titles) if existing_titles else "（まだ記事がありません）"
        
        prompt = f"""
【カテゴリ情報】
カテゴリ名: {category['name']}
説明: {category['description']}
検索意図: {category['search_intent']}

【記事の役割】
役割: {role['role']}
目的: {role['purpose']}
差別化ポイント: {role['differentiation']}
優先順位: {role['priority']}

【タイトル例】
{role['title_example']}

【同カテゴリの既存記事】
{existing_titles_text}

【指示】
上記の役割に基づき、SEO最適化された記事を作成してください。

- タイトルは検索意図を明確に反映
- 見出し構造（H2, H3）を論理的に
- 2000〜3000文字程度
- 既存記事との重複を避ける
- 判断基準・分析・比較を重視
- 具体的な情報を提供

出力フォーマットに従って記事を作成してください。
"""
        return prompt
    
    def _parse_article(self, content, category, role):
        """生成された記事をパース"""
        lines = content.split('\n')
        
        title = ""
        meta_info = {}
        article_content = []
        
        current_section = None
        
        for line in lines:
            if line.strip() == '---TITLE---':
                current_section = 'title'
                continue
            elif line.strip() == '---META---':
                current_section = 'meta'
                continue
            elif line.strip() == '---CONTENT---':
                current_section = 'content'
                continue
            
            if current_section == 'title' and line.strip():
                title = line.strip()
            elif current_section == 'meta' and line.strip():
                if ':' in line:
                    key, value = line.split(':', 1)
                    meta_info[key.strip()] = value.strip()
            elif current_section == 'content':
                article_content.append(line)
        
        return {
            'title': title or role['title_example'],
            'content': '\n'.join(article_content).strip(),
            'meta': meta_info,
            'category': category['slug'],
            'category_name': category['name'],
            'role': role['role'],
            'priority': role['priority']
        }
    
    def save_article(self, article_data):
        """記事をファイルに保存"""
        timestamp = datetime.datetime.now().strftime('%Y%m%d_%H%M%S')
        
        # JSON形式で保存
        json_filename = f"../data/articles/article_{timestamp}.json"
        os.makedirs(os.path.dirname(json_filename), exist_ok=True)
        
        with open(json_filename, 'w', encoding='utf-8') as f:
            json.dump(article_data, f, ensure_ascii=False, indent=2)
        
        # Markdown形式で保存
        md_filename = f"../data/articles/article_{timestamp}.md"
        md_content = f"""# {article_data['title']}

**カテゴリ**: {article_data['category_name']}
**役割**: {article_data['role']}
**優先順位**: {article_data['priority']}

---

{article_data['content']}
"""
        
        with open(md_filename, 'w', encoding='utf-8') as f:
            f.write(md_content)
        
        print(f"\n記事を保存しました: {json_filename}")
        print(f"Markdown: {md_filename}")
        
        return json_filename, md_filename


def main():
    generator = SEOArticleGenerator()
    article = generator.generate_article()
    
    if article:
        generator.save_article(article)
        print("\n✓ 記事生成が完了しました")
    else:
        print("\n✗ 記事生成に失敗しました")


if __name__ == '__main__':
    main()
