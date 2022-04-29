<?php
/**
 * Custom theme functions.
 *
 * This file contains hook functions attached to theme hooks.
 *
 * @package Creativ Musician
 */

if( ! function_exists( 'creativ_musician_site_branding' ) ) :
	/**
  	 * Site Branding
  	 *
  	 * @since 1.0.0
  	 */
function creativ_musician_site_branding() { ?>
    <div class="wrapper">
        <div class="site-branding">
            <div class="site-logo">
                <?php if(has_custom_logo()):?>
                    <?php the_custom_logo();?>
                <?php endif;?>
            </div><!-- .site-logo -->

            <div id="site-identity">
                <h1 class="site-title">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">  <?php bloginfo( 'name' ); ?></a>
                </h1>

                <?php 
                    $description = get_bloginfo( 'description', 'display' );
                    if ( $description || is_customize_preview() ) : ?>
                    <p class="site-description"><?php echo esc_html($description);?></p>
                <?php endif; ?>
            </div><!-- #site-identity -->
        </div> <!-- .site-branding -->

        <nav id="site-navigation" class="main-navigation" role="navigation" aria-label="Primary Menu">
            <button type="button" class="menu-toggle">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <?php
            wp_nav_menu( array(
                'theme_location' => 'primary',
                'menu_id'        => 'primary-menu',
                'menu_class'     => 'nav-menu',
                'fallback_cb'    => 'creativ_musician_primary_navigation_fallback',
            ) );
            ?>
        </nav><!-- #site-navigation -->
    </div><!-- .wrapper -->
<?php }
endif;
add_action( 'creativ_musician_action_header', 'creativ_musician_site_branding', 10 );

if ( ! function_exists( 'creativ_musician_footer_top_section' ) ) :

  /**
   * Top  Footer 
   *
   * @since 1.0.0
   */
  function creativ_musician_footer_top_section() {
      $footer_sidebar_data = creativ_musician_footer_sidebar_class();
      $footer_sidebar    = $footer_sidebar_data['active_sidebar'];
      $footer_class      = $footer_sidebar_data['class'];
      if ( empty( $footer_sidebar ) ) {
        return;
      }      ?>
      <div class="footer-widgets-area page-section <?php echo esc_attr( $footer_class ); ?>"> <!-- widget area starting from here -->
          <div class="wrapper">
            <?php
              for ( $i = 1; $i <= 4 ; $i++ ) {
                if ( is_active_sidebar( 'footer-' . $i ) ) {
                ?>
                  <div class="hentry">
                    <?php dynamic_sidebar( 'footer-' . $i ); ?>
                  </div>
                  <?php
                }
              }
            ?>
            </div>
          
      </div> <!-- widget area starting from here -->
    <?php
 }
endif;

add_action( 'creativ_musician_action_footer', 'creativ_musician_footer_top_section', 10 );

if ( ! function_exists( 'creativ_musician_footer_section' ) ) :

  /**
   * Footer copyright
   *
   * @since 1.0.0
   */

  function creativ_musician_footer_section() { ?>
        <div class="site-info">
            <?php 
                $copyright_footer = creativ_musician_get_option('copyright_text'); 
                if ( ! empty( $copyright_footer ) ) {
                $copyright_footer = wp_kses_data( $copyright_footer );
                }
                // Powered by content.
                $powered_by_text = sprintf( __( ' Theme Creativ Musician by %s', 'creativ-musician' ), '<a target="_blank" rel="designer" href="http://creativthemes.com/">Creativ Themes</a>' );
            ?>
            <div class="wrapper">
                <span class="copy-right"><?php echo esc_html($copyright_footer);?><?php echo $powered_by_text;?></span>
            </div> 
        </div> <!-- site generator ends here -->
        
    <?php }

endif;
add_action( 'creativ_musician_action_footer', 'creativ_musician_footer_section', 20 );

if ( ! function_exists( 'creativ_musician_footer_sidebar_class' ) ) :
  /**
   * Count the number of footer sidebars to enable dynamic classes for the footer
   *
   * @since Creativ Musician 0.1
   */
  function creativ_musician_footer_sidebar_class() {
    $data = array();
    $active_sidebar = array();
      $count = 0;

      if ( is_active_sidebar( 'footer-1' ) ) {
        $active_sidebar[]   = 'footer-1';
          $count++;
      }

      if ( is_active_sidebar( 'footer-2' ) ){
        $active_sidebar[]   = 'footer-2';
          $count++;
    }

      if ( is_active_sidebar( 'footer-3' ) ){
        $active_sidebar[]   = 'footer-3';
          $count++;
      }

      if ( is_active_sidebar( 'footer-4' ) ){
        $active_sidebar[]   = 'footer-4';
          $count++;
      }

      $class = '';

      switch ( $count ) {
          case '1':
            $class = 'col-1';
            break;
          case '2':
            $class = 'col-2';
            break;
          case '3':
            $class = 'col-3';
            break;
            case '4':
            $class = 'col-4';
            break;
      }

    $data['active_sidebar'] = $active_sidebar;
    $data['class']        = $class;

      return $data;
  }
endif;

if ( ! function_exists( 'creativ_musician_excerpt_length' ) ) :

  /**
   * Implement excerpt length.
   *
   * @since 1.0.0
   *
   * @param int $length The number of words.
   * @return int Excerpt length.
   */
  function creativ_musician_excerpt_length( $length ) {

    if ( is_admin() ) {
      return $length;
    }

    $excerpt_length = creativ_musician_get_option( 'excerpt_length' );

    if ( absint( $excerpt_length ) > 0 ) {
      $length = absint( $excerpt_length );
    }

    return $length;
  }

endif;

add_filter( 'excerpt_length', 'creativ_musician_excerpt_length', 999 );

if( ! function_exists( 'creativ_musician_banner_header
  ' ) ) :
    /**
     * Page Header
    */
    function creativ_musician_banner_header() { 

        if ( is_front_page() && is_home() ){ 
            $header_image = get_header_image();
            $header_image_url = ! empty( $header_image ) ?  $header_image : get_template_directory_uri() . '/assets/images/default-header.jpg';

        } 
        elseif( is_front_page() ) {
            return;
        }
        else {
            $header_image_url = creativ_musician_banner_image( $image_url = '' );
        } ?>
        <div id="page-site-header" style="background-image: url('<?php echo esc_url( $header_image_url ); ?>');">
            <div class="overlay"></div>
            <header class='page-header'>
                <div class="wrapper">
                    <?php creativ_musician_banner_title();?>
                </div><!-- .wrapper -->
            </header>
        </div><!-- #page-site-header -->
        <?php echo '<div class= "wrapper page-section">';
    }
endif;
add_action( 'creativ_musician_banner_header', 'creativ_musician_banner_header', 10 );

if( ! function_exists( 'creativ_musician_banner_title' ) ) :
/**
 * Page Header
*/
function creativ_musician_banner_title(){ 
    if ( ( is_front_page() && is_home() ) || is_home() ){ 
        $your_latest_posts_title = creativ_musician_get_option( 'your_latest_posts_title' );?>
        <h2 class="page-title"><?php echo esc_html($your_latest_posts_title); ?></h2><?php
    }

    if( is_singular() ) {
        the_title( '<h2 class="page-title">', '</h2>' );
    }       

    if( is_archive() ){
        the_archive_description( '<div class="archive-description">', '</div>' );
        the_archive_title( '<h2 class="page-title">', '</h2>' );
    }

    if( is_search() ){ ?>
        <h2 class="page-title"><?php printf( esc_html__( 'Search Results for: %s', 'creativ-musician' ), '<span>' . get_search_query() . '</span>' ); ?></h2>
    <?php }
    
    if( is_404() ) {
        echo '<h2 class="page-title">' . esc_html__( 'Error 404', 'creativ-musician' ) . '</h2>';
    }
}
endif;

if( ! function_exists( 'creativ_musician_banner_image' ) ) :
/**
 * Banner Header Image
*/
function creativ_musician_banner_image( $image_url ){
    global $post;

    $archive_header = creativ_musician_get_option( 'archive_header_image' );
    $search_header = creativ_musician_get_option( 'search_header_image' );
    $header_404 = creativ_musician_get_option( '404_header_image' );

    if ( is_home() && ! is_front_page() ){ 
        $image_url      = get_the_post_thumbnail_url( get_option( 'page_for_posts' ), 'full' );
/*        $fallback_image = ! empty( get_header_image() ) ?  get_header_image() : get_template_directory_uri() . '/assets/images/default-header.jpg';
*/
        $header_image = get_header_image();
        $fallback_image = ! empty( $header_image ) ?  $header_image : get_template_directory_uri() . '/assets/images/default-header.jpg';

        $image_url      = ( ! empty( $image_url) ) ? $image_url : $fallback_image;
    }
    elseif( is_singular() ){
        $image_url      = get_the_post_thumbnail_url( $post->ID, 'full' );

        $header_image = get_header_image();
        $fallback_image = ! empty( $header_image ) ?  $header_image : get_template_directory_uri() . '/assets/images/default-header.jpg';

/*        $fallback_image = ! empty( get_header_image() ) ?  get_header_image() : get_template_directory_uri() . '/assets/images/default-header.jpg';
*/        $image_url      = ( ! empty( $image_url) ) ? $image_url : $fallback_image;
    }
    elseif( is_archive() ){
        $image_url = ( ! empty( $archive_header) ) ? $archive_header : get_template_directory_uri() . '/assets/images/default-header.jpg';
    }
    elseif( is_search() ){ 
        $image_url = ( ! empty( $search_header) ) ? $search_header : get_template_directory_uri() . '/assets/images/default-header.jpg';
    }
    elseif( is_404() ) {
        $image_url = ( ! empty( $header_404) ) ? $header_404 : get_template_directory_uri() . '/assets/images/default-header.jpg';
    }
    return $image_url;
}
endif;

if ( ! function_exists( 'creativ_musician_posts_tags' ) ) :
    /**
     * Prints HTML with meta information for the current post-date/time and author.
     */
    function creativ_musician_posts_tags() {
        // Hide category and tag text for pages.
        if ( 'post' === get_post_type() && has_tag() ) { ?>
                <div class="tags-links">

                    <?php /* translators: used between list items, there is a space after the comma */
                    $tags = get_the_tags();
                    if ( $tags ) {

                        foreach ( $tags as $tag ) {
                            echo '<span><a href="' . esc_url( get_tag_link( $tag->term_id ) ) .'">' . esc_html( $tag->name ) . '</a></span>'; // WPCS: XSS OK.
                        }
                    } ?>
                </div><!-- .tags-links -->
        <?php } 
    }
endif;

/**
 * Render social links.
 *
 * @since 1.0
 */
function creativ_musician_render_social_links() {

        $social_link1 = creativ_musician_get_option( 'social_link_1' );
        $social_link2 = creativ_musician_get_option( 'social_link_2' );
        $social_link3 = creativ_musician_get_option( 'social_link_3' );
        
        if ( empty( $social_link1 ) && empty( $social_link2 ) && empty( $social_link3 ) && empty( $social_link4 ) && empty( $social_link5 ) ) {
                return;
        }

        echo '<div class="social-icons">';
        echo '<ul>';
        if ( ! empty( $social_link1 ) ) {
            echo '<li><a href="' . esc_url( $social_link1 ) . '"></a></li>';
        }
        if ( ! empty( $social_link2 ) ) {
            echo '<li><a href="' . esc_url( $social_link2 ) . '"></a></li>';
        }
        if ( ! empty( $social_link3 ) ) {
            echo '<li><a href="' . esc_url( $social_link3 ) . '"></a></li>';
        }
        echo '</ul>';
        echo '</div>';
}