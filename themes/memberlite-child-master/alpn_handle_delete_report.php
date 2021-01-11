<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;
$userMeta = get_user_meta( $userID, 'pte_user_network_id', true );

$qVars = $_POST;
$reportDomId = isset($qVars['report_dom_id']) ? $qVars['report_dom_id'] : '';

if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}

if ($reportDomId) {
	try {
			$whereClause = array(
				'dom_id' => $reportDomId
			);
			$wpdb->delete( 'alpn_templates', $whereClause );
			pte_json_out(array("dom_id" => $reportDomId));
	} catch (\Exception $e) {
			alpn_log($e);
			exit;
	}
}


?>
