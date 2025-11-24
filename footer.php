<?php
/**
 * Footer Template
 * フッター用テンプレート
 */
?>
</div><!-- /.bd-container -->

<footer class="bd-footer">
    <div class="bd-footer-inner">
        <div class="bd-footer-widgets">
            <?php if (is_active_sidebar('footer-1')): ?>
                <div class="bd-footer-widget">
                    <?php dynamic_sidebar('footer-1'); ?>
                </div>
            <?php endif; ?>
            
            <?php if (is_active_sidebar('footer-2')): ?>
                <div class="bd-footer-widget">
                    <?php dynamic_sidebar('footer-2'); ?>
                </div>
            <?php endif; ?>
            
            <?php if (is_active_sidebar('footer-3')): ?>
                <div class="bd-footer-widget">
                    <?php dynamic_sidebar('footer-3'); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="bd-footer-bottom">
            <p class="bd-copyright">
                &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.
            </p>
            <?php
            wp_nav_menu([
                'theme_location' => 'footer-menu',
                'container' => 'nav',
                'container_class' => 'bd-footer-nav',
                'fallback_cb' => false,
                'depth' => 1,
            ]);
            ?>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
