<?php
/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package HitMag
 */

$hitmag_sidebar_layout = hitmag_get_layout();

if ( $hitmag_sidebar_layout == 'th-content-centered' || $hitmag_sidebar_layout == 'th-no-sidebar' ) {
	return;
}

?>

<aside id="secondary" class="widget-area" role="complementary">

	<?php do_action( 'hitmag_before_sidebar' ); ?>

	<?php dynamic_sidebar( 'sidebar-1' ); ?>

	<?php do_action( 'hitmag_after_sidebar' ); ?>

</aside><!-- #secondary -->