<?php
	$topicContent = json_decode($data['topic_content'], true);
	$reportSettings = $this->params;
	$sendData = $reportSettings['send_data'];
	$highlightColor = (isset($sendData['accent_color']) && $sendData['accent_color']) ? $sendData['accent_color'] : '#444';
?>

<div class="page-header" style='margin: 0 -15px 0 -15px; padding: 0; height: 0.75in;'>
	<div style='width: 100%; height: 0.375in;  background-color: <?php echo $highlightColor; ?>; border-top-left-radius: 15px; border-top-right-radius: 15px;'></div>
	<div style='height: 2px; width: 100%; border-bottom: 1px solid <?php echo $highlightColor; ?>'></div>

	<div style='font-family: Arial, Helvetica, sans-serif; display: inline-block; width: 33%; text-align: left; height: 0.375in; line-height: 0.375in; vertical-align: bottom;'>

	</div>
	<div style='font-family: Arial, Helvetica, sans-serif; display: inline-block; width: 33%; text-align: center; height: 0.375in; line-height: 0.375in; vertical-align: bottom;'>

	</div>
	<div style='font-family: Arial, Helvetica, sans-serif; display: inline-block; width: 33%; text-align: right; height: 0.375in; line-height: 0.375in; vertical-align: bottom;'>

	</div>

</div>
