<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php'); //TODO WTF is the difference and can we be consistent
require('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/vendor/autoload.php');
require_once('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/typeset.sh.lib.phar');
require_once "quick_report.php";

use Ramsey\Uuid\Uuid;


function makeDataSource($topicMeta, $topicContent) { //Arranges data into two columms so we can consistent with forms
	$fieldMap = $topicMeta['field_map'];
	$newSource = $newRow = array();
	$column = 0;
	foreach ($fieldMap as $key => $value) {
		$hideFieldPrint = isset($value['hidden_print']) && $value['hidden_print'] == "true" ? true : false;
		if ($key && $value['id'] > 0 && substr($value['type'], 0, 5) != "core_" && !$hideFieldPrint) {
			$fieldName = $value['friendly'];
			$fieldData = $topicContent[$key];
			if (!$column) {
				$newRow['c1'] = $fieldName;
				$newRow['c2'] = $fieldData;
				$newRow['c3'] = "";
				$newRow['c4'] = "";
			} else {
				if (isset($value['newline']) && $value['newline'] == 'true') {
					$newSource[] = $newRow; //store current Row, create new row for newline
					$newRow['c1'] = $fieldName;
					$newRow['c2'] = $fieldData;
					$newRow['c3'] = "";
					$newRow['c4'] = "";
					$column = 0;
				} else {
					$newRow['c3'] = $fieldName;
					$newRow['c4'] = $fieldData;
					$newSource[] = $newRow;
				}
			}
			$column = !$column;
		}
	}

	if ($column == 1) {  //Commit c1/c2
		$newSource[] = $newRow;
	}
	return $newSource;
}


function pteCreateTopicQuickReport($reportSettings){
	alpn_log("starting pteCreateTopicQuickReport...");

	// alpn_log($reportSettings);

	try {
			$templateDirectory = get_template_directory();
			$pdfKey = $reportSettings['dom_id'] . ".pdf";
			$pdf = "{$templateDirectory}-child-master/quick_report_tmp/{$pdfKey}";

			$report = new quick_report($reportSettings);
			ob_start();
			$report->run()->render();
			$sourceReportHtml = ob_get_clean();

			try {

				$dedicatedDirectory = './tmp/' . Uuid::uuid4();
				mkdir($dedicatedDirectory, 0700);
				$uriResolver = Typesetsh\UriResolver::httpAndCurrentDir($dedicatedDirectory);
				$pdfObj = Typesetsh\createPdf($sourceReportHtml, $uriResolver);
				$pdfObj->toFile($pdf);
				delTree($dedicatedDirectory);

			} catch(Exception $e) {
			  alpn_log("EXCEPTION BROWSER");
			  alpn_log($e);
			}

		 return $pdfKey;

	} catch (\Exception $e) { // Global namespace
			alpn_log('Error handling quick report...');
			alpn_log($e);
			exit;
	}
}

?>
