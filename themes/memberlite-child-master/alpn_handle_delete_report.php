<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;
$userMeta = get_user_meta( $userID, 'pte_user_network_id', true );

$qVars = $_POST;
$reportDomId = isset($qVars['report_dom_id']) ? $qVars['report_dom_id'] : '';
$verify = 0;
if(isset($qVars['security']) && !empty($qVars['security']))
	$verify = wp_verify_nonce( $qVars['security'], 'alpn_script' );
if($verify==1) {
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
}
else
{
	echo $html = 'Not a valid request please hard refresh and try again.';
	alpn_log($html);
	exit;
}

?>
