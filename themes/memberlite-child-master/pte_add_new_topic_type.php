<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

//TODO Check logged in, etc
//TODO store HTML in MySql using htmlspecialchars()

$html="";
$pVars = $_POST;
$sourceTopicTypeId = isset($pVars['source_topic_type_id']) ? $pVars['source_topic_type_id'] : 0;

$userInfo = wp_get_current_user();
$ownerId = $userInfo->data->ID;

$newTopicTypeId = pte_topic_type_copy($sourceTopicTypeId, $ownerId);

//TODO because we need this to match wpforms.
$formId = 0;
$results = $wpdb->get_results($wpdb->prepare("SELECT form_id FROM alpn_topic_types WHERE id = %d", $newTopicTypeId));
if (isset($results[0])) {
	$formId = $results[0]->form_id;
}

$data = array(
	"table_type" => "topic_types",
	"per_page" => 5,
	"topic_type_id" => $newTopicTypeId,
	"owner_id" => $ownerId
);
$pageNumber = pte_get_page_number(array("data" => $data));

$returnResult = array(
	"new_topic_type_id" => $newTopicTypeId,
	"page_number" => $pageNumber,
	"form_id" => $formId
);


pte_json_out($returnResult);
?>
