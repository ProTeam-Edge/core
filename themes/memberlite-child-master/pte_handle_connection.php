<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');
alpn_log("Handle Connection Start");

$qVars = $_POST;

if ( !is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}

if(!check_ajax_referer('alpn_script', 'security', FALSE)) {
   echo 'Not a valid request.';
   die;
}

$operation = isset($qVars['operation']) ? $qVars['operation'] : "";
$topicId = isset($qVars['topic_id']) ? $qVars['topic_id'] : 0;
$userInfo = wp_get_current_user();
$userId = $userInfo->data->ID;


$userMeta = get_user_meta( $userId, 'pte_user_network_id', true );
$topicData = $contactData = $contactTopic = $fieldMap = array();
$topicDomId = '';

if ($topicId) {
		$contactData = $wpdb->get_results(
			$wpdb->prepare("SELECT alt_id, owner_id, topic_content, image_handle FROM alpn_topics WHERE id = %d", $topicId)
		);
}

if (isset($contactData[0])) {
	$userTopicType = $wpdb->get_results(
		$wpdb->prepare("SELECT form_id, topic_type_meta FROM alpn_topic_types WHERE owner_id = %d AND special = 'contact'", $userId)
	);
	if (isset($userTopicType[0])) {
		$formId = $userTopicType[0]->form_id;

		$topicTypeMeta = json_decode($userTopicType[0]->topic_type_meta, true);
		$fieldMap = $topicTypeMeta["field_map"];
		$contactDetailsToForm = array();
		$contactDetails = json_decode($contactData[0]->topic_content, true);
		foreach ($contactDetails as $key => $value) {  //cleanup to topic data only, not extra
			if (substr($key, 0, 4) != 'pte_') {
			 $formKey = $fieldMap[$key]["id"];
			 $contactDetailsToForm[$formKey] = $value;
			}
		}
	}
	switch ($operation) {
		case "connect":
			$newTopicId = pte_create_topic($formId, $userId, $contactDetailsToForm, $contactData[0]->image_handle); //Does all the heavy lifting based on topic special

			$topicResults = $wpdb->get_results(
				$wpdb->prepare("SELECT id AS '0', name AS '1', connected_owner_id AS '2', about AS '3', connected_status AS '4', 'na' AS '5', connected_owner_dom_id AS '6' FROM alpn_member_connections WHERE id = %d", $newTopicId)
			);

			if (isset($topicResults[0])) {
				$topicData = $topicResults[0];
			}
			//tODO Sync the other person would be nice
		break;
		case "block":


		break;
		case "un_block":


		break;

	}
	$notifyData = array('contact_id' => $userId);
	wcl_notify_contact_of_request($notifyData);

}

pte_json_out($topicData);
?>
