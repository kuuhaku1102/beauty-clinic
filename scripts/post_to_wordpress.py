#!/usr/bin/env python3
"""
WordPress自動投稿スクリプト
生成された記事をWordPress REST APIを使用して投稿
"""

import os
import sys
import json
import base64
import requests
from datetime import datetime

class WordPressPublisher:
    def __init__(self):
        # 環境変数から認証情報を取得
        self.site_url = os.getenv('WP_SITE_URL')
        self.username = os.getenv('WP_USER')
        self.app_password = os.getenv('WP_APP_PASSWORD')
        
        if not all([self.site_url, self.username, self.app_password]):
            raise ValueError("WordPress認証情報が設定されていません。環境変数を確認してください。")
        
        # REST API エンドポイント
        self.api_url = f"{self.site_url.rstrip('/')}/wp-json/wp/v2"
        
        # 認証ヘッダー
        credentials = f"{self.username}:{self.app_password}"
        token = base64.b64encode(credentials.encode()).decode()
        self.headers = {
            'Authorization': f'Basic {token}',
            'Content-Type': 'application/json'
        }
    
    def convert_markdown_to_html(self, markdown_content):
        """
        MarkdownをWordPress用のHTMLに変換
        簡易的な変換（見出し、段落のみ）
        """
        lines = markdown_content.split('\n')
        html_lines = []
        in_paragraph = False
        
        for line in lines:
            line = line.strip()
            
            if not line:
                if in_paragraph:
                    html_lines.append('</p>')
                    in_paragraph = False
                continue
            
            # 見出しの変換
            if line.startswith('# '):
                if in_paragraph:
                    html_lines.append('</p>')
                    in_paragraph = False
                html_lines.append(f'<h1>{line[2:]}</h1>')
            elif line.startswith('## '):
                if in_paragraph:
                    html_lines.append('</p>')
                    in_paragraph = False
                html_lines.append(f'<h2>{line[3:]}</h2>')
            elif line.startswith('### '):
                if in_paragraph:
                    html_lines.append('</p>')
                    in_paragraph = False
                html_lines.append(f'<h3>{line[4:]}</h3>')
            elif line.startswith('#### '):
                if in_paragraph:
                    html_lines.append('</p>')
                    in_paragraph = False
                html_lines.append(f'<h4>{line[5:]}</h4>')
            else:
                # 段落
                if not in_paragraph:
                    html_lines.append('<p>')
                    in_paragraph = True
                html_lines.append(line)
        
        if in_paragraph:
            html_lines.append('</p>')
        
        return '\n'.join(html_lines)
    
    def create_post(self, title, content, status='publish', categories=None, tags=None):
        """
        WordPress投稿を作成
        
        Args:
            title: 投稿タイトル
            content: 投稿内容（Markdown形式）
            status: 投稿ステータス（'publish', 'draft', 'pending'）
            categories: カテゴリーIDのリスト
            tags: タグIDのリスト
        
        Returns:
            投稿データ
        """
        # MarkdownをHTMLに変換
        html_content = self.convert_markdown_to_html(content)
        
        # 投稿データ
        post_data = {
            'title': title,
            'content': html_content,
            'status': status,
            'format': 'standard'
        }
        
        if categories:
            post_data['categories'] = categories
        
        if tags:
            post_data['tags'] = tags
        
        # 投稿を作成
        response = requests.post(
            f"{self.api_url}/posts",
            headers=self.headers,
            json=post_data
        )
        
        if response.status_code == 201:
            post = response.json()
            print(f"✓ 投稿が正常に作成されました")
            print(f"  ID: {post['id']}")
            print(f"  URL: {post['link']}")
            return post
        else:
            print(f"✗ 投稿の作成に失敗しました")
            print(f"  ステータスコード: {response.status_code}")
            print(f"  レスポンス: {response.text}")
            raise Exception(f"投稿の作成に失敗: {response.status_code}")
    
    def get_or_create_category(self, category_name):
        """
        カテゴリーを取得または作成
        
        Args:
            category_name: カテゴリー名
        
        Returns:
            カテゴリーID
        """
        # 既存のカテゴリーを検索
        response = requests.get(
            f"{self.api_url}/categories",
            headers=self.headers,
            params={'search': category_name}
        )
        
        if response.status_code == 200:
            categories = response.json()
            for cat in categories:
                if cat['name'] == category_name:
                    return cat['id']
        
        # カテゴリーが存在しない場合は作成
        response = requests.post(
            f"{self.api_url}/categories",
            headers=self.headers,
            json={'name': category_name}
        )
        
        if response.status_code == 201:
            category = response.json()
            print(f"✓ カテゴリー「{category_name}」を作成しました（ID: {category['id']}）")
            return category['id']
        else:
            print(f"✗ カテゴリーの作成に失敗しました: {category_name}")
            return None
    
    def get_or_create_tag(self, tag_name):
        """
        タグを取得または作成
        
        Args:
            tag_name: タグ名
        
        Returns:
            タグID
        """
        # 既存のタグを検索
        response = requests.get(
            f"{self.api_url}/tags",
            headers=self.headers,
            params={'search': tag_name}
        )
        
        if response.status_code == 200:
            tags = response.json()
            for tag in tags:
                if tag['name'] == tag_name:
                    return tag['id']
        
        # タグが存在しない場合は作成
        response = requests.post(
            f"{self.api_url}/tags",
            headers=self.headers,
            json={'name': tag_name}
        )
        
        if response.status_code == 201:
            tag = response.json()
            print(f"✓ タグ「{tag_name}」を作成しました（ID: {tag['id']}）")
            return tag['id']
        else:
            print(f"✗ タグの作成に失敗しました: {tag_name}")
            return None
    
    def publish_article(self, article_data, category_name='美容コラム'):
        """
        記事データをWordPressに投稿
        
        Args:
            article_data: 記事データ（generate_article.pyで生成）
            category_name: カテゴリー名
        
        Returns:
            投稿データ
        """
        # カテゴリーを取得または作成
        category_id = self.get_or_create_category(category_name)
        categories = [category_id] if category_id else None
        
        # タグを作成（メインキーワードと関連キーワード）
        tag_ids = []
        keywords = [article_data['keyword']] + article_data.get('related_keywords', [])[:3]
        for keyword in keywords:
            tag_id = self.get_or_create_tag(keyword)
            if tag_id:
                tag_ids.append(tag_id)
        
        # 投稿を作成
        post = self.create_post(
            title=article_data['title'],
            content=article_data['content'],
            status='publish',
            categories=categories,
            tags=tag_ids if tag_ids else None
        )
        
        return post

def main():
    """メイン処理"""
    if len(sys.argv) < 2:
        print("使用方法: python post_to_wordpress.py <記事JSONファイルパス>")
        sys.exit(1)
    
    article_file = sys.argv[1]
    
    if not os.path.exists(article_file):
        print(f"エラー: ファイルが見つかりません: {article_file}")
        sys.exit(1)
    
    # 記事データを読み込む
    with open(article_file, 'r', encoding='utf-8') as f:
        article_data = json.load(f)
    
    print(f"=== WordPress投稿開始 ===")
    print(f"タイトル: {article_data['title']}")
    print(f"キーワード: {article_data['keyword']}")
    
    # WordPressに投稿
    publisher = WordPressPublisher()
    post = publisher.publish_article(article_data)
    
    print(f"\n=== 投稿完了 ===")
    print(f"投稿URL: {post['link']}")
    
    # 投稿履歴を保存
    history_dir = os.path.join(os.path.dirname(__file__), '..', 'data', 'post_history')
    os.makedirs(history_dir, exist_ok=True)
    
    history_file = os.path.join(history_dir, 'posts.json')
    history = []
    if os.path.exists(history_file):
        with open(history_file, 'r', encoding='utf-8') as f:
            history = json.load(f)
    
    history.append({
        'post_id': post['id'],
        'title': post['title']['rendered'],
        'url': post['link'],
        'keyword': article_data['keyword'],
        'published_at': datetime.now().isoformat()
    })
    
    with open(history_file, 'w', encoding='utf-8') as f:
        json.dump(history, f, ensure_ascii=False, indent=2)
    
    print(f"投稿履歴を保存しました: {history_file}")

if __name__ == '__main__':
    main()
