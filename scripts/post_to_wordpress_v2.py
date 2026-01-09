#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
2026年SEO完全準拠 WordPress投稿スクリプト v2.0
- カテゴリ管理
- 内部リンク戦略
- 構造化データ
"""

import os
import json
import requests
import base64
from datetime import datetime


class WordPressPublisher:
    def __init__(self):
        self.site_url = os.getenv('WP_SITE_URL', '')
        self.username = os.getenv('WP_USER', '')
        self.app_password = os.getenv('WP_APP_PASSWORD', '')
        
        if not all([self.site_url, self.username, self.app_password]):
            raise ValueError("WordPress環境変数が設定されていません")
        
        self.api_base = f"{self.site_url}/wp-json/wp/v2"
        self.auth_header = self._create_auth_header()
        
        # カテゴリマッピング
        self.category_mapping = {
            'medical-hair-removal': '医療脱毛',
            'double-eyelid-surgery': '二重整形',
            'skin-treatment': '美肌治療',
            'botox-injection': 'ボトックス注射',
            'hyaluronic-acid': 'ヒアルロン酸注射'
        }
    
    def _create_auth_header(self):
        """Basic認証ヘッダーを作成"""
        credentials = f"{self.username}:{self.app_password}"
        token = base64.b64encode(credentials.encode()).decode()
        return {
            'Authorization': f'Basic {token}',
            'Content-Type': 'application/json'
        }
    
    def get_or_create_category(self, category_slug):
        """カテゴリを取得または作成"""
        category_name = self.category_mapping.get(category_slug, '美容コラム')
        
        # 既存カテゴリを検索（全件取得）
        response = requests.get(
            f"{self.api_base}/categories",
            params={'per_page': 100},  # 最大100件取得
            headers=self.auth_header
        )
        
        if response.status_code == 200:
            categories = response.json()
            for cat in categories:
                if cat['name'] == category_name:
                    print(f"✓ カテゴリ取得: {category_name} (ID: {cat['id']})")
                    return cat['id']
        else:
            print(f"⚠ カテゴリ検索失敗: {response.status_code}")
        
        # 既存カテゴリが見つからない場合、新規作成
        print(f"⚠ カテゴリが見つからないため、新規作成します: {category_name}")
        
        response = requests.post(
            f"{self.api_base}/categories",
            headers=self.auth_header,
            json={'name': category_name}
        )
        
        if response.status_code == 201:
            category_id = response.json()['id']
            print(f"✓ カテゴリ作成: {category_name} (ID: {category_id})")
            return category_id
        else:
            print(f"✗ カテゴリ作成失敗: {category_name}")
            print(f"  ステータスコード: {response.status_code}")
            print(f"  レスポンス: {response.text[:500]}")
            return None
    
    def create_internal_links(self, article_data):
        """内部リンクを追加"""
        content = article_data['content']
        category_slug = article_data['category']
        category_name = article_data['category_name']
        
        # カテゴリページへのリンクを追加
        category_link = f'<p class="internal-link-box">📚 <a href="{self.site_url}/category/{category_slug}/">「{category_name}」の記事一覧を見る</a></p>'
        
        # コンテンツの最後に追加
        content_with_links = content + '\n\n' + category_link
        
        return content_with_links
    
    def add_schema_markup(self, article_data):
        """構造化データを追加"""
        schema = {
            "@context": "https://schema.org",
            "@type": "Article",
            "headline": article_data['title'],
            "articleSection": article_data['category_name'],
            "datePublished": datetime.now().isoformat(),
            "author": {
                "@type": "Organization",
                "name": "美容クリニックラボ"
            },
            "publisher": {
                "@type": "Organization",
                "name": "美容クリニックラボ"
            }
        }
        
        schema_html = f'<script type="application/ld+json">{json.dumps(schema, ensure_ascii=False)}</script>'
        
        return schema_html
    
    def publish_article(self, article_data):
        """記事をWordPressに投稿"""
        print("\n" + "="*60)
        print("WordPress投稿")
        print("="*60)
        
        # カテゴリIDを取得
        category_id = self.get_or_create_category(article_data['category'])
        
        if not category_id:
            print("✗ カテゴリの取得/作成に失敗しました")
            return None
        
        # 内部リンクを追加
        content_with_links = self.create_internal_links(article_data)
        
        # 構造化データを追加
        schema_markup = self.add_schema_markup(article_data)
        final_content = content_with_links + '\n\n' + schema_markup
        
        # 投稿データ
        post_data = {
            'title': article_data['title'],
            'content': final_content,
            'status': 'publish',
            'categories': [category_id],
            'meta': {
                'article_role': article_data['role'],
                'article_priority': article_data['priority']
            }
        }
        
        # 投稿
        print(f"\n✓ WordPressに投稿中...")
        response = requests.post(
            f"{self.api_base}/posts",
            headers=self.auth_header,
            json=post_data
        )
        
        if response.status_code == 201:
            post = response.json()
            print(f"✓ 投稿成功")
            print(f"  タイトル: {post['title']['rendered']}")
            print(f"  URL: {post['link']}")
            print(f"  ID: {post['id']}")
            return post
        else:
            print(f"✗ 投稿失敗")
            print(f"  ステータスコード: {response.status_code}")
            print(f"  レスポンス: {response.text[:200]}")
            return None


def main():
    import sys
    
    if len(sys.argv) < 2:
        print("使用方法: python post_to_wordpress_v2.py <article_json_file>")
        sys.exit(1)
    
    article_file = sys.argv[1]
    
    if not os.path.exists(article_file):
        print(f"エラー: ファイルが見つかりません: {article_file}")
        sys.exit(1)
    
    with open(article_file, 'r', encoding='utf-8') as f:
        article_data = json.load(f)
    
    publisher = WordPressPublisher()
    post = publisher.publish_article(article_data)
    
    if post:
        print("\n✓ WordPress投稿が完了しました")
    else:
        print("\n✗ WordPress投稿に失敗しました")
        sys.exit(1)


if __name__ == '__main__':
    main()
