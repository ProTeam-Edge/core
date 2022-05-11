<?php

/*

Should really merge with wsc_handle_pk_add.php

*/

include('/var/www/html/proteamedge/public/wp-blog-header.php');

if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}

$userInfo = wp_get_current_user();
$userId = $userInfo->data->ID;

$qVars = $_POST;

$newWeb3Address = $qVars['new_address'];
$firstTime = true;
$accountProcessed = false;

$accountResult = $wpdb->get_results(
	$wpdb->prepare("SELECT id from alpn_wallet_meta WHERE account_address = %s", $newWeb3Address)
 );

 if (isset($accountResult[0])) {
	 $accountProcessed = true;
	 $accountResultUser = $wpdb->get_results( //Letting database handle
		$wpdb->prepare("SELECT id from alpn_wallet_relationships WHERE account_address = %s AND owner_id = %d", $newWeb3Address, $userId)
	 );
	 if (isset($accountResultUser[0])) {
		 $firstTime = false;
	 }
 }

	try {
		$data = array(
			"account_address" => $newWeb3Address,
			"owner_id" => $userId,
			"relation" => "owner",
			"first_time" => $firstTime,
			"account_processed" => $accountProcessed
		);
		$verificationKey = pte_get_short_id();
		$params = array(
			"verification_key" => $verificationKey
		);
		vit_store_kvp($verificationKey, $data);
		pte_async_job(PTE_ROOT_URL . "wsc_async_wallet_process.php", array('verification_key' => $verificationKey));

	} catch (Exception $e) {
		alpn_log($e);
	}

pte_json_out(array("web3_address" => $newWeb3Address, "first_time" => $firstTime, "account_processed" => $accountProcessed));

?>
