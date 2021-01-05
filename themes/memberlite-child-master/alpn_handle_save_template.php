<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;
$userMeta = get_user_meta( $userID, 'pte_user_network_id', true );

$qVars = $_POST;
$verify = 0;
if(isset($qVars['security']) && !empty($qVars['security']))
	$verify = wp_verify_nonce( $qVars['security'], 'alpn_script' );
	
	if($verify==1) {
$templateData = isset($qVars['template_data']) ? json_decode(stripslashes($qVars['template_data']), true) : array();
$templateName = pte_filename_sanitizer($templateData['template_name']);

$templateType = isset($templateData['template_type']) ? $templateData['template_type'] : 'message';
$templateDomId = isset($templateData['template_dom_id']) ? $templateData['template_dom_id'] : "";

if ($templateData && $userID) {
	try {
		$now = date ("Y-m-d H:i:s", time());
		if ($templateDomId) {
			$domId = $templateDomId;
			$templateUpdateData = array(
				'json' => $qVars['template_data'],
				'name' => $templateName,
				'modified_date' => $now
			);
			$whereClause = array(
				'dom_id' => $domId
			);
			$wpdb->update( 'alpn_templates', $templateUpdateData, $whereClause );
		} else {
			$domId = pte_get_short_id();
			$templateData = array(
				'template_type' => $templateData['template_type'],
				'created_date' => $now,
				'modified_date' => $now,
				'owner_id' => $userID,
				'type_key' => $templateData['topic_type_key'],
				'dom_id' => $domId,
				'name' => $templateName,
				'json' => $qVars['template_data']
			);
			$wpdb->insert( 'alpn_templates', $templateData );
		}
		pte_json_out(array("template_data" => $templateData, "dom_id" => $domId));
	} catch (\Exception $e) {
			alpn_log($e);
			exit;
	}
	
}
	}
	else
	{
		$html = 'Not a valid request please hard refresh and try again.';
		alpn_log($html);
		exit;
	}

?>
