<?php

include('/var/www/html/proteamedge/public/wp-load.php');

// if(!is_user_logged_in() ) {
// 	echo 'Not a valid request.';
// 	die;
// }

if(!check_ajax_referer('alpn_script', 'security', FALSE)) {
   echo 'Not a valid request.';
   die;
}

$qVars = $_POST;

$contractId = $qVars['contract_id'];
$queryFilter = $qVars['q'];

$queryArray = wsc_get_nft_query($qVars);  //Same as getNFTs
$fullQueryNoLimitAllContracts = $queryArray["full_query_no_limit_all_contracts"];
$contractDetails = wsc_get_contracts_for_query($fullQueryNoLimitAllContracts, $queryFilter, $contractId, $qVars['page'] + 1);

header('Content-Type: application/json');
echo json_encode(array("items" => $contractDetails['all_contracts'], "page" => $qVars['page'] + 1, "total_count" => $contractDetails['total_count']));

?>
