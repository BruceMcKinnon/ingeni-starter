<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


// If a feature image is set, get the id, so it can be injected as a css background property
$inline_style = $hero_img = '';

if ( has_post_thumbnail( $post->ID ) ) {
	$image_id = get_post_thumbnail_id( $post->ID );
	$image_title = get_the_title( $image_id );
	$image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', TRUE);
	if ( strlen(trim($image_alt)) < 1 ) {
		$image_alt = $image_title;
	}

	// Get the srcset
	$size = 'large';
	$img_src = wp_get_attachment_image_url( $image_id, $size );
	$srcset = wp_get_attachment_image_srcset( $image_id , $size );

	$sizes = 'sizes="(max-width: 48px) 480px, (max-width: 640px) 640px, (max-width: 1200px) 1200px"';

	$hero_img = '<img src="'.$img_src.'" srcset="'.$srcset.'" '.$sizes.' loading="lazy" title="'.$image_title.'" alt="'.$image_alt.'" />';

?>

	<div class="d-flex align-items-center justify-content-center page-feature-banner">
		<?php echo ($hero_img); ?>
		<?php if (!is_single()) { the_title( '<h1 class="entry-title">', '</h1>' ); } ?>
	</div>

<?php }
