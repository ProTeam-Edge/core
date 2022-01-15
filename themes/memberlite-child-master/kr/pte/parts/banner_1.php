<?php
	$topicContent = json_decode($data['topic_content'], true);
	$topicContent['logo_url'] = $data['logo_url'];
	
	$reportSettings = $this->params;
	$highlightColor = $reportSettings['highlight_color'];
?>

<style>
		div.banner {
			margin: 0;
			padding: 0;
			text-align: left;
			margin-bottom: 0.25in;
			max-height: 2.5in;
			width: 100%;'
		}
</style>
<div class='banner'>
	<div>
		<img src="<?php echo $topicContent["logo_url"] ?>" >
	</div>
	<div style='width: 100%;'>
		<div style='float: left; width: 50%; padding-left: 0.1in;'>
			<div style='font-size: 14pt;'>

				HELLO!!!!
				<?php echo $topicContent["place_address_postaladdress_streetaddress"]; ?>
			</div>
			<div style='font-size: 14pt;'>
			</div>
			<div style='font-size: 14pt;'>
				<?php echo $topicContent["place_address_postaladdress_addresslocality"] . ", " . $topicContent["place_address_postaladdress_addressregion"] . " " . $topicContent["place_address_postaladdress_postalcode"]; ?>
			</div>
		</div>
		<div style='float: right; width: 50%; text-align: right; padding-right: 0.1in;'>
			<div style='font-size: 14pt;'>
				<?php echo $topicContent["person_telephone"] ? $topicContent["person_telephone"] . " (phone)" : "";
				?>
			</div>
			<div style='font-size: 14pt;'>
				<?php echo $topicContent["person_faxnumber"] ? $topicContent["person_faxnumber"] . " (fax)" : "";
				?>
			</div>
			<div style='font-size: 14pt;'>
				<?php echo $topicContent["organization_url"]; ?>
			</div>
		</div>
		<div style='clear: both;'></div>
	</div>
</div>
