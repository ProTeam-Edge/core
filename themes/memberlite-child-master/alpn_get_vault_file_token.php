<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');

require 'vendor/autoload.php';
use Google\Cloud\Storage\StorageClient;

//TODO RUGGEDIZE -- queries, exceptions, size of files??, logs SECURITY!!!, pass in id and check for match. nonce thing maybe?
//TODO do not check over and over in failure situations.

//$siteUrl = get_site_url();
$html = "";
$qVars = $_GET;

if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}

$token = isset($qVars['token']) ? $qVars['token'] : '';
$whichFile = isset($qVars['which_file']) ? $qVars['which_file'] : 'original';

//$vId = "23498712983719283712";
//use token to get vault id and retrurn period.

$results = $wpdb->get_results(
	$wpdb->prepare("SELECT v.mime_type, v.file_name, v.pdf_key, v.file_key, l.link_meta FROM alpn_links l LEFT JOIN alpn_vault v ON v.id = l.vault_id WHERE l.uid = %s", $token)   //TODO check for logged in.
 );

if (isset($results[0])) {


  alpn_log("Link META");
  $linkMeta = json_decode($results->link_meta, true);

  alpn_log($linkMeta);
  

	$mimeType = $results[0]->mime_type;
	$fileName = $results[0]->file_name;



	$fileNameExtension = pathinfo($fileName, PATHINFO_EXTENSION);

	if (!$fileNameExtension) {
		$originalExtension = pathinfo($results[0]->file_key, PATHINFO_EXTENSION);
		$fileName = "{$fileName}.{$originalExtension}";
	}

	if ($whichFile == 'pdf') {
		$objectName = $results[0]->pdf_key ? $results[0]->pdf_key : $results[0]->file_key;
		$mimeType = "application/pdf";
		$fileName = ($fileNameExtension == "pdf") ? $results[0]->file_name : $results[0]->file_name . ".pdf";
	} else {
		$objectName = $results[0]->file_key;
	}
try {

	http_response_code (200);

	$storage = new StorageClient([
			'keyFilePath' => '/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/proteam-edge-cf8495258f58.json'
	]);
	$storage->registerStreamWrapper();
	$content = file_get_contents("gs://pte_file_store1/{$objectName}");

	header('Content-Disposition: attachment; filename="' . $fileName . '"');
	header("Content-Type: {$mimeType}");
	header("Content-Length: " . strlen($content));
	echo $content;
} catch (\Exception $e) { // Global namespace
		$pte_response = array("topic" => "pte_get_vault_google_exception", "message" => "Problem accessing Google Vailt.", "data" => $e);
		pp($pte_response);
		exit;
}
} else {
	http_response_code (204); //TODO fix this
}
echo $html;
?>
