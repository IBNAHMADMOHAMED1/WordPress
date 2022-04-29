<?php 
/**
 * Template part for displaying additional_info Section
 *
 *@package Creativ Musician
 */

    $ad_content_type                = creativ_musician_get_option( 'ad_content_type' );
    $number_of_column               = creativ_musician_get_option( 'number_of_column' );
    $number_of_items                = creativ_musician_get_option( 'number_of_items' );
    $additional_info_icon_container = creativ_musician_get_option( 'additional_info_icon_container' );
    $additional_info_icon_fontsize  = creativ_musician_get_option( 'additional_info_icon_fontsize' );
    $additional_info_title_fontsize = creativ_musician_get_option( 'additional_info_title_fontsize' );
    $additional_info_category       = creativ_musician_get_option( 'additional_info_category' );

    if( $ad_content_type == 'ad_page' ) :
        for( $i=1; $i<=$number_of_items; $i++ ) :
            $additional_info_posts[] = creativ_musician_get_option( 'additional_info_page_'.$i );
        endfor;  
    elseif( $ad_content_type == 'ad_post' ) :
        for( $i=1; $i<=$number_of_items; $i++ ) :
            $additional_info_posts[] = creativ_musician_get_option( 'additional_info_post_'.$i );
        endfor;
    endif;
    ?>

    <?php if( $ad_content_type == 'ad_page' ) : ?>
        <div class="section-content clear col-<?php echo esc_attr( $number_of_column ); ?>">
            <?php $args = array (
                'post_type'     => 'page',
                'posts_per_page' => absint( $number_of_items ),
                'post__in'      => $additional_info_posts,
                'orderby'       =>'post__in',
            );  

            $loop = new WP_Query($args);                        
            if ( $loop->have_posts() ) :
            $i=-1; $j=0;  
                while ($loop->have_posts()) : $loop->the_post(); $i++; $j++;
                $additional_info_icons[$j] = creativ_musician_get_option( 'additional_info_icon_'.$j ); ?>        
                
                <article>
                    <?php if( !empty( $additional_info_icons[$j] ) ) : ?>
                        <div class="icon-container" style="width: <?php echo esc_html($additional_info_icon_container);?>px; height: <?php echo esc_html($additional_info_icon_container);?>px;">
                            <i class="<?php echo esc_attr( $additional_info_icons[$j] )?>" style="font-size: <?php echo esc_html($additional_info_icon_fontsize);?>px;"></i>
                        </div><!-- .icon-container -->
                    <?php endif; ?>
                    
                    <header class="entry-header">
                        <h2 class="entry-title" style="font-size: <?php echo esc_html($additional_info_title_fontsize);?>px;"><a href="<?php the_permalink();?>"><?php the_title();?></a></h2>
                    </header>

                    <div class="entry-content">
                        <?php
                            $excerpt = creativ_musician_the_excerpt( 20 );
                            echo wp_kses_post( wpautop( $excerpt ) );
                        ?>
                    </div><!-- .entry-content -->
                </article>

              <?php endwhile;?>
              <?php wp_reset_postdata(); ?>
            <?php endif;?>
        </div><!-- .section-content -->

    <?php elseif( $ad_content_type == 'ad_post' ) : ?>
        <div class="section-content clear col-<?php echo esc_attr( $number_of_column ); ?>">
            <?php $args = array (
                'post_type'     => 'post',
                'posts_per_page' => absint( $number_of_items ),
                'post__in'      => $additional_info_posts,
                'orderby'       =>'post__in',
                'ignore_sticky_posts' => true,
            );   
            $loop = new WP_Query($args);                        
            if ( $loop->have_posts() ) :
            $i=-1; $j=0;  
                while ($loop->have_posts()) : $loop->the_post(); $i++; $j++;
                $additional_info_icons[$j] = creativ_musician_get_option( 'additional_info_icon_'.$j ); ?>        
                
                <article>
                    <?php if( !empty( $additional_info_icons[$j] ) ) : ?>
                        <div class="icon-container" style="width: <?php echo esc_html($additional_info_icon_container);?>px; height: <?php echo esc_html($additional_info_icon_container);?>px;">
                            <i class="<?php echo esc_attr( $additional_info_icons[$j] )?>" style="font-size: <?php echo esc_html($additional_info_icon_fontsize);?>px;"></i>
                        </div><!-- .icon-container -->
                    <?php endif; ?>
                    
                    <header class="entry-header">
                        <h2 class="entry-title" style="font-size: <?php echo esc_html($additional_info_title_fontsize);?>px;"><a href="<?php the_permalink();?>"><?php the_title();?></a></h2>
                    </header>

                    <div class="entry-content">
                        <?php
                            $excerpt = creativ_musician_the_excerpt( 20 );
                            echo wp_kses_post( wpautop( $excerpt ) );
                        ?>
                    </div><!-- .entry-content -->
                </article>

              <?php endwhile;?>
              <?php wp_reset_postdata(); ?>
            <?php endif;?>
        </div><!-- .section-content -->

    <?php else : ?>
        <div class="section-content clear col-<?php echo esc_attr( $number_of_column ); ?>">
            <?php $args = array (
                'posts_per_page' =>absint( $number_of_items ),
                   'post_type' => 'post',
                   'post_status' => 'publish',
                   'paged' => 1,
                   );
                   if ( absint( $additional_info_category ) > 0 ) {
                       $args['cat'] = absint( $additional_info_category );
                   }
            $loop = new WP_Query($args);                        
            if ( $loop->have_posts() ) :
            $i=-1; $j=0;  
                while ($loop->have_posts()) : $loop->the_post(); $i++; $j++;
                $additional_info_icons[$j] = creativ_musician_get_option( 'additional_info_icon_'.$j ); ?>        
                
                <article>
                    <?php if( !empty( $additional_info_icons[$j] ) ) : ?>
                        <div class="icon-container" style="width: <?php echo esc_html($additional_info_icon_container);?>px; height: <?php echo esc_html($additional_info_icon_container);?>px;">
                            <i class="<?php echo esc_attr( $additional_info_icons[$j] )?>" style="font-size: <?php echo esc_html($additional_info_icon_fontsize);?>px;"></i>
                        </div><!-- .icon-container -->
                    <?php endif; ?>
                    
                    <header class="entry-header">
                        <h2 class="entry-title" style="font-size: <?php echo esc_html($additional_info_title_fontsize);?>px;"><a href="<?php the_permalink();?>"><?php the_title();?></a></h2>
                    </header>

                    <div class="entry-content">
                        <?php
                            $excerpt = creativ_musician_the_excerpt( 20 );
                            echo wp_kses_post( wpautop( $excerpt ) );
                        ?>
                    </div><!-- .entry-content -->
                </article>

              <?php endwhile;?>
              <?php wp_reset_postdata(); ?>
            <?php endif;?>
        </div><!-- .section-content -->
    <?php endif;