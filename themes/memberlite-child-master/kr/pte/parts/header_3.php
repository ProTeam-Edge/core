<?php
	$topicContent = json_decode($data['topic_content'], true);
	$topicContent['logo_url'] = $data['logo_url'];
	$reportSettings = $this->params;
	$highlightColor = $reportSettings['highlight_color'];
?>

<div class="page-header" style='margin: 0 -15px 0 -15px; padding: 0; height: 0.75in;'>
	<div style='width: 100%; height: 0.06in;  background-color: <?php echo $highlightColor; ?>; margin-bottom: 0.255in;'></div>
	<div style='width: 100%; height: 0.06in;  background-color: <?php echo $highlightColor; ?>; '></div>
	
	<div style='font-family: Arial, Helvetica, sans-serif; display: inline-block; width: 33%; text-align: left; height: 0.375in; line-height: 0.375in; vertical-align: bottom;'>
		Report Name	
	</div>
	<div style='font-family: Arial, Helvetica, sans-serif; display: inline-block; width: 33%; text-align: center; height: 0.375in; line-height: 0.375in; vertical-align: bottom;'>
		ProTeam Confidential
	</div>
	<div style='font-family: Arial, Helvetica, sans-serif; display: inline-block; width: 33%; text-align: right; height: 0.375in; line-height: 0.375in; vertical-align: bottom;'>
		Report Date
	</div>	

</div>