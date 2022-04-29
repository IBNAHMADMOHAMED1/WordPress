<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Creativ Musician
 */
/**
* Hook - creativ_musician_action_doctype.
*
* @hooked creativ_musician_doctype -  10
*/
do_action( 'creativ_musician_action_doctype' );
?>
<head>
<?php
/**
* Hook - creativ_musician_action_head.
*
* @hooked creativ_musician_head -  10
*/
do_action( 'creativ_musician_action_head' );
?>

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php do_action( 'wp_body_open' ); ?>

<?php

/**
* Hook - creativ_musician_action_before.
*
* @hooked creativ_musician_page_start - 10
*/
do_action( 'creativ_musician_action_before' );

/**
*
* @hooked creativ_musician_header_start - 10
*/
do_action( 'creativ_musician_action_before_header' );

/**
*
*@hooked creativ_musician_site_branding - 10
*@hooked creativ_musician_header_end - 15 
*/
do_action('creativ_musician_action_header');

/**
*
* @hooked creativ_musician_content_start - 10
*/
do_action( 'creativ_musician_action_before_content' );

/**
 * Banner start
 * 
 * @hooked creativ_musician_banner_header - 10
*/
do_action( 'creativ_musician_banner_header' );  
