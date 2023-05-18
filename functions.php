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



add_action( 'init', 'ingeni_add_posts_menuorder' );
function ingeni_add_posts_menuorder() {
	add_post_type_support( 'post, page', 'page-attributes' );
	add_post_type_support( 'page', 'excerpt' );
}




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
		$extra_style = '<div class="logo" style="background-image: url('.$logo_url[0].');" alt="'.$site_name.' logo"><span class="hide">'.$site_name.'</span></div>';
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



add_shortcode('bl-next-events','bl_get_next_events');
function bl_get_next_events( $atts ) {
	$params = shortcode_atts( array(
		'max_posts' => 6,
		'after_link' => tribe_get_upcoming_link(),
		'after_link_text' => 'See all events'
	), $atts );

  global $wpdb;

  $retHtml = "";

  $sql = $wpdb->prepare( "SELECT DISTINCT $wpdb->posts.*, $wpdb->postmeta.meta_value, $wpdb->postmeta.meta_key FROM $wpdb->posts INNER JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id WHERE (post_type=%s) AND (post_status=%s) AND ($wpdb->postmeta.meta_key = '_EventStartDate') AND ($wpdb->postmeta.meta_value > UTC_TIMESTAMP()) ORDER BY $wpdb->postmeta.meta_value ASC LIMIT %d", "tribe_events", "publish", $params['max_posts'] );
  $events = $wpdb->get_results( $sql ) ;

  $hide_class = "";
  if ( $events ) {
    $retHtml = '<div class="row upcoming-events" >';

    $retHtml .= '<div class="col-sm-12"><h2>Upcoming Events</h2></div>';
    $mobile_bg_image = "";

    foreach ( $events as $event )  {

		$image_id = get_post_thumbnail_id( $event->ID );

		$hero_img = '';

		if ( $image_id ) {
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
		}
              
		$retHtml .= '<div class="col-sm-12 col-md-6 col-lg-4 event">';

			$retHtml .= '<a href="' . get_the_permalink($event->ID) . '">';
			$retHtml .= $hero_img;

			$retHtml .= '<div class="row event-content d-flex align-items-center">';
				$retHtml .= '<div class="col-2 event_date text-center">';
					$retHtml .= '<p class="dow">' . tribe_get_start_date($event->ID,false,"D") . '</p>';
					$retHtml .= '<p class="dom">' . tribe_get_start_date($event->ID,false,"j") . '</p>';
					$retHtml .= '<p class="mth">' . tribe_get_start_date($event->ID,false,"M") . '</p>';
				$retHtml .= '</div>';
				$retHtml .= '<div class="col-10 event_info">';
					$retHtml .= '<p class="time">' . tribe_get_start_time($event->ID,false,"HH:MM") . ' - '. tribe_get_end_time($event->ID,false,"HH:MM") .'</p>';
					$retHtml .= '<h3>' . get_the_title($event) . '</h3>';
				$retHtml .= '</div>';
			$retHtml .= '</div>';
			$retHtml .= '</a>';

		$retHtml .= '</div>'; // End of col
    }
	if ( $params['after_link'] != '') {
		$retHtml .= '<div class="col-sm-12 after_link"><a href="'.$params['after_link'].'" title="'.$params['after_link_text'].'">'.$params['after_link_text'].'</a></div>';
	}
	
    $retHtml .= '</div>'; // End of row
  } else {
	$retHtml .= '<div><p>No events found!</div>'; // End of row
  }
  return $retHtml;
}


function ingeni_preload_scripts() {
	echo('<link rel="stylesheet" href="https://use.typekit.net/pbz7jhl.css">');
}
//add_action('wp_head', 'ingeni_preload_scripts');




//
// Specialised Australian Address fields
//
add_filter("gform_address_types", "australian_address", 10, 2);
function australian_address($address_types, $form_id){
  $address_types["australia"] = array(
                                  "label" => "Australia",
                                  "country" => "Australia",
                                  "zip_label" => "Post Code",
                                  "state_label" => "State",
                                  "states" => array("ACT", "NSW", "NT", "QLD", "SA", "TAS", "VIC", "WA")
  );
  return $address_types;
}



// Validation an Australian phone number
add_filter( 'gform_field_validation_1_3', 'bl_valid_au_phone', 10, 4 );


//
// Specialised Australian Address fields
//
add_filter( 'gform_phone_formats', 'au_phone_format' );
function au_phone_format( $phone_formats ) {
    $phone_formats['au'] = array(
        'label'       => 'Australia',
        'mask'        => '99 9999 9999',
        'regex'       => '/^\({0,1}((0|\+61)(2|4|3|7|8)){0,1}\){0,1}(\ |-){0,1}[0-9]{2}(\ |-){0,1}[0-9]{2}(\ |-){0,1}[0-9]{1}(\ |-){0,1}[0-9]{3}$/',
        'instruction' => 'Australian phone numbers.',
    );
 
    return $phone_formats;
}

// Validation an Australian phone number
add_filter( 'gform_field_validation', 'bl_valid_au_phone', 10, 4 );
function bl_valid_au_phone($result, $number, $form, $field)  {
	$retVal = '';

	// Make sure you are checking against phone number fields.
	if ( is_a( $field,'GF_Field_Phone' ) ) {

		if (bl_match_aus_phone($number)) {
			$result['is_valid'] = true;
		} else {
			$result['is_valid'] = false;
			$result['message']  = 'Please enter a valid phone number';
		}
	}
	return $result;
}


function bl_match_aus_phone ( $number ) {
	try {	
		// Get rid of any non-numerics
		$number = preg_replace('/[^0-9]/s', '', $number);
		
		if (preg_match('/^0(2|3|4|7|8)?\d{8}$/', $number) || preg_match('/^61(2|3|4|7|8)?\d{8}$/', $number) ||
			preg_match('/^1(3|8)00\d{6}$/', $number) || preg_match('/^13\d{4}$/', $number) || 
			preg_match('/^611(3|8)00\d{6}$/', $number) || preg_match('/^6113\d{4}$/', $number) ) {
			$result = true;
		}	else {
			$result = false;
		}
		
	} catch (Exception $e) {
		$result = false;
	}

	return $result;
}


// Default scrolling to the GF confirmation message
add_filter( 'gform_confirmation_anchor', function() {
    return 100;
} );

//Remove field labels
add_filter( 'gform_enable_field_label_visibility_settings', '__return_true' );



// Send all the uploaded photos as email attachments
//
add_filter('gform_notification', 'change_user_notification_attachments', 10, 3);
function change_user_notification_attachments( $notification, $form, $entry ) {

	//There is no concept of admin notifications anymore, so we will need to target notifications based on other criteria, such as name
	if($notification["name"] == "Admin Notification"){

			$fileupload_fields = GFCommon::get_fields_by_type($form, array("fileupload"));

			if(!is_array($fileupload_fields))
					return $notification;

			$attachments = array();
			$upload_root = RGFormsModel::get_upload_root();
			foreach($fileupload_fields as $field){
					$url = $entry[$field["id"]];
					$attachment = preg_replace('|^(.*?)/gravity_forms/|', $upload_root, $url);
					if($attachment){
							$attachments[] = $attachment;
					}
			}
			$notification["attachments"] = $attachments;
	}
	return $notification;
}


function get_first_sentence($content, $min_character_count = 0, $max_character_count = 150, $num_sentances = 1) {
	$retVal = $content;

	// Remove H4s
	$clean = preg_replace('#<h4>(.*?)</h4>#', '', $content);
	$clean = wp_strip_all_tags($clean);
	// Replace all curly quotes.
	$clean = str_replace(array('“','”'), '"', $clean);

	$locs = get_sentance_endings($clean, $min_character_count);
	$loc = $locs[0];

 
	$retVal = substr($clean,0, ($loc+1) );

	if ($num_sentances == 2) {
		$clean = substr( $clean, ($loc+1), (strlen($clean)-($loc+1)) );

		$locs = get_sentance_endings($clean, $min_character_count);
		$loc = $locs[0];
		$retVal .= substr($clean,0, ($loc+1) );
	}

	if (strlen($retVal) > $max_character_count) {
		$retVal = substr($retVal,0,$max_character_count+10);
		$last_word = strripos($retVal,' ');
		if ($last_word !== false) {
			$retVal = substr($retVal,0,$last_word) . '...';
		}
	}


	return $retVal;
}


function get_sentance_endings( $clean, $min_character_count ) {
	$exclaim = strpos($clean, "!",$min_character_count);
	if ($exclaim === false) {
		$exclaim = strlen($clean)-1;
	}
	$question = strpos($clean, "?",$min_character_count);
	if ($question === false) {
		$question = strlen($clean)-1;
	}
	$endquote = strpos($clean, '".',$min_character_count);
	if ($endquote === false) {
		$endquote = strlen($clean)-1;
	}
	$period = strpos($clean, '.',$min_character_count);
	if ($period === false) {
		$period = strlen($clean)-1;
	}
	
	//$loc = min( array($period,$exclaim,$question));
	$locs = array($exclaim,$question,$endquote,$period);
	sort( $locs );

	return $locs;
}


add_filter('wp_handle_upload_prefilter', 'ingeni_media_library_upload_filter' );
function ingeni_media_library_upload_filter( $file ) {
	$is_allowed = true;

	// Limit file sizes to 500k for JPGs
	$max_jpg_size = 300;
	// Limit DPI
	$max_dpi = 96;
	$preferred_dpi = 72;
	// Limit x or y dimensions
	$max_dimension = 4000;

	$image_formats = array('image/jpeg', 'image/jpg', 'image/png', 'image/webp');

	// Let's put some limit around uploaded JPGs - no photos direct from the camera allowed.
	if ( ( $file['size'] > ( $max_jpg_size * 1024 ) ) && ( in_array( $file['type'], $image_formats ) ) ) {

		// The file is > 500k
		$is_allowed = false;
		$file['error'] = 'JPG images must be smaller than '.$max_jpg_size.'k. Try using WEBP format.';

		// Grab the EXIF data
		$exif = exif_read_data( $file['tmp_name'], 'IFD0');

		// Only check the X resolution for DPI
		if ( array_key_exists('XResolution', $exif) ) {
			
			$x_dpi =  $exif['XResolution'];
			if ( !is_numeric( $x_dpi ) ) {
				$x_dpi = floatval( $x_dpi );
			}
			$x_dpi = intval( $x_dpi );

			if ( $x_dpi > $max_dpi ) {
				$is_allowed = false;
				$file['error'] = 'This is a high resolution image ['.$x_dpi.'dpi]. It must be reduced to '.$preferred_dpi.'dpi using an image editor like PhotoShop or Paint.NET';
			}
		}
	}
    return $file;
}




/*
 * Filters all menu item URLs for a ##anchor-link
 */
function bl_dynamic_menu_items( $menu_items ) {
	$current_page = "{$_SERVER['SERVER_PROTOCOL']}{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

	foreach ( $menu_items as $menu_item ) {
// All the blcontact shortcodes to be used as the URL of custom links.
			// Must include a ## before the shortcode. E.g., ##[blcontact type='misc1']
			if ( startsWith($menu_item->url, '##%5Bblcontact') && endsWith($menu_item->url, '%5D') ) {
				$menu_item->url = str_ireplace("##",'',$menu_item->url);
				$shortcode = urldecode($menu_item->url);
				//fb_log('sc: '.$shortcode);
				$menu_item->url = do_shortcode($shortcode);

			} else {

				if ( startsWith($menu_item->url, '##url##/') ) {
					$urllen = strlen($menu_item->url);
					$menu_item->url = get_bloginfo('url') . '/' . substr($menu_item->url,8,$urllen-8);
				}
			}
			$menu_item->url = str_ireplace("##", "#", $menu_item->url);
	}
	return $menu_items;
}
add_filter( 'wp_nav_menu_objects', 'bl_dynamic_menu_items' );



add_shortcode("get-latest-posts","get_latest_posts");
function get_latest_posts( $atts ){
	$post_atts = shortcode_atts( array(
		'catname' => 'news',
		'orderby' => 'date',
		'sortorder' => 'desc',
		'numposts' => 1,
		'offset' => 0,
		'post_type' => 'post',
		'post_status' => 'publish',
		'post_parent' => 0,
		'post_mime_type' => '',
		'year' => '',
	), $atts );

	$attribs = array( 'posts_per_page' => $post_atts['numposts'], 'offset' => $post_atts['offset'], 'category_name' => $post_atts['catname'], 'orderby' => $post_atts['orderby'], 'order' => $post_atts['sortorder'], 'post_type' => $post_atts['post_type'] );

	if ($post_atts['post_mime_type'] != '') {
		$attribs += array('post_mime_type' => $post_atts['post_mime_type'], 'post_status' => 'any,inherit,publish');
	} else {
		$attribs += array('post_status' => $post_atts['post_status']);
	}

	if ( $post_atts['post_parent'] >= 0 ) {
		$attribs += array( 'post_parent' => $post_atts['post_parent'] );
	}
	
	if ( is_numeric($post_atts['year']) ) {
		$attribs += array( 'date_query' => array('year' => $post_atts['year']) );
	}

	$myquery = new WP_Query( $attribs );

	return $myquery;
}




add_shortcode('bl-link-it', 'bl_link_it');
function bl_link_it( $atts ) {
  $attribs = shortcode_atts( array(
    'ph' => '',
	'mail' => '',
    'class' => '',
    'tag' => '',
    'text' => '',
    'map' => '',
    'dummy' => '',
    'web' => '',
	), $atts );

  $retHtml = '';

  $content = $attribs['text'];


  if (strlen($attribs['ph']) > 0) {
    $stripped = str_replace(' ','',$attribs['ph']);

    if (is_numeric($stripped)) {
      $retHtml = '<a ';
      if (strlen($attribs['class']) > 0) {
        $retHtml .= 'class="'.$attribs['class'].'"';
      }
      if (strlen($content) < 1) {
        $content = $attribs['ph'];
      }
      $retHtml .= 'href="tel:'.$stripped.'">'.$content.'</a>';
    }
  }

  if (strlen($attribs['map']) > 0) {
    $stripped = str_replace(' ','+',$attribs['map']);
    if (strlen($content) < 1) {
      $content = $attribs['map'];
    }
    $retHtml .= '<a href="https://www.google.com.au/maps/place/'.$stripped.'" target="_blank">'.$content.'</a>';
  }


  if (strlen($attribs['web']) > 0) {
    $content = $attribs['web'];
    $url = str_replace( parse_url( $content, PHP_URL_SCHEME ) . '://', '', $content );
    $retHtml .= '<a href="//'.$content.'" target="_blank">'.$attribs['web'].'</a>';
  }

  if (strlen($attribs['dummy']) > 0) {
    if (strlen($content) < 1) {
      $content = $attribs['dummy'];
    }
    $retHtml .= '<a href="#">'.$content.'</a>';
  }

  if (strlen($attribs['mail']) > 0) {
    $stripped = str_replace(' ','',$attribs['mail']);

    if (strpos($stripped,'@') !== false) {
      $retHtml = '<a ';
      if (strlen($attribs['class']) > 0) {
        $retHtml .= 'class="'.$attribs['class'].'"';
      }
      if (strlen($content) < 1) {
        $content = $attribs['mail'];
      }
      $retHtml .= 'href="mailto:'.$stripped.'">'.$content.'</a>';
    } 
  }
  if (strlen($attribs['tag']) > 0) {
    $stripped = str_replace(' ','-',$attribs['tag']);

    if (strlen($content) < 1) {
        $content = 'View Articles';
    }
    $retHtml = '<a ';
    if (strlen($attribs['class']) > 0) {
      $retHtml .= 'class="'.$attribs['class'].'"';
    }
    $retHtml .= 'href="'.get_bloginfo('url').'//tag/'.$stripped.'/">'.$content.'</a>';
  }

  return $retHtml;
}
