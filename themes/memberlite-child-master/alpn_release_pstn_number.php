<?php

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
$userID = $userInfo->data->ID;
$userMeta = get_user_meta( $userID, 'pte_user_network_id', true );

$qVars = $_POST;
$phoneNumber = isset($qVars['phone_number']) ? $qVars['phone_number'] : '';


if ($phoneNumber) {
	try {
		//get pstn_uuid from phone number
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT pstn_uuid from alpn_pstn_numbers WHERE pstn_number = %s", $phoneNumber)
		 );
		 if (isset($results[0])) {
			$pstnUuid = $results[0]->pstn_uuid;
			 //Release
			$webhook = pte_call_documo('number_release', array('pstn_uuid' => $pstnUuid));
			$webhookData = json_decode($webhook, true);

			$now = date ("Y-m-d H:i:s", time());
			$pstnData = array(
				"release_date" => $now
			);
			$whereClause = array(
				'pstn_uuid' => $pstnUuid
			);
			$wpdb->update( 'alpn_pstn_numbers', $pstnData, $whereClause );
		 } else {
			 //error pstn not found TODO
		 }
	} catch (\Exception $e) {
			alpn_log($e);
			exit;
	}
}


pte_json_out(array("phone_number" => $phoneNumber, "pstn_uuid" => $pstnUuid));

?>
