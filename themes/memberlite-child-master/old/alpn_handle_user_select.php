<?php
include('../../../wp-blog-header.php');

//TODO Check logged in, etc

$siteUrl = get_site_url();

$qVars = $_GET;
$tableID = isset($qVars['tableId']) ? $qVars['tableId'] : 0;
$recordId = isset($qVars['uniqueRecId']) ? $qVars['uniqueRecId'] : 0;

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

$results = $wpdb->get_results(
	$wpdb->prepare("SELECT * FROM alpn_network WHERE dom_id = %s", $recordId) 
 );

$record = $results[0];

$html="";

$html .= "<div class='alpn_container_title_2'>
			<div class='alpn_container_2_left'>{$record->alpn_profile_last_name}, {$record->alpn_profile_first_name}</div>
			<div class='alpn_container_2_right'>Network</div>
		  </div>";
$html .= "
<div class='wp-block-kadence-rowlayout alignnone'>
	<div id='kt-layout-id_8efe5c-05' class='kt-row-layout-inner  kt-layout-id_8efe5c-05'>
		<div class='alpn_field_row kt-row-column-wrap kt-has-2-columns kt-gutter-default kt-v-gutter-default kt-row-valign-top kt-row-layout-right-golden kt-tab-layout-inherit kt-m-colapse-left-to-right kt-mobile-layout-row kt-custom-first-width-60 kt-custom-second-width-40'>
			<div class='wp-block-kadence-column inner-column-1 kadence-column_ac33b0-bc'>
				<div class='kt-inside-inner-col'>
					<table class='alpn_fields_table'>
						<tr>
							<td class='alpn_fields_col_key'><div class='alpn_view_field_name'>Title</div></td>
							<td class='alpn_fields_col_value'><div class='alpn_view_field_value'>{$record->alpn_profile_title}</div></td>
						</tr>					
						<tr>
							<td class='alpn_fields_col_key'><div class='alpn_view_field_name'>Business Type</div></td>
							<td class='alpn_fields_col_value'><div class='alpn_view_field_value'>{$record->alpn_profile_business_type}</div></td>
						</tr>							
						<tr>
							<td class='alpn_fields_col_key'><div class='alpn_view_field_name'>Business Name</div></td>
							<td class='alpn_fields_col_value'><div class='alpn_view_field_value'>{$record->alpn_profile_business_name}</div></td>
						</tr>
						<tr>
							<td class='alpn_fields_col_key'><div class='alpn_view_field_name'>Business Address<p class='alpn_map_it'><a href='https://maps.google.com/?q={$record->alpn_profile_address_1}, {$record->alpn_profile_address_2}, {$record->alpn_profile_city}, {$record->alpn_profile_state}, {$record->alpn_profile_postalcode}' target='_blank'>Map it</a></p></div></td>
							<td class='alpn_fields_col_value'>
								<div class='alpn_view_field_value'>{$record->alpn_profile_address_1}</div>
								<div class='alpn_view_field_value'>{$record->alpn_profile_address_2}</div>
								<div class='alpn_view_field_value'>{$record->alpn_profile_city}</div>
								<div class='alpn_view_field_value'>{$record->alpn_profile_state}</div>
								<div class='alpn_view_field_value'>{$record->alpn_profile_postalcode}</div>
							</td>							
						</tr>	
						<tr>
							<td class='alpn_fields_col_key'><div class='alpn_view_field_name'>Business Website</div></td>
							<td class='alpn_fields_col_value'><div class='alpn_view_field_value'><a href='{$record->alpn_profile_website_url}' target='_blank'>{$record->alpn_profile_website_url}</a></div></td>
						</tr>	
						<tr>
							<td class='alpn_fields_col_key'><div class='alpn_view_field_name'>Linked-In</div></td>
							<td class='alpn_fields_col_value'><div class='alpn_view_field_value'><a href='{$record->alpn_profile_linkedin_url}' target='_blank'>{$record->alpn_profile_linkedin_url}</a></div></td>
						</tr>		
						<tr>
							<td class='alpn_fields_col_key'><div class='alpn_view_field_name'>Email Address</div></td>
							<td class='alpn_fields_col_value'><div class='alpn_view_field_value'><a href='mailto:{$record->alpn_profile_primary_email}' target='_blank'>{$record->alpn_profile_primary_email} </a></div></td>
						</tr>								
						<tr>
							<td class='alpn_fields_col_key'><div class='alpn_view_field_name'>Mobile Phone</div></td>
							<td class='alpn_fields_col_value'><div class='alpn_view_field_value'><a href='tel:{$record->alpn_profile_cell_phone}'>{$record->alpn_profile_cell_phone}</a></div></td>
						</tr>							
						<tr>
							<td class='alpn_fields_col_key'><div class='alpn_view_field_name'>Office Phone</div></td>
							<td class='alpn_fields_col_value'><div class='alpn_view_field_value'><a href='tel:{$record->alpn_profile_desk_phone}'>{$record->alpn_profile_desk_phone}</a></div></td>
						</tr>							
						<tr>
							<td class='alpn_fields_col_key'><div class='alpn_view_field_name'>Fax</div></td>
							<td class='alpn_fields_col_value'><div class='alpn_view_field_value'>{$record->alpn_profile_fax_phone}</div></td>
						</tr>																		
					</table>					
				</div>
			</div>
			<div class='wp-block-kadence-column inner-column-2 kadence-column_136f0d-23'>
				<div class='kt-inside-inner-col'>
					<div class='alpn_action_controls'>
					
						<table class='alpn_actions_table'>
						<tr>
							<td class='alpn_actions_left'>
								<a href='#' class='btn btn-danger btn-lg' role='button'><i class='fa fa-lock'></i><br>Vault</a>							
							</td>
							<td class='alpn_actions_right'>
							</td>
						</tr>						
					
						<tr>
							<td class='alpn_actions_left'>
								<a href='#' onclick='alpn_mission_control(\"edit_network\", \"{$record->dom_id}\")' class='btn btn-danger btn-lg' role='button'><i class='fa fa-edit'></i><br>Edit</a>
							</td>
							<td class='alpn_actions_right'>
								<a href='#' class='btn btn-danger btn-lg' role='button'><i class='fa fa-trash-alt'></i><br>Delete</a>
							</td>
						</tr>						
					    </table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
";

echo $html;
?>	