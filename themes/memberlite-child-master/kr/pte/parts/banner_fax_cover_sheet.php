<?php
	$reportSettings = $this->params;
	$highlightColor = $reportSettings['highlight_color'];
	$topicContent = $reportSettings['topic_content'];

?>
<div style='margin: 0; padding: 0; text-align: left; margin-bottom: 0.25in; width: 100%;'>
	<div style='width: 100%; height: 400px;'>
		<div style='float: left; width: 50%; padding: 10px; border: solid 1px grey; height: 100%;'>
			<div style='font-size: 24pt; border: 0;'>
				<span style='font-weight: bold;'>From:</span> <?php echo $topicContent["person_givenname"] . " " . $topicContent["person_familyname"]; ?>
			</div>
			<div style='font-size: 18pt; margin-top: 5px;'>
				<?php echo $topicContent["organization_name"]; ?>
			</div>
			<div style='font-size: 18pt;'>
				<?php echo $topicContent["place_address_postaladdress_streetaddress"]; ?>
			</div>
			<div style='font-size: 18pt;'>
				<?php echo $topicContent["place_address_postaladdress_addresslocality"] . ", " . $topicContent["place_address_postaladdress_addressregion"] . " " . $topicContent["place_address_postaladdress_postalcode"]; ?>
			</div>
			<div style='font-size: 18pt;'>
				<?php echo $topicContent["person_telephone"] ? $topicContent["person_telephone"] . " (phone)" : "";
				?>
			</div>
			<div style='font-size: 18pt;'>
				<?php echo $topicContent["person_faxnumber"] ? $topicContent["person_faxnumber"] . " (fax)" : "";
				?>
			</div>
			<div style='font-size: 18pt;'>
				<?php echo $topicContent["organization_url"]; ?>
			</div>
		</div>
		<div style='float: right; width: 50%; height: 100%; text-align: left;  padding: 10px; border-top: solid 1px grey; border-right: solid 1px grey; border-bottom: solid 1px grey;'>
			<div style='font-size: 24pt;' border: 0;>
				<span style='font-weight: bold;'>Attention:</span> <?php echo $reportSettings["network_contact_name"]; ?>
			</div>
			<div style='font-size: 18pt;'>
				<?php echo $reportSettings["company_name"]; ?>
			</div>
			<div style='font-size: 18pt;'>
				<?php echo $reportSettings["pstn_number_formatted"]; ?>
			</div>
			<div style='font-size: 18pt; margin-top: 20px;'>
				<?php
					$templateName = $reportSettings["template_name"] ? "<span style='font-weight: bold;'>Regarding:</span> " . $reportSettings["template_name"] : '';
					echo $templateName;
				?>
			</div>
			<div style='font-size: 18pt;'><?php echo "<span style='font-weight: bold;'>Total Pages:</span> " . $reportSettings["page_count"]; ?></div>
		</div>
		<div style='clear: both;'></div>
	</div>
</div>
