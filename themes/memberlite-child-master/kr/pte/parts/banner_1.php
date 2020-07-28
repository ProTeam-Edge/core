<?php
	$topicContent = json_decode($data['topic_content'], true);
	$topicContent['logo_url'] = $data['logo_url'];

	$reportSettings = $this->params;
	$highlightColor = $reportSettings['highlight_color'];
?>

<div style='margin: 0; padding: 0; text-align: left; margin-bottom: 0.25in; max-height: 2.5in; width: 100%;'>
	<div>
		<img src="<?php echo $topicContent["logo_url"] ?>" >
	</div>	
	<div style='width: 100%;'>
		<div style='float: left; width: 50%; padding-left: 0.1in;'>
			<div style='font-size: 14pt;'>
				<?php echo $topicContent["organization.address.0.line[0]"]; ?>
			</div>
			<div style='font-size: 14pt;'>
				<?php echo $topicContent["organization.address.0.line[1]"]; ?>
			</div>
			<div style='font-size: 14pt;'>
				<?php echo $topicContent["organization.address.0.city"] . ", " . $topicContent["organization.address.0.state"] . " " . $topicContent["organization.address.0.postalCode"]; ?>
			</div>
		</div>		
		<div style='float: right; width: 50%; text-align: right; padding-right: 0.1in;'>
			<div style='font-size: 14pt;'>
				<?php echo $topicContent["organization.telecom.0.value"] ? $topicContent["organization.telecom.0.value"] . " (phone)" : ""; 
				?>
			</div>	
			<div style='font-size: 14pt;'>
				<?php echo $topicContent["organization.telecom.1.value"] ? $topicContent["organization.telecom.1.value"] . " (fax)" : ""; 
				?>
			</div>	
			<div style='font-size: 14pt;'>
				<?php echo $topicContent["organization.pte.website"]; ?>
			</div>	
		</div>
		<div style='clear: both;'></div>
	</div>		
</div>
