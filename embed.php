<?php
if(isset($_GET['campaign'])) {
?>
	<h3>Embed the SMS Subscribe widget in your posts or pages</h3>
	<p>Copy the code below and paste it in your posts or pages where you want the SMS Subscribe Widget for the specific campaign to be displayed.</p>
	<textarea onClick="javascript:this.select();" style="width:100%;" class="campaign_embed_code">[campaign id="<?php echo $_GET['campaign']; ?>" width="200" background="#ccc" title="My SMS Campaign"]</textarea>
<?php
}
?>