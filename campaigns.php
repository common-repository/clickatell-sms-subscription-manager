<?php

function sms_campaigns_page() {
	GLOBAL $wpdb;
	$current_url = $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
	if($_GET['action']=='update') {
		//Code to add/delete campaigns from db
		if(isset($_POST['campaign_name'])) {
			$inserted = $wpdb->insert($wpdb->prefix . "sms_campaigns",array('name'=>$wpdb->escape($_POST['campaign_name'])));
		}
	} 
?>
		<h2>Campaigns</h2>
<?php
	if(isset($inserted)) {
		if($inserted<>false)
			echo "<div id=\"message\" class=\"updated below-h2\"><p>Campaign added.</p>";
		else 
			echo "<div id=\"message\" class=\"error below-h2\"><p>Error adding campaign.</p>";
		echo "</div>";
	}
?>
		<br class="clear">
		<div id="col-container">
			<div id="col-right">
				<div class="col-wrap">
					<div class="tablenav">
						<div class="alignleft actions">
							<select name="action">
								<option selected>Bulk Actions</option>
								<option value="delete">Delete</option>
							</select>
							<input type="submit" value="Apply" name="doaction" id="doaction" class="button-secondary action">
						</div>
					</div>
					<table class="sortable widefat" cellspacing="0">
						<thead>
							<tr>
								<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox"></th>
								<th scope="col">Campaign Name</th>
								<th scope="col">Embed Code</th>
								<th scope="col">Subscribers</th>
							</tr>
						</thead>
						<tbody>
				<?php
					$table_name = $wpdb->prefix . "sms_campaigns";
					$results = $wpdb->get_results("SELECT a.id as c_id, a.name as c_name, count(b.id) as c_count FROM " . $table_name ." a left join " . $wpdb->prefix . "sms_subscribers b on b.campaign = a.id GROUP by a.name ORDER by a.id");

					if($results){
						$count = 1;
						foreach ($results as $result) {
							if($count % 2) {
								$rowstyle = 'class="alternate"';
							} else $rowstyle = '';
				?>
							<tr <?php echo $rowstyle; ?>>
								<th scope="row" class="check-column"><input type="checkbox" name="delete_tags[]" value="<?php echo $result->c_id; ?>"/></th>
								<td><?php echo $result->c_name; ?></td>
								<td><a href="javascript:void(0)" id="<?php echo $result->c_id; ?>" class="sms_embed_link">Get Embed Code</a></td>
								<td id="posts"><?php echo $result->c_count; ?></td>
							</tr>
				<?php
							$count++;
						} 
					} else	echo '<tr><td colspan="4" align="center"><strong>'. 'No campaigns defined.' .'</strong></td></tr>';
				?>
						</tbody>
					</table>
					<div class="tablenav">
						<div class="alignleft actions">
							<select name="action">
								<option selected>Bulk Actions</option>
								<option value="delete">Delete</option>
							</select>
							<input type="submit" value="Apply" name="doaction" id="doaction" class="button-secondary action">
						</div>
					</div>
				</div> <!--End col-wrap-->
			</div> <!--End col-right-->
			<div id="col-left">
				<div class="col-wrap">
					<div class="form-wrap">
						<h3>Add New Campaign</h3>
						<form action="admin.php?page=international-sms-campaigns&amp;action=update" method="post">
							<div class="form-field">
								<label for="campaign_name">Campaign Name</label>
								<input name="campaign_name" id="campaign_name" type="text" value="" size="40">
								<p>The friendly name for the campaign.</p>
							</div>
							<p class="submit"><input type="submit" class="button" name="submit" id="submit" value="Add New Campaign"></p>
						</form>
					</div>
				</div>
			</div>
<?php
} 