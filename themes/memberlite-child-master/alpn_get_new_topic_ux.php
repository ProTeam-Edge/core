<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

//TODO Check logged in, etc
if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}
$html="";
$pVars = $_POST;
//$formId = isset($pVars['form_id']) ? $pVars['form_id'] : 0;

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;
$userTopicId = get_user_meta( $userID, 'pte_user_network_id', true );

$results = array();
$replaceStrings = array();

if ($userTopicId) {

	$results = $wpdb->get_results($wpdb->prepare("SELECT t.id, t.owner_id, t.topic_type_id, t.topic_content FROM alpn_topics t WHERE t.id = %d", $userTopicId));
	if (isset($results[0])) {

		$topicData = $results[0];
		$topicContent = json_decode($topicData->topic_content, true);

		$occupation = isset($topicContent['person_hasoccupation_occupation_occupationalcategory']) ? $topicContent['person_hasoccupation_occupation_occupationalcategory'] : 'None';



		$html .= "
					<div>
						YO
					</div>
					<script>
					</script>
		";
	 }
}
echo $html;

?>
