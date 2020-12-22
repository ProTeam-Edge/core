<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
require('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/vendor/autoload.php');

use Google\Cloud\Storage\StorageClient;

//TODO RUGGEDIZE -- queries, exceptions, size of files??, logs SECURITY!!!, pass in id and check for match. nonce thing maybe?
//handle_create_zip_preview_pdf

function getHtml($tree){
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
										<td class='pte_pdf_directory_image_cell'><img src='folder.jpeg'></td>
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

//alpn_log ("Starting Create PDF...");

$pVars = unserialize($argv[1]);
$userID = isset($pVars['alpn_uid']) ? $pVars['alpn_uid'] : '';
$fileKey = isset($pVars['file_key']) ? $pVars['file_key'] : '';
$fileName = isset($pVars['file_name']) ? $pVars['file_name'] : '';
$vaultId = isset($pVars['vault_id']) ? $pVars['vault_id'] : '';
$jobId = isset($pVars['job_id']) ? $pVars['job_id'] : '';
$domId = isset($pVars['dom_id']) ? $pVars['dom_id'] : '';
$ownerId = isset($pVars['owner_id']) ? $pVars['owner_id'] : '';

$pdfKey = $fileKey . ".pdf";

//alpn_log($pVars);


if (!$fileKey) {
	$pte_response = array("topic" => "pte_create_pdf_from_zip_data_missing_error", "message" => "Create pdf from zip missing data.", "data" => $pVars);
	alpn_log($pte_response);
	exit;	
}

//Get Zip file

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
			
		$mpdf = new \Mpdf\Mpdf(
			['setAutoTopMargin' => 'stretch',
			 'setAutoBottomMargin' => 'stretch',
			 'autoMarginPadding' => 5]
		);		
		$mpdf->SetHTMLHeader("<div style='text-align: left; font-size: 1.2em; text-decoration: underline;'>{$fileName}</div>");
		$mpdf->SetHTMLFooter("
		<table width='100%'>
			<tr>
				<td width='50%'>{DATE M j, Y}</td>
				<td width='50%' style='text-align: right;'>{PAGENO}/{nbpg}</td>
			</tr>
		</table>");
		
		$mpdf->WriteHTML($html);
		$pdf = "{$templateDirectory}-child-master/tmp/{$pdfKey}";	
		$mpdf->Output($pdf, \Mpdf\Output\Destination::FILE);
			
		$fileContent = file_get_contents($pdf);
		$options = ['gs' => ['Content-Type' => "application/pdf"]];
		$context = stream_context_create($options);
		file_put_contents("gs://pte_file_store1/{$pdfKey}", $fileContent, 0, $context);	

		unlink ($pdf);
	}	
	unlink ($file);
		
} catch (\Exception $e) { // Global namespace
		$pte_response = array("topic" => "pte_get_cloud_file_google_exception", "message" => "Problem accessing Google Cloud Storage.", "data" => $e);
		alpn_log($pte_response);
		exit;
}

$now = date ("Y-m-d H:i:s", time());
$rowData = array(
	"status" => 'ready',
	"pdf_key" => $pdfKey,
	"ready_date" => $now
);
$whereClause['id'] = $vaultId; 	
$wpdb->update( 'alpn_vault', $rowData, $whereClause );	

$rowData1 = array(
	"status" => 'closed',
	"reason" => 'success',
	"completed_date" => $now
);	
$whereClause1['id'] = $jobId;
$wpdb->update( 'alpn_jobs', $rowData1, $whereClause1 );

$rowData['vault_id'] = $vaultId;
$rowData['dom_id'] = $domId;
$rowData['job_id'] = $jobId;
$rowData['owner_id'] = $ownerId;

$pte_response = array("topic" => "pte_create_pdf_preview_successful", "message" => "Successfully created PDF preview.", "data" => $rowData);
pte_send_notification($ownerId, "file_received", $pte_response);	


?>