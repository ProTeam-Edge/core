<?php
include('../../../wp-blog-header.php');

$siteUrl = get_site_url();

$qVars = $_POST;
$topicContext = isset($qVars['topic_context']) ? $qVars['topic_context'] : '';
$topicNetworkId = isset($qVars['topic_id']) ? pte_digits($qVars['topic_id']) : '';
$topicNetworkName = isset($qVars['topic_name']) ? $qVars['topic_name'] : '';
$topicWpId = isset($qVars['topic_wp_id']) ? pte_digits($qVars['topic_wp_id']) : '';
$networkDomId = isset($qVars['network_dom_id']) ? pte_digits($qVars['network_dom_id']) : false;

$html = "";

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

//Add lots of checks: Logged in, etc.

//TODO Check for dupes and don't insert -- enforce here important with logged in,. ALSO in Edit

if ($topicNetworkId) {

	//TODO make this a user options
	$defaultMemberRights = array(
		'download' => '0',
		'share' => '0',
		'delete' => '0',
		'fax'  => '0',
		'email' => '0',
		'new' => '1',
		'edit' => '1',
		'chat' => '1',
		'action' => '1',
		'print' => '1',
		'transfer' => '1',
		);


	$defaultAccessLevel = "10";
	$defaultState = "10";

	$checked = array();
	foreach ($defaultMemberRights as $key => $value) {
		$checked[$key] = $value;
	}

	$memberRights = json_encode($defaultMemberRights);

	$proTeamData = array( //TODO start IA and store processID
		'owner_id' => $userID,
		'topic_id' => $topicContext,
		'proteam_member_id' => $topicNetworkId,
		'wp_id' => $topicWpId,
		'access_level' => $defaultAccessLevel,
		'state' => $defaultState,
		'member_rights' => $memberRights
	);
	$wpdb->insert( 'alpn_proteams', $proTeamData );

	$panelData = array(
		'proTeamRowId' => $wpdb->insert_id,
		'topicNetworkId' => $topicNetworkId,
		'topicNetworkName' => $topicNetworkName,
		'topicAccessLevel' => $defaultAccessLevel,
		'topicDomId' => $networkDomId,
		'state' => $defaultState,
		'checked' => $checked
	);
	$html = pte_make_rights_panel_view($panelData);

	$ownerNetworkId = get_user_meta( $userID, 'pte_user_network_id', true ); //Owners Topic ID
	$data = array(
		'process_id' => "",
		'process_type_id' => "proteam_invitation",
		'owner_network_id' => $ownerNetworkId,
		'owner_id' => $userID,
		'process_data' => array(
				'topic_id' => $topicContext,
				'interaction_network_id' => $topicNetworkId,
				'proteam_row_id' => $wpdb->insert_id
		)
	);
	pte_manage_interaction($data);  //start new interaction (empty processId)
}
echo $html;

?>
