<?php get_header(); ?>

<?php
$clinic_id = get_query_var('clinic_id');
if ($clinic_id) {
    echo do_shortcode('[beauty_clinic_detail clinic_id="' . intval($clinic_id) . '"]');
} else {
    if (have_posts()) : while (have_posts()) : the_post();
        the_title('<h1>', '</h1>');
        the_content();
    endwhile; endif;
}
?>

<?php get_footer(); ?>
