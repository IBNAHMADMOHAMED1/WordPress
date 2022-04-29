<?php
/**
 * Active callback functions.
 *
 * @package Creativ Musician
 */

function creativ_musician_additional_info_active( $control ) {
    if( $control->manager->get_setting( 'theme_options[disable_additional_info_section]' )->value() == true ) {
        return false;
    }else{
        return true;
    }
}

function creativ_musician_additional_info_page( $control ) {
    $content_type = $control->manager->get_setting( 'theme_options[ad_content_type]' )->value();
    return ( creativ_musician_additional_info_active( $control ) && ( 'ad_page' == $content_type ) );
}

function creativ_musician_additional_info_post( $control ) {
    $content_type = $control->manager->get_setting( 'theme_options[ad_content_type]' )->value();
    return ( creativ_musician_additional_info_active( $control ) && ( 'ad_post' == $content_type ) );
}

function creativ_musician_additional_info_category( $control ) {
    $content_type = $control->manager->get_setting( 'theme_options[ad_content_type]' )->value();
    return ( creativ_musician_additional_info_active( $control ) && ( 'ad_category' == $content_type ) );
}


function creativ_musician_latest_albums_active( $control ) {
    if( $control->manager->get_setting( 'theme_options[disable_latest_albums_section]' )->value() == true ) {
        return false;
    }else{
        return true;
    }
}

function creativ_musician_latest_albums_page( $control ) {
    $content_type = $control->manager->get_setting( 'theme_options[cs_content_type]' )->value();
    return ( creativ_musician_latest_albums_active( $control ) && ( 'cs_page' == $content_type ) );
}

function creativ_musician_latest_albums_post( $control ) {
    $content_type = $control->manager->get_setting( 'theme_options[cs_content_type]' )->value();
    return ( creativ_musician_latest_albums_active( $control ) && ( 'cs_post' == $content_type ) );
}

function creativ_musician_latest_albums_category( $control ) {
    $content_type = $control->manager->get_setting( 'theme_options[cs_content_type]' )->value();
    return ( creativ_musician_latest_albums_active( $control ) && ( 'cs_category' == $content_type ) );
}


function creativ_musician_services_active( $control ) {
    if( $control->manager->get_setting( 'theme_options[disable_service_section]' )->value() == true ) {
        return false;
    }else{
        return true;
    }
}

function creativ_musician_services_page( $control ) {
    $content_type = $control->manager->get_setting( 'theme_options[ss_content_type]' )->value();
    return ( creativ_musician_services_active( $control ) && ( 'ss_page' == $content_type ) );
}

function creativ_musician_services_post( $control ) {
    $content_type = $control->manager->get_setting( 'theme_options[ss_content_type]' )->value();
    return ( creativ_musician_services_active( $control ) && ( 'ss_post' == $content_type ) );
}

function creativ_musician_services_category( $control ) {
    $content_type = $control->manager->get_setting( 'theme_options[ss_content_type]' )->value();
    return ( creativ_musician_services_active( $control ) && ( 'ss_category' == $content_type ) );
}

function creativ_musician_slider_active( $control ) {
    if( $control->manager->get_setting( 'theme_options[disable_featured_slider]' )->value() == true ) {
        return false;
    }else{
        return true;
    }
}

function creativ_musician_slider_page( $control ) {
    $content_type = $control->manager->get_setting( 'theme_options[sr_content_type]' )->value();
    return ( creativ_musician_slider_active( $control ) && ( 'sr_page' == $content_type ) );
}

function creativ_musician_slider_post( $control ) {
    $content_type = $control->manager->get_setting( 'theme_options[sr_content_type]' )->value();
    return ( creativ_musician_slider_active( $control ) && ( 'sr_post' == $content_type ) );
}

function creativ_musician_slider_category( $control ) {
    $content_type = $control->manager->get_setting( 'theme_options[sr_content_type]' )->value();
    return ( creativ_musician_slider_active( $control ) && ( 'sr_category' == $content_type ) );
}

function creativ_musician_cta_active( $control ) {
    if( $control->manager->get_setting( 'theme_options[disable_cta_section]' )->value() == true ) {
        return false;
    }else{
        return true;
    }
}

function creativ_musician_blog_active( $control ) {
    if( $control->manager->get_setting( 'theme_options[disable_blog_section]' )->value() == true ) {
        return false;
    }else{
        return true;
    }
}

/**
 * Active Callback for top bar section
 */
function creativ_musician_contact_info_ac( $control ) {

    $show_contact_info = $control->manager->get_setting( 'theme_options[show_header_contact_info]')->value();
    $control_id        = $control->id;
         
    if ( $control_id == 'theme_options[header_location]' && $show_contact_info ) return true;
    if ( $control_id == 'theme_options[header_email]' && $show_contact_info ) return true;
    if ( $control_id == 'theme_options[header_phone]' && $show_contact_info ) return true;

    return false;
}

function creativ_musician_social_links_active( $control ) {
    if( $control->manager->get_setting( 'theme_options[show_header_social_links]' )->value() == true ) {
        return true;
    }else{
        return false;
    }
}