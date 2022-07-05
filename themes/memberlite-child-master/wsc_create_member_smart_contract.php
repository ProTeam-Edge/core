<?php
include('/var/www/html/proteamedge/public/wp-load.php');

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

$contractAddress = isset($qVars['contract_address']) && $qVars['contract_address'] ? $qVars['contract_address'] : false;
$walletAddress = isset($qVars['wallet_address']) && $qVars['wallet_address'] ? $qVars['wallet_address'] : false;
$chainId = isset($qVars['chain_id']) && $qVars['chain_id'] ? $qVars['chain_id'] : false;
$processId = isset($qVars['process_id']) && $qVars['process_id'] ? $qVars['process_id'] : false;
$contractCollectionName = isset($qVars['contract_collection_name']) && $qVars['contract_collection_name'] ? $qVars['contract_collection_name'] : "";
$contractCollectionSymbol = isset($qVars['contract_collection_symbol']) && $qVars['contract_collection_symbol'] ? $qVars['contract_collection_symbol'] : "";
$contractTemplateId = isset($qVars['contract_template_id']) && $qVars['contract_template_id'] ? $qVars['contract_template_id'] : false;

if ($contractAddress && $walletAddress && $chainId && $processId) {

	try {
		$smartContractData = array(
			"contract_address" => $contractAddress,
			"wallet_address" => $walletAddress,
			"process_id" => $processId,
			"chain_id" => $chainId,
			"collection_name" => $contractCollectionName,
			"collection_symbol" => $contractCollectionSymbol,
			"template_id" => $contractTemplateId
		);
		$wpdb->insert( 'alpn_smart_contracts_deployed', $smartContractData );
		$returnData = array("new_id" => $wpdb->insert_id, "data" => $smartContractData);

		$processData = array(
			'process_id' => $processId,
			'process_type_id' => "deploy_contract",
			'process_data' => array(
					'contract_start_deploy' => true,
					'smart_contract_address' => $contractAddress,
					'smart_contract_chain_id' => $chainId,
					'smart_contract_template_id' => $contractTemplateId
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
