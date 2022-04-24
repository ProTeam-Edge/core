<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
alpn_log("Handle Background Wallet Processing");

//0xa93cfddb2d48df5e7492a82ecc57a554d17f0c0c  -- Pat
//0x5B93FF82faaF241c15997ea3975419DDDd8362c5  -- Coopatroopha
//0x267be1c1d684f78cb4f6a176c4911b741e4ffdc0  -- Jay Vermont
//0x3B3525F60eeea4a1eF554df5425912c2a532875D  -- Dame
//0xEB889d3FFD7170cD1E25A3B2cB0D522b8EAA5CB7  -- Cantino
//0xFC5446EfE679f109f2772e45EA623CaA63791d5e  -- Rebekah Bastian
//0x6002cA2e11B8e8c0F1F09c67F551B209eb51A0E4  -- Keith Axline
//0x1dF428833f2C9FB1eF098754e5D710432450d706  -- OxJoshua
//0x7221B104fba7701084759FD25FacA19Ac6300855  -- John Paller
//0x7B6f2f3032664691586AEDfeadBd60D6F5d88DA6  -- Jaime Schmidt
//0x14977b0dBE7e155F9907effECbB70c9b7a05e737  -- Nik Kalyani
//0xe11BFCBDd43745d4Aa6f4f18E24aD24f4623af04  -- Chris Dixon
//0xE0036fb4B5A3B232aCfC01fEc3bD1D787a93da75  -- Snoop
//0x5ea9681C3Ab9B5739810F8b91aE65EC47de62119  -- Gary Vee
//0xB2Ebc9b3a788aFB1E942eD65B59E9E49A1eE500D  -- Nader Dabit
//0x7a253BD170E3A8c6088aCdC912DC34f945F33D4E  -- Shady Holdings
//0x3C002Aee36010f7a92535C0aA7F0a93CEd4ee4e0  -- Crypto Neon Girls

$verificationKey = (isset($_POST['verification_key']) && strlen($_POST['verification_key']) >= 20 && strlen($_POST['verification_key']) <= 22) ? $_POST['verification_key'] : false;

if ( $verificationKey ) {

	$data = vit_get_kvp($verificationKey);

	if (isset($data['account_address']) && isset($data['owner_id'])) {

		$walletAddress = isset($data['account_address']) && $data['account_address'] ? $data['account_address'] : false;
		$ownerId = isset($data['owner_id']) && $data['owner_id'] ? $data['owner_id'] : false;
		$relation = isset($data['relation']) && $data['relation'] ? $data['relation'] : "visitor";
		$accountProcessed = isset($data['account_processed']) && $data['account_processed'] ? $data['account_processed'] : false;
		$firstTime = isset($data['first_time']) && $data['first_time']? $data['first_time'] : false;

		if (!$accountProcessed) {

			alpn_log("PROCESS Public Key for NFTs");
			//Put in processing mode right away so multiple users don't start this.
			$ensName = json_decode(wsc_resolve_ens($walletAddress))->name;
			$walletData = array (
				"account_address" => $walletAddress,
				"friendly_name" => $ensName,
				"ens_address" => $ensName,
				"state" => "processing",
				"meta" => "{}"
			);
			$wpdb->insert( 'alpn_wallet_meta', $walletData );

			//Get all NFTs  -- takes time and fails om large account. FIX
			$allNfts = wsc_get_all_member_nfts($walletAddress);

			if (count($allNfts)) {
				$state = "processing";
			} else {
				$state = "ready";
			}
			$walletData['nft_queue'] = json_encode($allNfts);
			$walletData['state'] = $state;
			$whereClause['account_address'] = $walletAddress;
			$wpdb->update( 'alpn_wallet_meta', $walletData, $whereClause );

			$data = array(
				"account_address" => $walletAddress
			);

			$verificationKey = pte_get_short_id();
			$params = array(
				"verification_key" => $verificationKey
			);
			vit_store_kvp($verificationKey, $data);
			try {
				pte_async_job(PTE_ROOT_URL . "wsc_async_nft_process.php", array('verification_key' => $verificationKey));
				alpn_log("NFT SYNC STARTED");
			} catch (Exception $e) {
				alpn_log($e);
			}

			//TODO START TRACKING ACCOUNT CHANGES FOR THIS ACCOUNT
		}

		if ($firstTime) {

			$ownershipData = array (
				"account_address" => $walletAddress,
				"relation" => $relation,
				"owner_id" => $ownerId
			);
			$wpdb->insert( 'alpn_wallet_relationships', $ownershipData );

		} else if ($relation == "owner") {
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
