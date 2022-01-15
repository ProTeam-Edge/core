<?php

use \koolreport\widgets\koolphp\Table;

$hostDomainName = PTE_HOST_DOMAIN_NAME;

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
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title></title>
<style>
    @page {
        size: letter;
        margin: 0.75in 0.5in;

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
					content: 'Page ' counter(page) ' of ' counter(pages);
					vertical-align: middle;
				}
				@bottom-right {
					vertical-align: middle;
				}
    }

    html {
        font-size: 12pt;
    }

		.pte_quick_report_cell{
			padding: 3px 10px;
			vertical-align: top;
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
			max-height: 1.75in;
			max-width: 2.5in;
		}
</style>
</head>
<body>

<div>
	<?php
		if ($showBanner == '1') {include "/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/kr/pte/parts/banner_{$bannerType}.php";} ?>
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
											"cssStyle"=>"font-weight: bold;"
									),
									"c2"=>array(
											"type"=>"string",
											"cssStyle"=>"font-weight: normal;"
									),
									"c3"=>array(
											"type"=>"string",
											"cssStyle"=>"font-weight: bold;"
								),
									"c4"=>array(
											"type"=>"string",
											"cssStyle"=>"font-weight: normal;"
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
</body>
</html>
