<?php
/**
 * Home Page Options.
 *
 * @package Creativ Musician
 */

$default = creativ_musician_get_default_theme_options();

// Add Panel.
$wp_customize->add_panel( 'home_page_panel',
	array(
	'title'      => __( 'Front Page Sections', 'creativ-musician' ),
	'priority'   => 100,
	'capability' => 'edit_theme_options',
	)
);

/**
* Section Customizer Options.
*/
require get_template_directory() . '/inc/customizer/home-sections/slider.php';
require get_template_directory() . '/inc/customizer/home-sections/additional-info.php';
require get_template_directory() . '/inc/customizer/home-sections/services.php';
require get_template_directory() . '/inc/customizer/home-sections/latest-albums.php';
require get_template_directory() . '/inc/customizer/home-sections/cta.php';
require get_template_directory() . '/inc/customizer/home-sections/blog.php';