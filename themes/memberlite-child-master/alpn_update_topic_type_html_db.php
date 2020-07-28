<?php
include('../../../wp-blog-header.php');

$qVars = $_POST;
$id = isset($qVars['id']) ? $qVars['id'] : '';
$html_template = isset($qVars['html_template']) ? $qVars['html_template'] : '';

//$userId = get_current_user_id();

if ($id == '' || $html_template == ''){
	$results = array('alpn_error' => 'No data.');
} else {
	
try {	

	$results = $wpdb->get_results(
		$wpdb->prepare("UPDATE alpn_topic_types SET html_template = '%s' WHERE id = '%s'", $html_template, $id)
	);	
	

} catch (Exception $e) { 

}	
	
	

}


header('Content-Type: application/json');
echo json_encode($results);

?>	