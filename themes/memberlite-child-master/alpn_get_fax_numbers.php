<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$qVars = $_POST;
$areaCode = isset($qVars['area_code']) ? $qVars['area_code'] : '';

$userInfo = wp_get_current_user();
$ownerId = $userInfo->data->ID;
$ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true );
$results = array();

if ($ownerId && $areaCode) {

	  $query = array(
	    'npa' => $areaCode,
			'limit' => 12
	    );

	  $results = pte_call_documo('number_search', $query);

}
pte_json_out(json_decode($results, true));
?>
