<?php
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <!-- Matomo -->
<script>
  var _paq = window._paq = window._paq || [];
  /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="//matomo.sakura.ne.jp/matomo/";
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', '1']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<!-- End Matomo Code -->

<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<header class="bd-header">
  <div class="bd-header-inner">
    <div>
      <div class="bd-logo">美容クリニックラボ</div>
      <div class="bd-tagline">Beauty Clinic Lab</div>
    </div>
    <nav class="bd-header-nav">
      <a href="<?php echo esc_url(home_url('/')); ?>" class="bd-nav-link">ホーム</a>
      <a href="<?php echo esc_url(home_url('/blog/')); ?>" class="bd-nav-link">美容コラム</a>
    </nav>
  </div>
</header>

<?php
// おすすめクリニックをカテゴリー別に表示
$categories = ['脱毛', '二重', '美肌', '痩身', 'AGA', 'その他'];
$has_featured = false;

foreach ($categories as $cat) {
    $featured_clinics = new WP_Query([
        'post_type' => 'featured_clinic',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => '_bd_category',
                'value' => $cat,
                'compare' => '='
            ]
        ],
        'meta_key' => '_bd_display_order',
        'orderby' => 'meta_value_num',
        'order' => 'ASC'
    ]);
    
    if ($featured_clinics->have_posts()):
        if (!$has_featured) {
            echo '<div class="bd-featured-clinics-wrapper">';
            $has_featured = true;
        }
        ?>
        <div class="bd-featured-category-section">
            <div class="bd-featured-clinics-container">
                <button class="bd-featured-category-title bd-accordion-toggle" type="button" aria-expanded="false">
                    <span class="bd-category-label"><?php echo esc_html($cat); ?></span>
                    のおすすめクリニック
                    <span class="bd-accordion-icon">▼</span>
                </button>
                <div class="bd-featured-clinics-slider bd-accordion-content">
                    <?php while ($featured_clinics->have_posts()): $featured_clinics->the_post();
                        $affiliate_url = get_post_meta(get_the_ID(), '_bd_affiliate_url', true);
                        $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
                        ?>
                        <div class="bd-featured-clinic-item">
                            <?php if ($affiliate_url): ?>
                                <a href="<?php echo esc_url($affiliate_url); ?>" 
                                   target="_blank" 
                                   rel="noopener noreferrer"
                                   class="bd-featured-clinic-link">
                            <?php endif; ?>
                            
                            <?php if ($thumbnail_url): ?>
                                <img src="<?php echo esc_url($thumbnail_url); ?>" 
                                     alt="<?php echo esc_attr(get_the_title()); ?>">
                            <?php endif; ?>
                            
                            <div class="bd-featured-clinic-info">
                                <h3 class="bd-featured-clinic-title"><?php the_title(); ?></h3>
                            </div>
                            
                            <?php if ($affiliate_url): ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        <?php
        wp_reset_postdata();
    endif;
}

if ($has_featured) {
    echo '</div>';
}
?>

<div class="bd-container">
