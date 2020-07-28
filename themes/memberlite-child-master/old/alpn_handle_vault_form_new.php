<?php
include('../../../wp-blog-header.php')

//TODO Check logged in, etc

$siteUrl = get_site_url();

$qVars = $_GET;
$recordId = isset($qVars['uniqueRecId']) ? $qVars['uniqueRecId'] : 0;
$alpn_selected_type = isset($qVars['alpn_selected_type']) ? $qVars['alpn_selected_type'] : false;
$html="";

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;


$html .= "<div class='alpn_vault_add_edit_inner'>
		  <div id='alpn_vault_forms' class='alpn_vault_forms'>
		 ";
$html .= do_shortcode("[wpdatatable id=6]"); //Form Search Table
$html .= "</div>
		  </div>
		 ";
$html = str_replace('table_1', 'table_form_search', $html);
$html = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $html);
$html = str_replace('"iDisplayLength":5,', '"iDisplayLength":4,', $html);		  

echo $html;

?>	