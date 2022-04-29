<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Creativ Musician
 */

/**
 *
 * @hooked creativ_musician_footer_start
 */
do_action( 'creativ_musician_action_before_footer' );

/**
 * Hooked - creativ_musician_footer_top_section -10
 * Hooked - creativ_musician_footer_section -20
 */
do_action( 'creativ_musician_action_footer' );

/**
 * Hooked - creativ_musician_footer_end. 
 */
do_action( 'creativ_musician_action_after_footer' );

wp_footer(); ?>

</body>  
</html>