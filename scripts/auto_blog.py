#!/usr/bin/env python3
"""
自動ブログ投稿メインスクリプト
記事生成からWordPress投稿までを一括実行
"""

import os
import sys
import json
from datetime import datetime

# スクリプトのディレクトリをパスに追加
sys.path.insert(0, os.path.dirname(__file__))

from generate_article import BeautyBlogGenerator
from post_to_wordpress import WordPressPublisher

def main():
    """
    メイン処理
    1. SEOキーワードを自動選択
    2. AI記事を生成
    3. WordPressに自動投稿
    """
    print("=" * 60)
    print("美容クリニック自動ブログ投稿システム")
    print("=" * 60)
    print(f"実行日時: {datetime.now().strftime('%Y年%m月%d日 %H:%M:%S')}")
    print()
    
    try:
        # ステップ1: 記事生成
        print("[ステップ1] 記事生成")
        print("-" * 60)
        generator = BeautyBlogGenerator()
        
        # キーワードを選択
        keyword = generator.select_keyword()
        print(f"✓ 選択されたキーワード: {keyword}")
        
        # 記事を生成
        print("✓ AI記事生成中...")
        article = generator.generate_article(keyword)
        print(f"✓ 記事生成完了")
        print(f"  タイトル: {article['title']}")
        print(f"  文字数: {len(article['content'])}文字")
        
        # 記事を保存
        article_file = generator.save_article(article)
        print(f"✓ 記事を保存: {article_file}")
        print()
        
        # ステップ2: WordPress投稿
        print("[ステップ2] WordPress投稿")
        print("-" * 60)
        publisher = WordPressPublisher()
        
        # 投稿
        print("✓ WordPressに投稿中...")
        post = publisher.publish_article(article)
        print(f"✓ 投稿完了")
        print(f"  投稿ID: {post['id']}")
        print(f"  投稿URL: {post['link']}")
        print()
        
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
            'keyword': article['keyword'],
            'article_file': article_file,
            'published_at': datetime.now().isoformat()
        })
        
        with open(history_file, 'w', encoding='utf-8') as f:
            json.dump(history, f, ensure_ascii=False, indent=2)
        
        # 完了サマリー
        print("=" * 60)
        print("✓ 自動ブログ投稿が完了しました")
        print("=" * 60)
        print(f"タイトル: {article['title']}")
        print(f"キーワード: {article['keyword']}")
        print(f"投稿URL: {post['link']}")
        print(f"記事ファイル: {article_file}")
        print(f"投稿履歴: {history_file}")
        print("=" * 60)
        
        return 0
        
    except Exception as e:
        print()
        print("=" * 60)
        print("✗ エラーが発生しました")
        print("=" * 60)
        print(f"エラー内容: {str(e)}")
        print()
        import traceback
        traceback.print_exc()
        return 1

if __name__ == '__main__':
    sys.exit(main())
