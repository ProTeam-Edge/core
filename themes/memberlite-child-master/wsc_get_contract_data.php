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

$type = isset($qVars['type']) && $qVars['type'] ? $qVars['type'] : false;
$contractTemplateId = isset($qVars['nft_contract_template_id']) && $qVars['nft_contract_template_id'] ? $qVars['nft_contract_template_id'] : false;
$contractMethodName = isset($qVars['method_name']) && $qVars['method_name'] ? $qVars['method_name'] : false;

if ($type && $contractTemplateId) {
	if ($type == "contract_factory"){
		try {
			$results = $wpdb->get_results(
				$wpdb->prepare("SELECT contract_abi, contract_bytecode from alpn_smart_contract_templates WHERE id = %d", $contractTemplateId)
			 );
			 if (isset($results[0])) {
				 $returnData = array("success" => true, "contract_data" => $results[0]);
			 } else {
				 $returnData = array("success" => false);
			 }
		} catch (Exception $error) {
			$returnData = array("error" => "db_error");
		}
	} else if ($type == "contract_call") {
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT method_abi FROM alpn_smart_contract_methods WHERE id = %d AND method_name = %s", $contractTemplateId, $contractMethodName)
		 );
		 if (isset($results[0])) {
			 $returnData = array("success" => true, "contract_data" => $results[0]);
		 } else {
			 $returnData = array("success" => false);
		 }
	}
} else {
	$returnData = array("error" => "missing required fields");
}

header('Content-Type: application/json');
echo json_encode($returnData);

?>
