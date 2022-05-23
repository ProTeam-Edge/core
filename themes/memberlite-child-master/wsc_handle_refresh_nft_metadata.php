<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');

if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}
alpn_log("Updating NFT Metadata");

$userInfo = wp_get_current_user();
$userId = $userInfo->data->ID;

$qVars = $_POST;

$nftId = isset($qVars['nft_id']) ? $qVars['nft_id'] : false;

if ($userId && $nftId) {

	$results = $wpdb->get_results(
		$wpdb->prepare("SELECT * from alpn_nft_meta WHERE id = %d LIMIT 1", $nftId)
	 );

	 if (isset($results[0])) {

		 $contractAddress = $results[0]->contract_address;
		 $tokenId = $results[0]->token_id;
		 $chainId = $results[0]->chain_id;

		 $result = wsc_nft_deep_update($contractAddress, $tokenId, $chainId);

   }
}

pte_json_out(array("qvars" => $qVars, "result" => $result));

?>
