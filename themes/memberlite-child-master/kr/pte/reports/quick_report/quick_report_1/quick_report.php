<?php

use \koolreport\KoolReport;
use \koolreport\export\Exportable;

class quick_report extends \koolreport\KoolReport
{
	use \koolreport\export\Exportable;
	use \koolreport\cache\FileCache;

	function cacheSettings()
	{
			return array(
					"ttl"=>60
			);
	}

	 function settings() {
		$hostDomainName = PTE_HOST_DOMAIN_NAME;
		return [
           "assets" =>
						 	array(
	                "path"=>"../../../../assets",
	                "url"=>"https://{$hostDomainName}/wp-content/themes/memberlite-child-master/kr/assets"
	            ),
						"dataSources" =>
							array()
		];
	}

   function setup()
    {
			//Prepare required Topic Data and register as standard KoolReport array data sources. //TODO go directly to DB with server backend paging?
			global $wpdb;
			$imageBaseUrl = PTE_IMAGES_ROOT_URL;
			alpn_log('SETTING UP QR');
			$userInfo = wp_get_current_user();
			$userID = $userInfo->data->ID;
			$userMeta = get_user_meta( $userID, 'pte_user_network_id', true );
			$reportSettings = $this->params;
			$topicTabs = $reportSettings['topic_tabs'];
			$sendData = $reportSettings['send_data'];
			$reportSections = $sendData['sections'];
			$topicId = $sendData['topic_id'];
			$topicContent = $reportSettings['topic_content'];
			$topicMeta = $reportSettings['topic_meta'];
			$timeZoneDelta = $sendData['timezone_delta'];  //Passed from the client for calculating local dates. Can't use the embedded Javascript trick.
			$placeTopicId =  $sendData['place_source'];
			$organizationTopicId =  $sendData['organization_source'];

			//User, Org, Place, Topic
			$userInfo = $wpdb->get_results(
				$wpdb->prepare("SELECT id, topic_content, logo_handle, image_handle, created_date, modified_date FROM alpn_topics WHERE id = %d OR id = %d OR id = %d OR id = %d", $userMeta, $organizationTopicId, $placeTopicId, $topicId)
		 );

		 if (isset($userInfo[0])) {
			 foreach ($userInfo as $key => $value) {
				 if ($value->id == $topicId) {
					 $logoHandle = $value->logo_handle;
					 if ($logoHandle) {
						 $topicLogoUrl = "<img class='pte_logo_image_print'  src='{$imageBaseUrl}{$logoHandle}'>";
					 }
					 $topicContent['pte_image_logo'] = $topicLogoUrl;
					 $createdDate = strtotime($value->created_date) - ($timeZoneDelta * 60);
					 $$createdDateFormatted = date(PTE_DATE_FORMAT_STRING_PHP, $createdDate);
					 $topicContent['pte_added_date'] = $createdDateFormatted;
					 $modifiedDate = strtotime($value->modified_date) - ($timeZoneDelta * 60);
					 $modifiedDateFormatted = date(PTE_DATE_FORMAT_STRING_PHP, $modifiedDate);
					 $topicContent['pte_modified_date'] = $modifiedDateFormatted;
					 $businessTypesList = get_custom_post_items('pte_profession', 'ASC');
					 if (isset($topicContent['person_hasoccupation_occupation_occupationalcategory'])) {  //TODO test this
						 $topicContent['person_hasoccupation_occupation_occupationalcategory'] = $businessTypesList[$topicContent['person_hasoccupation_occupation_occupationalcategory']];
					 } else {
						 $topicContent['person_hasoccupation_occupation_occupationalcategory'] = "Not Specified";
					 }
					}
				 if ($value->id == $userMeta) {
					 $this->params['user_content'] = json_decode($value->topic_content, true);
					 $this->params['user_content']['logo_url']  = $imageBaseUrl . $value->logo_handle;
					 $this->params['user_content']['image_url']  = $imageBaseUrl . $value->image_handle;
					}
				 if ($value->id == $placeTopicId) {
					 $this->params['place_content'] = json_decode($value->topic_content, true);
					 $this->params['place_content']['logo_url']  = $imageBaseUrl . $value->logo_handle;
					 $this->params['place_content']['image_url']  = $imageBaseUrl . $value->image_handle;
				 }
				 if ($value->id == $organizationTopicId) {
					 $this->params['organization_content']= json_decode($value->topic_content, true);
					 $this->params['organization_content']['logo_url']  = $imageBaseUrl . $value->logo_handle;
					 $this->params['organization_content']['image_url']  = $imageBaseUrl . $value->image_handle;
				 }
			 }
		 }
			//Info
			$subjectToken = 'pte_main_topic_0';
			$dataSource = array(
			 "class"=>'\koolreport\datasources\ArrayDataSource',
			 "dataFormat"=>"associate",
			 "data" => makeDataSource($topicMeta, $topicContent)
			);
			$this->reportSettings['dataSources'][$subjectToken] = $dataSource;
			$this->src($subjectToken)->pipe($this->dataStore($subjectToken));

			$topicTypesUsed = array();
			foreach ($topicTabs as $key => $value) {  //Tabs

				$topicKey = $value['key'];
				$subjectToken = $value['subject_token'];
				$ownerTopicId = $value['owner_topic_id'];  //Same for all. Todo confirm
				$topicFilter = isset($reportSections[$topicKey]['section_filter']) ? $reportSections[$topicKey]['section_filter'] : 'all';
				if ($topicFilter != 'exclude' && $subjectToken) {
					$topicTypesUsed[] = $subjectToken;
				}
			}
			 $topicsUsedList = "(" .  implode(',', array_map('pte_add_quotes', $topicTypesUsed)) . ")"; //so we can pull all remaining used topic types. //TODO extend to next level

				$results = $wpdb->get_results(
				$wpdb->prepare("select owner_topic_id, name, about, owner_id, dom_id, draw_id, owner_name, type_key, subject_token, connected_topic_id, connected_id, connected_topic_type_id, connected_topic_content, connected_topic_type_meta, connected_topic_type_html, connected_logo_handle, connected_created_date, connected_modified_date, link_id, connected_topic_special, topic_class FROM alpn_topics_linked_view WHERE owner_topic_id = %d AND subject_token in {$topicsUsedList} AND owner_id = %d ORDER BY subject_token ASC, name ASC", $ownerTopicId, $userID)
			 );

			 if (isset($results[0])) {
					 $currentTypeKey = '';
					 $subjectCounter = 0;
					 $oldSubjectToken = 'pte_place_holder';
					 $dataSources = array();
					 $column = 0;
					 $dataRows = array();

				 	 foreach ($results as $key => $value) {

							$connectedTopicContent = json_decode($value->connected_topic_content, true);
							$businessTypesList = get_custom_post_items('pte_profession', 'ASC');
							if (isset($connectedTopicContent['person_hasoccupation_occupation_occupationalcategory'])) {  //TODO test this
								$connectedTopicContent['person_hasoccupation_occupation_occupationalcategory'] = $businessTypesList[$connectedTopicContent['person_hasoccupation_occupation_occupationalcategory']];
							} else {
								$connectedTopicContent['person_hasoccupation_occupation_occupationalcategory'] = "Not Specified";
							}
							$createdDate = date(PTE_DATE_FORMAT_STRING_PHP, strtotime ($value->connected_created_date));
							$connectedTopicContent['pte_added_date'] = $createdDate;
							$modifiedDate = date(PTE_DATE_FORMAT_STRING_PHP, strtotime ($value->connected_modified_date));
							$connectedTopicContent['pte_modified_date'] = $modifiedDate;
							$topicLogoUrl = '';
							$topicLogoHandle = $value->connected_logo_handle;
							if ($topicLogoHandle) {
								$topicLogoUrl = "<img id='pte_logo_image_print' src='{$imageBaseUrl}{$topicLogoHandle}'>";
							}
							$connectedTopicContent['pte_image_logo'] = $topicLogoUrl;
							$connectedTopicMeta = json_decode($value->connected_topic_type_meta, true);

							$typeKey = $value->type_key;
							$subjectToken = $value->subject_token;
							$topicName = $connectedTopicMeta['topic_name'];

							if ($subjectToken) {
								if ($oldSubjectToken == $subjectToken) {
									$subjectCounter++;
								} else {
									$subjectCounter = 0;
								}

								//TODO Optimize by only creating data sources for needed ones. Creating too many for filtered links

								$dataSource = array(
			 					 "class"=>'\koolreport\datasources\ArrayDataSource',
			 					 "dataFormat"=>"associate",
			 					 "data" => makeDataSource($connectedTopicMeta, $connectedTopicContent)
			 				 	);
								$newSubjectToken = "{$subjectToken}_{$subjectCounter}";
								$this->reportSettings['dataSources'][$newSubjectToken] = $dataSource;
								$this->src($newSubjectToken)->pipe($this->dataStore($newSubjectToken));
								$oldSubjectToken = $subjectToken;
							}
					}
			 }


			 //TODO loop to a certain amount of depth. Then create datasources for each table required. Then draw the right table.
		}
}
