#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
2026年SEO完全準拠 自動ブログ投稿システム v2.0
統合スクリプト
"""

import sys
import os
from datetime import datetime

# スクリプトのディレクトリをパスに追加
sys.path.insert(0, os.path.dirname(__file__))

from generate_article_v2 import SEOArticleGenerator
from post_to_wordpress_v2 import WordPressPublisher


def main():
    print("="*60)
    print("2026年SEO完全準拠 自動ブログ投稿システム v2.0")
    print("="*60)
    print(f"実行日時: {datetime.now().strftime('%Y年%m月%d日 %H:%M:%S')}")
    print()
    
    try:
        # ステップ1: 記事生成
        print("[ステップ1] 記事生成")
        print("-"*60)
        
        generator = SEOArticleGenerator()
        article = generator.generate_article()
        
        if not article:
            print("\n✗ 記事生成に失敗しました")
            sys.exit(1)
        
        # 記事を保存
        json_file, md_file = generator.save_article(article)
        print(f"✓ 記事を保存: {json_file}")
        
        # ステップ2: WordPress投稿
        print("\n[ステップ2] WordPress投稿")
        print("-"*60)
        
        publisher = WordPressPublisher()
        post = publisher.publish_article(article)
        
        if not post:
            print("\n✗ WordPress投稿に失敗しました")
            sys.exit(1)
        
        # 完了
        print("\n" + "="*60)
        print("✓ 自動ブログ投稿が完了しました")
        print("="*60)
        print(f"\nタイトル: {article['title']}")
        print(f"カテゴリ: {article['category_name']}")
        print(f"役割: {article['role']}")
        print(f"URL: {post['link']}")
        
    except Exception as e:
        print("\n" + "="*60)
        print("✗ エラーが発生しました")
        print("="*60)
        print(f"エラー内容: {str(e)}")
        import traceback
        traceback.print_exc()
        sys.exit(1)


if __name__ == '__main__':
    main()
