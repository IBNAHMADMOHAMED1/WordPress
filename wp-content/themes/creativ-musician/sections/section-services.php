<?php 
/**
 * Template part for displaying Services Section
 *
 *@package Creativ Musician
 */

    $ss_content_type     = creativ_musician_get_option( 'ss_content_type' );
    $number_of_ss_items  = creativ_musician_get_option( 'number_of_ss_items' );
    $services_category  = creativ_musician_get_option( 'services_category' );

    if( $ss_content_type == 'ss_page' ) :
        for( $i=1; $i<=$number_of_ss_items; $i++ ) :
            $featured_services_posts[] = creativ_musician_get_option( 'services_page_'.$i );
        endfor;  
    elseif( $ss_content_type == 'ss_post' ) :
        for( $i=1; $i<=$number_of_ss_items; $i++ ) :
            $featured_services_posts[] = creativ_musician_get_option( 'services_post_'.$i );
        endfor;
    endif;
    ?>

    <?php if( $ss_content_type == 'ss_page' ) : ?>
        <div class="section-content">
            <?php $args = array (
                'post_type'     => 'page',
                'posts_per_page' => absint( $number_of_ss_items ),
                'post__in'      => $featured_services_posts,
                'orderby'       =>'post__in',
            );        
            $loop = new WP_Query($args);                        
            if ( $loop->have_posts() ) :
            $i=-1;  
                while ($loop->have_posts()) : $loop->the_post(); $i++;?>
                
                <article>
                    <div class="featured-image" style="background-image: url('<?php the_post_thumbnail_url( 'full' ); ?>');">
                        <a href="<?php echo the_permalink();?>" class="post-thumbnail-link"></a>
                    </div><!-- .featured-image -->

                    <div class="entry-container">
                        <header class="section-header">
                            <h2 class="section-title"><a href="<?php echo the_permalink();?>"><?php the_title();?></a></h2>
                        </header>

                        <div class="entry-content">
                            <?php
                                $excerpt = creativ_musician_the_excerpt( 50 );
                                echo wp_kses_post( wpautop( $excerpt ) );
                            ?>
                        </div><!-- .entry-content -->

                        <div class="read-more">
                            <?php $readmore_text = creativ_musician_get_option( 'readmore_text' );?>
                            <a href="<?php the_permalink();?>" class="btn"><?php echo esc_html($readmore_text);?></a>
                        </div><!-- .read-more -->
                    </div><!-- .entry-container -->
                </article>

              <?php endwhile;?>
              <?php wp_reset_postdata(); ?>
            <?php endif;?>
        </div>

    <?php elseif( $ss_content_type == 'ss_post' ) : ?>
        <div class="section-content">
            <?php $args = array (
                'post_type'     => 'post',
                'posts_per_page' => absint( $number_of_ss_items ),
                'post__in'      => $featured_services_posts,
                'orderby'       =>'post__in',
            );        
            $loop = new WP_Query($args);                        
            if ( $loop->have_posts() ) :
            $i=-1;  
                while ($loop->have_posts()) : $loop->the_post(); $i++;?>
                
                <article>
                    <div class="featured-image" style="background-image: url('<?php the_post_thumbnail_url( 'full' ); ?>');">
                        <a href="<?php echo the_permalink();?>" class="post-thumbnail-link"></a>
                    </div><!-- .featured-image -->

                    <div class="entry-container">
                        <header class="section-header">
                            <h2 class="section-title"><a href="<?php echo the_permalink();?>"><?php the_title();?></a></h2>
                        </header>

                        <div class="entry-content">
                            <?php
                                $excerpt = creativ_musician_the_excerpt( 50 );
                                echo wp_kses_post( wpautop( $excerpt ) );
                            ?>
                        </div><!-- .entry-content -->

                        <div class="read-more">
                            <?php $readmore_text = creativ_musician_get_option( 'readmore_text' );?>
                            <a href="<?php the_permalink();?>" class="btn"><?php echo esc_html($readmore_text);?></a>
                        </div><!-- .read-more -->
                    </div><!-- .entry-container -->
                </article>

              <?php endwhile;?>
              <?php wp_reset_postdata(); ?>
            <?php endif;?>
        </div>

    <?php else: ?>
        <div class="section-content">
            <?php $args = array (
                'posts_per_page' =>absint( $number_of_ss_items ),
                   'post_type' => 'post',
                   'post_status' => 'publish',
                   'paged' => 1,
                   );
                   if ( absint( $services_category ) > 0 ) {
                       $args['cat'] = absint( $services_category );
                   }      
            $loop = new WP_Query($args);                        
            if ( $loop->have_posts() ) :
            $i=-1;  
                while ($loop->have_posts()) : $loop->the_post(); $i++;?>
                
                <article>
                    <div class="featured-image" style="background-image: url('<?php the_post_thumbnail_url( 'full' ); ?>');">
                        <a href="<?php echo the_permalink();?>" class="post-thumbnail-link"></a>
                    </div><!-- .featured-image -->

                    <div class="entry-container">
                        <header class="section-header">
                            <h2 class="section-title"><a href="<?php echo the_permalink();?>"><?php the_title();?></a></h2>
                        </header>

                        <div class="entry-content">
                            <?php
                                $excerpt = creativ_musician_the_excerpt( 50 );
                                echo wp_kses_post( wpautop( $excerpt ) );
                            ?>
                        </div><!-- .entry-content -->

                        <div class="read-more">
                            <?php $readmore_text = creativ_musician_get_option( 'readmore_text' );?>
                            <a href="<?php the_permalink();?>" class="btn"><?php echo esc_html($readmore_text);?></a>
                        </div><!-- .read-more -->
                    </div><!-- .entry-container -->
                </article>

              <?php endwhile;?>
              <?php wp_reset_postdata(); ?>
            <?php endif;?>
        </div>

    <?php endif;