<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

//TODO Check logged in, etc

$html="";
$pVars = $_POST;

if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die();
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die();
}

$formId = isset($pVars['form_id']) ? $pVars['form_id'] : 0;

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

$results = array();
$replaceStrings = array();

if ($formId) {

	$results = $wpdb->get_results(
		$wpdb->prepare("SELECT * FROM alpn_topic_types tt WHERE tt.form_id = %s", $formId)
	 );

	if (isset($results[0])) {   //TODO Merge/generalize with topic_select
		$topicTypeData = $results[0];
		$topicTypeId = $topicTypeData->id;
		$schemaKey = $topicTypeData->schema_key;
		$typeKey = $topicTypeData->type_key;
		$icon = $topicTypeData->icon;
		$topicFriendlyName = htmlspecialchars($topicTypeData->name);
		$topicDescription = htmlspecialchars($topicTypeData->description);

		$topicTypeData->topic_type_meta = stripslashes($topicTypeData->topic_type_meta);
		$topicTypeMeta = json_decode($topicTypeData->topic_type_meta, true);
		$topicId = $topicTypeMeta['topic_name'];
		$fieldMap = $topicTypeMeta['field_map'];
		$topicTypeScope = isset($topicTypeMeta['topic_class']) ? $topicTypeMeta['topic_class'] : 'all';

		$typeKeyArray = explode("_", $typeKey);

		switch(count($typeKeyArray)) {
			case 1:
				$newString = $typeKeyArray[0];
				$typeKeyUidHtml = "";
			break;
			case 2:
				$newString = $typeKeyArray[0] . "_" . $typeKeyArray[1];
				$typeKeyUidHtml = "";
			break;
			case 3:
				$newString = $typeKeyArray[0] . "_" . $typeKeyArray[1];
				$typeKeyUid = $typeKeyArray[2];
				$typeKeyUidHtml = "<div class='pte_vault_row'><div class='pte_topic_type_property_title pte_vault_row_25'>Unique Id</div><div class='pte_topic_type_property_value pte_vault_row_75'>{$typeKeyUid}</div></div>";
			break;
		}

		$fieldTypeHtml = "<div class='pte_vault_row'><div class='pte_topic_type_property_title pte_vault_row_25'>Type</div><div class='pte_topic_type_property_value pte_vault_row_75'>{$newString}</div></div>";

		$ttAllSelected = ($topicTypeScope == 'topic') ? 'SELECTED' : "";
		$ttSomeSelected = ($topicTypeScope == 'link') ? 'SELECTED' : "";
		$ttTopicSelected = ($topicTypeScope == 'record') ? 'SELECTED' : "";
		$ttTopicListSelected = ($topicTypeScope == 'list') ? 'SELECTED' : "";

		$topicTypeItemsHtml = "";
		$requiredIconTrue = "<i class='far fa-asterisk' title='Field Required'></i>";
		$hiddenIconTrue = "<i class='far fa-eye-slash' title='Field Hidden'></i>";
		$hiddenIconFalse = "<i class='far fa-eye' title='Field Visible'></i>";
		$linkIcon = "<i class='far fa-link' title='Field is a Topic Link Type'></i>";

		foreach ($fieldMap as $key => $value) {
			if (isset($value['friendly']) && $key != "pte_meta") {
				$requiredItem = isset($value['required']) ? $value['required'] : "false";
				if ($requiredItem === 'true') {
						$requiredIcon = $requiredIconTrue;
				} else {
					$requiredIcon = '';
				}
				$hiddenIcon = isset($value['hidden']) && ($value['hidden'] == "true") ? $hiddenIconTrue : $hiddenIconFalse;
				$linkFieldType = isset($value['type']) && (substr($value['type'], 0, 5) == "core_") ? $linkIcon : "&nbsp;";
				$topicTypeItemsHtml .= "<li class='pte_topic_type_items_li' data-ptid=\"{$value['id']}\" data-ptname=\"{$value['name']}\"><div id='pte_topic_types_friendly_name' class='pte_vault_row_70 pte_vault_bold'>{$value['friendly']}</div><div id='pte_link_icon_container' class='pte_vault_row_10'>{$linkFieldType}</div><div id='pte_required_icon_container' class='pte_vault_row_10'>{$requiredIcon}</div><div id='pte_hidden_icon_container' class='pte_vault_row_10'>{$hiddenIcon}</div><div</li>";
			}
		}

		$topicProperties = "
				<ul id='pte_topic_type_properties_list'>
					{$topicTypeItemsHtml}
				</ul>
		";

		$adminId = 1;
		$coreTopics = "";
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT id, type_key, form_id, name, schema_key FROM alpn_topic_types WHERE owner_id = %d AND topic_state = 'user' AND special = 'topic'
											ORDER BY name",
											$userID)
		 );

		 foreach ($results as $key => $value) {
			 $coreTopics .= "<option data-pttk='{$value->type_key}' value='{$value->form_id}'>{$value->name}</option>";
		 }
		  $topicPropertiesAdd = "
		    <select id='alpn_select2_small_topic_properties' class='alpn_select2_small'>
		      <option value='' ></option>
					{$coreTopics}
		    </select>
		  ";

		$html .= "
			 <div id='pte_topic_type_property_editor' data-pttfid='{$formId}'>
			 <div class='pte_vault_row'>
					 <div class='pte_vault_row_35 pte_vault_text_xxl pte_vault_bold'>
						 <span class='alpn_name_field_label'>Topic Type Name</span>
						 <div class='pte_field_padding_right'><input id='alpn_name_field' placeholder='Give your Topic Type a unique name...' value='{$topicFriendlyName}'></div>
					 </div>
					 <div  class='pte_vault_row_65 pte_vault_text_xxl pte_field_padding_right pte_vault_bold pte_topic_type_info_sections'>
						 <span class='alpn_name_field_label'>Use As</span>
						 <div class='pte_topic_type_info_section_title'>
							 <select id='alpn_select2_small_topic_data_visibility' class='alpn_select2_small'>
							 	<option title='Main Topic' value='topic' {$ttAllSelected}>Main Topic</option>
							 	<option title='Link Topic' value='link' {$ttSomeSelected}>Topic Link</option>
								<option title='Form Fields' value='record' {$ttTopicSelected}>Topic Form</option>
								<option title='Tied to Record' value='list' {$ttTopicListSelected}>Topic List</option>
			 		    </select>
						 </div>
					 </div>
			 </div>
			 <div class='pte_vault_row pte_row_top_margin'>
					 <div class='pte_vault_row_35 pte_vault_text_xxl pte_vault_bold pte_field_padding_right'>
						 About
						 <textarea id='alpn_about_field' placeholder='Describe your Topic Type...' class='pte_field_padding_right'>{$topicDescription}</textarea>
					 </div>
					 <div class='pte_vault_row_65 pte_vault_text_xlarge pte_field_padding_right pte_topic_type_info_sections'>
						 <span class='alpn_name_field_label pte_vault_bold pte_vault_text_xxl'>Topic Type Properties</span>
						 <div class='pte_vault_row'><div class='pte_topic_type_property_title pte_vault_row_25'><a href='https://schema.org' target='_blank' rel='noopener noreferrer'>Schema.org</a></div><div class='pte_topic_type_property_value pte_vault_row_75'><a href='https://schema.org/{$schemaKey}' target='_blank' rel='noopener noreferrer' class='pte_topic_type_schema_key'>{$schemaKey}</a></div></div>
						 {$fieldTypeHtml}
						 {$typeKeyUidHtml}
						 <div class='pte_vault_row'><div class='pte_topic_type_property_title pte_vault_row_25'>Icon</div><div class='pte_topic_type_property_value pte_vault_row_75'><i class='{$icon} pte_topic_type_info_section_icon' title='Topic Icon'></i></div></div>
					 </div>
			 </div>
			 </div>
			 <div id='' class='pte_vault_row'>
				 <div class='pte_vault_row_35'>
					 <div class='pte_editor_title_text' style='margin-bottom: 5px;'>Data and Link Fields</div>
					 {$topicPropertiesAdd}
					 {$topicProperties}
				 </div>
				 <div id='pte_topic_type_field_property_editor' class='pte_vault_row_65'>
					 <div class='pte_editor_title_text'>Field Properties</div>
					 <div id='pte_topic_type_property_editor_proper' class=''>
					 	Select a field to customize its properties<br>or<br>Drag-and-drop to change its order
					 </div>
				 </div>
			 </div>
		";
		//scripts
		$html .= "
		<script>
			pte_selected_topic_type_object = {$topicTypeData->topic_type_meta};
			jQuery('#alpn_select2_small_topic_properties').select2( {
				theme: 'bootstrap',
				width: '100%',
				allowClear: true,
				closeOnSelect: false,
				placeholder: 'Add a Topic Link...'
			});
			jQuery('#alpn_select2_small_topic_properties').on('select2:select', function (e) {
				var data = e.params.data;
				pte_add_link_topic_type(data);
			});
			jQuery('#alpn_select2_small_topic_properties').on('select2:close', function (e) {
				jQuery('#alpn_select2_small_topic_properties').val('').trigger('change');
			});

			jQuery('#alpn_select2_small_topic_data_visibility').select2( {
				theme: 'bootstrap',
				width: '175px',
				minimumResultsForSearch: -1
			});
			jQuery('#alpn_select2_small_topic_data_visibility').on('select2:select', function (e) {
				var data = e.params.data;
				pte_change_topic_visibility(data);
			});
			jQuery('#pte_topic_type_properties_list').sortable({
				'direction': 'vertical',
				'onChoose': function (evt) {
					pte_handle_select_topic_type_row(evt.oldIndex, evt.item);
				},
				'onEnd': function (evt) {
					pte_handle_topic_type_order(evt);
				}
			});

			//TODO check for empty. Handle special characters

			jQuery('#alpn_name_field').donetyping(function(){
				var typeKey = jQuery('li.pte_topic_type_row_selected').data('ptname');
				var fieldNameValue = jQuery('#alpn_name_field').val();
				pte_selected_topic_type_object.topic_friendly_name = fieldNameValue;
				var tableCell = jQuery('div.alpn_topic_type_cell[data-uid={$formId}]').find('#pte_vault_name_content');
				tableCell.html(fieldNameValue);
				pte_save_topic_type_meta(true);
			});
			jQuery('#alpn_about_field').donetyping(function(){
				var fieldNameValue = jQuery('#alpn_about_field').val();
				pte_selected_topic_type_object.topic_description = fieldNameValue;
				var tableCell = jQuery('div.alpn_topic_type_cell[data-uid={$formId}]').find('#pte_vault_desc_content');
				tableCell.html(fieldNameValue);
				pte_save_topic_type_meta(true);
			});
		</script>
		";
	}

}


echo $html;

?>
