<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$results = array();
$qVars = $_POST;
$phoneNumber = isset($qVars['phone_number']) ? $qVars['phone_number'] : '';
$userInfo = wp_get_current_user();
$ownerId = $userInfo->data->ID;
$ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true );
$topicList = "";
if ($ownerId && $phoneNumber) {
	$provisionNumber = pte_call_documo('number_provision', array('phone_number' => $phoneNumber));
	$numberData = json_decode($provisionNumber, true);
	if (isset($numberData[0])) {
			$numberData = $numberData[0];
			$numberUuid = $numberData['uuid'];

			$webhook = pte_call_documo('setup_webhook', array('pstn_uuid' => $numberUuid, 'pstn_number' => $phoneNumber));
			$webhookData = json_decode($webhook, true);

			if (isset($webhookData['uuid'])) {

				$numberData = array(
					"owner_id" => $ownerId,
					"owner_network_id" => $ownerNetworkId,
					"pstn_number" => $phoneNumber,
					"pstn_uuid" => $numberUuid
				);
				$wpdb->insert( 'alpn_pstn_numbers', $numberData );
				$results = $wpdb_readonly->get_results(
					$wpdb_readonly->prepare("SELECT id, name FROM alpn_topics WHERE owner_id = '%s' AND topic_type_id NOT IN ('4', '5') ORDER BY name ASC;", $ownerId)
				);
				$phoneNumberKey = substr($phoneNumber, 1);
				$topicList .= "<select id='alpn_select2_small_{$phoneNumberKey}' data-ptrid='{$phoneNumber}'>";
				$topicList .= "<option value='{$ownerNetworkId}'>Personal</option>";
				foreach ($results as $key => $value) {
					$id = $value->id;
					$name = $value->name;
					$topicList .= "<option value='{$id}'>{$name}</option>";
				}
				$topicList .= "</select>";

			} else {
				//TODO unable to create webhook
			}
		} else {
				//TODO unable to provision number

		}
		$results['topic_list'] = $topicList;
}
pte_json_out($results);
?>
