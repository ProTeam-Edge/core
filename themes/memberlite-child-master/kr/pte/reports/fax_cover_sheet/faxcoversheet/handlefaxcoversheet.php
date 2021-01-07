<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php'); //TODO WTF is the difference and can we be consistent
require('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/vendor/autoload.php');
require_once "faxcoversheet.php";

function pteCreateFaxCoverSheetPdf($reportSettings){
	alpn_log("starting pteCreateFaxCoverSheetPdf...");
	try {
			$templateDirectory = get_template_directory();
			$pdfKey = wp_generate_uuid4() . ".pdf";
			$pdf = "{$templateDirectory}-child-master/tmp/{$pdfKey}";
			$report = new faxcoversheet($reportSettings);
			$report->run()
			->export('faxcoversheet')
			->settings(array(
					"phantomjs"=>"/usr/local/bin/phantomjs"
			))
			->pdf(array(
			      "format"=>$reportSettings['page_size'],
			      "orientation"=>$reportSettings['orientation'],
			      "margin"=>array(
			        "top"=>"0.25in",
			        "bottom"=>"0.25in",
			        "left"=>"0.5in",
			        "right"=>"0.5in"
			    )
			))
			->saveAs($pdf);
		return $pdf;

	} catch (\Exception $e) { // Global namespace
			alpn_log('Error handling cover sheet...');
			alpn_log($e);
			exit;
	}
}

?>
