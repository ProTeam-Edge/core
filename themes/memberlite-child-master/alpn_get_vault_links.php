<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
include(PTE_ROOT_PATH . 'pte_get_interaction_ux.php');

$qVars = $_POST;

if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}

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
				<input id='link_interaction_about' class='pte_interaction_input' type='input' placeholder='About this xLink'>
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
		"SELECT l.* FROM alpn_vault v JOIN alpn_links l ON l.vault_id = v.id WHERE v.dom_id = %s ORDER BY l.last_update DESC;", $cellId)
);


$returnArray = array(
	"link_results" => $results,
	"widget_html" => $html
);

	pte_json_out($returnArray);

?>
