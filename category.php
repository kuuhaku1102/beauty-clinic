<?php
/**
 * Category Template - SEOハブ型
 * 2026年SEO完全準拠
 */
get_header();

$category = get_queried_object();
$category_slug = $category->slug;

// カテゴリ別の説明文とSEO情報
$category_info = [
    'medical-hair-removal' => [
        'title' => '医療脱毛の基礎知識とクリニック選び',
        'description' => '医療脱毛を検討している方に向けて、仕組み・効果・料金相場・クリニックの選び方など、知っておくべき情報を網羅的に解説します。',
        'key_points' => [
            '医療脱毛とは、医療機関でのみ行える永久脱毛効果のある施術です',
            '全身脱毛の料金相場は月額3,000円〜、回数は5〜8回が一般的',
            'クリニック選びでは実績・料金の透明性・アフターケア体制を確認しましょう'
        ],
        'topics' => [
            '基礎知識' => '医療脱毛の仕組みと効果',
            'クリニック選び' => '失敗しない選び方のポイント',
            '部位別情報' => '全身・VIO・顔など部位ごとの特徴',
            '料金・支払い' => '料金体系と支払い方法の選び方'
        ]
    ],
    'double-eyelid-surgery' => [
        'title' => '二重整形の基礎知識とクリニック選び',
        'description' => '二重整形（埋没法・切開法）を検討している方に向けて、施術方法・ダウンタイム・料金相場・クリニックの選び方を詳しく解説します。',
        'key_points' => [
            '埋没法は糸で留める方法で、ダウンタイムが短く料金も手頃',
            '切開法は半永久的な効果がありますが、ダウンタイムは長め',
            '料金相場は埋没法29,800円〜、切開法150,000円〜が目安'
        ],
        'topics' => [
            '基礎知識' => '埋没法と切開法の違い',
            '施術詳細' => '各施術方法のメリット・デメリット',
            'ダウンタイム' => '腫れ・内出血を早く治す方法',
            'クリニック選び' => '失敗しないクリニックの選び方'
        ]
    ],
    'skin-treatment' => [
        'title' => '美肌治療の種類と効果',
        'description' => 'シミ・毛穴・ニキビ跡など肌悩みに対する美肌治療（レーザー・光治療・ピーリングなど）の種類と効果を詳しく解説します。',
        'key_points' => [
            'ピコレーザーはシミ・そばかすに高い効果があります',
            'フォトフェイシャル（光治療）は複合的な肌悩みに対応',
            'ダーマペンはニキビ跡・毛穴の開きに効果的'
        ],
        'topics' => [
            'レーザー治療' => 'ピコレーザーの効果とダウンタイム',
            '光治療' => 'フォトフェイシャル・IPL治療の仕組み',
            'ピーリング' => 'ケミカルピーリングの種類と効果',
            'ダーマペン' => 'ニキビ跡・毛穴への効果'
        ]
    ],
    'botox-injection' => [
        'title' => 'ボトックス注射の効果と注意点',
        'description' => 'ボトックス注射による表情ジワ改善・小顔効果について、効果・持続期間・料金相場・注意点を詳しく解説します。',
        'key_points' => [
            'ボトックス注射は表情ジワ（額・眉間・目尻）に効果的',
            'エラボトックスで小顔効果も期待できます',
            '効果は3〜6ヶ月持続、料金相場は4,980円〜'
        ],
        'topics' => [
            '基礎知識' => 'ボトックス注射の仕組みと効果',
            '部位別情報' => '額・眉間・エラなど部位ごとの効果',
            '注意点' => '失敗例とリスク管理',
            '料金・持続期間' => 'コストパフォーマンスの考え方'
        ]
    ],
    'hyaluronic-acid' => [
        'title' => 'ヒアルロン酸注射の効果と注意点',
        'description' => 'ヒアルロン酸注射による涙袋形成・鼻筋形成・ほうれい線改善について、効果・持続期間・料金相場・注意点を詳しく解説します。',
        'key_points' => [
            'ヒアルロン酸注射は即効性があり、自然な仕上がりが特徴',
            '涙袋・鼻筋・ほうれい線など様々な部位に対応',
            '効果は6〜12ヶ月持続、料金相場は部位により異なる'
        ],
        'topics' => [
            '基礎知識' => 'ヒアルロン酸注射の仕組みと効果',
            '部位別情報' => '涙袋・鼻・ほうれい線など部位ごとの効果',
            '注意点' => '失敗例とリスク管理',
            '料金・持続期間' => 'コストパフォーマンスの考え方'
        ]
    ]
];

$info = $category_info[$category_slug] ?? [
    'title' => $category->name,
    'description' => $category->description ?: '美容医療に関する情報をお届けします。',
    'key_points' => [],
    'topics' => []
];
?>

<div class="bd-category-page">
    <!-- カテゴリヒーロー -->
    <section class="bd-category-hero">
        <div class="bd-section-container">
            <div class="bd-breadcrumb">
                <a href="<?php echo home_url(); ?>">ホーム</a>
                <span class="bd-breadcrumb-separator">›</span>
                <span><?php echo esc_html($category->name); ?></span>
            </div>
            
            <h1 class="bd-category-title"><?php echo esc_html($info['title']); ?></h1>
            <p class="bd-category-description"><?php echo esc_html($info['description']); ?></p>
        </div>
    </section>
    
    <!-- カテゴリ要点整理 -->
    <?php if (!empty($info['key_points'])): ?>
    <section class="bd-category-keypoints">
        <div class="bd-section-container">
            <h2 class="bd-section-subtitle">このカテゴリのポイント</h2>
            <ul class="bd-keypoints-list">
                <?php foreach ($info['key_points'] as $point): ?>
                    <li class="bd-keypoint-item">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#c2185b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <?php echo esc_html($point); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- トピック別ナビゲーション -->
    <?php if (!empty($info['topics'])): ?>
    <section class="bd-category-topics">
        <div class="bd-section-container">
            <h2 class="bd-section-subtitle">トピックから探す</h2>
            <div class="bd-topics-grid">
                <?php foreach ($info['topics'] as $topic_name => $topic_desc): ?>
                    <div class="bd-topic-card">
                        <h3 class="bd-topic-name"><?php echo esc_html($topic_name); ?></h3>
                        <p class="bd-topic-desc"><?php echo esc_html($topic_desc); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- 記事一覧 -->
    <section class="bd-category-articles">
        <div class="bd-section-container">
            <h2 class="bd-section-subtitle">記事一覧</h2>
            
            <?php if (have_posts()): ?>
                <div class="bd-articles-grid">
                    <?php while (have_posts()): the_post(); ?>
                        <article class="bd-article-card" itemscope itemtype="https://schema.org/BlogPosting">
                            <a href="<?php the_permalink(); ?>" class="bd-article-link" itemprop="url">
                                <div class="bd-article-content">
                                    <time class="bd-article-date" datetime="<?php echo get_the_date('c'); ?>" itemprop="datePublished">
                                        <?php echo get_the_date('Y.m.d'); ?>
                                    </time>
                                    
                                    <h3 class="bd-article-title" itemprop="headline"><?php the_title(); ?></h3>
                                    
                                    <?php if (has_excerpt()): ?>
                                        <p class="bd-article-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 50); ?></p>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </article>
                    <?php endwhile; ?>
                </div>
                
                <!-- ページネーション -->
                <?php
                $pagination = paginate_links([
                    'type' => 'array',
                    'prev_text' => '← 前へ',
                    'next_text' => '次へ →'
                ]);
                
                if ($pagination):
                ?>
                    <nav class="bd-pagination" aria-label="ページネーション">
                        <?php foreach ($pagination as $page): ?>
                            <?php echo $page; ?>
                        <?php endforeach; ?>
                    </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="bd-no-posts">
                    <p>まだ記事が投稿されていません。</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php get_footer(); ?>
