<?php
/**
 * Single post partial template
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>

<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
	<div class="row bottom-30"><div class="col-sm-12 col-md-10 offset-md-1 col-lg-8 offset-lg-2">

		<div class="page-feature-banner bottom-30"><?php echo get_the_post_thumbnail( $post->ID, 'large' ); ?></div>

		<header class="entry-header">
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

			<div class="entry-meta">
				<?php echo( get_the_date() ); ?> - <?php echo( get_the_author() ); ?>
			</div><!-- .entry-meta -->

		</header><!-- .entry-header -->


		<div class="entry-content">

			<?php
			the_content();
			understrap_link_pages();
			?>

		</div><!-- .entry-content -->

		<footer class="entry-footer">

			<?php understrap_entry_footer(); ?>

		</footer><!-- .entry-footer -->

	</div></div><!-- end row col -->

</article><!-- #post-## -->
