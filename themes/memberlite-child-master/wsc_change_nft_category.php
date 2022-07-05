<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$siteUrl = get_site_url();
$userId = get_current_user_id();

if (!is_user_logged_in() ) {
	alpn_log("Not a valid request");
	die;
}
if (!check_ajax_referer('alpn_script', 'security', FALSE)) {
	 alpn_log("Not a valid request");
   die;
}
$qVars = $_POST;

$nftId = isset($qVars['nft_id']) && $qVars['nft_id'] ? $qVars['nft_id'] : false;
$categoryId = isset($qVars['category_id']) && $qVars['category_id'] ? $qVars['category_id'] : false;
$sourceId = isset($qVars['source_id']) && $qVars['source_id'] ? $qVars['source_id'] : false;
$viewingCategoryId = isset($qVars['viewing_category_id']) && $qVars['viewing_category_id'] ? $qVars['viewing_category_id'] : false;

if ($nftId && $categoryId && $userId && $sourceId && $viewingCategoryId) {

	try {

		$ownerCheck = $wpdb->get_results(
			$wpdb->prepare("SELECT id, contract_address, owner_address FROM alpn_nft_owner_view WHERE owner_id = %d AND id = %d", $userId, $nftId)
		 );

		 if (isset($ownerCheck[0])) {

			 $originalNftId = $ownerCheck[0]->id;
			 $originalNftOwnerAddress = $ownerCheck[0]->owner_address;
			 $originalNftContractAddress = $ownerCheck[0]->contract_address;

			 $doNotDeleteAllContractList = array("0x495f947276749ce646f68ac8c248420045cb7b5e");

			 if ($sourceId == "single") {
				 $nftData = array (
					 "category_id" => $categoryId
				 );
				 $whereData = array(
					 "id" => $nftId
				 );
				 $wpdb->update( 'alpn_nft_meta', $nftData, $whereData );
				 $returnData = array("success" => true);

			 } else if ($sourceId == "all" && !in_array($originalNftContractAddress, $doNotDeleteAllContractList)) {

				 $rows_affected = $wpdb->query($wpdb->prepare(
					 "UPDATE alpn_nft_meta SET category_id = %s WHERE owner_address = %s AND contract_address = %s;", $categoryId, $originalNftOwnerAddress, $originalNftContractAddress
					));
				 $returnData = array("success" => true, "rows_affected" => $rows_affected );

			 } else if ($sourceId == "all_ex" && !in_array($originalNftContractAddress, $doNotDeleteAllContractList)) {

				 $rows_affected = $wpdb->query($wpdb->prepare(
					 "UPDATE alpn_nft_meta SET category_id = %s WHERE owner_address = %s AND contract_address = %s AND id != %d;", $categoryId, $originalNftOwnerAddress, $originalNftContractAddress, $originalNftId
					));
				 $returnData = array("success" => true, "rows_affected" => $rows_affected );
			 }
		 } else {
			 $returnData = array("error" => "nft does not belong to this user");
		 }
	} catch (Exception $error) {
		$returnData = array("error" => "unknown");
	}
} else {
	$returnData = array("error" => "missing required fields");
}

header('Content-Type: application/json');
echo json_encode($returnData);

?>
