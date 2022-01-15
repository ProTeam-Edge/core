<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php'); //TODO WTF is the difference and can we be consistent
require('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/vendor/autoload.php');
require_once('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/typeset.sh.lib.phar');
require_once "faxcoversheet.php";

use Ramsey\Uuid\Uuid;

function pteCreateFaxCoverSheetPdf($reportSettings){

	alpn_log("starting pteCreateFaxCoverSheetPdf...");

		$templateDirectory = get_template_directory();
		$pdfKey = wp_generate_uuid4() . ".pdf";
		$pdf = "{$templateDirectory}-child-master/tmp/{$pdfKey}";

		$report = new faxcoversheet($reportSettings);
		ob_start();
		$report->run()->render();
		$sourceReportHtml = ob_get_clean();

		try {

			$dedicatedDirectory = './tmp/' . Uuid::uuid4();
			mkdir($dedicatedDirectory, 0700);
			$uriResolver = \Typesetsh\UriResolver::httpAndCurrentDir($dedicatedDirectory);
			$pdfObj = Typesetsh\createPdf($sourceReportHtml, $uriResolver);
			$pdfObj->toFile($pdf);
			delTree($dedicatedDirectory);

			return $pdf;

		} catch(Exception $e) {

		  alpn_log("EXCEPTION FAX COVER SHEET");
		  alpn_log($e);

		}
}
?>
