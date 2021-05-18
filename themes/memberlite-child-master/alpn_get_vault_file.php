<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');

alpn_log("Starting GET VAULT FILE");

require 'vendor/autoload.php';
use Google\Cloud\Storage\StorageClient;

//TODO RUGGEDIZE -- queries, exceptions, size of files??, logs SECURITY!!!, pass in id and check for match. nonce thing maybe?
//TODO do not check over and over in failure situations.

$html = "";
$qVars = $_GET;

if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}

if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   alpn_log('Not a valid request.');
   die;
}

$vId = isset($qVars['v_id']) ? pte_digits($qVars['v_id']) : '';
$whichFile = isset($qVars['which_file']) ? $qVars['which_file'] : 'original';

//TODO implement has rights to. Must be logged in and has rights to. Has rights to topic. Has rights to vault File.

$rightsCheckData = array(
  "vault_id" => $vId
);
if (!pte_user_rights_check("vault_item", $rightsCheckData)) {
	alpn_log('RIGHTS');
	header("PTE-Error-Code: insufficient_rights");
	http_response_code (204);
	exit;
}

$results = $wpdb->get_results(
	$wpdb->prepare("SELECT dom_id, mime_type, file_name, pdf_key, file_key FROM alpn_vault WHERE id = %s", $vId)   //TODO check for logged in.
 );

if (isset($results[0])) {

	$mimeType = $results[0]->mime_type;
	$fileName = $results[0]->file_name;

	if ($whichFile == 'pdf') {
		$objectName = $results[0]->pdf_key ? $results[0]->pdf_key : $results[0]->file_key;
		$fileName .= ".pdf";
	} else {
		$objectName = $results[0]->file_key;
	}

if (!$objectName) {
	alpn_log('Object Name');
	header("PTE-Error-Code: error_uploading");
	http_response_code (204);
	exit;
}

try {
	$vaultDomId = $results[0]->dom_id;

	header("PTE-Error-Code: false");
	header("PTE-Vault-Dom-Id: {$vaultDomId}");
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
		alpn_log($pte_response);
		header("PTE-Error-Code: error_uploading");
		http_response_code (204);
		exit;
}
} else {
	header("PTE-Error-Code: vault_row_not_found");
	http_response_code (204);
}
echo $html;
?>
