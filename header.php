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


<div class="bd-container">
