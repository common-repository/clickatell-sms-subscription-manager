<?php
function widget_sms($args) {
	require_once "functions.php";
	global $sms_submitted, $countrycode;
	global $wpdb;
	extract($args);
	echo "\n <!--Clickatell SMS Message Widget by Gerhard Potgieter http://www.igeek.co.za/-->\n";
	echo $before_widget;
	echo $before_title . get_option( "sms_header" ) . $after_title;
	echo "<div id=\"sms_submitted\" style=\"text-align:center;\"></div>";
	echo "<div id=\"sms_loading\" style=\"display:none; text-align:center;\"><img src=\"". get_bloginfo('url') . "/wp-content/plugins/clickatell-sms-subscription-manager/img/load.gif\"/></div>";
	//$countrycode = getCountryCodeFromIP($_SERVER['REMOTE_ADDR']);
?>
	<form name='sms_sub_form' id='sms_sub_form' style="padding:3px;text-align:center;" method='POST'>
		<p>Mobile Number<br/>
		<input type="hidden" name="sms_country_code_h" id="sms_country_code_h" value="<?php echo $countrycode; ?>"/>
		+<label name="sms_country_code" id="sms_country_code"><?php echo $countrycode; ?></label><input type="text" name="sms_number" id="sms_number"/>
		<div id="sms_country_div" style="display:none; text-align:center;"><?php echo countryDropDown(); ?></div><div id="sms_change_country" style="text-decoration: underline; cursor: pointer;">Change Country</div>
		</p>
		<input type="button" value="Subscribe" name="sms_subscribe" id="sms_subscribe"/>&nbsp;&nbsp;<input type="checkbox" name="sms_unsubscribe" id="sms_unsubscribe">Unsubscribe
	</form>
	<input type="hidden" value="<?php bloginfo('url'); ?>" id="sms_url"/>
<?php
	echo "<h6><em>" . get_option( "sms_footer" ) . "</em></h6>";
	echo $after_widget;
	echo "\n <!--End of Clickatell SMS Message-->";
}

function sms_widget_init(){
	register_sidebar_widget(__('SMS Subscribe Widget'), 'widget_sms');
}
?>