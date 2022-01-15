<?php

	$topicContent = json_decode($data['topic_content'], true);
	$topicContent['logo_url'] = $data['logo_url'];
	$reportSettings = $this->params;
	$sendData = $reportSettings['send_data'];
	$highlightColor = (isset($sendData['accent_color']) && $sendData['accent_color']) ? $sendData['accent_color'] : '#444';

	$reportSettings = $this->params;

	$topicContent = $reportSettings['user_content'];
	$placeContent = $reportSettings['place_content'];
	$organizationContent = $reportSettings['organization_content'];

	$sendData = $reportSettings['send_data'];
	$highlightColor = (isset($sendData['accent_color']) && $sendData['accent_color']) ? $sendData['accent_color'] : '#444';

	$topicImageUrl = (isset($topicContent['logo_url']) && $topicContent['logo_url']) ? $topicContent['logo_url'] : '';
	$orgImageUrl = (isset($organizationContent['logo_url']) && $organizationContent['logo_url']) ? $organizationContent['logo_url'] : '';

?>
<div class='banner_outer'>
	<div class='row_container'>
		<div class='col_left'>
			<div class='banner_text_line banner_text_bold'>
				<?php echo $organizationContent["organization_name"]; ?>
			</div>
			<div class='banner_text_line'>
				<?php echo $topicContent["person_givenname"] . " " . $topicContent["person_familyname"]; ?>
			</div>
			<div class='banner_text_line'>
				<?php echo $placeContent["place_address_postaladdress_streetaddress"]; ?>
			</div>
			<div class='banner_text_line'>
				<?php if ($placeContent["place_address_postaladdress_addresslocality"] && $placeContent["place_address_postaladdress_addresslocality"] && $placeContent["place_address_postaladdress_addresslocality"]) {echo $placeContent["place_address_postaladdress_addresslocality"] . ", " . $placeContent["place_address_postaladdress_addressregion"] . " " . $placeContent["place_address_postaladdress_postalcode"];} ?>
			</div>
			<div class='banner_text_line'>
				<?php echo $topicContent["person_telephone"] ? $topicContent["person_telephone"] . " (mobile)" : "";
				?>
			</div>
			<div class='banner_text_line'>
				<?php echo $organizationContent["organization_telephone"] ? $organizationContent["organization_telephone"] . " (office)" : "";
				?>
			</div>
			<div class='banner_text_line'>
				<?php echo $topicContent["person_faxnumber"] ? $topicContent["person_faxnumber"] . " (fax)" : "";
				?>
			</div>
			<div class='banner_text_line'>
				<?php echo $organizationContent["organization_url"]; ?>
			</div>
		</div>
		<div class='col_right'>
			<?php echo "<img class='banner_0_logo' src='{$orgImageUrl}'>"; ?>
		</div>
	</div>
</div>
