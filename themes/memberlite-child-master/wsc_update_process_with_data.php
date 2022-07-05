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
$qVars = $_POST;
$processId = isset($qVars['process_id']) && $qVars['process_id'] ? $qVars['process_id'] : false;
$processTypeId = isset($qVars['process_type_id']) && $qVars['process_type_id'] ? $qVars['process_type_id'] : false;
$data = isset($qVars['data']) ? $qVars['data'] : false;

if ($processId && $data && $processTypeId) {
	$processData = array(
		'process_id' => $processId,
		'process_type_id' => $processTypeId,
		'process_data' => $data
	);
	pte_manage_interaction($processData);
}
pte_json_out(array("process_id" => $processId, "data" => $data));

?>
