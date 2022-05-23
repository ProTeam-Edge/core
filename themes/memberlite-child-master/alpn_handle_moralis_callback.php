<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$processAfter = '+ 5 minutes';   //checking with Moralis on how long to wait

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


	 // alpn_log($moralisData);

	$fromAddress = ($mObject['from_address'] != "0x0000000000000000000000000000000000000000") ? $mObject['from_address'] : false;
	$toAddress = ($mObject['to_address'] != "0x0000000000000000000000000000000000000000") ? $mObject['to_address'] : false;
	$tokenAddress = $mObject['token_address'];
	$tokenId = $mObject['token_id'];
	$amount = (isset($mObject['amount'])) ? $mObject['amount'] : 0;
	$className = (isset($mObject['className'])) ? $mObject['className'] : 'EthNFTTransfers';
	$confirmed = (isset($mObject['confirmed'])) ? $mObject['confirmed'] : false;
	if ($className == "EthNFTTransfers") {
		$chainId = "eth";
	} else if ($className == "PolygonNFTTransfers") {
		$chainId = "polygon";
	}

	if (!$confirmed) {
		alpn_log("MORALIS SKIP UNCONFIRMED");
		exit;
	}

	// exit;

	if ($toAddress && $tokenAddress) {

		alpn_log("PROCESSING TRANSFERRED NFT");

		//Check if exists in wiscle
		$nftResults = $wpdb->get_results(
			$wpdb->prepare("SELECT id FROM alpn_nft_meta WHERE contract_address = %s AND token_id = %s AND chain_id = %s", $tokenAddress, $tokenId, $chainId)
		 );

		 if (!isset($nftResults[0])){  //Process it

			alpn_log("PROCESSING NEW NFT");

			$futureGm = gmdate ("Y-m-d H:i:s", strtotime($processAfter));

			try {
				$data = array(
				 "state" => "processing",
 				 "owner_address" => $toAddress,
				 "contract_address" => $tokenAddress,
				 "token_id" => $tokenId,
 				 "chain_id" => $chainId,
 				 "process_after" => $futureGm
 			 );
 			$wpdb->insert( 'alpn_nft_meta', $data );

			} catch (Exception $error) {
			 alpn_log("NEW NFT TRANSFERRED - CAN'T ADD");
			 alpn_log($error);
			}

		 }

		if ($fromAddress) {
			alpn_log("DO A FROM TRANSFER?");


		}


	}




	//if fromAddress, remove
	//if toAddress, update/add




} else {
	//Skip
	// alpn_log("MORALIS CALLBACK ERROR -- MISSING DATA");
	// alpn_log($moralisData);
	// alpn_log($post);
}

?>
