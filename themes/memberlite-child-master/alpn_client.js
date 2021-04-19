//PTE Globals
alpn_oldSelectedId = "";
alpn_oldVaultSelectedId = "";
alpn_oldFormSelectedRow = {};
pte_old_proteam_selected_id = "";
pte_selected_topic_tab = '';
pte_selected_topic_tab_content = '';
pte_active_tabs = [];

pte_message_to_clear = '';

pte_selected_report_template = '';

pte_oldTopicTypeSelectedId = '';
pte_old_topic_type_name = '';
pte_selected_topic_type_object = {};

pte_select_first_interaction = true;
pte_handle_interaction_skip_table_reselect = false;
pte_selected_interaction_process_id = "";

pte_chat_window_open = false;

alpn_activity_table_id = '';
alpn_set_vault_to_first_row = false;

alpn_waiting_indicator_id='';
alpn_oldVaultFormSelectedId = {};

pte_pstn_numbers = [];
pte_pstn_index = 0;

alpn_add_edit_events_registered = false;

pte_toolbar_active = 'none';

alpn_mode = 'topic';

pte_uppy_vault_instances = [];
pte_uppy_instance_id = '';

pte_uppy_outer = {};

pte_table_page_number = -1;

pte_global_vault_item_dom_id = '';

pte_vault_dom = '';
pte_back_button = false;

pte_local_date = new Date()
pte_timezone_offset = pte_local_date.getTimezoneOffset();
//TODO LOTS MORE SUPPORTED IMAGE FROM TYPES.  ALSO Missed doc mimetypes? PPT?

pte_chrome_extension = (typeof pte_chrome_extension != "undefined" && pte_chrome_extension) ? pte_chrome_extension : false;
pte_topic_manager_loaded = (typeof pte_topic_manager_loaded != "undefined" && pte_topic_manager_loaded) ? pte_topic_manager_loaded : false;
pte_template_editor_loaded = (typeof pte_template_editor_loaded != "undefined" && pte_template_editor_loaded) ? pte_template_editor_loaded : false;

ppCdnBase = "https://storage.googleapis.com/pte_media_store_1/";

access_levels = {'10': 'General', '20': 'Restricted', '30': 'Special', '40': 'Private'};
processColorMap = {"fax_send": "2", "fax_received": "4", "file_received": "5", "proteam_invitation": "6", "proteam_invitation_received": "7", "email_send": "9", "sms_send": "10"};

pte_supported_types_map = {
	'image/jpeg': 'Image - JPEG',
	'image/gif': 'Image - GIF',
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
	'application/octet-stream': 'Unknown',
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

pte_supported_types_map = {
	'image/jpeg': 'JPEG',
	'image/gif': 'GIF',
	'image/jpg': 'JPG',
	'image/png': 'PNG',
	'image/xvg+xml': 'SVG',
	'image/application/illustrator': 'AI',
	'application/postscript': 'EPS',
	'application/pdf': 'PDF',
	'text/plain': 'TXT',
	'text/html': 'HTML',
	'text/rtf': 'RTF',
	'text/richtext': 'RTF',
	'application/rtf': 'RTF',
	'application/x-rtf': 'RTF',
	'application/octet-stream': '',
	'application/msword': 'DOC',
	'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'DOCX',
	'application/vnd.ms-powerpoint': 'PPT',
	'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'PPTX',
	'application/vnd.ms-excel': 'XLS',
	'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'XLSX',
	'application/vnd.oasis.opendocument.text': 'ODT',
	'application/vnd.oasis.opendocument.presentation': 'ODP',
	'application/vnd.oasis.opendocument.spreadsheet': 'ODS',
	'application/zip': 'ZIP',
	'application/x-zip-compressed': 'ZIP'
}

//zendesk prefill support
window.zESettings = {
    webWidget: {
			position: { horizontal: 'right', vertical: 'top' },
      offset: {
        horizontal: '50px',
        vertical: '20px',
        mobile: {
          horizontal: '-10px',
          vertical: '45px'
        }
      }
    }
  };

  zE(function() {
		if (typeof alpn_user_id !== 'undefined' && alpn_user_id) {
	    zE.identify({
	      name: alpn_user_displayname,
	      email: alpn_user_email
	    });
		}
  });

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

function pte_add_link_to_topic(data) {
	var security = specialObj.security;
	jQuery.ajax({
		url: alpn_templatedir + 'pte_add_topic_link.php',
		type: 'POST',
		data: {
			'owner_id_1': data.pte_owner_id,
			'owner_topic_id_1': data.pte_topic_id,
			'owner_id_2': data.pte_owner_id,
			'owner_topic_id_2': data.id,
			'security': security,
			'subject_token': data.pte_subject_token
		},
		dataType: "json",
		success: function(json) {
			var tabId = data.pte_tab_id;
			if (json.owner_dom_id_2) {
				var pageData = {
					'owner_id': data.pte_owner_id,
					'topic_id': data.pte_topic_id,
					'subject_token': data.pte_subject_token,
					'table_type': 'topic_link',
					'tab_id': tabId,
					'connected_topic_id': data.id
				}
				pte_active_tabs[tabId]  = json.owner_dom_id_2;
				pte_set_table_page(pageData);

				//TODO Add a Topic Link then edit. Does not return properly. I think it's because it needs these values
			//	jQuery('#pte_selected_topic_meta').data('tdid', json.target_dom_id);
			//	jQuery('#pte_selected_topic_meta').data('tid', data.pte_topic_id);
			//	jQuery('#pte_selected_topic_meta').data('ttid', '');


			} else {
				console.log('Link already exists...');
			}

		},
		error: function() {
			console.log('problem - add link to topic');
		//TODO
		}
	});

}

function pte_get_form_for_link(tabId, dom_id) {
	var security = specialObj.security;
	if (dom_id) {
		jQuery.ajax({
			url: alpn_templatedir + 'pte_get_form_for_link.php',
			type: 'POST',
			data: {
				'dom_id': dom_id,
				'security': security,
			},
			dataType: "html",
			success: function(html) {
				if (tabId) {
					jQuery('#form_tab_' + tabId).html(html);
				} else {  //TODO Future

				}
			},
			error: function() {
				console.log('problem - pte_get_form_for_link');
			//TODO
			}
		});
	} else {
		jQuery('#form_tab_' + tabId).html('');
	}
}

function handleButtonState(toState, tabId, tButton) {
	var jButton = jQuery('#tabcontent_' + tabId + ' #' + tButton);
	if (toState == 'enabled') {
		if (jButton.hasClass('pte_extra_button_disabled')) {
			jButton.removeClass('pte_extra_button_disabled');
			jButton.addClass('pte_extra_button_enabled');
		}
	} else {
		if (jButton.hasClass('pte_extra_button_enabled')) {
			jButton.removeClass('pte_extra_button_enabled');
			jButton.addClass('pte_extra_button_disabled');
		}
	}
}

function pte_extra_control_table(domId){   //actually element

	var uid = domId.data('uid');
	var extraSelectedRow =  domId.closest('tr');
	var extraSelectedCell =  domId.closest('td');

	var tabDom = domId.closest('div.pte_tabcontent');
	var tabId = tabDom.data('tab-id');

	if (typeof pte_active_tabs[tabId] !== 'undefined') {
		var oldCell = jQuery('table#table_tab_' + tabId + ' #alpn_field_' + pte_active_tabs[tabId]);
		var oldSelectedCell =  oldCell.closest('td');
		oldSelectedCell.attr("style", "background-color: white !important;");
		pte_active_tabs.splice(tabId, 1);
	}
	pte_active_tabs[tabId] = uid;
	extraSelectedCell.attr("style", "background-color: #D8D8D8 !important;");
	  handleButtonState('enabled', tabId, 'pte_extra_unlink_button');
		handleButtonState('enabled', tabId, 'pte_extra_edit_topic_button');
		handleButtonState('enabled', tabId, 'pte_extra_delete_topic_button');
		handleButtonState('enabled', tabId, 'pte_extra_default_topic_button');
  	pte_get_form_for_link(tabId, uid);
}

function pte_unlink_selected_topic(){
	var security = specialObj.security;
	var selectedTab = jQuery("button.tablinks.pte_tab_button_active").data('tab-id');
	var tableId = 'table_tab_' + selectedTab;
	var selectedRowUid = pte_active_tabs[selectedTab];
	var trObj =  jQuery('table#' + tableId + ' #alpn_field_' + selectedRowUid).closest('tr');

	if ((typeof wpDataTables !== "undefined") && trObj) {
		var rowData = wpDataTables[tableId].fnGetData(trObj);
		var linkId = rowData[12];
		jQuery.ajax({
			url: alpn_templatedir + 'pte_handle_unlink_topic.php',
			type: 'POST',
			data: {
				link_id: linkId,
				security: security,
			},
			dataType: "json",
			success: function(json) { //UI udates handled vaia sync
				wpDataTables['table_tab_' + selectedTab].fnFilterClear();
				jQuery('#form_tab_' + selectedTab).html('');
				handleButtonState('disabled', selectedTab, 'pte_extra_unlink_button');
				handleButtonState('disabled', selectedTab, 'pte_extra_edit_topic_button');
				handleButtonState('disabled', selectedTab, 'pte_extra_delete_topic_button');
				handleButtonState('disabled', selectedTab, 'pte_extra_default_topic_button');
			},
			error: function() {
				console.log('problem handling unlink');
			//TODO
			}
		});
	}
}

function pte_draw_connections_table() {
	console.log("Drawing Connections Table");

	var formattedField, cellObj, cellId, connectedIcon, buttonLine;
	var tableData = wpDataTables.table_connections.fnGetData();

	for (i = 0; i < tableData.length; i++) {
		var connection = tableData[i];

		var connectionId = connection[0];
		var connectionName = connection[1];
		var connectionTopicId = connection[2];
		var connectionAbout = connection[3];
		var connectionState = connection[4];

		buttonLine = '';

		switch(connectionState) {
			case 'connected':
				connectedIcon = "<i class='far fa-user-check' title='Member, Connected'></i>";
				buttonLine += "<button class='btn btn-danger btn-sm pte_connected_action_button' onclick='pte_handle_connection_button('unblock', '" + connectionId + "');'>Block</button>";
			break;
			case 'disconnected':
				connectedIcon = "<i class='far fa-user-slash' title='Not a Member'></i>";
			case 'wants_to_connect':
				connectedIcon = "<i class='far fa-question-circle' title='Member Wants to Connect'></i>";
				buttonLine += "<button class='btn btn-danger btn-sm pte_connected_action_button' onclick='pte_handle_connection_button('connect', '" + connectionId + "');'>Connect</button>";
			break;
		}

		//console.log(connection);

		formattedField = "";
		formattedField += "<div class='pte_vault_row'>";
		formattedField += "<div class='pte_vault_5'>";
		formattedField += connectedIcon;
		formattedField += "</div>";
		formattedField += "<div class='pte_vault_row_40 pte_extra_padding_right pte_vault_bold'>";
		formattedField += connectionName;
		formattedField += "</div>";
		formattedField += "<div class='pte_vault_row_40 pte_extra_padding_right'>";
		formattedField += connectionAbout;
		formattedField += "</div>";
		formattedField += "<div class='pte_vault_row_15 pte_vault_right'>";
		formattedField += buttonLine;
		formattedField += "</div>";
		formattedField += "</div>";

		cellId = "div.pte_member_connection[data-uid='" + connectionId + "']";
		cellObj = jQuery(cellId);
		cellObj.html(formattedField);

	}

}

function alpn_handle_extra_table(extraKey) {

	console.log('alpn_handle_extra_table');
	var formattedField, cell, itemBody;
	var tableKey = "table_tab_" + extraKey;
	var tableData = wpDataTables[tableKey].fnGetData();
	var domId = ""
	var oldId = "";
	var cellId = "";
	var topicLink ="";
	var rowData = {};
	var itemName = '';
	var topicId = '';
	var ownerId = 0;
	var operationString = '';
	var pte_topic_link_id = '';
	var pte_topic_link = {};
	var pte_active_row_displaying = false;
	var connectedTopicId;
	var connectedTopicTypeId;
	var connectedTopicSpecial;
	var connectedTopicClass;
	var linkId;
	var defaultTopic;
	var subjectToken;
	var ownerName;

	//sconsole.log(tableData);

	for (i=0; i< tableData.length; i++) {
		rowData = tableData[i];
		domId = rowData[4];
		itemName = rowData[1];
		itemBody = rowData[2];
		ownerId = rowData[3];
		ownerName = rowData[6];
		subjectToken = rowData[8];
		connectedTopicId = rowData[9];
		connectedTopicTypeId = rowData[11];
		connectedTopicSpecial = rowData[13];
		connectedTopicClass = rowData[14];

		linkId = rowData[12];
		defaultTopic = rowData[15] ? rowData[15] : 'no';

		var topicOwnerName = '';
		if (subjectToken == 'pte_external') {
			topicOwnerName = "<div class='pte_external_link_owner'><i class='far fa-user-friends' title='Topic Owner'></i> " + ownerName + "</div>";
		}

		if (defaultTopic == 'yes') {
			var defaultTopicIcon = "<i class='far fa-check-circle pte_default_topic' title='Default Topic Link'></i>";
		} else {
			var defaultTopicIcon = "";
		}

		pte_topic_link_id = 'pte_topic_links_title_link_' + i;
		cellId = "div#tabcontent_" + extraKey + " #alpn_field_" + rowData[4];

		if (connectedTopicClass == 'LINKYES') {
			topicLink = "<div id='" + pte_topic_link_id + "' class='pte_topic_list pte_vault_bold' data-link-id='" + linkId  + "' data-default='" + defaultTopic + "'>" + itemName + defaultTopicIcon + "</div>" + topicOwnerName;
		} else {
			topicLink = "<div id='" + pte_topic_link_id + "' class='pte_topic_links_title pte_vault_bold' data-operation='topic_info' data-topic-dom-id='" + domId + "' data-topic-id='" + connectedTopicId + "' data-topic-type-id='" + connectedTopicTypeId + "' data-topic-special='" + connectedTopicSpecial  + "' data-link-id='" + linkId + "' data-default='" + defaultTopic + "'>" + itemName + defaultTopicIcon + "</div>" + topicOwnerName;
			if (connectedTopicSpecial == 'contact') {
				topicLink = "<div id='" + pte_topic_link_id + "' class='pte_topic_links_title pte_vault_bold' data-operation='network_info' data-network-dom-id='" + domId + "' data-network-id='" + connectedTopicId + "' data-topic-type-id='" + connectedTopicTypeId + "' data-topic-special='" + connectedTopicSpecial + "' data-link-id='" + linkId + "' data-default='" + defaultTopic + "'>" + itemName + defaultTopicIcon + "</div>" + topicOwnerName;
			}
			if (connectedTopicSpecial == 'user') {
				topicLink = "<div id='" + pte_topic_link_id + "' class='pte_topic_links_title pte_vault_bold' data-operation='personal_info' data-topic-dom-id='" + domId + "' data-topic-id='" + connectedTopicId + "' data-topic-type-id='" + connectedTopicTypeId + "' data-topic-special='" + connectedTopicSpecial + "' data-link-id='" + linkId + "' data-default='" + defaultTopic + "'>" + itemName + defaultTopicIcon + "</div>" + topicOwnerName;
			}
		}

		cellObj = jQuery(cellId);
		formattedField = "";
		formattedField += "<div class='pte_vault_row pte_links_table_row'>";
		formattedField += "<div class='pte_vault_row_35 pte_extra_padding_right'>";
		formattedField += topicLink;
		formattedField += "</div>";
		formattedField += "<div class='pte_vault_row_65'>";
		formattedField += itemBody;
		formattedField += "</div>";
		formattedField += "</div>";
		cellObj.html(formattedField);
		if (typeof pte_active_tabs !== 'undefined') {
			if (typeof pte_active_tabs[extraKey] !== 'undefined') {
				oldId = pte_active_tabs[extraKey];
				if (domId == oldId) {
					pte_active_row_displaying = true;
					var extraSelectedCell =  jQuery("div#tabcontent_" + extraKey + ' #alpn_field_' + domId).closest('td');
					extraSelectedCell.attr("style", "background-color: #D8D8D8 !important;");
					pte_get_form_for_link(extraKey, domId);
				}
			}
		}

		cellObj.parent().click(function(){
			var domId = jQuery(this).find('div:first');
				pte_extra_control_table(domId);
		});
		 pte_topic_link = jQuery("div#tabcontent_" + extraKey + " #" + pte_topic_link_id);
		 pte_topic_link.click(function(){
				 	event.stopPropagation();
 					pte_handle_interaction_link_object(this);
 		});
	}
	if (pte_active_row_displaying) {
		handleButtonState('enabled', extraKey, 'pte_extra_unlink_button');
		handleButtonState('enabled', extraKey, 'pte_extra_edit_topic_button');
		handleButtonState('enabled', extraKey, 'pte_extra_delete_topic_button');
		handleButtonState('enabled', extraKey, 'pte_extra_default_topic_button');
	} else {
		handleButtonState('disabled', extraKey, 'pte_extra_unlink_button');
		handleButtonState('disabled', extraKey, 'pte_extra_edit_topic_button');
		handleButtonState('disabled', extraKey, 'pte_extra_delete_topic_button');
		handleButtonState('disabled', extraKey, 'pte_extra_default_topic_button');
		pte_get_form_for_link(extraKey, '');
	}
}


function pte_make_map_data(topicDomId, topicId = 0, topicTypeId = 0, tabId = 0, topicSpecial = 'topic', vaultId = '', operation = 'to_topic_info_by_id') {

	var replaceMe = (topicDomId == "replace_me") ? true : false;

	var mapData = {
		operation: operation,
		topic_dom_id: topicDomId,
		topic_id: topicId,
		topic_type_id: topicTypeId,
		topic_special: topicSpecial,
		vault_dom_id: '',
		vault_id: vaultId,
		replace_me: replaceMe,
		timezone_delta: pte_timezone_offset
	};
	return mapData;
}


function alpn_handle_topic_table(theTable) {

	console.log('alpn_handle_topic_table');
	var formattedField, itemOwnerId;
	var phoneStr = "";
	var alpnControl;
	var rowMeta = {};
	var rowDetails = {};
	var iconArea = "";
	var picUrl = "";
	var connectedId="";
	var memberIndicatorClass="";
	var ownerStr='';
	var topicTypeId = 0;
	var topicId = 0;
	var i = j = 0;
	var iconClass = '';

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
		topicTypeId = rowDetails['2'];
		topicId = rowDetails['0'];

		if (theTable == 'network') {
			//console.log(rowDetails);
			connectedId = parseInt(rowDetails[8]);
			if (connectedId) {
				memberIndicatorClass = ' pte_member_class';
			}
			if (rowDetails[11]) {
				iconArea = "<img style='height: 32px; border-radius: 50%;' src='" + alpn_avatar_baseurl + rowDetails[11] + "'>";
			} else if (rowDetails[9]) {
				iconArea = "<img style='height: 32px;  border-radius: 50%;' src='" + alpn_avatar_baseurl + rowDetails[9] + "'>";
			} else{
				iconArea = "<div style='font-size: 24px;'><i class='" + rowDetails[13] + "'></i></div>";  //fontawesome icon
			}
		} else {  //TOPIC
			itemOwnerId = parseInt(rowDetails[1]);
			if (itemOwnerId != alpn_user_id) {
				memberIndicatorClass = ' pte_member_class';
			}
			//console.log(rowDetails);
			iconArea = '';
			if (rowDetails[8]) {
					iconArea = "<img style='height: 32px; border-radius: 50%;' src='" + alpn_avatar_baseurl + rowDetails[8] + "'>";
			} else {
				iconArea = "<div style='font-size: 24px;'><i class='" + rowDetails[11] + "'></i></div>";
			}
		}
		formattedField = "";
		formattedField += "<div class='pte_topic_wrapper' data-ttid='" + topicTypeId + "' data-tid = '" + topicId + "'>";
		formattedField += "<div id='alpn_topic_left' class='alpn_topic_left'>";
		formattedField += "<div class='alpn_name" + memberIndicatorClass + "'>" + rowDetails[3] + "</div>";
		formattedField += "<div class='alpn_about" + memberIndicatorClass + "'>" + rowDetails[4] + "</div>";
		formattedField += "</div>";
		formattedField += "<div id='alpn_topic_right' class='alpn_topic_right'>";
		formattedField += "<div class='alpn_topic_icons' style='line-height: 32px;'>";
		formattedField += iconArea;
		formattedField += "</div>";
		formattedField += "</div>";
		formattedField += "<div style='clear: both;'>";
		formattedField += "</div>";

		alpnControl = jQuery("div.alpn_column_1 [data-uid=" + rowDetails[6] + "]");
		alpnControl.html(formattedField);

		alpnControl.parent().click(
			function(){
				var topicContainer = jQuery(this).find('div');
				var domId = topicContainer.data('uid');
				alpn_mission_control('select_by_mode', domId);
		});
	}
	alpn_reselect();
}

function pte_handle_topic_link_container_click(topicLinkContainerId){
	var topicLink = jQuery("div[data-pte-topic-link-icon-id='" + topicLinkContainerId + "']");
	var topicContainer = jQuery("div[data-pte-topic-link-id='" + topicLinkContainerId + "']");
	if (topicContainer.css('display') == 'none') {
		topicContainer.show();
		topicLink.html("<i class='fas fa-angle-down'></i>");
	} else {
		topicContainer.hide();
		topicLink.html("<i class='fas fa-angle-right'></i>");
	}
	event.stopPropagation();
}


function pte_handle_widget_interaction(interactionData){ //run the process

	console.log("pte_handle_widget_interaction...");

	var security = specialObj.security;

	interactionData.message_title = jQuery('#pte_message_title_field').val();
	interactionData.message_body = jQuery('#pte_message_body_area').val();
	interactionData.message_response = jQuery('#pte_message_body_area_response').val();
	interactionData.fax_field_first = jQuery('#pte_fax_send_input_field_first').val();
	interactionData.fax_field_last = jQuery('#pte_fax_send_input_field_last').val();
	interactionData.fax_field_company = jQuery('#pte_fax_send_input_field_company').val();
	interactionData.fax_field_fax_number = jQuery('#pte_fax_send_input_field_fax_number').val();

	interactionData.link_interaction_password = jQuery('#link_interaction_password').val();

	var linkExpirationSelect = jQuery('#alpn_select2_small_link_expiration_select');
	var linkExpirationSelectData = linkExpirationSelect.select2('data');
	if (typeof linkExpirationSelectData != 'undefined' && typeof linkExpirationSelectData[0] != 'undefined') {
		var linkInteractionExpiration = linkExpirationSelectData[0].id;
		interactionData.link_interaction_expiration = linkInteractionExpiration;
	}

	var linkOptionsSelect = jQuery('#alpn_select2_small_link_options_select');
	var linkOptionsSelectData = linkOptionsSelect.select2('data');
	if (typeof linkOptionsSelectData != 'undefined' && typeof linkOptionsSelectData[0] != 'undefined') {
		var linkInteractionOptions = linkOptionsSelectData[0].id;
		interactionData.link_interaction_options = linkInteractionOptions;
	}

	var emailAddressSelect = jQuery('#alpn_select2_small_fax_number_select'); //Shared with fax, sms, email  TODO Make less confusing.
	var emailAddressSelectData = emailAddressSelect.select2('data');
	if (typeof emailAddressSelectData != 'undefined' && typeof emailAddressSelectData[0] != 'undefined') {
		var emailAddressTopicId = emailAddressSelectData[0].id;
		interactionData.send_email_address_id = emailAddressTopicId;
	}
	var connectionTypeSelect = jQuery('#alpn_select2_small_connection_type_select');
	var connectionTypeSelectData = connectionTypeSelect.select2('data');
	if (typeof connectionTypeSelectData != 'undefined' && typeof connectionTypeSelectData[0] != 'undefined') {
		interactionData.connection_link_type = connectionTypeSelectData[0].id;
	}
	var linkTopicSelect = jQuery('#alpn_select2_small_link_topic_select');
	var linkTopicSelectData = linkTopicSelect.select2('data');
	if (typeof linkTopicSelectData != 'undefined' && typeof linkTopicSelectData[0] != 'undefined') {
		interactionData.connection_link_topic_id = linkTopicSelectData[0].id;
	}
	var templateControl = jQuery('#alpn_select2_small_template_select');
	var templateData = templateControl.select2('data');
	if (typeof templateData != 'undefined' && typeof templateData[0] != 'undefined') {
		interactionData.template_id = templateData[0].id;
		interactionData.template_name = templateData[0].text;
	}
	var expirationControl = jQuery('#alpn_select2_small_expiration_select');
	var expirationData = expirationControl.select2('data');
	if (typeof expirationData != 'undefined' && typeof expirationData[0] != 'undefined') {
		interactionData.expiration_minutes = expirationData[0].id;
	}
	interactionData.owner_id = alpn_user_id;

	console.log(interactionData);

	jQuery.ajax({
		url: alpn_templatedir + 'pte_handle_widget_interaction.php',
		type: 'POST',
		data: {
			interaction_data: JSON.stringify(interactionData),
			security: security,
		},
		dataType: "json",
		success: function(json) { //UI udates handled vaia sync

			//console.log(interactionData);

		},
		error: function() {
			console.log('problem handling interaction widget');
		//TODO
		}
	})

}

function pte_handler_interaction_setting_slider(slider){

	console.log("Handling pte_handler_interaction_setting_slider...");
	var jSlider = jQuery(slider);

	console.log(jSlider);
}

function pte_make_formatted_number(phoneNumber){
	var country = areaCode = firstThree = lastFour = '';
	lastFour = phoneNumber.slice(8);
	firstThree = phoneNumber.slice(5,8);
	areaCode = phoneNumber.slice(2,5);
	country = phoneNumber.slice(0,2);
	return (country + " (" + areaCode + ") " + firstThree + "-" + lastFour);

}

function pte_handle_fax_number_selected(data){
	console.log('pte_handle_fax_number_selected...');
	if (typeof data.id != 'undefined' && typeof pteFaxNumbers != 'undefined') {
		console.log(data);
		var selectedId = parseInt(data.id);
		if (selectedId) {
			var selectedFaxData = pteFaxNumbers[selectedId];
			jQuery('#pte_fax_send_input_field_first').val(selectedFaxData.first_name);
			jQuery('#pte_fax_send_input_field_last').val(selectedFaxData.last_name);
			jQuery('#pte_fax_send_input_field_company').val(selectedFaxData.company_name);
			jQuery('#pte_fax_send_input_field_fax_number').val(selectedFaxData.fax_number);
		} else {
			jQuery('#pte_fax_send_input_field_first').val('');
			jQuery('#pte_fax_send_input_field_last').val('');
			jQuery('#pte_fax_send_input_field_company').val('');
			jQuery('#pte_fax_send_input_field_fax_number').val('');
		}
	}
}

function pte_copy_to_clipboard(clipText){
	var temp = jQuery("<input>");
	    jQuery("body").append(temp);
	    temp.val(clipText).select();
	    document.execCommand("copy");
	    temp.remove();
}

function pte_topic_link_copy_string(subject, copyString){
	pte_copy_to_clipboard(copyString);
	pte_show_message('green', 'timed', subject + ' sucessfully copied to the clipboard.');
}

function pte_handle_release_email_route(topicId){  //TODO update Personal Page
var security = specialObj.security;
		jQuery.ajax({
			url: alpn_templatedir + 'alpn_handle_remove_email_address.php',
			type: 'POST',
			data: {
				"topic_id": topicId,
				"security": security,
			},
			dataType: "html",
			success: function(html) {   //TODO update Personal Topic Page
				jQuery('#pte_emails_assigned').html(html);
			},
			error: function() {
				console.log('Failure handling add user to list...');
			}
			})
}

function pte_update_email_route(data){ //TODO update Personal Page
	var topicId = data.id;
	var security = specialObj.security;
		jQuery.ajax({
			url: alpn_templatedir + 'alpn_handle_update_email_address.php',
			type: 'POST',
			data: {
				"topic_id": topicId,
				"security": security,
			},
			dataType: "html",
			success: function(html) {
				jQuery('#pte_emails_assigned').html(html);
			},
			error: function() {
				console.log('Failure handling add user to list...');
			}
			})
}

function pte_update_fax_route_topic(phoneNumber, data){  //TODO update Personal Page
	console.log("Handling pte_update_fax_route_topic...");
	var security = specialObj.security;
	var topicId = data.id;
	jQuery.ajax({    //TODO When adding a new user on registration. Need to add them to all the Twilio Channels where they have been added to Topics system wide. Should be in proteam records. Then deleted
		url: alpn_templatedir + 'alpn_handle_update_pstn_number.php',
		type: 'POST',
		data: {
			"phone_number": phoneNumber,
			"topic_id": topicId,
			"security": security,
		},
		dataType: "json",
		success: function(json) {
			console.log('Success...');
		}	,
		error: function() {
			console.log('Failure handling add user to list...');
		}
		})
}

function pte_handle_release_fax_number(domEl){

	console.log('pte_handle_release_fax_number');
	var selectedLi = jQuery(domEl).closest('li');
	var pstnNumberSelected = selectedLi.find('select').data('ptrid');
	var security = specialObj.security;
	jQuery(domEl).addClass('pte_extra_button_disabled');

	jQuery.ajax({    //TODO When adding a new user on registration. Need to add them to all the Twilio Channels where they have been added to Topics system wide. Should be in proteam records. Then deleted
		url: alpn_templatedir + 'alpn_release_pstn_number.php',
		type: 'POST',
		data: {
			"phone_number": pstnNumberSelected,
			"security": security
		},
		dataType: "json",
		success: function(json) {
			console.log('Success pte_handle_release_fax_number...');
			console.log(json);
			selectedLi.remove();  //TODO solves the problem of waiting for mfax but if it fails, need to put it back.

		},
		error: function(json) {
			console.log('Failure pte_handle_release_fax_number...');
			console.log(json);
		}
		})
}

function pte_pstn_handle_use_number() {

		var html = topicList = '';
		var selectedIndex = pte_pstn_index;
		var selectedNumber = pte_pstn_numbers[selectedIndex];

		//TODO move to successful json
		pte_pstn_numbers.splice(selectedIndex, 1);
		pte_pstn_handle_next_number();

		var phoneNumber = selectedNumber.number;
		var formattedNumber = pte_make_formatted_number(phoneNumber);
		var phoneNumberKey = phoneNumber.substr(1);

		html = "<li id='alpn_replace_me_pstn_number_" + 	phoneNumberKey + "'><img src='" + alpn_templatedir + "ellipsisindicator.gif'></li>";
		var faxList = jQuery('#pte_fax_numbers_assigned');
		faxList.append(html);

		jQuery.ajax({    //TODO When adding a new user on registration. Need to add them to all the Twilio Channels where they have been added to Topics system wide. Should be in proteam records. Then deleted
			url: alpn_templatedir + 'alpn_handle_use_pstn_number.php',
			type: 'POST',
			data: {
				"phone_number": phoneNumber
			},
			dataType: "json",
			success: function(json) {
				topicList = json.topic_list;
				html = '';
				html += "<li class='pte_important_topic_scrolling_list_item'>";
				html += "<div class='pte_scrolling_item_left'><div class='pte_pstn_topic_list'>" + topicList  + "</div><div class='pte_pstn_number_list'>" + formattedNumber  + "</div></div>";
	      html += "<div class='pte_scrolling_item_right'><i class='far fa-minus-circle pte_scrolling_list_remove' title='Release Fax Number' onclick='pte_handle_release_fax_number(this);'></i></div>";
				html += "<div style='clear: both;'>";
				html += "</div>";
				html += "</li>";
				jQuery("#alpn_replace_me_pstn_number_" + phoneNumberKey).replaceWith(html);

				var listSelector = jQuery('#alpn_select2_small_' + phoneNumberKey);
				listSelector.select2({
					theme: "bootstrap",
					width: '130px',
					allowClear: false
				});

				listSelector.on("select2:select", function (e) {
					var ptrid = jQuery(e.currentTarget).data("ptrid");
					var data = e.params.data;
					pte_update_fax_route_topic(ptrid, data);
			});

			},
			error: function() {
				console.log('Failure handling add user to list...');
			}
		})
	}


function pte_pstn_handle_next_number() {
	var phoneNumber = formattedNumber = html = '';
	pte_pstn_index++;
	if (typeof pte_pstn_numbers[pte_pstn_index] == "undefined") {
		pte_pstn_index = 0;
		if (typeof pte_pstn_numbers[pte_pstn_index] == "undefined") {
			html = "No numbers available. Lookup again.";
			jQuery('#pte_available_fax_number').html(html);
			return;
		}
	}
	phoneNumber = pte_pstn_numbers[pte_pstn_index].number;
	formattedNumber = pte_make_formatted_number(phoneNumber);
	html = formattedNumber;
	jQuery('#pte_available_fax_number').html(html);
}

function pte_start_pst_number_rotator(json) {
	console.log('Got phone numbers - pte_start_pst_number_rotator');
	var phoneNumber = formattedNumber = html = '';
	if (typeof json.rows != "undefined" && json.rows) {
		pte_pstn_numbers = json.rows;
		pte_pstn_index = 0;
		phoneNumber = pte_pstn_numbers[0].number;
		formattedNumber = pte_make_formatted_number(phoneNumber);
		html += "<div id='pte_available_fax_number'>" + formattedNumber + "</div>";
		html += "<div id='pte_available_buttons'>" + "<button id='pte_pstn_use_number' class='btn btn-danger btn-sm' onclick='pte_pstn_handle_use_number();'>Use</button>" + "<button id='pte_pstn_next_number' class='btn btn-danger btn-sm' onclick='pte_pstn_handle_next_number();'>Next</button>" + "</div>";
		jQuery("#pte_pstn_number_widget_right").html(html);
	}
}

function pte_pstn_widget_lookup(){
	var security = specialObj.security;
	const regex = RegExp("^[0-9]{3}$");
	var areaCode = jQuery('#pte_pstn_widget_area_code').val();
	if (regex.test(areaCode)) {
		var waitIndicator = "<img style='vertical-align: middle;' src='" + alpn_templatedir + "pdf/web/images/loading-icon.gif'>";
		jQuery("#pte_pstn_number_widget_right").html(waitIndicator);

		jQuery.ajax({
			url: alpn_templatedir + 'alpn_get_fax_numbers.php',
			type: 'POST',
			data: {
				area_code: areaCode,
				security: security,
			},
			dataType: "json",
			success: function(json) { //UI udates handled vaia sync
					pte_start_pst_number_rotator(json);
			},
			error: function() {
				console.log('problem getting phone numberss');
			//TODO
			}
		})

	} else {  //not a 3 digit number TODO handle error
		jQuery('#pte_pstn_number_widget_right').html("Please enter a 3 digit area code.");
	}
}

function pte_set_table_page(data){

	console.log('pte_set_table_page');
	console.log(data);
	var security = specialObj.security;
	var tableType = data['table_type'];
	var domId = data['dom_id'];
	var ownerId = data['owner_id'];
	var connectedTopicDomId = data['connected_topic_dom_id'];
	var connectedTopicId = data['connected_topic_id'];

	jQuery.ajax({
		url: alpn_templatedir + 'pte_get_topic_page.php',
		type: 'POST',
		data: {
			"data": data,
			"security": security,
		},
		dataType: "json",
		success: function(json) {

			switch(tableType) {
				case 'table_topic_types':
					var table =	wpDataTables.table_topic_types;
				break;
				case 'topic':
					var table =	wpDataTables.table_topic;
				break;
				case 'network':
					var table =	wpDataTables.table_network;
				break;
				case 'topic_link':  //Topic Link -- has to wait for table.
					var tabId  = data['tab_id'];
					alpn_wait_for_ready(10000, 250,  //Interaction table
						function(){ //Something to check
							if (pte_external == false && wpDataTables['table_tab_' + tabId] != "undefined") {
								if (wpDataTables['table_tab_' + tabId].fnGetData().length >= 0) {
									return true;
								}
							}
							return false;
						},
						function(){ //Handle Success
							console.log('Handling Table Links Success');
							var table =	wpDataTables['table_tab_' + tabId];
							var currentPage = table.api().page();
							table.api().page(json.page_number).draw('page');
						},
						function(){ //Handle Error
							console.log("Failed -- Topic Link Not Ready..."); //TODO Handle Error
						});
					return;
				break;

		}
			alpn_oldSelectedId = domId;
			var currentPage = table.api().page();
			table.api().page(json.page_number).draw('page');
			if (connectedTopicId && connectedTopicDomId) {
				data['table_type'] = 'topic_link';
				pte_set_table_page(data);
			}
		},
		error: function() {
			console.log('Error setting table page...');
		}
	})
}


function pte_select_tab_when_ready (tabId){
tabId = (typeof tabId == 'undefined') ? 0 : parseInt(tabId);
	alpn_wait_for_ready(10000, 250,
		function(){
			if (jQuery('#tab_' + tabId).length) {
				return true;
			}
			return false;
		},
		function(){
			pte_handle_tab_selected(jQuery('#tab_' + tabId));
		},
		function(){ //Handle Error
			console.log("Error pte_select_tab_when_ready.."); //TODO Handle Error
		});
}

function pte_handle_interaction_link_object(theObj){

	var jObj = jQuery(theObj);
	// var operation = jObj.data('operation');
	// var topicId = jObj.data('topic-id');
	// var tabId = jObj.data('tab-id');
	// var networkId = jObj.data('network-id');
	// var topicTypeId = jObj.data('topic-type-id');
	// var topicTypeSpecial = jObj.data('topic-special');
	// var topicDomId = jObj.data('topic-dom-id');
	// var networkDomId = jObj.data('network-dom-id');
	// var vaultId = jObj.data('vault-id');
	// var vaultDomId = jObj.data('vault-dom-id');

	var data = {
		"operation": jObj.data('operation'),
		"topic_id": jObj.data('topic-id'),
		"tab_id": jObj.data('tab-id'),
		"network_id": jObj.data('network-id'),
		"topic_type_id": jObj.data('topic-type-id'),
		"topic_special": jObj.data('topic-special'),
		"topic_dom_id": jObj.data('topic-dom-id'),
		"network_dom_id": jObj.data('network-dom-id'),
		"vault_id": jObj.data('vault-id'),
		"vault_dom_id": jObj.data('vault-dom-id')
	}
	pte_handle_interaction_link(data);

}

function pte_handle_interaction_link(mapData){

	console.log('pte_handle_interaction_link');
	console.log(mapData);

		var operation = mapData.operation;
		var topicId = mapData.topic_id;
		var tabId = mapData.tab_id ? mapData.tab_id : 0;
		var networkId = mapData.network_id;
		var topicTypeId = mapData.topic_type_id;
		var topicDomId = mapData.topic_dom_id;
		var topicSpecial = mapData.topic_special
		var networkDomId = mapData.network_dom_id;

		var vaultId = mapData.vault_id;
		var vaultDomId = mapData.vault_dom_id;

		var table_type = (topicSpecial == "contact") ? "network" : "topic";
		var destinationTopicData = {
			"table_type": table_type,
			"dom_id": topicDomId,
			"owner_id": alpn_user_id,
			"per_page": 5
		};

		switch(operation) {
			case 'to_vault':
				alpn_mission_control("vault", alpn_oldSelectedId);
				alpn_mode = 'vault';
			break;
			case 'to_info':
				alpn_mission_control("select_topic", alpn_oldSelectedId);
				alpn_mode = 'topic';
			break;
			case 'to_report':
				alpn_mission_control("pdf_topic", alpn_oldSelectedId);
				alpn_mode = 'report';
			break;
			case 'to_topic_info_by_id':
				alpn_mission_control("select_topic", topicDomId);
				if (mapData.topic_special != "user") {
					pte_set_table_page(destinationTopicData);
				}
			break;
			case 'to_topic_designer_by_id':
				alpn_mission_control("pdf_topic", topicDomId);
				if (mapData.topic_special != "user") {
					pte_set_table_page(destinationTopicData);
				}
			break;
			case 'to_topic_vault_by_id':
				alpn_mission_control("vault", topicDomId);
				if (mapData.topic_special != "user") {
					pte_set_table_page(destinationTopicData);
				}
			break;
			case 'to_topic_chat_by_id':
				alpn_mission_control("select_by_mode", topicDomId);
				if (mapData.topic_special != "user") {
					pte_set_table_page(destinationTopicData);
				}
				if (!pte_chat_window_open) {
					jQuery('#alpn_chat_panel').click();
				}
			break;

			//TODO some unused and old here.
			case 'topic_audio':
			case 'topic_chat':
				if (!pte_chat_window_open) {
					jQuery('#alpn_chat_panel').click();
				}
			case 'topic_info':
				var data = {
					"table_type": "topic",
					"dom_id": topicDomId,
					"owner_id": alpn_user_id,
					"per_page": 5
				};
				alpn_mission_control("select_topic", topicDomId);
				pte_set_table_page(data);
				alpn_mode = 'topic';
			break;
			case 'topic_same':
				alpn_mission_control("select_by_mode", topicDomId);
			break;
			case 'topic_report':
				var data = {
					"table_type": "topic",
					"dom_id": topicDomId,
					"owner_id": alpn_user_id,
					"per_page": 5
				};
				alpn_mission_control("pdf_topic", topicDomId);
				pte_set_table_page(data);
				alpn_mode = 'report';
			break;
			case 'network_audio':
			case 'network_chat':
				if (!pte_chat_window_open) {
					jQuery('#alpn_chat_panel').click();
				}
			case 'network_info':
				var data = {
					"table_type": "network",
					"dom_id": networkDomId,
					"owner_id": alpn_user_id,
					"per_page": 5
				};
				alpn_mission_control("select_topic", networkDomId);
				pte_set_table_page(data);
				alpn_mode = 'topic';
			break;
			case 'network_report':
				var data = {
					"table_type": "network",
					"dom_id": networkDomId,
					"owner_id": alpn_user_id,
					"per_page": 5
				};
				alpn_mission_control("pdf_topic", networkDomId);
				pte_set_table_page(data);
				alpn_mode = 'report';
			break;
			case 'topic_vault':
				var data = {
					"table_type": "topic",
					"view_type": "vault",
					"dom_id": topicDomId,
					"owner_id": alpn_user_id,
					"per_page": 5
				};
				alpn_mission_control("vault", topicDomId);
				pte_set_table_page(data);
				alpn_mode = 'vault';
			break;
			case 'network_vault':
				var data = {
					"table_type": "network",
					"dom_id": networkDomId,
					"owner_id": alpn_user_id,
					"per_page": 5
				};
				alpn_mission_control("vault", networkDomId);
				pte_set_table_page(data);
				alpn_mode = 'vault';
			break;
			case 'personal_vault':
				var domId = jQuery('#alpn_me_field').find('div.alpn_user_container').data('uid');
				alpn_mission_control("vault", domId);
				alpn_mode = 'vault';
			break;
			case 'personal_info':
				var domId = jQuery('#alpn_me_field').find('div.alpn_user_container').data('uid');
				alpn_mission_control("select_topic", domId);
				alpn_mode = 'topic';
			break;
			case 'personal_report':
				var domId = jQuery('#alpn_me_field').find('div.alpn_user_container').data('uid');
				alpn_mission_control("pdf_topic", domId);
				alpn_mode = 'report';
			break;
			case 'vault_item':
				pte_global_vault_item_dom_id = vaultDomId;
				delete wpDataTables.table_vault;  //timing issues

				console.log('pte_handle_interaction_link VAULT ITEM');
				console.log(mapData);

				var table_type = (topicSpecial == "contact") ? "network" : "topic";

				var data = {
					"table_type": table_type,
					"dom_id": topicDomId,
					"owner_id": alpn_user_id,
					"per_page": 5
				};
				alpn_mission_control("vault", topicDomId);
				if (mapData.topic_special != "user") {
					pte_set_table_page(data);
				}

				alpn_wait_for_ready(10000, 250,
					function(){
						if (pte_external == false && typeof wpDataTables.table_vault != 'undefined') {
							if (wpDataTables.table_vault.api().data().count() > 0 ) {
								return true;
							}
						}
						return false;
					},
					function(){  //Vault

						console.log('FOUND VAULT TABLE');
						var security = specialObj.security;

						var topicMeta = jQuery("#pte_selected_topic_meta");
						var topicAccessLevel = topicMeta.data("pl");

						console.log(topicMeta);
						console.log(topicAccessLevel);

						var data = {
							"table_type": "vault",
							"topic_id": topicId,
							"vault_id": vaultId,
							"owner_id": alpn_user_id,
							"topic_key": topicId,    //Why unknown
							"permission": topicAccessLevel,
							"per_page": 5
						};
						jQuery.ajax({
							//HERE1
							url: alpn_templatedir + 'pte_get_topic_page.php',   //Vault
							type: 'POST',
							data: {
								"data": data,
								"security": security
							},
							dataType: "json",
							success: function(json) {

								console.log("JSON");
								console.log(json);
								var table = wpDataTables.table_vault;

								if (json.page_number == -1) {   //handle vault item not found
									alpn_set_vault_to_first_row = true;
									table.api().page(0).draw('page');
								} else {
									var currentPage = table.api().page();
									if (currentPage != json.page_number) {
											table.api().page(json.page_number).draw('page');
									}
								}
							},
							error: function() {
								console.log("VAULT AJAX PROBLEM");
							}
						})
					},
					function(){ //Handle Error
						console.log("Error Finding Vault Table After Link..."); //TODO Handle Error
					});
				break;
			}
}


function pte_handle_interaction_recall(data){

	console.log('pte_handle_interaction_recall');

	var interactionUxContainer = jQuery('#pte_interaction_outer_container');
	var interactionCard = jQuery("div.alpn_interaction_cell[data-uid=" + data.process_id + "]");
	var interactionMessage = jQuery('#pte_interaction_ux_message');
	var interactionCurrent = jQuery('#pte_interaction_current');


	interactionUxContainer.css('pointer-events', 'none');
	interactionCurrent.animate({opacity: 0.45}, 200, function(){
		interactionUxContainer.css('pointer-events', 'auto');
	});
	interactionMessage.fadeIn(200);
}

function pte_show_process_ux(processId) {

	console.log('Getting Process UX...', processId);

	var interactionOuterContainer = jQuery('#pte_interaction_outer_container');
	var interactionCurrent = jQuery('#pte_interaction_current');
	var interactionUxMessage = jQuery('#pte_interaction_ux_message');

	var security = specialObj.security;
	if (processId) {
		pte_interaction_wait_indicator('start');
		jQuery.ajax({
			url: alpn_templatedir + 'pte_get_interaction_ux.php',
			type: 'POST',
			data: {
				process_id: processId,
				security: security

			},
			dataType: "html",
			success: function(html) {
				var processUx = jQuery("#pte_interaction_current");
				processUx.html(html);

				interactionCurrent.css('opacity', 1);
				interactionUxMessage.hide();
				pte_interaction_wait_indicator('stop');

			},
			error: function() {
				console.log('problem getting interation');
			//TODO
			}
		})
	} else {
		//Interaction Box Zero
		var availableImages = 3;
		var processUx = jQuery("#pte_interaction_current");
		var randomNumber = "0000" + (Math.floor(Math.random() * availableImages) + 1);
		var imageUrl = ppCdnBase + "pte_inbox_background_image_" + randomNumber.slice(-4)  + ".png";
		var html  = "<div id='pte_interactions_message'>";
				html += "<img id='pte_interactions_message_image' src='" + imageUrl + "'>";
		    html += "</div>";
		processUx.html(html);
		pte_interaction_wait_indicator('stop')
		interactionCurrent.css('opacity', 1);
		interactionUxMessage.hide();
	}
}

function pte_handle_remove_list_item(item) {
	var security = specialObj.security;
	var selectedItem = jQuery(item);
	var selectedBox = selectedItem.closest('ul');
	var selectedBoxId = selectedBox.attr('id');
	var selectedItemLi = selectedItem.closest('li');
	var selectedItemItemId = selectedItemLi.data('topic-id');
	if (selectedBoxId && selectedItemItemId) {
		selectedItemLi.remove();
		jQuery.ajax({    //TODO When adding a new user on registration. Need to add them to all the Twilio Channels where they have been added to Topics system wide. Should be in proteam records. Then deleted
			url: alpn_templatedir + 'alpn_handle_delete_from_user_list.php',
			type: 'POST',
			data: {
				"list_key": selectedBoxId,
				"item_id": selectedItemItemId,
				"security": security,
			},
			dataType: "json",
			success: function(json) {
				console.log('Success handling delete user from list...');

			},
			error: function() {
				console.log('Failure handling delete user from list...');
			}
		})
	}
}


function pte_get_active_video_rooms() {

	var security = specialObj.security;

	if (typeof alpn_templatedir != "undefined" && alpn_templatedir && security) {

		jQuery.ajax({
			url: alpn_templatedir + 'pte_get_twilio_video_rooms.php',
			type: 'POST',
			data: {
					"security": security
			},
			dataType: "json",
			success: function(json) {
				console.log("pte_video_rooms_initial_list");
				console.log(json);
				json.name = "pte_video_rooms_initial_list";
				pte_message_chat_window(json)
			},
			error: function() {
				console.log('Failed Getting Twilio Video Rooms...');
			}
		});
	}

}

function pte_add_to_important_topics(type, data) {

	var topicId = 0;
	var topicName = '';
	var handled = false;
	var selectedName = data['text'];
	var selectedId = data['id'];
	var importantList = jQuery('#' + type);
	var importantListCount = importantList.children().length;
	var html = "<li class='pte_important_topic_scrolling_list_item' data-topic-id='" + selectedId  + "'><div class='pte_scrolling_item_left'>" + selectedName  + "</div><div class='pte_scrolling_item_right'><i class='far fa-minus-circle pte_scrolling_list_remove' title='Remove Item' onclick='pte_handle_remove_list_item(this);'></i></div><div style='clear: both;'></div></li>";
	if (importantListCount) {
		importantList.children().each(function(){
			var theLine = jQuery(this);
			topicId = theLine.data('topic-id');
			topicName = theLine.find('div.pte_scrolling_item_left').html();
			if (selectedId == topicId) { //DUPE
				handled = false;
				return false;
			}
			if (selectedName < topicName) {  //SMALLER
				theLine.before(html);
				handled = true;
				return false;
			}
		});
		if (!handled) {  //BIGGER
			importantList.append(html);
			handled = true;
		}
	} else {
		importantList.append(html); //FIRST
		handled = true;
	}
	if (handled) {
		jQuery.ajax({    //TODO When adding a new user on registration. Need to add them to all the Twilio Channels where they have been added to Topics system wide. Should be in proteam records. Then deleted
			url: alpn_templatedir + 'alpn_handle_add_user_list.php',
			type: 'POST',
			data: {
				"list_key": type,
				"item_id": selectedId,
				"item_value": selectedName
			},
			dataType: "json",
			success: function(json) {
			},
			error: function() {
				console.log('Failure handling add user to list...');
			}
		})
	}
}


function pte_handle_interaction_selected(processId) {

console.log('pte_handle_interaction_selected...');

	if (pte_selected_interaction_process_id) {
		var oldDom = jQuery("div[data-uid='" + pte_selected_interaction_process_id + "']");
		var oldCell = oldDom.closest('td');
		oldCell.attr("style", "background-color: rgb(248, 248, 248) !important;");
		pte_selected_interaction_process_id = '';
	}
	if (processId) {
		var selectedDom = jQuery("div[data-uid='" + processId + "']");

		console.log(selectedDom);

		var theCell = selectedDom.closest('td');
		theCell.attr("style", "background-color: #D8D8D8 !important;");
		pte_selected_interaction_process_id = processId;
	}
}


function pte_handle_active_filed_change(tObj){

	pte_interaction_wait_indicator('start');

	var security = specialObj.security;
	var jObj = jQuery(tObj);
	var checkedState = jObj.prop('checked');
	if (checkedState == true) {  //checked
		var showType = 'active';
	} else {
		var showType = 'filed';
	}
	jQuery.ajax({
		url: alpn_templatedir + 'pte_get_interaction_table.php',
		type: 'POST',
		data: {
			show_type: showType,
			security: security
		},
		dataType: "json",
		success: function(json) {

			var html = json.table_html;
			var interactionFilter = json.filter_html;
			var interactionsTableContainer = jQuery("#pte_interactions_table_container");
			interactionsTableContainer.html(html);
			var pte_interaction_table_setting = JSON.parse(jQuery('#pte_interactions_table_container :input')[2].value);
			wdtRenderDataTable(jQuery('#table_interactions'), pte_interaction_table_setting);
			wpDataTables.table_interactions.addOnDrawCallback( function(){
				pte_interactions_table();
			})
			wpDataTables.table_interactions.fnSettings().oLanguage.sZeroRecords = 'No Interactions';
			wpDataTables.table_interactions.fnSettings().oLanguage.sEmptyTable = 'No Interactions';
			pte_select_first_interaction = true;
			pte_interactions_table();

			var searchField = "#table_interactions_filter";
			alpn_prepare_search_field(searchField);
			//jQuery(searchField);
			jQuery(interactionFilter).insertBefore('#table_interactions_filter');
			jQuery('#pte_interaction_table_filter').select2({
				theme: "bootstrap",
				width: '168px',
			});

		},
		error: function() {
			console.log('problem getting interation table');
		//TODO
		}
	})
}

function pte_interaction_wait_indicator(operation = 'stop') {
	var alpnSectionAlert = jQuery('#alpn_section_alert');
	var interactionWaitIndicator = jQuery('#interaction_wait_indicator');
	if (operation == 'stop') {
		alpnSectionAlert.css('pointer-events', 'auto');
		interactionWaitIndicator.hide();
	} else {
		alpnSectionAlert.css('pointer-events', 'none');
		interactionWaitIndicator.show();
	}
}

function pte_handle_file_away(tObj){

	console.log('pte_handle_file_away...');
	var jObj = jQuery(tObj);
	var processId = jObj.data('pid');
	var security = specialObj.security;

	pte_interaction_wait_indicator('start');

	jQuery.ajax({
		url: alpn_templatedir + 'pte_file_interaction_away.php',
		type: 'POST',
		data: {
			process_id: processId,
			security: security,
		},
		dataType: "json",
		success: function(json) {
			pte_select_first_interaction = true;
			pte_handle_interaction_skip_table_reselect = false;
			wpDataTables.table_interactions.fnFilterClear();
			pte_interaction_wait_indicator('stop');

},
		error: function() {
			console.log('problem getting interation table');
		//TODO
		}
	})
	console.log(processId);
}

function pte_make_interaction_vault_link(uxMeta) {

	console.log("Making Link");
	console.log(uxMeta);

		var vaultId = uxMeta.vault_id;
		var vaultDomId = uxMeta.vault_dom_id;
		var topicId = uxMeta.topic_id;
		var topicDomId = uxMeta.topic_dom_id;
		var topicTypeId = uxMeta.topic_type_id;
		var topicSpecial = uxMeta.topic_special;
		var linkString = uxMeta.interaction_type_name;
		var fileName = (uxMeta.vault_file_name) ? uxMeta.vault_file_name.replace(/\\(.)/mg, "$1") : "";

		var vaultLink = vaultId ? '<div title="' + fileName + '" class="pte_topic_link" data-vault-id="' + vaultId  + '" data-topic-special="' + topicSpecial  + '" data-vault-dom-id="' + vaultDomId  + '" data-topic-id="' + topicId  + '" data-topic-type-id="' + topicTypeId  + '" data-topic-dom-id="' + topicDomId  + '" data-operation="vault_item" onclick="event.stopPropagation(); pte_handle_interaction_link_object(this);"><i class="far fa-file-pdf"></i>&nbsp;&nbsp;' + linkString + '</div>' : '';
		return vaultLink;
}

function pte_make_date_html(aDate, typeString) {

	var months = {0: "JAN", 1: "FEB", 2: "MAR", 3: "APR", 4: "MAY", 5: "JUN", 6: "JUL", 7: "AUG", 8: "SEP", 9: "OCT", 10: "NOV", 11: "DEC"};
	var createdDate = dayjs(aDate).utc(true).local();
	var createdDayOfMonth = createdDate.date();
	var createdMonth = createdDate.month();
	var createdTimeHours24 = createdDate.hour();
	var createdTimeAmPm = "AM";
	var createdTimeHours = createdTimeHours24 <= 12 ? createdTimeHours24 : createdTimeHours24 - 12;
			createdTimeHours = createdTimeHours == 0 ? "12" : createdTimeHours;
			createdTimeAmPm = createdTimeHours24 < 12 ? "AM" : "PM";
	var createdTimeMinutes =  '0' + createdDate.minute();
	    createdTimeMinutes = createdTimeMinutes.slice(-2);

	var createdCal = "<div id='pte_date_container' title='" + typeString + " Date'><div id='pte_date_number_date'>" + createdDayOfMonth + "</div><div id='pte_date_month_updated'>" + months[createdMonth] + "</div></div>";
	var createdTime = "<div id='pte_date_container' title='" + typeString + " Time'><div id='pte_date_number_time'>" + createdTimeHours + ":" + createdTimeMinutes + "</div><div id='pte_date_month_updated'>" + createdTimeAmPm + "</div></div>";

return {"cal_html": createdCal, "time_html": createdTime};

}


function pte_update_interaction_importance(interactionImportanceContainer, priority, interactionComplete) {
	var interactionImportanceIcon = interactionImportanceContainer.find('div.pte_importance_icon');
	if (priority > 2) {
		interactionImportanceIcon.css("color", "rgb(255, 140, 0)");
		interactionImportanceContainer.css("border-color", "rgb(255, 140, 0)");
		titleImportance = 'High';
	} else {
		interactionImportanceIcon.css("color", "green");
		interactionImportanceContainer.css("border-color", "green");
		titleImportance = 'Normal';
	}
	if (interactionComplete == '1') {  //true
		titleState = "Complete";
		interactionImportanceIcon.html("<i class='fas fa-check'>");
	} else {  //completed
		titleState = "In Process";
		interactionImportanceIcon.html("<i class='fas fa-heart-rate'>");
	}
	interactionImportanceContainer.attr("title", titleState + "/" + titleImportance);
}

function pte_make_interaction_panel(uxMeta, rowNumber) {

	console.log("UXMETA");
	console.log(uxMeta);

	if (uxMeta == null) {
		uxMeta = {};
	}

	//TODO To and From are absolute based on if sent or received.
	var interactionToFromString = uxMeta.interaction_to_from_string;
	var interactionToFromName = uxMeta.interaction_to_from_name;
	var interactionRegarding = uxMeta.interaction_regarding;
	var interactionLink = uxMeta.interaction_link;
	var interactionTemplateName = uxMeta.interaction_template_name ? uxMeta.interaction_template_name + " - " : "";
	var interactionTypeNameStatus = uxMeta.interaction_type_name + " - " + interactionTemplateName + uxMeta.interaction_type_status;
  var interactionVaultLink = pte_make_interaction_vault_link(uxMeta);

	var toFrom = (typeof uxMeta.to_from != 'undefined' && uxMeta.to_from) ? uxMeta.to_from : 'NA';

	var topicName = (typeof uxMeta.topic_name != 'undefined' && uxMeta.topic_name) ?  uxMeta.topic_name : '';
	var interactingWithName = (typeof uxMeta.network_name != 'undefined' && uxMeta.network_name) ? "<span class='pte_interaction_label'>" + toFrom + " </span>" + uxMeta.network_name : '';

	var templateName = (typeof uxMeta.template_name != 'undefined' && uxMeta.template_name) ? uxMeta.template_name + " - " : '';
	var interactionComplete = (typeof uxMeta.interaction_complete != 'undefined' && uxMeta.interaction_complete) ? true : false;

	var createdDateHtml = {"cal_html": "", "time_html": ""};
	if (typeof uxMeta.created_date != 'undefined' && uxMeta.created_date) {
		  createdDateHtml = pte_make_date_html(uxMeta.created_date, "Created");
	}

	var revisitDateHtml = {"cal_html": "", "time_html": ""};   //TODO
	if (typeof uxMeta.expiration_minutes != 'undefined' && uxMeta.expiration_minutes) {
		var revisitDate = dayjs(uxMeta.modified_date).add(uxMeta.expiration_minutes, 'minute');
		    revisitDateHtml = pte_make_date_html(revisitDate, "Revisit");
	}

	var buttons = (typeof uxMeta.buttons != "undefined" && uxMeta.buttons) ? uxMeta.buttons : [];
	var html = "";

	var archiveButton = "<i data-pid='" + uxMeta.process_id  + "'onclick='event.stopPropagation(); pte_handle_file_away(this);' class='far fa-sparkles pte_interaction_panel_button " + (buttons['file'] && uxMeta.state == 'active' ? "pte_ipanel_button_enabled" : "pte_ipanel_button_disabled") + "' title='File Interaction Away'></i>";
	var interactionType = uxMeta.process_type_id;  //TODO get from database interaction types

	var backGroundSrc = alpn_templatedir + 'dist/assets/interaction_card_background_' + processColorMap[interactionType] + '.png';  //TODO consider making the filename same as interaction type

	html += "<div class='pte_interaction_background_container'><img class='interaction_background_image' src='" + backGroundSrc + "'></div>";
	html += "<div id='pte_importance_icon_" + rowNumber + "' class='pte_importance_bg'><div class='pte_importance_icon'></div></div>";
	if (interactionComplete) {
	} else {
	}
	html += "<div class='pte_interaction_card_title_container'>";
	html += "<div class='pte_interaction_card_title_inner_left'>" + interactionTypeNameStatus +"</div>";
	html += "</div>";

	html += "<div class='pte_interaction_card_line_1'>";
	html += "<div class='pte_interaction_card_line_1_inner_left'>" + interactionToFromString + "</div>";
	html += "<div class='pte_interaction_card_line_1_inner_right'>" + interactionToFromName  + "</div>";
	html += "</div>";

	html += "<div class='pte_interaction_card_line_2'>";
	html += "<div class='pte_interaction_card_line_2_inner_left'>Re</div>";
	html += "<div class='pte_interaction_card_line_2_inner_right'>";
	html += interactionRegarding;
	html += "</div>";
	html += "</div>";

	html += "<div class='pte_interaction_card_line_2_link'>";
	html += interactionVaultLink;
	html += "</div>";


	html += "<div class='pte_interaction_card_line_3'>";
	html += "<div class='pte_interaction_card_line_3_inner_left'>";
	html += createdDateHtml.cal_html + createdDateHtml.time_html;
	html += "</div>";
	html += "<div class='pte_interaction_card_line_3_inner_center'>";
	html += revisitDateHtml.cal_html + revisitDateHtml.time_html;
	html += "</div>";
	html += "<div class='pte_interaction_card_line_3_inner_right'>";
	html += archiveButton;
	html += "</div>";

	html += "</div'>";

	return html;
}

function pte_interactions_table() {

	console.log("Handling Interactions Table...");

	var formattedField = "";
	var pteControl, priority, titleImportance, titleState;
	var tableData = wpDataTables[alpn_activity_table_id].fnGetData();
	var uxMeta = {};

	//console.log(tableData);

	var rowCount = tableData.length;

	for (i = 0; i < rowCount; i++) {

		if (i == 0) {
			var first_row_id = tableData[i][0];
		}

		uxMeta = JSON.parse(tableData[i][3]);
		uxMeta.priority = tableData[i][5];
		uxMeta.state =  tableData[i][6];
		uxMeta.interaction_complete =  tableData[i][7];

		if (tableData[i][3]) {
			formattedField =  "<div class='pte_interaction_body'>" + pte_make_interaction_panel(uxMeta, i) + "</div>";
		} else {
			formattedField =  "<div class='pte_interaction_body'>here</div>";
		}

		pteControl = jQuery("[data-uid=" + tableData[i][0] + "]");
		pteControl.html(formattedField);

		var interactionImportanceContainer = jQuery('#pte_importance_icon_' + i);
		pte_update_interaction_importance(interactionImportanceContainer, uxMeta.priority, uxMeta.interaction_complete);

		pteControl.parent().click(
			function(){
				var processId = jQuery(this).find("div.alpn_interaction_cell").data('uid');
				pte_handle_interaction_selected(processId);
				pte_show_process_ux(processId);

		});
	}

	if (!rowCount) {
			console.log('Showing empty pte_show_process_ux');
			pte_show_process_ux('');
			pte_select_first_interaction = true;
			pte_selected_interaction_process_id = '';

	} else {

			if (pte_handle_interaction_skip_table_reselect) {
				console.log('pte_handle_interaction_skip_table_reselect');
				pte_handle_interaction_skip_table_reselect = false;
				pte_handle_interaction_selected(pte_selected_interaction_process_id);
				return;
			}

			if (pte_select_first_interaction) {
				console.log('pte_select_first_interaction');

				pte_handle_interaction_selected(first_row_id);
				pte_select_first_interaction = false;
				pte_show_process_ux(first_row_id);
				pte_selected_interaction_process_id = first_row_id;
			} else if (pte_selected_interaction_process_id)  {

				console.log('selecting based on pte_selected_interaction_process_id');

				pte_handle_interaction_selected(pte_selected_interaction_process_id);
				pte_show_process_ux(pte_selected_interaction_process_id);
			}
	}
}

function pte_select_new_topic_from_id(topicId, vaultData = {}) {
	console.log('Selecting Topic From ID...', topicId);
		var isVaultItem = (typeof vaultData.vault_id != "undefined") ? true : false;
		var security = specialObj.security;
		jQuery.ajax({
			url: alpn_templatedir + 'alpn_handle_get_topic_channel.php',
			type: 'GET',
			data: {
				index_type: "topic_id",
				record_id: topicId,
				security: security,
			},
			dataType: "json",
			success: function(json) {
				console.log('RETURNING FROM GET CHANNEL...');
				if (typeof json[0] != "undefined") {
					var topicData = json[0];
					var linkData = {
						"topic_special": topicData.special,
						"topic_dom_id": topicData.dom_id,
						"topic_id": topicData.topic_id,
						"topic_type_id": topicData.topic_type_id
					};
					if (isVaultItem) {
						linkData.operation = "vault_item";
						linkData.vault_id = vaultData.vault_id;
						linkData.vault_dom_id = vaultData.vault_dom_id;
					} else {
						linkData.operation = "topic_same";
					}
					pte_handle_interaction_link(linkData);
				} else {
					console.log('Topic Not Found...');
				}
			},
			error: function() {
				console.log('problem getting domId');
			//TODO
			}
		})

}

function pte_handle_select_template (formId, editorMode) {
	var security = specialObj.security;
	jQuery.ajax({
		url: alpn_templatedir + 'pte_get_template_editor.php',
		type: 'POST',
		data: {
			form_id: formId,
			security: security,
			editor_mode: editorMode
		},
		dataType: "html",
		success: function(html) {
			console.log('Successfully got template editor...');
			jQuery('#template_editor_container').html(html);
		},
		error: function() {
			console.log('Error getting template editor...');
		//TODO
		}
	});
}

function pte_handle_select_topic_type (formId) {
	var security = specialObj.security;
	jQuery.ajax({
		url: alpn_templatedir + 'pte_get_topic_type_manager.php',
		type: 'POST',
		data: {
			form_id: formId,
			security: security,
		},
		dataType: "html",
		success: function(html) {
			console.log('Successfully got manager...');
			jQuery('#pte_topic_manager_inner').html(html);
		},
		error: function() {
			console.log('Error getting manager...');
		//TODO
		}
	});
}

function alpn_handle_topic_type_row_selected(formId) {
	pte_selected_report_template = "";
	if (pte_oldTopicTypeSelectedId) {
		var theOldRow =  jQuery("div.alpn_topic_type_cell[data-uid=" + pte_oldTopicTypeSelectedId + "]").closest('tr');
		jQuery(theOldRow).children().attr("style", "background-color: white !important;");
	}
	if (formId) {
		var theNewRow =  jQuery("div.alpn_topic_type_cell[data-uid=" + formId + "]").closest('tr');
		if (theNewRow.length) {
			theNewRow.children().attr("style", "background-color: #D8D8D8 !important;");
			if (typeof pte_template_editor_loaded != "undefined" && pte_template_editor_loaded) {
					var templateEditorMode = 'message';
					var templateEditorModeData = jQuery('#alpn_select2_template_type').select2('data');
					if (typeof templateEditorModeData != "undefined") {
						var templateEditorMode = templateEditorModeData[0].id;
					}
					pte_handle_select_template(formId, templateEditorMode);
			}
			if (typeof pte_topic_manager_loaded != "undefined" && pte_topic_manager_loaded) {
				pte_handle_select_topic_type(formId);
			}
			pte_oldTopicTypeSelectedId =	formId;
		}
	} else {
		pte_oldTopicTypeSelectedId =	'';
	}
}

function alpn_handle_topic_type_table() {

	console.log("alpn_handle_topic_type_table");

	var table = wpDataTables.table_topic_types;
	var tableData = table.fnGetData();

	var firstReady = '';
	var rowData = {};
	var ownerId, topicTypeId, typeKey, schemaKey, icon, iconHtml, titleHtml, descHtml, friendlyName, formId, description;

	if (tableData.length) {
		for (i=0; i< tableData.length; i++) {
			rowData = tableData[i];
			topicTypeId =  rowData[0];
			ownerId = rowData[1];
			typeKey = rowData[2];
			schemaKey = rowData[3];
			icon = rowData[4];
			friendlyName = rowData[5];
			description = rowData[6] ? rowData[6].replace(/\\(.)/mg, "$1") : " - -";
			formId = rowData[7];

		//	var aboutValue = (tableData[i][6]) ? tableData[i][6].replace(/\\(.)/mg, "$1") : " - -";
			var formattedField = "<div class='pte_vault_details'>";

			iconHtml = "<i class='" + icon + "'></i>";
			titleHtml = "<div class='pte_vault_row pte_negative_margins pte_vault_border_left pte_vault_border_top pte_vault_border_right'><div id='pte_vault_name_content' class='pte_vault_row_75 pte_topic_types_background'>" + friendlyName + "</div><div class='pte_vault_row_25 pte_topic_types_icon'>" + iconHtml + "</div></div>";
			descHtml = "<div class='pte_vault_row pte_negative_margins pte_vault_border_left pte_vault_border_right'><div id='pte_vault_desc_content' class='pte_vault_row_100 pte_desc_padding pte_extra_margins'>" + description + "</div></div>";

			formattedField += titleHtml;
			formattedField += descHtml;
			formattedField += "</div>";
			alpnControl = jQuery("div.alpn_topic_type_cell[data-uid=" + formId + "]");
			alpnControl.html(formattedField);

			jQuery(alpnControl).click(function(){
				var formId = jQuery(this).data('uid');
				alpn_handle_topic_type_row_selected(formId);
			});
		}
		if (!pte_oldTopicTypeSelectedId) {
			var firstFormId = tableData[0][7];
			alpn_handle_topic_type_row_selected(firstFormId);
		} else {
			alpn_handle_topic_type_row_selected(pte_oldTopicTypeSelectedId);
		}
	}
}

function pte_date_to_js(sourceDateTime, destinationDivId, prefixString=''){
	var sourcedate = dayjs(sourceDateTime).utc(true).local();
	jQuery('#' + destinationDivId).html(prefixString + sourcedate.format('MMM D, YYYY, h:mma'));
}

function alpn_handle_vault_table() {

	const strWidth = 25;
	var table = wpDataTables.table_vault;
	var tableData = table.fnGetData();
	var firstReady = '';
	var ownerHtml, ownerName, titleHtml, fName, descHtml, addOwnerRow;
	//console.log(tableData);
	var pteSpacer = "<div class='pte_vault_row pte_spacer_height'></div>";

	if (tableData.length) {
		for (i=0; i< tableData.length; i++) {
			var ownerId = tableData[i][1];
			var lmdate = dayjs(tableData[i][4]);
			//var cdate = dayjs(tableData[i][3]);
			var mimeType = tableData[i][9];
			var aboutValue = (tableData[i][6]) ? tableData[i][6].replace(/\\(.)/mg, "$1") : " - -";
			var upload_state = tableData[i][14];
			var dom_id = tableData[i][11];
			var access_level = tableData[i][2];
			if ((firstReady == '') && (upload_state == 'ready')) {
				firstReady = tableData[i][11];
			}
			var waiting_line = '';
			if (upload_state != 'ready') {
				waiting_line += "<div id='waiting_indicator_row' class='pte_negative_margins pte_vault_border_left pte_vault_border_right pte_vault_right pte_field_padding_right'>";
				waiting_line += "<img src='" + alpn_templatedir + "ellipsisindicator.gif'>";
				waiting_line += "</div>";
			}
				if (typeof pte_supported_types_map[mimeType] !== "undefined" ) {
					var docType = pte_supported_types_map[mimeType];
				} else {
					var docType = "";
				}
				docType = docType ? docType : "&nbsp;"

			ownerHtml = '';
			ownerName = '';
			if (ownerId != alpn_user_id) {
				ownerName = tableData[i][16];
			}
			addOwnerRow = ownerName ? "<div class='pte_vault_row pte_vault_border_top pte_negative_margins pte_vault_border_left pte_vault_border_right'><div class='pte_vault_row_100 pte_vault_text_small pte_cell_padding pte_vault_centered pte_vault_link' style='vertical-align: middle;'><i id='' class='far fa-user'></i>&nbsp;&nbsp;" + ownerName + "</div></div>" : '';
			ownerHtml = "<div class='pte_vault_row pte_vault_border_all pte_negative_margins'><div class='pte_vault_row_50 pte_vault_text_small pte_cell_padding pte_vault_centered'>" + lmdate.format('MMM D, YYYY, h:mma') + "</div><div class='pte_vault_row_25 pte_vault_text_small pte_vault_border_left pte_vault_centered'>" + docType + "</div><div class='pte_vault_row_25 pte_vault_text_small pte_vault_border_left pte_vault_centered' id='pte_vault_permission_content'>" + access_levels[access_level] + "</div></div>"

			var formattedField = "<div class='pte_vault_details'>";
				fName = tableData[i][7].replace(/\\(.)/mg, "$1");
				fName = fName.replace(/\.[^/.]+$/, "");  //Remove extension TODO not sure it's the right thing to do. New thoughts - What do we do if we don't know the file extension. Octet stuff..
				titleHtml = "<div class='pte_vault_row pte_negative_margins pte_vault_border_left pte_vault_border_top pte_vault_border_right'><div id='pte_vault_name_content' class='pte_vault_row_100 pte_vault_background pte_vault_3px pte_extra_margins pte_vault_text_large'>" + fName + "</div></div>";
				descHtml = "<div class='pte_vault_row pte_negative_margins pte_vault_border_left pte_vault_border_right'><div id='pte_vault_desc_content' class='pte_vault_row_100 pte_desc_padding pte_extra_margins'>" + aboutValue + "</div></div>";
				formattedField += titleHtml;
				formattedField += waiting_line;
				formattedField += descHtml;
				formattedField += addOwnerRow;
				formattedField += ownerHtml;
				formattedField += pteSpacer;
				formattedField += "</div>";
			alpnControl = jQuery("[data-uid=" + tableData[i][11] + "]");
			alpnControl.html(formattedField);
			if (upload_state != 'ready') {
				alpnControl.attr("style", "opacity: 0.5; pointer-events: none;");
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
			if (pte_global_vault_item_dom_id) {
				alpn_handle_vault_row_selected(pte_global_vault_item_dom_id);
				pte_global_vault_item_dom_id = '';
			} else {
				alpn_handle_vault_row_selected(alpn_oldVaultSelectedId);
			}
		}
	}
}

function pte_start_chat(indexType, recordId){
	//TODO start chat on topic changes but not mode changes.

	console.log('STARTING CHAT...');

	var security = specialObj.security;
	jQuery.ajax({
		url: alpn_templatedir + 'alpn_handle_get_topic_channel.php',
		type: 'GET',
		data: {
			index_type: indexType,
			record_id: recordId,
			security: security
		},
		dataType: "json",
		success: function(json) {
			if (typeof json[0] != "undefined") {
				var data = json[0];
				data['name'] = 'pte_chat_message';
				pte_message_chat_window(data);
			} else {
				console.log("Topic ID for Chat Not Found..." + recordId);
			}
		},
		error: function() {
			console.log('problem getting chatID');
		//TODO
		}
	})
}

function	pte_message_chat_window(data){
		var name = data.name;
		var chatBody = document.querySelector( "#alpn_chat_body" );
		if (chatBody) {
			chatBody.contentWindow.postMessage({ name, data }, "*" );
		}
}


function alpn_moveActivitySection(){ //Repositions alerts under topics on HD or less.

	var theSection = jQuery('#alpn_section_alert');
	var windowWidth = jQuery(window).width();
	var sectionParent = jQuery('#alpn_section_alert').parent();

	if (windowWidth <= 1280) {				//HD or Less
		if (sectionParent.hasClass('alpn_column_3')) {
			jQuery('.alpn_column_3').attr("style", "flex-basis: 0%;  margin-left: 0px !important; margin-right: 0px !important; min-width: unset !important;");
			jQuery('.alpn_column_2').attr("style", "flex-basis: 85%; margin-right: 0 !important;");
			jQuery('#alpn_section_alert').prependTo(".alpn_column_1");
		}
	}
	if (windowWidth >= 1440) {//Big enough
		if (sectionParent.hasClass('alpn_column_1')) {
			jQuery('.alpn_column_3').attr("style", "flex-basis: 20%; margin-left: 0px !important; margin-right: 10px !important; min-width: 240px !important;");
			jQuery('.alpn_column_2').attr("style", "flex-basis: 65%;  margin-right: 10px !important;");
			jQuery('#alpn_section_alert').prependTo(".alpn_column_3");
		}
	}
}

function alpn_resizeAll(){
	alpn_resizeChat()
	alpn_moveActivitySection();
}

function alpn_resizeChat() { //Centered on the window

	if (pte_external == false) {
		var windowWidth = jQuery(window).width();
		var panelWidth = jQuery('#alpn_chat_panel').width();
		var panelLeft = (windowWidth - panelWidth) / 2;

		// var containerLeft = jQuery('#alpn_main_container').position().left;
		// var containerWidth = jQuery('#alpn_main_container').parent().width();
		// var panelWidth = jQuery('#alpn_chat_panel').width();
		// var panelLeft = containerLeft + ((containerWidth - panelWidth) / 2);

		jQuery("#alpn_chat_panel").css('left', panelLeft);
	}
}

function pte_send_sync(activeChannelMeta){
	var notificationMember = activeChannelMeta.notifications_member_sync_id;
	if (typeof syncClient == "object") {
			//console.log("Syncing to ", notificationMember);
				syncClient.map(notificationMember).then(function(map){
					map.update('pte_video_room_event_notifications', activeChannelMeta)
				  .then(function(item) {
				    //console.log('Map Item update() successful, new value:', item.value);
				  })
				  .catch(function(error) {
				    console.error('Map Item update() failed', error);
  				});
				});
	}
}

function pte_handle_mute_audio(){
	var muteButtonIcon = jQuery("#pte_chat_audio_icon");
	var muteState = muteButtonIcon.hasClass('fa-microphone') ? 'unmuted' : "muted";
	if (muteState == 'unmuted') {
		var data = {"name": "pte_mute_current_channel"};
		pte_message_chat_window(data);
	} else {
		var data = {"name": "pte_unmute_current_channel"};
		pte_message_chat_window(data);
	}
}

function pte_handle_sync(data, item = false){

	  //console.log("Handling Sync");

	if (item) { //Migrate to this. I think. How it is supposed to work.

		var itemObj = item.item;
		var itemKey = itemObj.key;
		var itemData = itemObj.value;

		// console.log(itemObj);
		// console.log(itemKey);
		// console.log(itemData);


		switch(itemKey) {
			case 'pte_video_room_event_notifications':
				data.name = itemKey;
				pte_message_chat_window(data);
				return; //no need to go further
			break;
		}
	}

	var syncSection = data.sync_section;
	var syncPayload = data.sync_payload;

	//console.log("SYNCING SECTION - ", syncSection);

	switch(syncSection) {
		case 'proteam_card_update':
		 var topicStates = {'10': "Added", '20': "Invite Sent", '30': "Joined", '40': "Linked", '80': "Email Sent", '90': "Declined"};
			console.log("Handling ProTeam Card Update...");
			var ptId = syncPayload.proteam_row_id;
			var ptStatus = syncPayload.state;
			var ptStatusString = topicStates[ptStatus];
			var proTeamCard =  jQuery('div.proteam_user_panel[data-id=' + ptId + ']');
			var statusArea = proTeamCard.find('div#proTeamPanelUserData');
			var statusText = proTeamCard.find('span#pte_topic_state');
			statusArea.fadeOut('normal', function(){
					statusText.text(ptStatusString);
	        statusArea.fadeIn();
	    });
		break;

		case 'proteam_card_delete':
			console.log("Handling ProTeam Card Delete...");
			var ptId = syncPayload.proteam_row_id;
			var proTeamCard =  jQuery('div.proteam_user_panel[data-id=' + ptId + ']');
			proTeamCard.fadeOut(250, function(){
				proTeamCard.remove();
			});
		break;

		case 'interaction_recall':
			console.log("Handling interaction_recall...");

			pte_handle_interaction_recall(syncPayload);

		break;
		case 'interaction_item_update':
			console.log("Handling interaction_item_update...");

			var processId = syncPayload.process_id;
			var messageTitle = syncPayload.message_title;
			var messageBody = syncPayload.message_body;

			var updatedDate = dayjs(syncPayload.updated_date).utc(true).local().format('MMM D, YYYY h:mm A');
			var informationPanel = jQuery("div#pte_interaction_information_panel[data-pid=" + processId + "]");

			var informationPanelMessage = informationPanel.find('div.pte_updated_message');
			var informationPanelMessageTitle = informationPanel.find('input#pte_message_title_field');
			var informationPanelMessageTitleStatic = informationPanel.find('div#pte_message_title_field_static');
			var informationPanelMessageBody = informationPanel.find('textarea#pte_message_body_area');

			informationPanelMessage.html('Updated: ' + updatedDate).fadeIn();
			informationPanelMessageTitle.val(messageTitle);
			informationPanelMessageTitleStatic.html(messageTitle);
			informationPanelMessageBody.val(messageBody);
		break;
		case 'interaction_update':
			console.log("Handling interaction_update...");
			console.log(syncPayload);
			if (typeof syncPayload.restart_interaction != "undefined" && syncPayload.restart_interaction) {
				pte_selected_interaction_process_id = syncPayload.new_interaction_process_id;
			}
			if (typeof syncPayload.refresh_proteams != "undefined" && syncPayload.refresh_proteams) {
				var data = {
					"wp_id": syncPayload.connected_id,
					"dom_id": syncPayload.connected_network_dom_id,
					"text": syncPayload.network_name,
					"initial_proteam_panel_html": syncPayload.initial_proteam_panel_html
				};
				pte_add_to_proteam_table(data);
				alpn_setup_proteam_member_selector(syncPayload.proteam_member_row_id);
			}
			wpDataTables.table_interactions.fnFilterClear();
		break;
		case 'file_workflow_update':
			console.log("Handling file_workflow_update...");
			var payload = {
				'dom_id': syncPayload.dom_id
			};
			alpn_handle_file_submit(payload);
		break;

		case 'user_list_update':
			console.log("Handling user_list_update...");
			wpDataTables.table_interactions.fnFilterClear();
		break;
	}
	//console.log(syncPayload);
}

function pte_close_chat_panel(){
	jQuery('#alpn_chat_panel').css('bottom', '-405px');
	pte_chat_window_open = false;
	pte_message_chat_window({'name': 'pte_chat_window_closed'});
}

function pte_open_chat_panel(){
	jQuery('#alpn_chat_panel').css('bottom', '0px');
	pte_chat_window_open = true;
	pte_message_chat_window({'name': 'pte_chat_window_open'});
}

( function () {
	window.addEventListener( "message", ( event ) => {
		// console.log("RECEIVED MESSAGE FROM CHILD...");
		// console.log(event);
		var name = event.data.name;
		var activeChannelMeta = event.data.activeChannelMetaSnapshot;
		var channelName;

		switch(name) {
			case 'pte_handle_mute_button':
				var muteButtonText = jQuery("#alpn_chat_audio_mute_text");
				var muteButtonImage = jQuery("#pte_chat_mute_button");
				muteButtonImage.attr("src", alpn_templatedir + "dist/assets/button_off.png");
				muteButtonText.html("<i id='pte_chat_audio_icon' class='fas fa-microphone-slash'>");
				muteButtonText.css("color", '#444');
			break;
			case 'pte_handle_unmute_button':
				var muteButtonText = jQuery("#alpn_chat_audio_mute_text");
				var muteButtonImage = jQuery("#pte_chat_mute_button");
				muteButtonImage.attr("src", alpn_templatedir + "dist/assets/button_on.png");
				muteButtonText.html("<i id='pte_chat_audio_icon' class='fas fa-microphone'>");
				muteButtonText.css("color", 'yellow');
			break;

			case 'pte_handle_object_action':
				var objectData = activeChannelMeta.object_action_data;
				pte_select_new_topic_from_id(objectData.topic_id, objectData);
			break;
			case 'pte_send_notification_by_sync':
				pte_send_sync(activeChannelMeta);
			break;
			case 'pte_start_audio_wait':
				var waitIndicator = alpn_templatedir + "dist/assets/spinner_blue.gif";
				jQuery('#pte_chat_on_off_button').attr('src', waitIndicator);
				jQuery("#alpn_chat_audio_on_off_text").html("");
			break;
			case 'pte_handle_link':
				var linkOperation = activeChannelMeta.link_operation;
				switch(linkOperation) {
					case 'topic_same':
						console.log("SELECTING NEW TOPIC...");
						console.log(activeChannelMeta);
						pte_select_new_topic_from_id(activeChannelMeta.topic_id);
					break;
				}
			break;
			case 'pte_channel_stop':
				jQuery('#alpn_chat_audio_on_off').css({"opacity": "0.6", "pointer-events": "none"});
				jQuery('#alpn_chat_audio_mute').css({"opacity": "0.6", "pointer-events": "none"});
				jQuery("#pte_chat_topic_name").html('--');
				jQuery("#pte_chat_selected_unread").html('--');
				return;
			break;
			case 'pte_channel_started':
				jQuery('#alpn_chat_audio_on_off').css({"opacity": "1", "pointer-events": "auto"});
				jQuery('#alpn_chat_audio_mute').css({"opacity": "1", "pointer-events": "auto"});

				console.log(activeChannelMeta);

				//if (alpn_user_id == activeChannelMeta.topic_owner_id) {
				if (true) {   //what heppened to topic_owner_givenname
					jQuery("#pte_chat_topic_name").html(activeChannelMeta.topic_name);
				} else {
					channelName = activeChannelMeta.topic_name + " (" + activeChannelMeta.topic_owner_givenname + ")"
					jQuery("#pte_chat_topic_name").html(channelName);
				}

				if (activeChannelMeta.message_count * 1) {
					var unreadCount = activeChannelMeta.message_count;
				} else {
					var unreadCount = "--";
				}
				jQuery("#pte_chat_selected_unread").html(unreadCount);

				return;
			break;
			case 'pte_audio_started':
				jQuery("#pte_chat_on_off_button").attr("src", alpn_templatedir + "dist/assets/button_on.png");
				jQuery("#alpn_chat_audio_on_off_text").html("ON").css("color", "yellow");
			break;
			case 'pte_audio_ended':
				jQuery("#pte_chat_on_off_button").attr("src", alpn_templatedir + "dist/assets/button_off.png");
				jQuery("#alpn_chat_audio_on_off_text").html("OFF").css("color", "#444");
			break;
			case 'pte_update_chat_total':
				jQuery("#pte_chat_total_unreads").html(activeChannelMeta.chat_total_unreads);
				return;
			break;
			case 'open_file':
				var pte_chrome_extension_data = event.data.data;
				alpn_wait_for_ready(10000, 250,  //Network Table
					function(){
						if (typeof pte_external !== "undefined") {
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
					},
					function(){ //Handle Error
						console.log("Error Loading Open File Network Table..."); //TODO Handle Error
					});
			break;
		}
	});
window.parent.postMessage({ name: "app_ready" }, "*" );
} () );


function iformat(icon) {
    var originalOption = icon.element;
    return '<i class="far ' + jQuery(originalOption).data('icon') + ' alpn_icon_topic_list"></i>' + icon.text;
}


function pte_setup_window_onload() {

	if ((typeof alpn_user_id != "undefined") && (alpn_user_id > 0)) {	//Must be logged in

				console.log('WINDOW ON LOADED..');
				if (pte_external == false) {   //Initialize Mission Control
					//Setup Sync
					jQuery.getJSON(alpn_templatedir +  'chat/token.php', {
						device: 'browser'
					}, function(data) {
							userContext = {identity: data.identity};

							if (typeof syncClient != "object") {
								syncClient = new Twilio.Sync.Client(data.token, { logLevel: 'info' });
							}

							syncClient.map(alpn_sync_id).then(function (map) {
								map.on('itemAdded', function(item) {
									var descriptor = item.item.descriptor;
									var data = descriptor.data;
									pte_handle_sync(data, item);
								});
								map.on('itemUpdated', function(item) {
									var descriptor = item.item.descriptor;
									var data = descriptor.data;
									pte_handle_sync(data, item);
								});
							});

							syncClient.on('tokenAboutToExpire', function() {
								console.log("CLIENT TOKEN ABOUT TO EXPIRE");

								jQuery.getJSON(alpn_templatedir +  'chat/token.php', {
									device: 'browser'
								}, function(data1) {
									console.log("Updating Twilio Token");
									syncClient.updateToken(data1.token);
								});
							});

							syncClient.on('connectionStateChanged', function(state) {
								console.log("SYNC CLIENT -- CONNECTION STATE CHANGED");
								if (state != 'connected') {
									//console.log("Sync Client Connected");
								} else {
									//console.log("Sync Client Not Connected");

								}
							});
					});

					if (jQuery('#alpn_section_alert .wpdt-c :input')[2]) {
						var alpn_activity_table_obj = JSON.parse(jQuery('#alpn_section_alert .wpdt-c :input')[2].value);
						alpn_activity_table_id = alpn_activity_table_obj.tableId
					}

					alpn_moveActivitySection(); //place activity in column based on window width

					jQuery('#alpn_selector_topic_type').select2({
						theme: "bootstrap",
						width: '137px',
						allowClear: false,
						templateSelection: iformat,
						templateResult: iformat,
						escapeMarkup: function(text) {
							return text;
						}
					});

					alpn_wait_for_ready(10000, 250,  //Network Table
						function(){
							if (pte_external == false  && wpDataTables.table_network !== "undefined") {
									console.log("FOUND TABLE");
									return true;
							}
							return false;
						},
						function(){
							wpDataTables.table_network.addOnDrawCallback( function(){
								alpn_handle_topic_table('network');
							})
							alpn_handle_topic_table('network');
							alpn_prepare_search_field("#table_network_filter");
							wpDataTables.table_network.fnSettings().oLanguage.sZeroRecords = 'No Network Connections';
							wpDataTables.table_network.fnSettings().oLanguage.sEmptyTable = 'No Network Connections';
						},
						function(){ //Handle Error
							console.log("Error Loading Network Table..."); //TODO Handle Error
						});

					alpn_wait_for_ready(10000, 250,  //Topic Table
						function(){
							if (pte_external == false  && wpDataTables.table_topic !== "undefined") {
								console.log("FOUND TABLE");

									return true;
							}
							return false;
						},
						function(){
							wpDataTables.table_topic.addOnDrawCallback( function(){
								alpn_handle_topic_table('topic');
							})
							alpn_handle_topic_table('topic');
							alpn_prepare_search_field("#table_topic_filter");
							wpDataTables.table_topic.fnSettings().oLanguage.sZeroRecords = 'No Topics';
							wpDataTables.table_topic.fnSettings().oLanguage.sEmptyTable = 'No Topics';

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
							console.log("Error Loading Table Topic..."); //TODO Handle Error
						});

						alpn_wait_for_ready(10000, 250,  //Interaction table
							function(){ //Something to check
								if (pte_external == false && wpDataTables[alpn_activity_table_id] !== "undefined") {
									if (wpDataTables[alpn_activity_table_id].fnGetData().length !== "undefined") {
										return true;
									}
								}
								return false;
							},
							function(){ //Handle Success
								console.log("Success about to init interaction stuff..."); //TODO Handle Error
								wpDataTables[alpn_activity_table_id].fnSettings().oLanguage.sZeroRecords = 'No Interactions';
								wpDataTables[alpn_activity_table_id].fnSettings().oLanguage.sEmptyTable = 'No Interactions';
								wpDataTables[alpn_activity_table_id].addOnDrawCallback( function(){
									pte_interactions_table();
								})
								pte_interactions_table();
								alpn_prepare_search_field("#table_interactions_filter");
							jQuery("#pte_interaction_table_filter_container").insertBefore('#table_interactions_filter');
							jQuery('#pte_interaction_table_filter').select2({
								theme: "bootstrap",
								width: '168px',
								allowClear: false
							});
							},
							function(){ //Handle Error
								console.log("Error Loading Interactions..."); //TODO Handle Error
							});

							var initialTopicData = pte_make_map_data(jQuery(".alpn_user_container").data("uid"), jQuery(".alpn_user_container").data("topic-id"), 0, -1, "user");
							pte_handle_interaction_link(initialTopicData);

							jQuery("#alpn_selector_container_left").insertBefore('#table_network_filter');
							jQuery('#alpn_selector_network').select2({
								theme: "bootstrap",
								width: '137px',
								placeholder: "Filter...",
								allowClear: true
							});

							jQuery('#alpn_chat_body').attr('src', (alpn_templatedir + "chat/index.php"));
							alpn_resizeChat();

							jQuery( window ).resize(function(){ //Move things on resize
								alpn_resizeAll();
							});

							//Setup Slider Chat Panel

							jQuery('#alpn_chat_panel').click(function() {
								if (jQuery('#alpn_chat_panel').css('bottom') == '0px') {
									pte_close_chat_panel();
								} else {
									pte_open_chat_panel();
								}
							});
				}

				// Initialize Topic Manager
				if (typeof pte_template_editor_loaded != "undefined" && pte_template_editor_loaded) {

						console.log("Starting to Initialize Topic Types..."); //TODO Handle Error
						alpn_wait_for_ready(10000, 250,  //Topic Table
							function(){
								if (wpDataTables.table_topic_types !== "undefined") {
									return true;
								}
								return false;
							},
							function(){
								console.log(wpDataTables.table_topic_types); //TODO Handle Error
								wpDataTables.table_topic_types.addOnDrawCallback( function(){
									alpn_handle_topic_type_table();
								})
								alpn_handle_topic_type_table();
								alpn_prepare_search_field("#table_topic_types_filter");
								wpDataTables.table_topic_types.fnSettings().oLanguage.sZeroRecords = 'No Topic Types';
								wpDataTables.table_topic_types.fnSettings().oLanguage.sEmptyTable = 'No Topic Types';
							},
							function(){ //Handle Error
								console.log("Error Initializing Topic Type Table..."); //TODO Handle Error
							});
				}

				//Initialize Topic  Editor
				if (typeof pte_topic_manager_loaded != "undefined" && pte_topic_manager_loaded) {

					console.log("Starting to Initialize Topic Types..."); //TODO Handle Error
					alpn_wait_for_ready(10000, 250,  //Topic Table
						function(){
							if (wpDataTables.table_topic_types !== "undefined") {
								return true;
							}
							return false;
						},
						function(){
							console.log(wpDataTables.table_topic_types); //TODO Handle Error
							wpDataTables.table_topic_types.addOnDrawCallback( function(){
								alpn_handle_topic_type_table();
							})
							alpn_handle_topic_type_table();
							alpn_prepare_search_field("#table_topic_types_filter");
							wpDataTables.table_topic_types.fnSettings().oLanguage.sZeroRecords = 'No Topic Types';
							wpDataTables.table_topic_types.fnSettings().oLanguage.sEmptyTable = 'No Topic Types';
						},
						function(){ //Handle Error
							console.log("Error Initializing Topic Type Table..."); //TODO Handle Error
						});
				}
	}

}

jQuery( document ).ready( function(){


	console.log("DOC READY");
	setTimeout(function(){ alert("Hello"); }, 3000);
	pte_external =  pte_chrome_extension || pte_topic_manager_loaded || pte_template_editor_loaded;
	

	window.onload = function() {
			console.log("WORKED ONLOAD SETUP");
			pte_setup_window_onload();
	}

	if (!pte_external) {pte_get_active_video_rooms();}

	if (history.scrollRestoration) {
	  history.scrollRestoration = 'manual';
	}

	dayjs.extend(window.dayjs_plugin_utc);

	jQuery.fn.bindFirst = function(name, fn) {   //Makes an eventt trigger first. TODO are we still using this?
	  var elem, handlers, i, _len;
	  this.bind(name, fn);
	  for (i = 0, _len = this.length; i < _len; i++) {
	    elem = this[i];
	    handlers = jQuery._data(elem).events[name.split('.')[0]];
	    handlers.unshift(handlers.pop());
	  }
	};

	window.onpopstate = function (event) {  //Handle Back Button
		var state = event.state;
		if (state) {
				//console.log('Back Button Pressed...');
				pte_back_button = true;
				state.back_button = true;
				//console.log(state);
				pte_handle_interaction_link(state);     //fromBackButton is true so this won't be pushed on history again.
		}
	}

	jQuery.fn.extend({   //Waits until donetyping on a field then triggers event
			donetyping: function(callback,timeout){
					timeout = timeout || 1e3; // 1 second default timeout
					var timeoutReference,
							doneTyping = function(el){
									if (!timeoutReference) return;
									timeoutReference = null;
									callback.call(el);
							};
					return this.each(function(i,el){
							var $el = jQuery(el);
							$el.is(':input') && $el.on('keyup keypress paste',function(e){
									if (e.type=='keyup' && e.keyCode!=8) return;
									if (timeoutReference) clearTimeout(timeoutReference);
									timeoutReference = setTimeout(function(){
											doneTyping(el);
									}, timeout);
							}).on('blur',function(){
									doneTyping(el);
							});
					});
			}
	});

	if (typeof alpn_templatedir != "undefined" && alpn_templatedir) {

		Dropzone.autoDiscover = false;
		jQuery("#pte_chat_dropzone").dropzone({
				uploadMultiple: false,
			  addedfile: function (file) {
					console.log("DROPPED ON CHAT"),
					console.log(file);
					this.removeFile(file);
					jQuery("#pte_chat_dropzone").hide();
					jQuery("#pte_topic_dropzone").hide();
				},
			  url: alpn_templatedir + 'pte_donotdelete.php',
        addRemoveLinks: true
    });
		jQuery("#pte_topic_dropzone").dropzone({
				uploadMultiple: false,
				addedfile: function (file) {
					console.log("DROPPED ON TOPIC"),
					console.log(file);
					this.removeFile(file);
					jQuery("#pte_chat_dropzone").hide();
					jQuery("#pte_topic_dropzone").hide();
				},
				url: alpn_templatedir + 'pte_donotdelete.php',
				addRemoveLinks: true
		});

		draggedFile = false;
		document.ondragenter = (e) => {
		    if(!draggedFile) {
		        draggedFile = true;
						jQuery("#pte_chat_dropzone").show();
						jQuery("#pte_topic_dropzone").show();
					}
		}
		document.ondragleave = (e) => {
		    if (!e.fromElement && draggedFile) {
		        draggedFile = false;
						jQuery("#pte_chat_dropzone").hide();
						jQuery("#pte_topic_dropzone").hide();
	    }
		}

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


function alpn_handle_file_submit(payload) {
	var domId = payload.dom_id;
	jQuery('#alpn_field_' + domId).attr("style", "opacity: 1.0; pointer-events: auto;").find('#waiting_indicator_row').remove();
	jQuery('#pte_about_row_' + domId).attr("style", "display: table-row; opacity: 1.0;");
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

	if (pte_chrome_extension != "undefined" && pte_chrome_extension == true) {  //Get topicId from Drop Down Selection in Extension
		var selectedItem = jQuery('#pte_extension_topic_select').find(':selected');
		if (selectedItem.length) {
		 	topicId = selectedItem[0].value;
		}
	}
	var security = specialObj.security;
	jQuery.ajax({
		url: alpn_templatedir + 'alpn_handle_vault_files_start.php',
		type: 'POST',
		data: {
			topicId: topicId,
			description: description,
			permissionValue: permissionValue,
			security: security,
			pte_file_data: pte_file_data
		},
		dataType: "json",
		success: function(json) {

			if (pte_external == false) { // Uses same file workflow as extension so special case.
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
					file['meta']['pte_uid'] = pte_UUID();
					file['meta']['pte_source'] = file.source;
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

function pte_uppy_topic_logo_uppyeditor(){

	if (pte_external == false) {

		var fileCounter = 0;
		var allowedFileTypes = ['image/jpeg', 'image/jpg',	'image/png', 'image/xvg+xml'];

		jQuery('#pte_profile_logo_selector').empty();

		var fileCounter = 0;
		var uppyTopicLogo = Uppy.Core({
			id: "pte_profile_logo_selector",
		  debug: true,
		  autoProceed: false,
			allowMultipleUploads: false,
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
	  					height: "390px",
							proudlyDisplayPoweredByUppy: false,
							showProgressDetails: true,
							showLinkToFileUploadResult: false,
							animateOpenClose: false,
							metaFields: [
    							{ id: 'name', name: 'Name', placeholder: 'file name' }
  						]
	 	    })
				.use(Uppy.ImageEditor, {
					target: Uppy.Dashboard,
					id: 'pteImageEditor',
					quality: 0.8,
					cropperOptions: {
						dragMode: 'none',
						viewMode: 0,
						background: false,
						autoCropArea: 0.75,
						autoCrop: 1,
						initialAspectRatio: 1,
						aspectRatio: 1,
						responsive: true,
						movable: false,
						rotatable: true,
						scalable: true,
						zoomOnTouch: false,
						zoomOnWheel: false
					}
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

/*

.use(Uppy['image-editor'], {
	quality: 0.8,
	cropperOptions: {
		viewMode: 1,
		background: false,
		autoCropArea: 1,
		responsive: true
	}
})


*/


function pte_uppy_topic_logo(){

	if (pte_external == false && jQuery('#pte_profile_logo_selector').length) {

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
									file['data'] = output.file;
	 								file['handledByDoka'] = true;
	 								uppyTopicLogo.addFile(file);
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
						 file['data'] = output.file;
						 file['handledByDoka'] = true;
						 uppyTopicLogo.addFile(file);
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

	if (pte_external == false && jQuery("#pte_profile_image_selector").length) {

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
									file['data'] = output.file;
									file['isRemote'] = false;
	 								file['handledByDoka'] = true;
	 								uppyTopicIcon.addFile(file);
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
							file['data'] = output.file;
							file['handledByDoka'] = true;
							uppyTopicIcon.addFile(file);
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

if (pte_external == false) {

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
			file['meta']['pte_uid'] = pte_UUID();
			file['meta']['pte_source'] = file.source;
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
.use(Uppy.Transloadit, {  //TODO Filter application/octet-stream mimetype. Crashes Transloadit
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
						template_id: 'b51ccbe1760d410c8cf9b409228e6139'   //DEV TEMPLATE
 				    //template_id: '3b83f38410d744caa3060af90cd64bc0'  //PROD TEMPLATE TODO-PICK BASED ON ACTUAL
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

function pte_scroll_tab(scrollDirection){ //TODO while mouse down scroll
	var tabBar = jQuery("#pte_tab");
	if (scrollDirection == 'left') {
		tabBar.scrollLeft(tabBar.scrollLeft() - 30);
	}
	if (scrollDirection == 'right') {
		tabBar.scrollLeft(tabBar.scrollLeft() + 30);
	}
}

function pte_handle_tab_bar_scroll(){
	//console.log('pte_handle_tab_bar_scroll');

	var pte_tab_bar = jQuery("#pte_tab");
	var pte_tab_bar_width = (typeof pte_tab_bar[0] != "undefined") ? pte_tab_bar[0].scrollWidth : 0;
	var pte_tab_bar_scrollLeft = pte_tab_bar.scrollLeft();
	var pte_tab_bar_wrapper = jQuery('#pte_tab_wrapper');
	var pte_tab_bar_wrapper_width = pte_tab_bar_wrapper.width();

	var scrollableLeft = pte_tab_bar_scrollLeft > 0;
	var scrollableRight = (pte_tab_bar_width - pte_tab_bar_scrollLeft - 1) > pte_tab_bar_wrapper_width;

	var leftArrow = jQuery('#pte_tab_bar_left_arrow');
	var rightArrow = jQuery('#pte_tab_bar_right_arrow');

	if (scrollableLeft && leftArrow.hasClass('pte_ipanel_button_disabled')) {
		leftArrow.removeClass('pte_ipanel_button_disabled');
		leftArrow.addClass('pte_ipanel_button_enabled');
	}

	if (scrollableLeft) {
		if (leftArrow.hasClass('pte_ipanel_button_disabled')) {
 			leftArrow.removeClass('pte_ipanel_button_disabled');
 			leftArrow.addClass('pte_ipanel_button_enabled');
 		}
	} else {
		leftArrow.removeClass('pte_ipanel_button_enabled');
		leftArrow.addClass('pte_ipanel_button_disabled');
	}

	if (scrollableRight) {
		if (rightArrow.hasClass('pte_ipanel_button_disabled')) {
 			rightArrow.removeClass('pte_ipanel_button_disabled');
 			rightArrow.addClass('pte_ipanel_button_enabled');
 		}
	} else {
		rightArrow.removeClass('pte_ipanel_button_enabled');
		rightArrow.addClass('pte_ipanel_button_disabled');
	}
}

function alpn_file_add () {
	//alpn_manage_vault_buttons('');
	alpn_handle_vault_row_selected('');
	alpn_switch_panel ('add_edit');
	pte_uppy_vault_file();
}

function pte_handle_tab_selected(theObj){
	if (pte_selected_topic_tab && pte_selected_topic_tab_content) {
		var oldSelectedTab = jQuery(pte_selected_topic_tab);
		var oldSelectedTabContent = jQuery(pte_selected_topic_tab_content);
		oldSelectedTab.removeClass('pte_tab_button_active');
		oldSelectedTabContent.hide();
	}
	var selectedTab = jQuery(theObj);
	selectedTab.addClass('pte_tab_button_active');
	var tabId = selectedTab.data('tab-id');
	if (typeof history.state != "undefined" && history.state) {
		var oldHistory = history.state;
		oldHistory.tab_id = tabId;
		history.replaceState(oldHistory, null, "");
	}

	pte_selected_topic_tab = '#tab_' + tabId;
	pte_selected_topic_tab_content = '#tabcontent_' + tabId;
	var selectedTabContent = jQuery(pte_selected_topic_tab_content);
	selectedTabContent.show();
}

function pte_create_new_vault_url() {

	console.log('pte_create_new_vault_url');

	var linkAbout = jQuery('#link_interaction_about').val();
	var linkPassword = jQuery('#link_interaction_password').val();

	if (alpn_oldVaultSelectedId) {
		trObj =  jQuery('#alpn_field_' + alpn_oldVaultSelectedId).closest('tr');
		if ((typeof wpDataTables !== "undefined") && trObj) {
			rowData = wpDataTables.table_vault.fnGetData(trObj);
			var vaultId = rowData[0];
		}
	} else {
		console.log('Error. Should not Happen');
	}

	var linkExpirationSelect = jQuery('#alpn_select2_small_link_expiration_select_work_area');
	var linkExpirationSelectData = linkExpirationSelect.select2('data');
	if (typeof linkExpirationSelectData != 'undefined' && typeof linkExpirationSelectData[0] != 'undefined') {
		var linkExpiration = linkExpirationSelectData[0].id;
	}

	var linkOptionsSelect = jQuery('#alpn_select2_small_link_options_select_work_area');
	var linkOptionsSelectData = linkOptionsSelect.select2('data');
	if (typeof linkOptionsSelectData != 'undefined' && typeof linkOptionsSelectData[0] != 'undefined') {
		var linkOptions = linkOptionsSelectData[0].id;
	}

	var data = {
		'link_about': linkAbout,
		'link_password': linkPassword,
		'link_expiration': linkExpiration,
		'link_options': linkOptions,
		'link_about': linkAbout,
		'vault_id': vaultId,
		'link_meta': ''
	}

		jQuery.ajax({
			url: alpn_templatedir + 'pte_create_new_vault_url.php',
			type: 'POST',
			data: {
				"data": data
			},
			dataType: "json",
			success: function(json) {
					//console.log(json);
					pte_get_vault_links();
			},
			error: function(json) {
				console.log("Failed creating new vault url...");
				//TODO handle
			}
		})
}

function pte_set_work_area_html(areaType) {
	if (areaType == 'add-edit') {
		var workAreaHtml = " \
				<div class='pte_vault_row'> \
						<div class='pte_vault_row_67 pte_vault_text_xlarge pte_vault_bold'> \
							<span id='alpn_name_field_label'>Name</span> \
							<div class='pte_field_padding_right'><input id='alpn_name_field' placeholder='From Upload'></div> \
						</div> \
						<div class='pte_vault_row_33 pte_vault_text_xlarge pte_field_padding_right'> \
							<span class='pte_vault_bold'>Required Access Level</span> \
							<select id='alpn_selector_sharing' class='alpn_selector_sharing'> \
  							<option value='10'>General</option> \
								<option value='20'>Restricted</option> \
								<option value='40'>Private</option> \
							</select> \
						</div> \
				</div> \
				<div class='pte_vault_row pte_row_top_margin'> \
						<div class='pte_vault_row_67 pte_vault_text_xlarge pte_vault_bold pte_field_padding_right'> \
							Description \
							<textarea id='alpn_about_field' placeholder='Describe your vault entry so it can be easily found...' class='pte_field_padding_right'></textarea> \
						</div> \
						<div class='pte_vault_row_33 pte_vault_text_xlarge pte_field_padding_right'> \
						</div> \
				</div> \
						";
	} else { //Links
		var workAreaHtml = " \
						<div class='pte_vault_row pte_row_top_margin pte_vault_bold pte_vault_text_xlarge'> \
							<div class='pte_vault_row_70 pte_field_padding_right'> \
								xLinks \
							</div> \
							<div class='pte_vault_row_30 pte_vault_right'> \
							<i class='far fa-plus-circle pte_topic_link' style='font-size: 16px;' title='Create a New xLink' onclick='pte_create_new_vault_url();'></i> \
							</div> \
						</div> \
						<div class='pte_vault_row pte_vault_text_xlarge'> \
						<div class='pte_vault_row_70 pte_field_padding_right pte_vault_maxwidth_7-'> \
						<div class='pte_vault_row'><div class='pte_vault_row_40 pte_vault_row_border_left pte_vault_row_border_top pte_vault_row_border_right pte_extra_margins'>About</div><div class='pte_vault_row_20 pte_vault_centered pte_vault_row_border_top pte_vault_row_border_right'><i class='far fa-tasks' title='Permissions'></i></div><div class='pte_vault_row_20 pte_vault_centered pte_vault_row_border_top pte_vault_row_border_right'><i class='far fa-stopwatch' title='Expiration'></i></div><div class='pte_vault_row_20 pte_vault_centered pte_vault_row_border_top pte_vault_row_border_right'><i class='far fa-key' title='Passcode'></i></div></div> \
						<div id='pte_links_table'></div> \
						</div> \
						<div id='pte_link_widgets' class='pte_vault_row_30 pte_vault_maxwidth_30'> \
						</div> \
				</div> \
						";

	}

	jQuery('#alpn_vault_work_area').html(workAreaHtml);

}

function pte_set_work_area(operation) {

	switch(operation) {
		case 'add-edit':
			var fileName = '';
			var formAbout = '';
			var permissionValue = '40';
			if (alpn_oldVaultSelectedId) {
				trObj =  jQuery('#alpn_field_' + alpn_oldVaultSelectedId).closest('tr');
				if ((typeof wpDataTables !== "undefined") && trObj) {
					rowData = wpDataTables.table_vault.fnGetData(trObj);
					fileName = rowData[7];
					formAbout = rowData[6];
					permissionValue = rowData[2];
				}
			}
			pte_set_work_area_html('add-edit');
			jQuery('#alpn_selector_sharing').select2({
				theme: "bootstrap",
				width: '100%',
				allowClear: false,
				minimumResultsForSearch: -1
			}).on("select2:select", function (e) {
				pte_save_vault_meta();
			});
			jQuery('#alpn_name_field').val(fileName).donetyping(function(){
				pte_save_vault_meta();
			});
			jQuery('#alpn_about_field').val(formAbout).donetyping(function(){
				pte_save_vault_meta();
			});
			jQuery('#alpn_selector_sharing').val(permissionValue).trigger('change');

		break;
		case 'links':
			pte_set_work_area_html('links');
		break;
	}
}

function alpn_toggle_vault_work_area(){
	var area_dom = '#alpn_vault_work_area';
	if (jQuery(area_dom).height() == '0'){
		jQuery(area_dom).height('125px');
	} else {
		jQuery(area_dom).height('0px');
	}
}

function alpn_open_vault_work_area(){
	var area_dom = '#alpn_vault_work_area';
	if (jQuery(area_dom).height() == '0'){
		jQuery(area_dom).height('125px');
	}
}

function alpn_close_vault_work_area(){
	var area_dom = '#alpn_vault_work_area';
	if (jQuery(area_dom).height() == '125'){
		jQuery(area_dom).height('0px');
		pdfui.redraw();
	}
}

function pte_handle_delete_response(response, theObject) {

var security = specialObj.security;

if (response == 'yes' && typeof theObject !== "undefined") {

	var vaultId = theObject['vault_id'];

	jQuery.ajax({
		url: alpn_templatedir + 'alpn_handle_vault_delete_row.php',
		type: 'POST',
		data: {
			vault_id: vaultId,
			security: security,
		},
		dataType: "json",
		success: function(json) {
			alpn_set_vault_to_first_row = true;
			wpDataTables.table_vault.fnFilter();
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
	if (pte_message_to_clear) {
		clearTimeout(pte_message_to_clear);
		pte_message_to_clear = '';
	}
	jQuery("#alpn_message_area").attr('style', "opacity: 0; pointer-events: none; cursor: auto;");
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
		typeStr += "<div class='pte_message_confirmation_container'><i class='far fa-check-circle pte_message_confirmation_icon' title='Confirm Delete' onclick='" + handler + "(\"yes\", " + parms + ");'></i><i title='Cancel' class='far fa-times-circle pte_message_confirmation_icon' onclick='" + handler + "(\"no\", " + parms + ");'></i></div>";
	}

	if (type == 'ok') {
		typeStr += "<div class='pte_message_confirmation_container' title='Click/Tap to Clear '></div>";
	}

	var msToDraw = 0;
	var html = "<div class='" + theClass + "'><div class='pte_notification_icon_container'><i class='far fa-" + icon + " pte_notification_icon'></i></div><div class='pte_notification_message'>" + message + typeStr + "</div></div>";
	if (jQuery("#alpn_message_area").css("opacity") == 1) {
		msToDraw = 250;
		jQuery("#alpn_message_area").attr('style', "opacity: 0; pointer-events: none; cursor: auto;");
	}
	setTimeout(function(){   //show area but wait if needed to complete previous animation
		jQuery("#alpn_message_area").html(html).attr('style', "opacity: 1; 	pointer-events: auto; cursor: pointer;");
	}, msToDraw);
	if (type == 'timed') {
		if (parms) {
			var showTime = parms['show_time'];
		} else {
				var showTime = 5000;
		}
		pte_message_to_clear = setTimeout(function(){
			jQuery("#alpn_message_area").attr('style', "opacity: 0; pointer-events: none; cursor: auto;");
		}, showTime);
	}
}

function alpn_prepare_search_field(domSelect) {

	console.log("alpn_prepare_search_field");
	console.log(domSelect);

	var inputField = jQuery(domSelect + ' label :input').detach();
	jQuery(domSelect + ' label').empty();
	jQuery(domSelect + ' label').append(inputField);
	jQuery(domSelect + ' label').append("<span style='position: absolute; top: 6px; left: 76px; cursor: pointer; font-size: 14px;'><i class='far fa-times-circle' style='color: #3172B6;' title='Clear Search and Filter'></i></span>");
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
	var alpn_obj = jQuery('div.alpn_column_1 #alpn_field_' + uniqueRecId).closest("table");

	if (alpn_obj.length) {
		var selectedItem = alpn_obj[0];
		if (selectedItem.id == 'table_network') {return 'network';}
		if (selectedItem.id == 'table_topic') {return 'topic';}
	} else {
		return 'user';
	}
}

function pte_handle_active_toolbar (buttonType){
	switch(buttonType) {
		case 'add':
			pte_toolbar_active = 'add';
			jQuery('#alpn_vault_new').css("color", "rgb(0, 132, 238)");
			jQuery('#alpn_vault_links').css("color", "#3172B6");
			jQuery('#alpn_vault_edit').css("color", "#3172B6");
		break;
		case 'edit':
			pte_toolbar_active = 'edit';
			jQuery('#alpn_vault_new').css("color", "#3172B6");
			jQuery('#alpn_vault_links').css("color", "#3172B6");
			jQuery('#alpn_vault_edit').css("color", "rgb(0, 132, 238)");
		break;
		case 'links':
			pte_toolbar_active = 'links';
			jQuery('#alpn_vault_new').css("color", "#3172B6");
			jQuery('#alpn_vault_links').css("color", "rgb(0, 132, 238)");
			jQuery('#alpn_vault_edit').css("color", "#3172B6");
			pte_get_vault_links();
		break;
		case 'none':
			pte_toolbar_active = 'none';
			jQuery('#alpn_vault_new').css("color", "#3172B6");
			jQuery('#alpn_vault_links').css("color", "#3172B6");
			jQuery('#alpn_vault_edit').css("color", "#3172B6");
		break;
	}
}

function pte_get_link_options_string(value){
	var linkOptionsMap = {
		"0": "View",
		"1": "Print",
		"2": "Copy & Download",
	};
	return linkOptionsMap[value];
}

function pte_get_link_expiration_string(value){
	var linkExpirationMap = {
		"30": "30 Mins",
		"60": "1 Hour",
		"480": "8 Hours",
		"1440": "1 Day",
		"2880": "2 Days",
		"10080": "1 Week",
		"0": "Manual"
	};
	return linkExpirationMap[value];
}

function pte_expire_url_now(linkId) {

	jQuery.ajax({
		url: alpn_templatedir + 'pte_expire_url_now.php',
		type: 'POST',
		data: {
			"link_id": linkId
		},
		dataType: "json",
		success: function(json) {
			console.log(json);
			pte_get_vault_links();

		},
		error: function(json) {
			console.log("Failed Expiring link...");
			//TODO handle
		}
	})

}

function pte_get_vault_links(cellId){
	console.log("pte_get_vault_links...");
	var security = specialObj.security;
	if (!cellId) {
		cellId = alpn_oldVaultSelectedId;
	}

	var link = {};
	var meta = {};
	var html = '';
	var about = '';
	var targetName = '';
	var sentBy = '';
	var linkInteractionPassword = '';
	var linkInteractionExpiration = '';
	var linkInteractionOptions = '';
	var linkAbout = '';
	var secureURL = '';
	var uid = '';
	var expireNowButtonHtml = '';
	var expirationColor = '';
	var now = {};
	var isExpired = false;
	var expired = "true";

	jQuery.ajax({
		url: alpn_templatedir + 'alpn_get_vault_links.php',
		type: 'POST',
		data: {
			"cell_id": cellId,
			"security": security,
		},
		dataType: "json",
		success: function(json) {

			var vaultResults= json.link_results;
			var widgetHtml = json.widget_html;

			for (var i = 0; i < vaultResults.length; i++) {
				link = vaultResults[i];
				meta = JSON.parse(link.link_meta);
				uid = link.uid;
				expired = link.expired;
				targetName = (!linkAbout && typeof meta.send_email_address_name != "undefined" && meta.send_email_address_name) ? "To " + meta.send_email_address_name.replace(/\\(.)/mg, "$1") : linkAbout;
				linkAbout = (link.about) ? link.about.replace(/\\(.)/mg, "$1") : '';
				targetName = (!linkAbout && typeof meta.send_email_address_name != "undefined" && meta.send_email_address_name) ? "To " + meta.send_email_address_name.replace(/\\(.)/mg, "$1") : linkAbout;
				targetName = targetName ? targetName : "Unspecified";
				sentBy = (typeof meta.send_email_address != "undefined" && meta.send_email_address) ? "Email" : '';
				sentBy = (typeof meta.send_mobile_number != "undefined" && meta.send_mobile_number) ? "SMS" : sentBy;
				sentBy = sentBy ? sentBy : "Manual";
				linkInteractionPassword = (typeof meta.link_interaction_password != "undefined" && meta.link_interaction_password) ? "Set" : "None";
				linkInteractionExpiration = (typeof meta.link_interaction_expiration != "undefined" && meta.link_interaction_expiration) ? meta.link_interaction_expiration : 'Manual';
				lastUpdate = (typeof link.last_update != "undefined" && link.last_update) ? link.last_update : '';   //TODO will last update change for reasons that should not affect this?
				expirationDate = dayjs(lastUpdate).utc(true).local().add(linkInteractionExpiration, 'minute');
				now = dayjs();
				isExpired = expirationDate.isBefore(now);

				if ((isExpired && linkInteractionExpiration > 0) || (expired == 'true')) {
					expirationColor = 'red';
					expirationDateHtml = "Expired: " + expirationDate.format('dddd, MMMM D, YYYY h:mm A');
					expireNowButtonHtml = "";
				} else {
					expirationColor = 'green';
					expirationDateHtml = (linkInteractionExpiration > 0) ? "Expires: " + expirationDate.format('dddd, MMMM D, YYYY h:mm A') : "Manual Expiration";
					expireNowButtonHtml = "<i class='fa fa-times-hexagon pte_expire_now' title='Expire URL Now' onclick='pte_expire_url_now(" + link.id + ");'></i>";
				}
				linkInteractionOptions = (typeof meta.link_interaction_options != "undefined") ? meta.link_interaction_options : 0;

				secureURL = "https://" + window.location.hostname + "/viewer/?" + uid;
				html += "<div class='pte_vault_row pte_vault_text_small'><div title='Copy URL to Clipboard' class='pte_vault_row_40 pte_extra_margins pte_ellipsis pte_topic_link' onclick='pte_topic_link_copy_string(\"Secure URL\", \"" + secureURL + "\");'><i class='far fa-copy'></i> " + targetName + " - " + sentBy + "</div><div class='pte_vault_row_20 pte_vault_centered pte_ellipsis'>" + pte_get_link_options_string(linkInteractionOptions) + "</div><div class='pte_vault_row_20 pte_vault_centered pte_ellipsis' title='" + expirationDateHtml + "' style='color: " + expirationColor + "; cursor: default;'>" + pte_get_link_expiration_string(linkInteractionExpiration) + expireNowButtonHtml + "</div><div class='pte_vault_row_20 pte_vault_centered pte_ellipsis'>" + linkInteractionPassword + "</div></div>";
			}
			jQuery('#pte_links_table').html(html);

			var linkWidgets = jQuery('#pte_link_widgets');
			if (linkWidgets.length && !linkWidgets.html().trim()) {
				linkWidgets.html(widgetHtml);
			}

		},
		error: function(json) {
			console.log("Failed getting links...");
			//TODO handle
		}
	})

}

function alpn_handle_vault_row_selected(theCellId) {
	pte_clear_message();
	var theOldRow =  jQuery('#alpn_field_' + alpn_oldVaultSelectedId).closest('tr');
	jQuery(theOldRow).children().attr("style", "background-color: white !important;");
	alpn_oldVaultSelectedId =	theCellId;
	if (theCellId) {
		var theNewRow =  jQuery('#alpn_field_' + theCellId).closest('tr');
			if (theNewRow.length) {
				theNewRow.children().attr("style", "background-color: #D8D8D8 !important;");
				//alpn_manage_vault_buttons(theCellId);
				alpn_vault_control("view");
				if (pte_toolbar_active == 'add') {
						pte_handle_active_toolbar('edit');
				}

				if (pte_toolbar_active == 'links') {
					pte_get_vault_links(theCellId);
				}
				var selectedRowData = wpDataTables.table_vault.fnGetData(theNewRow);
				//TODO filter name field to match valid filename characteristics
				// OR filter characters at download time.
				var nameField = selectedRowData[7].replace(/\.[^/.]+$/, "").replace(/\\(.)/mg, "$1");
				var descField = selectedRowData[6].replace(/\\(.)/mg, "$1"); //stripslashes
				jQuery('#alpn_name_field').val(nameField);
				jQuery('#alpn_about_field').val(descField);
				jQuery('#alpn_selector_sharing').val(selectedRowData[2]).trigger('change');
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

function alpn_switch_panel(panel) {

	var previewEmbedded = jQuery('#alpn_vault_preview_embedded');
	var addEditOuterContainer = jQuery('#alpn_add_edit_outer_container');

	switch(panel) {
		case 'add_edit':
			previewEmbedded.fadeOut(250, function(){addEditOuterContainer.fadeIn(250)});

		/*	if (previewEmbedded.css('display') == 'block') {
				previewEmbedded.attr('style', 'display: none !important;').hide();
				addEditOuterContainer.show();
			}*/
		break;
		case 'view':
		addEditOuterContainer.fadeOut(250, function(){previewEmbedded.fadeIn(250)});

		/*
			if (addEditOuterContainer.css('display') == 'block') {
				addEditOuterContainer.attr('style', 'display: none !important;').hide();
				previewEmbedded.show();
			}
		*/
		break;
	}
}

function pte_setup_pdf_viewer(viewerSettings) {

	console.log('pte_setup_pdf_viewer');
	console.log(viewerSettings);

	var sidebarState = (typeof viewerSettings['sidebar_state'] != "undefined") ? viewerSettings['sidebar_state'] : 'closed';

		var readyWorker = preloadJrWorker({
			workerPath: alpn_templatedir + 'foxitpdf/lib/',
			enginePath: '../lib/jr-engine/gsdk',
			fontPath: '../external/brotli',
			licenseSN: licenseSN,
			licenseKey: licenseKey
		});

		var CustomAppearance = PDFViewCtrl.shared.createClass({
				getLayoutTemplate: function() {
					var template = document.querySelector('[role=layout-template-container]');
					return template.innerHTML;
				},
				beforeMounted: function(rootComponent) {
						this.rootComponent = rootComponent;
						this.toolbarComponent = rootComponent.getComponentByName('toolbar');
				},
				disableAll: function() {
						// in this example, only the download button will be disabled.
						// Once the document is openned, type this code( pdfui.close() ) into the browser to see what it looks like.
						this.toolbarComponent.disable();
				},
				enableAll: function() {
						this.toolbarComponent.enable();
				}
		}, UIExtension.appearances.Appearance);

		setup(CustomAppearance);

		function setup(appearance) {

				PDFUI = UIExtension.PDFUI;
				PDFViewCtrl = UIExtension.PDFViewCtrl;
				Events = PDFViewCtrl.Events;

				var pdfui = window.pdfui = new PDFUI({
						viewerOptions: {
								defaultScale: 'fitHeight',
								libPath: alpn_templatedir + 'foxitpdf/lib/',
								jr: {
										readyWorker: readyWorker,
										licenseSN: licenseSN,
										licenseKey: licenseKey
								}
						},
						renderTo: '#pte_pdf_ui',
						appearance: appearance,
						fragments: [
							{
							    target: 'editable-zoom-dropdown-fitpage',
							    config: {
							        iconCls: 'pte_viewer_fitpage_icon'
							    }
							},
							{
							    target: 'editable-zoom-dropdown-fitwidth',
							    config: {
							        iconCls: 'pte_viewer_fitwidth_icon'
							    }
							},
							{
							      target: "fv--page-contextmenu",
							      config: {
							        cls: "fv__ui-force-hide"
							      }
						  },
							{
									 target: " fv--thumbnail-contextmenu",
									 action: UIExtension.UIConsts.FRAGMENT_ACTION.REMOVE
							}
					],
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
								alpn_templatedir + 'foxitpdf/lib/uix-addons/undo-redo',
								alpn_templatedir + 'foxitpdf/lib/uix-addons/thumbnail'
						].concat(UIExtension.PDFViewCtrl.DeviceInfo.isMobile ? [] : alpn_templatedir + 'foxitpdf/lib/uix-addons/text-object')
				});

				pdfui.setEnableJS(false);

				pdfui.addUIEventListener('fullscreenchange', function(isFullscreen) {
						if(isFullscreen) {
								document.body.classList.add('fv__pdfui-fullscreen-mode');
						} else {
								document.body.classList.remove('fv__pdfui-fullscreen-mode');
						}
				});

				pdfui.addViewerEventListener(Events.openFileSuccess, function() {
							jQuery('#pte_refresh_report_loading').hide();
							jQuery('#alpn_vault_copy').removeClass('pte_extra_button_disabled').addClass('pte_extra_button_enabled');
							jQuery('#alpn_vault_copy_go').removeClass('pte_extra_button_disabled').addClass('pte_extra_button_enabled');
		    });

				pdfui.getComponentByName('pte_sidebar').then(function(pteComponent) {

					if (sidebarState == 'closed') {
						pteComponent.collapseTotally();
				}

				});

				window.addEventListener('scroll',(event) => {
						pdfui.redraw();
				});

				window.onresize = function () {
						pdfui.redraw().catch(function(){});
				}
		}
}

function pte_check_viewer_password(tObj){
	console.log('pte_check_viewer_passcode...');

	var jObj = jQuery(tObj);
	var md5Password = jObj.data('pte-pe');
	var vaultId = jObj.data('pte-vi');
	var permissions = jObj.data('pte-io');
	var md5PasswordSubmitted = md5(jQuery("#pte_viewer_password_input").val());
	if (md5PasswordSubmitted == md5Password) {
		var printFiles = "pte_ipanel_button_disabled";
		var downloadFiles = "pte_ipanel_button_disabled";
		if (permissions == 1) {
			printFiles = 'pte_ipanel_button_enabled';
		}
		if (permissions == 2) {
			printFiles = 'pte_ipanel_button_enabled';
			downloadFiles = 'pte_ipanel_button_enabled';
	}

	console.log('pte_viewer_file_meta');
	console.log(pte_viewer_file_meta);

	var toolBar = " \
		<div class='pte_vault_row_50'> \
			<i id='alpn_vault_print' class='far fa-print pte_icon_button " + printFiles + "' title='Print File' onclick='alpn_vault_control(\"print\")'></i> \
			<i id='alpn_vault_download_original' class='far fa-file-download pte_icon_button " + downloadFiles + "' title='Download Original File' onclick='alpn_vault_control(\"download_original\")'></i> \
			<i id='alpn_vault_download_pdf' class='far fa-file-pdf pte_icon_button " + downloadFiles + "' title='Download PDF File' onclick='alpn_vault_control(\"download_pdf\")'></i> \
		</div> \
		<div class='pte_vault_row_50 pte_vault_right'> \
		<div class='pte_viewer_info_outer'><div class='pte_viewer_info_inner_message'>File Name</div><div id='pte_viewer_info_filename' class='pte_viewer_info_inner_name'>" + pte_viewer_file_meta.file_name + "</div></div> \
		<div class='pte_viewer_info_outer' style='margin-left: 10px;'><div class='pte_viewer_info_inner_message'>Description</div><div id='pte_viewer_info_description' class='pte_viewer_info_inner_name'>" + pte_viewer_file_meta.description + "</div></div> \
		</div> \
		";
		pte_view_document(vaultId);
		jQuery('#pte_viewer_toolbar').fadeOut(250, function(){
			jQuery('#pte_viewer_toolbar').html(toolBar).fadeIn(250);
		});
	} else {
		jQuery('#pte_check_viewer_password_error').html('Incorrect File Passcode. Please try again...');
		setTimeout(function(){ jQuery('#pte_check_viewer_password_error').html('') }, 3000);
	}
}

function pte_view_document(vaultId, token = false) {
	var security = specialObj.security;
	//console.log('Viewing Document...');

	if (!token) {
		var srcFile = alpn_templatedir + 'alpn_get_vault_file.php?which_file=pdf&v_id=' + vaultId + '&security=' + security;
	} else {
		var srcFile = alpn_templatedir + 'alpn_get_vault_file_token.php?which_file=pdf&v_id=' + vaultId + '&security=' + security + '&token=' + token;
	}

	var xhr = new XMLHttpRequest();
	xhr.open('GET', srcFile, true);
	xhr.responseType = 'blob';
	xhr.onreadystatechange = function () {
		if (xhr.readyState !== 4) {
			return;
		}
		var status = xhr.status;
		if (status == 204) {  //Premission Denied
			console.log("Permission Denied");  //TODO handle - shouldn't happen too often cuz shouldnt show up. But yeah, hackers.
			return;
		}

		if ((status >= 200 && status < 300) || status === 304) {
			pdfui.openPDFByFile(xhr.response).catch(function (e) {
					if (e.error === 11 && e.encryptDict.Filter === 'FOPN_foweb') {
							console.log("ENCRYPTED DOC");
							var fileOpenKey = getFileOpenKey(e.encryptDict);
							pdfui.reopenPDFDoc(e.pdfDoc, {
									fileOpen: {
											encryptKey: fileOpenKey
									}
							})
					}
			})
		} else {
			console.log("ERROR OPENING FILE");
			console.log(xhr);
			console.log(status);
		}
	};
	xhr.send();
}
/*
function alpn_handle_close_add_edit(){
	alpn_close_vault_work_area();
	alert(1);
}
*/

function pte_overlay_success(elId){
	// TODO
	var jEl = jQuery("#" + elId);
	jEl.css("color", "rgb(0, 132, 238)");
	setTimeout(function(){
		jEl.css("color", "#0074BB");
	}, 1500);

}

function pte_close_print () {
  document.body.removeChild(this.__container__);
}

function pte_set_print () {
  this.contentWindow.__container__ = this;
  this.contentWindow.onbeforeunload = pte_close_print;
  this.contentWindow.onafterprint = pte_close_print;
  this.contentWindow.focus(); // Required for IE
  this.contentWindow.print();
}

function pte_print_page (sURL) {
  var oHiddFrame = document.createElement("iframe");
  oHiddFrame.onload = pte_set_print;
  oHiddFrame.style.position = "fixed";
  oHiddFrame.style.right = "0";
  oHiddFrame.style.bottom = "0";
  oHiddFrame.style.width = "0";
  oHiddFrame.style.height = "0";
  oHiddFrame.style.border = "0";
  oHiddFrame.src = sURL;
  document.body.appendChild(oHiddFrame);
}

function pte_get_report_description(){
	console.log("pte_get_report_description");
	var selectedId, sectionSelector, sectionName, sectionNameDiv, description = '';
	for (var i = 0; i <= pte_report_section_count; i++) {
		sectionSelector = jQuery('#alpn_select2_small_report_section_' + i);
		sectionNameDiv = jQuery('#pte_section_name_' + i);
		sectionName = sectionNameDiv.html();
		var sectionSelectorData = sectionSelector.select2('data');
		if (typeof sectionSelectorData != 'undefined' && typeof sectionSelectorData[0] != 'undefined') {
			if (sectionSelectorData[0].id != 1) {
				description += sectionName + ", ";
			}
		}
	}
return description.slice(0, -2);  //remove trailing
}

function pte_copy_report_to_vault(actionOnReturn){

		jQuery('#alpn_vault_copy').css('color', 'rgb(0, 132, 238)');
		var security = specialObj.security;
		var topicId = jQuery("#pte_selected_topic_meta").data('topic-id');
		var topicDomId = jQuery("#pte_selected_topic_meta").data('tdid');
		var topicName = jQuery("#pte_topic_name").html();
		var topicDescription = pte_get_report_description();

		console.log('COPY FILE', topicId);

		jQuery.ajax({
			url: alpn_templatedir + 'pte_add_report_to_vault.php',
			type: 'POST',
			data: {
				"topic_id": topicId,
				"topic_name": topicName,
				"security": security,
				"topic_dom_id": topicDomId,
				"topic_description": topicDescription
			},
			dataType: "json",
			success: function(json) {
				console.log("Success adding report to vault...");
				if (actionOnReturn == '1') {
					alpn_mission_control('vault', alpn_oldSelectedId);
				}
				jQuery('#alpn_vault_copy').css('color', '#3172B6');

			},
			error: function(json) {
				console.log("Failed adding report to vault...");
				//TODO handle
			}
		})
}


function pte_start_topic_team_invitation (topicId) {
	var sendData = {
		'topic_id': topicId,
		'process_type_id': 'proteam_invitation'
	};
	pte_handle_widget_interaction(sendData);
	pte_overlay_success('pte_proteam_add');  //Animates button to give user feedback that am interaction is starting. TODO get this right
}

function alpn_vault_control(operation) {
	//Get Row Context
	var trOb, rowData, s_id, from_id;
	var vaultId = '';
	var vaultDomId = '';
	var submissionId = '';
	var permissionValue;
	var formId = '';
	var formName = '';
	var formAbout = '';
	var fileName = '';
	var filesource = '';
	var mimeType = '';
	var id = '';
	var topicId = '';

	if (alpn_oldVaultSelectedId) {
		trObj =  jQuery('#alpn_field_' + alpn_oldVaultSelectedId).closest('tr');

		if ((typeof wpDataTables !== "undefined") && trObj) {
			rowData = wpDataTables.table_vault.fnGetData(trObj);

			vaultId = rowData[0];
			vaultDomId = rowData[11];
			submissionId = rowData[8];
			formId = rowData[10];
			formName = rowData[5];
			fileName = rowData[7];
			formAbout = rowData[6];
			fileSource = rowData[15];
			mimeType = rowData[9];
			permissionValue = rowData[2];
			topicId = rowData[13].replace(/\D/g,'');
		}
	}

//TODO CHeck for rights at server??

	switch(operation) {
		case 'insert_chat_vault_item':
			//insert link to vault item into chat window
			var selectedTopicMeta = jQuery("div#pte_selected_topic_meta");
			var topicSpecial = selectedTopicMeta.data("special");
			var topicDomId = selectedTopicMeta.data("tdid");
			var topicOwnerId = selectedTopicMeta.data("oid");
			var topicId = selectedTopicMeta.data("tid");
			var topicTypeId = selectedTopicMeta.data("ttid");

			var messageData = {
				"name": "pte_insert_new_object",
				"file_name": fileName,
				"file_about": formAbout,
				"topic_owner_id": topicOwnerId,
				"topic_special": topicSpecial,
				"topic_dom_id": topicDomId,
				"topic_id": topicId,
				"topic_type_id": topicTypeId,
				"vault_id": vaultId,
				"vault_dom_id": vaultDomId,
				"mime_type": mimeType,
				"permission": permissionValue,
				"object_type": "vault_item"
			};
			pte_message_chat_window(messageData);

		break;
		case 'download_original':
			console.log('Downloading Original...');
			var security = specialObj.security;
			var srcFile = alpn_templatedir + 'alpn_get_vault_file.php?which_file=original&v_id=' + vaultId + '&security=' + security;
			window.location = srcFile;
		break;
		case 'download_pdf':
			console.log('Downloading PDF...');
			var security = specialObj.security;
			var srcFile = alpn_templatedir + 'alpn_get_vault_file.php?which_file=pdf&v_id=' + vaultId + '&security=' + security;
			window.location = srcFile;
		break;
		case 'print':
			pdfui.getPDFViewer().then(function(pdfviewer){
				var pdfdoc = pdfviewer.getCurrentPDFDoc();
				var bufferArray = [];
				var fileblob;
				pdfdoc.getStream(function ({arrayBuffer, offset, size}) {
				bufferArray.push(arrayBuffer);
				}).then(function (size) {
					var fileblob= new Blob(bufferArray, {type: 'application/pdf'});
					var pdfUrl = URL.createObjectURL(fileblob);
					pte_print_page(pdfUrl);
				})
			});
		break;

		case 'sms':
			var typeSelected = alpn_select_type(alpn_oldVaultSelectedId);
			var sendData = {
				'vault_id': vaultId,
				'topic_id': topicId,
				'process_type_id': 'sms_send'
			};

			if (typeSelected == 'network'){
				var sendData = {
					'vault_id': vaultId,
					'network_id': topicId,
					'process_type_id': 'sms_send'
				};
			}
			pte_handle_widget_interaction(sendData);
			pte_overlay_success('alpn_vault_sms');  //Animates button to give user feedback that am interaction is starting. TODO get this right

		break;

		case 'email':
			var typeSelected = alpn_select_type(alpn_oldVaultSelectedId);
			var sendData = {
				'vault_id': vaultId,
				'topic_id': topicId,
				'process_type_id': 'email_send'
			};

			if (typeSelected == 'network'){
				var sendData = {
					'vault_id': vaultId,
					'network_id': topicId,
					'process_type_id': 'email_send'
				};
			}
			pte_handle_widget_interaction(sendData);
			pte_overlay_success('alpn_vault_email');  //Animates button to give user feedback that am interaction is starting. TODO get this right

		break;
		case 'fax':
			var typeSelected = alpn_select_type(alpn_oldVaultSelectedId);
			var sendData = {
				'vault_id': vaultId,
				'topic_id': topicId,
				'process_type_id': 'fax_send'
			};

			if (typeSelected == 'network'){
				var sendData = {
					'vault_id': vaultId,
					'network_id': topicId,
					'process_type_id': 'fax_send'
				};
			}
			pte_handle_widget_interaction(sendData);
			pte_overlay_success('alpn_vault_fax');

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
				if (!alpn_oldVaultSelectedId) {
						alpn_set_vault_to_first_row = true;
						wpDataTables.table_vault.fnFilter();
			}
			alpn_switch_panel('view');

			if (pte_toolbar_active == 'none') {
				pte_set_work_area('add-edit');
				alpn_open_vault_work_area();
				pte_handle_active_toolbar('edit');
			} else if (pte_toolbar_active == 'edit') {
				alpn_close_vault_work_area();
				pte_handle_active_toolbar('none');
			} else {
				pte_handle_active_toolbar('edit');
				pte_set_work_area('add-edit');
			}

		jQuery('#alpn_name_field').attr('style', 'pointer-events: auto; opacity: 1.0;');
		jQuery('#alpn_name_field_label').attr('style', 'pointer-events: auto; opacity: 1.0;');

		break;
		case 'view':
			//TODO Change to docType	-- UNSAFE. ANYONE CAN JQUERY TO GET FILES????
			alpn_switch_panel('view');
			jQuery('#alpn_name_field').attr('style', 'pointer-events: auto; opacity: 1.0;');
			jQuery('#alpn_name_field_label').attr('style', 'pointer-events: auto; opacity: 1.0;');
			if (vaultId) {
				pte_view_document(vaultId);
			}

		break;

		case 'links':
			//TODO Change to docType	-- UNSAFE. ANYONE CAN JQUERY TO GET FILES????
			if (!alpn_oldVaultSelectedId) {  //After Add
					alpn_set_vault_to_first_row = true;
					wpDataTables.table_vault.fnFilter();
			}
			alpn_switch_panel('view');

			if (pte_toolbar_active == 'none') {
				pte_set_work_area('links');
				alpn_open_vault_work_area();
				pte_handle_active_toolbar('links');
			} else if (pte_toolbar_active == 'links') {
				alpn_close_vault_work_area();
				pte_handle_active_toolbar('none');
			} else {
				pte_handle_active_toolbar('links');
				pte_set_work_area('links');
			}

		break;

		case 'add':   //this is add and edit
			alpn_file_add();

			if (pte_toolbar_active == 'none') {
				pte_set_work_area('add-edit');
				alpn_open_vault_work_area();
				pte_handle_active_toolbar('add');
			} else if (pte_toolbar_active == 'add') {
				alpn_close_vault_work_area();
				alpn_set_vault_to_first_row = true;
				wpDataTables.table_vault.fnFilter();
				pte_handle_active_toolbar('none');
			} else {
				pte_handle_active_toolbar('add');
				pte_set_work_area('add-edit');
			}

			jQuery('#alpn_name_field_label').attr('style', 'pointer-events: none; opacity: 0.5;');
			jQuery('#alpn_name_field').val('').attr('style', 'pointer-events: none; opacity: 0.5;');

		break;
		case 'delete':
			var parms = {'vault_id': vaultId};
			pte_show_message('yellow_question', 'confirm', 'Please confirm delete:', 'pte_handle_delete_response', JSON.stringify(parms));

		break;
	}
}

//REMOVE ProTeam Member
function alpn_proteam_member_delete(proTeamRowId) {

	var security = specialObj.security;
	var html = "<div id='alpn_replace_me_" + proTeamRowId + "' style='text-align: center; height: 40px;'><img src='" + alpn_templatedir + "pdf/web/images/loading-icon.gif'></div>";
	jQuery('#pte_proteam_item_' + proTeamRowId).replaceWith(html);
	jQuery.ajax({
		url: alpn_templatedir + 'alpn_handle_delete_rights.php',
		type: 'POST',
		data: {
			"rowToDelete": proTeamRowId,
			"security": security
		},
		dataType: "json",
		success: function(json) {
			var deletedChannelToo = json.deleted_channel_too;

			if (deletedChannelToo) {  //Channel gone, clear chat window by messaging iframe.
				var data = {
					"name": "pte_channel_deleted"
				}
				pte_message_chat_window(data);
			}
			jQuery('#alpn_replace_me_' + proTeamRowId).remove();
			var proTeamTable = jQuery('#alpn_proteam_selected_outer'); //network topic
			if (proTeamTable.children().length) {
				jQuery("#pte_no_proteam_members").hide();
			} else {
				jQuery("#pte_no_proteam_members").show();
			}
		},
		error: function(json) {
			console.log("Failed deleting...");
			//TODO handle
		}
	})

}

function alpn_rights_check(theDom){

	var theItem = jQuery(theDom).data('item');
	var checkState = jQuery(theDom).attr("pte-state");  //changing .data attributes then re-reading shows old value. workaround.
	var proTeamId = jQuery(theDom).data('ptid');

	var rightsInfo = {'id': proTeamId, 'key': theItem, 'check_state': checkState};
	jQuery.ajax({
		url: alpn_templatedir + 'alpn_handle_edit_rights.php',
		type: 'POST',
		data: {
			rightsInfo: JSON.stringify(rightsInfo)
		},
		dataType: "json",
		success: function(json) {
			var ptePtPanel = jQuery("#pte_proteam_item_" + proTeamId + " #proteam_" + theItem);
			var newStr = !checkState ? "<i class='fa fa-check' style='font-size: 0.9em; color: #4499d7;'></i>" : "";
			var newState = !checkState ? "set" : "";
			ptePtPanel.attr("pte-state", newState);
			var newthing = ptePtPanel.find("div.pte_panel_check");
			newthing.html(newStr);
		},
		error: function(e) {
			//TODO Don't make the change
		}
	})
}

function pte_rights_access_level(proTeamId, theSelection){

	console.log("Rights Access Level...");
	console.log(proTeamId);
	var security = specialObj.security;
	var jObj = jQuery(theSelection);
	var proTeamValue = jObj.attr('id');
	var proTeamText = jObj.attr('text');

	jQuery.ajax({
		url: alpn_templatedir + 'alpn_handle_edit_access_level.php',
		type: 'GET',
		data: {
			'proTeamId': proTeamId,
			'security': security,
			'proTeamValue' : proTeamValue
		},
		dataType: "json",
		success: function(json) {
			console.log(json);
		},
		error: function(e) {
			console.log("Updated Access Level Failed...");

			//TODO reset to previous value?
		}
	})
}

function pte_add_to_proteam_table(rightsInfo){

	console.log("Add To ProTeam Table");
	console.log(rightsInfo);

	var wp_id = rightsInfo['wp_id'];
	var dom_id = rightsInfo['dom_id'];
	var selectedName = rightsInfo['text'];
	var html = rightsInfo['initial_proteam_panel_html'];

	var proTeamTable = jQuery('#alpn_proteam_selected_outer'); //network topic
	var topicId = '';                    //network topic id or user
	var topicName = '';					//network name
	var theTopic = {};
	var handled = false;

	console.log(proTeamTable);
	console.log(proTeamTable.length);
	console.log(proTeamTable.children().length);

	var proTeamMemberCount = proTeamTable.children().length;

	if (proTeamMemberCount) {
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
				return false;  //break foreach
			}
		});
		if (!handled) { //if none greater than add at end
				theTopic.after(html);
		}
	} else {
		proTeamTable.append(html);
	}

 if (proTeamTable.children().length) {
	 console.log("HERE1");
	 console.log(proTeamTable);
	 jQuery("#pte_no_proteam_members").hide();
 } else {
	 jQuery("#pte_no_proteam_members").show();
	 console.log("HERE2");
	 console.log(proTeamTable);
 }

}

function alpn_setup_proteam_member_selector(proteam_id){

	console.log("alpn_setup_proteam_member_selector...");
	console.log(proteam_id);

	var selector = '#alpn_select2_small_' + proteam_id;

	if (proteam_id == 'all') {
		selector = '.alpn_select2_small';
	}

	jQuery(selector).select2( {
		width: '90px',
		theme: "bootstrap",
		allowClear: false,
		minimumResultsForSearch: -1
	});

	jQuery(selector).on("select2:select", function (e) {
		var ptrid = jQuery(e.currentTarget).data("ptrid");
		var data = e.params.data;
		pte_rights_access_level(ptrid, data);
	});
	//jQuery("[aria-labelledby^=select2-alpn_select2_small_]").addClass('alpn_select2_small');	 //TODO alt approach is dupe css, make changes accordingly.
}

function pte_handle_message_merge(docType = 'message'){

	//also handles importance weight changes so very chatty

  var security = specialObj.security;


	console.log("Handle Message Merge...");

	var thisProcessId = jQuery("#pte_interaction_information_panel").data("pid");
	console.log(thisProcessId);

	var selectedTargetId, selectedTemplateId, contextTopicId;

	var templateList = jQuery('#alpn_select2_small_template_select');
	var templateListData = templateList.select2('data');

	if (typeof templateListData != 'undefined' && typeof templateListData[0] != 'undefined') {
		selectedTemplateId = parseInt(templateListData[0].id);
		contextTopicId = parseInt(templateList.data('topic-id'));
	}

	var targetList = jQuery('#alpn_select2_small_fax_number_select');
	var targetListData = targetList.select2('data');
	if (typeof targetListData != 'undefined' && typeof targetListData[0] != 'undefined') {
		selectedTargetId = parseInt(targetListData[0].id);
	} else {
		selectedTargetId = jQuery('#pte_to_line').data('cid');
	}

	var sendButton = jQuery('#pte_message_panel_send');

	if (!selectedTargetId) {
		if (!sendButton.hasClass('pte_extra_button_disabled')) {
			sendButton.addClass('pte_extra_button_disabled');
		}
	}

	if (selectedTargetId) {

		if (sendButton.hasClass('pte_extra_button_disabled')) {
			sendButton.removeClass('pte_extra_button_disabled');
		}

		jQuery.ajax({
			url: alpn_templatedir + 'pte_handle_merge_message.php',
			type: 'POST',
			data: {
				template_id: selectedTemplateId,
				context_topic_id: contextTopicId,
				target_topic_id: selectedTargetId,
				process_id: thisProcessId,
				security: security,
				doc_type: docType
			},
			dataType: "json",
			success: function(json) {
				console.log("Message Merge JSON...");
				console.log(json);

				var contactImportant = json.contact_is_important;
				var interactionPriorityContainer = jQuery("div.alpn_interaction_cell[data-uid='" + thisProcessId + "']").find("div.pte_importance_bg");
				pte_update_interaction_importance(interactionPriorityContainer, (contactImportant == true) * 3, 0);

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


function pte_save_topic_pic(fileUploaded, source){
	var security = specialObj.security;
		if (fileUploaded.id) {
			var fileHandle = fileUploaded.id + "." + fileUploaded.ext;
			var currentSelection = alpn_select_type();
			var trObj =  jQuery('div.alpn_column_1 #alpn_field_' + alpn_oldSelectedId).closest('tr');
			switch(currentSelection) {
				case 'user':
					var topicId = jQuery('div.alpn_column_1 #alpn_field_' + alpn_oldSelectedId).data('topic-id');
					var topicSpecial = 'user';
				break;
				case 'network':
					var topicRowData = wpDataTables.table_network.fnGetData(trObj);
					var topicId = topicRowData[0];
					var topicSpecial = 'contact';
				break;
				case 'topic':
					var topicRowData = wpDataTables.table_topic.fnGetData(trObj);
					var topicId = topicRowData[0];
					var topicSpecial = 'topic';
				break;
			}

			jQuery.ajax({
				url: alpn_templatedir + 'pte_handle_profile_pic_upload.php',
				type: 'POST',
				data: {
					source: source,
					handle: fileHandle,
					topic_id: topicId,
					security: security,
					topic_special: topicSpecial
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
								var iconContainer = jQuery('div.alpn_column_1 #alpn_field_' + alpn_oldSelectedId + ' .alpn_topic_icons');
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
	var security = specialObj.security;
//		css: "https://proteamedge.com/wp-content/themes/memberlite-child-master/dist/css/cloudsponge.css",
if (typeof cloudsponge != "undefined" && !pte_external && alpn_select_type() == 'user') {
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
					security: security,
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
}

function pte_set_accordion(panel_id, set_to) {

	var accordion = jQuery(panel_id);
	var panel = accordion.next();

	var panelHeight = panel.data('height');

	if (panel.css('height') == '0px' && set_to == 'open') {
		panel.attr("style", "height: " + panelHeight + " !important;");
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
			panel.attr("style", "height: " + panelHeight + " !important;");
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

function pte_manage_history(data){
	var historyState = history.state;
	if (historyState && historyState.replace_me) {
		history.replaceState(data, null, "");
	} else {
		history.pushState(data, null, "");
	}
}

function pte_new_topic_link(topicToken) {
	alpn_mission_control('add_topic', "", topicToken);
}

function pte_default_topic_link (topicToken) {
	var selectedTab = jQuery("button.tablinks.pte_tab_button_active").data('tab-id');
	var tableId = 'table_tab_' + selectedTab;
	var recordDomId = jQuery('#tabcontent_' + selectedTab + ' #pte_tab_record_wrapper').data('dom_id');
	var selectedRowUid = recordDomId ? recordDomId : pte_active_tabs[selectedTab];  //Account for record versus row in table
	alpn_mission_control('make_default_topic', selectedRowUid);
}

function pte_edit_topic_link(topicToken) {
	var selectedTab = jQuery("button.tablinks.pte_tab_button_active").data('tab-id');
	var tableId = 'table_tab_' + selectedTab;
	var recordDomId = jQuery('#tabcontent_' + selectedTab + ' #pte_tab_record_wrapper').data('dom_id');
	var selectedRowUid = recordDomId ? recordDomId : pte_active_tabs[selectedTab];  //Account for record versus row in table
	alpn_mission_control('edit_topic', selectedRowUid, topicToken);
}

function pte_change_template_type(data){

	console.log("pte_change_template_type");

	var metaObj = jQuery('#pte_selected_template_meta');
	var topicTypeFormId = metaObj.data('ttfid');

	var templateEditorMode = data.id;
	pte_handle_select_template(topicTypeFormId, templateEditorMode)

	console.log(topicTypeFormId);
	console.log(templateEditorMode);


}

function pte_set_report_data(domId) { //Must always create the get version
	console.log("pte_apply_report_settings", domId);
	var sections, sectionList, sectionId, topicKey, topicValue, topicJson;
	var dataRow =  jQuery('#alpn_field_' + domId).closest('tr');
	var rowData = wpDataTables.table_reports.fnGetData(dataRow)
	var reportData = JSON.parse(rowData[3].replace(/\\(.)/mg, "$1"));  //strip slashes and parse to json
	jQuery('#pte_report_name_field').val(rowData[2]);
	jQuery('#alpn_select2_small_report_show_selector').val(reportData.show_banner).trigger('change');
	jQuery('#alpn_select2_small_report_place').val(reportData.place_source).trigger('change');
	jQuery('#alpn_select2_small_report_organization').val(reportData.organization_source).trigger('change');
	jQuery('#alpn_select2_small_report_banner_selector').val(reportData.banner_type + 1).trigger('change');
	jQuery('#alpn_select2_small_report_style_selector').val(reportData.accent_style + 1).trigger('change');
	jQuery('#alpn_select2_small_report_accent_color_selector').val(reportData.accent_color_id).trigger('change');
	sections = reportData.sections;
	console.log(sections);
	for (var i = 0; i <= reportData.section_count; i++) {
		sectionList = jQuery('#alpn_select2_small_report_section_' + i);
		if (sectionList.length) {
			topicKey = sectionList.data("topic-key");
			topicJson = sections[topicKey];
			if (topicJson) {
				topicValue = topicJson.section_filter;
				if (sectionList.find("option[value='" + topicValue + "']").length) {   //see if exists in list
					sectionList.val(topicValue).trigger('change');
				} else {
					sectionList.val('exclude').trigger('change');
				}
			}
		}
	}
	pte_report_section_count = reportData.section_count;  //Should be the same but just in case

	pte_handle_report_settings('refresh');

}

function pte_get_report_data(data) {  //Must always create the set version

	var sectionSelector = '';
	var topicKey = '';
	var topicData = {};

	data.section_count = pte_report_section_count;
	data.sections = {};

	var showBanner = jQuery('#alpn_select2_small_report_show_selector');
	var showBannerData = showBanner.select2('data');
	if (typeof showBannerData != 'undefined' && typeof showBannerData[0] != 'undefined') {
		data.show_banner = showBannerData[0].id;
	}
	var placeSelector = jQuery('#alpn_select2_small_report_place');
	var placeSelectorData = placeSelector.select2('data');
	if (typeof placeSelectorData != 'undefined' && typeof placeSelectorData[0] != 'undefined') {
		data.place_source = placeSelectorData[0].id;
	}
	var organizationSelector = jQuery('#alpn_select2_small_report_organization');
	var organizationSelectorData = organizationSelector.select2('data');
	if (typeof organizationSelectorData != 'undefined' && typeof organizationSelectorData[0] != 'undefined') {
		data.organization_source = organizationSelectorData[0].id;
	}
	var bannerSelector = jQuery('#alpn_select2_small_report_banner_selector');
	var bannerSelectorData = bannerSelector.select2('data');
	if (typeof bannerSelectorData != 'undefined' && typeof bannerSelectorData[0] != 'undefined') {
		data.banner_type = bannerSelectorData[0].id - 1;
	}
	var styleSelector = jQuery('#alpn_select2_small_report_style_selector');
	var styleSelectorData = styleSelector.select2('data');
	if (typeof styleSelectorData != 'undefined' && typeof styleSelectorData[0] != 'undefined') {
		data.accent_style = styleSelectorData[0].id - 1;
	}
	var accentColorSelector = jQuery('#select2-alpn_select2_small_report_accent_color_selector-container span');
	var accentColor = accentColorSelector.css('color');

	var accentColorIdSelector = jQuery('#alpn_select2_small_report_accent_color_selector');
	var accentColorIdSelectorData = accentColorIdSelector.select2('data');

	if (typeof accentColorIdSelectorData != 'undefined' && typeof accentColorIdSelectorData[0] != 'undefined') {
		data.accent_color_id = accentColorIdSelectorData[0].id;
	}
	if (accentColor) {
		data.accent_color = accentColor;
	}
	for (var i = 0; i <= pte_report_section_count; i++) {
		sectionSelector = jQuery('#alpn_select2_small_report_section_' + i);
		var sectionSelectorData = sectionSelector.select2('data');
		if (typeof sectionSelectorData != 'undefined' && typeof sectionSelectorData[0] != 'undefined') {
			topicKey = sectionSelector.data('topic-key');
			topicData = {
				'section_filter': sectionSelectorData[0].id
			};
			data.sections[topicKey] = topicData;
		}
	}
	return data;
}

function alpn_handle_reports_table(){
		console.log('alpn_handle_reports_table...');
		var tableRow, rowData, templateName, domId, cellObj, html;

		var tableData = wpDataTables.table_reports.fnGetData();

		for (i=0; i< tableData.length; i++) {
			html = '';
			tableRow = tableData[i];
			rowData = JSON.parse(tableRow[3].replace(/\\(.)/mg, "$1"));
			cellObj = jQuery('#alpn_field_' + tableRow[4]);
			html += "<div class='pte_report_table_name'>" + tableRow[2] + "</div>";
			cellObj.html(html);
			cellObj.parent().click(
				function(){
					var topicContainer = jQuery(this).find('div');
					var domId = topicContainer.data('uid');
					pte_manage_report_table_select(domId);
			});
		}
	pte_manage_report_table_select(pte_selected_report_template);
}

function pte_set_template_data(domId){
	console.log('Setting Template Data...');

	//TODO check for dirty if (tinymce.activeEditor.isDirty())

	if (domId) {

		var trObj =  jQuery('#alpn_field_' + domId).closest('tr');
		var templateRowData = wpDataTables.table_reports.fnGetData(trObj);
		if (typeof templateRowData != "undefined" && templateRowData != null && typeof templateRowData[3] != "undefined") {
			var templateJson = JSON.parse(templateRowData[3].replace(/\\(.)/mg, "$1"));  //stripslashes and parse json

			console.log(templateJson);

			jQuery("#pte_template_name_field").val(templateJson.template_name);
			jQuery("#pte_template_title_field").val(templateJson.template_title);
			tinymce.get("template_editor").setContent(templateJson.template_body);

		}

	}
}

function pte_manage_report_table_select(domId){
	console.log("pte_manage_report_table_select");
	if (pte_selected_report_template) {
		var oldObjectCell = jQuery("#alpn_field_" + pte_selected_report_template);
		var oldCellParent = oldObjectCell.parent();
		oldCellParent.attr('style', 'background-color: #F8F8F8 !important;')
		pte_selected_report_template = '';
		if (!domId) {
			jQuery("#pte_report_button_clone").removeClass('pte_extra_button_enabled').addClass('pte_extra_button_disabled');
			jQuery("#pte_report_button_delete").removeClass('pte_extra_button_enabled').addClass('pte_extra_button_disabled');
		}
	}
	if (domId) {
		var reportTableCell = jQuery("#alpn_field_" + domId);
		var cellParent = reportTableCell.parent();
		cellParent.attr('style', 'background-color: #D8D8D8 !important;')
		pte_selected_report_template = domId;
		jQuery("#pte_report_button_clone").removeClass('pte_extra_button_disabled').addClass('pte_extra_button_enabled');
		jQuery("#pte_report_button_delete").removeClass('pte_extra_button_disabled').addClass('pte_extra_button_enabled');
		if (pte_template_editor_loaded) {
			pte_set_template_data(domId);
		} else {
			pte_set_report_data(domId);
		}
	}
}

function pte_handle_delete_report(userResponse, parms){
	console.log('pte_handle_delete_report...');
	var security = specialObj.security;
	var reportDomId = parms.report_id;
	if (userResponse == 'yes' && reportDomId) {
		jQuery.ajax({
			url: alpn_templatedir + 'alpn_handle_delete_report.php',
			type: 'POST',
			data: {
				report_dom_id: reportDomId,
				security: security,
			},
			dataType: "json",
			success: function(json) {
				console.log('pte_handle_report_delete - SUCCESS');
				pte_selected_report_template = '';
				jQuery("#pte_report_button_clone").removeClass('pte_extra_button_enabled').addClass('pte_extra_button_disabled');
				jQuery("#pte_report_button_delete").removeClass('pte_extra_button_enabled').addClass('pte_extra_button_disabled');
				wpDataTables.table_reports.fnFilterClear(); //TODO do the paging thing.
				if (pte_template_editor_loaded) {
					jQuery('#pte_template_name_field').val('');
					jQuery("#pte_template_title_field").val('');
					tinymce.get("template_editor").setContent('');
				} else {
					jQuery('#pte_report_name_field').val('');
				}
			},
			error: function() {
				console.log('pte_handle_report_delete - FAIL');
			}
	});
}
pte_clear_message();
}

function pte_handle_template_operation(operation) {

	console.log('pte_handle_template_operation...');
	console.log(operation);

	var metaObj = jQuery('#pte_selected_template_meta');
	var topicTypeKey = metaObj.data('ttkey');
	var topicTypeFormId = metaObj.data('ttfid');

	console.log(topicTypeKey);
	console.log(topicTypeFormId);
	console.log(pte_selected_report_template);

	switch(operation) {
		case 'save':
			console.log('Handling Save...');
			var fieldObj = jQuery('#pte_template_name_field');
			var fieldName = fieldObj.val();
			if (!fieldName) { //check for name
				fieldObj.attr('placeholder', "Name Required...");
				return;
			}
			var templateEditorModeData = jQuery('#alpn_select2_template_type').select2('data');
			var templateEditorMode = templateEditorModeData[0].id;

			var templateTitle = jQuery("#pte_template_title_field").val();
			var templateBody = tinymce.get("template_editor").getContent();

			if (!templateTitle && !templateBody) {  //check for a minimum of data
				jQuery("#pte_template_title_field").attr('placeholder', "Title and/or Body Required...");
				return;
			}

			var allData = {};
			allData.template_name = fieldName;
			allData.template_type = templateEditorMode;
			allData.template_title = templateTitle;
			allData.template_body = templateBody;
			allData.template_dom_id = pte_selected_report_template;

			allData.topic_type_key = topicTypeKey;
			allData.topic_type_form_id = topicTypeFormId;
			var security = specialObj.security;
			jQuery.ajax({
				url: alpn_templatedir + 'alpn_handle_save_template.php',
				type: 'POST',
				data: {
					template_data: JSON.stringify(allData),
					security: security,
				},
				dataType: "json",
				success: function(json) {
					console.log('pte_handle_save_template - SUCCESS');
					console.log(json);
					pte_selected_report_template = json.dom_id;  //report and template are interchangeable. TODO Unify into generalized handling of templates
					wpDataTables.table_reports.fnFilterClear();  //TODO handle paging to the proper page
				},
				error: function() {
					console.log('pte_handle_save_template - FAIL');

				}
		})

		break;
		case 'delete':
			console.log('Handling Delete...');
			var parms = {'report_id': pte_selected_report_template};
			pte_show_message('yellow_question', 'confirm', 'Please confirm delete:', 'pte_handle_delete_report', JSON.stringify(parms))
		break;


		case 'clone':
		var security = specialObj.security;
			console.log('Handling Clone...');
			jQuery.ajax({
				url: alpn_templatedir + 'alpn_handle_clone_report.php',
				type: 'POST',
				data: {
					report_dom_id: pte_selected_report_template,
					security: security,
				},
				dataType: "json",
				success: function(json) {
					console.log('pte_handle_report_clone - SUCCESS');
					console.log(json);
					var templateDomId = json.dom_id;
					pte_selected_report_template = templateDomId;
					wpDataTables.table_reports.fnFilterClear();
				},
				error: function() {
					console.log('pte_handle_report_clone - FAIL');
				}
		});
		break;
	}
}

function pte_handle_report_settings(operation) {

	console.log('pte_handle_report_settings...');
	var metaObj = jQuery('#pte_selected_topic_meta');
	var topicDomId = metaObj.data('tdid');
	var topicId = metaObj.data('tid');
	var topicTypeId = metaObj.data('ttid');
	var topicTypeSpecial = metaObj.data('special');
	var topicTypeKey = metaObj.data('tkey');
	var mapData = pte_make_map_data(topicDomId, topicId, topicTypeId, 0, topicTypeSpecial, 0, "to_topic_designer_by_id");

	switch(operation) {
		case 'refresh':
			jQuery('#pte_refresh_report_loading').show();
			jQuery('#alpn_vault_copy').removeClass('pte_extra_button_enabled').addClass('pte_extra_button_disabled');
			jQuery('#alpn_vault_copy_go').removeClass('pte_extra_button_enabled').addClass('pte_extra_button_disabled');
			var allData = pte_get_report_data(mapData);
			jQuery.ajax({
				url: alpn_templatedir + 'alpn_handle_report_change.php',
				type: 'POST',
				data: {
					report_meta: JSON.stringify(allData)
				},
				dataType: "json",
				success: function(json) {
					console.log('pte_handle_report_change - SUCCESS');
					var filename = "placeholder.pdf";
					var srcFile = alpn_templatedir + "quick_report_tmp/" + json.pdf_key

					var xhr = new XMLHttpRequest();
					xhr.open('GET', srcFile, true);
					xhr.responseType = 'blob';
					xhr.onreadystatechange = function () {
						if (xhr.readyState !== 4) {
							return;
						}
						var status = xhr.status;
						console.log(status);

						if (status == 204) {  //Premission Denied
							console.log("Permission Denied");
							return;
						}

						if ((status >= 200 && status < 300) || status === 304) {
							pdfui.openPDFByFile(xhr.response).catch(function (e) {
									if (e.error === 11 && e.encryptDict.Filter === 'FOPN_foweb') {
											console.log("ENCRYPTED DOC");
											var fileOpenKey = getFileOpenKey(e.encryptDict);
											pdfui.reopenPDFDoc(e.pdfDoc, {
													fileOpen: {
															encryptKey: fileOpenKey
													}
											})
									}
							})
						} else {
							console.log("ERROR OPENING FILE");
							console.log(xhr);
							console.log(status);
						}
					};
					xhr.send();
				},
				error: function() {
					console.log('pte_handle_report_change - FAIL');
				}
		})
		break;
		case 'delete':
			console.log('Handling Delete...');
			var parms = {'report_id': pte_selected_report_template};
			pte_show_message('yellow_question', 'confirm', 'Please confirm delete:', 'pte_handle_delete_report', JSON.stringify(parms));
		break;
		case 'clone':
				var security = specialObj.security;
			console.log('Handling Clone...');
			jQuery.ajax({
				url: alpn_templatedir + 'alpn_handle_clone_report.php',
				type: 'POST',
				data: {
					report_dom_id: pte_selected_report_template,
					security: security,
				},
				dataType: "json",
				success: function(json) {
					console.log('pte_handle_report_clone - SUCCESS');
					console.log(json);
					var reportDomId = json.dom_id;
					pte_selected_report_template = reportDomId;
					wpDataTables.table_reports.fnFilterClear();
				},
				error: function() {
					console.log('pte_handle_report_clone - FAIL');
				}
		});
		break;
		case 'save':
			console.log('Handling Save...');
			var fieldObj = jQuery('#pte_report_name_field');
			var fieldName = fieldObj.val();
			if (!fieldName) {
				fieldObj.attr('placeholder', "Name Required...");
				return;
			}
			var allData = pte_get_report_data(mapData);
			allData.report_name = fieldName;
			allData.template_type = 'report';
			allData.topic_type_key = topicTypeKey;
			allData.report_template_dom_id = pte_selected_report_template;
			jQuery.ajax({
				url: alpn_templatedir + 'alpn_handle_save_report.php',
				type: 'POST',
				data: {
					report_data: JSON.stringify(allData)
				},
				dataType: "json",
				success: function(json) {
					console.log('pte_handle_save_report - SUCCESS');
					var reportDomId = json.dom_id;
					pte_selected_report_template = reportDomId;
					wpDataTables.table_reports.fnFilterClear(); //TODO do the paging thing.
				},
				error: function() {
					console.log('pte_handle_save_report - FAIL');
				}
		})
		break;
	}
}

function alpn_mission_control(operation, uniqueRecId = '', overRideTopic = ''){

	var tabId = jQuery("button.tablinks.pte_tab_button_active").data('tab-id');
	var subjectToken = jQuery("button.tablinks.pte_tab_button_active").data('stoken');

	var topicDomId = jQuery('#pte_selected_topic_meta').data('tdid');
	var topicId = jQuery('#pte_selected_topic_meta').data('tid');
	var topicTypeId = jQuery('#pte_selected_topic_meta').data('ttid');
	var topicTypeSpecial = jQuery('#pte_selected_topic_meta').data('special');
	var security = specialObj.security;

	var returnDetails = {
		"return_to": overRideTopic,
		"tab_id": tabId,
		"topic_id": topicId,
		"topic_type_id": topicTypeId,
		"topic_special": topicTypeSpecial,
		"topic_dom_id": topicDomId,
		"subject_token": subjectToken
	};

	switch(operation) {

		case 'manage_connections':

			if (jQuery("#pte_editor_container").data("mc")) {return;}

			jQuery.ajax({
				url: alpn_templatedir + 'alpn_manage_connections.php',
				type: 'POST',
				data: {
					security: security,
					return_details: JSON.stringify(returnDetails)
				},
				dataType: "html",
				success: function(html) {
					console.log('Manage Connections SUCCESS');
					alpn_deselect();
					pte_close_chat_panel();
					jQuery('#alpn_edit_container').html(html).fadeIn();
					var pte_connections_setting = JSON.parse(jQuery('#pte_connection_manager_outer :input')[2].value);
					wdtRenderDataTable(jQuery('#table_connections'), pte_connections_setting);
					alpn_prepare_search_field('#table_connections_filter');
					//jQuery(nameFieldHtml).insertBefore('#table_connections_filter');
					wpDataTables.table_connections.fnSettings().oLanguage.sZeroRecords = 'No Connections';
					wpDataTables.table_connections.fnSettings().oLanguage.sEmptyTable = 'No Connections';
					wpDataTables.table_connections.addOnDrawCallback( function(){
						pte_draw_connections_table();
					})
					var mapData = pte_make_map_data('replace_me');
					pte_manage_history(mapData);
				},
				error: function() {
					console.log('Manage Connections ERROR');
				}
			});

		break;

		case 'make_default_topic':
		var tableId = "table_tab_" + tabId;
		var trObj =  jQuery('#alpn_main_container #alpn_field_' + uniqueRecId).closest('tr');
		var vaultRowData = wpDataTables[tableId].fnGetData(trObj);

		if (vaultRowData.length) {

			var newLinkId = vaultRowData[12];
			var ownerTopicId1 = vaultRowData[0];
			var topicSubjectToken = subjectToken;

			jQuery.ajax({
				url: alpn_templatedir + 'alpn_make_default_topic.php',
				type: 'POST',
				data: {
					unique_record_id: uniqueRecId,
					security: security,
   				new_link_id: newLinkId,
					owner_topic_id_1: ownerTopicId1,
					topic_subject_token: topicSubjectToken,
				},
				dataType: "json",
				success: function(json) {
					console.log('make_default_topic SUCCESS');
					console.log(json);
					var oldDefaultTopic = json.old_link_id;
					if (oldDefaultTopic) {
						var oldTitleDom = jQuery("div.pte_topic_links_title[data-link-id=" + oldDefaultTopic + "]");
						oldTitleDom.find('i.pte_default_topic').remove();
					}
					var newTitleDom = jQuery("div.pte_topic_links_title[data-link-id=" + newLinkId + "]");
					var defaultTopicHtml = "<i class='far fa-check-circle pte_default_topic' title='Default Topic'></i>";
					newTitleDom.append(defaultTopicHtml);
				},
				error: function() {
					console.log('make_default_topic ERROR');
				}
			})
		}
		break;

		case 'pdf_topic':
		var security = specialObj.security;
		console.log('PDF TOPIC...');
			jQuery.ajax({
				url: alpn_templatedir + 'alpn_handle_topic_pdf.php',
				type: 'POST',
				data: {
					uniqueRecId: uniqueRecId,
					security: security,
					return_details: JSON.stringify(returnDetails)
				},
				dataType: "html",
				success: function(html) {

					pte_clear_message();
					alpn_mode = 'report';
					alpn_handle_select(uniqueRecId);
					pte_start_chat("unique_record_id", uniqueRecId);
					jQuery('#alpn_edit_container').html(html).fadeIn();
						var viewerSettings = {
								'sidebar_state': 'closed'
						}
					pte_setup_pdf_viewer(viewerSettings);
					if (!pte_back_button) {
						var metaObj = jQuery('#pte_selected_topic_meta');
						var topicDomId = metaObj.data('tdid');
						var topicId = metaObj.data('tid');
						var topicTypeId = metaObj.data('ttid');
						var topicTypeSpecial = metaObj.data('special');
						var mapData = pte_make_map_data(topicDomId, topicId, topicTypeId, tabId, topicTypeSpecial, 0, "to_topic_designer_by_id");
						pte_manage_history(mapData);
					}
					pte_back_button = false;
					pte_handle_report_settings('refresh');
					pte_manage_report_table_select();
					var nameFieldHtml = "<input type='text' id='pte_report_name_field' placeholder='Template Name...'></input>";
					var alpn_report_table_settings = JSON.parse(jQuery('#pte_saved_reports :input')[2].value);
					wdtRenderDataTable(jQuery('#table_reports'), alpn_report_table_settings);
					alpn_prepare_search_field('#table_reports_filter');
					jQuery(nameFieldHtml).insertBefore('#table_reports_filter');
					wpDataTables.table_reports.fnSettings().oLanguage.sZeroRecords = 'No Saved Reports';
					wpDataTables.table_reports.fnSettings().oLanguage.sEmptyTable = 'No Saved Reports';
					wpDataTables.table_reports.addOnDrawCallback( function(){
						alpn_handle_reports_table();
					})
				},
      	error: function() {
		//TODO
      	}
  	})
		break;
		case 'add_topic':
		var security = specialObj.security;
			var topicTypeId = jQuery('#alpn_selector_topic_type').val();
			if (overRideTopic != '') {topicTypeId = overRideTopic};
			jQuery.ajax({
				url: alpn_templatedir + 'alpn_handle_topic_add.php',
				type: 'POST',
				data: {
					topicTypeId: topicTypeId,
					topicTypeSpecial: topicTypeSpecial,
					previous_topic: alpn_oldSelectedId,
					security: security,
					return_details: JSON.stringify(returnDetails)
				},
				dataType: "html",
				success: function(html) {
					jQuery('#alpn_edit_container').html(html).fadeIn();
					var formId = jQuery('.wpforms-form').data('formid');
					wpforms.ready(); //required to ajax up the form
					alpn_deselect();
					pte_close_chat_panel();
					//WORKING
					bindWpformsAjaxSuccess(formId,  function(){	//Handle Successful Add
						alpn_handle_topic_done(formId); //show results
					});
					var mapData = pte_make_map_data('replace_me');
					pte_manage_history(mapData);
				},
      	error: function() {
		//TODO
      	}
  	})
		break;
		case 'edit_topic':
		var security = specialObj.security;
			var alpn_selected_type = alpn_select_type(uniqueRecId);
			if (overRideTopic != '') {alpn_selected_type = overRideTopic};
			jQuery.ajax({
				url: alpn_templatedir + 'alpn_handle_topic_edit.php',
				type: 'POST',
				data: {
					uniqueRecId: uniqueRecId,
					alpn_selected_type: alpn_selected_type,
					security: security,
					return_details: JSON.stringify(returnDetails)
			},
				dataType: "html",
				success: function(html) {
					jQuery('#alpn_edit_container').html(html).fadeIn();
					pte_close_chat_panel();

					jQuery("div#pte_editor_container[data-personal-topic='true'] div.wpforms-field-email input").attr('tabindex', '-1');  //for email field, makes it non tabbable
					var formId = jQuery('.wpforms-form').data('formid');
					wpforms.ready(); //required to ajax up the form
					var textArea = jQuery('.wpforms-field-textarea textarea');
					if (textArea.length) {
						textArea.val(textArea.val().replaceAll("*r*n*", "\r\n"));    //TODO will this work properly when two textareas are on the same form
					}
					bindWpformsAjaxSuccess(formId, function(){	//Handle Successful Add
						alpn_handle_topic_done(formId); //show results
					});
					var mapData = pte_make_map_data('replace_me');
					pte_manage_history(mapData);
					//TODO update icon with new image_handle data if needed.
					var newUser = jQuery("div#alpn_field_" + uniqueRecId);
					if (newUser.data('nm') == 'yes') { //Put them in edit mode for their topic. Tell em something
						pte_show_message('green', 'ok', 'Welcome to ProTeam Edge. Please complete and save your personal profile. Keeping it updated is a good idea because your contacts will see this information.');
						newUser.data('nm', 'no');
					}
				},
				error: function() {
					//TODO
				}
			})
		break;

		case 'go_back':
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
				break;

				case 'report':
					alpn_mission_control('pdf_topic', uniqueRecId);
				break;
			}
		break;

		case 'select_topic':
			pte_active_tabs = []; //reset all row-selected state for tabs
			pte_selected_report_template = '';  //TODO unless switching between types

			var pteSelectedType = alpn_select_type(uniqueRecId);
			if (pteSelectedType == 'user') {
				console.log("Selecting User Topic");
				var newUser = jQuery("div#alpn_field_" + uniqueRecId);
				if (newUser.data('nm') == 'yes') { //Put them in edit mode for their topic. Tell em something
					alpn_mission_control('edit_topic', uniqueRecId);
					alpn_handle_select(uniqueRecId);
					return;
				}
			}
			alpn_handle_select(uniqueRecId);
			pte_start_chat("unique_record_id", uniqueRecId);

			jQuery.ajax({
				url: alpn_templatedir + 'alpn_handle_topic_select.php',
				type: 'POST',
				data: {
					uniqueRecId: uniqueRecId,
					security: security
				},
				dataType: "html",
				success: function(html) {
					alpn_mode = 'topic';
					if (pte_back_button) {
						tabId = history.state.tab_id;  //On back button, grab metadata from history state
					}
					jQuery('#alpn_edit_container').html(html).fadeIn();
					if (!jQuery('button#tab_' + tabId).length) {
								tabId = 0;      //maintain same tab unless too big.
					}
					pte_select_tab_when_ready(tabId);
					pte_initialize_topic_controls();
					wpforms.ready();
					alpn_setup_proteam_member_selector('all');
					pte_handle_tab_bar_scroll();


					if (!pte_back_button) {
						var metaObj = jQuery('#pte_selected_topic_meta');
						var topicDomId = metaObj.data('tdid');
						var topicId = metaObj.data('tid');
						var topicTypeId = metaObj.data('ttid');
						var topicTypeSpecial = metaObj.data('special');
						var mapData = pte_make_map_data(topicDomId, topicId, topicTypeId, tabId, topicTypeSpecial);
						pte_manage_history(mapData);
					}
					pte_back_button = false;
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
		var security = specialObj.security;
			console.log("Selecting Vault...");
			pte_handle_active_toolbar('none');  //Resets active toolbar if needed

			if (pte_back_button) {
				tabId = history.state.tab_id;  //On back button, grab metadata from history state
			}
			alpn_handle_select(uniqueRecId);
			var trObj =  jQuery('div.alpn_column_1 #alpn_field_' + alpn_oldSelectedId).closest('tr');
			var alpn_selected_type = alpn_select_type(uniqueRecId);
			pte_start_chat("unique_record_id", uniqueRecId);
			jQuery.ajax({
				url: alpn_templatedir + 'alpn_handle_vault.php',
				type: 'POST',
				data: {
					uniqueRecId: uniqueRecId,
					security: security,
					alpn_selected_type: alpn_selected_type
				},
				dataType: "html",
				success: function(html) {
					pte_clear_message();
					alpn_mode = 'vault';
					jQuery('#alpn_edit_container').html(html).fadeIn();
						var viewerSettings = {
								'sidebar_state': 'closed'
						}
						pte_setup_pdf_viewer(viewerSettings);

					if (jQuery('#alpn_outer_vault .wpdt-c :input')[2]) {
						if (pte_global_vault_item_dom_id) {
							alpn_set_vault_to_first_row = false;
							alpn_oldVaultSelectedId = '';
						} else {
							alpn_set_vault_to_first_row = true;
							alpn_oldVaultSelectedId = '';
						}

						var alpn_vault_table_settings = JSON.parse(jQuery('#alpn_outer_vault :input')[2].value);
						//console.log(alpn_vault_table_settings);  SETTINGS TO TABLE fed to datatable. Not a bad place to change settings.
						//console.log(alpn_vault_table_settings.selector);
						wdtRenderDataTable(jQuery('#table_vault'), alpn_vault_table_settings);
					  alpn_prepare_search_field('#table_vault_filter');
						wpDataTables.table_vault.fnSettings().oLanguage.sZeroRecords = 'No Vault Items';
						wpDataTables.table_vault.fnSettings().oLanguage.sEmptyTable = 'No Vault Items';
						wpDataTables.table_vault.addOnDrawCallback( function(){
							alpn_handle_vault_table();
						})
					}

					if (!pte_back_button) {
						var metaObj = jQuery('#pte_selected_topic_meta');
						var topicDomId = metaObj.data('tdid');
						var topicId = metaObj.data('tid');
						var topicTypeId = metaObj.data('ttid');
						var topicTypeSpecial = metaObj.data('special');
						var mapData = pte_make_map_data(topicDomId, topicId, topicTypeId, tabId, topicTypeSpecial, 0, "to_topic_vault_by_id");
						pte_manage_history(mapData);
					}
					pte_back_button = false;
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
		jQuery('div.alpn_column_1 #alpn_field_' + alpn_oldSelectedId).parent().attr('style', 'background-color: #D8D8D8 !important;');
	}
	//TODO manage_chat()
}

function alpn_deselect () {

	if (alpn_oldSelectedId) {
		jQuery('div.alpn_column_1 #alpn_field_' + alpn_oldSelectedId).parent().attr('style', 'background-color: #F8F8F8 !important;');
	}
	alpn_oldSelectedId = "";
}

function alpn_handle_select(uniqueId) {
	alpn_deselect();
	jQuery('div.alpn_column_1 #alpn_field_' + alpn_oldSelectedId).parent().attr('style', 'background-color: #F8F8F8 !important;');
	jQuery('div.alpn_column_1 #alpn_field_' + uniqueId).parent().attr('style', 'background-color: #D8D8D8 !important;');
	alpn_oldSelectedId = uniqueId;
}

// For AJAX with wpforms, how to handle callback of success or failure. TODO -- implement Failed

function bindWpformsAjaxSuccess (table_profile_id, callBackFunc) {
			jQuery('#wpforms-form-' + table_profile_id).bind('wpformsAjaxSubmitSuccess', callBackFunc);
}

function bindWpformsAjaxFailed (table_profile_id, callBackFunc) {
			jQuery('#wpforms-form-' + table_profile_id).bind('wpformsAjaxSubmitFailed', callBackFunc);
}


function alpn_handle_topic_done(formId){

	console.log('alpn_handle_topic_done - Form Id', formId);
	var security = specialObj.security;
	var topicDomId = '';
	var topicTypeSpecial = '';
	var topicTypeId = 0;
	var topicId = 0;
	var connectedTopicId = 0;
	var connectedTopicDomId = '';
	var tabId = 0;
	var topicSubjectToken = '';
	var topicTableType = '';
	var topicReturnTo = {};

	jQuery.ajax({   //TODO combine into topic_select.
		url: alpn_templatedir + 'alpn_topic_latest.php',
		type: 'GET',
		dataType: "json",
		data: {
					security:security
					},
		success: function(topic) {

			console.log(topic);

			//Main Topic
			tabId = 0;
			topicDomId = topic.dom_id;
			topicId = topic.id;
			topicTypeSpecial = topic.special;
			topicTypeId = topic.topic_type_id;
			topicSubjectToken = '';
			topicTableType = (topicTypeSpecial == 'contact') ? 'network' : 'topic';   //TODO Move completely to topicSpecial for this.
			connectedTopicId = 0;
			connectedTopicDomId = '';

			topicReturnTo = JSON.parse(topic.last_return_to);

			if (typeof topicReturnTo.pte_error != "undefined" && topicReturnTo.pte_error) {
				pte_show_message('red', 'ok', topicReturnTo.pte_error_message);
				return;
			}

			var returnHandler = (typeof topicReturnTo['return_to'] != "undefined" && topicReturnTo['return_to']) ? (topicReturnTo['return_to'].toString().substring(0,5) == "core_") ? true : false : false;

			jQuery.ajax({
					url: alpn_templatedir + 'alpn_handle_topic_select.php',
					type: 'POST',
					data: {
						uniqueRecId: returnHandler ? topicReturnTo.topic_dom_id : topic.dom_id,
						security:security
					},
					dataType: "html",
					success: function(html) {
							//TODO -- Manage vault area after add/edit -- Bug //
							//TODO Consider using SYNC from function.php to notify client to go to a specific record.
							jQuery('#alpn_edit_container').html(html).fadeIn();
							pte_initialize_topic_controls()
							alpn_setup_proteam_member_selector('all');
							pte_handle_tab_bar_scroll();

						if (returnHandler)	{ //Linked Topic Handler
							tabId = topicReturnTo.tab_id;
							topicDomId = topicReturnTo.topic_dom_id;
							topicId = topicReturnTo.topic_id;
							topicSubjectToken = topicReturnTo.subject_token;
							topicTypeSpecial = topicReturnTo.topic_special;
							topicTypeId = topicReturnTo.topic_type_id;
							topicTableType = (topicTypeSpecial == 'contact') ? 'network' : 'topic';   //TODO Move completely to topicSpecial for this.
							connectedTopicId = topic.id;
							connectedTopicDomId = topic.dom_id;
						}
							var pageData = {
								'owner_id': alpn_user_id,
								'topic_id': topicId,
								'dom_id': topicDomId,
								'subject_token': topicSubjectToken,
								'table_type': topicTableType,
								'tab_id': tabId,
								'connected_topic_id': connectedTopicId,
								'connected_topic_dom_id': connectedTopicDomId
							}

							//pte_active_tabs[tabId]  = topic.dom_id;
							pte_active_tabs[tabId]  = connectedTopicDomId;
							if (topicTypeSpecial != "user") {pte_set_table_page(pageData);}
							pte_handle_tab_selected(jQuery('#tab_' + tabId));

					if (!returnHandler)	{ //Linked Topic Handler
							console.log('Handling Main Topic...');
							if (topic['last_op'] == 'edit') { //edit
								var line1 = topic['name'];
								var line2 = topic['about'];
								var fieldNameId = 'div.alpn_column_1 #alpn_field_' + topicDomId + ' .alpn_name';
								var fieldAboutId = 'div.alpn_column_1 #alpn_field_' + topicDomId + ' .alpn_about';
								jQuery(fieldNameId).html(line1);
								jQuery(fieldAboutId).html(line2);
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


function pte_new_topic_type () {

	var topicArea = jQuery("#pte_topic_manager_inner");
	var security = specialObj.security;
	jQuery.ajax({
		url: alpn_templatedir + 'alpn_get_new_topic_ux.php',
		type: 'POST',
		data: {
			security:security,
		},
		dataType: "html",
		success: function(html) {
			console.log('alpn_get_new_topic_ux Succeeded');

				topicArea.html(html);
				alpn_handle_topic_type_row_selected();
		},
		error: function() {
			console.log('alpn_get_new_topic_ux FAILED');
		//TODO
		}
	});
}

function pte_handle_select_topic_type_row(rowId, selectedItem){
	console.log('pte_handle_select_topic_type');

	var item = jQuery(selectedItem);
	var formID = jQuery('#pte_topic_type_property_editor').data('pttfid');
	var ptId = item.data('ptid');
	var ptName = item.data('ptname');
	if (pte_old_topic_type_name) {
		var oldItem = jQuery('li[data-ptname= ' + pte_old_topic_type_name + ']');
		if (oldItem.hasClass('pte_topic_type_row_selected')) {
			oldItem.removeClass('pte_topic_type_row_selected');
		}
	}
	if (!item.hasClass('pte_topic_type_row_selected')) {
		item.addClass('pte_topic_type_row_selected');
	}
	pte_old_topic_type_name = ptName;
	pte_show_topic_type_object(formID, ptName);
}



function pte_handle_topic_type_check (checkContainerId){

	console.log("pte_handle_topic_type_check");

	var checkHtml = "<i class='fa fa-check'></i>";
	var hiddenIconTrue = "<i class='far fa-eye-slash' title='Field Hidden'></i>";
	var hiddenIconFalse = "<i class='far fa-eye' title='Field Visible'></i>";

	var checkContainer = jQuery("#" + checkContainerId);
	var checkContainerStartState = (checkContainer.html().length == checkHtml.length) ? true : false;
	var checkContents = (checkContainerStartState) ? "&nbsp;" : checkHtml;
	checkContainer.html(checkContents);

	switch (checkContainerId) {
		case 'pte_topic_type_check_inner_hidden':
			var checkContainerNewState = checkContainerStartState ? "false" : "true";
			var checkContainerNewIcon = checkContainerStartState ? hiddenIconFalse : hiddenIconTrue;
			var typeKey = jQuery("li.pte_topic_type_row_selected").data("ptname");
			pte_selected_topic_type_object.field_map[typeKey].hidden = checkContainerNewState;
			jQuery("li[data-ptname=" + typeKey + "]").find("div#pte_hidden_icon_container").html(checkContainerNewIcon);
		break;
		case 'pte_topic_type_check_inner_hidden_print':
			var checkContainerNewState = checkContainerStartState ? "false" : "true";
			var checkContainerNewIcon = checkContainerStartState ? hiddenIconFalse : hiddenIconTrue;
			var typeKey = jQuery("li.pte_topic_type_row_selected").data("ptname");
			pte_selected_topic_type_object.field_map[typeKey].hidden_print = checkContainerNewState;
			jQuery("li[data-ptname=" + typeKey + "]").find("div#pte_hidden_icon_container").html(checkContainerNewIcon);
		break;
		case 'pte_topic_type_check_inner_newline':
			var checkContainerNewState = checkContainerStartState ? "false" : "true";
			var checkContainerNewIcon = checkContainerStartState ? hiddenIconFalse : hiddenIconTrue;
			var typeKey = jQuery("li.pte_topic_type_row_selected").data("ptname");
			pte_selected_topic_type_object.field_map[typeKey].newline = checkContainerNewState;
		break;

	}
	pte_save_topic_type_meta(true);
}

function pte_show_topic_type_object(formID, ptName) {

	console.log('pte_show_topic_type_object');

	var fieldMap = pte_selected_topic_type_object.field_map;
	//var selectedProp = ptName;
	var selectedProp = fieldMap[ptName];

	console.log(selectedProp);

	var friendlyName = selectedProp.friendly;
	var fieldType = selectedProp.type;
	var coreFieldType = fieldType.substring(0, 5) == 'core_' ? true : false;
	var schemaKey = selectedProp.schema_key;

	if (fieldType) {
		var fieldTypeArray = fieldType.split("_");
		switch(fieldTypeArray.length) {
			case 1:
				var newString = fieldTypeArray[0];
				var typeKeyUidHtml = "";
			break;
			case 2:
				var newString = fieldTypeArray[0] + "_" + fieldTypeArray[1];
				var typeKeyUidHtml = "";
			break;
			case 3:
				var newString = fieldTypeArray[0] + "_" + fieldTypeArray[1];
				var typeKeyUid = fieldTypeArray[2];
				var typeKeyUidHtml = "<div class='topic_type_check_container'><div class='pte_vault_row_20'>Unique Id</div><div class='pte_vault_row_80'>" + typeKeyUid + "</div></div>";
			break;
		}
		var fieldTypeHtml = "<div class='topic_type_check_container'><div class='pte_vault_row_20'>Type</div><div class='pte_vault_row_80'>" + newString + "</div></div>";
	} else {
		var fieldTypeHtml = "";
		var typeKeyUidHtml = "";
	}

	var propertyName = selectedProp.name;
	if (propertyName) {
		var propertyNameHtml = "<div class='topic_type_check_container'><div class='pte_vault_row_20'>Field Key</div><div class='pte_vault_row_80'>" + propertyName + "</div></div>";
	} else {
		var propertyNameHtml = "";
	}
	if (schemaKey && schemaKey.includes("_")) {

		var uPos = schemaKey.lastIndexOf("_") + 1;
		var schemaKeyLink = "<a href='https://schema.org/" + schemaKey.substr(uPos) + "' target='_blank' rel='noopener noreferrer' class='pte_topic_type_schema_key'>" + schemaKey.substr(uPos) + "</a>";
		var schemaKeyHtml = "<div class='topic_type_check_container'><div class='pte_vault_row_20'><a href='https://schema.org' target='_blank' rel='noopener noreferrer'>Schema.org</a></div><div class='pte_vault_row_80'>" + schemaKeyLink + "</div></div>";
	} else {
		var schemaKeyHtml = '';
	}

	var fieldFriendlyNameHtml = "<div class='topic_type_check_container'><div class='pte_vault_row_20 pte_topic_type_check_title pte_blue_link'>Name</div><div class='pte_vault_row_80'><input id='topic_property_friendly_name' type='text' value='" + friendlyName + "'></div></div>";
	var fieldRequired = selectedProp.required;
	if (fieldRequired == 'true') {
		var editableClass = 'pte_extra_button_disabled';
	} else {
		var editableClass = 'pte_extra_button_enabled';
	}
	var fieldHidden = selectedProp.hidden;
	if (fieldHidden == 'true') {
		var hiddenCheck ="<i class='fa fa-check'></i>";
	} else {
		var hiddenCheck ="";
	}

	var printHidden = selectedProp.hidden_print;
	if (printHidden == 'true') {
		var hiddenCheck2 ="<i class='fa fa-check'></i>";
	} else {
		var hiddenCheck2 ="";
	}

	var formNewline = selectedProp.newline;
	if (formNewline == 'true') {
		var hiddenCheck1 ="<i class='fa fa-check'></i>";
	} else {
		var hiddenCheck1 ="";
	}

	var removeButtonHtml = '';
	if (coreFieldType){
		 removeButtonHtml = "<div class='topic_type_check_container topic_type_check_container_gap'><div class='pte_vault_row_20 pte_topic_type_check_title_link' onclick='pte_handle_remove_topic_link_field();'>Remove</div><div class='pte_vault_row_80 pte_topic_type_property_editor_text'>Removes all links but does not delete linked Topics</div></div>";
	}

	var fieldHiddenHtml = "<div class='topic_type_check_container pte_topic_type_check_title_link " + editableClass + "' onclick='pte_handle_topic_type_check(\"pte_topic_type_check_inner_hidden\");'><div class='pte_vault_row_20'>Hide on Screen</div><div id='pte_topic_type_check_inner_hidden' class='pte_vault_row_80'>" + hiddenCheck + "</div></div>";
	var printHiddenHtml = "<div class='topic_type_check_container pte_topic_type_check_title_link " + editableClass + "' onclick='pte_handle_topic_type_check(\"pte_topic_type_check_inner_hidden_print\");'><div class='pte_vault_row_20'>Hide in Reports</div><div id='pte_topic_type_check_inner_hidden_print' class='pte_vault_row_80'>" + hiddenCheck2 + "</div></div>";
	var formNewlineHtml = !coreFieldType ? "<div class='topic_type_check_container pte_topic_type_check_title_link' onclick='pte_handle_topic_type_check(\"pte_topic_type_check_inner_newline\");'><div class='pte_vault_row_20'>New Line</div><div id='pte_topic_type_check_inner_newline' class='pte_vault_row_80'>" + hiddenCheck1 + "</div></div>" : "";

	var html =  schemaKeyHtml + propertyNameHtml + fieldTypeHtml + typeKeyUidHtml + fieldFriendlyNameHtml + fieldHiddenHtml  + printHiddenHtml + formNewlineHtml + removeButtonHtml;
	jQuery('#pte_topic_type_property_editor_proper').html(html);
	jQuery('#topic_property_friendly_name').donetyping(function(){
		var typeKey = jQuery("li.pte_topic_type_row_selected").data("ptname");
		var friendlyNameValue = jQuery('#topic_property_friendly_name').val();
		pte_selected_topic_type_object.field_map[typeKey].friendly = friendlyNameValue;
		jQuery("li[data-ptname=" + typeKey + "]").find("div#pte_topic_types_friendly_name").html(friendlyNameValue);
		pte_save_topic_type_meta(true);
	});
}

function pte_handle_remove_topic_link_field() {
	console.log("pte_handle_remove_topic_link_field");
	var typeKey = jQuery("li.pte_topic_type_row_selected").data("ptname");
	delete pte_selected_topic_type_object.field_map[typeKey];
	pte_save_topic_type_meta();
}


function pte_add_a_new_topic_type (data){   //Logged In
	console.log("pte_add_a_new_topic_type");
	console.log(data);
	var security = specialObj.security;
	var sourceTopicTypeId = data.id;  //This means the source for the new topic not the id of the new one

	jQuery.ajax({
		url: alpn_templatedir + 'pte_add_new_topic_type.php',
		type: 'POST',
		data: {
			source_topic_type_id: sourceTopicTypeId,
			security: security,
		},
		dataType: "json",
		success: function(json) {
			console.log(json);

			var newTopicTypeId = json.new_topic_type_id;
			var newPageNumber = json.page_number;
			var formId = json.form_id;
			var table = wpDataTables.table_topic_types;

			pte_oldTopicTypeSelectedId = formId;

			var currentPage = table.api().page();
			table.api().page(newPageNumber).draw('page');

		},
		error: function() {
			console.log('alpn_add_new_topic_type FAILED');
		//TODO
		}
	});
}

function pte_add_link_topic_type(data) {
	//TODO no underscores in certain names?

	console.log("pte_add_link_topic_type");
	console.log(data);

	// if (pte_old_topic_type_name) {
	// 	var oldItem = jQuery('li[data-ptname= ' + pte_old_topic_type_name + ']');
	// 	if (oldItem.hasClass('pte_topic_type_row_selected')) {
	// 		oldItem.removeClass('pte_topic_type_row_selected');
	// 		pte_topic_type_row_selected = "";
	// 	}
	// }


	var typeKey = jQuery('ul#pte_topic_type_properties_list li').last().attr('data-ptname');
	var lastFieldMap = pte_selected_topic_type_object.field_map[typeKey];
	var newId = (typeof lastFieldMap['new_position'] != "undefined") ? parseInt(lastFieldMap['new_position']) + 1 : parseInt(lastFieldMap['id']) + 101
	var jEl = jQuery(data.element);
	var schemaKey = jEl.attr('data-pttk');
	var formId = jEl.attr('value');
	var friendlyName = jEl.text();
	var schemaParts = schemaKey.split('_');
	var coreType = schemaParts[1];
	var schemaType = coreType.charAt(0).toUpperCase() + coreType.slice(1);
	var fieldName = "pte_" + coreType + "_" + newId;
	var newField = {
		'key': fieldName,
		'name': fieldName,
		'friendly': friendlyName,
		'id': newId,
		'new_position': newId,
		'type': schemaKey,
		'required': 'false',
		'hidden': 'false'
	}
	pte_selected_topic_type_object.field_map[fieldName] = newField;
	pte_save_topic_type_meta();
	//TODO someday write the update into the dom rather than refreshing the table OR page properly.
	//var topicTypeItemsHtml = "<li class='pte_topic_type_items_li' data-ptid='" + ptId + "' data-ptname=\"{$value['name']}\"><div id='pte_topic_types_friendly_name' class='pte_vault_row_70 pte_vault_bold'>{$value['friendly']}</div><div id='pte_link_icon_container' class='pte_vault_row_10'>{$linkFieldType}</div><div id='pte_required_icon_container' class='pte_vault_row_10'>{$requiredIcon}</div><div id='pte_hidden_icon_container' class='pte_vault_row_10'>{$hiddenIcon}</div><div</li>";
}

function pte_handle_topic_type_order(evt){
	console.log('pte_handle_topic_type_order...');
	var listObj = {};
	var newFieldMap = [];
	var listKey = "";
	var typeKey = jQuery("li.pte_topic_type_row_selected").data("ptname");
	var allListItems = jQuery("ul#pte_topic_type_properties_list li");
	allListItems.each(function(index, value){  //TODO javascript wants to sort JSON by key.
		listObj = jQuery(value);
		listKey = listObj.data('ptname');
	  pte_selected_topic_type_object.field_map[listKey].new_position = index + 1;
	});
	pte_save_topic_type_meta(true);
}

function pte_change_topic_visibility(data){
	console.log('pte_change_topic_visibility...');
	pte_selected_topic_type_object.topic_class = data.id;
	pte_save_topic_type_meta(true);
}


function pte_save_topic_type_meta(skipRefresh = false) {
	console.log('pte_save_topic_type_meta...');
	console.log(pte_selected_topic_type_object);
	var security = specialObj.security;


	var formId = jQuery('#pte_topic_type_property_editor').data('pttfid');
	jQuery.ajax({
		url: alpn_templatedir + 'alpn_handle_update_topic_type_field_properties.php',
		type: 'POST',
		data: {
			form_id: formId,
			topic_type_object: JSON.stringify(pte_selected_topic_type_object),
			security: security
		},
		dataType: "json",
		success: function(json) {
			//var sttobj = JSON.parse(json.topic_type_object.replace(/\\(.)/mg, "$1"));
			if (!skipRefresh) {
				var pageData = {
					'table_type': 'table_topic_types',
					'topic_type_form_id': formId,
					'owner_id': alpn_user_id
				}
				pte_set_table_page(pageData);
			}

		},
		error: function() {
			console.log('pte_save_topic_type_meta FAILED');
		//TODO
		}
	});

}


function pte_save_vault_meta(){

	if (alpn_oldVaultSelectedId) {

		var vaultSelectedRow =  jQuery('#alpn_field_' + alpn_oldVaultSelectedId).closest('tr');
		var vaultRowData = wpDataTables.table_vault.fnGetData(vaultSelectedRow);
		var vaultId =  vaultRowData[0];
		var fieldName = jQuery('#alpn_name_field').val();   //Name
		var description = jQuery('#alpn_about_field').val();   //About/Description
		var permissions = jQuery('#alpn_selector_sharing').find(':selected');

		if (typeof permissions[0] !== "undefined") {
			var permissionValue = permissions[0]['value'];
		} else{
			var permissionValue = '40';	 //Private though should never be empty
		}

		// console.log('Saving Vault Meta');
		// console.log(vaultId);
		// console.log(fieldName);
		// console.log(description);
		// console.log(permissionValue);
	var security = specialObj.security;
		jQuery.ajax({
			url: alpn_templatedir + 'alpn_handle_update_vault_meta.php',
			type: 'POST',
			data: {
				vault_id: vaultId,
				field_name: fieldName,
				description: description,
				security: security,
				permission_value: permissionValue
			},
			dataType: "json",
			success: function(json) {

					//Update in table data structure so that when user navs within page, the data is there too for prepopulating the field. False option so it doesn't reresh from the DB
					wpDataTables.table_vault.fnUpdate(description, vaultSelectedRow, 6, false);
					wpDataTables.table_vault.fnUpdate(fieldName, vaultSelectedRow, 7, false);
					wpDataTables.table_vault.fnUpdate(permissionValue, vaultSelectedRow, 2, false);

					description = description ? description : " - -";
					fieldName = fieldName ? fieldName : " - -";

					var vaultDescriptionEl = vaultSelectedRow.find('#pte_vault_desc_content');
					vaultDescriptionEl.html(description);

					var vaultNameEl = vaultSelectedRow.find('#pte_vault_name_content');
					vaultNameEl.html(fieldName);

					var vaultPermissionEl = vaultSelectedRow.find('#pte_vault_permission_content');
					vaultPermissionEl.html(access_levels[permissionValue]);

			},
			error: function() {
				console.log('Saving Vault Meta AJAX RESPONSE FAILED');
			//TODO
			}
		});
	}
}
