<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseUser;
use Parse\ParseException;
use Parse\ParseClient;
use Parse\ParseCloud;

alpn_log("Handle Background Wallet Processing");

$verificationKey = (isset($_POST['verification_key']) && strlen($_POST['verification_key']) >= 20 && strlen($_POST['verification_key']) <= 22) ? $_POST['verification_key'] : false;

if ( $verificationKey ) {

	$data = vit_get_kvp($verificationKey);

	if (isset($data['account_address']) && isset($data['owner_id'])) {

		$walletAddress = isset($data['account_address']) && $data['account_address'] ? $data['account_address'] : false;
		$ownerId = isset($data['owner_id']) && $data['owner_id'] ? $data['owner_id'] : false;
		$relation = isset($data['relation']) && $data['relation'] ? $data['relation'] : "visitor";
		$accountProcessed = isset($data['account_processed']) && $data['account_processed'] ? true : false;
		$firstTime = isset($data['first_time']) && $data['first_time']? $data['first_time'] : false;

		if (!$accountProcessed) {

			alpn_log("PROCESS Public Key for NFTs");

			$ensName = json_decode(wsc_resolve_ens($walletAddress))->name;
			$walletData = array (
				"account_address" => $walletAddress,
				"ens_address" => $ensName,
				"meta" => "{}"
			);
			$wpdb->insert( 'alpn_wallet_meta', $walletData );

			//Start Processing NFTs
			$nftTotal = wsc_get_all_member_nfts($walletAddress);

			if ($relation == "visitor") {
				try { //track non authenticated addresses. Srill need to know transfers.
			 		$parseClient = new ParseClient;
			 		$parseClient->initialize( MORALIS_APPID, null, MORALIS_MK );
			 		$parseClient->setServerURL(MORALIS_SERVER_URL, 'server');
					$results = ParseCloud::run("watchEthAddress", array(
						"address" => $walletAddress,
						"sync_historical" => true
					), array("useMasterKey" => true));
					$results = ParseCloud::run("watchPolygonAddress", array(
						"address" => $walletAddress,
						"sync_historical" => true
					), array("useMasterKey" => true));
		 	 } catch (ParseException $error) {
		 		alpn_log("PARSE CLOUD RUN FAILED");
		 		alpn_log($error);
		 	 }
			}
		}

		if ($firstTime) {

			$ownershipData = array (
				"account_address" => $walletAddress,
				"relation" => $relation,
				"owner_id" => $ownerId
			);
			$wpdb->insert( 'alpn_wallet_relationships', $ownershipData );

		} else if ($relation == "owner") {   //owner is owner
			$relationData = array('relation' => "visitor");
			$whereClause = array('account_address' => $walletAddress, 'relation' => "owner");
			$wpdb->update( 'alpn_wallet_relationships', $relationData, $whereClause );

			$relationData = array('relation' => "owner");
			$whereClause = array('account_address' => $walletAddress, 'owner_id' => $ownerId);
			$wpdb->update( 'alpn_wallet_relationships', $relationData, $whereClause );
		}

	alpn_log($walletAddress);
	alpn_log("DONE");

} else {
	alpn_log("No VITAL INFO");
}

} else {
	alpn_log("No VERIFICATION KEY");
}
?>
