<?php
/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Creativ Musician
 */

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>
<?php $sidebar_layout = creativ_musician_get_option('layout_options'); 
if ( 'no-sidebar' !== $sidebar_layout ) {?>
	<aside id="secondary" class="widget-area" role="complementary">
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	</aside><!-- #secondary -->
<?php } ?>