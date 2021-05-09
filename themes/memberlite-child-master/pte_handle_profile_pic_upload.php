<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$qVars = $_POST;

if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die();
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die();
}

$source = isset($qVars['source']) ? $qVars['source'] : '';
$handle = isset($qVars['handle']) ? $qVars['handle'] : '';
$topicId = isset($qVars['topic_id']) ? pte_digits($qVars['topic_id']) : 0;
$topicSpecial = isset($qVars['topic_special']) ? $qVars['topic_special'] : 'topic';

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;


// alpn_log("Updating Profile Pic");
// alpn_log($userID);
// alpn_log($handle);
// alpn_log($topicId);
// alpn_log($topicSpecial);

if ($userID && $handle && $topicId) {
	try {

		if ($source == 'logo') {
			$rowData = array(
				"logo_handle" => $handle
			);
		} else {
			$rowData = array(
				"image_handle" => $handle
			);
			if ($topicSpecial == 'user') {  //replaces or adds metadata value for profile image into the WP system WP function
				update_user_meta( $userID, "tml_avatar",  $handle);
				update_user_meta( $userID, "pte_user_icon",  $handle);
				$data = array(
					"image_handle" => $handle,
					"owner_id" => $userID
				);
				pte_manage_cc_groups("update_user_image", $data);
			}
		}
		$whereClause['owner_id'] = $userID;
		$whereClause['id'] = $topicId;
		$wpdb->update( 'alpn_topics', $rowData, $whereClause );
		$pte_response = array("topic" => "pte_handle_profile_pic_upload_successful", "message" => "Upload Successful", "data" => $qVars);

		//$qVars['lq'] = $wpdb->last_query;
		//$qVars['le'] = $wpdb->last_error;

	} catch(Exception $e) {
		$pte_response = array("topic" => "pte_handle_file_update_exception", "message" => "File Update Exception", "data" => $qVars);
	}
} else {
	$pte_response = array("topic" => "pte_handle_profile_pic_data_missing", "message" => "Data missing", "data" => $qVars);
}

header('Content-Type: application/json');

echo json_encode($pte_response);

?>
