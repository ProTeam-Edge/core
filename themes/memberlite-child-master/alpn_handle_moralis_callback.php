<?php
include('/var/www/html/proteamedge/public/wp-load.php');

alpn_log("MORALIS CALLBACK");
$post = file_get_contents('php://input');
$moralisData = json_decode($post, true);

$mHeaders = isset($moralisData['headers']) ? $moralisData['headers'] : false;
$mTriggerName = isset($moralisData['triggerName']) ? $moralisData['triggerName'] : false;
$mObject = isset($moralisData['object']) ? $moralisData['object'] : false;
$mIp = isset($moralisData['ip']) ? $moralisData['ip'] : false;

//create events

if ($mHeaders && $mTriggerName && $mObject && $mIp) {

	if ($mHeaders['x-parse-application-id'] != MORALIS_APPID) {
		alpn_log("MORALIS WRONG APP ID");
		alpn_log($mHeaders['x-parse-application-id']);
		exit;
	}

	if ($mTriggerName != "afterSave") {
		alpn_log("MORALIS WRONG Trigger");
		alpn_log($mTriggerName);
		exit;
	}

	// Check IP address if consistent

	$fromAddress = ($mObject['from_address'] && $mObject['from_address'] != "0x0000000000000000000000000000000000000000") ? $mObject['from_address'] : false;
	$toAddress = ($mObject['to_address'] && $mObject['to_address'] != "0x0000000000000000000000000000000000000000") ? $mObject['to_address'] : false;
	$tokenAddress = $mObject['token_address'];
	$tokenId = $mObject['token_id'];
	$amount = (isset($mObject['amount'])) ? $mObject['amount'] : 0;
	$className = (isset($mObject['className'])) ? $mObject['className'] : 'EthNFTTransfers';
	$confirmed = (isset($mObject['confirmed'])) ? $mObject['confirmed'] : false;
	$transactionHash = (isset($mObject['hash'])) ? $mObject['hash'] : false;

	if ($className == "EthNFTTransfers" || $className == "EthTransactions") {
		$chainId = "eth";
	} else if ($className == "PolygonNFTTransfers" || $className == "PolygonTransactions") {
		$chainId = "polygon";
	}
	if ($className == "EthTransactions" || $className == "PolygonTransactions") {
		$isTransaction = true;
		$isTransfer = false;
	} else if ($className == "EthNFTTransfers" || $className == "PolygonNFTTransfers") {
		$isTransaction = false;
		$isTransfer = true;
	}

	if ($isTransfer && $toAddress && $tokenAddress && !$confirmed) {
		alpn_log("PROCESSING TRANSFERRED NFT");

		// alpn_log($moralisData);

		$nftResults = $wpdb->get_results(
			$wpdb->prepare("SELECT id FROM alpn_nft_meta WHERE contract_address = %s AND token_id = %s AND chain_id = %s", $tokenAddress, $tokenId, $chainId)
		 );

		 // alpn_log($nftResults);

		 if (!isset($nftResults[0])){  //Process it
			alpn_log("PROCESSING NEW NFT");
			$currentGm = gmdate ("Y-m-d H:i:s", time());
			try {
				$data = array(
				 "state" => "processing",
 				 "owner_address" => $toAddress,
				 "contract_address" => $tokenAddress,
				 "token_id" => $tokenId,
 				 "chain_id" => $chainId,
 				 "process_after" => $currentGm
 			 );
 			$wpdb->insert( 'alpn_nft_meta', $data );
			alpn_log($data);

			} catch (Exception $error) {
			 alpn_log("NEW NFT TRANSFERRED - CAN'T ADD");
			 alpn_log($error);
			}

		} else {  //move owners
			$nftId = $nftResults[0]->id;
			alpn_log("MOVING OWNERS - " . $nftId);
			try {
				$data = array(
				 "owner_address" => $toAddress,
				 "category_id" => "visible"
			  );
				$whereclause = array(
				 "id" => $nftId
			 );
			  $results = $wpdb->update( 'alpn_nft_meta', $data, $whereclause );
			} catch (Exception $error) {
			 alpn_log("NEW NFT TRANSFERRED - CAN'T UPDATE");
			 alpn_log($error);
			}

		}

		$nftDeployedResults = $wpdb->get_results(
			$wpdb->prepare("SELECT process_id FROM alpn_nfts_deployed WHERE contract_address = %s AND token_id = %s AND chain_id = %s", $tokenAddress, $tokenId, $chainId)
		 );
		if (isset($nftDeployedResults[0])) {
			//Update to ready.
			$processData = array(
				'process_id' => $nftDeployedResults[0]->process_id,
				'process_type_id' => "mint_nft",
				'process_data' => array(
						'finish_mint_nft' => true,
						'nft_wallet_error' => false
					)
			);
			pte_manage_interaction($processData);
		}
	}


	if ($isTransaction && $transactionHash && $fromAddress && !$confirmed) {

		alpn_log("PROCESSING SMART CONTRACT TRANSACTION");
		alpn_log($fromAddress);
		alpn_log($transactionHash);

		$headers = array();
		$moralisApiKey = MORALIS_API_KEY;
		$fullUrl = "https://deep-index.moralis.io/api/v2/transaction/{$transactionHash}?chain={$chainId}";
		$headers[] = "Accept: application/json";
		$headers[] = "X-API-Key: {$moralisApiKey}";
		$options = array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_URL => $fullUrl,
				CURLOPT_HTTPHEADER => $headers
		);
		 $ch = curl_init();
		 curl_setopt_array($ch, $options);
		 $response = curl_exec($ch);
		 curl_close($ch);

		$transactionDetails = json_decode($response, true);
		$newContractAddress = $transactionDetails['receipt_contract_address'];

		if ($newContractAddress) {
			$contractData = array('state' => 'ready');
			$whereClause = array('contract_address' => $newContractAddress);
			$wpdb->update( 'alpn_smart_contracts_deployed', $contractData, $whereClause);

			$contractDeployed = $wpdb->get_results(
				$wpdb->prepare("SELECT process_id FROM alpn_smart_contracts_deployed WHERE contract_address = %s", $newContractAddress)
			 );
			 if (isset($contractDeployed[0])) {
				 alpn_log("NOTIFY PROCESS COMPLETE");
				 $processData = array(
					 'process_id' => $contractDeployed[0]->process_id,
					 'process_type_id' => "deploy_contract",
					 'process_data' => array(
							 'contract_finish_deploy' => true
						 )
				 );
				 pte_manage_interaction($processData);
			 }

		}	 else {
			alpn_log("MORALIS TRANSACTION NO CONTRACT ADDRESS");
			alpn_log($transactionDetails);
		}
	}

} else {
	//Skip
	// alpn_log("MORALIS CALLBACK ERROR -- MISSING DATA");
	// alpn_log($moralisData);
	// alpn_log($post);
}

?>
