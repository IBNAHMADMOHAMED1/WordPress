<?php
/**
 * Services options.
 *
 * @package Creativ Musician
 */

$default = creativ_musician_get_default_theme_options();

// Featured Services Section
$wp_customize->add_section( 'section_home_service',
	array(
		'title'      => __( 'About Us', 'creativ-musician' ),
		'priority'   => 100,
		'capability' => 'edit_theme_options',
		'panel'      => 'home_page_panel',
		)
);
// Disable Service Section
$wp_customize->add_setting('theme_options[disable_service_section]', 
	array(
	'default' 			=> $default['disable_service_section'],
	'type'              => 'theme_mod',
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'creativ_musician_sanitize_checkbox'
	)
);

$wp_customize->add_control('theme_options[disable_service_section]', 
	array(		
	'label' 	=> __('Disable Services Section', 'creativ-musician'),
	'section' 	=> 'section_home_service',
	'settings'  => 'theme_options[disable_service_section]',
	'type' 		=> 'checkbox',	
	)
);

// Number of items
$wp_customize->add_setting('theme_options[number_of_ss_items]', 
	array(
	'default' 			=> $default['number_of_ss_items'],
	'type'              => 'theme_mod',
	'capability'        => 'edit_theme_options',	
	'sanitize_callback' => 'creativ_musician_sanitize_number_range'
	)
);

$wp_customize->add_control('theme_options[number_of_ss_items]', 
	array(
	'label'       => __('Number Of Slides', 'creativ-musician'),
	'description' => __('Save & Refresh the customizer to see its effect. Maximum is 3.', 'creativ-musician'),
	'section'     => 'section_home_service',   
	'settings'    => 'theme_options[number_of_ss_items]',		
	'type'        => 'number',
	'active_callback' => 'creativ_musician_services_active',
	'input_attrs' => array(
			'min'	=> 1,
			'max'	=> 3,
			'step'	=> 1,
		),
	)
);

$wp_customize->add_setting('theme_options[ss_content_type]', 
	array(
	'default' 			=> $default['ss_content_type'],
	'type'              => 'theme_mod',
	'capability'        => 'edit_theme_options',	
	'sanitize_callback' => 'creativ_musician_sanitize_select'
	)
);

$wp_customize->add_control('theme_options[ss_content_type]', 
	array(
	'label'       => __('Content Type', 'creativ-musician'),
	'section'     => 'section_home_service',   
	'settings'    => 'theme_options[ss_content_type]',		
	'type'        => 'select',
	'active_callback' => 'creativ_musician_services_active',
	'choices'	  => array(
			'ss_page'	  => __('Page','creativ-musician'),
			'ss_post'	  => __('Post','creativ-musician'),
			'ss_category' => __('Category','creativ-musician'),
		),
	)
);

$number_of_ss_items = creativ_musician_get_option( 'number_of_ss_items' );

for( $i=1; $i<=$number_of_ss_items; $i++ ){

	// Page
	$wp_customize->add_setting('theme_options[services_page_'.$i.']', 
		array(
		'type'              => 'theme_mod',
		'capability'        => 'edit_theme_options',	
		'sanitize_callback' => 'creativ_musician_dropdown_pages'
		)
	);

	$wp_customize->add_control('theme_options[services_page_'.$i.']', 
		array(
		'label'       => sprintf( __('Select Page #%1$s', 'creativ-musician'), $i),
		'section'     => 'section_home_service',   
		'settings'    => 'theme_options[services_page_'.$i.']',		
		'type'        => 'dropdown-pages',
		'active_callback' => 'creativ_musician_services_page',
		)
	);

	// Posts
	$wp_customize->add_setting('theme_options[services_post_'.$i.']', 
		array(
		'type'              => 'theme_mod',
		'capability'        => 'edit_theme_options',	
		'sanitize_callback' => 'creativ_musician_dropdown_pages'
		)
	);

	$wp_customize->add_control('theme_options[services_post_'.$i.']', 
		array(
		'label'       => sprintf( __('Select Post #%1$s', 'creativ-musician'), $i),
		'section'     => 'section_home_service',   
		'settings'    => 'theme_options[services_post_'.$i.']',		
		'type'        => 'select',
		'choices'	  => creativ_musician_dropdown_posts(),
		'active_callback' => 'creativ_musician_services_post',
		)
	);
}

// Setting Category.
$wp_customize->add_setting( 'theme_options[services_category]',
	array(
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'absint',
	)
);
$wp_customize->add_control(
	new creativ_musician_Dropdown_Taxonomies_Control( $wp_customize, 'theme_options[services_category]',
		array(
		'label'    => __( 'Select Category', 'creativ-musician' ),
		'section'  => 'section_home_service',
		'settings' => 'theme_options[services_category]',	
		'active_callback' => 'creativ_musician_services_category',		
		)
	)
);