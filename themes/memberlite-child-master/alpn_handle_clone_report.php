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
$reportDomId = isset($qVars['report_dom_id']) ? $qVars['report_dom_id'] : '';

if ($reportDomId) {

	try {

		$sourceTemplates = $wpdb->get_results(
			$wpdb->prepare("select * FROM alpn_templates WHERE dom_id = %s", $reportDomId)
		);

		if (isset($sourceTemplates[0])) {
			$domId = pte_get_short_id();
			$destinationTemplate = (array) $sourceTemplates[0];
			$now = date ("Y-m-d H:i:s", time());
			$destinationTemplate['dom_id'] = $domId;
			$destinationTemplate['created_date'] = $now;
			$destinationTemplate['modified_date'] = $now;
			$destinationTemplate['name'] = $destinationTemplate['name'] . " (copy)";
			unset($destinationTemplate['id']);
			unset($destinationTemplate['draw_id']);
		}
		$wpdb->insert( 'alpn_templates', $destinationTemplate);
		pte_json_out(array("destination_template" => $destinationTemplate, "dom_id" => $domId));
	} catch (\Exception $e) {
			alpn_log($e);
			exit;
	}
}

}
else
{
	echo $html = 'Not a valid request.';
	alpn_log($html);
	exit;
}

?>
