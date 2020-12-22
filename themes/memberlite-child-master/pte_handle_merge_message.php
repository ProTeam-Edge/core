<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

//TODO add sharing and other met

$qVars = $_POST;
$templateId = isset($qVars['template_id']) ? $qVars['template_id'] : '';
$topicId = isset($qVars['topic_id']) ? $qVars['topic_id'] : '';
$networkContactId = isset($qVars['network_contact_id']) ? $qVars['network_contact_id'] : '';

$userId = get_current_user_id();

$templates = $wpdb->get_results(
	$wpdb->prepare("SELECT id, message_title, message_body FROM alpn_message_templates WHERE owner_id = '%s' AND id = '%s'", $userId, $templateId)
 );

$topicTypeId = '5';
$topicContent = $wpdb->get_results(
	$wpdb->prepare("SELECT topic_content FROM alpn_topics WHERE owner_id = '%s' AND topic_type_id = '%s'", $userId, $topicTypeId)
 );
$topicContentDec = json_decode($topicContent[0]->topic_content);

$networkContent = $wpdb->get_results(
	$wpdb->prepare("SELECT t.topic_content FROM alpn_proteams p LEFT JOIN alpn_topics t ON p.proteam_member_id = t.id WHERE p.id = '%s' AND p.owner_id = '%s'", $pteId, $userId)
 );
$networkContentDetails = json_decode($networkContent[0]->topic_content);

$topic = $wpdb->get_results(
	$wpdb->prepare("SELECT t.*, tt.id AS topic_type_id, tt.form_id, tt.name AS topic_name, tt.icon, tt.topic_type_meta, tt.html_template FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id WHERE t.id = %s", $topicId)
 );
$topicDetails = $topic[0];

$tokens = array("first_name" => $networkContentDetails->alpn_profile_first_name,
				"topic_type" => $topicDetails->topic_name,
				"my_first_name" => $topicContentDec->alpn_profile_first_name,
				"topic_name" => $topicDetails->name
			   );

$message_title = $templates[0]->message_title;
$message_body = $templates[0]->message_body;

foreach ($tokens as $key => $value ) {
	$message_title = str_replace("{-$key-}", $value, $message_title);
	$message_body = str_replace("{-$key-}", $value, $message_body);
}

$results['template_id'] = $templateId;
$results['topic_id'] = $topicId;
$results['user_id'] = $userId;
$results['message_title'] = $message_title;
$results['message_body'] = $message_body;
$results['topic_content'] = $topicContentDec;
$results['topic_details'] = $topicDetails;
$results['pte_id'] = $pteId;
$results['network_content'] = $networkContentDetails;

header('Content-Type: application/json');
echo json_encode($results);

?>
