<?php

use \koolreport\widgets\koolphp\Table;

$objSettings = $this->reportSettings;
$reportSettings = $this->params;

$topicTabs = $reportSettings['topic_tabs'];
$sendData = $reportSettings['send_data'];
$reportSections = $sendData['sections'];

$highlightColor = (isset($sendData['accent_color']) && $sendData['accent_color']) ? $sendData['accent_color'] : '#444';
$styleId = (isset($sendData['accent_style']) && $sendData['accent_style']) ? $sendData['accent_style'] : '0';
$bannerType = (isset($sendData['banner_type']) && $sendData['banner_type']) ? $sendData['banner_type'] : '0';
$showBanner = (isset($sendData['show_banner']) && $sendData['show_banner']) ? $sendData['show_banner'] : '1';

$oldSubjectToken = '';
?>
<html>
	<head>
		<link rel="stylesheet" href="https://proteamedge.com/wp-content/themes/memberlite-child-master/kr/pte/assets/bs3/bootstrap.min.css" />
    <link rel="stylesheet" href="https://proteamedge.com/wp-content/themes/memberlite-child-master/kr/pte/assets/bs3/bootstrap-theme.min.css" />
		<style>

			@media print {

				table, tr, td, th, tbody, thead, tfoot {
            page-break-inside: avoid !important;
        }

				.pte_quick_report_table{
						table-layout: fixed !important;
						word-wrap: break-word !important;
				}
				.pte_quick_report_row{
				}
				.pte_quick_report_cell{
					font-size: 20px;
					border: 0 !important;
					padding: 2px 15px 2px 0 !important;
				}
				.pte_report_indented_1 {
					margin-left: 40px;
				}
				.pte_report_subhead_1{
						margin: 20px 0 20px; 0;
						font-size: 26px;
						font-weight: bold;
						padding: 3px 8px;
						color: white !important;
						background-color: <?php echo $highlightColor ?> !important;
				}
				.pte_report_subhead_2{
						border-top: dotted 1px <?php echo $highlightColor ?> !important;
				}

				.pte_logo_image_print{
					max-height: 200px;
				}
		}
		</style>
	</head>
    <body style='margin 0; padding: 0;'>
		<?php include "/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/kr/pte/parts/header_{$styleId}.php"; ?>
		<?php if ($showBanner == '1') {include "/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/kr/pte/parts/banner_{$bannerType}.php";} ?>
		<div style='margin: 0;'>
			<?php
			foreach ($topicTabs as $key => $value) {
				$topicKey = $value['key'];
				$topicFilter = isset($reportSections[$topicKey]['section_filter']) ? $reportSections[$topicKey]['section_filter'] : 'all';

				$subjectToken = $value['subject_token'];
				$tabName = $value['name'];
				$endOfSubjectType = false;
				$subjectCounter = 0;

				$drawnCounter = 0;
				while (!$endOfSubjectType) {

					$drawLinkRecord = false;

					switch ($topicFilter) {
						case 'exclude':
							$drawLinkRecord = false;
						break;
						case 'all':
							$drawLinkRecord = true;
						break;
						default:
							if ($topicFilter == $subjectCounter) {
								$drawLinkRecord = true;
							}
						break;
					}

					$newSubjectToken = "{$subjectToken}_{$subjectCounter}";
					$dataStore = $this->dataStore($newSubjectToken);

					if ($dataStore->count()) {
							if ($subjectCounter == 0) {
								echo "<div class='pte_report_subhead_1'>{$tabName}</div>";
							}
							if ($drawLinkRecord) {
								if ($drawnCounter > 0) {
									echo "<div class='pte_report_subhead_2'>&nbsp;</div>";
								}
								Table::create(array(
									"dataSource"=>$dataStore,
									"showHeader"=>false,
									"columns"=>array(
											"c1"=>array(
													"type"=>"string",
													"cssStyle"=>"font-weight: bold; width: 18%;"
											),
											"c2"=>array(
													"type"=>"string",
													"cssStyle"=>"font-weight: normal; width: 32%;"
											),
											"c3"=>array(
													"type"=>"string",
													"cssStyle"=>"font-weight: bold; width: 18%;"
										),
											"c4"=>array(
													"type"=>"string",
													"cssStyle"=>"font-weight: normal; width: 32%;"
											)
									),
									"cssClass"=>array(
											"table"=>"pte_quick_report_table",
											"tr"=>"pte_quick_report_row",
											"th"=>"pte_quick_report_header",
											"td"=>"pte_quick_report_cell",
											"tf"=>"pte_quick_report_footer",
									)
								));
								$drawnCounter++;

							}
							$subjectCounter++;
					} else {
						$endOfSubjectType = true;
					}
				}
			}

			?>
		</div>
		<?php include "/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/kr/pte/parts/footer_{$styleId}.php"; ?>
    </body>
</html>
