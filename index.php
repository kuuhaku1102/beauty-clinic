<?php
/**
 * Index Template
 * トップページ・メインページ用テンプレート
 */
get_header();
?>

<div class="bd-top-page">
    <div class="bd-page-header">
        <h1 class="bd-page-title">美容クリニック検索</h1>
        <p class="bd-page-description">あなたにぴったりの美容クリニックを見つけよう</p>
    </div>
    
    <?php echo do_shortcode('[beauty_clinic_search]'); ?>
</div>

<?php get_footer(); ?>
