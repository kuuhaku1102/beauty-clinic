<?php
/**
 * Sidebar Template
 * サイドバー用テンプレート
 */
if (!is_active_sidebar('sidebar-1')) {
    return;
}
?>

<aside class="bd-sidebar">
    <?php dynamic_sidebar('sidebar-1'); ?>
</aside>
