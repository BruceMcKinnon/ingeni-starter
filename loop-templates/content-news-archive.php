<?php
/**
 * The default template for displaying news archive content
 *
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>

<div class="col-sm-12 col-md-6 col-lg-4 news-item">
	<?php
	if ( has_post_thumbnail( $post->ID ) ) {
		$image_id = get_post_thumbnail_id( $post->ID );
		$image_title = get_the_title( $image_id );
		$image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', TRUE);
		if ( strlen(trim($image_alt)) < 1 ) {
			$image_alt = $image_title;
		}

		// Get the srcset
		$size = 'medium';
		$img_src = wp_get_attachment_image_url( $image_id, $size );
		$srcset = wp_get_attachment_image_srcset( $image_id , $size );

		$sizes = 'sizes="(max-width: 48px) 480px, (max-width: 640px) 640px, (max-width: 1200px) 1200px"';

		$hero_img = '<img src="'.$img_src.'" srcset="'.$srcset.'" '.$sizes.' loading="lazy" title="'.$image_title.'" alt="'.$image_alt.'" />';
	} ?>


	<a href="<?php the_permalink() ?>" aria-hidden="true">
		<div class="featured-hero" role="banner"><?php echo($hero_img); ?></div>
	</a>

	<div class="meta"><?php echo( get_the_date( ) ); ?></div>
	<a href="<?php the_permalink() ?>" aria-label="<?php the_title(); ?>">
		<h2><?php the_title(); ?></h2>
	</a>

	<?php 
		$button_text = "Read More";
	?>
	<a href="<?php the_permalink() ?>" class="button"><?php echo($button_text); ?></a>
</div>
