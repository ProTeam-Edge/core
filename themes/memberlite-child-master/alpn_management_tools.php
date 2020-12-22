<?php
include('../../../wp-blog-header.php');
	
$siteUrl = get_site_url();

$html="  
<script src='https://code.jquery.com/jquery-3.4.1.min.js' integrity='sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=' crossorigin='anonymous'></script>
<script>

function alpn_commit() {

	var tt_id = jQuery('#tt_id').val();
	var tt_html_template = jQuery('#tt_html_template').val();
	
	jQuery('#tt_id').val('');
	jQuery('#tt_html_template').val('');	
	
	jQuery.ajax({ 
		url: '{$siteUrl}/wp-content/themes/memberlite-child-master/alpn_update_topic_type_html_db.php',
		type: 'POST',
		data: {
			id: tt_id,
			html_template: encodeURIComponent(tt_html_template)
		},
		dataType: 'json',
		success: function(json) {
			console.log('Success');
			console.log(json);
		},
		error: function(json) {
			console.log('Problem');
			console.log(json);
		}
	})	
}	



</script>
<div>Template Loader</div>
<div>ID:<br><input type='text' id='tt_id'></div>
<div style='margin-top: 10px;'>Template:<br><textarea id='tt_html_template' style='width: 500px; height: 300px;'></textarea></div>
<br>
<div><button type='button' onclick='alpn_commit();'>Commit</button></div>


";
echo $html;

?>	