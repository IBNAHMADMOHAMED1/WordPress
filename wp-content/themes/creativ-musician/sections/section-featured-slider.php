<?php 
/**
 * Template part for displaying Featured Slider Section
 *
 *@package Creativ Musician
 */
    $sr_content_type   = creativ_musician_get_option( 'sr_content_type' );
    $number_of_sr_items = creativ_musician_get_option( 'number_of_sr_items' );
    $featured_slider_overlay = creativ_musician_get_option( 'featured_slider_overlay' );
    $featured_slider_speed   = creativ_musician_get_option( 'featured_slider_speed' );
    $featured_slider_fontsize   = creativ_musician_get_option( 'featured_slider_fontsize' );
    $featured_slider_height   = creativ_musician_get_option( 'featured_slider_height' );
    $slider_category  = creativ_musician_get_option( 'slider_category' );
    
    if( $sr_content_type == 'sr_page' ) :
        for( $i=1; $i<=$number_of_sr_items; $i++ ) :
            $featured_slider_posts[] = creativ_musician_get_option( 'slider_page_'.$i );
        endfor;  
    elseif( $sr_content_type == 'sr_post' ) :
        for( $i=1; $i<=$number_of_sr_items; $i++ ) :
            $featured_slider_posts[] = creativ_musician_get_option( 'slider_post_'.$i );
        endfor;
    endif;
    ?>
    
    <?php if( $sr_content_type == 'sr_page' ) : ?>
        <div class="featured-slider-wrapper" data-slick='{"slidesToShow": 1, "slidesToScroll": 1, "infinite": true, "speed": <?php echo esc_html( $featured_slider_speed ); ?>, "dots": true, "arrows":true, "autoplay": true, "fade": false }'>
            <?php $args = array (
                'post_type'     => 'page',
                'posts_per_page' => absint( $number_of_sr_items ),
                'post__in'      => $featured_slider_posts,
                'orderby'       =>'post__in',
            );   

            $loop = new WP_Query($args);                        
                if ( $loop->have_posts() ) :
                $i=-1;  
                    while ($loop->have_posts()) : $loop->the_post(); $i++;
                    $class='';
                    if ($i==0) {
                        $class='display-block';
                    } else{
                        $class='display-none';}
                    ?>
                        <article class="slick-item <?php echo esc_attr($class); ?>" style="background-image: url('<?php the_post_thumbnail_url( 'full' ); ?>'); padding: <?php echo esc_html( $featured_slider_height ); ?>px 0;">
                            <div class="overlay" style="opacity: <?php echo esc_html($featured_slider_overlay);?>;"></div>
                            <div class="wrapper">
                                <div class="featured-content-wrapper">
                                    <header class="entry-header">
                                        <h2 class="entry-title" style="font-size: <?php echo esc_html( $featured_slider_fontsize ); ?>px;"><a href="<?php the_permalink();?>"><?php the_title();?></a></h2>
                                    </header>
                                    
                                    <div class="entry-content">
                                        <?php
                                            $excerpt = creativ_musician_the_excerpt( 30 );
                                            echo wp_kses_post( wpautop( $excerpt ) );
                                        ?>
                                    </div><!-- .entry-content -->

                                    <div class="read-more">
                                        <?php $readmore_text = creativ_musician_get_option( 'readmore_text' );?>
                                        <a href="<?php the_permalink();?>" class="btn btn-primary"><?php echo esc_html($readmore_text);?></a>
                                    </div><!-- .read-more -->
                                </div><!-- .featured-content-wrapper -->
                            </div><!-- .wrapper -->
                        </article><!-- .slick-item -->
                    <?php endwhile;?>
                    <?php wp_reset_postdata();
                endif;?>
        </div><!-- .featured-slider-wrapper -->

    <?php elseif( $sr_content_type == 'sr_post' ) : ?>
        <div class="featured-slider-wrapper" data-slick='{"slidesToShow": 1, "slidesToScroll": 1, "infinite": true, "speed": <?php echo esc_html( $featured_slider_speed ); ?>, "dots": true, "arrows":true, "autoplay": true, "fade": false }'>
            <?php $args = array (
                'post_type'     => 'post',
                'posts_per_page' => absint( $number_of_sr_items ),
                'post__in'      => $featured_slider_posts,
                'orderby'       =>'post__in',
                'ignore_sticky_posts' => true,
            );   

            $loop = new WP_Query($args);                        
                if ( $loop->have_posts() ) :
                $i=-1;  
                    while ($loop->have_posts()) : $loop->the_post(); $i++;
                    $class='';
                    if ($i==0) {
                        $class='display-block';
                    } else{
                        $class='display-none';}
                    ?>
                        <article class="slick-item <?php echo esc_attr($class); ?>" style="background-image: url('<?php the_post_thumbnail_url( 'full' ); ?>'); padding: <?php echo esc_html( $featured_slider_height ); ?>px 0;">
                            <div class="overlay" style="opacity: <?php echo esc_html($featured_slider_overlay);?>;"></div>
                            <div class="wrapper">
                                <div class="featured-content-wrapper">
                                    <header class="entry-header">
                                        <h2 class="entry-title" style="font-size: <?php echo esc_html( $featured_slider_fontsize ); ?>px;"><a href="<?php the_permalink();?>"><?php the_title();?></a></h2>
                                    </header>
                                    
                                    <div class="entry-content">
                                        <?php
                                            $excerpt = creativ_musician_the_excerpt( 30 );
                                            echo wp_kses_post( wpautop( $excerpt ) );
                                        ?>
                                    </div><!-- .entry-content -->

                                    <div class="read-more">
                                        <?php $readmore_text = creativ_musician_get_option( 'readmore_text' );?>
                                        <a href="<?php the_permalink();?>" class="btn btn-primary"><?php echo esc_html($readmore_text);?></a>
                                    </div><!-- .read-more -->
                                </div><!-- .featured-content-wrapper -->
                            </div><!-- .wrapper -->
                        </article><!-- .slick-item -->
                    <?php endwhile;?>
                    <?php wp_reset_postdata();
                endif;?>
        </div><!-- .featured-slider-wrapper -->

    <?php else : ?>
        <div class="featured-slider-wrapper" data-slick='{"slidesToShow": 1, "slidesToScroll": 1, "infinite": true, "speed": <?php echo esc_html( $featured_slider_speed ); ?>, "dots": true, "arrows":true, "autoplay": true, "fade": false }'>
            <?php $args = array (
                'posts_per_page' =>absint( $number_of_sr_items ),
                   'post_type' => 'post',
                   'post_status' => 'publish',
                   'paged' => 1,
                   );
                   if ( absint( $slider_category ) > 0 ) {
                       $args['cat'] = absint( $slider_category );
                   }

            $loop = new WP_Query($args);                        
                if ( $loop->have_posts() ) :
                $i=-1;  
                    while ($loop->have_posts()) : $loop->the_post(); $i++;
                    $class='';
                    if ($i==0) {
                        $class='display-block';
                    } else{
                        $class='display-none';}
                    ?>
                        <article class="slick-item <?php echo esc_attr($class); ?>" style="background-image: url('<?php the_post_thumbnail_url( 'full' ); ?>'); padding: <?php echo esc_html( $featured_slider_height ); ?>px 0;">
                            <div class="overlay" style="opacity: <?php echo esc_html($featured_slider_overlay);?>;"></div>
                            <div class="wrapper">
                                <div class="featured-content-wrapper">
                                    <header class="entry-header">
                                        <h2 class="entry-title" style="font-size: <?php echo esc_html( $featured_slider_fontsize ); ?>px;"><a href="<?php the_permalink();?>"><?php the_title();?></a></h2>
                                    </header>
                                    
                                    <div class="entry-content">
                                        <?php
                                            $excerpt = creativ_musician_the_excerpt( 30 );
                                            echo wp_kses_post( wpautop( $excerpt ) );
                                        ?>
                                    </div><!-- .entry-content -->

                                    <div class="read-more">
                                        <?php $readmore_text = creativ_musician_get_option( 'readmore_text' );?>
                                        <a href="<?php the_permalink();?>" class="btn btn-primary"><?php echo esc_html($readmore_text);?></a>
                                    </div><!-- .read-more -->
                                </div><!-- .featured-content-wrapper -->
                            </div><!-- .wrapper -->
                        </article><!-- .slick-item -->
                    <?php endwhile;?>
                    <?php wp_reset_postdata();
                endif;?>
        </div><!-- .featured-slider-wrapper -->
    <?php endif;