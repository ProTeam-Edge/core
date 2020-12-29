<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

//TODO Check logged in, etc
$siteUrl = get_site_url();
$rootUrl = PTE_ROOT_URL;
$ppCdnBase = PTE_IMAGES_ROOT_URL;
$html = "";
$pVars = $_POST;
$formId = isset($pVars['form_id']) ? $pVars['form_id'] : 0;

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

$results = array();
$replaceStrings = array();
$editorMode = "message";

if ($formId) {

	$results = $wpdb->get_results(
		$wpdb->prepare("SELECT name, type_key FROM alpn_topic_types WHERE form_id = %s", $formId)
	 );

	 if (isset($results[0])) {
		 		$typeKey = $results[0]->type_key;
				$topicName = $results[0]->name;
		 		$savedTemplateTable = do_shortcode("[wpdatatable id='10' var1='message' var2='{$typeKey}']");
		 		$savedTemplateTable = str_replace('table_1', 'table_reports', $savedTemplateTable);
		 		$savedTemplateTable = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $savedTemplateTable);
		 		$savedTemplateTable = str_replace('"iDisplayLength":5,', '"iDisplayLength":10,', $savedTemplateTable);

				$saveControls = "
					<div class='pte_vault_row pte_template_editor_list'>
			 				<div class='pte_vault_row_65'>
								<div id='pte_saved_reports' class='pte_report_saved_list pte_saved_reports_min_height'>{$savedTemplateTable}</div>
				 			</div>
			 				<div class='pte_vault_row_35'>
			 					&nbsp;
			 				</div>
			 		</div>
					";

		 		$availableTopicFields = pte_get_available_topic_fields($formId);

		 		$templateTypeSelector = "
		 			<select id='alpn_select2_template_type' class=''>
		 				<option value='message'>Messages</option>
		 				<option value='document'>Documents</option>
		 			</select>
		 		";

				$hideShowMessageTitle = "none";
				$savedString = "Saved Documents for {$topicName}";

				if ($editorMode == 'message') {
					$hideShowMessageTitle = "block";
					$savedString = "Saved Messages for {$topicName}";
				}

		 		$html .= "
		 		<div class='pte_vault_row'>
		 				<div class='pte_vault_row_25'>
							<div class='pte_topic_manager_editor_title'>Template Type</div>
		 					<div class='pte_template_list'>{$templateTypeSelector}</div>
		 				</div>
		 				<div class='pte_vault_row_75'>
		 					&nbsp;
		 				</div>
		 		</div>

				<div class='pte_template_editor_container'>
					<div class='pte_topic_manager_editor_title'>{$savedString}</div>
					{$saveControls}
					<input type='text' id='pte_template_title_field' placeholder='Message Title...' style='display: {$hideShowMessageTitle};'>
				</div>
				<textarea id='template_editor'></textarea>

		 		<script>
		 		var editorMode = '{$editorMode}';
		 		var tokens = {$availableTopicFields};

		 		if (editorMode == 'message') {
		 			var pte_toolbar = 'tokens';
		 			var savedTypeString = 'Edit Saved Message...';
					var pte_body_placeholder = 'Message Body...';
		 		} else {
		 			var pte_toolbar = 'styleselect | bold italic underline | numlist bullist | outdent indent | table | tokens';
		 			var savedTypeString = 'Edit Saved Document...'
					var pte_body_placeholder = 'Document...';
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

				console.log('Initializing...');

		 		tinymce.init({
					placeholder: pte_body_placeholder,
		 			selector: '#template_editor',
		 			toolbar: pte_toolbar,
		 			toolbar_mode: 'wrap',
		 			plugins: 'image table lists advlist noneditable',
		 			menubar: false,
		 			resize: false,
		 			statusbar: false,
		 			max_height: 550,
		 			min_height: 300,
					width: `80%`,
		 			noneditable_noneditable_class: 'pte_field_token',
		 			setup: (editor) => {
		 					editor.ui.registry.addMenuButton('tokens', {
		 							text: 'Field Token',
		 							tooltip: 'Insert field token',
		 							fetch: (callback) => {
		 									var items = tokens.map((token) => {
		 											return {
		 													type: 'menuitem',
		 													text: token.text,
		 													onAction: () => {
		 															editor.insertContent(`<span data-fid='`  + token.key + `' class='pte_field_token'>` + token.text + `</span>`);
		 													}
		 											}
		 									});
		 									callback(items);
		 							}
		 					});
		 			},
		 			content_style: `
		 					.pte_field_token {
		 							background-color: #e9f5f8;
		 							padding: 1px 0;
		 							color: #444;
		 							font-family: SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
		 							font-size: 0.9375em;
		 					}
		 			`,
		 		 });
				var fieldControls = ` \
	 				<input type='text' id='pte_template_name_field' placeholder='Template Name...' class=''> \
	 				<i id='pte_report_button_save' class='far fa-save quick_report_button' title='Save Report Settings Template' onclick='pte_handle_report_settings(\"save\");' style='font-size: 20px;'></i> \
	 				<i id='pte_report_button_clone' class='far fa-clone quick_report_button pte_extra_button_disabled' title='Duplicate Report Settings Template' onclick='pte_handle_report_settings(\"clone\");' ></i> \
	 				<i id='pte_report_button_delete' class='far fa-trash-alt quick_report_button pte_indent_right_margin pte_extra_button_disabled' title='Delete Report Settings Template' onclick='pte_handle_report_settings(\"delete\");'></i> \
	 			`;
	 			var alpn_report_table_settings = JSON.parse(jQuery('#pte_saved_reports :input')[2].value);
	 			wdtRenderDataTable(jQuery('#table_reports'), alpn_report_table_settings);
	 			alpn_prepare_search_field('#table_reports_filter');
	 			jQuery(fieldControls).insertBefore('#table_reports_filter');
	 			wpDataTables.table_reports.fnSettings().oLanguage.sZeroRecords = 'No Saved Templates';
	 			wpDataTables.table_reports.fnSettings().oLanguage.sEmptyTable = 'No Saved Templates';
	 			wpDataTables.table_reports.addOnDrawCallback( function(){
	 				alpn_handle_reports_table();
	 			})

		 		</script>
		 		";


	 }


	}
echo $html;

?>
