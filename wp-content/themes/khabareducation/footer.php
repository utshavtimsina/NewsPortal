<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package HitMag
 */

?>
	</div><!-- .hm-container -->
	</div><!-- #content -->

	<?php do_action( 'hitmag_before_footer' ); ?>

	<footer id="colophon" class="site-footer" role="contentinfo">
		<div class="hm-container">

			<?php do_action( 'hitmag_before_footer_widget_area' ); ?>

			<div class="footer-widget-area">
				<div class="footer-sidebar" role="complementary">
					<?php if ( ! dynamic_sidebar( 'footer-left' ) ) : ?>
						
					<?php endif; // end sidebar widget area ?>
				</div><!-- .footer-sidebar -->
		
				<div class="footer-sidebar" role="complementary">
					<?php if ( ! dynamic_sidebar( 'footer-mid' ) ) : ?>

					<?php endif; // end sidebar widget area ?>
				</div><!-- .footer-sidebar -->		

				<div class="footer-sidebar" role="complementary">
					<?php if ( ! dynamic_sidebar( 'footer-right' ) ) : ?>

					<?php endif; // end sidebar widget area ?>
				</div><!-- .footer-sidebar -->	
					<div class="footer-sidebar" role="complementary">
					<?php if ( ! dynamic_sidebar( 'footer-right-end' ) ) : ?>

					<?php endif; // end sidebar widget area ?>
				</div><!-- .footer-sidebar -->			
			</div><!-- .footer-widget-area -->

			<?php do_action( 'hitmag_after_footer_widget_area' ); ?>

		</div><!-- .hm-container -->

		<div class="site-info">
			<div class="hm-container">
				<div class="site-info-owner">
					<?php
						$footer_copyright_text = get_theme_mod( 'footer_copyright_text', '' );

						if ( ! empty ( $footer_copyright_text ) ) {
							echo wp_kses_post( $footer_copyright_text );
						} else {
							$site_link = '<a href="' . esc_url( home_url( '/' ) ) . '" title="' . esc_attr( get_bloginfo( 'name' ) ) . '" >' . esc_attr( get_bloginfo( 'name' ) ) . '</a>';
							printf( esc_html__( 'Copyright &#169; %1$s %2$s.', 'hitmag' ), date_i18n( 'Y' ), $site_link );
						}		
					?>
				</div>	
				<div class="designby">
				    <p>design and developed by <a href="https://www.facebook.com/pradipAAryal">Pradip Aryal</a></p>
				</div>
			</div><!-- .hm-container -->
		</div><!-- .site-info -->
	</footer><!-- #colophon -->

	<?php do_action( 'hitmag_after_footer' ); ?>

</div><!-- #page -->


<?php wp_footer(); ?>
</body>
</html>