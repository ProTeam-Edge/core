<?php
require 'vendor/autoload.php';
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$qVars = $_POST;

$verify = 0;
if(isset($qVars['security']) && !empty($qVars['security']))
	$verify = wp_verify_nonce( $qVars['security'], 'alpn_script' );
if($verify==1) {

$interactionData = isset($qVars['interaction_data']) ? json_decode(stripslashes($qVars['interaction_data']), true) : array();

alpn_log("handle widget interaction...");
alpn_log($interactionData);

$ownerNetworkId = get_user_meta( $interactionData['owner_id'], 'pte_user_network_id', true ); //Owners Topic ID

$data = array(
	'process_id' => $interactionData['process_id'],
	'process_type_id' =>  $interactionData['process_type_id'],
	'owner_network_id' => $ownerNetworkId,
	'owner_id' => $interactionData['owner_id'],
	'process_data' => $interactionData
);

pte_manage_interaction($data);

pte_json_out($data);
}else{
	$html = 'Not a valid request.';
	echo $html;
	die;
}
?>
