<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;
$userMeta = get_user_meta( $userID, 'pte_user_network_id', true );

$qVars = $_POST;
$reportData = isset($qVars['report_data']) ? json_decode(stripslashes($qVars['report_data']), true) : array();
$reportName = pte_filename_sanitizer($reportData['report_name']);

$reportTemplateDomId = isset($reportData['report_template_dom_id']) ? $reportData['report_template_dom_id'] : "";

if ($reportData) {
	try {
		$now = date ("Y-m-d H:i:s", time());
		if ($reportTemplateDomId) {
			$domId = $reportTemplateDomId;
			$templateData = array(
				'json' => $qVars['report_data'],
				'name' => $reportName,
				'modified_date' => $now
			);
			$whereClause = array(
				'dom_id' => $domId
			);
			$wpdb->update( 'alpn_templates', $templateData, $whereClause );
		} else {
			$domId = pte_get_short_id();
			$templateData = array(
				'template_type' => $reportData['template_type'],
				'created_date' => $now,
				'modified_date' => $now,
				'owner_id' => $userID,
				'topic_id' => $reportData['topic_id'],
				'topic_type_id' => $reportData['topic_type_id'],
				'type_key' => $reportData['topic_type_key'],
				'dom_id' => $domId,
				'name' => $reportName,
				'json' => $qVars['report_data']
			);
			$wpdb->insert( 'alpn_templates', $templateData );
		}
		pte_json_out(array("report_data" => $reportData, "dom_id" => $domId));
	} catch (\Exception $e) {
			alpn_log($e);
			exit;
	}

//	unlink($localStoreName);
}



?>
