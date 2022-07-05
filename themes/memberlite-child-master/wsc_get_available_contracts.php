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

$chainId = isset($qVars['chain_id']) && $qVars['chain_id'] ? $qVars['chain_id'] : false;
$accountAddress = isset($qVars['account_address']) && $qVars['account_address'] ? $qVars['account_address'] : false;

if ($chainId && $accountAddress) {

	$contractListHtml = wsc_get_available_contracts_list($qVars);  //I know
	$returnData = array("contracts_list" => $contractListHtml);

} else {
	$returnData = array("error" => "missing required fields");
}

header('Content-Type: application/json');
echo json_encode($returnData);

?>
