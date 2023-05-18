<?php
/**
 * Partial template for content in page.php
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>

<article <?php post_class( $post->post_name ); ?> id="post-<?php the_ID(); ?>">

	<?php 
	// Don't repeat the featured image and H1 if using one of these templates
	if (!is_page_template(
		array(
			'page-templates/fullwidthpage.php',
			'page-templates/fullwidthfrontpage.php',
			'page-templates/right-sidebarpage.php',
			'page-templates/left-sidebarpage.php'
		))) { ?>
		<header class="entry-header">

			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

		</header><!-- .entry-header -->

		<?php echo get_the_post_thumbnail( $post->ID, 'large' ); ?>

	<?php } ?>

	<div class="entry-content">

		<?php
		the_content();
		understrap_link_pages();
		?>

	</div><!-- .entry-content -->

	<footer class="entry-footer">

		<?php understrap_edit_post_link(); ?>

	</footer><!-- .entry-footer -->

</article><!-- #post-## -->
