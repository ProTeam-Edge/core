<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}
$qVars = $_POST;
$listKey = isset($qVars['list_key']) ? $qVars['list_key'] : '';
$itemId	 = isset($qVars['item_id']) ? pte_digits($qVars['item_id']) : false;

$userInfo = wp_get_current_user();
$ownerId = $userInfo->data->ID;
$ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true );

$listItem = array(
	'owner_network_id' => $ownerNetworkId,
	'list_key' => $listKey,
	'item_id' => $itemId
);

$deleteResults = $wpdb->delete('alpn_user_lists', $listItem);

$listItem['operation'] = "important_removed";
pte_update_interaction_weight($listKey, $listItem);

$data = array(
	"sync_type" => "add_update_section",
	"sync_section" => "user_list_update",
	"sync_user_id" => $ownerId,
	"sync_payload" => $listItem
);
pte_manage_user_sync($data);

pte_json_out($data);

?>
