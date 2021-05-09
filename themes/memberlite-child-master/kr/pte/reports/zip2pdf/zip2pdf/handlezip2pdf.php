<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php'); //TODO WTF is the difference and can we be consistent
require('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/vendor/autoload.php');
require_once "zip2pdf.php";

use Google\Cloud\Storage\StorageClient;

function getHtml($tree){

	$templateDirectory = get_template_directory();

	$html = '';
	$sortedFiles = $sortedDirectories = array();
	foreach ($tree as $key => $value) {
		if ($key == $value) { //File
			$sortedFiles[$key] = $value;
		} else {              //Dir
			$sortedDirectories[$key] = $value;
		}
	}
	ksort($sortedFiles);
	ksort($sortedDirectories);
	$tree = array_merge($sortedFiles, $sortedDirectories);
	foreach ($tree as $key => $value) {
		if ($key == $value) {
			$html .= "<div class='pte_pdf_file'>{$key}</div>";
		}
		else {
			$html .= "	<div class='pte_pdf_directory_container'>
							<div class='pte_pdf_directory_title'>
								<table>
									<tr>
										<td class='pte_pdf_directory_image_cell'><img src='https://proteamedge.com/wp-content/themes/memberlite-child-master/folder.jpeg'></td>
										<td class='pte_pdf_directory_text_cell'>{$key}</td>
									</tr>
								</table>
							</div>
					 ";
			$html .= getHtml($value);
			$html .= "</div>";
		}
 	}
	return $html;
}

function formatBytes($size, $precision = 2)
{
    $base = log($size, 1024);
    $suffixes = array('', 'k', 'm', 'g', 't');

    return round(pow(1024, $base - floor($base)), $precision) . '' . $suffixes[floor($base)];
}

function explodeTree($urls) {

	$array = array();
	foreach ($urls as $url) {
		$parts = explode('/', $url);
		krsort($parts);
		$line_array = null;
		$part_count = count($parts);
		foreach ($parts as $key => $value) {
			if ($line_array == null) {
				$line_array = array($value => $value);
			} else {
				$temp_array = $line_array;
				$line_array = array($value => $temp_array);
			}
		}
		$array = array_merge_recursive($array, $line_array);
	}
	return $array;
}

function createAndStorePdf($reportSettings){

	$fileKey = $reportSettings["pte_file_key"];
	$pdfKey = $reportSettings["pte_pdf_key"];

	try {
		$storage = new StorageClient([
	    	'keyFilePath' => '/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/proteam-edge-cf8495258f58.json'
		]);
		$storage->registerStreamWrapper();
	    $bucket = $storage->bucket("pte_file_store1");
	    $object = $bucket->object($fileKey);
		$templateDirectory = get_template_directory();
		$file = "{$templateDirectory}-child-master/tmp/{$fileKey}";
	  $object->downloadToFile($file);
		$urls = array();
		$zip = zip_open($file);
		$html = '';
		if ($zip) {
			while ($zip_entry = zip_read($zip)) {
				$zen = zip_entry_name($zip_entry);
				if (zip_entry_filesize($zip_entry)) {
					$zeo = formatBytes(zip_entry_filesize($zip_entry)) . "b";
					$urls[] = "{$zen} ({$zeo})";
				}
				//$zec = formatBytes(zip_entry_compressedsize($zip_entry))  . "b";
			}
			zip_close($zip);
			$newArray = explodeTree($urls);
			$html .= "
				<!doctype html>
				<html lang='en'>
				  <head>
					<meta charset='utf-8'>
					<meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
						<style>
						/* latin-ext */
						@font-face {
						  font-family: 'Lato';
						  font-style: normal;
						  font-weight: 400;
						  src: local('Lato Regular'), local('Lato-Regular'), url(https://fonts.gstatic.com/s/lato/v16/S6uyw4BMUTPHjxAwXiWtFCfQ7A.woff2) format('woff2');
						  unicode-range: U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
						}
						/* latin */
						@font-face {
						  font-family: 'Lato';
						  font-style: normal;
						  font-weight: 400;
						  src: local('Lato Regular'), local('Lato-Regular'), url(https://fonts.gstatic.com/s/lato/v16/S6uyw4BMUTPHjx4wXiWtFCc.woff2) format('woff2');
						  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
						}
						/* latin-ext */
						@font-face {
						  font-family: 'Lato';
						  font-style: normal;
						  font-weight: 700;
						  src: local('Lato Bold'), local('Lato-Bold'), url(https://fonts.gstatic.com/s/lato/v16/S6u9w4BMUTPHh6UVSwaPGQ3q5d0N7w.woff2) format('woff2');
						  unicode-range: U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
						}
						/* latin */
						@font-face {
						  font-family: 'Lato';
						  font-style: normal;
						  font-weight: 700;
						  src: local('Lato Bold'), local('Lato-Bold'), url(https://fonts.gstatic.com/s/lato/v16/S6u9w4BMUTPHh6UVSwiPGQ3q5d0.woff2) format('woff2');
						  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
						}

						.pte_pdf_directory_container{
							margin-left: 40px;
							margin-top: 10px;
						}

						.pte_pdf_directory_image_cell{
							width: 35px;
						}

						.pte_pdf_directory_text_cell{
							font-weight: bold;
						}


						.pte_pdf_file{
							margin-left: 42px;
						}

						body{
							color: #444;
							font-size: 14pt;
							font-family: 'Lato', sans-serif;
							font-weight: 400;
						}
					</style>
					<title>ProTeam Edge - PDF</title>
				  </head>
				  <body>";
			$html .= getHtml($newArray);
			$html .= "
				</body>
			</html>
			";

			$reportSettings['html_content'] = $html;
			$pdf = "{$templateDirectory}-child-master/tmp/{$pdfKey}";
			$report = new zip2pdf($reportSettings);

			$report->run()
			->export('zip2pdf')
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

			$fileContent = file_get_contents($pdf);
			$options = ['gs' => ['Content-Type' => "application/pdf"]];
			$context = stream_context_create($options);
			file_put_contents("gs://pte_file_store1/{$pdfKey}", $fileContent, 0, $context);
			$pdfSize = filesize($pdf);
			unlink ($pdf);
		}
		unlink ($file);

		return $pdfSize;

	} catch (\Exception $e) { // Global namespace
			$pte_response = array("topic" => "pte_get_cloud_file_google_exception", "message" => "Problem accessing Google Cloud Storage.", "data" => $e);
			alpn_log($pte_response);
			exit;
	}
}

function pte_zip_structure_pdf($pteFileKey){

	$pdfKey = wp_generate_uuid4() . ".pdf";

	$reportSettings = array(
		'orientation' => 'portrait',
		'page_size' => 'letter',
		'topic_id' => '11',
		'highlight_color' => '#0074BB',
		'header_footer_style' => 'z2p',
		"pte_file_key" => $pteFileKey,
		"pte_pdf_key" => $pdfKey
	);
$pdfFileSize =	createAndStorePdf ($reportSettings);
return array(
	"pte_pdf_key" =>  $pdfKey,
	"pte_pdf_size" => $pdfFileSize
);
}
?>
