<?php

include('/var/www/html/proteamedge/public/wp-load.php');

$qVars = $_POST;
$extraSecurity = isset($qVars['security']) && $qVars['security'] ==  MORALIS_EXTRA_SECURITY ? true : false;
$processId = isset($qVars['process_id']) && $qVars['process_id'] ? $qVars['process_id'] : false;
$processTypeId = isset($qVars['process_type_id']) && $qVars['process_type_id'] ? $qVars['process_type_id'] : false;
$data = isset($qVars['data']) ? $qVars['data'] : false;

if ($extraSecurity && $processId && $data && $processTypeId) {
	$processData = array(
		'process_id' => $processId,
		'process_type_id' => $processTypeId,
		'process_data' => $data
	);
	pte_manage_interaction($processData);
}
pte_json_out(array("process_id" => $processId, "data" => $data));

?>
