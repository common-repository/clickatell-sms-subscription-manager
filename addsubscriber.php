<?php
include_once("../../../wp-config.php");
include_once("../../../wp-load.php");
include_once("../../../wp-includes/wp-db.php");

if(!empty($_POST['action'])) {
	$sms_number = $_POST['sms_number'];
	$sms_number = ereg_replace("[^0-9]", "", $sms_number);
	$sms_country = $_POST['sms_country_code_h'];
	$sms_country = ereg_replace("[^0-9]", "", $sms_country);
	if($sms_country <> "") {
		if((strlen($sms_country.$sms_number) <= 15) && strlen($sms_number) >= 9) {
			$sms_number = $sms_country.$sms_number;
			global $wpdb;
			$table_name = $wpdb->prefix . "sms_subscribers";
			
			$exists = $wpdb->get_results("SELECT number FROM " . $table_name . " WHERE number = '".$wpdb->escape($sms_number)."'");
			if(($exists[0]->number == $sms_number) OR (($_POST['sms_unsubscribe'] == 'true') and ($exists[0]->number == $sms_number))) {
				if(($_POST['sms_unsubscribe'] == 'true') and ($exists[0]->number == $sms_number)) {
					$delete = "DELETE FROM " . $table_name .
							" WHERE number = '".$wpdb->escape($sms_number)."'";
					$results = $wpdb->query($wpdb->prepare( $delete ));
					$sms_submitted = "<font color='green'>Success! You are unsubscribed.</font>";
				} elseif(isset($_POST['sms_unsubscribe']) and ($exists[0]->number <> $sms_number)) {
					$sms_submitted = "<font color='red'>Your number does not exist.</font>";
				} else {
					$sms_submitted = "<font color='red'>You have already subscribed.</font>";
				}
			} else {
				$insert = "INSERT INTO " . $table_name .
						" (number, ip, date) " .
						"VALUES ('" . $wpdb->escape($sms_number) . "','" . $_SERVER["REMOTE_ADDR"] . "',NOW())";
				$results = $wpdb->query($wpdb->prepare( $insert ));
				$sms_submitted = "<font color='green'>Thank you for subscribing.</font>";
			}
		} else {
			$sms_submitted = "<font color='red'>Please enter a valid cellphone number.</font>";
		}
	} else {
		$sms_submitted = "<font color='red'>Please select your country</font>";
	}
	print $sms_submitted;
}
?>