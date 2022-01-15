<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php'); //TODO WTF is the difference and can we be consistent
require('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/vendor/autoload.php');
require_once('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/typeset.sh.lib.phar');
require_once "zip2pdf.php";

use Google\Cloud\Storage\StorageClient;
use Ramsey\Uuid\Uuid;

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
										<td class='pte_pdf_directory_image_cell'><img src='https://wiscle.com/wp-content/themes/memberlite-child-master/folder.jpeg'></td>
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
	    	'keyFilePath' => '/var/www/html/proteamedge/private/proteam-edge-cf8495258f58.json'
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
				$testUuid = substr($zen, 0, 36);
				$UUIDv4 = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
				if (preg_match($UUIDv4, $testUuid)) {  //TODO removing uuid for zip if in there but should really be metadata
					$zen = substr($zen, 36);
				}
				if (zip_entry_filesize($zip_entry)) {
					$zeo = formatBytes(zip_entry_filesize($zip_entry)) . "b";
					$urls[] = "{$zen} ({$zeo})";
				}
				//$zec = formatBytes(zip_entry_compressedsize($zip_entry))  . "b";
			}
			zip_close($zip);
			$newArray = explodeTree($urls);

			$reportSettings['html_content'] = getHtml($newArray);
			$pdf = "{$templateDirectory}-child-master/tmp/{$pdfKey}";
			$report = new zip2pdf($reportSettings);

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

				$fileContent = file_get_contents($pdf);
				$options = ['gs' => ['Content-Type' => "application/pdf"]];
				$context = stream_context_create($options);
				file_put_contents("gs://pte_file_store1/{$pdfKey}", $fileContent, 0, $context);
				$pdfSize = filesize($pdf);
				unlink ($pdf);
				return $pdfSize;

			} catch(Exception $e) {

				alpn_log("EXCEPTION BROWSER");
				alpn_log($e);

			}
		}

	} catch(Exception $e) {
		alpn_log("EXCEPTION GOOGLE CLOUD FILES");
		alpn_log($e);
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
