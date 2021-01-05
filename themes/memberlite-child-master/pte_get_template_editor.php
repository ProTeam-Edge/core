<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

//TODO Check logged in, etc
$siteUrl = get_site_url();
$rootUrl = PTE_ROOT_URL;
$ppCdnBase = PTE_IMAGES_ROOT_URL;
$html = "";
$pVars = $_POST;
$verify = 0;
if(isset($pVars['security']) && !empty($pVars['security']))
	$verify = wp_verify_nonce( $pVars['security'], 'alpn_script' );
if($verify==1) {
$formId = isset($pVars['form_id']) ? $pVars['form_id'] : 0;
$editorMode = isset($pVars['editor_mode']) ? $pVars['editor_mode'] : 'message';

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

$results = array();
$replaceStrings = array();

if ($formId) {

	$results = $wpdb->get_results(
		$wpdb->prepare("SELECT name, type_key FROM alpn_topic_types WHERE form_id = %s", $formId)
	 );

	 if (isset($results[0])) {

		 		$typeKey = $results[0]->type_key;
				$topicName = $results[0]->name;

		 		$savedTemplateTable = do_shortcode("[wpdatatable id='10' var1='{$editorMode}' var2='{$typeKey}']");
		 		$savedTemplateTable = str_replace('table_1', 'table_reports', $savedTemplateTable);
		 		$savedTemplateTable = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $savedTemplateTable);
		 		$savedTemplateTable = str_replace('"iDisplayLength":5,', '"iDisplayLength":10,', $savedTemplateTable);

		 		$availableTopicFields = pte_get_available_topic_fields($formId, $editorMode);

				$hideShowMessageTitle = "none";
				$savedString = "Document Templates for Topic Type: {$topicName}";
				$editorWidth = "100%";
				$modeFriendly = "Document";
				$editorModeDocumentSelected = "SELECTED";
				$editorModeMessageSelected = "";

				if ($editorMode == 'message') {
					$hideShowMessageTitle = "block";
					$savedString = "Message Templates for Topic Type: {$topicName}";
					$editorWidth = "75%";
					$modeFriendly = "Message";
					$editorModeMessageSelected = "SELECTED";
					$editorModeDocumentSelected = "";
				}

		 		$templateTypeSelector = "
		 			<select id='alpn_select2_template_type' class=''>
		 				<option value='message' {$editorModeMessageSelected}>Message Templates</option>
		 				<option value='document' {$editorModeDocumentSelected}>Document Templates</option>
		 			</select>
		 		";

				$saveControls = "
					<div class='pte_vault_row pte_template_editor_list'>
			 				<div class='pte_vault_row_50'>
								<div id='pte_saved_reports' class='pte_report_saved_list pte_saved_reports_min_height'>{$savedTemplateTable}</div>
				 			</div>
			 				<div class='pte_vault_row_25 pte_vault_padding_left'>
			 					{$templateTypeSelector}
			 				</div>
							<div class='pte_vault_row_25'>
								&nbsp;
			 				</div>
			 		</div>
					";

		 		$html .= "
				<div id='pte_selected_template_meta' class='pte_template_editor_container' data-ttkey='{$typeKey}' data-ttfid='{$formId}'>
					<div class='pte_topic_manager_editor_title'>{$savedString}</div>
					{$saveControls}
					<input type='text' id='pte_template_title_field' placeholder='Message Title...' style='display: {$hideShowMessageTitle}; width: {$editorWidth};'>
				</div>
				<textarea id='template_editor'></textarea>
		 		<script>
		 		var editorMode = '{$editorMode}';
		 		var tokens = {$availableTopicFields};
		 		if (editorMode == 'message') {
		 			var pte_toolbar = 'tokens';
		 			var savedTypeString = 'Edit Saved Message...';
					var pte_body_placeholder = 'Message Body...';
					var minHeight = 300;
					var maxHeight = 550;
		 		} else {
		 			var pte_toolbar = 'bold italic underline | fontselect fontsizeselect | align | numlist bullist | table | tokens';
		 			var savedTypeString = 'Edit Saved Document...'
					var pte_body_placeholder = 'Document...';
					var minHeight = 550;
					var maxHeight = 800;
		 		}
		 		jQuery('#alpn_select2_template_type').select2( {
		 			theme: 'bootstrap',
		 			width: '100%',
					minimumResultsForSearch: -1
		 		});
		 		jQuery('#alpn_select2_template_type').on('select2:select', function (e) {
		 			var data = e.params.data;
		 			pte_change_template_type(data);
		 		});
		 		tinymce.init({
					placeholder: pte_body_placeholder,
		 			selector: '#template_editor',
		 			toolbar: pte_toolbar,
		 			toolbar_mode: 'wrap',
		 			plugins: 'image table lists advlist noneditable',
		 			menubar: false,
		 			resize: false,
		 			statusbar: false,
		 			max_height: maxHeight,
		 			min_height: minHeight,
					width: '{$editorWidth}',
		 			noneditable_noneditable_class: 'pte_field_token',
		 			setup: (editor) => {
		 					editor.ui.registry.addMenuButton('tokens', {
		 							text: 'Smart Token',
		 							tooltip: 'Insert Smart Token',
		 							fetch: (callback) => {
		 									var items = tokens.map((token) => {
		 											return {
		 													type: 'menuitem',
		 													text: token.text,
		 													onAction: () => {
		 															editor.insertContent(`<span data-ttid='`  + token.topic_type_id + `' data-fname='`  + token.field_name + `' class='pte_field_token'>` + token.text + `</span>`);
		 													}
		 											}
		 									});
		 									callback(items);
		 							}
		 					});
		 			},
		 			content_style: `
							body { font-family: Arial; }
							p { margin: 2px; }
		 					.pte_field_token {
		 							background-color: #e9f5f8;
		 							padding: 1px 0;
		 							color: #444;
		 							font-family: SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
		 					}
		 			`,
		 		 });
				var fieldControls = ` \
	 				<input type='text' id='pte_template_name_field' placeholder='Template Name...' class=''> \
	 				<i id='pte_report_button_save' class='far fa-save quick_report_button' title='Save Template' onclick='pte_handle_template_operation(\"save\");' style='font-size: 20px;'></i> \
	 				<i id='pte_report_button_clone' class='far fa-clone quick_report_button pte_extra_button_disabled' title='Duplicate Template' onclick='pte_handle_template_operation(\"clone\");' ></i> \
	 				<i id='pte_report_button_delete' class='far fa-trash-alt quick_report_button pte_indent_right_margin pte_extra_button_disabled' title='Delete Template' onclick='pte_handle_template_operation(\"delete\");'></i> \
	 			`;
		 			var alpn_report_table_settings = JSON.parse(jQuery('#pte_saved_reports :input')[2].value);
		 			wdtRenderDataTable(jQuery('#table_reports'), alpn_report_table_settings);
		 			alpn_prepare_search_field('#table_reports_filter');
		 			jQuery(fieldControls).insertBefore('#table_reports_filter');
		 			wpDataTables.table_reports.fnSettings().oLanguage.sZeroRecords = 'No Saved {$modeFriendly} Templates';
		 			wpDataTables.table_reports.fnSettings().oLanguage.sEmptyTable = 'No Saved {$modeFriendly} Templates';
		 			wpDataTables.table_reports.addOnDrawCallback( function(){
		 				alpn_handle_reports_table();
		 			})
		 		</script>
		 		";
	 }
	}
}
else
{
	$html = 'Not a valid request please hard refresh and try again.';
	alpn_log($html);
	
}
echo $html;

?>
