<?php

$hostDomainName = PTE_HOST_DOMAIN_NAME;

$reportSettings = $this->params;
$highlightColor = $reportSettings['highlight_color'];
$topicContent = $reportSettings['topic_content'];

$reportTitle = isset($reportSettings["message_title"]) && $reportSettings["message_title"] ? $reportSettings["message_title"] : "No Title";
$reportBody = isset($reportSettings["message_body"]) && $reportSettings["message_body"] ? $reportSettings["message_body"] : "No Message";

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title></title>
<style>
    @page {
        size: letter;
        margin: 0.5in 0.3in 0.5in 0.5in;

        @top-left {
          vertical-align: middle;
        }

				@top-center {
          vertical-align: middle;
        }
				@top-right {
          vertical-align: middle;
        }
				@bottom-left {
          vertical-align: middle;
        }
				@bottom-center {
					content: 'Wiscle Fax';
					vertical-align: middle;
				}
				@bottom-right {
					vertical-align: middle;
				}
    }

    html {
        font-size: 12pt;
				border-collapse: collapse;
    }

		.pte_quick_report_cell{
			padding: 3px 10px;
		}
		.pte_report_indented_1 {
			margin-left: 40px;
		}
		.pte_report_subhead_1{
				margin: 20px 0 20px; 0;
				font-size: 16pt;
				font-weight: bold;
				line-height: 18pt;
				padding: 5px 10px;
				color: white;
				background-color: <?php echo $highlightColor ?>;
		}
		.pte_report_subhead_2{
				margin: 10px 0;
				border-top: dotted 1px <?php echo $highlightColor ?>;
		}

		.row_container {
			display: flex;
			flex-wrap: wrap;
			width: 100%;
			box-sizing: border-box;
		}

		.col_left {
			flex-grow: 1;
			flex-basis: 50%;
			max-width: 50%;
		}

		.col_right {
			flex-grow: 1;
			flex-basis: 50%;
			max-width: 50%;
			text-align: right;
		}

		.banner_text_bold {
			font-weight: bold;
		}

		.banner_0_logo{
			max-height: 1.0in;
			max-width: 3.0in;
		}

		.wsc_border{
			padding: 10px 15px;
			border: solid 1px #505050;
			min-height: 3in;
			max-height: 5in;
			box-sizing: border-box;
		}

		.wsc_huge_title{
				font-size: 64pt;
				font-weight: bold;
				margin-bottom: 10px;
		}

		.wsc_message_title{
			font-size: 17pt;
			font-weight: bold;
			margin-top: 20px;
			margin-bottom: 20px;
			box-sizing: border-box;
			padding: 5px 0;
		}

		.wsc_message_body{
			font-size: 15pt;
			box-sizing: border-box;
		}


</style>
</head>
<body>
	<div class='banner_outer'>
		<div class='wsc_huge_title'>FAX</div>
		<div class='row_container'>
			<div class='col_left wsc_border'>
				<div style='font-size: 18pt;'>
					<div style='font-weight: bold;'>FROM</div> <?php echo $topicContent["person_givenname"] . " " . $topicContent["person_familyname"]; ?>
				</div>
				<div style='font-size: 16pt; margin-top: 15px;'>
					<?php echo $topicContent["organization_name"]; ?>
				</div>
				<div style='font-size: 16pt;'>
					<?php echo $topicContent["place_address_postaladdress_streetaddress"]; ?>
				</div>
				<div style='font-size: 16pt;'>
					<?php echo $topicContent["place_address_postaladdress_addresslocality"] . ", " . $topicContent["place_address_postaladdress_addressregion"] . " " . $topicContent["place_address_postaladdress_postalcode"]; ?>
				</div>
				<div style='font-size: 16pt;'>
					<?php echo $topicContent["person_telephone"] ? $topicContent["person_telephone"] . " (phone)" : "";
					?>
				</div>
				<div style='font-size: 16pt;'>
					<?php echo $topicContent["person_faxnumber"] ? $topicContent["person_faxnumber"] . " (fax)" : "";
					?>
				</div>
				<div style='font-size: 16pt;'>
					<?php echo $topicContent["organization_url"]; ?>
				</div>
			</div>
			<div class='col_right wsc_border'>
				<div style='font-size: 18pt;' border: 0;>
					<div style='font-weight: bold;'>ATTENTION</div> <?php echo $reportSettings["network_contact_name"]; ?>
				</div>
				<div style='font-size: 16pt; margin-top: 15px;'>
					<?php echo $reportSettings["company_name"]; ?>
				</div>
				<div style='font-size: 16pt;'>
					<?php echo $reportSettings["pstn_number_formatted"]; ?>
				</div>
				<div style='font-size: 16pt; margin-top: 20px;'>
					<?php
						$templateName = $reportSettings["template_name"] ? "<span style='font-weight: bold;'>Regarding:</span> " . $reportSettings["template_name"] : '';
						echo $templateName;
					?>
				</div>
				<div style='font-size: 16pt;'><?php echo "<span style='font-weight: bold;'>Total Pages:</span> " . $reportSettings["page_count"]; ?></div>
			</div>
		</div>
		<div>
			<div class='wsc_message_title'><?php echo $reportTitle; ?></div>
			<div class='wsc_message_body'><?php echo $reportBody; ?></div>
		</div>
	</div>

</body>
</html>
