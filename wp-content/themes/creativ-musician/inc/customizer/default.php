<?php
/**
 * Default theme options.
 *
 * @package Creativ Musician
 */

if ( ! function_exists( 'creativ_musician_get_default_theme_options' ) ) :

	/**
	 * Get default theme options.
	 *
	 * @since 1.0.0
	 *
	 * @return array Default theme options.
	 */
function creativ_musician_get_default_theme_options() {

	$defaults = array();

	$defaults['show_header_contact_info'] 	= true;
    $defaults['header_email']             	= __( 'example@gmail.com','creativ-musician' );
    $defaults['header_phone' ]            	= __( '8801 234 567 890','creativ-musician' );
    $defaults['header_location' ]           = __( 'Melbourne, Australia','creativ-musician' );
    $defaults['show_header_social_links'] 	= true;
    $defaults['header_social_links']		= array();

    // Homepage Options
	$defaults['enable_frontpage_content'] 		= true;

	// Featured Slider Section
	$defaults['disable_featured_slider']	= true;
	$defaults['number_of_sr_items']			= 3;
	$defaults['featured_slider_overlay']	= 0.4;
	$defaults['featured_slider_speed']		= 1000;
	$defaults['featured_slider_fontsize']	= 72;
	$defaults['featured_slider_height']		= 300;
	$defaults['sr_content_type']			= 'sr_page';

	// Our Features Section
	$defaults['disable_additional_info_section']	= true;
	$defaults['number_of_column']					= 3;
	$defaults['number_of_items']					= 3;
	$defaults['additional_info_icon_container']		= 60;
	$defaults['additional_info_icon_fontsize']		= 32;
	$defaults['additional_info_title_fontsize']		= 22;
	$defaults['ad_content_type']					= 'ad_page';

	//Latest albums Section	
	$defaults['disable_latest_albums_section']	= true;
	$defaults['latest_albums_section_title']	= esc_html__( 'Latest Albums', 'creativ-musician' );
	$defaults['number_of_cs_column']			= 3;
	$defaults['number_of_cs_items']				= 3;
	$defaults['cs_content_type']				= 'cs_page';

	//Cta Section	
	$defaults['disable_cta_section']	   	= true;
	$defaults['background_cta_section']		= get_template_directory_uri() .'/assets/images/default-header.jpg';
	$defaults['cta_small_description']	   	= esc_html__( 'Capitalize on low hanging fruit to identify a ballpark value added activity to beta test. Override the digital divide with additional clickthroughs from DevOps.', 'creativ-musician' );
	$defaults['cta_description']	   	 	= esc_html__( 'Maroon 6 Live Show', 'creativ-musician' );
	$defaults['cta_overlay']				= 0.7;
	$defaults['cta_button_label']	   	 	= esc_html__( 'Buy Ticket Now', 'creativ-musician' );
	$defaults['cta_button_url']	   	 		= '#';

	// About us Section
	$defaults['disable_service_section']	= true;
	$defaults['number_of_ss_items']			= 1;
	$defaults['ss_content_type']			= 'ss_page';

	// Blog Section
	$defaults['disable_blog_section']		= true;
	$defaults['blog_section_title']	   	 			= esc_html__( 'Upcoming Events', 'creativ-musician' );
	$defaults['blog_category']	   			= 0; 
	$defaults['blog_number']				= 3;

	//General Section
	$defaults['readmore_text']					= esc_html__('Read More','creativ-musician');
	$defaults['your_latest_posts_title']		= esc_html__('Blog','creativ-musician');
	$defaults['excerpt_length']					= 25;
	$defaults['layout_options_blog']			= 'right-sidebar';
	$defaults['layout_options_archive']			= 'right-sidebar';
	$defaults['layout_options_page']			= 'right-sidebar';	
	$defaults['layout_options_single']			= 'right-sidebar';	
	$defaults['layout_options_boxed']			= 'full-width';	

	//Footer section 		
	$defaults['copyright_text']					= esc_html__( 'Copyright &copy; All rights reserved.', 'creativ-musician' );

	// Pass through filter.
	$defaults = apply_filters( 'creativ_musician_filter_default_theme_options', $defaults );
	return $defaults;
}

endif;

/**
*  Get theme options
*/
if ( ! function_exists( 'creativ_musician_get_option' ) ) :

	/**
	 * Get theme option
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Option key.
	 * @return mixed Option value.
	 */
	function creativ_musician_get_option( $key ) {

		$default_options = creativ_musician_get_default_theme_options();
		if ( empty( $key ) ) {
			return;
		}

		$theme_options = (array)get_theme_mod( 'theme_options' );
		$theme_options = wp_parse_args( $theme_options, $default_options );

		$value = null;

		if ( isset( $theme_options[ $key ] ) ) {
			$value = $theme_options[ $key ];
		}

		return $value;

	}

endif;