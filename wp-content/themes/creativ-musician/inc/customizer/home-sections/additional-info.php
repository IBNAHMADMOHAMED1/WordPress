<?php
/**
 * Additional Information options.
 *
 * @package Creativ Musician
 */

$default = creativ_musician_get_default_theme_options();

// Featured Additional Information Section
$wp_customize->add_section( 'section_additional_info',
	array(
		'title'      => __( 'Our Services', 'creativ-musician' ),
		'priority'   => 100,
		'capability' => 'edit_theme_options',
		'panel'      => 'home_page_panel',
		)
);
// Disable Additional Information Section
$wp_customize->add_setting('theme_options[disable_additional_info_section]', 
	array(
	'default' 			=> $default['disable_additional_info_section'],
	'type'              => 'theme_mod',
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'creativ_musician_sanitize_checkbox'
	)
);

$wp_customize->add_control('theme_options[disable_additional_info_section]', 
	array(		
	'label' 	=> __('Disable Additional Information Section', 'creativ-musician'),
	'section' 	=> 'section_additional_info',
	'settings'  => 'theme_options[disable_additional_info_section]',
	'type' 		=> 'checkbox',	
	)
);

// Number of counter
$wp_customize->add_setting('theme_options[number_of_column]', 
	array(
	'default' 			=> $default['number_of_column'],
	'type'              => 'theme_mod',
	'capability'        => 'edit_theme_options',	
	'sanitize_callback' => 'creativ_musician_sanitize_number_range'
	)
);

$wp_customize->add_control('theme_options[number_of_column]', 
	array(
	'label'       => __('Column Per Row', 'creativ-musician'),
	'description' => __('Save & Refresh the customizer to see its effect. Maximum is 3', 'creativ-musician'),
	'section'     => 'section_additional_info',   
	'settings'    => 'theme_options[number_of_column]',		
	'type'        => 'number',
	'active_callback' => 'creativ_musician_additional_info_active',
	'input_attrs' => array(
			'min'	=> 1,
			'max'	=> 3,
			'step'	=> 1,
		),
	)
);
// Number of items
$wp_customize->add_setting('theme_options[number_of_items]', 
	array(
	'default' 			=> $default['number_of_items'],
	'type'              => 'theme_mod',
	'capability'        => 'edit_theme_options',	
	'sanitize_callback' => 'creativ_musician_sanitize_number_range'
	)
);

$wp_customize->add_control('theme_options[number_of_items]', 
	array(
	'label'       => __('Number Of Items', 'creativ-musician'),
	'description' => __('Save & Refresh the customizer to see its effect. Maximum is 6.', 'creativ-musician'),
	'section'     => 'section_additional_info',   
	'settings'    => 'theme_options[number_of_items]',		
	'type'        => 'number',
	'active_callback' => 'creativ_musician_additional_info_active',
	'input_attrs' => array(
			'min'	=> 1,
			'max'	=> 6,
			'step'	=> 1,
		),
	)
);

// Icon Container Size
$wp_customize->add_setting('theme_options[additional_info_icon_container]', 
	array(
	'default' 			=> $default['additional_info_icon_container'],
	'type'              => 'theme_mod',
	'capability'        => 'edit_theme_options',	
	'sanitize_callback' => 'sanitize_text_field'
	)
);

$wp_customize->add_control('theme_options[additional_info_icon_container]', 
	array(
	'label'       => __('Icon Container Size', 'creativ-musician'),
	'section'     => 'section_additional_info',   
	'settings'    => 'theme_options[additional_info_icon_container]',		
	'type'        => 'range',
	'active_callback' => 'creativ_musician_additional_info_active',
	'input_attrs' => array(
			'min'	=> 10,
			'max'	=> 300,
			'step'	=> 1,
		),
	)
);

// Icon Font Size
$wp_customize->add_setting('theme_options[additional_info_icon_fontsize]', 
	array(
	'default' 			=> $default['additional_info_icon_fontsize'],
	'type'              => 'theme_mod',
	'capability'        => 'edit_theme_options',	
	'sanitize_callback' => 'sanitize_text_field'
	)
);

$wp_customize->add_control('theme_options[additional_info_icon_fontsize]', 
	array(
	'label'       => __('Icon Font Size', 'creativ-musician'),
	'section'     => 'section_additional_info',   
	'settings'    => 'theme_options[additional_info_icon_fontsize]',		
	'type'        => 'range',
	'active_callback' => 'creativ_musician_additional_info_active',
	'input_attrs' => array(
			'min'	=> 16,
			'max'	=> 200,
			'step'	=> 1,
		),
	)
);

// Title Font Size
$wp_customize->add_setting('theme_options[additional_info_title_fontsize]', 
	array(
	'default' 			=> $default['additional_info_title_fontsize'],
	'type'              => 'theme_mod',
	'capability'        => 'edit_theme_options',	
	'sanitize_callback' => 'sanitize_text_field'
	)
);

$wp_customize->add_control('theme_options[additional_info_title_fontsize]', 
	array(
	'label'       => __('Service Title Font Size', 'creativ-musician'),
	'description' => esc_html__( 'Select Value Between 16 to 100. Default is 24.', 'creativ-musician' ),
	'section'     => 'section_additional_info',   
	'settings'    => 'theme_options[additional_info_title_fontsize]',		
	'type'        => 'number',
	'active_callback' => 'creativ_musician_additional_info_active',
	'input_attrs' => array(
			'min'	=> 16,
			'max'	=> 100,
			'style' => 'width: 100px;',
		),
	)
);

$wp_customize->add_setting('theme_options[ad_content_type]', 
	array(
	'default' 			=> $default['ad_content_type'],
	'type'              => 'theme_mod',
	'capability'        => 'edit_theme_options',	
	'sanitize_callback' => 'creativ_musician_sanitize_select'
	)
);

$wp_customize->add_control('theme_options[ad_content_type]', 
	array(
	'label'       => __('Content Type', 'creativ-musician'),
	'section'     => 'section_additional_info',   
	'settings'    => 'theme_options[ad_content_type]',		
	'type'        => 'select',
	'active_callback' => 'creativ_musician_additional_info_active',
	'choices'	  => array(
			'ad_page'	  	=> __('Page','creativ-musician'),
			'ad_post'	  	=> __('Post','creativ-musician'),
			'ad_category'	=> __('Category','creativ-musician'),
		),
	)
);

$number_of_items = creativ_musician_get_option( 'number_of_items' );

for( $i=1; $i<=$number_of_items; $i++ ){

	// Additional Information First Icon
	$wp_customize->add_setting('theme_options[additional_info_icon_'.$i.']', 
		array(
		'type'              => 'theme_mod',
		'capability'        => 'edit_theme_options',	
		'sanitize_callback' => 'sanitize_text_field'
		)
	);

	$wp_customize->add_control('theme_options[additional_info_icon_'.$i.']', 
		array(
		'label'       => sprintf( __('Select Icon #%1$s', 'creativ-musician'), $i),
		'description' => sprintf( __('Please input icon as eg: fa fa-archive. Find Font-awesome icons %1$shere%2$s', 'creativ-musician'), '<a href="' . esc_url( 'https://fontawesome.com/v4.7.0/cheatsheet/' ) . '" target="_blank">', '</a>' ),
		'section'     => 'section_additional_info',   
		'settings'    => 'theme_options[additional_info_icon_'.$i.']',		
		'active_callback' => 'creativ_musician_additional_info_active',			
		'type'        => 'text',
		)
	);

	// Page
	$wp_customize->add_setting('theme_options[additional_info_page_'.$i.']', 
		array(
		'type'              => 'theme_mod',
		'capability'        => 'edit_theme_options',	
		'sanitize_callback' => 'creativ_musician_dropdown_pages'
		)
	);

	$wp_customize->add_control('theme_options[additional_info_page_'.$i.']', 
		array(
		'label'       => sprintf( __('Select Page #%1$s', 'creativ-musician'), $i),
		'section'     => 'section_additional_info',   
		'settings'    => 'theme_options[additional_info_page_'.$i.']',		
		'type'        => 'dropdown-pages',
		'active_callback' => 'creativ_musician_additional_info_page',
		)
	);

	// Posts
	$wp_customize->add_setting('theme_options[additional_info_post_'.$i.']', 
		array(
		'type'              => 'theme_mod',
		'capability'        => 'edit_theme_options',	
		'sanitize_callback' => 'creativ_musician_dropdown_pages'
		)
	);

	$wp_customize->add_control('theme_options[additional_info_post_'.$i.']', 
		array(
		'label'       => sprintf( __('Select Post #%1$s', 'creativ-musician'), $i),
		'section'     => 'section_additional_info',   
		'settings'    => 'theme_options[additional_info_post_'.$i.']',		
		'type'        => 'select',
		'choices'	  => creativ_musician_dropdown_posts(),
		'active_callback' => 'creativ_musician_additional_info_post',
		)
	);
}

// Setting Category.
$wp_customize->add_setting( 'theme_options[additional_info_category]',
	array(
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'absint',
	)
);
$wp_customize->add_control(
	new creativ_musician_Dropdown_Taxonomies_Control( $wp_customize, 'theme_options[additional_info_category]',
		array(
		'label'    => __( 'Select Category', 'creativ-musician' ),
		'section'  => 'section_additional_info',
		'settings' => 'theme_options[additional_info_category]',	
		'active_callback' => 'creativ_musician_additional_info_category',		
		)
	)
);