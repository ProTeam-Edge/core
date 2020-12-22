<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

//TODO Check logged in, etc
//TODO store HTML in MySql using htmlspecialchars()

$html="";
$pVars = $_POST;
$ownerId1 = isset($pVars['owner_id_1']) ? $pVars['owner_id_1'] : 0;
$ownerTopicId1 = isset($pVars['owner_topic_id_1']) ? $pVars['owner_topic_id_1'] : 0;
$ownerId2 = isset($pVars['owner_id_2']) ? $pVars['owner_id_2'] : 0;
$ownerTopicId2 = isset($pVars['owner_topic_id_2']) ? $pVars['owner_topic_id_2'] : 0;
$subjectToken = isset($pVars['subject_token']) ? $pVars['subject_token'] : '';

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;
$requestData = array(
	'owner_id' => $ownerId1,
	'topic_id' => $ownerTopicId1,
	'connected_id' => $ownerId2,
	'connection_link_topic_id' => $ownerTopicId2
);
pte_json_out(pte_manage_topic_link('add_edit_topic_bidirectional_link', $requestData, $subjectToken));
?>
