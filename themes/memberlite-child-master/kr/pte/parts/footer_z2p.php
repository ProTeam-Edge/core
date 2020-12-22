<?php
	$reportSettings = $this->params;
	$highlightColor = $reportSettings['highlight_color'];
?>
<div class="page-footer" style='margin: 0 -15px 0 -15px; padding: 0; height: 0.75in; font-size: 14pt;'>
	<div style='font-family: Arial, Helvetica, sans-serif; display: inline-block; width: 33%; text-align: left; height: 0.375in; line-height: 0.375in; vertical-align: top;'>

	</div>
	<div style='font-family: Arial, Helvetica, sans-serif; display: inline-block; width: 33%; text-align: center; height: 0.375in; line-height: 0.375in; vertical-align: top;'>

	</div>
	<div style='font-family: Arial, Helvetica, sans-serif; display: inline-block; width: 33%; text-align: right; height: 0.375in; line-height: 0.375in; vertical-align: top;'>
		Page {pageNum} of {numPages}
	</div>
	<div style='width: 100%; height: 0.375in;  background-color: <?php echo $highlightColor; ?>;'></div>
</div>
