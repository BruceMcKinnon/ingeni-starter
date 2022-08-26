<?php
/**
 * The template part for displaying a message that posts cannot be found
 *
 * Learn more: {@link https://codex.wordpress.org/Template_Hierarchy}
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>

<header class="hide page-header">
	<h1 class="page-title"><?php _e( 'Nothing Found', 'foundationpress' ); ?></h1>
</header>

<div class="page-content">
	<?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>

	<p>
		<?php
			/* translators: %1$s: new post url */
			printf( __(
				'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'foundationpress' ),
				admin_url( 'post-new.php' )
			);
		?>
	</p>

	<?php elseif ( is_search() ) : ?>

	<p><?php _e( '<p>Sorry, but nothing matched your search terms.</p><p>Please try again with some different keywords.</p>', 'foundationpress' ); ?></p>
	<?php get_search_form(); ?>

	<?php else : ?>

	<p><?php _e( '</p>It seems we can&rsquo;t find what you&rsquo;re looking for.</p><p>Perhaps searching can help.</p>', 'foundationpress' ); ?></p>
	<?php get_search_form(); ?>

	<?php endif; ?>
</div>
