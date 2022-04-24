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

if ($nftId && $categoryId && $userId) {
	try {

		$ownerCheck = $wpdb->get_results(
			$wpdb->prepare("SELECT id FROM alpn_nft_owner_view WHERE owner_id = %d AND id = %d", $userId, $nftId)
		 );

		 if (isset($ownerCheck[0])) {
			 $nftData = array (
				 "category_id" => $categoryId
			 );
			 $whereData = array(
				 "id" => $nftId
			 );
			 $wpdb->update( 'alpn_nft_meta', $nftData, $whereData );
			 $returnData = array("success" => true);
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
