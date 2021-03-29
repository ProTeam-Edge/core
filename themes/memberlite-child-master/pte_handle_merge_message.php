<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

//TODO add sharing and other met
$templateJson = array();
$targetTopicContent = array();
$contextTopicContent = array();
$templateHtml = '';
$templateTitle = '';
$contactIsImportant = false;
$nodeValue = "";

$qVars = $_POST;

alpn_log("MERGING");
alpn_log($qVars);

if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}

$userInfo = wp_get_current_user();
$ownerId = $userInfo->data->ID;
$ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true );

$templateId = isset($qVars['template_id']) ? pte_digits($qVars['template_id']) : 0;
$contextTopicId = isset($qVars['context_topic_id']) ? pte_digits($qVars['context_topic_id']) : 0;
$targetTopicId = isset($qVars['target_topic_id']) ? pte_digits($qVars['target_topic_id']) : 0;
$processId = isset($qVars['process_id']) ? $qVars['process_id'] : '';
$docType = isset($qVars['doc_type']) ? $qVars['doc_type'] : 'message';

$userId = get_current_user_id();

if ($templateId) {
	$templateData = $wpdb->get_results(
		$wpdb->prepare("SELECT id, json FROM alpn_templates WHERE id = %d", $templateId)
	);
	if (isset($templateData[0])) {
		$templateJson = json_decode(stripslashes($templateData[0]->json), true);
		$templateHtml = $templateJson['template_body'];
		$templateTitle = $templateJson['template_title'];
	}
}

$testArray = array();
if ($docType == 'message') {
	$contextData = $wpdb->get_results(
	 $wpdb->prepare("SELECT t.id, t.topic_type_id, t.name, t.topic_content, u.id AS important_contact FROM alpn_topics t LEFT JOIN alpn_user_lists u ON u.item_id = t.id AND u.owner_network_id = %d AND u.list_key = 'pte_important_network' WHERE t.id = %d OR t.id = %d", $ownerNetworkId, $contextTopicId, $targetTopicId)
	 );
	 foreach ($contextData as $key => $value) {
		  $contextTopicTypeId = $value->topic_type_id;
		  $contextTopicId = $value->id;
			$testArray[] = array(
				"contextTopicId" => $contextTopicId,
				"targetTopicId" => $targetTopicId
			);
			if ($contextTopicId == $targetTopicId) {
				$contextTopicContent[1] = json_decode($value->topic_content, true);
				$contactIsImportant = ($value->important_contact) ? true : false;
			} else {
				$contextTopicContent[$contextTopicTypeId] = json_decode($value->topic_content, true);
			}
	 }
} else { //TODO handle getting a lot more data for document merge HERE based on topic links
	$contextTopicContent = array();
}

if ($templateId) {
	$spanArray = array();
	$doc = new DOMDocument();
	$doc->loadHTML($templateHtml);

	$allSpans = $doc->getElementsByTagName('span');
	foreach ($allSpans as $key => $value) {
		$spanClass = $value->getAttribute("class");
		if ($spanClass == 'pte_field_token') {
			$topicTypeId = base_convert($value->getAttribute("data-ttid"), 36, 10);
			$fieldName = $value->getAttribute("data-fname");
			$value->nodeValue = $contextTopicContent[$topicTypeId][$fieldName];
		}
	}
	$nodeValue = $doc->getElementsByTagName('html')[0]->nodeValue;
}

pte_json_out(array(
	"message_body" => $nodeValue,
	"message_title" => $templateTitle,
	"contact_is_important" => $contactIsImportant,
	"template" => $templateJson
));

?>
