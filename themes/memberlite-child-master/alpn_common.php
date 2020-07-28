<?php

date_default_timezone_set('UTC');

include('pte_config.php');
require 'vendor/autoload.php';

use Google\Cloud\Storage\StorageClient;
use Twilio\Rest\Client;


function getRootPath()
{
    return str_replace("\\","/",realpath(dirname(dirname(__FILE__))));
}

function getRootUrl()
{
    $document_root = $_SERVER["DOCUMENT_ROOT"];
    return $document_root;
}

function pte_manage_interaction($payload) {

  //TODO MAKE SURE ALL OF THIS IS securable with nonces

    alpn_log('Starting Async Job...');
    $sitePath = getRootPath() . "/memberlite-child-master/pte_interactions.php";
    pte_async_job ($sitePath, array("data" => $payload));
}

function pte_async_job ($url, $params) {
	$fullUrl = "php -f '{$url}' "  . escapeshellarg(serialize($params)) . " > /dev/null &";
	shell_exec($fullUrl);
}

function pte_async_job_old ($url, $params) {

    foreach ($params as $key => &$val) {
      if (is_array($val)) $val = implode(',', $val);
        $post_params[] = $key.'='.urlencode($val);
    }
    $post_string = implode('&', $post_params);

    $parts=parse_url($url);

    $fp = fsockopen($parts['host'],
        isset($parts['port'])?$parts['port']:80,
        $errno, $errstr, 30);

    $out = "POST ".$parts['path']." HTTP/1.1\r\n";
    $out.= "Host: ".$parts['host']."\r\n";
    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
    $out.= "Content-Length: ".strlen($post_string)."\r\n";
    $out.= "Connection: Close\r\n\r\n";
    if (isset($post_string)) $out.= $post_string;

    fwrite($fp, $out);
    fclose($fp);
}


function pte_call_documo($type, $query){
  $urlbase = 'https://api.documo.com/v1/';
  $apiKey = FAX_DOCUMO_API_KEY;
  switch ($type) {
    case "search":
      $endPoint = 'numbers/provision/search';
    break;
  }
  $query = http_build_query($query);
  $headers = array(
    "Authorization: Basic {{$apiKey}}",
    "Accept: application/json"
  );
  $ch = curl_init();
  $url = $urlbase . $endPoint;
  $options = array(
    CURLOPT_URL => "{$url}?{$query}",
    CURLOPT_HTTPHEADER => $headers
  );
  curl_setopt_array($ch, $options);
  $response = curl_exec($ch);
  curl_close($ch);
  return $response;
}

function pte_search_for_fax_number(){

  $query = array(
    'npa' => '408',
    'limit' => 10

    );

  $response = pte_call_documo('search', $query);

  pp($response);
}

function pte_get_interaction_settings_sliders($data) {

  global $wpdb_readonly;
  $sliders = "";
  $userInfo = wp_get_current_user();
  $ownerId = $userInfo->data->ID;
  $ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true );

  $data = array("ProTeam Invitation", "Fax Send", "Fax Received", "Message", "Added to Network", "File Share", "File Received", "Copy Request", "Reminder", "Chat Activity", "Form Fill Request");

  sort($data);

  $sliders .= "<div id='pte_sliders_container'><div id='pte_sliders_container_inner'>";
  foreach ($data as $key) {
      $rando = rand (1,3);

      $sliders .= "<div class='pte_sliders_line'><div class='pte_sliders_type'>{$key}</div><div class='pte_interaction_slider'><input type='range' min='1' max='3' step='1' value='{$rando}' onchange='pte_handler_interaction_setting_slider(this);'></div><div style='clear: both;'></div></div>";
  }
  $sliders .= "</div></div>";
  return $sliders;
}

function pte_update_interaction_weight($listKey, $data) {
    global $wpdb;
    $interaction = $whereClause = array();
    $operation = isset($data['operation']) ? $data['operation'] : "important_added";
    $networkValueVip = IMP_NETWORK_VIP;
    $networkValueGeneral = IMP_NETWORK_GENERAL;
    $topicValueVit = IMP_TOPIC_VIT;
    $topicValueGeneral = IMP_TOPIC_GENERAL;
    $whereClause['owner_network_id'] = $data['owner_network_id'];
    if ($listKey == 'pte_important_network') {
      $networkValue = ($operation == 'important_added') ? $networkValueVip : $networkValueGeneral;
      $whereClause['imp_network_id'] = $data['item_id'];
      $interaction['imp_network_value'] = $networkValue;
    } else {  //TOPIC
      $topicValue = ($operation == 'important_added') ? $topicValueVit : $topicValueGeneral;
      $whereClause['imp_topic_id'] = $data['item_id'];
      $interaction['imp_topic_value'] = $topicValue;
    }
    $wpdb->update( 'alpn_interactions', $interaction, $whereClause );
}

function pte_get_important_items($listKey){
  global $wpdb_readonly;
  $listItems = "";
  $userInfo = wp_get_current_user();
  $ownerId = $userInfo->data->ID;
  $ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true );
  if ($ownerNetworkId && $listKey) {
    $results = $wpdb_readonly->get_results(
      $wpdb_readonly->prepare(
        "SELECT u.item_id, t.name FROM alpn_user_lists u LEFT JOIN alpn_topics t ON t.id = u.item_id WHERE u.owner_network_id = %s AND list_key = %s ORDER BY t.name ASC", $ownerNetworkId, $listKey)
    );
    if (count($results)) {
      foreach ($results as $key => $value) {
        $selectedId = $value->item_id;
        $selectedName = $value->name;
        $listItems .= "<li class='pte_important_topic_scrolling_list_item' data-topic-id='{$selectedId}'><div class='pte_scrolling_item_left'>{$selectedName}</div><div class='pte_scrolling_item_right'><i class='far fa-minus-circle pte_scrolling_list_remove' title='Remove Item' onclick='pte_handle_remove_list_item(this);'></i></div><div style='clear: both;'></div></li>";
      }
    }
  }
  return $listItems;
}

function pte_get_topic_list($listType) {
  global $wpdb_readonly;
  $topicOptions = "";
  $userInfo = wp_get_current_user();
  $userID = $userInfo->data->ID;
  $userNetworkId = get_user_meta( $userID, 'pte_user_network_id', true );
  if ($userID && $listType) {
    switch ($listType) {
      case "network_contacts":
      $results = $wpdb_readonly->get_results(
        $wpdb_readonly->prepare(
          "SELECT id, name FROM alpn_topics WHERE owner_id = '%s' AND topic_type_id = '4' ORDER BY name ASC;", $userID)
      );
      $id = 'pte_important_network_topic_list';
      break;
      case "topics":
        $results = $wpdb_readonly->get_results(
          $wpdb_readonly->prepare(
            "SELECT id, name FROM alpn_topics WHERE owner_id = '%s' AND topic_type_id NOT IN ('4', '5') ORDER BY name ASC;", $userID)
        );
        $id = 'pte_important_topic_list';
        break;
    }
    if (isset($results[0])) {
      $topicOptions .= "<select id='{$id}'>";
      $topicOptions .= "<option></option>";
      foreach ($results as $key => $value) {
          $topicOptions .= "<option value='{$value->id}'>{$value->name}</option>";
      }
      $topicOptions .= "</select>";
    }
  }
  return $topicOptions;
}

function pte_manage_user_sync($data){   ///Must be a user and have a wpid


  alpn_log("pte_manage_user_sync...");
  //alpn_log($data);

  global $wpdb;

  $accountSid = ACCOUNT_SID;
  $authToken = AUTHTOKEN;
  $syncServiceId = SYNCSERVICEID;

  try {
    $twilio = new Client($accountSid, $authToken);
  } catch (Exception $e) {
      $response = array(
          'message' =>  $e->getMessage(),
          'code' => $e->getCode(),
          'error' => $e
      );
      alpn_log("pte_manage_user_sync EXCEPTION...");
      alpn_log($response);
      return;
  }

  $syncType = isset($data['sync_type']) ? $data['sync_type'] : false ;
  $syncSection = isset($data['sync_section']) ? $data['sync_section'] : '';
  $syncUserId = isset($data['sync_user_id']) ? $data['sync_user_id'] : 0;

  if (!$syncUserId) { //TODO trying to sync but not a user -- do something?
    return;
  }

  $syncId = get_user_meta( $syncUserId, 'pte_user_sync_id', true );

  switch ($syncType) {
    case "create_sync_id":
    alpn_log("create_sync_id...");
    if (!$syncId) {
            try {
              $sync_map = $twilio->sync->v1->services($syncServiceId)
                                           ->syncMaps
                                           ->create();
              $syncId = $sync_map->sid;
              $topicData = array(
                "sync_id" => $syncId
              );
          		$whereClause['owner_id'] = $syncUser;
              $whereClause['topic_type_id'] = 5;

              $wpdb->update( 'alpn_topics', $topicData, $whereClause );  //persist channelid

              update_user_meta( $syncUser, "pte_user_sync_id",  $syncId); //SH

            } catch (Exception $e) {
                $syncId = "Fail";
                $response = array(
                    'message' =>  $e->getMessage(),
                    'code' => $e->getCode(),
                    'error' => $e
                );
                alpn_log($response);
            }
          }
          return $syncId;
		break;

    case "add_update_section":

    alpn_log("add_update_section...");

    try {

      alpn_log("Trying to edit...");

      $sync_map_item = $twilio->sync->v1->services($syncServiceId)
                                      ->syncMaps($syncId)
                                      ->syncMapItems($syncSection)
                                      ->update(array("data" => $data));
    } catch (Exception $e) {

      alpn_log("Adding, not exist...");

        $response = array(
            'message' =>  $e->getMessage(),
            'code' => $e->getCode(),
            'error' => $e
        );
        try {

          alpn_log("Trying to add...");

          $sync_map_item = $twilio->sync->v1->services($syncServiceId)
                                            ->syncMaps($syncId)
                                            ->syncMapItems
                                            ->create($syncSection, $data);
        } catch (Exception $e) {
          $response = array(
              'message' =>  $e->getMessage(),
              'code' => $e->getCode(),
              'error' => $e
          );

        }


    }

      alpn_log("Updated Item...");

    break;

  }





}

function pte_manage_user_connection($data){

  //If I add you to my network and you add me to your network than we're connected... We then show connected demographics....
  //TODO Handle exceptions, etc.

  global $wpdb;

  $contactEmail = $data['contact_email'];
  $contactTopicId = $data['contact_topic_id'];
  $contactInfo = get_user_by('email', $contactEmail);

  if (isset($contactInfo->ID)) {
    $contactId = $contactInfo->ID;
    $contactNetworkId = get_user_meta( $contactId, 'pte_user_network_id', true ); //Owners Topic ID

    $userId = isset($data['owner_wp_id']) ? $data['owner_wp_id'] : $userInfo->data->ID;
    $userInfo = get_user_by('id', $userId);
    $userEmail =  $userInfo->data->user_email;
    $userNetworkId = get_user_meta( $userId, 'pte_user_network_id', true ); //Owners Topic ID

    $results = $wpdb->get_results(
    	$wpdb->prepare("SELECT id, owner_id, connected_id FROM alpn_topics WHERE owner_id = %s AND topic_type_id = 4 AND alt_id = %s", $contactId, $userEmail)
     );
     if (isset($results[0])) {
       $contactId = $results[0]->owner_id;
       $connectedId = $results[0]->connected_id;
       if (!$connectedId) {

          $user = $wpdb->get_results(
          	$wpdb->prepare("SELECT id FROM alpn_topics WHERE owner_id = %s AND topic_type_id = 4 AND alt_id = %s", $userId, $contactEmail)
           );

           if (isset($user[0])) {
             $userTopicId = $user[0]->id;
             $data = array(
         			'topic_id' => $userTopicId
           		);
         		$newChannelId = pte_manage_cc_groups("get_create_channel", $data);     //create a channel for contact. Adds contact. Stores channel for contact

             //user
             $topicData = array(
               'connected_id' => $contactId,
               'connected_network_id' => $contactNetworkId
             );
             $whereClause = array(
               'id' => $userTopicId
             );
             $wpdb->update( 'alpn_topics', $topicData, $whereClause );

             $data = array(  //add contact to channel
              'topic_id' => $userTopicId,
              'user_id' => $contactId
            );
            pte_manage_cc_groups("add_member", $data);

             //contact
             $topicData = array(
               'connected_id' => $userId,
               'connected_network_id' => $userNetworkId,
               'channel_id' => $newChannelId
             );
             $whereClause = array(
               'id' => $results[0]->id
             );
             $wpdb->update( 'alpn_topics', $topicData, $whereClause );
         }
      }
    } else {  //TODO if contact in system, send IA offering to connect them with user... $contactId




    }
  }
}

function pte_manage_cc_groups($operation, $data) {

  global $wpdb;

  $accountSid = ACCOUNT_SID;
  $authToken = AUTHTOKEN;
  $chatServiceId = CHATSERVICESID;

  try {
    $twilio = new Client($accountSid, $authToken);
  } catch (Exception $e) {
      $response = array(
          'message' =>  $e->getMessage(),
          'code' => $e->getCode(),
          'error' => $e
      );
      alpn_log($response);
      return;
  }

  $topicId = isset($data['topic_id']) ? $data['topic_id'] : "";
  $userId = isset($data['user_id']) ? $data['user_id'] : "";
  $topicName = isset($data['topic_name']) ? $data['topic_name'] : "";

  $imageHandle = isset($data['image_handle']) ? $data['image_handle'] : "pte_icon_letter_" . strtolower(substr($topicName, 1));

  $ownerInfo = wp_get_current_user();
  $ownerId = $ownerInfo->data->ID;

	switch ($operation) {

//TODO Exceptions - rooms there that didn't get deleted causing issues,

		case "get_create_channel":

    $channelId = "";
    if ($topicId && $ownerId) {
      $results = $wpdb->get_results(
      	$wpdb->prepare("SELECT channel_id FROM alpn_topics WHERE id = %s", $topicId)
       );
      if (isset($results[0])) {
        $channelId = $results[0]->channel_id;
        if (!$channelId) {
          try {
            $channel = $twilio->chat->v2->services($chatServiceId)
              ->channels
              ->create(array(
                'type' => 'private',
                'friendlyName' => $topicId
              ));
            $channelId = $channel->sid;
            $member = $twilio->chat->v2  //Add owner to new channel
              ->services($chatServiceId)
              ->channels($channelId)
              ->members
              ->create($ownerId);
              $topicData = array(
                "channel_id" => $channelId
              );
          		$whereClause['id'] = $topicId;
          		$wpdb->update( 'alpn_topics', $topicData, $whereClause );  //persist channelid
              alpn_log("Created New Channel..." . $channelId);
          } catch (Exception $e) {
              $response = array(
                  'message' =>  $e->getMessage(),
                  'code' => $e->getCode(),
                  'error' => $e
              );
              alpn_log($response);
          }

        } else {
          //TODO Handle Error -- did not create a channel
        }
      } else {
        //TODO HANDLE ERROR -- did not find Topic
      }
    } else {
      //TODO HANDLE ERROR -- No TopicID Found
    }

    if ($channelId) { //Make sure it exists
      try {
        $channel = $twilio->chat->v2->services($chatServiceId)
          ->channels($channelId)
          ->fetch();
      } catch (Exception $e) {
          $response = array(
              'message' =>  $e->getMessage(),
              'code' => $e->getCode(),
              'error' => $e
          );
          alpn_log($response);
          if ($topicId) {
            $topicData = array(
              "channel_id" => ""
            );
            $whereClause['id'] = $topicId;
            $wpdb->update( 'alpn_topics', $topicData, $whereClause );  //clear channelid
        }
      }
    }
    return $channelId;
		break;

    case "add_member":

      $channelId = pte_manage_cc_groups("get_create_channel", $data);

      if ($channelId) {
        try {
          $member = $twilio->chat->v2  //Add user to channel
            ->services($chatServiceId)
            ->channels($channelId)
            ->members
            ->create($userId);

          alpn_log("Added Member..." . $userId);
        } catch (Exception $e) {
            $response = array(
                'message' =>  $e->getMessage(),
                'code' => $e->getCode(),
                'error' => $e
            );
            alpn_log($response);
        }
    } else {   //TODO Handle error


    }

		break;

    case "delete_member":
    $channelId = pte_manage_cc_groups("get_create_channel", $data);
    if ($channelId) {
      try {
        $members = $twilio->chat->v2
          ->services($chatServiceId)
          ->channels($channelId)
          ->members
          ->read([], 100);

          $memberCount = count($members);
           foreach ($members as $record) {
             if ($record->identity == $userId) {

               $user = $twilio->chat->v2
                ->services($chatServiceId)
                ->channels($channelId)
                ->members($record->sid)
                ->delete();

                alpn_log("Deleted member... " . $record->sid);
                $memberCount--;
                break;
              }
            }
            if ($memberCount <= 1) {   //owner left. Delete channel. Free up since user can only be concurrently assigned to 1000 channels.
              pte_manage_cc_groups("delete_channel", $data);
              return true;
            }
      } catch (Exception $e) {
          $response = array(
              'message' =>  $e->getMessage(),
              'code' => $e->getCode(),
              'error' => $e
          );
          alpn_log($response);
      }
    } else {  //TODO Handle Error


    }
    return false;
    break;


		case "delete_channel":

    alpn_log("About to delete channel...");
    $channelId = pte_manage_cc_groups("get_create_channel", $data);

    if ($channelId && $topicId) {

      $topicData = array(
        "channel_id" => ""
      );
      $whereClause['id'] = $topicId;
      $wpdb->update( 'alpn_topics', $topicData, $whereClause );  //clear channelid

      try {
        $channel = $twilio->chat->v2
          ->services($chatServiceId)
          ->channels($channelId)
          ->delete();
          return true;
      } catch (Exception $e) {
          $response = array(
              'message' =>  $e->getMessage(),
              'code' => $e->getCode(),
              'error' => $e
          );
          alpn_log($response);
      }
    } else { //TODO handle error.


    }
    return false;
		break;

		case "add_user":

      $imageHandle = "pte_icon_letter_" . strtolower(substr($topicName, 1));
      $attributes = json_encode(array(
        "image_handle" => $imageHandle
      ));

      try {
        $user = $twilio->chat->v2->services($chatServiceId)
          ->users
          ->create(array(
            "identity" => $ownerId,
            "friendlyName" => $topicName,
            "attributes" => json_encode($attributes)
          ));
          alpn_log("Created user... " . $ownerId);

      } catch (Exception $e) {
          $response = array(
              'message' =>  $e->getMessage(),
              'code' => $e->getCode(),
              'error' => $e
          );
          alpn_log($response);
      }

		break;

    case "update_user":
    $user = $twilio->chat->v2
      ->services($chatServiceId)
      ->users($ownerId)
      ->update(array(
        "friendlyName" => $topicName
      ));
      alpn_log("Updated user... " . $ownerId);
		break;

    case "update_user_image":
      $attributes = array(
        "image_handle" => $imageHandle
      );
      $user = $twilio->chat->v2
        ->services($chatServiceId)
        ->users($ownerId)
        ->update(array(
          "attributes" => json_encode($attributes)
        ));
        alpn_log("Updated user image... " . $ownerId);
		break;

    case "delete_user":
    $user = $twilio->chat->v2
      ->services($chatServiceId)
      ->users($ownerId)
      ->delete();
      alpn_log("Delete user... " . $ownerId);
		break;
	}

}

function pte_record_event(){


}

function pp($objtopp) {
	echo "<pre>"; print_r($objtopp); echo "</pre>";
}

function alpn_log($logstr){
	error_log(print_r($logstr, true) . PHP_EOL, 3, get_theme_file_path() . '/logs/alpn_error.log');
}

function pte_make_string($theItems, $theFields, $theMap, $gmtOffset){

	$gmtOffset = 0; //TODO neeg to figure this out

	//Make local Dates.

	$theString = '';
	foreach ($theItems as $itemKey => $itemValue) {
		$key = $itemValue['type'];
		$value = $itemValue['value'];
		switch ($key){
			case "modified_date_pretty":
				$theString = strtotime("now") + $gmtOffset;
				$theString = date("F j, Y, g:iA", $theString);
			break;
			case "modified_date":
				$theString = strtotime("now") + $gmtOffset;
				$theString = substr("00000000" . $theString, -14);
			break;
			case "make_date":
				$date = $theFields[$theMap[$itemValue['date_field']]];
				$time = $theFields[$theMap[$itemValue['time_field']]];
				$theString = strtotime($date['date'] . " " . $time['time']) + $gmtOffset;
				$theString = substr("00000000" . $theString, -14);
			break;
			case "field":
				if (array_key_exists ($value, $theMap)){
					$theField = $theFields[$theMap[$value]];
					if (is_array($theField)) {
						if (isset($theField['date'])) {
							$pDate = strtotime($theField['date']) + $gmtOffset;
							$theString .= date('F j, Y', $pDate);
						} else if (isset($theField['time'])){
							$pTime = strtotime($theField['time']) + $gmtOffset;
							$theString .= date('g:iA', $pTime);
						}
					} else {
						$theString .= $theFields[$theMap[$value]];
					}
				}
			break;
			case "string":
				$theString .= $value;
			break;
			case "field_if_empty":
				if (array_key_exists ($value, $theMap)){
					$theField = $theFields[$theMap[$value]];
					if ($theField != "") {
						return $theField;
					}
				}
			break;
		}
	}
	return $theString;
}


function pte_time_elapsed($secs){
    $bit = array(
        'y' => $secs / 31556926 % 12,
        'w' => $secs / 604800 % 52,
        'd' => $secs / 86400 % 7,
        'h' => $secs / 3600 % 24,
        'm' => $secs / 60 % 60,
        's' => $secs % 60,
		'ms' => $secs
        );

    foreach($bit as $k => $v)
        if($v > 0)$ret[] = $v . $k;

    return join(' ', $ret);
}


function pte_json_out($theObject) {
	header('Content-Type: application/json');
	echo json_encode($theObject);
	return;
}

function get_custom_post_items($post_type, $order){
	$args = array(
		'post_type'=> $post_type,
		'order'    => $order,
		'orderby' => 'title',
		'posts_per_page' => 100
	);

    $loop = new WP_Query( $args );
	if (isset($loop->posts)) {
		$items = $loop->posts;
		foreach ($items as $key => $value) {
			$id = $value->ID;
			$title = $value->post_title;
			$postItems[$id] = $title;
		}
	return ($postItems);
	}
	return ('error');
}

function  pte_make_rights_panel_view($panelData) {

	$topicStates = array('10' => "Added", '20' => "Notified", '30' => "Active", '40' => "Topic Linked");

	$proTeamRowId = $panelData['proTeamRowId'];
	$topicNetworkId = $panelData['topicNetworkId'];
	$topicNetworkName = $panelData['topicNetworkName'];
	$topicAccessLevel = $panelData['topicAccessLevel'];
	$topicState = $panelData['state'];
	$checked = $panelData['checked'];

  if ($topicAccessLevel == '10') {
    $generalChecked = "SELECTED";
    $restrictedChecked = "";
  } else if ($topicAccessLevel == '30') {
    $generalChecked = "";
    $restrictedChecked = "SELECTED";
  }

  $permissions = "
    <select id='alpn_select2_small_{$proTeamRowId}' class='alpn_select2_small' data-ptrid='{$proTeamRowId}'>
      <option value='10' {$generalChecked}>General</option>
      <option value='30' {$restrictedChecked}>Restricted</option>
    </select>
  ";


	//TODO Loop array.
	$download = (isset($checked['download']) && $checked['download']) ? "<div id='proteam_download' data-item='download' pte-state='set' data-ptid='{$proTeamRowId}' class='proteam_rights_check' onclick='alpn_rights_check(this);'><div class='pte_panel_check'><i class='fa fa-check' style='font-size: 0.9em; color: #4499d7;'></i></div>Download</div>" : "<div id='proteam_download' data-item='download' pte-state='' data-ptid='{$proTeamRowId}' class='proteam_rights_check' onclick='alpn_rights_check(this);'><div class='pte_panel_check'></div>Download</div>";

  $share = (isset($checked['share']) && $checked['share']) ? "<div id='proteam_share' data-item='share' pte-state='set' data-ptid='{$proTeamRowId}' class='proteam_rights_check' onclick='alpn_rights_check(this);'><div class='pte_panel_check'><i class='fa fa-check' style='font-size: 0.9em; color: #4499d7;'></i></div>Share</div>" : "<div id='proteam_share' data-item='share' pte-state='' data-ptid='{$proTeamRowId}' class='proteam_rights_check' onclick='alpn_rights_check(this);'><div class='pte_panel_check'></div>Share</div>";

  $transfer = (isset($checked['transfer']) && $checked['transfer']) ? "<div id='proteam_transfer' data-item='transfer' pte-state='set' data-ptid='{$proTeamRowId}' class='proteam_rights_check' onclick='alpn_rights_check(this);'><div class='pte_panel_check'><i class='fa fa-check' style='font-size: 0.9em; color: #4499d7;'></i></div>Transfer Data</div>" : "<div id='proteam_transfer' data-item='transfer' pte-state='' data-ptid='{$proTeamRowId}' class='proteam_rights_check' onclick='alpn_rights_check(this);'><div class='pte_panel_check'></div>Transfer Data</div>";


	$html = "
		<div id='pte_proteam_item_{$proTeamRowId}' class='proteam_user_panel' data-name='{$topicNetworkName}' data-id='{$proTeamRowId}'>
			<div class='proTeamPanelUserOuter'>
				<div id='proTeamPanelUser' class='proTeamPanelUser'>{$topicNetworkName}</div>
				<div id='proTeamPanelUserData' class='proTeamPanelUserData'>{$topicStates[$topicState]}</div>
        <div style='font-weight: normal; color: rgb(0, 116, 187); cursor: pointer; font-size: 11px; line-height: 16px;' onclick='alpn_proteam_member_delete({$proTeamRowId});'>Remove</div>
			</div>
			<div class='proTeamPanelSettings'>
				<div id='pte_proteam_controls' class='pte_proteam_controls' data-id='{$topicNetworkId}'>
					<table class='pte_proteam_rights_table' data-pte-proteam-id='{$proTeamRowId}'>
						<tr class='pte_proteam_row'>
							<td class='pte_proteam_cell_left'>
                <div style='display: inline-block; vertical-align: middle; margin-left: 0px; margin-right: 5px; margin-bottom: 3px; font-weight: bold;'>Access:</div><div style='display: inline-block; vertical-align: middle; margin-bottom: 3px;'>{$permissions}</div>
                <div class='pte_proteam_row_rights'>
                  <div class='pte_proteam_cell_rights'>$download</div><div class='pte_proteam_cell_rights'>$share</div>
                </div>
                <div class='pte_proteam_row_rights'>
                  <div class='pte_proteam_cell_rights'>$transfer</div><div class='pte_proteam_cell_rights'></div>
                </div>
              </td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		";

	return $html;

}



?>
