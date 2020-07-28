<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

//TODO Check logged in, etc

$siteUrl = get_site_url();
$ppCdnBase = "https://storage.googleapis.com/pte_media_store_1/";

$qVars = $_GET;
$recordId = isset($qVars['uniqueRecId']) ? $qVars['uniqueRecId'] : 0;
$alpn_selected_type = isset($qVars['alpn_selected_type']) ? $qVars['alpn_selected_type'] : false;

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

$results = $wpdb->get_results(
	$wpdb->prepare("SELECT t.*, tt.id AS topic_type_id, tt.form_id, tt.name AS topic_name, tt.icon, tt.topic_type_meta, tt.html_template, t2.image_handle AS profile_handle FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id LEFT JOIN alpn_topics t2 ON t.connected_id = t2.owner_id AND t2.topic_type_id=5 WHERE t.dom_id = %s", $recordId)
 );

if (isset($results[0])) {

	$record = $results['0'];
	$topipTypeId = $record->topic_type_id;
	$topicIcon = $record->icon;
	$topicName = $record->topic_name;
	$topicId = $record->id;
	$topicImageHandle = $record->image_handle;
	$topicProfileHandle = $record->profile_handle;

	$context = $topicName;
	if ($topipTypeId == '4') {$context = "Network";}
	if ($topipTypeId == '5') {$context = "Personal";}

	$contextAll = "{$context}";
}

$permissionLevel = '40'; //TODO

$viewerUrl = $siteUrl;


if ($topicProfileHandle) {
	$topicImage = "<img src='{$ppCdnBase}{$topicProfileHandle}' style='height: 35px; width: 35px; border-radius: 50%; margin-left: 10px;'>";
} else if ($topicImageHandle) {
	$topicImage = "<img src='{$ppCdnBase}{$topicImageHandle}' style='height: 35px; width: 35px; border-radius: 50%; margin-left: 10px;'>";
} else {
	$topicImage = "<i class='{$topicIcon}' style='margin-left: 15px;  margin-top: 2px; color: #4499d7; font-size: 1.2em;'></i>";
}

$pdfViewer = "
<div id='pte_pdf_ui'></div>
<template role='pc-layout-template-container'>
	<webpdf>
		<toolbar name='toolbar'>
			<div style='display: flex; flex-direction: row; padding: 0 0 0 40px; background-color: #F8F8F8; border 0;'>
				<group-list name='home-toolbar-group-list'>
					<group name='home-tab-group-select' retain-count='2'>
						<hand-button></hand-button>
						<selection-button></selection-button>
					</group>
					<group name='home-tab-group-zoom' retain-count='3'>
						<zoom-out-button></zoom-out-button>
						<zoom-in-button></zoom-in-button>
						<editable-zoom-dropdown></editable-zoom-dropdown>
					</group>
					<group name='home-tab-group-nav' retain-count='3'>
						<goto-prev-page-button></goto-prev-page-button>
						<goto-next-page-button></goto-next-page-button>
						<goto-page-input></goto-page-input>
					</group>
				</group-list>
				<fpmodule:file-property-button></fpmodule:file-property-button>
				<print:print-button></print:print-button>
			</div>
		</toolbar>
		<div class='fv__ui-body'>
			<sidebar name='sidebar' @controller='sidebar:SidebarController'>
				<search-sidebar-panel></search-sidebar-panel>
				<bookmark-sidebar-panel></bookmark-sidebar-panel>
				<thumbnail-sidebar-panel></thumbnail-sidebar-panel>
				<commentlist-sidebar-panel>
					<slot for='header'>
						<dropdown class='comment-list-dropdown' icon-class='fv__icon-toolbar-more'>
							<comment-list:expand-pages-button></comment-list:expand-pages-button>
							<comment-list:collapse-pages-button></comment-list:collapse-pages-button>
						</dropdown>
					</slot>
				</commentlist-sidebar-panel>
				<attachment-sidebar-panel></attachment-sidebar-panel>
			</sidebar>
			<distance:ruler-container name='pdf-viewer-container-with-ruler'>
				<slot>
					<viewer @zoom-on-pinch @zoom-on-doubletap @zoom-on-wheel @touch-to-scroll></viewer>
				</slot>
			</distance:ruler-container>
		</div>
		<template name='template-container'>
			<create-stamp-dialog></create-stamp-dialog>
			<print:print-dialog></print:print-dialog>
			<create-ink-sign-dialog></create-ink-sign-dialog>
			<fpmodule:file-property-dialog></fpmodule:file-property-dialog>
			<!-- contextmenus -->
			<page-contextmenu></page-contextmenu>
			<default-annot-contextmenu></default-annot-contextmenu>
			<markup-contextmenu></markup-contextmenu>
			<markup-contextmenu name='fv--line-contextmenu'></markup-contextmenu>
			<markup-contextmenu name='fv--linearrow-contextmenu'></markup-contextmenu>
			<markup-contextmenu name='fv--ink-contextmenu'></markup-contextmenu>
			<markup-contextmenu name='fv--stamp-contextmenu'></markup-contextmenu>
			<markup-contextmenu name='fv--text-contextmenu'></markup-contextmenu>
			<markup-contextmenu name='fv--replace-contextmenu'></markup-contextmenu>
			<measurement-contextmenu></measurement-contextmenu>
			<default-annot-contextmenu name='fv--caret-contextmenu'></default-annot-contextmenu>
			<textmarkup-contextmenu name='fv--highlight-contextmenu'></textmarkup-contextmenu>
			<textmarkup-contextmenu name='fv--strikeout-contextmenu'></textmarkup-contextmenu>
			<textmarkup-contextmenu name='fv--underline-contextmenu'></textmarkup-contextmenu>
			<textmarkup-contextmenu name='fv--squiggly-contextmenu'></textmarkup-contextmenu>
			<freetext-contextmenu name='fv--callout-contextmenu'></freetext-contextmenu>
			<freetext-contextmenu name='fv--textbox-contextmenu'></freetext-contextmenu>
			<action-annot-contextmenu name='fv--image-contextmenu'></action-annot-contextmenu>
			<action-annot-contextmenu name='fv--link-contextmenu'></action-annot-contextmenu>
			<comment-card-contextmenu></comment-card-contextmenu>
			<text-sel:text-selection-tooltip></text-sel:text-selection-tooltip>
			<freetext:freetext-tooltip></freetext:freetext-tooltip>
		</template>
	</webpdf>
</template>
<template role='mobile-layout-template-container'>
	<webpdf>
		<div class='fv__ui-mobile-header' name='fv--mobile-header'>
			<div class='fv__ui-mobile-header-right' name='fv--mobile-header-right' style='margin-left: auto;'>
				<hand-button icon-class='fv__icon-mobile-topbar-hand'></hand-button>
				<mobile:pageview-dropdown name='fv--mobile-pageview-dropdown'>
					<single-page-button></single-page-button>
					<continuous-page-button></continuous-page-button>
				</mobile:pageview-dropdown>
				<dropdown class='fv__ui-mobile-topbar-tools-dropdown fv__ui-dropdown-hide-text' name='fv--mobile-topbar-tools-dropdown' @cannotBeDisabled>
					<open-localfile-button @cannotBeDisabled></open-localfile-button>
					<open-fromurl-button @cannotBeDisabled></open-fromurl-button>
					<print:print-button></print:print-button>
					<download-file-button></download-file-button>
					<full-screen:toggle-full-screen-button name='fv--toggle-full-screen-button' @hide-on-device='ios'></full-screen:toggle-full-screen-button>
					<fpmodule:file-property-button name='fv--file-property-button'></fpmodule:file-property-button>
				</dropdown>
			</div>
		</div>
		<div class='fv__ui-body'>
			<viewer @zoom-on-pinch @zoom-on-doubletap @touch-to-scroll></viewer>
		</div>
		<template name='template-container'>
			<create-stamp-dialog></create-stamp-dialog>
			<print:print-dialog></print:print-dialog>
			<loupe-tool-dialog></loupe-tool-dialog>
			<create-ink-sign-dialog></create-ink-sign-dialog>
			<distance:measurement-popup></distance:measurement-popup>
			<fpmodule:file-property-dialog></fpmodule:file-property-dialog>
			<redaction:redaction-page-dialog></redaction:redaction-page-dialog>
			<!-- contextmenus -->
			<page-contextmenu></page-contextmenu>
			<default-annot-contextmenu></default-annot-contextmenu>
			<markup-contextmenu></markup-contextmenu>
			<markup-contextmenu name='fv--line-contextmenu'></markup-contextmenu>
			<markup-contextmenu name='fv--linearrow-contextmenu'></markup-contextmenu>
			<markup-contextmenu name='fv--polylinedimention-contextmenu'></markup-contextmenu>
			<markup-contextmenu name='fv--polygondimension-contextmenu'></markup-contextmenu>
			<markup-contextmenu name='fv--circle-contextmenu'></markup-contextmenu>
			<markup-contextmenu name='fv--square-contextmenu'></markup-contextmenu>
			<markup-contextmenu name='fv--polyline-contextmenu'></markup-contextmenu>
			<markup-contextmenu name='fv--polygon-contextmenu'></markup-contextmenu>
			<markup-contextmenu name='fv--polygoncloud-contextmenu'></markup-contextmenu>
			<markup-contextmenu name='fv--ink-contextmenu'></markup-contextmenu>
			<markup-contextmenu name='fv--stamp-contextmenu'></markup-contextmenu>
			<markup-contextmenu name='fv--text-contextmenu'></markup-contextmenu>
			<markup-contextmenu name='fv--areahighlight-contextmenu'></markup-contextmenu>
			<markup-contextmenu name='fv--replace-contextmenu'></markup-contextmenu>
			<measurement-contextmenu></measurement-contextmenu>
			<default-annot-contextmenu name='fv--caret-contextmenu'></default-annot-contextmenu>
			<textmarkup-contextmenu name='fv--highlight-contextmenu'></textmarkup-contextmenu>
			<textmarkup-contextmenu name='fv--strikeout-contextmenu'></textmarkup-contextmenu>
			<textmarkup-contextmenu name='fv--underline-contextmenu'></textmarkup-contextmenu>
			<textmarkup-contextmenu name='fv--squiggly-contextmenu'></textmarkup-contextmenu>
			<freetext-contextmenu name='fv--typewriter-contextmenu'></freetext-contextmenu>
			<freetext-contextmenu name='fv--callout-contextmenu'></freetext-contextmenu>
			<freetext-contextmenu name='fv--textbox-contextmenu'></freetext-contextmenu>
			<action-annot-contextmenu name='fv--image-contextmenu'></action-annot-contextmenu>
			<action-annot-contextmenu name='fv--link-contextmenu'></action-annot-contextmenu>
			<comment-card-contextmenu></comment-card-contextmenu>
			<fileattachment-contextmenu></fileattachment-contextmenu>
			<media-contextmenu></media-contextmenu>
			<sound-contextmenu></sound-contextmenu>
			<redact-contextmenu></redact-contextmenu>
		</template>
	</webpdf>
</template>
";

$html="";
$html .= "<div class='outer_button_line'>
			   <i class='far fa-info-circle pte_icon_button' title='Topic Details' onclick='alpn_mission_control(\"go_back\", \"{$recordId}\")' style='font-size: 28px; width: 40px; float: left; margin-left: 10px;'></i>
			  <div id='alpn_message_area' class='alpn_message_area'></div>
			  <div id='alpn_vault_button_bar' class='alpn_vault_button_bar'>
				<i id='alpn_vault_comment' class='far fa-comment-alt-lines pte_icon_button' title='Give Feedback on this Item' onclick='alpn_vault_control(\"comment\")'></i>
				<i id='alpn_vault_message' class='far fa-share pte_icon_button' title='Send a Message About this Item' onclick='alpn_vault_control(\"message\")'></i>
				<i id='alpn_vault_chat' class='far fa-comments pte_icon_button' title='Send a Chat About this Item' onclick='alpn_vault_control(\"chat\")' ></i>
				<i id='alpn_vault_transfer' class='far fa-exchange-alt pte_icon_button' title='Transfer Data' onclick='alpn_vault_control(\"transfer\")'></i>
				<i id='alpn_vault_email' class='far fa-envelope pte_icon_button' title='Send a Link to this Item in Email' onclick='alpn_vault_control(\"email\")'></i>
				<i id='alpn_vault_fax' class='far fa-fax pte_icon_button' title='Send this Item as a Fax' onclick='alpn_vault_control(\"fax\")'></i>
				<i id='alpn_vault_download' class='far fa-cloud-download-alt pte_icon_button' title='Get Original or PDF Item' onclick='alpn_vault_control(\"download\")'></i>
				<i id='alpn_vault_edit_original' class='fab fa-google-drive pte_icon_button' title='Open Item in Original Cloud Service' onclick='alpn_vault_control(\"open_original\")'></i>
				<div style='display: inline-block; width: 20px;'></div>
				<i id='alpn_vault_new' class='far fa-plus-circle pte_icon_button' title='Open Item in Original Cloud Service' onclick='alpn_vault_control(\"add\")'></i>
				<i id='alpn_vault_edit' class='far fa-pencil-alt pte_icon_button' title='Edit Vault Item' onclick='alpn_vault_control(\"edit\")'></i>
				<i id='alpn_vault_delete' class='far fa-trash-alt pte_icon_button' title='Delete Vault Item' onclick='alpn_vault_control(\"delete\")'></i>
				<div style='display: inline-block; width: 10px;'></div>
			  </div>
		  	  <div style='clear: both;'></div>
		  </div>
		  <div class='alpn_container_title_2' data-topic-id='{$topicId}'>
			<div class='alpn_container_2_left'><i class='far fa-lock-alt' style='width: 30px; margin-bottom: 5px; font-size: 1.2em; color: rgb(68, 68, 68);'></i>&nbsp;&nbsp;{$record->name}</div>
			<div class='alpn_container_2_right'><div style='display: inline-block; vertical-align: middle;'>{$contextAll}</div><div style='display: inline-block;  vertical-align: middle; height: 35px;'>{$topicImage}</div></div>
		  </div>
		  <div style='clear: both;'></div>
		  <div id='alpn_vault_work_area' class='alpn_vault_work_area'>
		  	<div id='alpn_vault_work_inner' class='alpn_vault_work_inner'></div>
		  </div>
		  ";
$html .= "<div id='alpn_vault_main_container' class='wp-block-columns'>";
$html .= "<div id='alpn_outer_vault' class='wp-block-column'>";
$html .= do_shortcode("[wpdatatable id=5 var1='{$topicId}' var2='{$permissionLevel}']");
$html .= "</div>";

$html .= "<div id='alpn_add_edit_outer_container' class='alpn_add_edit_outer_container'></div>";

$html .= "<div id='alpn_vault_preview_embedded' class='wp-block-column'>";
$html .= "{$pdfViewer}";
$html .= "</div>";

$html .= "</div>";


$html = str_replace('table_1', 'table_vault', $html);
$html = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $html);
$html = str_replace('"iDisplayLength":5,', '"iDisplayLength":6,', $html);


echo $html;

?>
