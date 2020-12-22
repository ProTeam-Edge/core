<?php
include('../../../wp-blog-header.php');

//TODO Check logged in, etc

$siteUrl = get_site_url();

$html="";
$qVars = $_GET;
$recordId = isset($qVars['uniqueRecId']) ? $qVars['uniqueRecId'] : 0;
$alpn_selected_type = isset($qVars['alpn_selected_type']) ? $qVars['alpn_selected_type'] : '';

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

//Get topic information
$results = $wpdb->get_results(
	$wpdb->prepare("SELECT t.*, tt.id as topic_type_id, tt.form_id, tt.topic_type_meta FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id WHERE t.dom_id = %s", $recordId) 
 );
$topicData = $results[0];
$topicTypeId = $topicData->topic_type_id;
$topicDomId = $topicData->dom_id;
$topicContent = json_decode($topicData->topic_content, true);
$topicMeta = json_decode($topicData->topic_type_meta, true);
$fieldMap = array_flip($topicMeta['field_map']);

$businessTypes = get_categories( array(
	'hide_empty'    => false
));
$businessTypesList = array();
foreach ($businessTypes as $key => $value) {
	$businessTypesList[$value->term_id] = $value->name;
}

//map
$record = new stdClass();
foreach($topicContent as $key => $value){
	$record->{$fieldMap[$key]} = $value;	
}

$businessTypeFriendly = $businessTypesList[$record->alpn_profile_business_type];

if $context = ($alpn_selected_type == 'user') ? "Me" : "Network";

$html .= "<div class='alpn_container_title_2'>
			<div class='alpn_container_2_left'>{$topicData->name}</div>
			<div class='alpn_container_2_right'>{$context}</div>
		  </div>";
$html .= "
<div class='wp-block-kadence-rowlayout alignnone' style='clear: both;'>
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
							<td class='alpn_fields_col_value'><div class='alpn_view_field_value'>{$businessTypeFriendly}</div></td>
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
						</tr>";	

			  //show email for network but not users. TODO may want to show info warning that it is in login.

			if ($alpn_selected_type == 'network') {
				
			  $html .= "<tr>
						<td class='alpn_fields_col_key'><div class='alpn_view_field_name'>Email Address</div></td>
							<td class='alpn_fields_col_value'><div class='alpn_view_field_value'><a href='mailto:{$record->alpn_profile_primary_email}' target='_blank'>{$record->alpn_profile_primary_email} </a></div></td>
						</tr>";	
			}
   			  $html .= "<tr>
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
					<div class='alpn_network_button_panel'>
						<a onclick='alpn_mission_control(\"vault\", \"{$topicDomId}\")' class='btn btn-danger btn-lg' role='button'><i class='fa fa-lock'></i><br>Vault</a><br>
						<a onclick='alpn_mission_control(\"edit_network\", \"{$topicDomId}\")' class='btn btn-danger btn-lg' role='button'><i class='fa fa-edit'></i><br>Edit</a>
						<a class='btn btn-danger btn-lg' role='button'><i class='fa fa-trash-alt'></i><br>Delete</a
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
";

echo $html;

?>	