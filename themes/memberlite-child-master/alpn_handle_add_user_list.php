<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$qVars = $_POST;
$listKey = isset($qVars['list_key']) ? $qVars['list_key'] : '';
$itemId = isset($qVars['item_id']) ? $qVars['item_id'] : 0;

$html = "";

$userInfo = wp_get_current_user();
$ownerId = $userInfo->data->ID;
$ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true );


$listItem = array("error" => "missing data...");

if ($listKey && $itemId && $ownerNetworkId) {
		$listItem = array(
			'owner_id'=> $ownerId,
			'owner_network_id' => $ownerNetworkId,
			'list_key' => $listKey,
			'item_id' => $itemId
		);
		$wpdb->insert( 'alpn_user_lists', $listItem );

		$listItem['operation'] = "important_added";
		pte_update_interaction_weight($listKey, $listItem);

		$data = array(
			"sync_type" => "add_update_section",
			"sync_section" => "user_list_update",
			"sync_user_id" => $ownerId,
			"sync_payload" => $listItem
		);
		pte_manage_user_sync($data);

}
pte_json_out($listItem);
?>
