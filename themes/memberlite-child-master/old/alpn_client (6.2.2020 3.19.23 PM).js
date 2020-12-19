//ALPN Globals

alpn_oldSelectedId = "";
alpn_oldVaultSelectedId = "";
alpn_oldFormSelectedRow = {};
pte_old_proteam_selected_id = "";
pte_selected_topic_tab = '';
pte_selected_topic_tab_content = '';

pte_chat_window_open = false;

alpn_activity_table_id = '';
alpn_set_vault_to_first_row = false;

alpn_waiting_indicator_id='';
alpn_oldVaultFormSelectedId = {};

pte_user_timezone_offset = new Date().getTimezoneOffset() * -60;

alpn_add_edit_events_registered = false;

alpn_mode = 'topic';

pte_uppy_vault_instances = [];
pte_uppy_instance_id = '';

//TODO LOTS MORE SUPPORTED IMAGE FROM TYPES.  ALSO Missed doc mimetypes? PPT?

pte_supported_types_map = {
	'image/jpeg': 'Image - JPEG',
	'image/jpg': 'Image - JPG',
	'image/png': 'Image - PNG',
	'image/xvg+xml': 'Image - SVG',
	'image/application/illustrator': 'Illustrator - AI',
	'application/postscript': 'Illustrator - AI',
	'application/pdf': 'Document - PDF',
	'text/plain': 'Text - Plain',
	'text/html': 'Text - HTML',
	'text/rtf': 'Text - RTF',
	'text/richtext': 'Text - RTF',
	'application/rtf': 'Text - RTF',
	'application/x-rtf': 'Text - RTF',
	'application/msword': 'Word - DOC',
	'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'Word - DOCX',
	'application/vnd.ms-powerpoint': 'PowerPoint - PPT',
	'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'PowerPoint - PPTX',
	'application/vnd.ms-excel': 'Excel - XLS',
	'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'Excel - XLSX',
	'application/vnd.oasis.opendocument.text': 'Open Text - ODT',
	'application/vnd.oasis.opendocument.presentation': 'Open Presentation - ODP',
	'application/vnd.oasis.opendocument.spreadsheet': 'Open Spreadsheet - ODS',
	'application/zip': 'Archive - ZIP',
	'application/x-zip-compressed': 'Archive - ZIP'
}

function pte_UUID() { // Public Domain/MIT
    var d = new Date().getTime();//Timestamp
    var d2 = (performance && performance.now && (performance.now()*1000)) || 0;//Time in microseconds since page-load or 0 if unsupported
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = Math.random() * 16;//random number between 0 and 16
        if(d > 0){//Use timestamp until depleted
            r = (d + r)%16 | 0;
            d = Math.floor(d/16);
        } else {//Use microseconds since page-load if supported
            r = (d2 + r)%16 | 0;
            d2 = Math.floor(d2/16);
        }
        return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
    });
}

function pte_extra_control(operation, key, tabTypeId, topicId, formId, uniqueFieldId){

		if (typeof pte_active_tabs !== 'undefined') {
			if (typeof pte_active_tabs[key] !== 'undefined') {
				var oldCell = jQuery("#alpn_field_" + pte_active_tabs[key]);
				var oldSelectedCell =  oldCell.closest('td');
				if (operation == 'add') {
					oldSelectedCell.attr("style", "background-color: white !important;");
					pte_active_tabs.splice(key, 1);
					pte_get_formlet(key, "", tabTypeId, topicId, formId, uniqueFieldId);
				} else if (operation == 'delete') {
					jQuery.ajax({
						url: alpn_templatedir + 'pte_delete_extra_item.php',
						type: 'POST',
						data: {
							uid: pte_active_tabs[key]
						},
						dataType: "json",
						success: function(json) {
							var tableKey = "table_tab_" + key;
							wpDataTables[tableKey].fnFilter();
							pte_get_formlet(key, "", tabTypeId, topicId, formId, uniqueFieldId);
						},
						error: function() {
							console.log('problem - pte_delete_extra');
						//TODO
						}
					})
				}
				var deleteButton = jQuery('#tabcontent_' + key + ' #pte_extre_delete_item');
				if (deleteButton.hasClass('pte_extra_button_enabled')) {
					deleteButton.removeClass('pte_extra_button_enabled');
					deleteButton.addClass('pte_extra_button_disabled');
				}
			}
		}
}

function pte_get_formlet(tabId, uid, tabTypeId, topicId, formId, uniqueFieldId) {

	jQuery.ajax({
		url: alpn_templatedir + 'pte_serve_formlet.php',
		type: 'POST',
		data: {
			uid: uid,
			tab_type_id: tabTypeId,
			topic_id: topicId,
			form_id: formId,
			unique_field_id: uniqueFieldId,
			pte_user_timezone_offset: pte_user_timezone_offset
		},
		dataType: "html",
		success: function(html) {
			var htmlContainer = jQuery('#form_tab_' + tabId).html(html);
			var form = htmlContainer.find('form');		 //This way because no formId on new
			htmlContainer.html(html);
			if (form.length) {
				formId = form.data('formid');
				setTimeout(function(){ wpforms.ready();}, 0);
				bindWpformsAjaxSuccess(formId, function(){	//Handle Successful Add
				var tableKey = "table_tab_" + tabId;
					wpDataTables[tableKey].fnFilter();
					if (!uid) {
						pte_get_formlet(tabId, "", tabTypeId, topicId, formId, uniqueFieldId);
					}
				});
			}
		},
		error: function() {
			console.log('problem - pte_serve_formlet');
		//TODO
		}
	})
}


function pte_extra_control_table(domId){

	var uid = domId.data('uid');
	var extraSelectedRow =  domId.closest('tr');
	var extraSelectedCell =  domId.closest('td');

	var tabDom = domId.closest('div.pte_tabcontent');
	var tabId = tabDom.data('tab-id');

	var deleteButton = jQuery('#tabcontent_' + tabId + ' #pte_extre_delete_item');
	if (deleteButton.hasClass('pte_extra_button_disabled')) {
		deleteButton.removeClass('pte_extra_button_disabled');
		deleteButton.addClass('pte_extra_button_enabled');
	}

	if (typeof pte_active_tabs === 'undefined') {
		pte_active_tabs = [];
	}
	if (typeof pte_active_tabs[tabId] !== 'undefined') {
		var oldCell = jQuery("#alpn_field_" + pte_active_tabs[tabId]);
		var oldSelectedCell =  oldCell.closest('td');
		oldSelectedCell.attr("style", "background-color: white !important;");
		pte_active_tabs.splice(tabId, 1);
	}
	pte_active_tabs[tabId] = uid;
	extraSelectedCell.attr("style", "background-color: #EBF3F9 !important;");

	pte_get_formlet(tabId, uid);
}

function alpn_handle_extra_table(extraKey) {

	const descStrWidth = 200;
	var formattedField, cell, itemBody;
	var tableKey = "table_tab_" + extraKey;
	var tableData = wpDataTables[tableKey].fnGetData();
	var domId = ""
	var oldId = "";

	for (i=0; i< tableData.length; i++) {
		itemBody = tableData[i][3];
		if (itemBody.length > descStrWidth) {
			itemBody = itemBody.substring(0, descStrWidth) + "...";
		}

		cellObj = jQuery("#alpn_field_" + tableData[i][5]);
		formattedField = "";
		formattedField += "<div class='pte_extra_table'>";
		formattedField += "<div class='pte_extra_cell'>";
		formattedField += tableData[i][2];
		formattedField += "</div>";
		formattedField += "<div class='pte_extra_body'>";
		formattedField += itemBody;
		formattedField += "</div>";
		formattedField += "</div>";
		cellObj.html(formattedField);

		if (typeof pte_active_tabs !== 'undefined') {
			if (typeof pte_active_tabs[extraKey] !== 'undefined') {
				domId = tableData[i][5];
				oldId = pte_active_tabs[extraKey];
				if (domId == oldId) {
					var extraSelectedCell =  jQuery('#alpn_field_' + domId).closest('td');
					extraSelectedCell.attr("style", "background-color: #EBF3F9 !important;");
				}
			}
		}

		cellObj.parent().click(
			function(){
				var domId = jQuery(this).find('div:first');
				pte_extra_control_table(domId);
		});
	}
}


function alpn_handle_topic_table(theTable) {

	var formattedField;
	var phoneStr = "";
	var alpnControl;
	var rowMeta = {};
	var rowDetails = {};
	var iconArea = "";
	var picUrl = "";
	var connectedId="";
	var memberIndicatorClass="";

	if (theTable == 'network') {
		var tableData = wpDataTables.table_network.fnGetData();
	} else {
		var tableData = wpDataTables.table_topic.fnGetData();
		var memberIndicatorClass = '';
	}
	//console.log(tableData);
	for (i=0; i< tableData.length; i++) {
		rowDetails = tableData[i];
		memberIndicatorClass = '';
		if (theTable == 'network') {
			connectedId = parseInt(rowDetails[8]);
			if (connectedId) {
				memberIndicatorClass = ' pte_member_class';
			}
			if (rowDetails[11]) {
				iconArea = "<img style='height: 32px; border-radius: 50%;' src='" + alpn_avatar_baseurl + rowDetails[11] + "'>";
			} else if (rowDetails[9]) {
				iconArea = "<img style='height: 32px;  border-radius: 50%;' src='" + alpn_avatar_baseurl + rowDetails[9] + "'>";
			} else{
				iconArea = '';
			}
		} else if (theTable =='topic' && rowDetails[8]) {
				iconArea = "<img style='height: 32px; border-radius: 50%;' src='" + alpn_avatar_baseurl + rowDetails[8] + "'>";
		} else if (rowDetails['5']) {
				rowMeta = JSON.parse(rowDetails[5]);
				iconArea = "<i class='far " + rowMeta['icon1'] + " alpn_icon_left' style='font-size: 24px; margin-top: 7px;' title='" + rowMeta['title1'] + "'></i>"
		}
		formattedField = "";
		formattedField += "<div id='alpn_topic_left' class='alpn_topic_left'>"
		formattedField += "<div class='alpn_name" + memberIndicatorClass + "'>" + rowDetails[3] + "</div>";
		formattedField += "<div class='alpn_about" + memberIndicatorClass + "'>" + rowDetails[4] + "</div>";
		formattedField += "</div>";
		formattedField += "<div id='alpn_topic_right' class='alpn_topic_right'>";
		formattedField += "<div class='alpn_topic_icons' style='line-height: 32px;'>";
		formattedField += iconArea;
		formattedField += "</div>";
		formattedField += "</div>";

		alpnControl = jQuery("[data-uid=" + rowDetails[6] + "]");
		alpnControl.html(formattedField);

		alpnControl.parent().click(
			function(){
				var domid = jQuery(this).find('div');
				alpn_mission_control('select_by_mode', jQuery(domid).data('uid'));
		});
	}
	alpn_reselect();
}

//TODO Do I need to re-attach the event on every table refresh? Seems like this should only need to happen once because they run relative to runtime anyway
// Mqy be a setup step separate from refresh step. Vault handling below appears to only need setup and may not need to subsribe to on refraw for table

function alpn_handle_activity_table() {

	var formattedField = "";
	var alpnControl;
	var tableData = wpDataTables[alpn_activity_table_id].fnGetData();

	for (i=0; i< tableData.length; i++) {

		formattedField =  "<div class='alpn_description'>" + tableData[i]['1'] + "</div>";

		alpnControl = jQuery("[data-uid=" + tableData[i][4] + "]");
		alpnControl.html(formattedField);

		alpnControl.parent().click(
			function(){
				var domid = jQuery(this).find('div');
				alpn_mission_control('select_by_mode', jQuery(domid).data('uid'));
		});
	}
	alpn_reselect();
}

function alpn_handle_vault_table() {

	const strWidth = 19;
	const descStrWidth = 45;
	var table = wpDataTables.table_vault;
	var tableData = table.fnGetData();
	var firstReady = '';

	var access_levels = {'10': 'General', '20': 'Sensitive', '30': 'Special Permissions', '40': 'Private'};

	if (tableData.length) {
		for (i=0; i< tableData.length; i++) {

			var lmdate = dayjs(tableData[i][4]);
			//var cdate = dayjs(tableData[i][3]);
			var mimeType = tableData[i][9];
			var aboutValue = (tableData[i][6]) ? tableData[i][6] : " -";
			if (aboutValue.length > descStrWidth) {
				aboutValue = aboutValue.substring(0, descStrWidth) + "...";
			}

			var upload_state = tableData[i][14];
			var dom_id = tableData[i][11];
			var access_level = tableData[i][2];

			if ((firstReady == '') && (upload_state == 'ready')) {
				firstReady = tableData[i][11];
			}

			var waiting_line = '';
			if (upload_state != 'ready') {
				waiting_line += "<tr id='waiting_indicator_row'>";
				waiting_line += "<td class='alpn_vault_details_cell_name' style='background-color: transparent !important;'><img src='" + alpn_templatedir + "ellipsisindicator.gif'></td>";
				waiting_line += "<td class='alpn_vault_details_cell_value' style='background-color: transparent !important;'>" + "&nbsp;" + "</td>"
				waiting_line += "</tr>";
			}

			if (tableData[i][10]) { //Form Type
				var docType = "<i class='far fa-file-alt' style='margin-right: 5px;'></i>" + tableData[i][5];
				var fileNameRow = "";
			} else {
				if (typeof pte_supported_types_map[mimeType] !== "undefined" ) {
					var docType = pte_supported_types_map[mimeType];
				} else {
					var docType = "";
				}
				var fileName = tableData[i][7].substring(0, strWidth);
				if (tableData[i][7].length > strWidth) {
					fileName += "...";
				}
				//Extra field for non-forms.
				var fileNameRow =  "<tr id='pte_file_name_row'>";
				    fileNameRow += "<td class='alpn_vault_details_cell_name' style='background-color: transparent !important;'>File Name</td>";
					fileNameRow += "<td class='alpn_vault_details_cell_value' style='background-color: transparent !important;'>" + fileName + "</td>";
					fileNameRow += "</tr>";
			}

			var formattedField = "<table class='alpn_vault_details_table'>";
				formattedField += waiting_line;
				if (waiting_line) {
					formattedField += "<tr id='pte_about_row_" + dom_id + "' class='pte_about_row' style='display: none; opacity 0;'>";
				} else {
					formattedField += "<tr id='pte_about_row' class='pte_about_row'>";
				}
				formattedField += "<td class='alpn_vault_details_cell_name' style='background-color: transparent !important;'>Description</td>";
				formattedField += "<td class='alpn_vault_details_cell_value' style='background-color: transparent !important;'>" + aboutValue + "</td>";
				formattedField += "</tr>";
				formattedField += "<tr>";
				if (docType) {
					formattedField += "<td class='alpn_vault_details_cell_name' style='background-color: transparent !important;'>Type</td>";
					formattedField += "<td class='alpn_vault_details_cell_value' style='background-color: transparent !important;'>" + docType + "</td>";
				}
				formattedField += "</tr>";
				formattedField += "<tr>";
				formattedField += "<td class='alpn_vault_details_cell_name' style='background-color: transparent !important;'>Modified</td>";
				formattedField += "<td class='alpn_vault_details_cell_value' style='background-color: transparent !important;'>" + lmdate.format('MMM D, YYYY, h:mma') + "</td>";
				formattedField += "</tr>";
				formattedField += fileNameRow;
				formattedField += "<tr>";
				formattedField += "<td class='alpn_vault_details_cell_name' style='background-color: transparent !important;'>Access</td>";
				formattedField += "<td class='alpn_vault_details_cell_value' style='background-color: transparent !important;'>" + access_levels[access_level] + "</td>";
				formattedField += "</tr>";
				formattedField += "</table>";

			alpnControl = jQuery("[data-uid=" + tableData[i][11] + "]");
			alpnControl.html(formattedField);
			if (upload_state != 'ready') {
				alpnControl.attr("style", "opacity: 0.6; pointer-events: none;");
			}

			jQuery(alpnControl).click(function(){
				var dom_id = jQuery(this).data('uid');
				alpn_handle_vault_row_selected(dom_id);
			});
		}
			if (alpn_set_vault_to_first_row) {    //For add, edit, new -- allows auto select of top row. Do nothing if everything is being processed.
				if (firstReady) {
					alpn_handle_vault_row_selected(firstReady)
					alpn_set_vault_to_first_row = false;
					alpn_oldVaultSelectedId = firstReady;
				} else {
					alpn_oldVaultSelectedId = '';
				}
			} else {
				alpn_handle_vault_row_selected(alpn_oldVaultSelectedId);
			}
	}
}

function pte_set_chat(state){

	var chatWindow = jQuery('#cometchat_embed_synergy_container');
	var cwOpacity = chatWindow.css('opacity');

	if (state == 'disabled') {
		chatWindow.css('opacity', '0').css('pointer-events', 'none');
	} else {
		chatWindow.css('opacity', '1').css('pointer-events', 'auto');
	}
}

function pte_start_chat(chatType, chatId){
	console.log('Top ' + chatType + ' with ' + chatId);
	alpn_wait_for_ready(10000, 250,
		function(){
			if (typeof CometChathasBeenRun !== 'undefined') {
					console.log('Chat Ready...');
					return true;
			}
			console.log('Chat Waiting ' + chatType + ' with ' + chatId);
			return false;
		},
		function(){
			if (chatType == 'single') {
				console.log('One-on-One Chat' + ' with ' + chatId);
				jqcc.cometchat.launch({uid:chatId});
			} else { //group
				console.log('Group Chat' + ' with ' + chatId);
				jqcc.cometchat.launch({guid:chatId});
			}
			pte_set_chat('enable');
		},
		function(){ //Handle Error
			console.log("Error Starting Chat..."); //TODO Handle Error
		});
}

function alpn_loadChat() {

	console.log('Setting up chat...');

	chat_auth = "7d9a0cf1b26826f6e583117c7a4b779d";
	chat_appid = '53889';

  	var chat_height = '350px';
  	var chat_width = '690px';

	jQuery('#' + 'alpn_chat_body').html('<div id="cometchat_embed_synergy_container" style="width:'+chat_width+';height:'+chat_height+';max-width:100%;border:1px solid #CCCCCC;border-radius:5px;overflow:hidden;"></div>');

		var chat_js = document.createElement('script'); chat_js.type = 'text/javascript'; chat_js.src = 'https://fast.cometondemand.net/'+chat_appid+'x_xchatx_xcorex_xembedcode.js';
		chat_js.onload = function() {
			var chat_iframe = {}; chat_iframe.module="synergy"; chat_iframe.style="min-height:"+chat_height+";min-width:"+chat_width+";"; chat_iframe.width=chat_width.replace('px','');chat_iframe.height=chat_height.replace('px','');
			chat_iframe.src='https://'+chat_appid+'.cometondemand.net/cometchat_embedded.php';
			if(typeof(addEmbedIframe)=="function"){addEmbedIframe(chat_iframe);}
		}

		var chat_script = document.getElementsByTagName('script')[0]; chat_script.parentNode.insertBefore(chat_js, chat_script);
}

function alpn_moveActivitySection(){ //Repositions alerts under topics on HD or less.

	var theSection = jQuery('#alpn_section_alert');
	var windowWidth = jQuery(window).width();
	var sectionParent = jQuery('#alpn_section_alert').parent();

	if (windowWidth <= 1280) {				//HD or Less
		if (sectionParent.hasClass('alpn_column_3')) {
			jQuery('.alpn_column_3').attr("style", "flex-basis: 0%;  margin-left: 0px !important; margin-right: 0px !important; min-width: unset !important;");
			jQuery('.alpn_column_2').attr("style", "flex-basis: 85%; margin-right: 0 !important;");
			jQuery('#alpn_section_alert').appendTo(".alpn_column_1");
		}
	}
	if (windowWidth >= 1440) {//Big enough
		if (sectionParent.hasClass('alpn_column_1')) {
			jQuery('.alpn_column_3').attr("style", "flex-basis: 15%; margin-left: 0px !important; margin-right: 10px !important; min-width: 240px !important;");
			jQuery('.alpn_column_2').attr("style", "flex-basis: 70%;  margin-right: 10px !important;");
			jQuery('#alpn_section_alert').appendTo(".alpn_column_3");
		}
	}
}

function alpn_resizeAll(){
	alpn_resizeChat()
	alpn_moveActivitySection();
}

function alpn_resizeChat() { //Centered on the work

	if (pte_chrome_extension == false) {
		var containerLeft = jQuery('#alpn_main_container').position().left;
		var containerWidth = jQuery('#alpn_main_container').parent().width();
		var panelWidth = jQuery('#alpn_chat_panel').width();
		var panelLeft = containerLeft + ((containerWidth - panelWidth) / 2);

		jQuery("#alpn_chat_panel").css('left', panelLeft);
	}
}

( function () {

//			alpn_show_wait_on('#alpn_add_edit_outer_container');


	window.addEventListener( "message", ( event ) => {
		var name = event.data.name;
		if (event.data.name == 'open_file') {
				var pte_chrome_extension_data = event.data.data;
				alpn_wait_for_ready(10000, 250,  //Network Table
					function(){
						if (typeof pte_chrome_extension !== "undefined") {
								return true;
						}
						return false;
					},
					function(){
						pte_uppy_chrome_extension();
						if (typeof uppyChromeVault !== "undefined") {
							//uppyChromeVault.reset();
							uppyChromeVault.addFile({
							  name: pte_chrome_extension_data.file_name, // file name
							  type: pte_chrome_extension_data.blob.type, // file type
							  data: pte_chrome_extension_data.blob,
								source: "chrome"
							})
						}
						jQuery('#pte_extension_topic_select').select2({
							theme: "bootstrap",
							width: '100%',
							allowClear: false
						});
						jQuery('#alpn_selector_sharing').select2({
							theme: "bootstrap",
							width: '100%',
							allowClear: false,
							minimumResultsForSearch: -1
						});

					},
					function(){ //Handle Error
						console.log("Error Loading..."); //TODO Handle Error
					});
		}
	});
window.parent.postMessage({ name: "app_ready" }, "*" );
} () );


function iformat(icon) {
    var originalOption = icon.element;
    return '<i class="far ' + jQuery(originalOption).data('icon') + ' alpn_icon_topic_list"></i>' + icon.text;
}

jQuery( document ).ready( function(){

	if ((typeof alpn_user_id != "undefined") && (alpn_user_id > 0)) {	//Must be logged in
		//TODO get rid of this like network and topic
		if (jQuery('#alpn_section_alert .wpdt-c :input')[2]) {
			var alpn_activity_table_obj = JSON.parse(jQuery('#alpn_section_alert .wpdt-c :input')[2].value);
			alpn_activity_table_id = alpn_activity_table_obj.tableId
		}

		alpn_moveActivitySection(); //place activity in column based on window width

		//WORKING

		jQuery('#alpn_selector_topic_type').select2({
			theme: "bootstrap",
			width: '137px',
			allowClear: false,
			minimumResultsForSearch: -1,
			templateSelection: iformat,
			templateResult: iformat,
			escapeMarkup: function(text) {
				return text;
			}
		});

		window.onload = function(){

			alpn_wait_for_ready(10000, 250,  //Network Table
				function(){
					if (pte_chrome_extension == false  && wpDataTables.table_network !== "undefined") {
							return true;
					}
					return false;
				},
				function(){
					wpDataTables.table_network.addOnDrawCallback( function(){
						alpn_handle_topic_table('network');
					})
					alpn_handle_topic_table('network');
					alpn_prepare_search_field(wpDataTables.table_network.selector + "_filter");
					wpDataTables.table_network.fnSettings().oLanguage.sZeroRecords = 'No Network Connections...';
					wpDataTables.table_network.fnSettings().oLanguage.sEmptyTable = 'No Network Connections...';
				},
				function(){ //Handle Error
					console.log("Error Loading..."); //TODO Handle Error
				});

			alpn_wait_for_ready(10000, 250,  //Topic Table
				function(){
					if (pte_chrome_extension == false  && wpDataTables.table_topic !== "undefined") {
							return true;
					}
					return false;
				},
				function(){
					wpDataTables.table_topic.addOnDrawCallback( function(){
						alpn_handle_topic_table('topic');
					})
					alpn_handle_topic_table('topic');
					alpn_prepare_search_field(wpDataTables.table_topic.selector + "_filter");
					wpDataTables.table_topic.fnSettings().oLanguage.sZeroRecords = 'No Topics...';
					wpDataTables.table_topic.fnSettings().oLanguage.sEmptyTable = 'No Topics...';

					jQuery("#alpn_topic_container_left").insertBefore('#table_topic_filter');
					jQuery('#alpn_selector_topic_filter').select2({
						theme: "bootstrap",
						width: '137px',
						allowClear: false,
						placeholder: 'Filter...',
						minimumResultsForSearch: -1
					});
				},
				function(){ //Handle Error
					console.log("Error Loading..."); //TODO Handle Error
				});

			alpn_wait_for_ready(10000, 250,  //Client Table
				function(){ //Something to check
					if (pte_chrome_extension == false && wpDataTables[alpn_activity_table_id] !== "undefined") {
						if (wpDataTables[alpn_activity_table_id].fnGetData().length) {
							return true;
						}
					}
					return false;
				},
				function(){ //Handle Success
					wpDataTables[alpn_activity_table_id].addOnDrawCallback( function(){
						alpn_handle_activity_table();
					})
					alpn_handle_activity_table();
					alpn_prepare_search_field(wpDataTables[alpn_activity_table_id].selector + "_filter");
				},
				function(){ //Handle Error
					console.log("Error Loading..."); //TODO Handle Error
				});

				alpn_mission_control('select_by_mode', jQuery(".alpn_user_container").data("uid"));

			//TODO Filters

			jQuery("#alpn_selector_container_left").insertBefore('#table_network_filter');

			jQuery('#alpn_selector_network').select2({
				theme: "bootstrap",
				width: '137px',
				placeholder: "Filter...",
				allowClear: true
			});

		};


		//SETUP CHAT

		alpn_resizeChat();
		alpn_wait_for_ready(10000, 250,
			function() {
				if (alpn_oldSelectedId) {
					return true;
				}
				return false;
			},
			function(){

				chat_id = alpn_user_id;
				if (chat_id) {
					chat_name = alpn_user_displayname;
					chat_avatar = alpn_avatar_baseurl + alpn_avatar_handle;
				} else {
					chat_name = '_error_';
				}
				alpn_loadChat();
			},
			function(){ //Handle Error
				console.log("Error Chat Setup..."); //TODO Handle Error
			});

		jQuery( window ).resize(function(){ //Move things on resize
			alpn_resizeAll();
		});

		//Setup Slider Chat Panel

		jQuery('#alpn_chat_panel').click(function() {
			if (jQuery('#alpn_chat_panel').css('bottom') == '0px') {
					jQuery('#alpn_chat_panel').css('bottom', '-355px');
					pte_chat_window_open = false;
					console.log('closing...');
			} else {
					jQuery('#alpn_chat_panel').css('bottom', '0px');
					pte_chat_window_open = true;
					console.log('opening...');
			}
	  });

	}
});

function alpn_wait_for_ready(waitPeriod, tryFrequency, checkCondition, callback, errorHandler){
	//in milliseconds. Checks every tryFrequency for checkCondition. If true, then callback. Keeps trying up to waitPeriod. Fails to errorHandler.
	var tryTimes = parseInt(waitPeriod / tryFrequency);
	if (checkCondition()) {
		callback();
		return;
	}
	var tryCount = 1;
	var alpn_timer = setInterval(function(){
		if (checkCondition()) {
			clearInterval(alpn_timer);
			callback();
		}
		tryCount++;
		if (tryCount >= tryTimes) {
			clearInterval(alpn_timer);
			errorHandler();
			console.log('Problemo loading stuff');  //TODO Handle
		}
	}, tryFrequency);
}

function isEmpty(obj) {
    for(var prop in obj) {
        if(obj.hasOwnProperty(prop))
            return false;
    }
    return true;
}


function alpn_handle_file_submit(payload) { //file_received handlers
	console.log('file_received...');
	console.log(payload);

	var message = payload['message'];
	var topic = message['topic'];
	var data = message['data'];

	var domId = data['dom_id']
	jQuery('#alpn_field_' + domId).attr("style", "opacity: 1.0; pointer-events: auto;").find('#waiting_indicator_row').remove();
	jQuery('#pte_about_row_' + domId).attr("style", "display: table-row; opacity: 1.0;");
}


function pte_file_upload_complete(res) {
    for(var prop in res) {
		if (prop == 'filesUploaded') {

				var topicId = jQuery('.alpn_container_title_2').data('topic-id');
				var description = jQuery('#alpn_about_field').val();   //About/Description
				var permissions = jQuery('#alpn_selector_sharing').find(':selected');
				if (typeof permissions[0] !== "undefined") {
					var permissionValue = permissions[0]['value'];
				} else{
					var permissionValue = '40';	 //Private thought should never be empty
				}

				jQuery.ajax({
					url: alpn_templatedir + 'alpn_handle_vault_files_start.php',
					type: 'GET',
					data: {
						resources: res[prop],
						topicId: topicId,
						description: description,
						permissionValue: permissionValue
					},
					dataType: "json",
					success: function(json) {
						alpn_set_vault_to_first_row = false;
						wpDataTables.table_vault.fnFilter();
						alpn_handle_vault_table_row_selected(jQuery('#table_form_search tbody tr:first')[0]);
						jQuery('#alpn_about_field').val('');
						jQuery('#alpn_selector_sharing').val('40').trigger('change');
					},
					error: function() {
						console.log('problemo - filesUploaded');
					//TODO
					}
				})
		}

		if (prop == 'filesFailed') { //Some files failed. Deal with it. TODO
		}
    }
}

function pte_register_uploads(pteUploads){

	var pte_file_data = [];
	var file = id = mimeType = "";
	for (var key in pteUploads) {
		file = pteUploads[key];
		id = file['meta']['pte_uid'];
		mimeType = file['type'];
		name = file['name'];
		pte_file_data.push({"pte_uid": id, "mimeType": mimeType, "name": name});
	}



	var topicId = jQuery('.alpn_container_title_2').data('topic-id');
	var description = jQuery('#alpn_about_field').val();   //About/Description
	var permissions = jQuery('#alpn_selector_sharing').find(':selected');
	if (typeof permissions[0] !== "undefined") {
		var permissionValue = permissions[0]['value'];
	} else{
		var permissionValue = '40';	 //Private though should never be empty
	}

	if (pte_chrome_extension == true) {  //Get topicId from Drop Down Selection in Extension
		var selectedItem = jQuery('#pte_extension_topic_select').find(':selected');
		if (selectedItem.length) {
		 	topicId = selectedItem[0].value;
		}
	}

	jQuery.ajax({
		url: alpn_templatedir + 'alpn_handle_vault_files_start.php',
		type: 'POST',
		data: {
			topicId: topicId,
			description: description,
			permissionValue: permissionValue,
			pte_file_data: pte_file_data
		},
		dataType: "json",
		success: function(json) {

			if (pte_chrome_extension == false) { // Uses same file workflow as extension so special case.
				alpn_set_vault_to_first_row = false;
				wpDataTables.table_vault.fnFilter();
				//alpn_handle_vault_table_row_selected(jQuery('#table_form_search tbody tr:first')[0]);
				jQuery('#alpn_about_field').val('');
				jQuery('#alpn_selector_sharing').val('40').trigger('change');
			}
		},
		error: function() {
			console.log('problemo - filesUploaded');
		//TODO
		}
	});
}


function pte_uppy_chrome_extension(){

	if (pte_chrome_extension == true) {

		if (typeof uppyChromeVault !== "undefined") {
				uppyChromeVault.reset();
		} else {

		uppyChromeVault = Uppy.Core({
				id: pte_uppy_uploader,
			  debug: true,
			  autoProceed: false,
				allowMultipleUploads: false,
				waitForEncoding: false,
				waitForMetadata: false,
				onBeforeFileAdded: (file) => {
					file['meta'] = {
						...file['meta'],
						'pte_uid':pte_UUID(),
						'pte_source': file.source
					};
 				return true;
			},
				onBeforeUpload: (files) => {
					pte_register_uploads(files);
				return true;
				},
			  restrictions: {
			    maxFileSize: 1024 * 1024 * 100,
			    maxNumberOfFiles: 1,
			    minNumberOfFiles: 1
			  },
			  locale: {
					encoding: "Processing...",
			    strings: {
			      youCanOnlyUploadFileTypes: 'Should Not See This...',
			    }
			  }
			})
		.use(Uppy.Transloadit, {
				 service: 'https://api2.transloadit.com',
			   waitForMetadata: true,
				 waitForEncoding: false,
			   importFromUploadURLs: false,
			   alwaysRunAssembly: false,
			   signature: null,
			   fields: {},
			   limit: 1,
					getAssemblyOptions (file) {
						return {
							params: {
		 				    auth: { key: '0f89b090056541ff8ed17c5136cd7499' },
		 				    template_id: '3b83f38410d744caa3060af90cd64bc0'
		 		  		},
							fields: {
								pte_uid: file.meta.pte_uid,
								pte_source: file.meta.pte_source
							}
						}
					}
		   })
			 .use(Uppy.Dashboard, {
		 	          inline: true,
		 	          target: '#pte_uppy_uploader',
		 						width: "300px",
								height: "250px",
								hideCancelButton: true,
								showRemoveButtonAfterComplete: true,
								hideProgressAfterFinish: true,
								proudlyDisplayPoweredByUppy: false,
								showProgressDetails: true,
								showLinkToFileUploadResult: false,
								animateOpenClose: false
		 	    })
		}

}

}


function pte_uppy_topic_logo(){

	if (pte_chrome_extension == false) {

		var fileCounter = 0;
		var allowedFileTypes = ['image/jpeg', 'image/jpg',	'image/png', 'image/xvg+xml'];

		jQuery('#pte_profile_logo_crop').empty();
		jQuery('#pte_profile_logo_selector').empty();

		var doka = Doka.create(document.querySelector('#pte_profile_logo_crop'),
		{
		    utils: ['crop', 'filter'],
				allowButtonCancel: true,
				allowDropFiles: false,
				allowAutoClose: false,
				cropAllowRotate: false,
				cropAllowImageTurnLeft: false,
				cropAllowImageFlipHorizontal: false,
				cropAllowImageFlipVertical: false,
				oncancel: function(){
					fileCounter--;
				}
		});

		var fileCounter = 0;
		var uppyTopicLogo = Uppy.Core({
			id: "pte_profile_logo_selector",
		  debug: true,
		  autoProceed: true,
			allowMultipleUploads: false,
			onBeforeFileAdded: function(file) {
				if (file.handledByDoka) return true;
				if (fileCounter >= 1) return false;
				if (!allowedFileTypes.includes(file.type)) return false;
	 		 jQuery('#pte_profile_logo_selector').hide();
	 		 jQuery('#pte_profile_logo_crop').show();
	 		 if (file.preview) {
	 			 if (file.source == 'Dropbox') {
	 				 var previewUrl = file.preview; //TODO find a usable version of the image -- getting auth error messages when trying to get this preview. Need to use authorized
	 				 console.log(previewUrl);
	 			 } else if (file.source == 'GoogleDrive') {
	 				 var previewUrl = file.preview.substring(0, file.preview.indexOf("="));
	 			 }
	 			 var xhr = new XMLHttpRequest();
	 			 xhr.open('GET', previewUrl, true);
	 			 xhr.responseType = 'blob';
	 			 xhr.onload = function(e) {
	 				 if (this.status == 200) {
	 					 var myBlob = this.response;
	 					 doka.edit(myBlob).then(output => {
	 						 jQuery('#pte_profile_logo_crop').hide();
	 						 jQuery('#pte_profile_logo_selector').show();
	 						 if (output) {
	 							 uppyTopicLogo.addFile({
	 									 ...file,
	 									 data: output.file,
	 									 isRemote: false,
	 									 handledByDoka: true
	 							 });
	 						 }
	 					 });
	 				 }
	 			 };
	 			 xhr.send();
	 		 } else {
	 			 doka.edit(file.data).then(output => {
	 				 jQuery('#pte_profile_logo_crop').hide();
	 				 jQuery('#pte_profile_logo_selector').show();
	 				 if (output) {
	 					 uppyTopicLogo.addFile({
	 							 ...file,
	 							 data: output.file,
	 							 handledByDoka: true
	 					 });
	 				 }
	 			 });
	 		 }
	 		 return false;
	    },
		  restrictions: {
		    maxFileSize: 1024 * 1024 * 5,
		    maxNumberOfFiles: 1,
		    minNumberOfFiles: 1,
		    allowedFileTypes: allowedFileTypes
		  },
		  locale: {
		    strings: {
		      youCanOnlyUploadFileTypes: 'Please select an image file',
					encoding: "Processing..."
		    }
		  }
		})
		.use(Uppy.Transloadit, {
			 service: 'https://api2.transloadit.com',
		   waitForEncoding: true,
		   importFromUploadURLs: false,
		   alwaysRunAssembly: false,
		   signature: null,
		   fields: {},
		   limit: 1,
			 params: {
			    auth: { key: '0f89b090056541ff8ed17c5136cd7499' },
			    template_id: 'dd30dd60dd6140d4a74ee83ab874e313'
	  		}
	   })
		 .use(Uppy.Dashboard, {
	 	          inline: true,
	 	          target: '#pte_profile_logo_selector',
	 						note: '',
	 						width: "100%",
	  					height: "325px",
							proudlyDisplayPoweredByUppy: false,
							showProgressDetails: true,
							showLinkToFileUploadResult: false,
							animateOpenClose: false
	 	    })
				.use(Uppy.GoogleDrive, {
					target: Uppy.Dashboard,
					companionUrl: Uppy.Transloadit.COMPANION,
					companionAllowedHosts: Uppy.Transloadit.COMPANION_PATTERN
				})
				.on('transloadit:complete', (assembly) => {
					console.log("Topic Logo Upload Complete...");
					if (typeof assembly.results !== "undefined") {
						var results = assembly['results'].resize_image[0];
						pte_save_topic_pic(results, 'logo');
						pte_uppy_topic_logo();
				}
				})
		}
}

function pte_uppy_topic_icon(){

	if (pte_chrome_extension == false) {

		var fileCounter = 0;
		var allowedFileTypes = ['image/jpeg', 'image/jpg',	'image/png', 'image/xvg+xml'];

		jQuery('#pte_profile_image_crop').empty();
		jQuery('#pte_profile_image_selector').empty();

		var doka = Doka.create(document.querySelector('#pte_profile_image_crop'),
		{
		    utils: ['crop', 'filter'],
				allowButtonCancel: true,
				allowDropFiles: false,
				allowAutoClose: false,
				cropAspectRatio: 1,
				cropAllowRotate: false,
				cropAllowImageTurnLeft: false,
				cropAllowImageFlipHorizontal: false,
				cropAllowImageFlipVertical: false,
				oncancel: function(){
					fileCounter--;
				}
		});

		var uppyTopicIcon = Uppy.Core({
			id: "pte_profile_image_selector",
		  debug: true,
		  autoProceed: true,
			allowMultipleUploads: false,
			onBeforeFileAdded: function(file) {
	      if (file.handledByDoka) return true;
				if (fileCounter >= 1) return false;
				if (!allowedFileTypes.includes(file.type)) return false;
				jQuery('#pte_profile_image_selector').hide();
				jQuery('#pte_profile_image_crop').show();
				if (file.preview) {
					if (file.source == 'Dropbox') {
						var previewUrl = file.preview; //TODO find a usable version of the image -- getting auth error messages when trying to get this preview. Need to use authorized
						console.log(previewUrl);
					} else if (file.source == 'GoogleDrive') {
						var previewUrl = file.preview.substring(0, file.preview.indexOf("="));
					}
					var xhr = new XMLHttpRequest();
					xhr.open('GET', previewUrl, true);
					xhr.responseType = 'blob';
					xhr.onload = function(e) {
					  if (this.status == 200) {
					    var myBlob = this.response;
							doka.edit(myBlob).then(output => {
								jQuery('#pte_profile_image_crop').hide();
								jQuery('#pte_profile_image_selector').show();
								if (output) {
									uppyTopicIcon.addFile({
											...file,
											data: output.file,
											isRemote: false,
											handledByDoka: true
									});
								}
							});
					  }
					};
					xhr.send();
				} else {
					doka.edit(file.data).then(output => {
						jQuery('#pte_profile_image_crop').hide();
						jQuery('#pte_profile_image_selector').show();
						if (output) {
							uppyTopicIcon.addFile({
									...file,
									data: output.file,
									handledByDoka: true
							});
						}
					});
				}
				return false;
	    },
		  restrictions: {
		    maxFileSize: 1024 * 1024 * 5,
		    maxNumberOfFiles: 1,
		    minNumberOfFiles: 1,
		    allowedFileTypes: allowedFileTypes
		  },
		  locale: {
		    strings: {
		      youCanOnlyUploadFileTypes: 'Please select an image file',
					encoding: "Processing..."
		    }
		  }
		})
		.use(Uppy.Transloadit, {
			 service: 'https://api2.transloadit.com',
		   waitForEncoding: true,
		   importFromUploadURLs: false,
		   alwaysRunAssembly: false,
		   signature: null,
		   fields: {},
		   limit: 1,
			 params: {
			    auth: { key: '0f89b090056541ff8ed17c5136cd7499' },
			    template_id: '6945a1a9bf0041b183f445b5796bc998'
	  		}
	   })
		 .use(Uppy.Dashboard, {
	 	          inline: true,
	 	          target: '#pte_profile_image_selector',
	 						note: '',
	 						width: "100%",
	  					height: "325px",
							proudlyDisplayPoweredByUppy: false,
							showProgressDetails: true,
							showLinkToFileUploadResult: false,
							animateOpenClose: false
	 	    })
				.use(Uppy.GoogleDrive, {
					target: Uppy.Dashboard,
					companionUrl: Uppy.Transloadit.COMPANION,
					companionAllowedHosts: Uppy.Transloadit.COMPANION_PATTERN
				})
				.on('transloadit:complete', (assembly) => {
					console.log("Topic Icon Upload Complete...");
					if (typeof assembly.results !== "undefined") {
						var results = assembly['results'].resize_image[0];
						pte_save_topic_pic(results, 'topic');
						pte_uppy_topic_icon();
				}
				})
		}
}

function pte_uppy_vault_file(){

if (pte_chrome_extension == false) {

	if (pte_uppy_instance_id) {
		var currentUppyState = pte_uppy_vault_instances[pte_uppy_instance_id].getState();  //TODO clean up "spent" Uppys. We create one for each upload session. P2 since resets every page refresh
		if (currentUppyState.allowNewUpload == false) {
			pte_uppy_instance_id = pte_UUID();
		}
	} else {
		pte_uppy_instance_id = pte_UUID();
	}
	jQuery('#alpn_add_edit_outer_container').html("<div id='alpn_add_edit_outer_uppy'>");

	pte_uppy_vault_instances[pte_uppy_instance_id] = Uppy.Core({
		id: alpn_add_edit_outer_uppy,
	  debug: true,
	  autoProceed: true,
		allowMultipleUploads: false,
		onBeforeFileAdded: (file, files) => {
			file['meta'] = {
				...file['meta'],
				'pte_uid':pte_UUID(),
				'pte_source': file.source
			};
			return true;
	},
		onBeforeUpload: (files) => {
			pte_uppy_vault_instances[pte_uppy_instance_id].setMeta({ pte_uppy_instance_id:  pte_uppy_instance_id})
			pte_register_uploads(files);
		return true;
		},
	  restrictions: {
	    maxFileSize: 1024 * 1024 * 100,
	    maxNumberOfFiles: 8,
	    minNumberOfFiles: 1
	  },
	  locale: {
			encoding: "Registering...",
	    strings: {
	      youCanOnlyUploadFileTypes: 'Should Not See This...',
	    }
	  }
	})
.use(Uppy.Transloadit, {
		 service: 'https://api2.transloadit.com',
	   waitForMetadata: true,
		 waitForEncoding: false,
	   importFromUploadURLs: false,
	   alwaysRunAssembly: false,
	   signature: null,
	   fields: {},
	   limit: 4,
			getAssemblyOptions (file) {
				return {
					params: {
 				    auth: { key: '0f89b090056541ff8ed17c5136cd7499' },
 				    template_id: '3b83f38410d744caa3060af90cd64bc0'
 		  		},
					fields: {
						pte_uid: file.meta.pte_uid,
						pte_source: file.meta.pte_source,
						pte_uppy_instance_id: file.meta.pte_uppy_instance_id
					}
				}
			}
   })
	 .use(Uppy.Dashboard, {
 	          inline: true,
 	          target: '#alpn_add_edit_outer_uppy',
 						note: '',
 						width: "100%",
  					height: "500px",
						proudlyDisplayPoweredByUppy: false,
						showProgressDetails: true,
						showLinkToFileUploadResult: false,
						animateOpenClose: false
 	    })
			.use(Uppy.GoogleDrive, {
				target: Uppy.Dashboard,
			  companionUrl: Uppy.Transloadit.COMPANION,
			  companionAllowedHosts: Uppy.Transloadit.COMPANION_PATTERN
			})
			.use(Uppy.Dropbox, {
				target: Uppy.Dashboard,
			  companionUrl: Uppy.Transloadit.COMPANION,
			  companionAllowedHosts: Uppy.Transloadit.COMPANION_PATTERN
			})
			.use(Uppy.OneDrive, {
				target: Uppy.Dashboard,
			  companionUrl: Uppy.Transloadit.COMPANION,
			  companionAllowedHosts: Uppy.Transloadit.COMPANION_PATTERN
			})
			.on('transloadit:complete', (result) => {
				alpn_vault_control("add");  //TODO ONLY DO THIS WHEN STILL IN ADD MODE.
			})
		}

}

function alpn_file_add () {

	alpn_manage_vault_buttons('');
	alpn_handle_vault_row_selected('');
	alpn_switch_panel ('add_edit');

	pte_uppy_vault_file();
}

function pte_handle_tab_selected(theObj){

	console.log(theObj);

	if (pte_selected_topic_tab && pte_selected_topic_tab_content) {
		var oldSelectedTab = jQuery(pte_selected_topic_tab);
		var oldSelectedTabContent = jQuery(pte_selected_topic_tab_content);
		oldSelectedTab.removeClass('pte_tab_button_active');
		oldSelectedTabContent.hide();
	}
	var selectedTab = jQuery(theObj);
	selectedTab.addClass('pte_tab_button_active');
	var tabId = selectedTab.data('tab-id');
	pte_selected_topic_tab = '#tab_' + tabId;
	pte_selected_topic_tab_content = '#tabcontent_' + tabId;
	var selectedTabContent = jQuery(pte_selected_topic_tab_content);
	selectedTabContent.show();
}


function alpn_open_vault_work_area(){
	var area_dom = '#alpn_vault_work_area';
	if (jQuery(area_dom).height() == '0'){
		jQuery(area_dom).height('200px');
	}
}

function alpn_close_vault_work_area(){
	var area_dom = '#alpn_vault_work_area';
	if (jQuery(area_dom).height() == '200'){
		jQuery(area_dom).height('0px');
	}
}

function pte_handle_delete_response(response, theObject) {

if (response == 'yes' && typeof theObject !== "undefined") {
	var vaultId = theObject['vault_id'];
	jQuery.ajax({
		url: alpn_templatedir + 'alpn_handle_vault_delete_row.php',
		type: 'GET',
		data: {
			vault_id: vaultId
		},
		dataType: "json",
		success: function(json) {
			console.log('deleted from cloud...');
			alpn_set_vault_to_first_row = true;
			wpDataTables.table_vault.fnFilter();
			setTimeout(function(){
				pte_show_message('green', 'timed', 'Delete successful.');
			}, 500);
			console.log(json);
		},
		error: function() {
			console.log('problemo edit');
		//TODO
		}
	})
}
	pte_clear_message();
}

function pte_clear_message() {
	jQuery("#alpn_message_area").attr('style', "opacity: 0;");
}

function pte_show_message(color, type, message, handler, parms) {

	var theClass = 'pte_' + color + '_notification';
	switch(color) {
		case 'blue':
			var icon = 'info-square';
		break;
		case 'green':
			var icon = 'check-square';
		break;
		case 'yellow_question':
			var icon = 'question-square';
		break;
		case 'yellow-exclamation':
			var icon = 'exclamation-triangle';
		break;
		case 'red':
			var icon = 'exclamation-triangle';
		break;
	}

	var typeStr='';
	if (type == 'confirm') {
		typeStr += "<div id='pte_confirm_container'><div class='pte_confirm_button' onclick='" + handler + "(\"yes\", " + parms + ");'>Yes</div><div class='pte_confirm_button' onclick='" + handler + "(\"no\", " + parms + ");'>No</div></div>";
	}

	var msToDraw = 0;
	var html = "<div class='" + theClass + "'><i id='pte_notification_icon' class='far fa-" + icon + "'></i><div id='pte_notification_message'>" + message + typeStr + "</div></div>";
	if (jQuery("#alpn_message_area").css("opacity") == 1) {
		msToDraw = 500;
		jQuery("#alpn_message_area").attr('style', "opacity: 0;");
	}
	setTimeout(function(){
		jQuery("#alpn_message_area").html(html).attr('style', "opacity: 1;");
	}, msToDraw);

	if (type == 'timed') {
		if (parms) {
			var showTime = parms['show_time'];
		} else {
				var showTime = 5000;
		}
		setTimeout(function(){
			jQuery("#alpn_message_area").attr('style', "opacity: 0;");
		}, showTime);
	}
}

function alpn_prepare_search_field(domSelect) {
	var inputField = jQuery(domSelect + ' label :input').detach();
	jQuery(domSelect + ' label').empty();
	jQuery(domSelect + ' label').append(inputField);
	jQuery(domSelect + ' label').append("<span style='position: absolute; top: 4px; left: 80px; opacity: 0.5; cursor: pointer; font-size: 14px;'>x</span>");
	jQuery(domSelect + ' input').attr("placeholder", "Search...").attr("style", "padding-left:6px !important; padding-right:20px !important; border-style: solid; border-width: 1px; border-radius: 0 20px 0 0 !important; border-color: #ccc; height: 24px; width: 100px; font-weight: normal; background-color: white;");
	jQuery(domSelect + ' label:before').attr("content:'x';display: none; width: 14px; height: 14px; position: absolute; top:5px; left:75px; opacity: .5; z-index: 1000;");

	jQuery(domSelect + '  span').click(
		function(){
			var theControl = jQuery(this).prev();
			var theTableId = theControl.attr('aria-controls');
			var theTable = wpDataTables[theTableId];
			theControl.val('');  //clear field and filters
			theTable.fnFilterClear();
	});
}


function alpn_select_type(uniqueRecId){

	if (!uniqueRecId) {
		uniqueRecId = alpn_oldSelectedId;
	}

	var alpn_mode = '';
	var alpn_obj = jQuery('#alpn_field_' + uniqueRecId).closest("table");

	if (alpn_obj.length) {
		var selectedItem = alpn_obj[0];
		if (selectedItem.id == 'table_network') {return 'network';}
		if (selectedItem.id == 'table_topic') {return 'topic';}
	} else {
		return 'user';
	}
}

function alpn_handle_vault_row_selected(theCellId) {

	pte_clear_message();
	var theOldRow =  jQuery('#alpn_field_' + alpn_oldVaultSelectedId).closest('tr');
	jQuery(theOldRow).children().attr("style", "background-color: white !important;");

	if (theCellId) {

		var theNewRow =  jQuery('#alpn_field_' + theCellId).closest('tr');
		jQuery(theNewRow).children().attr("style", "background-color: #EBF3F9 !important;");

		alpn_manage_vault_buttons(theCellId);

		var tcell = theCellId.toString();
		var told = alpn_oldVaultSelectedId.toString();

		if ((tcell != '') && (tcell != told)) {
			alpn_oldVaultSelectedId = theCellId;
			alpn_vault_control("view");
			alpn_close_vault_work_area();
		}
	} else {
		alpn_oldVaultSelectedId = '';
	}
}

function alpn_manage_vault_buttons(theCellId) {

	var objType = 'form';  //Get from row data soon and below. Extend with rights??
	var objMeta;

	if (theCellId) {
		objMeta = {
				'new': 1,
				'view': 1,
				'edit': 1,
				'delete': 1,
				'chat': 1,
				'alert': 1,
				'fax': 1,
				'email': 1
			}
	} else { //defaults
		 objMeta = {
				'new': 1,
				'view': 0,
				'edit': 0,
				'delete': 0,
				'chat': 0,
				'alert': 0,
				'fax': 0,
				'email': 0
			}
	}

	var table = wpDataTables.table_vault;
	var theRow =  jQuery('#alpn_field_' + theCellId).closest('tr');
	var selectedRowData = table.fnGetData(theRow);
	var theButton;

	for( var key in objMeta ) {
		theButton = jQuery("#alpn_vault_" + key);
		if (objMeta[key]) {
			theButton.attr("style", "opacity: 1; pointer-events: auto;")
		} else {
			theButton.attr("style", "opacity: 0.6; pointer-events: none;")
		}
	}

	var openableFile = false;
	if (selectedRowData) {
		var fileSource = selectedRowData[15];
		var mimeType = selectedRowData[9];
		if (fileSource == 'googledrive' || fileSource == 'onedrive' || fileSource == 'onedriveforbusiness') {
			if (mimeType == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' || mimeType == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || mimeType == 'application/vnd.openxmlformats-officedocument.presentationml.presentation') {
				jQuery('#alpn_vault_edit_original').attr('style', 'pointer-events: auto; opacity: 1.0;');
				openableFile = true;
			}
		}
	}
	if (!openableFile) {
			jQuery('#alpn_vault_edit_original').attr('style', 'pointer-events: none; opacity: 0.5;');
	}
}

function alpn_show_wait_on(elementId){
	jQuery(elementId).waitMe({
		effect : 'roundBounce',
		text : 'Accessing Vault...',
		bg : 'rgba(255,255,255,0.7)',
		color : '#4499d7',
		maxSize : '',
		waitTime : -1,
		textPos : 'vertical',
		fontSize : '',
		source : '',
		onClose : function() {}
	});
	alpn_waiting_indicator_id = elementId;
}

function alpn_hide_wait_on(){
	jQuery(alpn_waiting_indicator_id).waitMe('hide');
	alpn_waiting_indicator_id = "";
}

function alpn_preview_container_loaded(theObj){
	var theSrc = jQuery(theObj).attr('src');
	if (theSrc == '') {
		jQuery('#alpn_vault_preview_embedded').attr('style', 'display: none !important;').hide();
	}
}

function alpn_switch_panel(panel) {
	switch(panel) {
		case 'add_edit':
			jQuery('#alpn_preview_container').attr('src', ''); //added onload, if src empty to hide itself. Solves timing issue where PDF.js complaining when display:none
			jQuery('#alpn_vault_preview_embedded').attr('style', 'display: none !important;').hide();
			jQuery('#alpn_add_edit_outer_container').show();
			alpn_open_vault_work_area();
		break;
		case 'view':
			jQuery('#alpn_add_edit_outer_container').attr('style', 'display: none !important;').hide();
			jQuery('#alpn_vault_preview_embedded').show();
			//alpn_show_wait_on('#alpn_vault_preview_embedded');
		break;
	}
}


function pte_setup_pdf_viewer() {
	var readyWorker = preloadJrWorker({
		workerPath: alpn_templatedir + 'foxitpdf/lib/',
		enginePath: '../lib/jr-engine/gsdk',
		fontPath: '../external/brotli',
		licenseSN: licenseSN,
		licenseKey: licenseKey
	});

  	var PCAppearance = PDFViewCtrl.shared.createClass({
		constructor: function() {
			// CONSTRUCTOR
		},
		getLayoutTemplate: function () {
			var template = document.querySelector('[role=pc-layout-template-container]');
			return template.innerHTML;
		},
		beforeMounted: function(rootComponent) {
			this.rootComponent = rootComponent;
			this.toolbarComponent = rootComponent.getComponentByName('toolbar');
		},
		disableAll: function() {
			this.toolbarComponent && this.toolbarComponent.disable();
		},
		enableAll: function() {
			this.toolbarComponent && this.toolbarComponent.enable();
		}
	}, UIExtension.appearances.Appearance);

	//setup(UIExtension.PDFViewCtrl.DeviceInfo.isMobile ? MobileAppearance : PCAppearance);  //TODO FIX WITH THEM

	setup(PCAppearance);

	function setup(appearance) {
		var PDFUI = UIExtension.PDFUI;
		var Events = UIExtension.PDFViewCtrl.Events;
		var pdfui = window.pdfui = new PDFUI({
			viewerOptions: {
				defaultScale: 'fitHeight',
				libPath: './foxitpdf/lib',
				jr: {
					readyWorker: readyWorker,
					licenseSN: licenseSN,
					licenseKey: licenseKey
				}
			},
			renderTo: '#pte_pdf_ui',
			appearance: appearance,
			fragments: [{
				target: 'zoom-out',
				config: {
				}
			}],
			addons: [
				alpn_templatedir + 'foxitpdf/lib/uix-addons/file-property',
				alpn_templatedir + 'foxitpdf/lib/uix-addons/multi-media',
				alpn_templatedir + 'foxitpdf/lib/uix-addons/password-protect',
				alpn_templatedir + 'foxitpdf/lib/uix-addons/redaction',
				alpn_templatedir + 'foxitpdf/lib/uix-addons/path-objects',
				alpn_templatedir + 'foxitpdf/lib/uix-addons/print',
				alpn_templatedir + 'foxitpdf/lib/uix-addons/full-screen',
				alpn_templatedir + 'foxitpdf/lib/uix-addons/import-form',
				alpn_templatedir + 'foxitpdf/lib/uix-addons/export-form',
				alpn_templatedir + 'foxitpdf/lib/uix-addons/undo-redo'
			].concat(UIExtension.PDFViewCtrl.DeviceInfo.isMobile ? [] : alpn_templatedir + 'foxitpdf/lib/uix-addons/text-object')
		});

		pdfui.addUIEventListener('fullscreenchange', function (isFullscreen) {
			if (isFullscreen) {
				document.body.classList.add('fv__pdfui-fullscreen-mode');
			} else {
				document.body.classList.remove('fv__pdfui-fullscreen-mode');
			}
		});


		pdfui.addViewerEventListener(Events.beforeOpenFile, function () {

			//console.log("Before Open File...");
				//TODO Start wait

		});
		pdfui.addViewerEventListener(Events.openFileSuccess, function () {

			//console.log("File Open Success...");

		});
		pdfui.addViewerEventListener(Events.openFileFailed, function () {
			//handle problem
		});

		window.onresize = function () {
			pdfui.redraw().catch(function () {});
		}
	}
}

function pte_view_document(vaultId, formId, filename) {

	alpn_switch_panel('view');

	var srcFile = alpn_templatedir + 'alpn_get_vault_file.php?which_file=pdf&v_id=' + vaultId;

	pdfui.openPDFByHttpRangeRequest({
		range: {
			url: srcFile,
		}
	}, {
		fileName: filename
	})

}

function alpn_handle_close_add_edit(){
	alpn_close_vault_work_area();
}

function alpn_vault_control(operation) {

	//Get Row Context
	var trOb, rowData, s_id, from_id;
	var vaultId = '';
	var submissionId = '';
	var permissionValue;
	var formId = '';
	var formName = '';
	var formAbout = '';
	var fileName = '';
	var filesource = '';
	var mimeType = '';
	var id = '';


	if (alpn_oldVaultSelectedId) {
		trObj =  jQuery('#alpn_field_' + alpn_oldVaultSelectedId).closest('tr');

		if ((typeof wpDataTables !== "undefined") && trObj) {
			rowData = wpDataTables.table_vault.fnGetData(trObj);

			vaultId = rowData[0];
			submissionId = rowData[8];
			formId = rowData[10];
			formName = rowData[5];
			fileName = rowData[7];
			formAbout = rowData[6];
			fileSource = rowData[15];
			mimeType = rowData[9];
			permissionValue = rowData[2];
		}
	}

	switch(operation) {

		case 'test':

		break;

		case 'open_original' :

				var openUrl = '';
				switch(fileSource) {

					case 'googledrive':
						switch (mimeType) {
							case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
								openUrl = "https://docs.google.com/document/d" + submissionId + "/edit";
							break;
							case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
								openUrl = "https://docs.google.com/spreadsheets/d" + submissionId + "/edit";
							break;
							case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
								openUrl = "https://docs.google.com/presentation/d" + submissionId + "/edit";
							break;
						}
					break;

					case 'onedrive':	 //TODO
						switch (mimeType) {
							case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
								openUrl = "https://docs.google.com/document/d" + submissionId + "/edit";
							break;
							case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
								openUrl = "https://docs.google.com/spreadsheets/d" + submissionId + "/edit";
							break;
							case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
								openUrl = "https://docs.google.com/presentation/d" + submissionId + "/edit";
							break;
						}
					break;

					case 'onedriveforbusiness':	//TODO
						switch (mimeType) {
							case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
								openUrl = "https://docs.google.com/document/d" + submissionId + "/edit";
							break;
							case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
								openUrl = "https://docs.google.com/spreadsheets/d" + submissionId + "/edit";
							break;
							case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
								openUrl = "https://docs.google.com/presentation/d" + submissionId + "/edit";
							break;
						}
					break;
				}

				if (openUrl) {
					window.open(openUrl);
				}

		break;

		case 'edit':
			jQuery('#alpn_about_field').val(formAbout);   //About/Description
			jQuery('#alpn_selector_sharing').val(permissionValue).trigger('change');
			jQuery('#alpn_vault_save_info').attr('style', 'pointer-events: auto; opacity: 1.0;');
			alpn_open_vault_work_area();
		break;
		case 'view':
			//TODO Change to docType	-- UNSAFE. ANYONE CAN JQUERY TO GET FILES????
			//console.log('viewing...' + vaultId);
			if (vaultId) {
				pte_view_document(vaultId, formId, fileName);
			}
		break;
		case 'add':   //this is add and edit

			jQuery('#alpn_about_field').val('');   //About/Description
			jQuery('#alpn_selector_sharing').val('40').trigger('change');

			jQuery('#alpn_vault_edit_original').attr('style', 'pointer-events: none; opacity: 0.5;');
			jQuery('#alpn_vault_save_info').attr('style', 'pointer-events: none; opacity: 0.5;');

			alpn_file_add();

		break;
		case 'delete':
			var parms = {'vault_id': vaultId};
			pte_show_message('yellow_question', 'confirm', 'Please confirm delete:', 'pte_handle_delete_response', JSON.stringify(parms));

		break;
	}
}

function alpn_proteam_member_delete(theDom) {
	var proTeamRowId = jQuery('#alpn_select2_small').attr('pte-pt-id');
	var html = "<div id='alpn_replace_me_" + proTeamRowId + "' style='text-align: center; height: 40px;'><img src='" + alpn_templatedir + "pdf/web/images/loading-icon.gif'></div>";

	jQuery('#pte_proteam_item_' + proTeamRowId).replaceWith(html);
	jQuery.ajax({
		url: alpn_templatedir + 'alpn_handle_delete_rights.php',
		type: 'GET',
		data: {
			rowToDelete: proTeamRowId
		},
		dataType: "json",
		success: function(json) {
			jQuery('#alpn_replace_me_' + proTeamRowId).remove();
			pte_handle_proteam_select("");
		},
		error: function(json) {
			console.log("Failed deleting...");
			//TODO handle
		}
	})
}

function alpn_rights_check(theDom){

	var theItem = jQuery(theDom).data('item');
	var checkState = jQuery(theDom).prop("checked");
	var proTeamId = jQuery(theDom).attr('pte-pt-id');

	var rightsInfo = {'id': proTeamId, 'key': theItem, 'check_state': checkState};
	jQuery.ajax({
		url: alpn_templatedir + 'alpn_handle_edit_rights.php',
		type: 'GET',
		data: {
			rightsInfo: JSON.stringify(rightsInfo)
		},
		dataType: "json",
		success: function(json) {
			var ptePtPanel = jQuery("#pte_proteam_item_" + proTeamId + " #proteam_" + theItem);
			var newStr = checkState ? "&#x2713;" : "";
			var newState = checkState ? "set" : "";
			ptePtPanel.attr("pte-state", newState);
			ptePtPanel.html(newStr);
		},
		error: function(e) {
			//TODO Don't make the change
		}
	})
}

function pte_rights_access_level(theSelection){

	var jObj = jQuery(theSelection);
	var proTeamValue = jObj.attr('id');
	var proTeamText = jObj.attr('text');
	var proTeamId = jQuery("#alpn_select2_small").attr('pte-pt-id');

	jQuery.ajax({
		url: alpn_templatedir + 'alpn_handle_edit_access_level.php',
		type: 'GET',
		data: {
			'proTeamId': proTeamId,
			'proTeamValue' : proTeamValue
		},
		dataType: "json",
		success: function(json) {
			jQuery("#pte_proteam_item_" + proTeamId + " #proteam_access_level").attr("pte-pt-id", proTeamValue);
			jQuery("#pte_proteam_item_" + proTeamId + " #proteam_access_level_text").html(proTeamText);
		},
		error: function(e) {
			console.log("Updated Access Level Failed...");

			//TODO reset to previous value?
		}
	})
}

function pte_add_to_proteam_table(rightsInfo){

	var jRightsInfo = jQuery(rightsInfo.element);
	var wp_id = jRightsInfo.attr('data-wp-id');  //wp-id
	rightsInfo['wp_id'] = wp_id;
	var selectedName = rightsInfo['text'];
	var selectedId = rightsInfo['id'];
	var proTeamTable = jQuery('#alpn_proteam_selected_outer'); //network topic
	var topicId = '';                    //network topic id or user
	var topicName = '';					//network name
	var theTopic = {};
	var handled = false;
	var dbCommit = false;
	var html = "<div id='alpn_replace_me_" + selectedId + "' style='text-align: center;'><img src='" + alpn_templatedir + "pdf/web/images/loading-icon.gif'></div>";

	if (proTeamTable.children().length) {
		proTeamTable.children().each(function(){
			theTopic = jQuery(this);
			topicId = theTopic.data('id');
			topicName = theTopic.data('name');
			if (selectedName == topicName) {   //dupe - skip
				handled = true;
				return false;
			}
			if (selectedName < topicName) { //look for greater than insert before
				theTopic.before(html);
				handled = true;
				dbCommit = true;
				return false;  //break foreach
			}
		});
		if (!handled) { //if none greater than add at end
				theTopic.after(html);
				dbCommit = true;
		}
	} else {
		proTeamTable.append(html);
		dbCommit = true;
	}

	if (dbCommit) {
		var topicContext = jQuery('#alpn_inner_proteam_manager').data('for-topic');

		jQuery.ajax({
			url: alpn_templatedir + 'alpn_handle_add_rights.php',
			type: 'GET',
			data: {
				"topic_context": topicContext,
				"topic_id": selectedId,
				"topic_name": selectedName,
				"topic_wp_id": wp_id
			},
			dataType: "html",
			success: function(html) {
				jQuery('#alpn_replace_me_' + selectedId).replaceWith(html);


				//var newSelect = jQuery(html).data('id');
				//alpn_setup_proteam_member_selector(newSelect);
			},
			error: function() {
				console.log('Failure handling add...');

				//TODO
			}
		})
	}
}

function alpn_setup_proteam_member_selector(proteam_id){

	var selector = '#alpn_select2_small_' + proteam_id;

	if (proteam_id == 'all') {
		selector = '.alpn_select2_small';
	}

	jQuery(selector).select2( {
		width: '125px',
		theme: "bootstrap",
		allowClear: false,
		minimumResultsForSearch: -1
	});

	jQuery(selector).on("select2:select", function (e) {
		var data = e.params.data;
		pte_rights_access_level(data);
	});
	jQuery("[aria-labelledby^=select2-alpn_select2_small_]").addClass('alpn_select2_small');	 //TODO alt approach is dupe css, make changes accordingly.

}

function pte_handle_message_merge(){
	var selectData = jQuery('#pte_proteam_template_select').select2('data');
		if (selectData.length) {

		var selectRow = selectData[0];
		var selectId = selectRow.id;
		var topicId = jQuery('#alpn_inner_proteam_manager').data('for-topic');
		var topicTypeId = jQuery('#alpn_inner_proteam_manager').data('for-topic-type');
		var pteId = jQuery('#alpn_select2_small').attr('pte-pt-id');

		if (topicTypeId != 4 && topicTypeId !=5 && pteId) {
			jQuery.ajax({
				url: alpn_templatedir + 'pte_handle_merge_message.php',
				type: 'GET',
				data: {
					template_id: selectId,
					topic_id: topicId,
					pte_id: pteId
				},
				dataType: "json",
				success: function(json) {
					var messageTitle = json['message_title'];
					var messageBody = json['message_body'];
					jQuery('#pte_message_title_field').val(messageTitle);
					jQuery('#pte_message_body_area').val(messageBody);
				},
				error: function() {
					console.log('Failure handling merge...');
					//TODO
				}
			})
		}
	}
}

function alpn_setup_proteam_selector(uniqueRecId){

	var alpn_selected_type = alpn_select_type(uniqueRecId);

	if (alpn_selected_type == 'topic') {

		jQuery('#alpn_proteam_selector').select2( {
			theme: "bootstrap",
			width: '100%',
			allowClear: true,
			closeOnSelect: false,
			placeholder: "Add to Team..."
		});
		jQuery('#alpn_proteam_selector').on('select2:select', function (e) {
			var data = e.params.data;
			pte_add_to_proteam_table(data, uniqueRecId);
		});
		jQuery('#alpn_proteam_selector').on('select2:close', function (e) {
			jQuery("#alpn_proteam_selector").val('').trigger('change');
		});

		jQuery('#pte_proteam_template_select').select2( {
			theme: "bootstrap",
			width: '100%',
		});

		jQuery('#pte_proteam_template_select').on('select2:select', function (e) {
			pte_handle_message_merge();
		});

		pte_handle_message_merge();
	}
}

function pte_handle_proteam_select(theObj){

	if (theObj == ''){
		pte_old_proteam_selected_id	= "";
		jQuery("#pte_proteam_work_area").height('0px');
		return;
	}

	var newObj = jQuery(theObj);

	if (pte_old_proteam_selected_id) {
		var preObj = jQuery("#" + pte_old_proteam_selected_id);
		preObj.css('background-color', '#fdfdfd');
		jQuery("#pte_proteam_work_area").height('0px');
	}

	var pte_new_proteam_selected_id = newObj.attr("id");

	if (pte_new_proteam_selected_id == pte_old_proteam_selected_id) {
		pte_old_proteam_selected_id	= "";
		jQuery("#pte_proteam_work_area").height('0px');
	} else {
		var proteam_setting = '';
		newObj.css('background-color', '#EBF3F9');
		var proTeamId = newObj.data('id');
		var proTeamName = newObj.data('name');
		jQuery('#proTeamPanelUser').html(proTeamName);
		var proteam_access_level = newObj.find('#proteam_access_level').attr('pte-pt-id');
		jQuery('#alpn_select2_small').val(proteam_access_level);
		jQuery('#alpn_select2_small').trigger('change');
		jQuery('#alpn_select2_small').attr('pte-pt-id', proTeamId);
		proteam_setting = newObj.find('#proteam_edit').attr("pte-state");  //TODO change to an array driven loop...
		jQuery("input[data-item='edit']").prop('checked', proteam_setting == 'set').attr('pte-pt-id', proTeamId);	  //TODO used DATA attr but data was not coming through. It looked right but got stale results.
		proteam_setting = newObj.find('#proteam_view').attr("pte-state");
		jQuery("input[data-item='view']").prop('checked', proteam_setting == 'set').attr('pte-pt-id', proTeamId);
		proteam_setting = newObj.find('#proteam_delete').attr("pte-state");
		jQuery("input[data-item='delete']").prop('checked', proteam_setting == 'set').attr('pte-pt-id', proTeamId);
		proteam_setting = newObj.find('#proteam_faz').attr("pte-state");
		jQuery("input[data-item='fax']").prop('checked', proteam_setting == 'set').attr('pte-pt-id', proTeamId);
		proteam_setting = newObj.find('#proteam_new').attr("pte-state");
		jQuery("input[data-item='new']").prop('checked', proteam_setting == 'set').attr('pte-pt-id', proTeamId);
		proteam_setting = newObj.find('#proteam_send').attr("pte-state");
		jQuery("input[data-item='send']").prop('checked', proteam_setting == 'set').attr('pte-pt-id', proTeamId);
		proteam_setting = newObj.find('#proteam_get').attr("pte-state");
		jQuery("input[data-item='get']").prop('checked', proteam_setting == 'set').attr('pte-pt-id', proTeamId);
		pte_old_proteam_selected_id = pte_new_proteam_selected_id;
		jQuery("#pte_proteam_work_area").height('295px');
		pte_handle_message_merge();
	}
}

function pte_save_topic_pic(fileUploaded, source){

		if (fileUploaded.id) {
			var fileHandle = fileUploaded.id + "." + fileUploaded.ext;
			var currentSelection = alpn_select_type();
			var trObj =  jQuery('#alpn_field_' + alpn_oldSelectedId).closest('tr');

			switch(currentSelection) {
				case 'user':
					var topicId = jQuery('#alpn_field_' + alpn_oldSelectedId).data('topic-id');
					var topicTypeId = '5';
				break;
				case 'network':
					var topicRowData = wpDataTables.table_network.fnGetData(trObj);
					var topicId = topicRowData[0];
					var topicTypeId = '4';
				break;
				case 'topic':
					var topicRowData = wpDataTables.table_topic.fnGetData(trObj);
					var topicId = topicRowData[0];
					var topicTypeId = topicRowData[2];
				break;
			}
			jQuery.ajax({
				url: alpn_templatedir + 'pte_handle_profile_pic_upload.php',
				type: 'POST',
				data: {
					source: source,
					handle: fileHandle,
					topic_id: topicId,
					topic_type_id: topicTypeId
				},
				dataType: "json",
				success: function(json) {

					if (source == 'logo') {
						var alpn_logo_url = alpn_avatar_baseurl + fileHandle;
						jQuery('#pte_profile_logo_image').attr('src', alpn_logo_url);
						pte_set_accordion('#pte_topic_logo_accordion', 'close');
					} else {
						alpn_avatar_url = alpn_avatar_baseurl + fileHandle;
						alpn_avatar_handle = fileHandle;
						switch(currentSelection) {
							case 'user':
								jQuery('#user_profile_image').replaceWith("<img id='user_profile_image' style='height: 32px; border-radius: 50%;' src='" + alpn_avatar_url + "'>");
								jQuery('#pte_profile_pic_topic').attr("src", alpn_avatar_url);
							break;
							case 'network':
							case 'topic':
								var iconContainer = jQuery('#alpn_field_' + alpn_oldSelectedId + ' .alpn_topic_icons');
								iconContainer.html("<img id='user_profile_image' style='height: 32px; border-radius: 50%;' src='" + alpn_avatar_url + "'>");
							break;
						}
						pte_set_accordion('#pte_topic_photo_accordion', 'close');
					}

				},
        error: function() {
					console.log("Update Profile Pic Failed...");
					//TODO
            	}
        })
		}
}

function pte_setup_address_book(){
//		css: "https://proteamedge.com/wp-content/themes/memberlite-child-master/dist/css/cloudsponge.css",

	cloudsponge.init({
		afterInit :function() {
			cloudsponge.launch();
		},
		theme: 'inline',
		selectionLimit: 1000,
		rootNodeSelector: "#pte_address_book_ui",
		sources: ['office365', 'windowslive', 'outlook', 'gmail', 'csv', 'yahoo', 'icloud', 'aol'],
	  afterLaunch: function() { },
	  beforeClosing: function() { },
		afterSubmitContacts: function(contacts, source){

			jQuery.ajax({
				url: alpn_templatedir + 'pte_handle_add_to_network.php',
				type: 'POST',
				data: {
					contacts: contacts
				},
				dataType: "json",
				success: function(json) {

					console.log("Update Network Contacts Succeeded..");
					console.log(json);

				},
        error: function() {
					console.log("Update Network Contacts Failed..");
					//TODO
        }
      })



				//console.log(source);
				//console.log(contacts);



		},
		afterClosing: function(){
			pte_setup_address_book();
		}
	});
}

function pte_set_accordion(panel_id, set_to) {

	var accordion = jQuery(panel_id);
	var panel = accordion.next();

	var panelHeight = panel.data('height');

	if (panel.css('height') == '0px' && set_to == 'open') {
		panel.attr("style", "height: " + panelHeight + "; !important");
		accordion.removeClass("pte_accordion_plus");
		accordion.addClass("pte_accordion_minus");
	} else if (set_to == 'close'){
		panel.attr("style", "height: 0px;");
		accordion.removeClass("pte_accordion_minus");
		accordion.addClass("pte_accordion_plus");
	}
}

function pte_initialize_topic_controls(){

	jQuery(".pte_accordion").on("click", function(){
		var accordion = jQuery(this);
		var panel = jQuery(this.nextElementSibling);

		var panelHeight = panel.data('height');


		if (panel.css('height') == '0px') {
			panel.attr("style", "height: " + panelHeight + "; !important");
			accordion.removeClass("pte_accordion_plus");
			accordion.addClass("pte_accordion_minus");
		} else {
			panel.attr("style", "height: 0px;");
			accordion.removeClass("pte_accordion_minus");
			accordion.addClass("pte_accordion_plus");
		}
	});

	pte_uppy_topic_icon();
	pte_uppy_topic_logo();
	pte_setup_address_book();
}

function alpn_mission_control(operation, uniqueRecId = '', overRideTopic = ''){

	switch(operation) {
		case 'add_topic':
			var topicTypeId = jQuery('#alpn_selector_topic_type').val();
			if (overRideTopic != '') {topicTypeId = overRideTopic};
			jQuery.ajax({
				url: alpn_templatedir + 'alpn_handle_topic_add.php',
				type: 'POST',
				data: {
					topicTypeId: topicTypeId,
					pte_user_timezone_offset: pte_user_timezone_offset,
					previous_topic: alpn_oldSelectedId
				},
				dataType: "html",
				success: function(html) {
					jQuery('#alpn_edit_container').html(html).fadeIn();
					var tableId = jQuery('.wpforms-form').data('formid');  //ONLY works with one form showing...should be ok. TODO with formlets, many forms may be hidden in DOm. Find a better way to get the tableID. For instance. Search the html before setting in page.
					wpforms.ready(); //required to ajax up the form
					alpn_deselect();
					//WORKING
					bindWpformsAjaxSuccess(tableId, function(){	//Handle Successful Add
						alpn_handle_topic_done(); //show results
					});
				},
            	error: function() {
					//TODO
            	}
        	})

		break;

		case 'edit_topic':

			var alpn_selected_type = alpn_select_type(uniqueRecId);

			if (alpn_selected_type == 'user') {var theTable = '';}

			jQuery.ajax({
				url: alpn_templatedir + 'alpn_handle_topic_edit.php',
				type: 'POST',
				data: {
					uniqueRecId: uniqueRecId,
					alpn_selected_type: alpn_selected_type,
					pte_user_timezone_offset: pte_user_timezone_offset
				},
				dataType: "html",
				success: function(html) {
					jQuery('#alpn_edit_container').html(html).fadeIn();
					var tableId = jQuery('.wpforms-form').data('formid');  //ONLY works with one form showing...should be ok
					wpforms.ready(); //required to ajax up the form
					bindWpformsAjaxSuccess(tableId, function(){	//Handle Successful Add
						alpn_handle_topic_done(); //show results
					});
				},
				error: function() {
					//TODO
				}
			})
		break;

		case 'go_back':

			console.log('go_back was called...');  //TODO is this needed?
			alpn_mode = 'topic';
			alpn_mission_control('select_topic', uniqueRecId);
		break;

		case 'select_by_mode':
			switch(alpn_mode){
				case 'topic':
					alpn_mission_control('select_topic', uniqueRecId);
				break;
				case 'vault':
					alpn_mission_control('vault', uniqueRecId);
					//console.log('someone is selecting vault by mode...');

				break;
			}
		break;

		case 'select_topic':

			pte_active_tabs = []; //reset all row-selected state for tabs

			alpn_handle_select(uniqueRecId);

			var trObj =  jQuery('#alpn_field_' + alpn_oldSelectedId).closest('tr');
			var alpn_selected_type = alpn_select_type(uniqueRecId);
			var chatType = '';
			var chatTarget = '';

			switch(alpn_selected_type) {

				case 'user':
					var chatType = 'single';
					var chatTarget = 2;
				break;
				case 'network':
					var networkRowData = wpDataTables.table_network.fnGetData(trObj);
					var chatType = 'single';
					var chatTarget = parseInt(networkRowData[8]);		 //WP_ID

				break;
				case 'topic':
					var topicRowData = wpDataTables.table_topic.fnGetData(trObj);
					var chatType = 'group';
					var chatTarget = parseInt(topicRowData[0]);	//Topic ID
				break;
			}

			jQuery.ajax({
				url: alpn_templatedir + 'alpn_handle_topic_select.php',
				type: 'POST',
				data: {
					uniqueRecId: uniqueRecId,
					pte_user_timezone_offset: pte_user_timezone_offset
				},
				dataType: "html",
				success: function(html) {

					jQuery('#alpn_edit_container').html(html).fadeIn();
					pte_handle_tab_selected(jQuery('#tab_0'));
					pte_initialize_topic_controls();
					wpforms.ready();

					alpn_setup_proteam_selector(uniqueRecId);
					alpn_setup_proteam_member_selector('all');

					if (chatTarget) {
						pte_start_chat(chatType, chatTarget);
					} else {
						pte_set_chat('disabled');
					}


				},
				error: function() {
					//TODO
				}
			})
		break;

		case 'select_alert':
			console.log(uniqueRecId);

			alpn_handle_select(uniqueRecId);
			jQuery('#alpn_edit_container').html("<div>SMART ALERT</div>").fadeIn();

		break;

		case 'vault':
			console.log("Selecting Vault...");

			alpn_handle_select(uniqueRecId);

			var trObj =  jQuery('#alpn_field_' + alpn_oldSelectedId).closest('tr');
			var alpn_selected_type = alpn_select_type(uniqueRecId);
			var chatType = '';
			var chatTarget = '';

			switch(alpn_selected_type) {

				case 'user':
					var chatType = 'single';
					var chatTarget = 2;
				break;
				case 'network':
					var networkRowData = wpDataTables.table_network.fnGetData(trObj);
					var chatType = 'single';
					var chatTarget = parseInt(networkRowData[8]);		 //WP_ID

				break;
				case 'topic':
					var topicRowData = wpDataTables.table_topic.fnGetData(trObj);
					var chatType = 'group';
					var chatTarget = parseInt(topicRowData[0]);	//Topic ID
				break;
			}

			if (chatTarget) {
				pte_start_chat(chatType, chatTarget);
			} else {
				pte_set_chat('disabled');
			}

			jQuery.ajax({
				url: alpn_templatedir + 'alpn_handle_vault.php',
				type: 'GET',
				data: {
					uniqueRecId: uniqueRecId,
					alpn_selected_type: alpn_selected_type
				},
				dataType: "html",
				success: function(html) {
					pte_clear_message();
					alpn_mode = 'vault';
					jQuery('#alpn_edit_container').html(html).fadeIn();
					if (jQuery('#alpn_outer_vault .wpdt-c :input')[2]) {
							var alpn_vault_table_settings = JSON.parse(jQuery('#alpn_outer_vault :input')[2].value);
							wdtRenderDataTable(jQuery('#table_vault'), alpn_vault_table_settings);
						    alpn_prepare_search_field('#table_vault_filter');
							wpDataTables.table_vault.fnSettings().oLanguage.sZeroRecords = 'No Vault Items...';
							wpDataTables.table_vault.fnSettings().oLanguage.sEmptyTable = 'No Vault Items...';
							alpn_set_vault_to_first_row = true;
							alpn_oldVaultSelectedId = '';
							wpDataTables.table_vault.addOnDrawCallback( function(){
								alpn_handle_vault_table();
							})

							//console.log('switching to vault before vault workarea...');
					}

					pte_setup_pdf_viewer();

					jQuery.ajax({  //TODO - Merge with alpn_handle_vault.php -- doesn't need to standalone since it should only happen as the vault is getting rendered and not every add/edit
						url: alpn_templatedir + 'alpn_handle_vault_add.php',
						type: 'GET',
						data: {
						},
						dataType: "html",
						success: function(html) {
							jQuery('#alpn_vault_work_area').html(html);

							jQuery('#alpn_selector_sharing').select2({
								theme: "bootstrap",
								width: '137px',
								allowClear: false,
								minimumResultsForSearch: -1
							});

							setTimeout(function(){
								if (wpDataTables.table_vault.fnGetData().length == 0) {
										alpn_file_add();
										jQuery('#alpn_vault_save_info').attr('style', 'pointer-events: none; opacity: 0.5;');
										pte_show_message('blue', 'timed', 'Upload files or ...');
								}
							}, 500);

						},
						error: function() {
							//TODO
						}
					})
				},
            	error: function() {
                //TODO
            	}
        })
		break;
		default:

			console.log('Mission Control Error');

		break;
	}
}

function alpn_reselect () {

	if (alpn_oldSelectedId) {
		jQuery('#alpn_field_' + alpn_oldSelectedId).parent().attr('style', 'background-color: #C8C8C8 !important;');
	}
	//TODO manage_chat()

}

function alpn_deselect () {

	if (alpn_oldSelectedId) {
		jQuery('#alpn_field_' + alpn_oldSelectedId).parent().attr('style', 'background-color: #F8F8F8 !important;');
	}
	alpn_oldSelectedId = "";
}

function alpn_handle_select(uniqueId) {
	alpn_deselect();
	jQuery('#alpn_field_' + alpn_oldSelectedId).parent().attr('style', 'background-color: #F8F8F8 !important;');
	jQuery('#alpn_field_' + uniqueId).parent().attr('style', 'background-color: #C8C8C8 !important;');
	alpn_oldSelectedId = uniqueId;
}

// For AJAX with wpforms, how to handle callback of success or failure. TODO -- implement Failed

function bindWpformsAjaxSuccess (table_profile_id, callBackFunc) {
			jQuery('#wpforms-form-' + table_profile_id).bind('wpformsAjaxSubmitSuccess', callBackFunc);
}

function bindWpformsAjaxFailed (table_profile_id, callBackFunc) {
			jQuery('#wpforms-form-' + table_profile_id).bind('wpformsAjaxSubmitFailed', callBackFunc);
}

function alpn_handle_topic_done(){

	jQuery.ajax({
		url: alpn_templatedir + 'alpn_topic_latest.php',
		type: 'GET',
		dataType: "json",
		success: function(topic) {
			var topic_dom_id = topic['dom_id'];
			jQuery.ajax({
					url: alpn_templatedir + 'alpn_handle_topic_select.php',
					type: 'POST',
					data: {
						uniqueRecId: topic_dom_id,
						pte_user_timezone_offset: pte_user_timezone_offset
					},
					dataType: "html",
					success: function(html) {


						//TODO -- Manage vault area after add/edit -- Bug


						jQuery('#alpn_edit_container').html(html).fadeIn();
						pte_handle_tab_selected(jQuery('#tab_0'));
						pte_initialize_topic_controls()
						wpforms.ready();


						alpn_setup_proteam_selector(topic_dom_id);
						alpn_setup_proteam_member_selector('all');
						if (topic['last_op'] == 'edit') { //edit
							var line1 = topic['name'];
							var line2 = topic['about'];
							var fieldNameId = '#alpn_field_' + topic_dom_id + ' .alpn_name';
							var fieldAboutId = '#alpn_field_' + topic_dom_id + ' .alpn_about';
							jQuery(fieldNameId).html(line1);
							jQuery(fieldAboutId).html(line2);
						} else { //add
							var topicTypeId = topic['topic_type_id'];
							var topicId = topic['id'];
							if (topicTypeId == '4') { //network
								var table = wpDataTables.table_network.fnFilter(topicId, 0);
								alpn_handle_select(topic_dom_id)
							} else if (topicTypeId == '5') { //user
								//should only be edit, never add
							} else { //topic
								var table = wpDataTables.table_topic.fnFilter(topicId, 0);
								alpn_handle_select(topic_dom_id)
							}
						}
					},
				error: function() {
					//TODO
				}
			})
		},
		error: function() {
			//TODO
		}
	})
}

//TODO Manage save button states
function pte_handle_workarea_button(operation){
	var vaultSelectedRow =  jQuery('#alpn_field_' + alpn_oldVaultSelectedId).closest('tr');
	var vaultRowData = wpDataTables.table_vault.fnGetData(vaultSelectedRow);
	var fileSource = vaultRowData[15];
	var mimeType = vaultRowData[9];
	var id = vaultRowData[8];    //original file id
	var vaultId =  vaultRowData[0];
	var description = jQuery('#alpn_about_field').val();   //About/Description
	var permissions = jQuery('#alpn_selector_sharing').find(':selected');
	if (typeof permissions[0] !== "undefined") {
		var permissionValue = permissions[0]['value'];
	} else{
		var permissionValue = '40';	 //Private thought should never be empty
	}

	switch(operation) {
		case 'update_info':
			jQuery.ajax({
				url: alpn_templatedir + 'alpn_handle_update_vault_meta.php',
				type: 'GET',
				data: {
					vault_id: vaultId,
					description: description,
					permission_value: permissionValue
				},
				dataType: "json",
				success: function(json) {
				},
				error: function() {
					//TODO
				}
			})
		break;
	}
}
