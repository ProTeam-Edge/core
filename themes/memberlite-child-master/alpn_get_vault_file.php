<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');

require 'vendor/autoload.php';
use Google\Cloud\Storage\StorageClient;

//TODO RUGGEDIZE -- queries, exceptions, size of files??, logs SECURITY!!!, pass in id and check for match. nonce thing maybe?
//alpn_log("after db..." . pte_time_elapsed(microtime() - $nowtime));
//$nowtime = microtime();

//TODO do not check over and over in failure situations.

//$siteUrl = get_site_url();

$qVars = $_GET;
$vId = isset($qVars['v_id']) ? $qVars['v_id'] : '';
$whichFile = isset($qVars['which_file']) ? $qVars['which_file'] : 'original';

$results = $wpdb->get_results(
	$wpdb->prepare("SELECT mime_type, file_name, pdf_key, file_key FROM alpn_vault WHERE id = %s", $vId)   //TODO check for logged in.
 );

if (array_key_exists('0', $results)) {
	$mimeType = $results[0]->mime_type;
	$fileName = $results[0]->file_name;
	$currentExt = substr($fileName, -4);

	if ($whichFile == 'pdf') {
		$objectName = $results[0]->pdf_key ? $results[0]->pdf_key : $results[0]->file_key;
		$mimeType = "application/pdf";
		$fileName = $results[0]->pdf_key;
	} else {
		$objectName = $results[0]->file_key;
	}
try {
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
		$pte_response = array("topic" => "pte_get_vault_record_not_found", "message" => "Vault record not found.", "data" => $results);
		pp($pte_response);
		exit;
}

?>
