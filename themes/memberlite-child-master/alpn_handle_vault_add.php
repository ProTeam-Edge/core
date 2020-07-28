<?php
include('../../../wp-blog-header.php');

//TODO Check logged in, etc

$siteUrl = get_site_url();

$qVars = $_GET;
$recordId = isset($qVars['uniqueRecId']) ? $qVars['uniqueRecId'] : 0;
$alpn_selected_type = isset($qVars['alpn_selected_type']) ? $qVars['alpn_selected_type'] : false;

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

$html="";

$html .= "<div class='wp-block-columns alpn_vault_add_edit_inner'>

		  <div class='wp-block-column alpn_vault_about' style='flex-basis: 50% !important;'>
			
			<div>Description</div>

			<textarea id='alpn_about_field' placeholder='Describe your vault entry so it can be easily found...'></textarea>
			
			<div id='pte_add_sharing_line'>
				<div id='pte_add_sharing_line_left'>			
					<div style='margin-top: 5px;'>Access</div>	 			
					<select id='alpn_selector_sharing' class='alpn_selector_sharing'>
						<option value='40'>Private</option>
						<option value='10'>General</option>
						<option value='20'>Restricted</option>
					</select>
				</div>
				<div id='pte_add_sharing_line_right'>			
					<button id='alpn_vault_save_info' class='btn btn-danger btn-work-area' onclick='pte_handle_workarea_button(\"update_info\")'>Save Info</button>
				</div>
			</div>
			<div style='clear: both;'></div>
		   </div>
		   
		  <div id='alpn_vault_forms' class='wp-block-column alpn_vault_forms' style='flex-basis: 50% !important;'>
		  	
		  </div>		   
		  </div>";
$html = str_replace('table_1', 'table_form_search', $html);
$html = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $html);

echo $html;

?>