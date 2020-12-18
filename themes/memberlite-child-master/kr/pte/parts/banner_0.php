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

<style>
	.banner_text_line {
		font-size: 24px !important;
	}

	.banner_text_bold {
		font-weight: bold;
	}

	.banner_0_logo{
		max-width: 300px;
		max-height: 150px;
	}

</style>

<div style='margin: 0; padding: 0; text-align: left; margin-bottom: 0.25in; max-height: 2.5in; width: 100%;'>

	<div style='width: 100%;'>
		<div style='float: left; width: 50%; padding-left: 0.1in;'>
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
		<div style='float: right; width: 50%; text-align: right; padding-right: 0.1in;'>
			<?php echo "<img class='banner_0_logo' src='{$orgImageUrl}'>"; ?>
		</div>
		<div style='clear: both;'></div>
	</div>
</div>
