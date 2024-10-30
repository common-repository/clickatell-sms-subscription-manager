<?php
global $sms_version;
$sms_version = "1.4";

function sms_install() {
	global $wpdb;
	global $sms_version;
	
	$table_name = $wpdb->prefix . "sms_subscribers";
	
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) { //Plugin table does not exist yet, create it now
	
		$sql = "CREATE TABLE " . $table_name . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			number text NOT NULL,
			ip varchar(100) NOT NULL,
			date datetime NOT NULL,
			campaign bigint NOT NULL,
			UNIQUE KEY id (id)
		);";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		$table_name = $wpdb->prefix . "sms_campaigns";
		//Check for new campaigns table.
		if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
			$sql = "CREATE TABLE " . $table_name . " (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				name text NOT NULL,
				UNIQUE KEY id (id)
			);";
			dbDelta($sql);
		}
		
		add_option('sms_header','Receive updates via SMS');
		add_option('sms_footer',"SMS Subscription Manager by <a href='http://www.igeek.co.za/'>iGeek</a>");
		add_option('sms_max','160');
		add_option('sms_version',$sms_version);
		add_option('sms_from','');
		add_option('sms_css','/* Start SMS Embed Styles */
label {
    display: block;
}

.field {
    font-family: "Trebuchet MS", Helvetica, Sans-Serif;
    font-size: 13px;
    margin-bottom: 30px;
    border: 1px solid #ccc;
}

.field:hover {
    border-color: #b8b8b8;
}

.field:focus {
    border-color: #a8a8a8;
}

input.field {
    width: 244px;
    height: 15px;
    padding: 5px 3px;
    background: url(input-top.jpg) repeat-x;
}

textarea.field {
    width: 388px;
    height: 192px;
    padding: 4px 6px;
    background: #fff url(text-bg.jpg) bottom repeat-x;
}');
		
	} else { // Plugin table already exists just update it if new version available
	
		$installed_ver = get_option( "sms_version" );
		if($installed_ver != $sms_version ) {
			$table_name = $wpdb->prefix . "sms_subscribers";
			$sql = "CREATE TABLE " . $table_name . " (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				number text NOT NULL,
				ip varchar(100) NOT NULL,
				date datetime NOT NULL,
				campaign bigint NOT NULL,
				UNIQUE KEY id (id)
			);";
		
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			$table_name = $wpdb->prefix . "sms_campaigns";
			$sql = "CREATE TABLE " . $table_name . " (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				name text NOT NULL,
				UNIQUE KEY id (id)
			);";
			dbDelta($sql);
			
			update_option( "sms_version", $sms_version );
			update_option( "sms_max", '160' );
			update_option('sms_css','/* Start SMS Embed Styles */
label {
    display: block;
}

.field {
    font-family: "Trebuchet MS", Helvetica, Sans-Serif;
    font-size: 13px;
    margin-bottom: 30px;
    border: 1px solid #ccc;
}

.field:hover {
    border-color: #b8b8b8;
}

.field:focus {
    border-color: #a8a8a8;
}

input.field {
    width: 244px;
    height: 15px;
    padding: 5px 3px;
    background: url(input-top.jpg) repeat-x;
}

textarea.field {
    width: 388px;
    height: 192px;
    padding: 4px 6px;
    background: #fff url(text-bg.jpg) bottom repeat-x;
}');
		}
	}
}
?>