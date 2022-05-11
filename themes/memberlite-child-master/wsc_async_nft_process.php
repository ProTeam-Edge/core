<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');


 alpn_log("Handle Background NFT Processing");


$verificationKey = (isset($_POST['verification_key']) && strlen($_POST['verification_key']) >= 20 && strlen($_POST['verification_key']) <= 22) ? $_POST['verification_key'] : false;

if ( !$verificationKey ) {
	alpn_log("ASYNC NFT PROCESS -- NO VERIFICATION KEY");
	exit;
}

$value = false;

$data = vit_get_kvp($verificationKey);

$walletAddress = $data['account_address'];
$walletData = array();

if (!$walletAddress) {
	alpn_log("ASYNC NFT PROCESS -- NO PUBLIC KEY");
	exit;
}

$results = $wpdb->get_results(
	$wpdb->prepare("SELECT state, JSON_EXTRACT(nft_queue, '$[0]') AS queue_element FROM alpn_wallet_meta WHERE account_address = %s", $walletAddress)
);

if (isset($results[0]) && $results[0]->queue_element) {
	 if ($results[0]->state == "processing") {
		 $walletData = $results[0]->queue_element;
		 $wpdb->query(
			 $wpdb->prepare("UPDATE alpn_wallet_meta SET nft_queue = JSON_REMOVE(nft_queue, '$[0]') WHERE account_address = %s", $walletAddress)
		 );
	 } else {
		 alpn_log("STOPPED NFT PROCESSING - STATE");
		 exit;
	 }
} else {
	alpn_log("QUEUE EMPTY - UPDATE STATE");
	$newWalletData = array('state' => 'ready');
	$whereClause = array('account_address' => $walletAddress);
	$wpdb->update( 'alpn_wallet_meta', $newWalletData, $whereClause );
	alpn_log("DONE - LIST EMPTY");
	exit;
}

$value = json_decode($walletData, true);

if (!$value) {
	exit;
}

wsc_process_single_nft($value);

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
} catch (Exception $e) {
	alpn_log($e);
}


?>
