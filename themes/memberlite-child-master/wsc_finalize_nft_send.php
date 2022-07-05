<?php

include('/var/www/html/proteamedge/public/wp-load.php');

if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}
alpn_log("Finalizing NFT");

$qVars = $_POST;

$qVars['nft_ready_to_mint'] = true;

$processId = (isset($qVars['process_id']) && $qVars['process_id']) ? $qVars['process_id'] : false;

if ($processId) {
	$processData = array(
		'process_id' => $processId,
		'process_type_id' => "mint_nft",
		'process_data' => $qVars
	);
	pte_manage_interaction($processData);
}

pte_json_out(array("qvars" => $qVars));

?>
