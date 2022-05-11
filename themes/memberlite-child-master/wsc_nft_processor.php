<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
//error_reporting(E_ERROR);

echo("Handle NFT Processing" . PHP_EOL);

$waitSeconds = 60;
$nftQuery = "SELECT * FROM alpn_nft_meta WHERE state = 'processing' AND process_after <= %s ORDER BY id LIMIT 1";

$nowGm = gmdate ("Y-m-d H:i:s", time());
$result = $wpdb->get_results($wpdb->prepare($nftQuery, $nowGm));

if (isset($result[0])) {
	$processNft = true;
	$waitForNft = false;
	$nftData = $result[0];
	if ($nftData->id < 9999) {
		echo("Exiting Gracefully" . PHP_EOL);
		exit;
	}
} else {
	$processNft = false;
	$waitForNft = true;
	$nftData = array();
}

while ($processNft || $waitForNft) {

	if ($processNft) {

		$contractAddress = $nftData->contract_address;
		$tokenId = $nftData->token_id;
		$chainId = $nftData->chain_id;

		$nftMetaUpdate = array( //TODO address risk of multiple concurrent processors hitting same record? SMALL
			"state" => "busy"
		);
		$nftWhere = array(
			"contract_address" => $contractAddress,
			"token_id" => $tokenId,
			"chain_id" => $chainId
		);
		$wpdb->update( 'alpn_nft_meta', $nftMetaUpdate, $nftWhere );

		echo("Processing | " . $nftData->id . " | " . $nowGm . PHP_EOL);

		//many wild west failures yet to be understood. Separating to increase success
	  $result = pte_sync_curl_nft ("wsc_process_single_nft", $nftData);

		$data = json_decode($result, true);

		if (isset($data['state'])){
			$wpdb->update( 'alpn_nft_meta', $data, $nftWhere );
			$errorCode = (isset($data['error_code']) && $data['error_code']) ? " - " . $data['error_code'] : "";
			echo("SUCCESS - " . $data['state'] . $errorCode . PHP_EOL);
		} else {
			echo("DISASTER" . PHP_EOL);
			$data = array(
				"state" => "failed",
				"error_code" => "disaster",
				"category_id" => "error"
			);
			$wpdb->update( 'alpn_nft_meta', $data, $nftWhere );
			print_r($nftData);
			//print_r($result);
		}
	} else if ($waitForNft) {
		echo("Waiting {$waitSeconds} for NFTs..." . PHP_EOL);
		sleep($waitSeconds);
	}
	$nowGm = gmdate ("Y-m-d H:i:s", time());
	$result = $wpdb->get_results($wpdb->prepare($nftQuery, $nowGm));
	if (isset($result[0])) {
		$processNft = true;
		$waitForNft = false;
		$nftData = $result[0];
		if ($nftData->id < 9999) {
			echo("Exiting Gracefully" . PHP_EOL);
			exit;
		}
	} else {
		$processNft = false;
		$waitForNft = true;
		$nftData = array();
	}
}
?>
