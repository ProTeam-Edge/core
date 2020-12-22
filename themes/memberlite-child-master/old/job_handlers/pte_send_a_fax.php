<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
require('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/vendor/autoload.php');

use Google\Cloud\Storage\StorageClient;
use Twilio\Rest\Client;

//TODO RUGGEDIZE -- queries, exceptions, size of files??, logs SECURITY!!!, pass in id and check for match. nonce thing maybe?
//handle_create_zip_preview_pdf


pp ("Starting to fax PDF...");


$pVars = unserialize($argv[1]);
$vaultId = isset($pVars['v_id']) ? $pVars['v_id'] : '';
$jobId = isset($pVars['job_id']) ? $pVars['job_id'] : '';
$ownerId = isset($pVars['owner_id']) ? $pVars['owner_id'] : '';

$ownerId = '2';
$vaultId = '1400';
$outboundFaxNumber = '+16507290910';
$pteFaxNumber = '+114084191490';


if ($vaultId && $ownerId) {
	
	$sid = 'ACa3cfb8ff4e9f2b263e37a00f35c3e1ae';
	$token = 'e74ba46c3b14d739c731429877b37fc3';
	$client = new Client($sid, $token);

	$templateDirectory = get_template_directory_uri();
	$fileUri = "{$templateDirectory}-child-master/alpn_get_vault_file.php?which_file=pdf&v_id={$vaultId}";	
	
	pp($fileUri);

	$fax = $client->fax->v1->faxes
		->create($outboundFaxNumber,
			$fileUri,
			array("from" => $pteFaxNumber)
   );	

	pp($fax->sid);
	pp($fax);
	
	pp ("Fax complete...");

}







exit;

if (!$fileKey) {
	$pte_response = array("topic" => "pte_create_pdf_from_zip_data_missing_error", "message" => "Create pdf from zip missing data.", "data" => $pVars);
	alpn_log($pte_response);
	exit;	
}

//Get Zip file

try {	
	$storage = new StorageClient([
    	'keyFilePath' => 'proteam-edge-cf8495258f58.json'
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
		pp($pte_response);
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

/* SMS
$client->messages->create(
    '+1408-410-0365',
    array(
        'from' => '+114084191490',
        'body' => 'Hey Jenny! Good luck on the bar exam!'
    )
);
*/



?>