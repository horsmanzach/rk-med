<?php

function divi__child_theme_enqueue_styles() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'divi__child_theme_enqueue_styles' );

/*================================================
#Load custom Blog Module
================================================*/
function divi_custom_blog_module() {
  get_template_part( '/includes/Blog' ); 
  $dcfm = new custom_ET_Builder_Module_Blog();
  remove_shortcode( 'et_pb_blog' );
  add_shortcode( 'et_pb_blog', array( $dcfm, '_shortcode_callback' ) ); 
}
add_action( 'et_builder_ready', 'divi_custom_blog_module' );
function divi_custom_blog_class( $classlist ) {
  // Blog Module 'classname' overwrite.
  $classlist['et_pb_blog'] = array( 'classname' => 'custom_ET_Builder_Module_Blog',);
  return $classlist;
}
add_filter( 'et_module_classes', 'divi_custom_blog_class' );

// Inject Popup from Library into Header

function mp_custom_header_below_menu( $content ) {
    $custom_header = '<header id="header-below-menu">';
        $custom_header .= do_shortcode('[et_pb_section global_module="255400"][/et_pb_section]');
    $custom_header .= '</header> <!-- #header-below-menu -->';
    return $content . $custom_header;
}
add_filter( 'et_html_main_header', 'mp_custom_header_below_menu' ); 

// Add Email Validation to Contact Form 7

add_filter( 'wpcf7_validate_email*', 'custom_email_confirmation_validation_filter', 20, 2 );
  
function custom_email_confirmation_validation_filter( $result, $tag ) {
  if ( 'your-email-confirm' == $tag->name ) {
    $your_email = isset( $_POST['your-email'] ) ? trim( $_POST['your-email'] ) : '';
    $your_email_confirm = isset( $_POST['your-email-confirm'] ) ? trim( $_POST['your-email-confirm'] ) : '';
  
    if ( $your_email != $your_email_confirm ) {
      $result->invalidate( $tag, "Are you sure this is the correct address?" );
    }
  }
  
  return $result;
}

// Change Password Protect Text

// Add this to your theme's functions.php or to your functional plugin.
function et_password_form_new() {
	$pwbox_id = rand();
	$form_output = sprintf(
		'<div class="et_password_protected_form">
			<h1>%1$s</h1>
			<p>%2$s</p>
			<form action="%3$s" method="post">
				<p><label for="%4$s">%5$s: </label><input name="post_password" id="%4$s" type="password" size="20" maxlength="20" /></p>
				<p><button type="submit" name="et_divi_submit_button" class="et_submit_button et_pb_button">%6$s</button></p>
			</form>
		</div>',
		esc_html__( 'Enter your access code', 'Divi' ),
		'<p>The Webinar page requires an access code. To get one emailed to you simply fill out the form on the <a href="https://richardkimmedicine.com/webinars-sign-up/" style="color: #ff5e1a;"><u>Webinar Sign Up</u></a> page.</p>',
		esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ),
		esc_attr( 'pwbox-' . $pwbox_id ),
		esc_html__( 'Password', 'Divi' ),
		esc_html__( 'Submit', 'Divi' )
	);
	$output = sprintf(
		'<div class="et_pb_section et_section_regular">
			<div class="et_pb_row">
				<div class="et_pb_column et_pb_column_4_4">
					%1$s
				</div>
			</div>
		</div>',
		$form_output
	);
	return $output;
}
add_filter( 'the_password_form', 'et_password_form_new', 9999 );

// Change Registration Redirect URL

add_filter( 'woocommerce_registration_redirect', 'custom_redirection_after_registration', 10, 1 );
function custom_redirection_after_registration( $redirection_url ){
    // Change the redirection Url
    $redirection_url = "https://richardkimmedicine.com/patient-survey";
    return $redirection_url; // Always return something
}

//Change Logout URL to redirect to Home

add_action('wp_logout','auto_redirect_after_logout');
function auto_redirect_after_logout(){
  wp_redirect( home_url() );
  exit();
}


// Create Member Only Shortcode
function member_only_shortcode($atts, $content = null)
{
    if (is_user_logged_in() && !is_null($content) && !is_feed()) {
        return $content;
    }
}
add_shortcode('member_only', 'member_only_shortcode');




//Show Free Shipping Only When Available

/**
 * Hide shipping rates when free shipping is available.
 * Updated to support WooCommerce 2.6 Shipping Zones.
 *
 * @param array $rates Array of rates found for the package.
 * @return array
 */
function my_hide_shipping_when_free_is_available( $rates ) {
	$free = array();
	foreach ( $rates as $rate_id => $rate ) {
		if ( 'free_shipping' === $rate->method_id ) {
			$free[ $rate_id ] = $rate;
			break;
		}
	}
	return ! empty( $free ) ? $free : $rates;
}
add_filter( 'woocommerce_package_rates', 'my_hide_shipping_when_free_is_available', 100 );




//============ Add to Cart After Products On Shop, Category, Related Products

// Add "Add to Cart" buttons in Divi shop pages
add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 20 );



//Remove zoom on magnify in woocommerce products

function remove_zoom_woocommerce() {
    remove_theme_support( 'wc-product-gallery-zoom' );
}
add_action( 'after_setup_theme', 'remove_zoom_woocommerce', 100 );



//ALLOW SVG UPLOAD - DISABLE WHEN DONE FOR SECURITY
/*function cc_mime_types($mimes) {
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');
*/
//ADD CUSTOM LOGO TO WORDPRESS LOGIN SCREEN
add_action( 'login_enqueue_scripts', 'my_login_logo' );
function my_login_logo() { ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/images/site-login-logo.png); /*replace file in path*/
            padding-bottom: 15px;
			background-size: 200px;
			background-position: center center;
			width: 200px;
        }
    </style>
<?php }

add_filter( 'login_headerurl', 'my_login_logo_url' );
function my_login_logo_url() {
    return home_url();
}

add_filter( 'login_headertitle', 'my_login_logo_url_title' );
function my_login_logo_url_title() {
    return 'Richard Kim MD';
}

//ADD WP CORE CATEGORY TAXONOMY TO THE EVENTS CALENDAR PLUGIN
add_action('init','tribe_events_category');
function tribe_events_category(){
	register_taxonomy_for_object_type('category', 'tribe_events');
}

//CHANGE ANY TEXT (STRING) IN THE EVENTS CALENDAR
/* See the codex to learn more about WP text domains:
 * http://codex.wordpress.org/Translating_WordPress#Localization_Technology
 * Example Tribe domains: 'tribe-events-calendar', 'tribe-events-calendar-pro'...
 */
function tribe_custom_theme_text ( $translation, $text, $domain ) {
 
	// Put your custom text here in a key => value pair
	// Example: 'Text you want to change' => 'This is what it will be changed to'
	// The text you want to change is the key, and it is case-sensitive
	// The text you want to change it to is the value
	// You can freely add or remove key => values, but make sure to separate them with a comma
	// This example changes the label "Venue" to "Location", and "Related Events" to "Similar Events"
	$custom_text = array(
		'Venue' => 'Location',
	);
 
	// If this text domain starts with "tribe-", "the-events-", or "event-" and we have replacement text
    	if( (strpos($domain, 'tribe-') === 0 || strpos($domain, 'the-events-') === 0 || strpos($domain, 'event-') === 0) && array_key_exists($translation, $custom_text) ) {
		$translation = $custom_text[$translation];
	}

    return $translation;
}
add_filter('gettext', 'tribe_custom_theme_text', 20, 3);

//EVENTS CALENDAR LINK OPEN IN NEW TAB
add_filter( 'tribe_get_event_website_link_target', 'rkm_blank_target_for_new_window' );
function rkm_blank_target_for_new_window() {
	return '_blank';
}

//NEW MENU OPTION
function register_my_menu() {
  register_nav_menu('landing-page-menu',__( 'Landing Page Menu' )); //replace footer-top-menu
}
add_action( 'init', 'register_my_menu' );

add_action('wp_head' , 'conversion_tracking');
function conversion_tracking(){
	?>
	<!-- Global site tag (gtag.js) - Google Ads: 716856343 -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-716856343"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'AW-716856343');
</script>
<?
	if(is_page( 936 )){
?>
<!-- Event snippet for New Thank you page conversion page -->
<script>
  gtag('event', 'conversion', {'send_to': 'AW-716856343/UpKmCMTC0rgBEJe46dUC'});
</script>
<?
	}
}