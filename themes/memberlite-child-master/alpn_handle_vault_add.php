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
$html .= "			  <div class='pte_vault_row'>
										<div class='pte_vault_row_67 pte_vault_text_xlarge pte_vault_bold'>
											<span id='alpn_name_field_label'>Name</span>
											<div class='pte_field_padding_right'><input id='alpn_name_field' placeholder='From Upload'></div>
										</div>
										<div class='pte_vault_row_33 pte_vault_text_xlarge pte_field_padding_right'>
											<span class='pte_vault_bold'>Access</span>
											<select id='alpn_selector_sharing' class='alpn_selector_sharing'>
												<option value='40'>Private</option>
												<option value='10'>General</option>
												<option value='20'>Restricted</option>
											</select>
										</div>
								</div>
								<div class='pte_vault_row pte_row_top_margin'>
										<div class='pte_vault_row_67 pte_vault_text_xlarge pte_vault_bold pte_field_padding_right'>
											Description
											<textarea id='alpn_about_field' placeholder='Describe your vault entry so it can be easily found...' class='pte_field_padding_right'></textarea>
										</div>
										<div class='pte_vault_row_33 pte_vault_text_xlarge pte_field_padding_right'>
										</div>
								</div>
					";
$html = str_replace('table_1', 'table_form_search', $html);
$html = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $html);

echo $html;

?>
