<?php
if (is_admin()) {
	if($_GET['mode'] =='export'){
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"WordPressSMSSubscribers.csv\"");
		GLOBAL $wpdb;
		$table_name = $wpdb->prefix . "sms_subscribers";
		$result = $wpdb->get_results("SELECT * FROM " . $table_name);
		foreach ($result as $results) {
			$data .= $results->number."\n";
		}
		echo $data;
		die();
	}
	
	if(isset($_POST['sms_import'])) {
		$data = file_get_contents($_FILES['sms_import_file']['tmp_name']);
		$numbers = explode("\n",$data);
		foreach($numbers as $number) {
			$number = ereg_replace("[^0-9]", "", $number);
			if((strlen($number) <= 15) && (strlen($number) >= 10)) {
				global $wpdb;
				$table_name = $wpdb->prefix . "sms_subscribers";
				$exists = $wpdb->get_results("SELECT number FROM " . $table_name . " WHERE number = '".$wpdb->escape($number)."'");
				if($exists[0]->number != $number) {
					$insert = "INSERT INTO " . $table_name .
					" (number, ip, date) " .
					"VALUES ('" . $wpdb->escape($number) . "','IMPORT',NOW())";
					$results = $wpdb->query($wpdb->prepare( $insert ));
				}
			}
		}
	}
}
function sms_subscribers_page() {
$current_url = $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
?>
	<div class="wrap">
			<h2>SMS Subscribers</h2>
			<p><a href="#" id="sms_import_link">Import from CSV</a>&nbsp;&nbsp;<a href="http://<?php echo $current_url; ?>&amp;mode=export">Export to CSV</a></p>
			<div id="sms_upload" style="display:none;">
				<form method="post" enctype="multipart/form-data">
					<p>
						Please select a csv file containing 11 digit numbers to upload
					</p>
					<table>
						<tr>
							<td>File</td>
							<td><input type="file" name="sms_import_file"/></td>
						</tr>
					</table>
					<input type="submit" value="Import Subscribers" name="sms_import"/>
				</form>
				<br/>
			</div>
			<table class="sortable widefat" cellspacing="0">
				<thead>
				<tr>
					<th scope="col" >ID</th>
					<th scope="col" >Phone Number</th>
					<th scope="col" >Submit IP</th>
					<th scope="col" >Submit Date</th>
					<th scope="col" >Action</th>
				</tr>
				</thead>
				<tbody>
<?php
	GLOBAL $wpdb;
	$table_name = $wpdb->prefix . "sms_subscribers";
	
	if(isset($_GET['id']) && ($_GET['mode']=='delete')){
		$quer = "DELETE FROM " . $table_name . " WHERE id = " . $_GET['id']; 
		$wpdb->query($quer);

		echo "<div style='color:red'>" . $_GET['id'] . " removed</div>";
	}
	
	$result = $wpdb->get_results("SELECT * FROM " . $table_name);

	if($result){
		foreach ($result as $results) {
			$tablenum = $results->number;
			$tableip = $results->ip;
			$tabledate = $results->date;
			$tableid = $results->id;
?>
<tr onmouseover="this.style.backgroundColor='lightblue';" onmouseout="this.style.backgroundColor='white';">
	<td><?php echo $tableid; ?></td>
	<td><?php echo $tablenum; ?></td>
	<td><?php echo $tableip; ?></td>
	<td><?php echo $tabledate; ?></td>
	<td><a href="http://<?php echo $current_url; ?>&amp;mode=delete&amp;id=<?php echo $tableid; ?>" onclick="javascript:check=confirm( '<?php echo "Delete this subscriber?"?>');if(check==false) return false;"><?php _e('Delete') ?></a></td>
</tr>
<?php
		}
	} else
		echo '<tr><td colspan="7" align="center"><strong>'. 'No entries found' .'</strong></td></tr>';
?>
			</tbody>
		</table>
		<p>Sign up with <a href="http://www.anrdoezrs.net/click-4159320-10790930" target="_blank">Bulk SMS Provider</a> to send SMS Messages</p>
		<br/>
		<div>
			Plugin by <a href="http://www.igeek.co.za/" title="iGeek">iGeek</a>
		</div>
	</div>
<?php
}
?>