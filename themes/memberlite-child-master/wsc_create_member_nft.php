<?php
include('/var/www/html/proteamedge/public/wp-load.php');

$siteUrl = get_site_url();
$userId = get_current_user_id();

alpn_log("Creating Member NFT");

if (!is_user_logged_in() ) {
	alpn_log("Not a valid request");
	die;
}
if (!check_ajax_referer('alpn_script', 'security', FALSE)) {
	 alpn_log("Not a valid request");
   die;
}
$qVars = $_POST;

$contractAddress = isset($qVars['contract_address']) && $qVars['contract_address'] ? $qVars['contract_address'] : false;
$walletAddress = isset($qVars['wallet_address']) && $qVars['wallet_address'] ? $qVars['wallet_address'] : false;
$chainId = isset($qVars['chain_id']) && $qVars['chain_id'] ? $qVars['chain_id'] : false;
$processId = isset($qVars['process_id']) && $qVars['process_id'] ? $qVars['process_id'] : false;
$nftTokenId = isset($qVars['nft_token_id']) && $qVars['nft_token_id'] ? $qVars['nft_token_id'] : false;
$nftQuantity = isset($qVars['nft_quantity']) && $qVars['nft_quantity'] ? $qVars['nft_quantity'] : 1;
$nftRecipientAddress = isset($qVars['nft_recipient_id']) && $qVars['nft_recipient_id'] ? $qVars['nft_recipient_id'] : "";
$contractTemplateId = isset($qVars['contract_template_id']) && $qVars['contract_template_id'] ? $qVars['contract_template_id'] : false;

if ($contractAddress && $walletAddress && $chainId && $processId && $nftTokenId) {

	try {
		$nftData = array(
			"contract_address" => $contractAddress,
			"token_id" => $nftTokenId,
			"wallet_address" => $walletAddress,
			"process_id" => $processId,
			"chain_id" => $chainId,
			"quantity" => $nftQuantity,
			"state" => 'processing',
			"recipient_address" => $nftRecipientAddress,
			"template_id" => $contractTemplateId
		);
		$wpdb->insert( 'alpn_nfts_deployed', $nftData );
		$returnData = array("data" => $nftData);

		$processData = array(
			'process_id' => $processId,
			'process_type_id' => "mint_nft",
			'process_data' => array(
					'nft_wallet_address' => $walletAddress,
					'nft_record_id' => $wpdb->insert_id,
					'nft_contract_address' => $contractAddress,
					'nft_contract_chain_id' => $chainId,
					'nft_token_id' => $nftTokenId,
					'nft_contract_template_id' => $contractTemplateId,
					'nft_recipient_address' => $nftRecipientAddress,
					'nft_quantity' => $nftQuantity,
				)
		);
		pte_manage_interaction($processData);

	} catch (Exception $e) {
		alpn_log($e);
		$returnData = array("error" => "exception");
	}

} else {
	$returnData = array("error" => "missing required fields");
}

header('Content-Type: application/json');
echo json_encode($returnData);

?>
