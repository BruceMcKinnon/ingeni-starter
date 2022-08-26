<?php
/**
 * Understrap Child Theme functions and definitions
 *
 * @package UnderstrapChild
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;



/**
 * Removes the parent themes stylesheet and scripts from inc/enqueue.php
 */
function understrap_remove_scripts() {
	wp_dequeue_style( 'understrap-styles' );
	wp_deregister_style( 'understrap-styles' );

	wp_dequeue_script( 'understrap-scripts' );
	wp_deregister_script( 'understrap-scripts' );
}
add_action( 'wp_enqueue_scripts', 'understrap_remove_scripts', 20 );



/**
 * Enqueue our stylesheet and javascript file
 */
function theme_enqueue_styles() {

	// Get the theme data.
	$the_theme = wp_get_theme();

	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	// Grab asset urls.
	$theme_styles  = "/css/child-theme{$suffix}.css";
	$theme_scripts = "/js/child-theme{$suffix}.js";

	wp_enqueue_style( 'child-understrap-styles', get_stylesheet_directory_uri() . $theme_styles, array(), $the_theme->get( 'Version' ) );
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'child-understrap-scripts', get_stylesheet_directory_uri() . $theme_scripts, array(), $the_theme->get( 'Version' ), true );
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	wp_enqueue_script( 'ingeni-starter-js', get_stylesheet_directory_uri(). '/js/ingeni-starter.js', array(), 0, true );
	wp_enqueue_script( 'browser-zoom-js', get_stylesheet_directory_uri(). '/js/browser_zoom.js', array(), 0, true );

	posts_load_more_script(); // Don't forget to enqueue the AJAX post loader
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );




function posts_load_more_script() {
	global $wp_query; 

	// register the js but do not enqueue it yet
	wp_register_script( 'posts_load_more_js', get_stylesheet_directory_uri(). '/js/posts_load_more.js', array('jquery') );
 
	// now the most interesting part
	// we have to pass parameters to posts_load_more.js script but we can get the parameters values only in PHP
	// you can define variables directly in your HTML but I decided that the most proper way is wp_localize_script()
	wp_localize_script( 'posts_load_more_js', 'misha_loadmore_params', array(
		'ajaxurl' => site_url() . '/wp-admin/admin-ajax.php', // WordPress AJAX
		'posts' => json_encode( $wp_query->query_vars ), // everything about your loop is here
		'current_page' => get_query_var( 'paged' ) ? get_query_var('paged') : 1,
		'max_page' => $wp_query->max_num_pages
	) );

 	wp_enqueue_script( 'posts_load_more_js' );
}


function misha_loadmore_ajax_handler(){
	$limit = get_option('posts_per_page');

	// prepare our arguments for the query
	$args = json_decode( stripslashes( $_POST['query'] ), true );

	$args['paged'] = $_POST['page'] + 1; // we need next page to be loaded
	$args['post_status'] = 'publish';
	$args['posts_per_page'] = $limit;
	$args['posts_per_archive_page'] = $limit;

	// it is always better to use WP_Query but not here
	query_posts( $args );

	if( have_posts() ) {
		// run the loop
		while( have_posts() ) {
			the_post();

			$cat_name = '';
			$cat = get_the_category();
			if ( $cat ) {
				$cat_name = $cat[0]->name;
				if ($cat[0]->category_parent > 0) {
					$term = get_category( $cat[0]->category_parent );
					$cat_name = $term->name;
				}
			}

			if ( ($cat_name == '') && ( in_category( array("insights-news","legal-insight","news") ) ) ) {
				$cat_name = 'news';
			}
			get_template_part( 'loop-templates/content', $cat_name.'-archive' );
		}
	}
	die; // here we exit the script and even no wp_reset_query() required!
}
add_action('wp_ajax_loadmore', 'misha_loadmore_ajax_handler'); // wp_ajax_{action}
add_action('wp_ajax_nopriv_loadmore', 'misha_loadmore_ajax_handler'); // wp_ajax_nopriv_{action}






/**
 * Load the child theme's text domain
 */
function add_child_theme_textdomain() {
	load_child_theme_textdomain( 'understrap-child', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'add_child_theme_textdomain' );



/**
 * Overrides the theme_mod to default to Bootstrap 5
 *
 * This function uses the `theme_mod_{$name}` hook and
 * can be duplicated to override other theme settings.
 *
 * @param string $current_mod The current value of the theme_mod.
 * @return string
 */
function understrap_default_bootstrap_version( $current_mod ) {
	return 'bootstrap5';
}
add_filter( 'theme_mod_understrap_bootstrap_version', 'understrap_default_bootstrap_version', 20 );



/**
 * Loads javascript for showing customizer warning dialog.
 */
function understrap_child_customize_controls_js() {
	wp_enqueue_script(
		'understrap_child_customizer',
		get_stylesheet_directory_uri() . '/js/customizer-controls.js',
		array( 'customize-preview' ),
		'20130508',
		true
	);
}
add_action( 'customize_controls_enqueue_scripts', 'understrap_child_customize_controls_js' );










// Let the text widgets use shortcodes
add_filter( 'widget_text', 'do_shortcode');

// Add custom shortcodes
function get_child_theme_path() {
// Return the path to the child theme
return get_stylesheet_directory_uri();
}
add_shortcode( 'child_theme_path', 'get_child_theme_path' );

function url_shortcode() {
// Return the site URL
return get_bloginfo('url');
}
add_shortcode('url','url_shortcode');

function bloginfo_shortcode( $atts ) {
$show_atts = shortcode_atts( array(
			'show' => 'name',
	), $atts );
return get_bloginfo( $show_atts['show'] );
}
add_shortcode('bloginfo','bloginfo_shortcode');


function get_site_name() {
	return '<strong>'.get_bloginfo("title").'</strong>';
}
add_shortcode("site-name","get_site_name");


function get_site_logo() {
	return '<div class="logo"></div>';
}
add_shortcode("site-logo","get_site_logo");



if ( !function_exists("is_local") ) {
	function is_local() {
		$local_install = false;
		if ( ($_SERVER['SERVER_NAME']=='localhost') || ($_SERVER['SERVER_NAME']=='dev.local') ) {
			$local_install = true;
		}
		return $local_install;
	}
}

if (!function_exists("get_local_upload_url")) {
	function get_local_upload_path() {
		$upload_dir = wp_upload_dir();
		return $upload_dir['baseurl'];
	}
}

if (!function_exists("fb_log")) {
	function fb_log($msg) {
		$upload_dir = wp_upload_dir();
		$logFile = $upload_dir['basedir'] . '/' . 'fb_log.txt';
		date_default_timezone_set('Australia/Sydney');

		// Now write out to the file
		$log_handle = fopen($logFile, "a");
		if ($log_handle !== false) {
			fwrite($log_handle, date("H:i:s").": ".$msg."\r\n");
			fclose($log_handle);
		}
	}
}

if (!function_exists("startsWith")) {
	function startsWith($haystack, $needle) {
			// search backwards starting from haystack length characters from the end
			return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
	}
}
if (!function_exists("endsWith")) {
	function endsWith($haystack, $needle) {
			// search forward starting from end minus needle length characters
			return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
	}
}
if (!function_exists("bool2str")) {
	function bool2str($value) {
		if ($value)
			return 'true';
		else
			return 'false';
	}
}





function unregister_default_footer_widgets() {
	unregister_sidebar ( 'footer-widgets' );
}
add_action( 'widgets_init', 'unregister_default_footer_widgets', 11);

if (function_exists('register_sidebar')) {
	register_sidebar(array(
			'name' => 'Footer One',
			'id'   => 'footer-one-widget',
			'description'   => 'Footer One widget position.',
			'before_widget' => '<div id="%1$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3>',
			'after_title'   => '</h3>'
	));

	register_sidebar(array(
			'name' => 'Footer Two',
			'id'   => 'footer-two-widget',
			'description'   => 'Footer Two widget position.',
			'before_widget' => '<div id="%1$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3>',
			'after_title'   => '</h3>'
	));

	register_sidebar(array(
		'name' => 'Footer Three',
		'id'   => 'footer-three-widget',
		'description'   => 'Footer Three widget position.',
		'before_widget' => '<div id="%1$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3>',
		'after_title'   => '</h3>'
	));

	register_sidebar(array(
		'name' => 'Footer Four',
		'id'   => 'footer-four-widget',
		'description'   => 'Footer Four widget position.',
		'before_widget' => '<div id="%1$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3>',
		'after_title'   => '</h3>'
	));


	register_sidebar(array(
		'name' => 'Header One',
		'id'   => 'header-one-widget',
		'description'   => 'Header One widget position.',
		'before_widget' => '<div id="%1$s" class="header-one-widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4>',
		'after_title'   => '</h4>'
	));
	

}




add_shortcode('background-it','backgrounder');
function backgrounder( $atts ) {
	$bg = shortcode_atts( array(
			'img' => '',
			'width' => '',
			'height' => '',
			'link' => '',
			'class' => '',
			'id' => '',
			'position' => 'center',
			'size' => 'contain',
	), $atts );
	
	//fb_log($bg['class']);
	$retHtml = "<div";
	if ($bg['class'] != '')
		$retHtml .= ' class="'.$bg['class'].'"';
	if ($bg['id'] != '')
		$retHtml .= ' id="'.$bg['id'].'"';
	
	$retHtml .= ' style="background: url('.get_bloginfo('url').$bg['img'].') no-repeat;';
	
	if ($bg['position'] != '') {
		$retHtml .= ' background-position:'.$bg['position'].';';
	} else {
		$retHtml .= ' background-position:center;';
	}
	if ($bg['position'] != '') {
		$retHtml .= ' background-size:'.$bg['size'].';';
	} else {
		$retHtml .= ' background-size:contain;';
	}
	
	if ($bg['width'] != '')
		$retHtml .= 'width:'.$bg['width'].';';
	//if ($bg['height'] != '')
		//$retHtml .= 'height:'.$bg['height'].';';
	
	$retHtml .= '" ></div>';
	
	if ($bg['link'] != '')
		$retHtml = '<a href="'.$bg['link'].'" target="_blank">'.$retHtml.'</a>';
	//fb_log($retHtml);
	return $retHtml;
}


add_filter( 'get_custom_logo',  'ingeni_custom_logo' );
function ingeni_custom_logo ( $html ) {

	$custom_logo_id = get_theme_mod( 'custom_logo' );
	$url = get_bloginfo('url');
	$site_name = get_bloginfo('name');

	$logo_url = wp_get_attachment_image_src( $custom_logo_id, 'full', false);
	if ( strlen($logo_url[0]) > 0 ) {
		$extra_style = '<div class="logo" style="background-image: url('.$logo_url[0].');" title="'.$site_name.'"></div>';
	} else {
		$extra_style = $site_name;
	}

	$retHtml = '<a href="' . $url . '" rel="home" itemprop="url">' . $extra_style . '</a>';

	return $retHtml;    
}



function bl_get_social_icons( $atts ) {
	$params = shortcode_atts( array(
				'show_phone' => '1',
				'show_socials' => '1',
				'show_email' => '1',
				'show_facebook' => '1',
				'show_instagram' => '1',
				'show_twitter' => '0',
				'show_linkedin' => '0',
				'show_youtube' => '0',
				'class' => 'sub-footer-socials',
	), $atts );

//fb_log('params:'.print_r($params,true));

	$retHtml = '<div class="' . $params['class'] . '">';
	
	if ( $params['show_socials'] !== '0' ) {

		if ( $params['show_youtube'] !== '0' ) {
			$url = do_shortcode('[blcontact type="youtube"]');
			if (strlen($url) > 0) {
				$retHtml .= '<a href="'.$url.'" target="_blank" aria-label="youtube"><i class="fab fa-youtube"></i></a>';
			}
		}

		if ( $params['show_facebook'] !== '0' ) {
			$url = do_shortcode('[blcontact type="facebook"]');
			if (strlen($url) > 0) {
				$retHtml .= '<a href="'.$url.'" target="_blank" aria-label="facebook"><i class="fab fa-facebook-f"></i></a>';
			}
		}

		if ( $params['show_linkedin'] !== '0' ) {
			$url = do_shortcode('[blcontact type="linkedin"]');
			if (strlen($url) > 0) {
				$retHtml .= '<a href="'.$url.'" target="_blank" aria-label="linkedin"><i class="fab fa-linkedin-in"></i></a>';
			}
		}

		if ( $params['show_twitter'] !== '0' ) {
			$url = do_shortcode('[blcontact type="twitter"]');
			if (strlen($url) > 0) {
				$retHtml .= '<a href="'.$url.'" target="_blank" aria-label="twitter"><i class="fab fa-twitter"></i></a>';
			}
		}

		if ( $params['show_instagram'] !== '0' ) {
			$url = do_shortcode('[blcontact type="instagram"]');
			if (strlen($url) > 0) {
				$retHtml .= '<a href="'.$url.'" target="_blank" aria-label="instagram"><i class="fab fa-instagram"></i></a>';
			}
		}
	}


	if ( $params['show_email'] !== '0' ) {
		$url = do_shortcode('[blcontact type="email" nolink="1" rawtext="0"]');
		if (strlen($url) > 0) {
			$retHtml .= '<a href="mailto://'.str_replace(' ','',$url).'" target="_blank" class="envelope" aria-label="email"><i class="far fa-envelope"></i></a>';
		}
	}


	if ( $params['show_phone'] !== '0' ) {
		$url = do_shortcode('[blcontact type="phone" nolink="1" rawtext="0"]');
		if (strlen($url) > 0) {
			$retHtml .= '<a href="tel://'.str_replace(' ','',$url).'" target="_blank" class="phone" aria-label="phone"><i class="fas fa-phone-alt"></i></a>';
		}
	}


	$retHtml .= '</div>';

	return $retHtml;
}
add_shortcode('bl-social-icons', 'bl_get_social_icons');



// Grab the hero image of a page or post and pre-load it.
function ingeni_preload_hero() {
	$postID = get_current_blog_id();
	$postID = get_queried_object_id();

	if ( has_post_thumbnail( $postID ) ) {
		$image_id = get_post_thumbnail_id( $postID );
		$size = 'large';
		$img_src = wp_get_attachment_image_url( $image_id, $size );
		$srcset = wp_get_attachment_image_srcset( $image_id , $size );

		echo('<link rel="preload" href="'.$img_src.'"  srcset="'.$srcset.'" as="image">');
	}
}
add_action('wp_head', 'ingeni_preload_hero');


function footer_copyright() {
	$retHtml = '<div class="footer-copyright">';

	$retHtml .= '&copy '.date("Y").' '.get_bloginfo('sitename').'<div class="copy-dot"></div>';

	$retHtml .= 'All Rights Reserved<div class="copy-dot"></div>';
	$retHtml .= '<a href="'.get_bloginfo('url').'/privacy-policy">Privacy Policy</a>';
	
	$retHtml .= '</div>';
	echo $retHtml;
}
