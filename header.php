<?php
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
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
    <nav>
      <a href="<?php echo esc_url(home_url('/')); ?>">ホーム</a>
    </nav>
  </div>
</header>
<div class="bd-container">
