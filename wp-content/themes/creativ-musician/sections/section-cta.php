<?php 
/**
 * Template part for displaying About Section
 *
 *@package Creativ Musician
 */
?>
    <?php 
        $cta_overlay            = creativ_musician_get_option( 'cta_overlay' );
        $cta_small_description  = creativ_musician_get_option( 'cta_small_description' );
        $cta_description        = creativ_musician_get_option( 'cta_description' );
        $cta_button_label       = creativ_musician_get_option( 'cta_button_label' );
        $cta_button_url         = creativ_musician_get_option( 'cta_button_url' );
    ?>

    <div class="overlay" style="opacity: <?php echo esc_html($cta_overlay);?>;"></div>
    <div class="wrapper">
        <?php if ( !empty($cta_description ) )  :?>
            <div class="section-header">
                <h2 class="section-title"><?php echo esc_html($cta_description); ?></h2>
                <p><?php echo esc_html($cta_small_description); ?></p>
            </div><!-- .section-header -->
        <?php endif;?>

        <?php if ( !empty($cta_button_label ) )  :?>
            <div class="read-more">
                <a href="<?php echo esc_url($cta_button_url); ?>" class="btn"><?php echo esc_html($cta_button_label); ?></a>
            </div><!-- .read-more -->
        <?php endif;?>
    </div><!-- .wrapper -->

