<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
include(PTE_ROOT_PATH . 'pte_get_interaction_ux.php');

$qVars = $_POST;
$verify = 0;
if(isset($qVars['security']) && !empty($qVars['security']))
	$verify = wp_verify_nonce( $qVars['security'], 'alpn_script' );
if($verify==1) {
$cellId = isset($qVars['cell_id']) ? $qVars['cell_id'] : '';

$userInfo = wp_get_current_user();
$ownerId = $userInfo->data->ID;
$ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true );
$results = array();

$expirationSelectHtml = pte_make_link_expiration_html("work_area");
$linkOptions = pte_make_link_options_html("work_area");
$html = "
		<div class='pte_vault_row pte_tiny_margin_bottom'>
			<div class='pte_vault_row_100'>
				<input id='link_interaction_about' class='pte_interaction_input' type='input' placeholder='About URL'>
			</div>
		</div>
		<div class='pte_vault_row pte_tiny_margin_bottom' style='margin-bottom: 4px !important;'>
			<div class='pte_vault_row_100'>
				{$linkOptions}
			</div>
		</div>
		<div class='pte_vault_row pte_tiny_margin_bottom'>
			<div class='pte_vault_row_100'>
				{$expirationSelectHtml}
			</div>
		</div>
		<div class='pte_vault_row pte_tiny_margin_bottom'>
			<div class='pte_vault_row_100'>
				<input id='link_interaction_password' class='pte_interaction_input' type='input' placeholder='No Passcode'>
			</div>
		</div>
";

$results = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT l.* from alpn_links l LEFT JOIN alpn_vault v ON l.vault_id = v.id WHERE v.owner_id = '%s' AND v.dom_id = '%s' ORDER BY l.last_update DESC;", $ownerId, $cellId)
);


$returnArray = array(
	"link_results" => $results,
	"widget_html" => $html
);

	pte_json_out($returnArray);
} 
else
{
	$html = 'Not a valid request.';
	echo $html;
	die;
}
?>
