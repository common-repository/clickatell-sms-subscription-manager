<?php
//SMS Subscribe Shortcode
function do_subscribe($atts, $content = null) {
	global $sms_submitted, $countrycode;
	extract(shortcode_atts(array(
		"title" => '',
		"width" => '200',
		"background" => "#ccc",
		"campaign_id" => '0'
	), $atts));
   
	return 	"<fieldset style=\"display: block; margin: 0 0 3em 0; padding: 1em 1em 1em 1em; background-color: ".$background."; width:".$width."px;\">
				<form class=\"sms_embed_form\" id=\"sms_sub_form_".$campaign_id."\" method=\"POST\">
					<h3>".$title."</h3>
					<label for=\"mobile_number_".$campaign_id."\">Mobile Number</label>
					+27<input id=\"mobile_number_".$campaign_id."\" name=\"mobile_number\" type=\"text\"/>
					<input class=\"submit\" type=\"submit\" value=\"Subscribe\">
				</form>
			</fieldset>";
}
add_shortcode("subscribe", "do_subscribe");
?>