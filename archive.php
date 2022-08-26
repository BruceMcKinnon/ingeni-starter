<?php
/**
 * The template for displaying archive pages - incorporates AJAX infinitate loading
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

$cat = get_the_category();
$cat_name = $cat[0]->name;
if ($cat[0]->category_parent > 0) {
	$term = get_category( $cat[0]->category_parent );
	$cat_name = $term->name;
}

$container = get_theme_mod( 'understrap_container_type' );
?>

<div class="wrapper <?php echo($cat_name); ?>" id="archive-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" tabindex="-1">

		<div class="row">

			<main class="site-main" id="main">

				<?php
				if ( have_posts() ) {
					?>
					<header class="page-header">
						<h1><?php echo($cat_name); ?></h1>
						<?php the_archive_description( '<div class="taxonomy-description">', '</div>' ); ?>
					</header><!-- .page-header -->

					<div class="row top-bottom-30" id="archive_content">
					<?php
					// Start the loop.
					while ( have_posts() ) {
						the_post();

						/*
						 * Use a category-specific content template
						*/
						get_template_part( 'loop-templates/content', $cat_name.'-archive' );
					}
					?></div><?php
				} else {
					get_template_part( 'loop-templates/content', 'none' );
				}
				?>

			</main><!-- #main -->


		</div><!-- .row -->

	</div><!-- #content -->

	<div class="load_more_loader"><!-- .load_more_loader -->
		<?php
		// AJAX load more button
		if (  $wp_query->max_num_pages > 1 )
			echo '<a class="button" id="load_more">Load More</a>';
		?>
	</div>


</div><!-- #archive-wrapper -->

<?php
get_footer();
