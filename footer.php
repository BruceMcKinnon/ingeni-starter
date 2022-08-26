<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$container = get_theme_mod( 'understrap_container_type' );
?>

<?php get_template_part( 'sidebar-templates/sidebar', 'footerfull' ); ?>

<footer class="footer">
	<div class="container <?php echo esc_attr( $container ); ?>">
		<div class="row d-flex align-items-center">

			<div class="col-12 col-md-6 col-lg-3">
				<?php dynamic_sidebar( 'footer-one-widget' ); ?>
			</div><!--col end -->

			<div class="col-12 col-md-6 col-lg-3">
				<?php dynamic_sidebar( 'footer-two-widget' ); ?>
			</div><!--col end -->

			<div class="col-12 col-md-6 col-lg-3">
				<?php dynamic_sidebar( 'footer-three-widget' ); ?>
			</div><!--col end -->

			<div class="col-12 col-md-6 col-lg-3">
				<?php dynamic_sidebar( 'footer-four-widget' ); ?>
			</div><!--col end -->

		</div><!-- row end -->

	</div><!-- container end -->

</footer><!-- footer end -->


<div class="copyright">
	<div class="container <?php echo esc_attr( $container ); ?>">
		<div class="row">

			<div class="col-12 text-center">

				<?php footer_copyright(); ?>
								
			</div><!--col end -->

		</div><!-- row end -->

	</div><!-- container end -->

</div><!-- copyright -->


</div><!-- #page we need this extra closing tag here -->

<?php wp_footer(); ?>

</body>

</html>
