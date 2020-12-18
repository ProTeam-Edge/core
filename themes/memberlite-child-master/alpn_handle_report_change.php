<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');
require ('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/kr/pte/reports/quick_report/quick_report_1/handle_quick_report.php');
require ('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/vendor/autoload.php');

use Google\Cloud\Storage\StorageClient;

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;
$userMeta = get_user_meta( $userID, 'pte_user_network_id', true );

$qVars = $_POST;
$sendData = isset($qVars['report_meta']) ? json_decode(stripslashes($qVars['report_meta']), true) : array();

if ($sendData) {

	alpn_log('Start pte_handle_report_change...');
	alpn_log($sendData);

	$topicContent = array();
	$domId = $sendData['topic_dom_id'] ? $sendData['topic_dom_id'] : $sendData['network_dom_id'];

	$results = $wpdb->get_results(
		$wpdb->prepare("SELECT t.*, p.access_level, f.pstn_number, tt.id AS topic_type_id, tt.special, tt.type_key, tt.form_id, tt.name AS topic_name, tt.icon, tt.topic_type_meta, tt.html_template, t2.topic_content AS connected_topic_content, t3.name AS owner_name, t3.topic_content AS owner_topic_content, t2.image_handle AS profile_handle FROM alpn_topics t LEFT JOIN alpn_proteams p ON p.topic_id = t.id AND p.owner_id = t.owner_id LEFT JOIN alpn_pstn_numbers f ON f.topic_id = t.id LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id LEFT JOIN alpn_topics t2 ON t2.owner_id = t.connected_id AND t2.special = 'user' LEFT JOIN alpn_topics t3 ON t3.owner_id = t.owner_id AND t3.special = 'user' WHERE t.dom_id = %s", $domId)
	 );

	if (isset($results[0])){

		$topicData = $results[0];
		$topicTypeId = $topicData->topic_type_id;
		$topicSpecial = $topicData->special;
		$topicTypeName = $topicData->topic_name;
		$topicIcon = $topicData->icon;
		$topicId = $topicData->id;
		$topicOwnerId = $topicData->owner_id;
		$topicImageHandle = $topicData->image_handle;
		$topicLogoHandle = $topicData->logo_handle;
		$topicProfileHandle = $topicData->profile_handle;
		$topicName = $topicData->name;
		$topicChannelId = $topicData->channel_id;
		$topicDomId = $topicData->dom_id;
		$topicMeta = json_decode($topicData->topic_type_meta, true);
		$topicContent = json_decode($topicData->topic_content, true);
		$topicHtml = stripcslashes($topicData->html_template);
		$typeKey = $topicData->type_key;
		$nameMap = pte_name_extract($topicMeta['field_map']);
		$fieldMap = array_flip($nameMap);

		$topicEmailRoute = $topicData->email_route_id;
		$topicFaxRoute = $topicData->pstn_number;
		$subjectToken = '';
		$fullMap = $topicMeta['field_map'];
		$topicTabs = array();

		$topicBelongsToUser = ($userID == $topicOwnerId) ? true : false;

		$linkId = 0;
		$topicTabs[] = array(   //Info Page. All Topics Have Them
			'type' => 'page',
			'key' => $typeKey,
			'id' => $linkId,
			'name' => $topicTypeName,
			'html' => $topicHtml,
			'subject_token' => 'pte_main_topic',
			'owner_topic_id' => $topicId,
			'topic_title' => ''
		);

		$topicLinkKeys = array();
		foreach ($fullMap as $key => $value) {

			$fieldType = isset($value['type']) ? $value['type'] : "";
			$hidden = isset($value['hidden']) && ($value['hidden'] == "true") ? true : false;

			if (substr($fieldType, 0, 5) == "core_" && !$hidden) {
					$fieldTypeArray = explode("_", $fieldType);
					if (count($fieldTypeArray) == 2) {  //Handle Core Type Mapping
						$mainCoreTopic = true;
					} else {  //Handle User Topic Type
						$mainCoreTopic = false;
					}

					$topicLinkKeys[] = $fieldType;
					$linkId++;
					$topicTabs[] = array(
						'main_core_topic' => $mainCoreTopic,
						'type' => 'linked',
						'id' => $linkId,
						'name' => $value['friendly'] ? $value['friendly'] : "Not Specified",
						'key' => $fieldType,  //object type
						'subject_token' => $key,   //field_unique id
						'owner_topic_id' => $topicId
					);
				}
			}
			$topicBelongsToUser = ($userID == $topicOwnerId) ? true : false;

			$proteam = $wpdb->get_results(  //get proteam  //TODO integrate into single initial query
				$wpdb->prepare("SELECT p.*, t.name, t.image_handle, t.profile_handle, t.dom_id FROM alpn_proteams p LEFT JOIN alpn_topics_network_profile t ON p.proteam_member_id = t.id WHERE p.topic_id = '%s' ORDER BY name ASC", $topicId)
			 );
			$topicHasTeamMembers = count($proteam) ? true : false;

			if ($topicBelongsToUser) {
				//Team Links
				//Being user by. Linked to me.
				$linkId++;
				$topicTabs[] = array(
					'type' => 'linked',
					'id' => $linkId,
					'name' => "Linked by",
					'key' => 'pte_inbound',
					'subject_token' => 'pte_inbound',
					'owner_topic_id' => $topicId,
					'topic_title' => 'Links to this Topic'
				);

				if ($topicHasTeamMembers) {
					$linkId++;
					$topicTabs[] = array(
						'type' => 'linked',
						'id' => $linkId,
						'name' => "Team",
						'key' => 'pte_external',
						'subject_token' => 'pte_external',
						'owner_topic_id' => $topicId,
						'topic_title' => 'Links to Team Member Topics'
					);
				}
			}
	 $imageBaseUrl = PTE_IMAGES_ROOT_URL;
	 $topicContent['logo_url'] = $imageBaseUrl . $topicData->logo_handle;
	} else {
		$topicContent['logo_url'] = '';
	}

	try {
		$reportSettings = array(
			'dom_id' => "{$domId}",
			'orientation' => 'portrait',
			'page_size' => 'letter',
			'topic_tabs' => $topicTabs,
			'topic_content' => $topicContent,
			'topic_meta' => $topicMeta,
			'send_data' => $sendData
		);

		alpn_log($reportSettings);

		$pdfReportPath = pteCreateTopicQuickReport ($reportSettings);
		pte_json_out(array("pdf_key" => $pdfReportPath));

	} catch (\Exception $e) {
			alpn_log($e);
			exit;
	}

//	unlink($localStoreName);
}



?>
