<?php

if ( ! function_exists( 'music_business_enqueue_styles' ) ) :

	function music_business_enqueue_styles() {
		wp_enqueue_style( 'musician-business-style-parent', get_template_directory_uri() . '/style.css' );

		wp_enqueue_style( 'musician-business-style', get_stylesheet_directory_uri() . '/style.css', array( 'musician-business-style-parent' ), '1.0' );

		wp_enqueue_style( 'musician-business-fonts', music_business_fonts_url(), array(), null );
	}
endif;
add_action( 'wp_enqueue_scripts', 'music_business_enqueue_styles', 99 );


if ( ! function_exists( 'music_business_fonts_url' ) ) :

	function music_business_fonts_url() {
		
		$fonts_url = '';
		$fonts     = array();
		$subsets   = 'latin,latin-ext';


		if ( 'off' !== _x( 'on', 'Poppins font: on or off', 'musician-business' ) ) {
			$fonts[] = 'Poppins:400,500,600,700';
		}

		if ( 'off' !== _x( 'on', 'Lato font: on or off', 'musician-business' ) ) {
			$fonts[] = 'Lato:400';
		}

		$query_args = array(
			'family' => urlencode( implode( '|', $fonts ) ),
			'subset' => urlencode( $subsets ),
		);

		if ( $fonts ) {
			$fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
		}

		return esc_url_raw( $fonts_url );
	}
endif;