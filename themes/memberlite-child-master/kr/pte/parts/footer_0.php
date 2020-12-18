<?php
	$topicContent = json_decode($data['topic_content'], true);
	$topicContent['logo_url'] = $data['logo_url'];
	$reportSettings = $this->params;
	$sendData = $reportSettings['send_data'];
	$highlightColor = (isset($sendData['accent_color']) && $sendData['accent_color']) ? $sendData['accent_color'] : '#444';
?>
<div class="page-footer" style='margin: 0 -15px 0 -15px; padding: 0; height: 0.75in;'>
	<div style='font-family: Arial, Helvetica, sans-serif; display: inline-block; width: 33%; text-align: left; height: 0.375in; line-height: 0.375in; vertical-align: top;'>

	</div>
	<div style='font-family: Arial, Helvetica, sans-serif; display: inline-block; width: 33%; text-align: center; height: 0.375in; line-height: 0.375in; vertical-align: top;'>
	Page {pageNum} of {numPages}
	</div>
	<div style='font-family: Arial, Helvetica, sans-serif; display: inline-block; width: 33%; text-align: right; height: 0.375in; line-height: 0.375in; vertical-align: top;'>

	</div>
	<div style='height: 1px; width: 100%; border-top: 1px solid <?php echo $highlightColor; ?>;'></div>
	<div style='width: 100%; height: 0.375in;  background-color: <?php echo $highlightColor; ?>; border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;'></div>
</div>
