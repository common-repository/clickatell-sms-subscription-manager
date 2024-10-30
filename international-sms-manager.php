<?php
/*
Plugin Name: International SMS Subscription Manager
Plugin URI: http://www.igeek.co.za/sms-alert-wordpress-plugin/
Description: Allows users to subscribe to sms notifications and allows blog owners to send sms message from within the dashboard to the subscribed numbers.
Author: Gerhard Potgieter
Version: 1.2
Author URI: http://www.igeek.co.za/
*/

require_once WP_PLUGIN_DIR . "/clickatell-sms-subscription-manager/install.php";
require_once WP_PLUGIN_DIR . "/clickatell-sms-subscription-manager/options.php";
require_once WP_PLUGIN_DIR . "/clickatell-sms-subscription-manager/widget.php";
require_once WP_PLUGIN_DIR . "/clickatell-sms-subscription-manager/subscribers.php";
require_once WP_PLUGIN_DIR . "/clickatell-sms-subscription-manager/campaigns.php";
require_once WP_PLUGIN_DIR . "/clickatell-sms-subscription-manager/functions.php";
require_once WP_PLUGIN_DIR . "/clickatell-sms-subscription-manager/shortcodes.php";

//
add_action("plugins_loaded", "sms_widget_init");

//Call install function if its a new install or update
register_activation_hook(__FILE__,'sms_install');

//Add the admin menu to the dashboad
add_action('admin_menu', 'sms_add_menu');

//New post fields to control sms sending
add_action('publish_post', 'sms_send_on_post', 99);
add_action('publish_post', 'sms_store_post_meta', 1, 2);
add_action('save_post', 'sms_store_post_meta', 1, 2);

//Profile SMS field
add_action( 'show_user_profile', 'sms_profile_fields' );
add_action( 'edit_user_profile', 'sms_profile_fields' );
add_action( 'personal_options_update', 'sms_save_profile_fields' );
add_action( 'edit_user_profile_update', 'sms_save_profile_fields' );
add_action( 'send_headers', 'sms_set_cookie');

//Add ajax script to blog
wp_enqueue_script('jquery');
wp_enqueue_script('thickbox',null,array('jquery'));
wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');
wp_register_script("international-sms-manager", "/wp-content/plugins/clickatell-sms-subscription-manager/international-sms-manager.js");
wp_enqueue_script('international-sms-manager');
add_action( 'wp_print_styles', 'enqueue_sms_embed_styles' );

//Some special js for backend
add_action('admin_head', 'sms_head');

global $smssuccfail;

function sms_head() {
	echo '
		<script type="text/javascript">
			var plugin_url = "'.WP_PLUGIN_URL.'";
		</script>
	';
}

function enqueue_sms_embed_styles() {
	echo '<style type="text/css">';
	echo get_option('sms_css');
	echo '</style>';
}

function sms_add_menu() {
	add_menu_page('SMS Manager', 'SMS Manager', 8, __FILE__, 'sms_main_page',WP_PLUGIN_URL . '/clickatell-sms-subscription-manager/clickatell.png');
	add_submenu_page(__FILE__, 'Options', 'Options', 8, 'international-sms-options', 'sms_options_page');
	add_submenu_page(__FILE__, 'Campaigns', 'Campaigns', 8, 'international-sms-campaigns', 'sms_campaigns_page');
	add_submenu_page(__FILE__, 'Subscribers', 'Subscribers', 8, 'international-sms-subscribers', 'sms_subscribers_page');
	add_meta_box('sms_post_form', __('Send Post to SMS Subscribers'), 'sms_metabox_post_sidebar', 'post', 'side');
}

function sms_store_post_meta($post_id, $post = false) {
	$post = get_post($post_id);
	if (!$post || $post->post_type == 'revision') {
		return;
	}
	$posted_meta = $_POST['sms_send_sms'];
	
	if (!empty($posted_meta)) {
		$posted_meta == 'yes' ? $meta = 'yes' : $meta = 'no';
	} else {
		$meta = 'no';
	}
	
	update_post_meta($post_id, 'sms_send_sms', $meta);
}

function sms_set_cookie()
{
	global $countrycode;
	
	if (isset($_COOKIE['countrycode'])) {
		$countrycode = $_COOKIE['countrycode'];
	} else {
		$countrycode = getCountryCodeFromIP(get_ip());
	}
	setcookie('countrycode', $countrycode);
}

function sms_send_on_post($post_id = 0) {
	if($post_id == 0) {
		return;
	}
	$post = get_post($post_id);
	//Keep private posts private
	if ($post->post_status == 'private') {
		return;
	}
	
	if(get_post_meta($post->ID, 'sms_send_sms', true) == 'no') {
		return;
	}
	
	if(get_post_meta($post->ID, 'sms_sent',true) == 'yes') {
		return;
	}
	
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, 'http://api.bit.ly/v3/shorten?login=kloon&apiKey=R_4f3c837d876d5d4bd69977cb8b245150&longUrl='.urlencode(get_permalink($post_id))."&format=json"); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$data = curl_exec($ch);
	$urldata = json_decode($data);
	curl_close($ch);
	$bitlyurl = $urldata->data->url;
	
	//Now send the SMS Messages
	require_once WP_PLUGIN_DIR . "/clickatell-sms-subscription-manager/class.sms_api.php";
	
	$user = get_option( "sms_user" );
	$password = get_option( "sms_password" );
	$api_id = get_option( "sms_apikey" );
	$from = get_option( "sms_from" );
	
	$mysms = new sms($api_id,$user,$password);

	if($mysms->session <> -1) {
		global $wpdb;
		$table_name = $wpdb->prefix . "sms_subscribers";
		$result = $wpdb->get_results("SELECT number FROM " . $table_name);
		
		//set post status to sent to avoid resending on updates
		update_post_meta($post->ID, 'sms_sent', 'yes');
		
		$smssent = 0;
		foreach ($result as $results) {
			$sms_number = $results->number;
			$smsres = $mysms->send($sms_number,$from,"New Blog Post: ".$post->post_title." ".$bitlyurl);
			if($smsres == 'OK') {
				$smssent+=1;
			} else {
				$smsfail .= "Failed sending to ".$sms_number."<br/>";
			}
		}
		$smssuccfail = "<span style=\"color='green';\">Sent to $smssent subscribers</span><br/>";
		$smssuccfail .= "<span style=\"color='red';\">".$smsfail."</span>";
	} else {
		$smssuccfail = "<span style=\"color: red\">Failed to authenticate to Clickatell</span>";
	}
}

//Handle POST variables to save options and send messages

//First check if sms messages need to be sent
if(!empty($_POST['sms_message'])) {
	require_once WP_PLUGIN_DIR . "/clickatell-sms-subscription-manager/class.sms_api.php";
	
	$user = get_option( "sms_user" );
	$password = get_option( "sms_password" );
	$api_id = get_option( "sms_apikey" );
	$from = get_option( "sms_from" );
	
	$mysms = new sms($api_id,$user,$password);
	if($mysms->session <> -1) {
		//Send SMS to subscribed readers
		if(isset($_POST['sms_send_readers']) == 'on') {
			global $wpdb;
			$table_name = $wpdb->prefix . "sms_subscribers";
			$result = $wpdb->get_results("SELECT number FROM " . $table_name);
			
			foreach ($result as $results) {
				$sms_number = $results->number;
				$smsres = $mysms->send($sms_number,$from,$_POST['sms_message']);
				if($smsres == 'OK')
					$smssuccfail .= "<span style=\"color: green\">Message sent to $sms_number</span><br/>";
				else
					$smssuccfail .= "<span style=\"color: red\">Message failed to $sms_number</span><br/>";
			}
		}
		if(isset($_POST['sms_send_users']) == 'on') {
			global $wpdb;
			$aUsersID = $wpdb->get_col( $wpdb->prepare("SELECT $wpdb->users.ID FROM $wpdb->users"));
			foreach ( $aUsersID as $iUserID ) :
				$sms_number = get_user_meta( $iUserID, 'sms_profile_number' );
				$sms_number = $sms_number[0];
				if ( $sms_number <> "") {
					$smsres = $mysms->send($sms_number,$from,$_POST['sms_message']);
					if($smsres == 'OK')
						$smssuccfail .= "<span style=\"color: green\">Message sent to $sms_number</span><br/>";
					else
						$smssuccfail .= "<span style=\"color: red\">Message failed to $sms_number</span><br/>";
				}
			endforeach;
		}
	} else {
		$smssuccfail = "<span style=\"color: red\">Failed to authenticate to Clickatell</span>";
	}
}

//Update SMS options
if(!empty($_POST['sms_options'])) {
	update_option( "sms_user", $_POST['sms_api_user']);
	update_option( "sms_password", $_POST['sms_api_pass']);
	update_option( "sms_apikey", $_POST['sms_api_key']);
	update_option( "sms_header", $_POST['sms_header']);
	update_option( "sms_footer", $_POST['sms_footer']);
	update_option( "sms_from", $_POST['sms_from']);
}
?>