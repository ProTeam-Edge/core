<?php
include('../../../wp-blog-header.php');
require_once('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/vendor/autoload.php');

use Google\Cloud\Storage\StorageClient;

//TODO Check logged in, etc. Good Request. User-ID in all mysql
if (!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}
$qVars = $_POST;
$vaultId = isset($qVars['vault_id']) ? pte_digits($qVars['vault_id']) : false;

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

global $wpdb;

//Delete at PTE
$pte_response = array();

if ($vaultId) {

	$pte_response = array("error" => 'unable_to_delete_vault_item', "message" => "Unable to delete Vault Item.", "vault_id" => $vaultId);

	$rightsCheckData = array(
	  "vault_id" => $vaultId
	);
	if (!pte_user_rights_check("vault_item_edit", $rightsCheckData)) {
		$pte_response = array("error" => 'insufficent_rights', "message" => "Insufficient rights to delete vault item.", "vault_id" => $vaultId);
		pte_json_out($pte_response);
	  exit;
	}

	try {
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT file_key, pdf_key FROM alpn_vault WHERE id = %d", $vaultId)
		 );

		if (isset($results[0])) {
			$fileDeleted = $pdfDeleted = false;
			$vaultInfo = $results[0];
			$fileKey = $vaultInfo->file_key;
			$pdfKey = $vaultInfo->pdf_key;
			if ($fileKey) {
				$fileDeleted = delete_from_cloud_storage($fileKey);
			}
			if ($pdfKey) {
				$pdfDeleted = delete_from_cloud_storage($pdfKey);
			}
			if ($fileDeleted || $pdfDeleted) {
				$whereClause['id'] = $vaultId;
				$wpdb->delete( 'alpn_vault', $whereClause );
				$pte_response = array("error" => false, "message" => "Successfully deleted Vault Item.", "vault_id" => $vaultId);
			}
		}
	} catch(Exception $e) {
			alpn_log("Exception Deleting Vault Item");
			alpn_log($e);
	}
}
pte_json_out($pte_response);

?>
