<?php
/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Creativ Musician
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="post-item">
		<?php if( is_sticky() ){ ?>
	    	<div class="favourite"><i class="fa fa-star"></i></div>
		<?php } ?>

		<?php if ( has_post_thumbnail() ) { ?>
			<figure>
			    <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
			</figure>
		<?php } ?>

		<div class="entry-container">
			<?php if ( 'post' === get_post_type() ) : ?>
				<div class="entry-meta">
					<?php creativ_musician_posted_on();
					creativ_musician_entry_meta(); ?>
				</div><!-- .entry-meta -->
			<?php endif; ?>

			<header class="entry-header">
				<?php
				if ( is_single() ) :
					the_title( '<h1 class="entry-title">', '</h1>' );
				else :
					the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
				endif; ?>
			</header><!-- .entry-header -->

			<div class="entry-content">
				<?php the_excerpt(); ?>
			</div><!-- .entry-content -->

			<?php $readmore_text = creativ_musician_get_option( 'readmore_text' );?>
			<div class="read-more">
				<a class="btn" href="<?php the_permalink();?>"><?php echo esc_html($readmore_text);?></a>
			</div><!-- .read-more -->
		</div><!-- .entry-container -->
	</div><!-- .post-item -->
</article><!-- #post-## -->
