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
$setName = isset($qVars['set_name']) && $qVars['set_name'] ? $qVars['set_name'] : false;

if ($nftId && $setName && $userId) {
	try {
		$whereClause = array (
			"owner_id" => $userId,
			"nft_id" => $nftId,
			"set_name" => $setName
		);
		$wpdb->delete( 'alpn_nft_sets', $whereClause );
		$returnData = array("success" => true);
	} catch (Exception $error) {
		$returnData = array("error" => "unknown");
	}
} else {
	$returnData = array("error" => "missing required fields");
}

header('Content-Type: application/json');
echo json_encode($returnData);

?>
