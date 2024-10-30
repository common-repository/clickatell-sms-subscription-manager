<?php
function sms_options_page() {
$sms_apikey = get_option( "sms_apikey" );
$sms_api_user = get_option( "sms_user" );
$sms_api_pass = get_option( "sms_password" );
$sms_header = get_option( "sms_header" );
$sms_footer = get_option( "sms_footer" );
$sms_from = get_option( "sms_from" );
?>
	<div class="wrap">
		<h2>Clickatell SMS Subscription Manager Options</h2>
		
		<br/>
		<p>Sign up with <a href="http://www.anrdoezrs.net/click-4159320-10790930" target="_blank">Bulk SMS Provider</a> to send SMS Messages</p>
		<br/>
		<form name='sms_update_options' id='sms_update_options' method='POST' action='<?php echo "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'] ?>'>
			<table>
				<tr>
					<td>Clickatell API Key</td>
					<td><input type="text" name="sms_api_key" value="<?php echo $sms_apikey; ?>"/></td>
				</tr>
				<tr>
					<td>Clickatell API Username</td>
					<td><input type="text" name="sms_api_user" value="<?php echo $sms_api_user;?>"/></td>
				</tr>
				<tr>
					<td>Clickatell Password</td>
					<td><input type="text" name="sms_api_pass" value="<?php echo $sms_api_pass;?>"/></td>
				</tr>
				<tr>
					<td>SMS From</td>
					<td><input type="text" name="sms_from" value="<?php echo $sms_from;?>"/> (From Number for replies)</td>
				</tr>
				<tr>
					<td>SMS Widget Header</td>
					<td><input type="text" name="sms_header" size="60" value="<?php echo $sms_header;?>"/></td>
				</tr>
				<tr>
					<td>SMS Widget Footer</td>
					<td><input type="text" name="sms_footer" size="60" value="<?php echo $sms_footer;?>"/></td>
				</tr>
			</table><br/>
			<span class="submit"><input type="submit" value="Update" name="sms_options"/></span>
		</form>
	</div>
	<br/>
	<div>
		Plugin by <a href="http://www.igeek.co.za/" title="iGeek">iGeek</a>
	</div>
<?php
}

function sms_meta_box_send(){
	global $smssuccfail;
	$sms_maxlen = "160";
?>
	<div style="padding: 10px;">
		<form name='send_sms_form' id='send_sms_form' method='POST'>
			Send an SMS to your subscribers:
			<br/>
			<br/>
			<table>
				<tr>
					<td>Message:</td>
				</tr>
				<tr>
					<td>
						<textarea maxlength="<?php echo $sms_maxlen; ?>" name="sms_message" id="sms_message"></textarea>
					</td>
				</tr>
				<tr>
					<td><input size=5 value="<?php echo $sms_maxlen; ?>" name="sms_left" id="sms_left" readonly="true"> Characters Left</td>
				</tr>
				<tr>
					<td><b>Send To:</b> Registered Users<input type="checkbox" name="sms_send_users" checked="checked"/>&nbsp; &nbsp; Readers<input type="checkbox" name="sms_send_readers" checked="checked"/></td>
				</tr>
			</table>
			<span class="submit"><input type="submit" value="Send Messages" /></span>
		</form>
		<p>Sign up with <a href="http://www.anrdoezrs.net/click-4159320-10790930" target="_blank">Bulk SMS Provider</a> to send SMS Messages</p>
<?php 
		echo $smssuccfail;
		$smssuccfail = '';?>
	</div>
<?php
}

function sms_meta_box_stats() {
	global $wpdb;
	$table_name = $wpdb->prefix . "sms_subscribers";
	$result = $wpdb->get_results("SELECT count(*) as totalsubs FROM " . $table_name);
?>
	<div style="padding: 10px;">
	<table>
		<tr>
			<td><b>Total Reader Subscriptions:</b></td>
			<td><?php echo $result[0]->totalsubs; ?></td>
		</tr>
<?php
	$usercount = 0;
	$aUsersID = $wpdb->get_col( $wpdb->prepare("SELECT $wpdb->users.ID FROM $wpdb->users"));
	foreach ( $aUsersID as $iUserID ) :
		 if ( get_the_author_meta( 'sms_profile_number', $iUserID ) <> "") {
			$usercount ++;
		 }
	endforeach;
?>
		<tr>
			<td><b>Total Registered User Subscriptions:</b></td>
			<td><?php echo $usercount; ?></td>
		</tr>
	</table>
	</div>
<?php
}

function sms_main_page() {
	global $smssuccfail;
?>
	<div class="wrap">
		<h2><?php _e('SMS Message Control Panel') ?></h2>
	</div>
<?php
	add_meta_box("sms_send", "Send SMS Messages", "sms_meta_box_send", "sms");
	add_meta_box("sms_stats", "Subscriber Statistics", "sms_meta_box_stats", "smsstats");
?>
	<div id="dashboard-widgets-wrap">
		<div class="metabox-holder">
			<div style="float:left; width:50%;" class="inner-sidebar1">
<?php
	do_meta_boxes('sms','advanced','');
?>
			</div>
			<div style="float:right; width:50%;" class="inner-sidebar2">
<?php
	do_meta_boxes('smsstats','advanced','');
?>	
			</div>
		</div>
	</div>
<?php
}

function sms_metabox_post_sidebar() {
	global $wpdb,$post,$smssuccfail;
	$sendsms = get_post_meta($post->ID, 'sms_send_sms', true);
	echo '<p>'.__('Send Post via SMS?').'&nbsp;';
	echo '<input type="radio" name="sms_send_sms" id="sms_send_sms_yes" value="yes" '.checked('yes', $sendsms, false).' /> <label for="sms_send_sms_yes">'.__('Yes').'</label> &nbsp;&nbsp;';
	echo '<input type="radio" name="sms_send_sms" id="sms_send_sms_no" value="no" '.checked('no', $sendsms, false).' /> <label for="sms_send_sms_no">'.__('No').'</label>';
	echo '</p>';
	$table_name = $wpdb->prefix . "sms_subscribers";
	$result = $wpdb->get_results("SELECT count(*) as totalsubs FROM " . $table_name);
	echo '<p><b>Total Subscribers</b>: '.$result[0]->totalsubs.'</p>';
	echo $smssuccfail;
	$smssuccfail = '';
}

function sms_profile_fields($user) {
	echo "<h3>SMS Subscription Options</h3>";
	echo "<table class=\"form-table\">";
	echo "	<tr>";
	echo "		<th><label for=\"sms_profile_number\">Mobile Number</label></th>";
	echo "		<td>";
	echo "			<input type=\"text\" name=\"sms_profile_number\" id=\"sms_profile_number\" value=\"". esc_attr( get_the_author_meta( 'sms_profile_number', $user->ID ) ) ."\" class=\"regular-text\" /><br />";
	echo "			<span class=\"description\">Please enter your mobile number (International Format eg. 2773000000)</span>";
	echo "		</td>";
	echo "	</tr>";
	echo "</table>";
}

function sms_save_profile_fields($user_id) {
	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;
		
	$sms_number = $_POST['sms_profile_number'];
	$sms_number = ereg_replace("[^0-9]", "", $sms_number);
	
	//Check if its a valid cellphone number or blank.
	if(strlen($sms_number) >= 10 or $sms_number == "") {
		update_usermeta( $user_id, 'sms_profile_number', $sms_number);
	} else {
		return false;
	}
}
?>