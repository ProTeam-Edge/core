<?php
include('../../../wp-blog-header.php');

$siteUrl = get_site_url();
$html="";
$qVars = $_POST;

if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}
$returnDetails = isset($qVars['return_details']) ? json_decode(stripslashes($qVars['return_details']), true) : array();

$rightsCheckData = array(
  "topic_dom_id" => $uniqueRecId
);
if (!pte_user_rights_check("topic_dom_edit", $rightsCheckData)) {
  // $html = "
  // <div class='pte_topic_error_message'>
  //    You do not have permission to edit this Topic.
  // </div>";
  //echo $html;
  //exit;
}

$userInfo = wp_get_current_user();
$userId = $userInfo->data->ID;
$userEmail = $userInfo->data->user_email;

//pp($userInfo->data);

$connectionEditor = "";

$connectionEditor .= "<div id='pte_connection_manager_outer'>";

$connectionEditor .= do_shortcode("[wpdatatable id=13]");

$connectionEditor = str_replace('table_1', 'table_connections', $connectionEditor);
$connectionEditor = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $connectionEditor);
$connectionEditor = str_replace('"iDisplayLength":5,', '"iDisplayLength":10,', $connectionEditor);

$connectionEditor .= "</div>";

	$html .= "
						<div class='outer_button_line'>
							<div class='pte_vault_row_35'>
							</div>
							<div class='pte_vault_row_65'>
							</div>
							<div id='alpn_message_area' class='alpn_message_area' onclick='pte_clear_message();'></div>
					</div>
			";

	$html .= "
						<div class='alpn_container_title_2'>
							<div id='pte_topic_form_title_view'>
								<i class='far fa-user-circle pte_title_icon_margin_right'></i>Connections
							</div>
							<div id='pte_topic_form_title_view' class='pte_vault_right'>
							<div class='pte_title_topic_icon_container'></div>
							</div>
						</div>
				";

$html .= "
						<div id='pte_editor_container' class='pte_vault_row' >
							<div id='pte_topic_form_edit_view_left'>
								{$connectionEditor}
							</div>
							<div id='pte_topic_form_edit_view_right'>
							</div>
						 </div>
						";
echo $html;

?>
