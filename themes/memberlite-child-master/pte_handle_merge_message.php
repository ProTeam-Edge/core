<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

//TODO add sharing and other met
$templateJson = array();
$targetTopicContent = array();
$contextTopicContent = array();
$templateHtml = '';
$templateTitle = '';

$qVars = $_POST;
$templateId = isset($qVars['template_id']) ? $qVars['template_id'] : 0;
$contextTopicId = isset($qVars['context_topic_id']) ? $qVars['context_topic_id'] : 0;
$targetTopicId = isset($qVars['target_topic_id']) ? $qVars['target_topic_id'] : 0;
$docType = isset($qVars['doc_type']) ? $qVars['doc_type'] : 'message';

$userId = get_current_user_id();

$templateData = $wpdb->get_results(
	$wpdb->prepare("SELECT id, json FROM alpn_templates WHERE id = %d", $templateId)
);
if (isset($templateData[0])) {
	$templateJson = json_decode(stripslashes($templateData[0]->json), true);
	$templateHtml = $templateJson['template_body'];
	$templateTitle = $templateJson['template_title'];
}

$testArray = array();
if ($docType == 'message') {
	$contextData = $wpdb->get_results(
	 $wpdb->prepare("SELECT id, topic_type_id, name, topic_content FROM alpn_topics WHERE id = %d OR id = %d", $contextTopicId, $targetTopicId)
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
			} else {
				$contextTopicContent[$contextTopicTypeId] = json_decode($value->topic_content, true);
			}
	 }
} else { //TODO handle getting a lot more data for document merge HERE based on topic links
	$contextTopicContent = array();
}

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

pte_json_out(array(
	"message_body" => $doc->getElementsByTagName('html')[0]->nodeValue,
	"message_title" => $templateTitle,
	"template" => $templateJson
));

?>
