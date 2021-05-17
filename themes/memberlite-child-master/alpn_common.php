<?php
date_default_timezone_set('UTC');

include_once('pte_config.php');
require 'vendor/autoload.php';

use Google\Cloud\Storage\StorageClient;
use Twilio\Rest\Client;
use PascalDeVink\ShortUuid\ShortUuid;

function pte_user_rights_check($resourceType, $data){
  //alpn_log('RIGHTS CHECK');
  global $wpdb;
  $userInfo = wp_get_current_user();
  $userId = $userInfo->data->ID;
  $userNetworkId = get_user_meta( $userId, 'pte_user_network_id', true );
  switch ($resourceType) {

    case 'vault_item_edit':   //Mine or Shared Contact Topic.
      $vaultId = $data['vault_id'];
      $results = $wpdb->get_results($wpdb->prepare(
        "SELECT v.id FROM alpn_vault v WHERE v.owner_id = %d AND v.id = %d
         UNION SELECT v.id FROM alpn_vault v INNER JOIN alpn_topics t ON t.id = v.topic_id AND t.connected_id = %d WHERE v.id = %d", $userId, $vaultId, $userId, $vaultId));
       if (isset($results[0])) {
         return true;
       }
    break;

    case 'vault_item':
    case 'vault_item_view':
     $vaultId = $data['vault_id'];    //Mine, My Contacts, My Topics
     $results = $wpdb->get_results($wpdb->prepare(
       "SELECT v.id FROM alpn_vault v WHERE v.owner_id = %d AND v.id = %d
         UNION
        SELECT v.id FROM alpn_vault v INNER JOIN alpn_topics t ON t.id = v.topic_id AND t.connected_id = %d WHERE v.id = %d
         UNION
        SELECT v.id FROM alpn_vault v INNER JOIN alpn_proteams p ON p.topic_id = v.topic_id AND p.wp_id = %d AND v.access_level <= p.access_level WHERE v.id = %d
       ", $userId, $vaultId, $userId, $vaultId, $userId, $vaultId));

      if (isset($results[0])) {
        return true;
      }
   break;

   case 'topic_dom_view':
    $topicDomId = $data['topic_dom_id'];
    $results = $wpdb->get_results($wpdb->prepare(
      "SELECT t.id FROM alpn_topics t WHERE t.owner_id = %d AND t.dom_id = %s UNION SELECT t.id FROM alpn_proteams p LEFT JOIN alpn_topics t ON t.id = p.topic_id WHERE p.wp_id = %d AND t.dom_id = %s", $userId, $topicDomId, $userId, $topicDomId));
     if (isset($results[0])) {
       return true;
     }
   break;
   case 'topic_dom_edit':  //TODO Future allow editing as part of comprehensive multuser capability.
    $topicDomId = $data['topic_dom_id'];
    $results = $wpdb->get_results($wpdb->prepare(
      "SELECT t.id FROM alpn_topics t WHERE t.owner_id = %d AND t.dom_id = %s", $userId, $topicDomId));
     if (isset($results[0])) {
       return true;
     }
   break;

   case 'action':
    alpn_log('ACTION');


   break;

 }

  return false;
}

function pte_encode_value ($s) {
    return htmlentities($s, ENT_COMPAT|ENT_QUOTES,'ISO-8859-1', true);
}

function pte_digits($sourceString){
  return preg_replace('/\D/', '', $sourceString);
}

//TODO centralize this with report usages on ZIP

function pte_add_to_proteam($data) {

  global $wpdb;

  $accessLevel = isset($data['access_level']) && $data['access_level'] ? $data['access_level'] : "10";
  $state = isset($data['state']) && $data['state'] ? $data['state'] : "10";

  $defaultMemberRights = array(
		'download' => '0',
		'share' => '0',
		'delete' => '0',
		'fax'  => '0',
		'email' => '0',
		'new' => '1',
		'edit' => '1',
		'chat' => '1',
		'action' => '1',
		'print' => '1',
		'transfer' => '1',
		);
  $memberRights = isset($data['member_rights']) && $data['member_rights'] ? json_decode($data['member_rights'], true) : $defaultMemberRights;

  $connectedType = isset($data['connected_type']) && $data['connected_type'] ? $data['connected_type'] : "external";
  $processId = isset($data['process_id']) && $data['process_id'] ? $data['process_id'] : "";

	$proTeamData = array( //TODO start IA and store processID
		'owner_id' => $data['owner_id'],
		'topic_id' => $data['topic_id'],  //topicContext
		'proteam_member_id' => $data['proteam_member_id'],
		'wp_id' => $data['wp_id'],
		'access_level' => $accessLevel,
		'state' => $state,
    'connected_type' => $connectedType,
    'process_id' => $processId,
		'member_rights' => json_encode($memberRights)
	);
	$wpdb->insert( 'alpn_proteams', $proTeamData );

  return $wpdb->insert_id;
}

function delete_from_cloud_storage($fileKey){
  alpn_log("Deleting Vault Item in Cloud Storage.");
	try {
		$storage = new StorageClient([
	    	'keyFilePath' => '/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/proteam-edge-cf8495258f58.json'
		]);
    $bucket = $storage->bucket('pte_file_store1');
      $object = $bucket->object($fileKey);
      $object->delete();
    return true;
	} catch (\Exception $e) { // Global namespace
    alpn_log('Failed to Delete from Cloud Storage');
    alpn_log($e);
    return false;
	}
}


function storePdf($pdfSettings){
  alpn_log('STORING PDF');
  $pdfKey = $pdfSettings["pdf_key"];
	$localFile = $pdfSettings["local_file"];
  $doNotUnlinkLocal = $pdfSettings["do_not_unlink_local"];
	try {
		$storage = new StorageClient([
	    	'keyFilePath' => '/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/proteam-edge-cf8495258f58.json'
		]);
		$storage->registerStreamWrapper();
		$fileContent = file_get_contents($localFile);
		$options = ['gs' => ['Content-Type' => "application/pdf"]];
		$context = stream_context_create($options);
		$response = file_put_contents("gs://pte_file_store1/{$pdfKey}", $fileContent, 0, $context);
		if (!$doNotUnlinkLocal) {unlink ($localFile);}
    $fileInfo = array(
      'status' => 'ok',
      'pdf_size' => $response,
      'pdf_key' => $pdfKey
    );
    return $fileInfo;

	} catch (\Exception $e) { // Global namespace
			$pte_response = array("topic" => "pte_get_cloud_file_google_exception", "message" => "Problem accessing Google Cloud Storage.", "data" => $e);
			alpn_log($pte_response);
			exit;
	}
}

function pte_date_to_js($sourceDateTime, $prefix=''){
  $shortId = pte_get_short_id();
  return "<div id='{$shortId}'><script>pte_date_to_js('{$sourceDateTime}', '{$shortId}', '{$prefix}');</script></div>";
}

function pte_map_extract($theMap){
  $extractedMap = array();
  foreach ($theMap as $key => $value) {    //TODO find the right function
    $extractedMap[$key] = isset($value['id']) ? $value['id'] : "";
 }
  return ($extractedMap);
}

function pte_add_quotes($str) {
    return sprintf("'%s'", $str);
}

function pte_get_available_topic_fields($formId, $editorMode) {

  global $wpdb;

  $userInfo = wp_get_current_user();
  $userID = $userInfo->data->ID;

  $topicTypeMap = array();
  $tokens = array();
  alpn_log("pte_get_available_topic_fields");
  //Get the desired topic
  $results = $wpdb->get_results($wpdb->prepare("SELECT id, name, type_key, schema_key, topic_type_meta, special FROM alpn_topic_types WHERE form_id = %d",	$formId));
  if (isset($results[0])) {
  	$ttData = $results[0];
  	$ttMeta = isset($ttData->topic_type_meta) ? json_decode($ttData->topic_type_meta, true) : array();
  	$fieldmap = isset($ttMeta['field_map']) ? $ttMeta['field_map'] : array();
    $schemaKey = $ttData->schema_key;
    $topicTypeId = $ttData->id;
    $ttIdEncoded = base_convert($topicTypeId, 10, 36);
    $ttName = $ttData->name ? $ttData->name : $schemaKey;
    $ttSpecial = $ttData->special;
    foreach ($fieldmap as $key => $value) {
      if (isset($value['type']) && substr($value['type'], 0, 5) == "core_") {
        if ($editorMode != "message") {  //messages can't travel links. TODO: Maybe one day but will require adding filtering to Interactions. Probably interesting.
          $topicTypeMap[$value['type']] = $value['type'];
        }
  		} else {
        $hiddenPrint = isset($value['hidden_print']) && $value['hidden_print'] == 'true' ? true : false;
        if ($key && !$hiddenPrint && $value['id'] != "0"){
          $fieldId = $value['id'];
          $fieldFriendlyName = isset($value['friendly']) && $value['friendly'] ? $value['friendly'] : "NA";
          $friendlyKey = "{$ttName} | {$fieldFriendlyName}";
          $tokens[] = array(
            "text" => $friendlyKey,
            "topic_type_id" => $ttIdEncoded,
            "field_name" => $key
          );
        }
      }
    }

  $recipientTypeKey = '';
  if ($editorMode == "message") { //add recipient (based on core_person) But only on messages since this will come from the interaction.
      $recipientTypeKey = "core_person";
      $topicTypeMap[$recipientTypeKey] = '';
  }
  //Get linked topic fields, if any
  if (count($topicTypeMap)) {
    $topicListString = "('" . implode("','", array_keys($topicTypeMap)) . "')";
    $results = $wpdb->get_results("SELECT id, name, type_key, schema_key, topic_type_meta FROM alpn_topic_types WHERE type_key IN {$topicListString}");
    foreach ($results as $key => $value) {
    	$ttMeta = isset($value->topic_type_meta) ? json_decode($value->topic_type_meta, true) : array();
    	$fieldmap = isset($ttMeta['field_map']) ? $ttMeta['field_map'] : array();
      $schemaKey = $value->schema_key;
      $topicTypeId = $value->id;
      $ttIdEncoded = base_convert($topicTypeId, 10, 36);
      $ttName = $recipientTypeKey ? "Recipient" : $value->name;
      foreach ($fieldmap as $key1 => $value1) {
        $hiddenPrint = isset($value1['hidden_print']) && $value1['hidden_print'] == 'true' ? true : false;
        if (!$hiddenPrint && $key1 && $value1['id'] != "0" && (isset($value1['type']) && substr($value1['type'], 0, 5) != "core_")) {
          $fieldId = $value1['id'];
          $fieldFriendlyName = $value1['friendly'];
          $friendlyKey = "{$ttName} | {$fieldFriendlyName}";
          $tokens[] = array(
            "text" => $friendlyKey,
            "topic_type_id" => $ttIdEncoded,
            "field_name" => $key1
          );
        }
      }
    }
  }
  }
  sort($tokens);
  return json_encode($tokens, true);
}

function pte_name_extract($theMap){
  $extractedNames = array();
  foreach ($theMap as $key => $value) {    //TODO find the right function
    $extractedNames[$key] = isset($value['friendly']) ? $value['friendly'] : $key;
 }
  return ($extractedNames);
}


function pte_file_interaction_away($processId) {

  alpn_log('pte_file_interaction_away');

  global $wpdb;
  $uxMeta = array();

  $results = $wpdb->get_results(
  	$wpdb->prepare("SELECT ux_meta FROM alpn_interactions WHERE process_id = %s", $processId)
   );

   if (isset($results[0])) {
  	 $interactionDetails = $results[0];
  	 $uxMeta = json_decode($interactionDetails->ux_meta, true);
  	 $fileInteractionOperation = isset($uxMeta['interaction_file_away_handling']) ? $uxMeta['interaction_file_away_handling'] : false;

  	 switch ($fileInteractionOperation) {

  		case 'delete_interaction':
  			alpn_log('delete_interaction');

  			$whereClause['process_id'] = $processId;
  			$wpdb->delete( 'alpn_interactions', $whereClause );
   		break;
  		case 'archive_interaction':
  			alpn_log('archive_interaction');

        $interactionData = array(
          "state" => "filed"
        );
        $whereClause['process_id'] = $processId;
        $wpdb->update( 'alpn_interactions', $interactionData, $whereClause );

  		break;

  		case 'decline_archive_interaction':
  			alpn_log('decline_archive_interaction');

  		break;
  	}

   }

   return $uxMeta;
}


function pte_remove_proteam_member($rowToDelete) {

  alpn_log('pte_remove_proteam_member');

  global $wpdb;
  $ptRow = array();

  $proTeamMemberResults = $wpdb->get_results(
  	$wpdb->prepare("SELECT id, topic_id, wp_id, process_id FROM alpn_proteams WHERE id = %d", $rowToDelete)
   );

  if (isset($proTeamMemberResults[0])) {

  	$ptRow = $proTeamMemberResults[0];
  	$wpId = $ptRow->wp_id;
  	$topicId = $ptRow->topic_id;

  	$deletedChannelToo = false;
  	if ($wpId) {
  		$data = array(
  			'topic_id' => $topicId,
  			'user_id' => $wpId
  		);
  		$deletedChannelToo = pte_manage_cc_groups("delete_member", $data);   //TODO handle async. Takes several seconds.
  	}
  	$deleteResults = $wpdb->delete('alpn_proteams', array('id' => $rowToDelete));
  }
  return (array)$ptRow;
}

function pte_filename_sanitizer($name) {
    // remove illegal file system characters https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
    $name = str_replace(array_merge(
        array_map('chr', range(0, 31)),
        array('<', '>', ':', '"', '/', '\\', '|', '?', '*')
    ), '', $name);
    // maximise filename length to 255 bytes http://serverfault.com/a/9548/44086
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $name= mb_strcut(pathinfo($name, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($name)) . ($ext ? '.' . $ext : '');
    return $name;
}

function pte_get_short_id() {
  $shortUuid = new ShortUuid();
  return $shortUuid->uuid4();
}

function getRootPath()
{
    return str_replace("\\","/",realpath(dirname(dirname(__FILE__))));
}

function getRootUrl()
{
    return PTE_ROOT_URL;
}

function pte_manage_interaction($payload) {
  //TODO MAKE SURE ALL OF THIS IS securable with nonces
    $sitePath = getRootUrl() . "pte_interactions.php";
    pte_async_job ($sitePath, array("data" => json_encode($payload)));
}

function pte_async_job_old ($url, $params) {
	$fullUrl = "php -f '{$url}' "  . escapeshellarg(serialize($params)) . " > /dev/null &";
	shell_exec($fullUrl);
}

function pte_sync_curl($endPoint, $postRequest) {
  $domainName = PTE_HOST_DOMAIN_NAME;
  $baseUrl = "https://{$domainName}/wp-content/themes/memberlite-child-master/topics/";
  $fullUrl = "{$baseUrl}{$endPoint}.php";
  $headers[] = "Accept: application/json";
  $options = array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_POST => true,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => array('payload' => $postRequest),
      CURLOPT_URL => $fullUrl,
      CURLOPT_HTTPHEADER => $headers
  );
   $ch = curl_init();
   curl_setopt_array($ch, $options);
   $response = curl_exec($ch);
   curl_close($ch);
   return $response;
}


function pte_send_wp_mail($data){

  $toEmail = $data['to_email'];
  $toName =  $data['to_name'];
  $friendlyToEmail = "{$toName} <{$toEmail}>";

  wp_mail( $friendlyToEmail, $data['subject_text'], "<div>HTML HERE!</div>" . $data['body_text'] );

}

function pte_send_mail ($data) {
  $siteDomain = PTE_HOST_DOMAIN_NAME;
  $email = new \SendGrid\Mail\Mail();
  $sendGridKey = SENDGRID_KEY;
  $emailTemplateName =  isset($data['email_template_name']) && $data['email_template_name'] ? PTE_ROOT_PATH . "email_templates/{$data['email_template_name']}" : PTE_ROOT_PATH . "email_templates/pte_email_template_1.html";
  $emailTemplateHtml = file_get_contents($emailTemplateName);
  $fromName =  $data['from_name'];
  $fromEmail =  $data['from_email'];
  $toEmail = $data['to_email'];
  $toName =  $data['to_name'];
  $linkType =  $data['link_type'];
  $fileName = isset($data['vault_file_name']) && $data['vault_file_name'] ? "File Name: " . $data['vault_file_name'] : "";
  $subject =  $data['subject_text'];

  $replaceStrings["-{pte_site_domain}-"] = $siteDomain;
  $replaceStrings["-{pte_email_body}-"] = $data['body_text'];
  $replaceStrings["-{pte_link_button}-"] = isset($data['link_id']) && $data['link_id'] ? "<div class='pte_button'><a class='pte_button_link' href='https://{$siteDomain}/viewer/?{$data['link_id']}'>View File</a></div>" : "" ;
  $replaceStrings["-{pte_email_file_details}-"] = $fileName;
  $replaceStrings["-{pte_email_signature}-"] = isset($data['email_signature']) && $data['email_signature'] ? $data['email_signature'] : "" ;
  //TODO Pick template based on link type or conditional.

  $emailTemplateHtml = str_replace(array_keys($replaceStrings), $replaceStrings, $emailTemplateHtml);

  $replyFrom = $fromName . " (using ProTeam Edge)";

  $email->setFrom("sender@proteamedge.com", $replyFrom);
  $email->setReplyTo($fromEmail, $fromName);

  $email->setSubject($subject);
  $email->addTo($toEmail, $toName);
  $email->addContent("text/html", $emailTemplateHtml );

  $sendgrid = new \SendGrid($sendGridKey);

  try {
      $response = $sendgrid->send($email);
  } catch (Exception $e) {
      alpn_log ('Caught exception: '. $e->getMessage());
  }
}


function pte_send_sms($data){

  alpn_log("pte_send twilio SMS...");
  global $wpdb;
  $domainName = PTE_HOST_DOMAIN_NAME;

  $fromName = isset($data['from_name']) ? $data['from_name'] : 'Error';
  $sendMobileNumber = isset($data['send_mobile_number']) ? "+1" . preg_replace('/\D/', '', $data['send_mobile_number'])  : '';
  $subject = isset($data['subject_text']) ? $data['subject_text'] : 'File Received';
  $body = isset($data['body_text']) ? $data['body_text'] : '';
  $link = isset($data['link_id']) ? "https://{$domainName}/viewer/?" . $data['link_id'] : '';
  $fileName = isset($data['vault_file_name']) ? $data['vault_file_name'] : '';

  $body = "Secure Link From {$fromName} (using proteamedge.com) - {$subject} - {$body} - $fileName - {$link}";
  $body = substr($body, 0, 1575);

  $accountSid = ACCOUNT_SID;
  $authToken = AUTHTOKEN;
  $messagingServiceId = MESSAGINGSERVICEID;

  try {
    $twilio = new Client($accountSid, $authToken);
    $message = $twilio->messages
        ->create($sendMobileNumber, // to
                 [
                     "body" => $body,
                     "messagingServiceSid" => $messagingServiceId
                 ]
        );
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
}


function pte_duplicate_topic_type($data){

  //alpn_log('pte_duplicate_topic_type');
  //alpn_log($data);
  global $wpdb;
  $relatedId = $data["related_id"];
  $topicTypeMap = $data["topic_type_map"];
  $topicTypeValue = $data["topic_type_value"];
  $newOwnerId = $data["new_owner_id"];
  $formId = $topicTypeValue['form_id'];
  $typeKey = $topicTypeValue['type_key'];
  $skipTopicLinks = isset($data['skip_topic_links']) ? $data['skip_topic_links'] : false;
  $uuid = $topicTypeValue['uuid'];
  $newTypeKey = "{$typeKey}_{$uuid}";
  $nameDetail = $relatedId ? " - {$relatedId}" : "";

  if ($formId && $newOwnerId) {
    $resultsPosts = $wpdb->get_results($wpdb->prepare("SELECT * FROM wp_posts WHERE ID = %d", $formId));
    if (isset($resultsPosts[0])) {
      //create new wpform based on source
       $postData = $resultsPosts[0];
       $now = date ("Y-m-d H:i:s", time());
       $nowGm = gmdate ("Y-m-d H:i:s", time());
       unset($postData->ID);
       $postData->post_date = $now;
       $postData->post_modified = $now;
       $postData->post_date_gmt = $nowGm;
       $postData->post_modified_gmt = $nowGm;
       $postData->post_author = 1;
       $postData->post_title = "User - {$newTypeKey}";
       $postData->post_name = "{$newTypeKey}";
       $wpdb->insert( 'wp_posts', (array) $postData );
       $newFormId = $wpdb->insert_id;
       $postContent = json_decode($postData->post_content, true);
       $postContent['id'] = $newFormId;
       $newContent['post_content'] = json_encode($postContent);
       $whereClause['ID'] = $newFormId;
       $wpdb->update( 'wp_posts', $newContent, $whereClause );
       //create newTopicType based on source
       unset($topicTypeValue['id']);
       $sourceTypeKey = $topicTypeValue['type_key'];
       $topicTypeValue['type_key'] = $topicTypeMap[$topicTypeValue['type_key']];
       $topicTypeValue['form_id'] = $newFormId;
       $topicTypeMeta = json_decode($topicTypeValue['topic_type_meta'], true);
       $fieldMap = $topicTypeMeta['field_map'];
       foreach ($fieldMap as $key1 => $value1 ) {    //Maps all core fields to their new topics.
         $typeKey = isset($value1['type']) ? $value1['type'] : "";
         $pos = strpos($typeKey, "_", strpos($typeKey, "_") + 1);
         if ($pos) {
           $typeKey = substr($typeKey, 0, $pos);
         }
         if (substr($typeKey, 0, 5) == 'core_') {
           if ($skipTopicLinks) {
             unset($topicTypeMeta['field_map'][$key1]);
          } else {
            $topicTypeMeta['field_map'][$key1]['type'] = $topicTypeMap[$typeKey];
          }
         }
       }
       $topicTypeValue['topic_type_meta'] = json_encode($topicTypeMeta);
       $topicTypeValue['owner_id'] = $newOwnerId;
       $topicTypeValue['topic_state'] = "user";
       $topicTypeValue['source_type_key'] = $sourceTypeKey;
       $topicTypeValue['name'] = $topicTypeValue['name'] . $nameDetail;
       $wpdb->insert( 'alpn_topic_types', $topicTypeValue);
       $newTopicTypeId = $wpdb->insert_id;
       return $newTopicTypeId;
     }
 }
 return false;
}

function pte_topic_type_deep_copy($sourceTopicTypeId, $newOwnerId) {

  global $wpdb;
  $topicTypeMap = array();

  alpn_log("pte_topic_type_deep_copy");
  //Get the desired topic
  $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM alpn_topic_types WHERE id = %d",	$sourceTopicTypeId));
  if (isset($results[0])) {

  	$ttData = $results[0];
  	$ttMeta = isset($ttData->topic_type_meta) ? json_decode($ttData->topic_type_meta, true) : array();
  	$fieldmap = isset($ttMeta['field_map']) ? $ttMeta['field_map'] : array();

    //Determine what topic types are used by selected topic type and create mappings to new ones for this user. Deep Copy. TODO we will want to allow users to map to their existing topics
    foreach ($fieldmap as $key => $value) {   //prepare unique new ids for forthcoming tts Must include all required mappings for dupe.
      if (substr($value['type'], 0, 5) == "core_") {
        $newUuid = pte_get_short_id();
        $typeKey = $value['type'];
        $newTypeKey = "{$typeKey}_{$newUuid}";
        $topicTypeMap[$typeKey] = $newTypeKey;
  		}
    }
  }

  //go get all TTs that make up the deep copy.
  $topicListString = "('" . implode("','", array_keys($topicTypeMap)) . "')";
  $results = $wpdb->get_results("SELECT * FROM alpn_topic_types WHERE type_key IN {$topicListString}");

  $relatedId = substr(str_shuffle("0123456789"), 0, 3);

  foreach ($results as $key => $value) {
    $data = array(
      "related_id" => $relatedId,
      "new_owner_id" => $newOwnerId,
      "topic_type_map" => $topicTypeMap,
      "topic_type_value" => (array) $value
    );
    //Making Copies
    $newTopicTypeId = pte_duplicate_topic_type($data);
  }

  $currentTypeKey = $ttData->type_key;
  $newUuid = pte_get_short_id();
  $newTypeKey = "{$currentTypeKey}_{$newUuid}";
  $topicTypeMap[$currentTypeKey] = $newTypeKey;

  $data = array(
    "related_id" => $relatedId,
    "new_owner_id" => $newOwnerId,
    "topic_type_map" => $topicTypeMap,
    "topic_type_value" => (array) $ttData
  );
  $newTopicTypeId = pte_duplicate_topic_type($data);

  return $newTopicTypeId;
}

function pte_topic_type_copy ($sourceTopicTypeId, $newOwnerId) {

  global $wpdb;
  $topicTypeMap = array();

  alpn_log("pte_topic_type_copy...");
  //Get the desired topic
  $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM alpn_topic_types WHERE id = %d",	$sourceTopicTypeId));
  if (isset($results[0])) {
      $ttData = $results[0];
      $ttMeta = isset($ttData->topic_type_meta) ? json_decode($ttData->topic_type_meta, true) : array();
      $fieldmap = isset($ttMeta['field_map']) ? $ttMeta['field_map'] : array();

      $relatedId = substr(str_shuffle("0123456789"), 0, 3);
      $currentTypeKey = $ttData->type_key;
      $currentTypeKeyArray = explode("_", $currentTypeKey);
      if (count($currentTypeKeyArray) == 3) {
        $currentTypeKey = $currentTypeKeyArray[0] . "_" . $currentTypeKeyArray[1];
      }

      $newUuid = pte_get_short_id();
      $newTypeKey = "{$currentTypeKey}_{$newUuid}";
      $topicTypeMap[$currentTypeKey] = $newTypeKey;

      $data = array(
      "related_id" => $relatedId,
      "new_owner_id" => $newOwnerId,
      "topic_type_map" => $topicTypeMap,
      "topic_type_value" => (array) $ttData,
      "skip_topic_links" => true
      );
      $newTopicTypeId = pte_duplicate_topic_type($data);

      return $newTopicTypeId;
    }
    return false;
  }

  function pte_create_topic($formId, $ownerId, $data, $iconImage = '', $logoImage = '', $emailRoute = '') {
    alpn_log('pte_create_topic');
    $entry = array(
      'id' => $formId,  //source user template type  Using custom TT
      'owner_id' => $ownerId,
      'fields' => $data,
      'icon_image' => $iconImage,
      'logo_image' => $logoImage,
      'create_email_route' => $emailRoute
    );
    return alpn_handle_topic_add_edit ('', $entry, '', '' );	//Add user
  }


function pte_create_default_topics($newOwnerId, $createSampleData = false) {

  global $wpdb;
  $shortUuid = new ShortUuid();
  $topicState = "active";
  $coreUserFormId = "";
  $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM alpn_topic_types WHERE topic_state = %s", $topicState));
  $topicTypeMap = array();

  foreach ($results as $key => $value) {   //prepare unique new ids for forthcoming tts Must include all required mappings for dupe.
    $newUuid = pte_get_short_id();
    $typeKey = $value->type_key;
    $newTypeKey = "{$typeKey}_{$newUuid}";
    $topicTypeMap[$typeKey] = $newTypeKey;
    $results[$key]->uuid = $newUuid;
  }
  foreach ($results as $key => $value) {
    $data = array(
      "related_id" => "",
      "new_owner_id" => $newOwnerId,
      "topic_type_map" => $topicTypeMap,
      "topic_type_value" => (array) $value
    );
    $duplicateResult = pte_duplicate_topic_type($data);
    }
    $topicTypeListString = "('" . implode("','", array_values($topicTypeMap)) . "')";
    $formRows = $wpdb->get_results("SELECT form_id, type_key FROM alpn_topic_types WHERE type_key IN {$topicTypeListString}");
    if (isset($formRows[0])) {
      foreach ($formRows as $key => $value) {
        if ($value->type_key == $topicTypeMap['core_user']) {
          $coreUserFormId = $value->form_id;
        }
        if ($createSampleData && $value->type_key == $topicTypeMap['core_contact']) {
          $iconImage = '6d906171ee3c42c492d350f52a0056d1.jpg';
          $sampleContact1 = array(
            0 => "{}",
            4 => "Miranda",
            2 => "Chang",
            6 => "Fiduciary (sample)",
            5 => "38",
            1 => "adipiscing@eratVivamusnisi.org",
            10 => "https://linkedin.com/arbella32",
            8 => "(873) 800-0488",
            3 => "(408) 357-2824",
            7 => "#aginglife",
            9 => "Expert in the field."
          );
          $sampleContact1Id = pte_create_topic($value->form_id, $newOwnerId, $sampleContact1, $iconImage);
          $iconImage = 'b98c23c4d0c2447f83183d394ac8e211.jpg';
          $sampleContact2 = array(
            0 => "{}",
            4 => "Rudyard",
            2 => "Lambert",
            6 => "Geriatrician (sample)",
            5 => "40",
            1 => "laoreet@Suspendisse.org",
            10 => "https://linkedin.com/lambertr01",
            8 => "(778) 275-6832",
            3 => "(408) 357-2824",
            7 => "#concierge #medical",
            9 => "Primary contact of Suspendisse medical team."
          );
          $sampleContact2Id = pte_create_topic($value->form_id, $newOwnerId, $sampleContact2, $iconImage);
        }
        if ($createSampleData && $value->type_key == $topicTypeMap['core_person']) {
          $iconImage = '03d5606550d740de82b9120a16c38ac1.jpg';
          $samplePerson1 = array(
            0 => "{}",
            4 => "Harriet",
            2 => "Kalinski",
            6 => "Customer (sample)",
            5 => "41",
            1 => "hkalinski12@xfinity.com",
            10 => "",
            8 => "(227) 555-6832",
            3 => "",
            7 => "#caregiving #meds",
            9 => "Sweet lady requires regular help with meds. Has 24/7 care."
          );
          $samplePerson1EmailId = $shortUuid->uuid4();
          $samplePerson1Id = pte_create_topic($value->form_id, $newOwnerId, $samplePerson1, $iconImage, "", $samplePerson1EmailId);
        }
        if ($createSampleData && $value->type_key == $topicTypeMap['core_organization']) {
          $iconImage = '91d3a2bf8d404858a4799b9f78850ba8.png';
          $logoImage = 'c7791a4c74dc43e9897035d2b1e53536.png';
          $sampleOrganization1 = array(
            0 => "{}",
            7 => "Acme Corporation",
            5 => "(619) 555-1233",
            3 => "(408) 357-2824",
            2 => "info@acmecorp.cc",
            8 => "https://acmecorp.cc",
            6 => "Maker of fine rockets and associated gear. (sample)"
          );
          $sampleOrganization1Id = pte_create_topic($value->form_id, $newOwnerId, $sampleOrganization1, $iconImage, $logoImage);
        }
        if ($createSampleData && $value->type_key == $topicTypeMap['core_general']) {
          $iconImage = '9deaa5a2e2b84bc590daa2c1a409d481.png';
          $sampleGeneral1 = array(
            0 => "{}",
            2 => "White Paper Research",
            1 => "A place to organize and discuss our findings and recommendations. (sample)"
          );
          pte_create_topic($value->form_id, $newOwnerId, $sampleGeneral1, $iconImage);
        }
        if ($createSampleData && $value->type_key == $topicTypeMap['core_place']) {
          $samplePlace1 = array(
            0 => "{}",
            8 => "Home",
            4 => "1029 Summer Breeze Street",
            1 => "San Diego",
            2 => "CA",
            3 => "96192",
            6 => "(619) 555-3957",
            5 => "(408) 357-2824",
            7 => "Main Residence (sample)",
            9 => ""
          );
          $samplePlace1Id = pte_create_topic($value->form_id, $newOwnerId, $samplePlace1);
          $samplePlace2 = array(
            0 => "{}",
            8 => "Office",
            4 => "222 Borderline Avenue, Suite B",
            1 => "San Diego",
            2 => "CA",
            3 => "96193",
            6 => "(619) 555-9385",
            5 => "(408) 357-2824",
            7 => "Headquarters (sample)",
            9 => ""
          );
          $samplePlace2Id = pte_create_topic($value->form_id, $newOwnerId, $samplePlace2);
        }
      }
    }
    return array(  //TODO rework this ugly thing
      'core_user_form_id' => $coreUserFormId,
      'sample_place_id_1' => $samplePlace1Id,
      'sample_place_id_2' => $samplePlace2Id,
      'sample_organization_id_1' => $sampleOrganization1Id,
      'sample_person_id_1' => $samplePerson1Id,
      'sample_person_email_id_1' => $samplePerson1EmailId
    );
}


function pte_manage_link($operation, $requestData){
  global $wpdb;
  switch ($operation) {
    case "create_link":
      $linkKey = pte_get_short_id();
      $linkType = isset($requestData['link_type']) ? $requestData['link_type'] : 'file';
      $ownerId = isset($requestData['owner_id']) ? $requestData['owner_id'] : 0;
      $vaultId = isset($requestData['vault_id']) ? $requestData['vault_id'] : 0;
      $about = isset($requestData['link_about']) ? $requestData['link_about'] : 'Manual';
      $now = date ("Y-m-d H:i:s", time());
      $rowData = array(
        'owner_id' => $ownerId,
        'uid' => $linkKey,
        'vault_id' => $vaultId,
        'link_type' => $linkType,
        'about' => $about,
        'link_meta' => json_encode($requestData),
        'created_date' => $now,
        'last_update' => $now
      );
      $wpdb->insert( 'alpn_links', $rowData );
      return $linkKey;
    break;
    case "expire_link":
      $linkId = isset($requestData['link_id']) ? $requestData['link_id'] : 0;
      $ownerId = isset($requestData['owner_id']) ? $requestData['owner_id'] : 0;
      $now = date ("Y-m-d H:i:s", time());
      $linkData = array(
        "expired" => 'true',
        'last_update' => $now
      );
      $whereClause = array(
        'owner_id' => $ownerId,
        'id' => $linkId
      );
      $wpdb->update( 'alpn_links', $linkData,  $whereClause);
    break;
  }

}

function pte_get_viewer_template() {

  $pdfViewer = "
  <template role='layout-template-container'>
  	<webpdf>
  		<toolbar name='toolbar'>
  			<div style='display: flex; flex-direction: row; padding: 0 0 0 0; border 0;'>
  				<group-list name='home-toolbar-group-list'>
  					<group name='home-tab-group-select' retain-count='7'>
  						<zoom-out-button icon-class='pte_viewer_zoomout_icon'></zoom-out-button>
  						<zoom-in-button icon-class='pte_viewer_zoomin_icon'></zoom-in-button>
  						<editable-zoom-dropdown></editable-zoom-dropdown>
  						<goto-prev-page-button icon-class='pte_viewer_prevpage_icon'></goto-prev-page-button>
  						<goto-next-page-button icon-class='pte_viewer_nextpage_icon'></goto-next-page-button>
  						<goto-page-input></goto-page-input>
  					</group>
  				</group-list>
  			</div>
  		</toolbar>
  		<div class='fv__ui-body'>
  			<sidebar name='pte_sidebar' @controller='sidebar:SidebarController'>
  				<search-sidebar-panel icon-class='pte_viewer_search_icon'></search-sidebar-panel>
  				<bookmark-sidebar-panel icon-class='pte_viewer_bookmark_icon'></bookmark-sidebar-panel>
  				<thumbnail-sidebar-panel icon-class='pte_viewer_thumbnail_icon'></thumbnail-sidebar-panel>
  			</sidebar>
  			<distance:ruler-container name='pdf-viewer-container-with-ruler'>
  				<slot>
  					<viewer @zoom-on-pinch @zoom-on-doubletap @zoom-on-wheel @touch-to-scroll></viewer>
  				</slot>
  			</distance:ruler-container>
  		</div>
  		<print:print-dialog></print:print-dialog>
  		<page-contextmenu></page-contextmenu>
  	</webpdf>
  </template>
  ";

  return $pdfViewer;
}

function pte_get_topic_manager($topicManagerSettings){
  //$sidebarState = isset($topicManagerSettings['sidebar_state']) ? $topicManagerSettings['sidebar_state'] : 'closed';
  $topicTable = do_shortcode("[wpdatatable id=9]");
  $topicTable = str_replace('table_1', 'table_topic_types', $topicTable);
  $topicTable = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $topicTable);

  $deleteButton =  "<i id='pte_delete_topic_type_button' class='far fa-trash-alt pte_topic_type_button pte_ipanel_button_disabled' title='Delete Topic' onclick='pte_delete_topic_link(\"\");'></i>";
  $duplicateButton =  "<i id='pte_dupe_topic_type_button' class='far fa-clone pte_topic_type_button pte_ipanel_button_disabled' title='Delete Topic' onclick='pte_delete_topic_link(\"\");'></i>";
  $extraTableControls =  json_encode("<div class='pte_topic_type_buttons'>{$deleteButton}{$duplicateButton}</div>");

  $addaTopicHtml = pte_get_topic_list('active_core_topic_types', '', 'pte_active_core_topic_types');

  //pte_topic_manager_inner is the container to switch between add/edit
  $html = "";
  $html .= "
    <div class='pte_vault_row pte_topic_manager_outer'>
      <div class='pte_vault_row_25'>
      <div class='pte_editor_title'>
        <div class='pte_vault_row_75'>
          Topic Types -- DNI/WIP
        </div>
        <div class='pte_vault_row_25 pte_vault_right'>
          &nbsp;
        </div>
      </div>
        <div class='pte_topic_type_add_container'>{$addaTopicHtml}</div>
        {$topicTable}
      </div>
      <div id='pte_topic_manager_container' class='pte_vault_row_75'>
        <div id='alpn_message_area' class='pte_template_editor_message_area'></div>
        <div id='pte_topic_manager_inner' class=''>
          &nbsp;
        </div>
      </div>
    </div>
  <script src='https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js'></script>
  <script src='https://cdn.jsdelivr.net/npm/jquery-sortablejs@latest/jquery-sortable.js'></script>
  <script>
    pte_topic_manager_loaded = true;

    jQuery('#pte_active_core_topic_types').select2( {
      theme: 'bootstrap',
      width: '100%',
      allowClear: true,
      closeOnSelect: false,
      placeholder: 'Add a Topic Type...'
    });
    jQuery('#pte_active_core_topic_types').on('select2:select', function (e) {
      var data = e.params.data;
      pte_add_a_new_topic_type(data);
    });
    jQuery('#pte_active_core_topic_types').on('select2:close', function (e) {
      jQuery('#pte_active_core_topic_types').val('').trigger('change');
    });
    alpn_wait_for_ready(10000, 250,  //Network Table
      function(){
        if (typeof wpDataTables != 'undefined' && typeof wpDataTables.table_topic_types != 'undefined') {
            return true;
        }
        return false;
      },
      function(){
        jQuery({$extraTableControls}).insertBefore('#table_topic_types_filter');
      },
      function(){ //Handle Error
        console.log('Error Adding to Table Toolbar...'); //TODO Handle Error
      });

  </script>
  ";
return $html;
}

function pte_create_topic_team_member($data) {    //add to proteam.

    global $wpdb;
    $ownerId = isset($data['owner_id']) ? $data['owner_id'] : '';
    $topicId = isset($data['topic_id']) ? $data['topic_id'] : '';
    $proTeamMemberId = isset($data['proteam_member_id']) ? $data['proteam_member_id'] : '';
    $wpId = isset($data['wp_id']) ? $data['wp_id'] : '';
    $processId = isset($data['process_id']) ? $data['process_id'] : '';

  	//TODO make this a user options
  	$defaultMemberRights = array(
  		'download' => '0',
  		'share' => '0',
  		'delete' => '0',
  		'fax'  => '0',
  		'email' => '0',
  		'new' => '1',
  		'edit' => '1',
  		'chat' => '1',
  		'action' => '1',
  		'print' => '1',
  		'transfer' => '1',
  		);
  	$defaultAccessLevel = "10";
  	$defaultState = "20";
  	$memberRights = json_encode($defaultMemberRights);

  	$proTeamData = array( //TODO start IA and store processID
  		'owner_id' => $ownerId,
  		'topic_id' => $topicId,
  		'proteam_member_id' => $proTeamMemberId,
      'process_id' => $processId,
		  'wp_id' => $wpId,
  		'access_level' => $defaultAccessLevel,
  		'state' => $defaultState,
  		'member_rights' => $memberRights
  	);
  	$wpdb->insert( 'alpn_proteams', $proTeamData );
    $proTeamData['id'] = $wpdb->insert_id;

    return $proTeamData;
}

function pte_is_on_topic_team($topicId, $emailContactTopicId){
  global $wpdb;
  $results = $wpdb->get_results(
    $wpdb->prepare("SELECT id from alpn_proteams WHERE topic_id = %d AND proteam_member_id = %d", $topicId, $emailContactTopicId)
   );
   if (isset($results[0])) {
     return $results[0];
   }
return false;
}

function pte_get_recipient($ownerNetworkId, $topicId){
  //alpn_log('Getting Recipient...');
  global $wpdb;
  $results = $wpdb->get_results(
    $wpdb->prepare("SELECT u.id AS network_important, t.id, t.special, t.owner_id, t.topic_type_id, t.connected_id, t.topic_content, t.dom_id AS email_contact_dom_id, t.connected_network_id, p.access_level, f.pstn_number, tt.id AS topic_type_id, tt.form_id, tt.name AS topic_name, tt.icon, tt.topic_type_meta, tt.html_template, t3.name AS owner_name, t3.topic_content AS owner_topic_content, t2.image_handle AS profile_handle, t2.topic_content AS connected_topic_content, t2.id AS connected_topic_id, t2.dom_id AS connected_topic_dom_id FROM alpn_topics t LEFT JOIN alpn_proteams p ON p.topic_id = t.id AND p.owner_id = t.owner_id LEFT JOIN alpn_pstn_numbers f ON f.topic_id = t.id LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id LEFT JOIN alpn_topics t2 ON t2.owner_id = t.connected_id AND t2.special = 'user' LEFT JOIN alpn_topics t3 ON t3.owner_id = t.owner_id AND t3.special = 'user' LEFT JOIN alpn_user_lists u ON u.item_id = t.id AND u.owner_network_id = %d AND u.list_key = 'pte_important_network' WHERE t.id = %s", $ownerNetworkId, $topicId)
   );
   if (isset($results[0])) {
     return $results[0];
   }
return false;
}

function pte_get_template_editor($editorSettings) {
  //$sidebarState = isset($topicManagerSettings['sidebar_state']) ? $topicManagerSettings['sidebar_state'] : 'closed';
  $topicTable = do_shortcode("[wpdatatable id=9]");
  $topicTable = str_replace('table_1', 'table_topic_types', $topicTable);
  $topicTable = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $topicTable);

  //pte_topic_manager_inner is the container to switch between add/edit
  $html = "";
  $html .= "
    <div class='pte_vault_row pte_topic_manager_outer'>
      <div class='pte_vault_row_25 pte_max_width_25'>
      <div class='pte_editor_title'>
        <div class='pte_vault_row_75'>
          <div>Templates</div>
        </div>
        <div class='pte_vault_row_25 pte_vault_right'>
          &nbsp;
        </div>
      </div>
        {$topicTable}
      </div>
      <div id='pte_topic_manager_container' class='pte_vault_row_75 pte_max_width_75'>
      <div id='alpn_message_area' class='pte_template_editor_message_area'></div>
      <div id='template_editor_container'>
        &nbsp;
      </div>
      </div>
    </div>
  <script>
      pte_template_editor_loaded = true;
  </script>
  ";
return $html;
}

function pte_get_viewer($viewerSettings){

  $sidebarState = isset($viewerSettings['sidebar_state']) ? $viewerSettings['sidebar_state'] : 'closed';
  $linkKey = isset($viewerSettings['link_key']) ? $viewerSettings['link_key'] : '';

  $data = $_GET;
  $html = "";
  global $wpdb_readonly;

  if (!$linkKey) { //get first variable passed in.
    foreach ($data as $key => $value) {
      $linkKey = $key;
      break;
    }
  }

  $linkKeyLength = strlen($linkKey);
  if ($linkKeyLength >= 20 && $linkKeyLength <= 22) {  //Valid Length.
    $results = $wpdb_readonly->get_results(
      $wpdb_readonly->prepare(
        "SELECT v.id, v.file_name, v.description, v.mime_type, v.modified_date, l.* FROM alpn_links l LEFT JOIN alpn_vault v ON v.id = l.vault_id WHERE l.uid = '%s';", $linkKey)   //Case sensitive
    );
    if (isset($results[0])) {
      $linkRow = $results[0];
      //TODO Rights Check.

      $linkLastUpdate = $linkRow->last_update;
      $linkMeta = json_decode($linkRow->link_meta, true);
      $linkInteractionExpiration = isset($linkMeta['link_interaction_expiration']) ? $linkMeta['link_interaction_expiration'] : 0;

      $now = new DateTime();
      $lastUpdateDate = new DateTime($linkLastUpdate);
      $lastUpdateDate->modify("+{$linkInteractionExpiration} minutes");
      $linkExpired = (($lastUpdateDate < $now) && ($linkInteractionExpiration > 0)) || ($linkRow->expired == 'true');

      if ($linkExpired) {
        return ("<div class='pmpro_content_message'><div class='pte_membership_message'>Access to this link has expired for security reasons. Please contact the original sender.</div></div>");
      }

      $linkInteractionPassword = isset($linkMeta['link_interaction_password']) ? $linkMeta['link_interaction_password'] : '';
      $linkInteractionOptions = isset($linkMeta['link_interaction_options']) ? $linkMeta['link_interaction_options'] : 0;
      $templateDirectory = get_template_directory_uri();
      $pdfViewer = pte_get_viewer_template();
      $vaultId = $linkRow->vault_id;
      $linkLastUpdate = $linkRow->last_update;
      $vaultFileName = stripslashes($linkRow->file_name);
      $vaultDescription = stripslashes($linkRow->description);
      $vaultMimeType = $linkRow->mime_type;
      $vaultModifiedDate = $linkRow->modified_date;

      $fileMeta = json_encode(array(
        "file_name" => $vaultFileName,
        "description" => $vaultDescription,
        "mime_type" => $vaultMimeType,
        "modified_data" => $vaultModifiedDate,
        "vault_id" => $vaultId,
        "link_token" => $linkKey
      ));

      $passwordHtml = $md5Password = $viewDocumentHtml = "";
      $downloadFiles = $printFiles = $copyFile = "pte_ipanel_button_disabled";
      $showPassword= 'none';

      if ($linkInteractionPassword) {
        $md5Password = md5($linkInteractionPassword);
        $showPassword = 'inline-block';
        $toolbar = "<div id='pte_viewer_toolbar' class='pte_viewer_toolbar'>
                      <div class='pte_vault_row_100'>
                          <div id='pte_viewer_password_container' class='pte_viewer_password_container' style='display: {$showPassword};'>File Passcode:&nbsp;&nbsp;<input type='text' id='pte_viewer_password_input' placeholder='Required...'><div class='pte_button_new' data-pte-pe='{$md5Password}' data-pte-vi='{$vaultId}' data-pte-io='{$linkInteractionOptions}' data-pte-token='{$linkKey}' onclick='pte_check_viewer_password(this);'>Open</div><span id='pte_check_viewer_password_error'></span></div>
            		      </div>
                    </div>
                  ";
      } else {
        $viewDocumentHtml = "pte_view_document({$vaultId}, '{$linkKey}');";
        if ($linkInteractionOptions == 1) {
          $printFiles = 'pte_ipanel_button_enabled';
        }
        if ($linkInteractionOptions == 2) {
          $printFiles = 'pte_ipanel_button_enabled';
          $downloadFiles = 'pte_ipanel_button_enabled';
          $copyFile = 'pte_ipanel_button_enabled';
        }
        $toolbar = "<div id='pte_viewer_toolbar' class='pte_viewer_toolbar'>
                      <div class='pte_vault_row_40'>
                        <i id='alpn_vault_print' class='far fa-print pte_icon_button {$printFiles}' title='Print File' onclick='alpn_vault_control(\"print\")'></i>
                        <i id='alpn_vault_download_original' class='far fa-file-download pte_icon_button {$downloadFiles}' title='Download Original File' onclick='alpn_vault_control(\"download_original\")'></i>
                        <i id='alpn_vault_download_pdf' class='far fa-file-pdf pte_icon_button {$downloadFiles}' title='Download PDF File' onclick='alpn_vault_control(\"download_pdf\")'></i>
            		      </div>
                      <div class='pte_vault_row_60 pte_vault_right'>
                      <div class='pte_viewer_info_outer'><div class='pte_viewer_info_inner_message'>File Name</div><div id='pte_viewer_info_filename' class='pte_viewer_info_inner_name'>{$vaultFileName}</div></div>
                      <div class='pte_viewer_info_outer' style='margin-left: 10px;'><div class='pte_viewer_info_inner_message'>Description</div><div id='pte_viewer_info_description' class='pte_viewer_info_inner_name'>{$vaultDescription}</div></div>
              		    </div>
                    </div>
                  ";
      }


      //TODO  Make Sense? <i id='alpn_vault_copy' class='far fa-file-export pte_icon_button  {$copyFile}' title='Copy File to Linked Topic' onclick='alpn_vault_control(\"copy_file\")'></i>

      //TODO Make Vault Access More seecure at back end and nonces.
      //TODO See what other kinds of controls are needed - for instance number retries. Captcha

      $viewerSettings = json_encode(array(
        "sidebar_state" => $sidebarState
      ));

      $html .= "
          <div id='alpn_vault_preview_embedded'>
              {$toolbar}
              <div id='pte_pdf_ui'></div>
              {$pdfViewer}
          </div>
 			 		<script>
 						alpn_templatedir = '{$templateDirectory}-child-master/';
 	          pte_setup_pdf_viewer({$viewerSettings});
            {$viewDocumentHtml}
            pte_viewer_file_meta = {$fileMeta};
 					</script>
      ";
    } else {

      pp("Error1");

    }

  } else {

    pp("Error2");

  }

return $html;
}


function pte_get_page_number($data) { //uses row_number from database and per_page to calculate proper page in table.. Queries need to match those in the tables.

  alpn_log("pte_get_page_number");
  //alpn_log($data);

  global $wpdb;
  $data = $data['data'];
  $type = isset($data['table_type']) ? $data['table_type'] : '';
  $ownerId = isset($data['owner_id']) ? $data['owner_id'] : 0;
  $domId = isset($data['dom_id']) ? $data['dom_id'] : 0;
  $vaultId = isset($data['vault_id']) ? $data['vault_id'] : 0;
  $topicId = isset($data['topic_id']) ? $data['topic_id'] : 0;

  $topicKey = isset($data['topic_key']) ? $data['topic_key'] : 0;
  $permission = isset($data['permission']) ? $data['permission'] : 0;

  $perPage = isset($data['per_page']) ? $data['per_page'] : 5;
  $subjectToken = isset($data['subject_token']) ? $data['subject_token'] : '';

  $connectedTopicId = isset($data['connected_topic_id']) ? $data['connected_topic_id'] : 0;
  $connectedTopicDomId = isset($data['connected_topic_dom_id']) ? $data['connected_topic_dom_id'] : '';

  $topicTypeFormId = isset($data['topic_type_form_id']) ? $data['topic_type_form_id'] : 0;   //Topic Manager

  switch ($type) {
    case "topic_link":   //TODO broken. Only supports one subject_token per topic.
      $query = "
        WITH tempList AS
        (
          SELECT connected_topic_id, row_number() OVER ( order by name ) AS row_num
          FROM alpn_topics_linked_view
          WHERE owner_id = {$ownerId} AND owner_topic_id = {$topicId} AND subject_token = '{$subjectToken}'
        )
        SELECT row_num
        FROM tempList
        WHERE connected_topic_id = '{$connectedTopicId}'
      ";
    break;
    case "network":
      $query = "
        WITH tempList AS
        (
          SELECT dom_id, row_number() OVER ( order by name ) AS row_num
          FROM alpn_topics_network_profile
          WHERE owner_id = {$ownerId}
        )
        SELECT row_num
        FROM tempList
        WHERE dom_id = '{$domId}'
      ";
    break;
    case "topic":
    $query = "
        WITH tempList AS
        (
          SELECT dom_id, row_number() OVER ( order by name ) AS row_num
          FROM alpn_topics_with_joins
          WHERE search_key = {$ownerId}
        )
        SELECT row_num
        FROM tempList
        WHERE dom_id = '{$domId}'
    ";
    break;
    case "vault":
      $query = "
      WITH tempList AS
      (
        SELECT id, row_number() OVER ( order by modified_date DESC ) AS row_num
        FROM alpn_vault_all
        WHERE topic_key = '{$topicKey}' AND access_level <= {$permission}
      )
      SELECT  row_num
      FROM    tempList
      WHERE   id = {$vaultId}
    ";
    break;
    case "table_topic_types":
    $query = "
      WITH tempList AS
      (
        SELECT form_id, row_number() OVER ( order by name ASC ) AS row_num
        FROM alpn_topic_types
        WHERE owner_id = {$ownerId}
      )
      SELECT  row_num
      FROM    tempList
      WHERE   form_id = {$topicTypeFormId}
    ";
    break;
}
  if ($query) {
    $result = $wpdb->get_row($query);
    if (isset($result->row_num)) {
      $rowNum = $result->row_num;
      return intval(($rowNum - 1) / $perPage);
    }
  }
  return -1;
}

function pte_async_job ($url, $params) {
    foreach ($params as $key => &$val) {
      if (is_array($val)) $val = implode(',', $val);
        $post_params[] = $key.'='.urlencode($val);
    }
    $post_string = implode('&', $post_params);
    $parts = parse_url($url);
    $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);
    $out = "POST ".$parts['path']." HTTP/1.1\r\n";
    $out.= "Host: ".$parts['host']."\r\n";
    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
    $out.= "Content-Length: ".strlen($post_string)."\r\n";
    $out.= "Connection: Close\r\n\r\n";
    if (isset($post_string)) $out.= $post_string;
    fwrite($fp, $out);
    fclose($fp);
}

function pte_format_pstn_number($phoneNumber){
  $phoneNumber = (substr($phoneNumber, 0, 1) == "+") ? $phoneNumber : "+{$phoneNumber}";
	$lastFour = substr($phoneNumber, 8);
	$firstThree = substr($phoneNumber, 5, 3);
	$areaCode = substr($phoneNumber, 2, 3);
	$country = substr($phoneNumber, 0, 2);
	return ($country . " (" . $areaCode . ") " . $firstThree . "-" . $lastFour);
}

function pte_release_all_pstn_numbers($ownerId){
  global $wpdb;
  $pstnNumbers = $wpdb->get_results(
    $wpdb->prepare("SELECT pstn_number from alpn_pstn_numbers WHERE owner_id = %s AND release_date IS NULL", $ownerId)
   );
   foreach ($pstnNumbers as $key => $value) {
     $phoneNumber = $value->pstn_number;
     pte_release_pstn_number($phoneNumber);
   }
}

function pte_release_pstn_number($phoneNumber) {
  global $wpdb;
  if ($phoneNumber) {
    try {
      $results = $wpdb->get_results(
        $wpdb->prepare("SELECT pstn_uuid from alpn_pstn_numbers WHERE pstn_number = %s", $phoneNumber)
       );
       if (isset($results[0])) {
        $pstnUuid = $results[0]->pstn_uuid;
         //Release
        $webhook = pte_call_documo('number_release', array('pstn_uuid' => $pstnUuid));
        $webhookData = json_decode($webhook, true);

        $now = date ("Y-m-d H:i:s", time());
        $pstnData = array(
          "release_date" => $now
        );
        $whereClause = array(
          'pstn_uuid' => $pstnUuid
        );
        $wpdb->update( 'alpn_pstn_numbers', $pstnData, $whereClause );
        return array(
          "error" => false,
          "message_key" => "pte_pstn_number_release_success",
          "pstn_uuid" => $pstnUuid
        );
       } else {
         return array(
           "error" => true,
           "message_key" => "pte_pstn_number_release_number_not_found"
         );
       }
    } catch (\Exception $e) {
        alpn_log($e);
        return array(
          "error" => true,
          "message_key" => "pte_pstn_number_release_exception",
          "exception" => $e
        );
    }
  }
  return array(
    "error" => true,
    "message_key" => "pte_pstn_number_release_number_not_provided"
  );
}

function get_user_fax_numbers() {

  global $wpdb_readonly;
  $faxNumbers = '';
  $userInfo = wp_get_current_user();
  $ownerId = $userInfo->data->ID;
  $ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true );
  $resultsNumbers = $wpdb_readonly->get_results(
    $wpdb_readonly->prepare(
      "SELECT p.id, p.pstn_number, p.topic_id FROM alpn_pstn_numbers p LEFT JOIN alpn_topics t ON t.id = p.topic_id WHERE p.owner_id = %s AND ISNULL(release_date) ORDER BY t.name ASC;", $ownerId)
  );
  if (isset($resultsNumbers[0])) {

    $resultsTopics = $wpdb_readonly->get_results(
      $wpdb_readonly->prepare("SELECT t.id, t.name FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON tt.id = t.topic_type_id WHERE t.owner_id = %d AND t.special = 'topic' AND t.name != '' AND (tt.topic_class = 'topic' OR tt.topic_class = 'link') ORDER BY name ASC", $ownerId)
    );

    foreach ($resultsNumbers as $key => $value) {
      $topicList = '';
      $phoneNumber = $value->pstn_number;
      $formattedNumber = pte_format_pstn_number($phoneNumber);
      $topicId = $value->topic_id;
    	$phoneNumberKey = substr($phoneNumber, 1);


    	$topicList .= "<select id='alpn_select2_small_{$phoneNumberKey}' data-ptrid='{$phoneNumber}'>";
    	$topicList .= "<option value='{$ownerNetworkId}'>Personal</option>";
    	foreach ($resultsTopics as $key1 => $value1) {
          $selected = ($value1->id == $topicId) ? " SELECTED" : "";
      		$id = $value1->id;
      		$name = $value1->name;
      		$topicList .= "<option value='{$id}' {$selected}>{$name}</option>";
  	  }
	    $topicList .= "</select>";
      $faxNumbers .= "<li class='pte_important_topic_scrolling_list_item' style='' >";
      $faxNumbers .= "<div class='pte_scrolling_item_left'><div class='pte_pstn_topic_list'>" . $topicList  . "</div><div class='pte_pstn_number_list'>" . $formattedNumber  . "</div></div>";
      $faxNumbers .= "<div class='pte_scrolling_item_right'><i class='far fa-minus-circle pte_scrolling_list_remove' title='Release Fax Number' onclick='pte_handle_release_fax_number(this);'></i></div>";
      $faxNumbers .= "<div style='clear: both;'>";
      $faxNumbers .= "</div>";
      $faxNumbers .= "</li>";
      $faxNumbers .= "
      <script>
          jQuery('#alpn_select2_small_' + '{$phoneNumberKey}').select2({
            theme: 'bootstrap',
            width: '130px',
            allowClear: false
          });
          jQuery('#alpn_select2_small_' + '{$phoneNumberKey}').on('select2:select', function (e) {
            var ptrid = jQuery(e.currentTarget).data('ptrid');
            var data = e.params.data;
            pte_update_fax_route_topic(ptrid, data);
          });
      </script>
      ";
  }
}
  return $faxNumbers;
}

function get_network_contact_topics($networkContactId) {

  global $wpdb_readonly;
  $contactTopics = '';

  $userInfo = wp_get_current_user();
  $ownerId = $userInfo->data->ID;
  $ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true );

  $resultTopics = $wpdb_readonly->get_results(
    $wpdb_readonly->prepare(
      "SELECT t.id, t.name, t.dom_id, t.topic_type_id, t.about FROM alpn_proteams p JOIN alpn_topics t ON t.id = p.topic_id WHERE p.owner_id = '%s' AND p.proteam_member_id = '%s' ORDER BY name ASC;", $ownerId, $networkContactId)
  );

  if (isset($resultTopics[0])) {

    $contactTopics .= "<div class='pte_route_container_title'>Teams</div>";
    $contactTopics .= "<div id='pte_contacts_topics_container'>";
    foreach ($resultTopics as $key => $value) {
      $topicList = '';
      $topicId = $value->id;
      $topicName = $value->name;
      $topicDomId = $value->dom_id;
      $topicTypeId = $value->topic_type_id;
      $topicAbout = $value->about;

      $topicAll = "{$topicName} - {$topicAbout}";

      $contactTopics .= "<li class='pte_important_topic_scrolling_list_item'>";
      $contactTopics .= "<div class='pte_scrolling_item_full' title='Link to this topic'><div class='pte_link_bar_link_contacts'><div data-topic-id='{$topicId}' data-topic-dom-id='{$topicDomId}' data-topic-type-id='{$topicTypeId}' data-operation='topic_info' class='interaction_panel_row_link' onclick='pte_handle_interaction_link_object(this);' style='text-align: left; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;'><div class='pte_icon_interaction_link'><i class='far fa-info-circle'></i></div>{$topicAll}</div></div></div>";
      $contactTopics .= "</li>";
  }
  $contactTopics .= "</div>";
}
  return $contactTopics;
}


function get_routing_email_addresses() {

  global $wpdb_readonly;
  $domainName = PTE_HOST_DOMAIN_NAME;
  $emailAddresses = '';
  $userInfo = wp_get_current_user();
  $ownerId = $userInfo->data->ID;
  $ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true );

  $resultsEmails = $wpdb_readonly->get_results(
    $wpdb_readonly->prepare(
      "SELECT id, email_route_id, name FROM alpn_topics WHERE owner_id = '%s' AND email_route_id IS NOT NULL ORDER BY name ASC;", $ownerId)
  );

  if (isset($resultsEmails[0])) {
    foreach ($resultsEmails as $key => $value) {
      $topicList = '';
      $topicId = $value->id;
      $topicName = $value->name;

      $dottedName = str_replace(array(', ', ',', "'", '"'), array('.', '.', "", ""), $topicName);
      $emailAddress = "{$dottedName} - ProTeam Edge Topic <{$value->email_route_id}@files.{$domainName}>";

      $emailAddresses .= "<li class='pte_important_topic_scrolling_list_item'>";
      $emailAddresses .= "<div class='pte_scrolling_item_left' title='Copy Email Address to Clipboard'><div class='pte_pstn_topic_list pte_topic_link' onclick='pte_topic_link_copy_string(\"Email\", \"{$emailAddress}\");'><i class='far fa-copy' style='margin-right: 5px;'></i>" . $topicName  . "</div></div>";
      $emailAddresses .= "<div class='pte_scrolling_item_right'><i class='far fa-minus-circle pte_scrolling_list_remove' title='Remove Email Route' onclick='pte_handle_release_email_route({$topicId});'></i></div>";
      $emailAddresses .= "<div style='clear: both;'>";
      $emailAddresses .= "</div>";
      $emailAddresses .= "</li>";
  }
}
  return $emailAddresses;
}

function pte_get_email_ux() {

  global $wpdb_readonly;
  $topicOptions = $topicList = '';
  $userInfo = wp_get_current_user();
  $ownerId = $userInfo->data->ID;

  $results = $wpdb_readonly->get_results(
    $wpdb_readonly->prepare(
      "SELECT id, name, '0' AS row_type FROM alpn_topics WHERE owner_id = '%s' AND special = 'user' AND name != '' UNION
       SELECT id, name, '1' AS row_type FROM alpn_topics WHERE owner_id = '%s' AND special = 'contact' AND name != '' UNION
       SELECT t.id, t.name, '2' AS row_type FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON tt.id = t.topic_type_id WHERE t.owner_id = '%s' AND t.special = 'topic' AND t.name != '' AND (tt.topic_class = 'topic' OR tt.topic_class = 'link')
       ORDER BY row_type ASC, name ASC;",
       $ownerId, $ownerId, $ownerId)
  );

  if (isset($results[0])) {

    $topicOptions .= "
      <option></option>
      <optgroup label='Personal'>
      <option value='{$results[0]->id}'>{$results[0]->name}</option>
      </optgroup>
      <optgroup label='Contacts'>
    ";

    foreach ($results as $key => $value) {
        if ($value->row_type == 1) {
          $topicOptions .= "
            <option value='{$value->id}'>{$value->name}</option>
            ";
        }
    }

    $topicOptions .= "
      </optgroup>
      <optgroup label='Topics'>
    ";

    foreach ($results as $key => $value) {
      if ($value->row_type == 2) {
        $topicOptions .= "
          <option value='{$value->id}'>{$value->name}</option>
          ";
      }
    }

    $topicOptions .= "
      </optgroup>
    ";
  }

  $topicList .= "<select id='pte_extension_topic_select'>";
  $topicList .= $topicOptions;
  $topicList .= "</select>";
  $emailAddresses = get_routing_email_addresses();
  $emailUx = "
    <div id='pte_email_ux_container'>
      <div id='pte_email_ux_container_inner'>
        <div class='pte_fax_words'>
          Email attachments securely route to the designated Topic Vault. Click on the Topic Name to copy the email address to the clipoard. Disposible in case of abuse.
        </div>
        <div class='pte_email_address_selector_outer'><div class='pte_email_address_selector_left'></div><div class='pte_email_address_selector_right'>{$topicList}</div></div>
        <ul id='pte_emails_assigned' class='pte_important_topic_scrolling_list'>{$emailAddresses}</ul>
      </div>
    </div>
  ";
  $emailUx .= "
  <script>
      jQuery('#pte_extension_topic_select').select2({
        theme: 'bootstrap',
        width: '100%',
        allowClear: true,
        closeOnSelect: false,
        placeholder: 'Select a Topic...'
      });
      jQuery('#pte_extension_topic_select').on('select2:select', function (e) {
        var data = e.params.data;
        pte_update_email_route(data);
        jQuery('#pte_extension_topic_select').val('').trigger('change');
      });
  </script>
  ";
  return $emailUx;
}

function pte_get_fax_ux() {
  $faxNumbers = get_user_fax_numbers();
  $faxUx = "
  <div id='pte_fax_ux_container'>
    <div id='pte_fax_ux_container_inner'>
      <div id='pte_pstn_number_widget'>
          <div id='pte_pstn_number_widget_left'>
            <input type='text' id='pte_pstn_widget_area_code' placeholder='Area Code'>
            <button id='pte_pstn_widget_lookup' class='btn btn-danger btn-sm' onclick='pte_pstn_widget_lookup();'>Lookup</button>
          </div>
          <div id='pte_pstn_number_widget_right'>
            <div class='pte_inner_widget_text'>Enter desired area code and press 'Lookup'. Press 'Use', then assign the Topic to the fax number.</span></div>
          </div>
          <div style='clear: both;'></div>
        </div>
        <div class='pte_fax_words'>Faxes securely route to the selected Topic Vault in PDF format. Disposible in case of abuse. Non-transferable.</div>
        <ul id='pte_fax_numbers_assigned' class='pte_important_topic_scrolling_list' style='padding: 5px;'>{$faxNumbers}</ul>
      </div>
    </div>
  ";
  return $faxUx;
}
//        <div class='pte_fax_words'>By pressing 'Use Fax Number', you will be billed $1/day up to a maximum of $10/month per fax number plus per-page fees. To stop using a fax number, press the 'Release Fax Number' icon.</div>

function pte_documo_fax_send($sendData){
  $sitePath = getRootUrl() . "alpn_send_documo_fax.php";
  pte_async_job ($sitePath, array("data" => json_encode($sendData)));
}


function pte_call_documo($type, $data){
  $urlbase = 'https://api.documo.com/v1/';
  $apiKey = FAX_DOCUMO_API_KEY;
  $query = '';
  $headers = array(
    "Authorization: Basic {$apiKey}"
  );
  $options = array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
  );
  switch ($type) {
    case "get_accounts":
      $endPoint = 'accounts';
      $query = http_build_query($data);
    break;
    case "number_search":
      $endPoint = 'numbers/provision/search';
      $headers[] = "Accept: application/json";
      $query = http_build_query($data);
    break;
    case "send_fax":
    $endPoint = 'faxes';
    $headers[] = "Content-Type: multipart/form-data";
    $pstnNumber = $data['pstn_number'];
    $attachmentPath = $data['attachment_path'];
    $coverSheetPath = $data['cover_sheet_path'];
     $body = array(
       "attachments['file1']" => new CURLFile($coverSheetPath, 'application/pdf', 'cover_sheet'),
       "attachments['file2']" => new CURLFile($attachmentPath, 'application/pdf', 'attachment'),
       "faxNumber" => $pstnNumber,
       "coverPage" => "false"
     );
      $options[CURLOPT_POST] = true;
      $options[CURLOPT_CUSTOMREQUEST] = "POST";
      $options[CURLOPT_POSTFIELDS] = $body;
    break;
    case "get_webhooks":
      $endPoint = 'webhooks';
      $urlbase = 'https://api.documo.com/';
      $query = http_build_query($data);
    break;
    case "setup_webhook":
      $hostDomain = PTE_HOST_DOMAIN_NAME;
      $endPoint = 'webhooks';
      $headers[] = "Content-Type: application/x-www-form-urlencoded";
      $urlbase = 'https://api.documo.com/';
      $pstnUuid = $data['pstn_uuid'];
      $pstnNumber = $data['pstn_number'];
      $body = array(
        'name' => "For: {$pstnNumber}",
        'url' => "https://{$hostDomain}/wp-content/themes/memberlite-child-master/pte_fax_in_out.php",
        'events' => '{"fax.inbound":true}',
        'numberId' => $pstnUuid,
        'attachmentEnabled' => true
      );
      $options[CURLOPT_POST] = true;
      $options[CURLOPT_CUSTOMREQUEST] = "POST";
      $options[CURLOPT_POSTFIELDS] = http_build_query($body);
    break;
    case "get_numbers":
      $endPoint = 'numbers';
      $headers[] = "Accept: application/json";
      $query = http_build_query($data);
    break;
    case "number_provision":
      $endPoint = 'numbers/provision';
      $headers[] = "Content-Type: application/x-www-form-urlencoded";
      $body = array(
        'numbers' => $data['phone_number'],
        'type' => 'local'
      );
      $options[CURLOPT_POST] = true;
      $options[CURLOPT_CUSTOMREQUEST] = "POST";
      $options[CURLOPT_POSTFIELDS] = http_build_query($body);
    break;
    case "number_release":
      $uuidPhoneNumber = isset($data['pstn_uuid']) ? $data['pstn_uuid'] : "";
      $endPoint = "numbers/{$uuidPhoneNumber}/release";
      $options[CURLOPT_CUSTOMREQUEST] = "DELETE";
    break;
  }
  $options[CURLOPT_URL] = "{$urlbase}{$endPoint}?{$query}";
  $options[CURLOPT_HTTPHEADER] = $headers;
  $ch = curl_init();
  curl_setopt_array($ch, $options);
  $response = curl_exec($ch);
  curl_close($ch);
  return $response;
}


function pte_get_interaction_settings_sliders($data) {

  global $wpdb_readonly;
  $sliders = "";
  $userInfo = wp_get_current_user();
  $ownerId = $userInfo->data->ID;
  $ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true );


  //TODO from DB, with color to match interactions
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


function pte_manage_topic_link($operation, $requestData, $subjectToken = 'pte_external'){

  global $wpdb;
  switch ($operation) {
    case "add_edit_topic_bidirectional_link":
      $ownerId = isset($requestData['owner_id']) ? $requestData['owner_id'] : 0;  //context is the other person.
      $topicId = isset($requestData['topic_id']) ? $requestData['topic_id'] : 0;
      $listDefault = isset($requestData['list_default']) && $requestData['list_default'] ? $requestData['list_default'] : 'no';
      $connectedId = isset($requestData['connected_id']) ? $requestData['connected_id'] : 0;
      $connectionLinkTopicId = isset($requestData['connection_link_topic_id']) ? $requestData['connection_link_topic_id'] : 0;
      $results = $wpdb->get_results(
      	$wpdb->prepare("SELECT id FROM alpn_topic_links WHERE owner_topic_id_1 = %s AND owner_topic_id_2 = %s AND subject_token = %s", $topicId, $connectionLinkTopicId, $subjectToken)
       );
       if (!isset($results[0])) {  //new insert

         $rowData = array(      //TODO
           'owner_id_1' => $ownerId,
           'owner_topic_id_1' => $topicId,
           'owner_id_2' => $connectedId,
           'owner_topic_id_2' => $connectionLinkTopicId,
           'subject_token' => $subjectToken,
           'list_default' => $listDefault
         );
         $wpdb->insert( 'alpn_topic_links', $rowData ); //new link
         //TODO can we reduce db hits?
          $results = $wpdb->get_results(
         	  $wpdb->prepare("SELECT dom_id FROM alpn_topics WHERE id = %s", $connectionLinkTopicId)
          );

          if (isset($results[0])) {
            $rowData['owner_dom_id_2'] = $results[0]->dom_id;
            return $rowData;
          }
       }
       return false;  //existing link
    break;
    case "delete_topic_bidirectional_link":
      $linkId = isset($requestData['link_id']) ? $requestData['link_id'] : 0;
      if ($linkId) {
        $whereclause = array('id' => $linkId);
        $wpdb->delete( "alpn_topic_links", $whereclause );
        return true;
      } else {
       return false;
     }
    break;
  }
}

function pte_update_interaction_weight($listKey, $data) {
    global $wpdb;
    $interactionUpdates = $whereClause = array();
    $operation = isset($data['operation']) ? $data['operation'] : "important_added";
    $importanceValue = ($operation == 'important_added') ? 1 : 0;

    $whereClause['owner_network_id'] = $data['owner_network_id'];
    $whereClause['state'] = 'active';

    if ($listKey == 'pte_important_network') {
      $interactionUpdates['network_is_important'] = $importanceValue;
      $whereClause['imp_network_id'] = $data['item_id'];
      $wpdb->update( 'alpn_interactions', $interactionUpdates, $whereClause );
    }

    //Topic  Regarding or Context Topic can be any of Personal, Topic or Contact(Network) So if a user list is changed, importance is updated

    //clean up query and run every time on Topic.
    $interactionUpdates = array();
    if (isset($whereClause['imp_network_id'])) {unset ($whereClause['imp_network_id']);}
    $interactionUpdates['topic_is_important'] = $importanceValue;
    $whereClause['imp_topic_id'] = $data['item_id'];
    $wpdb->update( 'alpn_interactions', $interactionUpdates, $whereClause );

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


function pte_get_create_linked_form($ownerTopicId, $subjectToken, $topicKey){

  global $wpdb;
  $userInfo = wp_get_current_user();
  $ownerId = $userInfo->data->ID;
  $domID = '';

  $results = $wpdb->get_results(
    $wpdb->prepare("SELECT t.dom_id, tl.id FROM alpn_topic_links tl LEFT JOIN alpn_topics t ON t.id = tl.owner_topic_id_2 WHERE tl.owner_topic_id_1 = %d AND tl.subject_token = %s ", $ownerTopicId, $subjectToken)
   );

   if (isset($results[0])) {
      $domId = $results[0]->dom_id;
   } else {
     //Create a default topic and link, then return dom_id.
     $results = $wpdb->get_results(
       $wpdb->prepare("SELECT form_id FROM alpn_topic_types WHERE type_key = %s ", $topicKey)
      );

     if (isset($results[0])) {
        $formId = $results[0]->form_id;
        $now = date ("Y-m-d H:i:s", time());
        $entry = array(
          'id' => $formId,
          'fields' => array()
        );
        $newTopicId =  alpn_handle_topic_add_edit ('', $entry, '', '' );	//Add a topic of proper type
        //Make the link
        $requestData = array(
        	'owner_id' => $ownerId,
        	'topic_id' => $ownerTopicId,
        	'connected_id' => $ownerId,
        	'connection_link_topic_id' => $newTopicId
        );
        pte_manage_topic_link('add_edit_topic_bidirectional_link', $requestData, $subjectToken);

        $results = $wpdb->get_results(
          $wpdb->prepare("SELECT dom_id FROM alpn_topics WHERE id = %d ", $newTopicId)
         );

         if (isset($results[0])) {
           $domId = $results[0]->dom_id;
         }
    }
   }

   return "<div id='pte_tab_record_wrapper' data-dom_id='{$domId}'>" . pte_get_linked_form($domId) . "</div>";
}


function pte_get_linked_form($domId){   //TODO Merge with select topic
    $ppCdnBase = PTE_IMAGES_ROOT_URL;
    global $wpdb;
    $html = $topicHtml = '';
    $replaceStrings = array();
  	$results = $wpdb->get_results(  //TODO should this be selecting based on links?
  		$wpdb->prepare("SELECT concat(JSON_UNQUOTE(JSON_EXTRACT(t3.topic_content, '$.person_givenname')), ' ', JSON_UNQUOTE(JSON_EXTRACT(t3.topic_content, '$.person_familyname'))) AS owner_nice_name, t.*, p.access_level, f.pstn_number, tt.id AS topic_type_id, tt.type_key, tt.form_id, tt.name AS topic_name, tt.icon, tt.topic_type_meta, tt.html_template, t2.topic_content AS connected_contact_topic_content, t3.name AS owner_name, t3.topic_content AS owner_topic_content, t2.image_handle AS profile_handle FROM alpn_topics t LEFT JOIN alpn_proteams p ON p.topic_id = t.id AND p.owner_id = t.owner_id LEFT JOIN alpn_pstn_numbers f ON f.topic_id = t.id LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id LEFT JOIN alpn_topics t2 ON t2.owner_id = t.connected_id AND t2.special = 'user' LEFT JOIN alpn_topics t3 ON t3.owner_id = t.owner_id AND t3.special = 'user' WHERE t.dom_id = %s", $domId)
  	 );
  	if (isset($results[0])) {   //TODO Merge/generalize with topic_select
  		$topicData = $results[0];
  		$topicMeta = json_decode($topicData->topic_type_meta, true);
  		$topicContent = json_decode($topicData->topic_content, true);
  		$topicHtml = stripcslashes($topicData->html_template);
      $topicLogoHandle = $topicData->logo_handle;
      $topicDomId = $topicData->dom_id;
  		$typeKey = $topicData->type_key;
  		$nameMap = pte_name_extract($topicMeta['field_map']);
  		$fieldMap = array_flip($nameMap);
  		foreach($topicContent as $key => $value){	   //deals with date/time being arrays
  			if (is_array($value)) {
  				foreach ($value as $key2 => $value2) {
  					$actualValue = $value2;
  				}
  			} else {
  				$actualValue = $value;
  			}
        switch ($key) {  //TODO this is iterating through stored data. If schema changes, then the data is out of date. Workaround, edit/save the record. Shouldn't be a problem if topic starts with these system fields but what about new system fields?
          case 'pte_added_date':
            $replaceStrings['-{' . 'pte_added_date' . '}-'] = pte_date_to_js($topicData->created_date);
            $replaceStrings['-{' . 'pte_added_date_title' . '}-'] = $nameMap['pte_added_date'];
          break;
          case 'pte_modified_date':
            $replaceStrings['-{' . 'pte_modified_date' . '}-'] = pte_date_to_js($topicData->modified_date);
            $replaceStrings['-{' . 'pte_modified_date_title' . '}-'] = $nameMap['pte_modified_date'];
          break;
          case 'pte_image_logo':
            $topicLogoUrl = "";
            if ($topicLogoHandle) {
              $topicLogoUrl = "<img class='pte_logo_image_screen' src='{$ppCdnBase}{$topicLogoHandle}'>";
            }
            $replaceStrings['-{' . 'pte_image_logo' . '}-'] = $topicLogoUrl;
            $replaceStrings['-{' . 'pte_image_logo_title' . '}-'] = $nameMap['pte_image_logo'];
          break;
          default:
            $replaceStrings['-{' . $key . '}-'] = $actualValue;
      			$replaceStrings['-{' . $key . '_title}-'] = isset($nameMap[$key]) ? $nameMap[$key] : "";
          break;
        }
  		}
  		$replaceStrings["{topicDomId}"] = $topicDomId;
      $businessTypesList = get_custom_post_items('pte_profession', 'ASC');
      if (isset($replaceStrings['-{person_hasoccupation_occupation_occupationalcategory}-'])) {  //TODO test this
      	$replaceStrings['-{person_hasoccupation_occupation_occupationalcategory}-'] = $businessTypesList[$replaceStrings['-{person_hasoccupation_occupation_occupationalcategory}-']];
      } else {
      	$replaceStrings['-{person_hasoccupation_occupation_occupationalcategory}-'] = "Not Specified";
      }
  	}
    return str_replace(array_keys($replaceStrings), $replaceStrings, $topicHtml);
}


//TODO Make this use SELECT2 AJAX infinite scroll paging.

function pte_get_topic_list($listType, $topicTypeId = 0, $uniqueId = '', $typeKey = '', $hidePlaceholder = false, $emptyMessage = '') {
  global $wpdb_readonly;
  $topicOptions = "";
  $userInfo = wp_get_current_user();
  $userID = $userInfo->data->ID;
  $userNetworkId = get_user_meta( $userID, 'pte_user_network_id', true );

  alpn_log('pte_get_topic_list');
  alpn_log($topicTypeId);

  if ($userID && $listType) {
    switch ($listType) {
      case "linked_topics":
      $results = $wpdb_readonly->get_results(
        $wpdb_readonly->prepare(
          "SELECT connected_topic_id as id, name FROM alpn_topics_linked_view WHERE owner_topic_id = %d AND subject_token = %s AND owner_id = %d ORDER BY name ASC", $topicTypeId, $typeKey, $userID)
      );
      alpn_log('pte_get_topic_list - RESULTS');
      alpn_log($wpdb_readonly->last_query);
      alpn_log($wpdb_readonly->last_error);
      alpn_log($results);

      $id = $uniqueId ? $uniqueId : 'pte_by_type_key';
      break;
      case "type_key":
      $results = $wpdb_readonly->get_results(
        $wpdb_readonly->prepare(
          "SELECT t.id, t.name FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON tt.id = t.topic_type_id WHERE t.owner_id = %d AND tt.type_key = %s ORDER BY t.name ASC;", $userID, $typeKey)
      );
      $id = $uniqueId ? $uniqueId : 'pte_by_type_key';
      break;
      case "network_contacts":
      $results = $wpdb_readonly->get_results(
        $wpdb_readonly->prepare(
          "SELECT id, name FROM alpn_topics WHERE owner_id = %d AND special = 'contact' ORDER BY name ASC;", $userID)
      );
      $id = 'pte_important_network_topic_list';
      break;
      case "topics":    //Primary only
        $results = $wpdb_readonly->get_results(
          $wpdb_readonly->prepare(
            "SELECT t.id, t.name FROM alpn_topics t RIGHT JOIN alpn_topic_types tt ON tt.id = t.topic_type_id AND tt.topic_class = 'topic' AND tt.special = 'topic' WHERE t.owner_id = '%s' ORDER BY name ASC;", $userID)
        );
        alpn_log($results);
        $id = 'pte_important_topic_list';
        break;
        case "single_schema_type":
          $results = $wpdb_readonly->get_results(
            $wpdb_readonly->prepare(
              "SELECT t.id, t.name FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON tt.id = t.topic_type_id WHERE t.owner_id = '%s' AND tt.schema_key = %s ORDER BY name ASC;", $userID, $topicTypeId)
          );
          $id = $uniqueId ? $uniqueId : 'pte_single_topic_type_list';
        break;
        case "active_core_topic_types":
          $topicTypeState = 'active';
          $results = $wpdb_readonly->get_results(
            $wpdb_readonly->prepare(
              "SELECT id, name FROM alpn_topic_types WHERE topic_state = %s AND special = 'topic' ORDER BY name ASC;", $topicTypeState)
          );
          $id = $uniqueId ? $uniqueId : 'pte_active_core_topic_types';
        break;
    }
    if (isset($results[0])) {
      $topicOptions .= "<select id='{$id}'>";
      if (!$hidePlaceholder) {$topicOptions .= "<option></option>";}
      foreach ($results as $key => $value) {
          $topicOptions .= "<option value='{$value->id}'>{$value->name}</option>";
      }
      $topicOptions .= "</select>";
    } else {
      $topicOptions = "<div class='pte_widget_message'>$emptyMessage</div>";
    }
  }
  return $topicOptions;
}

function pte_proteam_state_change_sync($data){
  alpn_log("pte_proteam_state_change_sync...");
  //alpn_log($data);

  global $wpdb;

  $connectedType =  isset($data['connected_type']) ? $data['connected_type'] : '';
  $ptState =  isset($data['state']) ? $data['state'] : 0;
  $ptId =  isset($data['proteam_row_id']) ? $data['proteam_row_id'] : 0;
  $ownerId = isset($data['owner_id']) ? $data['owner_id'] : 0;
  $processId = isset($data['process_id']) ? $data['process_id'] : '';

if ($connectedType && $ptState && $ptId) {

    $proTeamData = array(
      "connected_type" => $connectedType,
      "state" => $ptState,
      "process_id" => $processId
    );
    $whereClause = array(
      "id" => $ptId
    );
    $wpdb->update("alpn_proteams", $proTeamData, $whereClause);

    alpn_log('ProTeams Updated...');

    if ($ownerId) {

      $syncdata = array(
        "sync_type" => "add_update_section",
        "sync_section" => "proteam_card_update",
        "sync_user_id" => $ownerId,
        "sync_payload" => $data
      );
      pte_manage_user_sync($syncdata);
      alpn_log('ProTeams Sync Sent...');
    }
  }
}

function pte_manage_user_sync($data){   ///Must be a user and have a wpid

  alpn_log("pte_manage_user_sync...");
  //alpn_log($data);

  global $wpdb;

  $userInfo = wp_get_current_user();
  $userID = $userInfo->data->ID;

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
  $syncUserId = isset($data['sync_user_id']) ? $data['sync_user_id'] : $userID;

  if (!$syncUserId) {
    $syncId = '';
  } else {
    $syncId = get_user_meta( $syncUserId, 'pte_user_sync_id', true );
  }

  switch ($syncType) {

    case "update_all_sync_ids":

      $results = $wpdb->get_results("SELECT id, owner_id, sync_id, name FROM alpn_topics where special = 'user'");

      foreach($results as $value) {
        pp("Handled " . $value->name);

        if ($value->sync_id && $value->owner_id) {

          try {
            $sync_map = $twilio->sync->v1->services($syncServiceId)
                                         ->syncMaps($value->sync_id)
                                         ->update(array(
                                           "uniqueName" => $value->owner_id
                                         ));

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
      }

      return;

    break;
    case "return_create_sync_id":
    alpn_log("return_create_sync_id...");
    if (!$syncId) {
            try {
              $sync_map = $twilio->sync->v1->services($syncServiceId)
                                           ->syncMaps
                                           ->create(array(
                                             "uniqueName" => $syncUserId
                                           ));
              $syncId = $sync_map->sid;
              $topicData = array(
                "sync_id" => $syncId
              );
          		$whereClause['owner_id'] = $syncUserId;
              $whereClause['special'] = 'user';
              $wpdb->update( 'alpn_topics', $topicData, $whereClause );  //persist channelid

              update_user_meta( $syncUserId, "pte_user_sync_id",  $syncId); //SH

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

  alpn_log('pte_manage_user_connection...');
  //alpn_log($data);

  global $wpdb;

  $contactEmail = $data['contact_email'];
  $contactTopicId = $data['contact_topic_id'];
  $contactInfo = get_user_by('email', $contactEmail);

  if (isset($contactInfo->ID)) {

    $contactId = $contactInfo->ID;
    $contactNetworkId = get_user_meta( $contactId, 'pte_user_network_id', true ); //Contact Topic ID

    $userId = isset($data['owner_wp_id']) ? $data['owner_wp_id'] : '';
    $userInfo = get_user_by('id', $userId);
    $userEmail =  $userInfo->data->user_email;
    $userNetworkId = get_user_meta( $userId, 'pte_user_network_id', true ); //Owners Topic ID

    //go find ME in contact's contacts by email.

    $results = $wpdb->get_results(
    	$wpdb->prepare("SELECT id, owner_id, connected_id FROM alpn_topics WHERE owner_id = %s AND special = 'contact' AND alt_id = %s", $contactId, $userEmail)
     );

     if (isset($results[0])) {
       $contactId = $results[0]->owner_id;
       $connectedId = $results[0]->connected_id;
       $connectedTopicId = $results[0]->id;
       if (!$connectedId) {
         //Now go find contact in my Topics by email.
          $user = $wpdb->get_results(
          	$wpdb->prepare("SELECT id, name, about FROM alpn_topics WHERE owner_id = %s AND special = 'contact' AND alt_id = %s", $userId, $contactEmail)
           );

           if (isset($user[0])) {
             $userTopicId = $user[0]->id;
             $userName = $user[0]->name;
             $userAbout = $user[0]->about;

             $data = array(
              'owner_id' => $userId,
         			'topic_id' => $userTopicId,
              "contact_id" => $contactId
           		);
         		$newChannelId = pte_manage_cc_groups("get_create_channel", $data);     //create a channel for contact. Adds contact. Stores channel for contact
            $contactTopicData = $wpdb->get_results(
              $wpdb->prepare("SELECT name, about FROM alpn_topics WHERE id = %d", $contactNetworkId)
             );
             $contactName = isset($contactTopicData[0]) ? $contactTopicData[0]->name : "n/a";
             $contactAbout = isset($contactTopicData[0]) ? $contactTopicData[0]->about : "n/a";
             //user
             $topicData = array(
               'connected_id' => $contactId,
               'connected_network_id' => $contactNetworkId,
               'connected_topic_id' => $connectedTopicId,
               'name' => $contactName,
               'about' => $contactAbout
             );
             $whereClause = array(
               'id' => $userTopicId
             );
             $wpdb->update( 'alpn_topics', $topicData, $whereClause );

             $data = array(  //add contact to channel
              'topic_id' => $userTopicId,
              'user_id' => $contactId,
              'owner_id' => $userId
              );
            pte_manage_cc_groups("add_member", $data);

            $userTopicData = $wpdb->get_results(
              $wpdb->prepare("SELECT name, about FROM alpn_topics WHERE id = %d", $userNetworkId)
             );
             $userName = isset($userTopicData[0]) ? $userTopicData[0]->name : "n/a";
             $userAbout = isset($userTopicData[0]) ? $userTopicData[0]->about : "n/a";

             //contact
             $topicData = array(
               'connected_id' => $userId,
               'connected_network_id' => $userNetworkId,
               'connected_topic_id' => $userTopicId,
               'channel_id' => $newChannelId,
               'name' => $userName,
               'about' => $userAbout
             );
             $whereClause = array(
               'id' => $connectedTopicId
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
  $ownerInfo = wp_get_current_user();

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
  $syncId = isset($data['sync_id']) ? $data['sync_id'] : "";
  $topicName = isset($data['topic_name']) && $data['topic_name'] ? $data['topic_name'] : "New";
  $fullName = isset($data['full_name']) && $data['full_name'] ? $data['full_name'] : "";
  $imageHandle = isset($data['image_handle']) && $data['image_handle'] ? $data['image_handle'] : false;
  $ownerId = (isset($data['owner_id']) && $data['owner_id']) ? $data['owner_id'] : "";
  $contactId = (isset($data['contact_id']) && $data['contact_id']) ? $data['contact_id'] : false;
  $channelIdToDelete = (isset($data['channel_id']) && $data['channel_id']) ? $data['channel_id'] : false;

	switch ($operation) {

//TODO Exceptions - rooms there that didn't get deleted causing issues,
    case "update_channel_image":  //update channel with new name accounting for simple and shared topics.
      alpn_log("UPDATING CHANNEL IMAGE");
      $channelId = pte_manage_cc_groups("get_create_channel", $data);   //get or create for the first time.
      $channel = $twilio->chat->v2->services($chatServiceId)
                            ->channels($channelId)
                            ->fetch();
      $channelAttributes = json_decode($channel->attributes, true);
      $channelAttributes['image_handle'] = $imageHandle;
      $channel = $twilio->chat->v2->services($chatServiceId)
      ->channels($channelId)
      ->update(array(
          'attributes' => json_encode($channelAttributes)
        )
      );
    break;
    case "update_channel":  //update channel with new name accounting for simple and shared topics.
      alpn_log("UPDATING CHANNEL");
      $channelId = pte_manage_cc_groups("get_create_channel", $data);   //get or create for the first time.
      $channel = $twilio->chat->v2->services($chatServiceId)
                            ->channels($channelId)
                            ->fetch();
      $channelAttributes = json_decode($channel->attributes, true);
      $channelAttributes['image_handle'] = $imageHandle;
      $channelAttributes['topic_owner_id'] = $ownerId;
      $channel = $twilio->chat->v2->services($chatServiceId)
      ->channels($channelId)
      ->update(array(
          'friendlyName' => $topicName,
          'attributes' => json_encode($channelAttributes)
        )
      );
    break;

		case "get_create_channel":
    $channelId = "";
    if ($topicId && $ownerId) {
      $results = $wpdb->get_results(
      	$wpdb->prepare("SELECT channel_id, name, image_handle FROM alpn_topics WHERE id = %s", $topicId)
       );
      if (isset($results[0])) {
        $channelId = $results[0]->channel_id;
        $channelName = $results[0]->name;
        $imageHandle = $results[0]->image_handle;
        if (!$channelId) {
          try {
            $channelAttributes = array(
              'image_handle' => $imageHandle,
              'topic_owner_id' => $ownerId
            );
            if ($contactId) {
              $nameAttributes = array(
                'owner_id' => $ownerId,
                'contact_id' => $contactId
              );
              $channelName = json_encode($nameAttributes);
            }
            $channel = $twilio->chat->v2->services($chatServiceId)
              ->channels
              ->create(array(
                'type' => 'private',
                'friendlyName' => $channelName,
                'attributes' => json_encode($channelAttributes),
                'uniqueName' => $topicId
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
              alpn_log('get_create_channel');
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

          if ($topicId) {
            alpn_log('CLEARING CHANNEL');
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

      $channelId = pte_manage_cc_groups("get_create_channel", $data);   //get or create for the first time.

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
            alpn_log('add_member');
            alpn_log($response);
            alpn_log('Not sure why this is happening -- should be deleted as a member -- but shouldnt be a problem');
        }
    } else {   //TODO Handle error



    }

		break;

    case "delete_member":
    $channelId = pte_manage_cc_groups("get_create_channel", $data);

    if ($channelId) {  //TODO SEEMS Like this should not require a loop
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
          alpn_log('delete_member');
          alpn_log($response);
        }
    } else {  //TODO Handle Error


    }
    return false;
    break;

    case "delete_channel_by_channel_id":
      alpn_log("About to delete channel by channel ID...");
      if ($channelIdToDelete) {
        try {
          $channel = $twilio->chat->v2
            ->services($chatServiceId)
            ->channels($channelIdToDelete)
            ->delete();
            return true;
        } catch (Exception $e) {
            $response = array(
                'message' =>  $e->getMessage(),
                'code' => $e->getCode(),
                'error' => $e
            );
            alpn_log('delete_channel_by_channel_id');
            alpn_log($response);
        }
      } else { //TODO handle not channelID.


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
          alpn_log('delete_channel');
          alpn_log($response);
      }
    } else { //TODO handle error.


    }
    return false;
		break;

		case "add_user":
      try {
        $user = $twilio->chat->v2->services($chatServiceId)
          ->users
          ->create($ownerId);
        $imageHandle = "pte_icon_letter_n.png";  //TODO for new
        $attributes = json_encode(array(
          "image_handle" => $imageHandle,
          "full_name" => $fullName,
          "sync_id" => $syncId
        ));
        $updates = array(
                        "attributes" => $attributes,
                        "friendlyName" => $topicName
                       );
        $user = $twilio->chat->v2->services($chatServiceId)
                                 ->users($ownerId)
                                 ->update($updates);

        alpn_log("Created user with updated settings... " . $ownerId);

      } catch (Exception $e) {
          $response = array(
              'message' =>  $e->getMessage(),
              'code' => $e->getCode(),
              'error' => $e
          );
          alpn_log('add user');
          alpn_log($response);
      }

		break;
    case "update_all_user_attributes":

      $results = $wpdb->get_results("SELECT id, owner_id, sync_id, name, image_handle FROM alpn_topics where special = 'user'");

      foreach($results as $value) {

        if ($value->owner_id) {

          try {
            $user = $twilio->chat->v2->services($chatServiceId)
                                     ->users($value->owner_id)
                                     ->fetch();
            $attributes = json_decode($user->attributes, true);
            $attributes["image_handle"] = $value->image_handle;
            $attributes["full_name"] = $value->name;
            $attributes["sync_id"] = $value->sync_id;
            $updates = array(
                            "attributes" => json_encode($attributes)
                           );

            $user = $twilio->chat->v2
              ->services($chatServiceId)
              ->users($value->owner_id)
              ->update($updates);
              alpn_log("Updated user... " . $ownerId);

          } catch (Exception $e) {
              $response = array(
                  'message' =>  $e->getMessage(),
                  'code' => $e->getCode(),
                  'error' => $e
              );
              alpn_log($response);
          }
        }
      }

    break;

    case "update_user":
      $user = $twilio->chat->v2->services($chatServiceId)
                             ->users($ownerId)
                             ->fetch();
      $attributes = json_decode($user->attributes, true);
      $attributes["image_handle"] = $imageHandle;
      $attributes["full_name"] = $fullName;
      $attributes["sync_id"] = $syncId;


      alpn_log("Updated DATA... ");
      alpn_log($attributes);



      $updates = array(
                      "attributes" => json_encode($attributes),
                      "friendlyName" => $topicName
                     );

    $user = $twilio->chat->v2
      ->services($chatServiceId)
      ->users($ownerId)
      ->update($updates);
      alpn_log("Updated user... " . $ownerId);
		break;

    case "update_user_image":
      alpn_log("Updating user image... ");
      try {

        $user = $twilio->chat->v2->services($chatServiceId)
                               ->users($ownerId)
                               ->fetch();
        $attributes = json_decode($user->attributes, true);


        alpn_log("Before... ");
        alpn_log($attributes);



        $attributes["image_handle"] = $imageHandle;

        alpn_log("After... ");
        alpn_log($attributes);

        $user = $twilio->chat->v2
          ->services($chatServiceId)
          ->users($ownerId)
          ->update(array(
            "attributes" => json_encode($attributes, true)
          ));

          alpn_log("Updated user image... ");
        } catch (Exception $e) {
            alpn_log("Tried to Update user image... ");
            $response = array(
                'message' =>  $e->getMessage(),
                'code' => $e->getCode(),
                'error' => $e
            );
            alpn_log($response);
        }		break;

    case "delete_user":
      try {
        $user = $twilio->chat->v2
          ->services($chatServiceId)
          ->users($ownerId)
          ->delete();
          alpn_log("Deleted user... " . $ownerId);
      } catch (Exception $e) {
          $response = array(
              'message' =>  $e->getMessage(),
              'code' => $e->getCode(),
              'error' => $e
          );
          alpn_log($response);
      }
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

function pte_make_string($theItems, $theFields, $theMap){

	//Make local Dates.

  // alpn_log('pte_make_string');
  // alpn_log($theItems);
  // alpn_log($theFields);
  // alpn_log($theMap);

	$theString = '';
	foreach ($theItems as $itemKey => $itemValue) {
		$key = $itemValue['type'];
		$value = $itemValue['value'];
		switch ($key){
			case "modified_date_pretty":
				$theString = strtotime("now");
				$theString = date("F j, Y, g:iA", $theString);
			break;
			case "modified_date":
				$theString = strtotime("now");
				$theString = substr("00000000" . $theString, -14);
			break;
			case "make_date":
				$date = $theFields[$theMap[$itemValue['date_field']]];
				$time = $theFields[$theMap[$itemValue['time_field']]];
				$theString = strtotime($date['date'] . " " . $time['time']);
				$theString = substr("00000000" . $theString, -14);
			break;
			case "field":
				if (array_key_exists ($value, $theMap)){
					$theField = $theFields[$theMap[$value]];
					if (is_array($theField)) {
						if (isset($theField['date'])) {
							$pDate = strtotime($theField['date']);
							$theString .= date('F j, Y', $pDate);
						} else if (isset($theField['time'])){
							$pTime = strtotime($theField['time']);
							$theString .= date('g:iA', $pTime);
						}
					} else {
						$theString .= $theFields[$theMap[$value]];
					}
				}
			break;
      case "field_date":
        if (array_key_exists ($value, $theMap)){
          $theDate = $theFields[$theMap[$value]];
          $theString .= date("F j, Y, g:i a", $theDate);


          alpn_log('Testing The Field');
          alpn_log($theDate);
          alpn_log($theString);

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

function pte_create_panel($value){

  alpn_log("Creating Panel");

  $value = (is_array($value)) ? (object) $value : $value;

  $topicNetworkId = $value->id;
  $topicDomIdProTeam = $value->dom_id;
  $topicNetworkName = $value->name;
  $topicAccessLevel = $value->access_level;
  $connectedContactStatus = 'not_connected_not_member';
  if ($value->connected_id) {
    $connectedContactStatus = 'connected_member';
  } else if ($value->alt_id) {
     $userData = get_user_by('email', $value->alt_id);
     if (isset($userData->data->ID) && $userData->data->ID) {
       $connectedContactStatus = 'not_connected_member';
     }
  }
  $topicNetworkRights = json_decode($value->member_rights, true);
  $checked = array();
  foreach ($topicNetworkRights as $key2 => $value2) {
    $checked[$key2] = $value2;
  }
  $topicPanelData = array(
    'proTeamRowId' => $value->id,
    'topicNetworkId' => $value->proteam_member_id,
    'topicDomId' => $topicDomIdProTeam,
    'topicNetworkName' => $value->name,
    'topicAccessLevel' => $topicAccessLevel,
    'state' => $value->state,
    'checked' => $checked,
    'connected_contact_status' => $connectedContactStatus
  );
  return pte_make_rights_panel_view($topicPanelData);
}


function  pte_make_rights_panel_view($panelData) {

  // alpn_log("pte_make_rights_panel_view");
  // alpn_log($panelData);

	$topicStates = array('10' => "Added", '20' => "Invited", '30' => "Joined", '40' => "Linked", '80' => "Email Sent", '90' => "Declined");

	$proTeamRowId = $panelData['proTeamRowId'];
  $topicNetworkId = $panelData['topicNetworkId'];
	$topicDomId = $panelData['topicDomId'];
  $topicNetworkName = $panelData['topicNetworkName'];
	$connectedContactStatus = $panelData['connected_contact_status'];
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

  $connectedContactStatusIcon = "<i class='far fa-user-slash' title='Not a Member'></i>";
  if ($connectedContactStatus == 'not_connected_member') {
    $connectedContactStatusIcon = "<i class='far fa-user' title='Member, Not Connected'></i>";
  } else if ($connectedContactStatus == 'connected_member') {
    $connectedContactStatusIcon = "<i class='far fa-user-check' title='Member, Connected'></i>";
  }

  $permissions = "
    <select id='alpn_select2_small_{$proTeamRowId}' class='alpn_select2_small' data-ptrid='{$proTeamRowId}'>
      <option value='10' {$generalChecked}>General</option>
      <option value='30' {$restrictedChecked}>Restricted</option>
    </select>
  ";

	//TODO Loop array.
	$download = (isset($checked['download']) && $checked['download']) ? "<div id='proteam_download' data-item='download' pte-state='set' data-ptid='{$proTeamRowId}' class='proteam_rights_check' onclick='alpn_rights_check(this);'><div class='pte_panel_check'><i class='fa fa-check' style='font-size: 0.9em; color: #4499d7;'></i></div>Download</div>" : "<div id='proteam_download' data-item='download' pte-state='' data-ptid='{$proTeamRowId}' class='proteam_rights_check' onclick='alpn_rights_check(this);'><div class='pte_panel_check'></div>Copy/Download</div>";

  //$share = (isset($checked['share']) && $checked['share']) ? "<div id='proteam_share' data-item='share' pte-state='set' data-ptid='{$proTeamRowId}' class='proteam_rights_check' onclick='alpn_rights_check(this);'><div class='pte_panel_check'><i class='fa fa-check' style='font-size: 0.9em; color: #4499d7;'></i></div>Share</div>" : "<div id='proteam_share' data-item='share' pte-state='' data-ptid='{$proTeamRowId}' class='proteam_rights_check' onclick='alpn_rights_check(this);'><div class='pte_panel_check'></div>Share</div>";
  $share = ' ';

  $print = (isset($checked['print']) && $checked['print']) ? "<div id='proteam_print' data-item='print' pte-state='set' data-ptid='{$proTeamRowId}' class='proteam_rights_check' onclick='alpn_rights_check(this);'><div class='pte_panel_check'><i class='fa fa-check' style='font-size: 0.9em; color: #4499d7;'></i></div>Print</div>" : "<div id='proteam_print' data-item='print' pte-state='' data-ptid='{$proTeamRowId}' class='proteam_rights_check' onclick='alpn_rights_check(this);'><div class='pte_panel_check'></div>Print</div>";

  //if ($connectedContactStatus == 'not_connected_not_member') {
  if (false) {
    $html = "
  		<div id='pte_proteam_item_{$proTeamRowId}' class='proteam_user_panel' data-name='{$topicNetworkName}' data-id='{$proTeamRowId}'>
        <div class='proTeamPanelUserOuter'>
          <div id='proTeamPanelUser' data-network-id='{$topicNetworkId}' data-network-dom-id='{$topicDomId}' data-operation='network_info' class='proTeamPanelUser' onclick='pte_handle_interaction_link_object(this);'>{$topicNetworkName}</div>
  				<div id='proTeamPanelUserData' class='proTeamPanelUserData'><span id='pte_topic_state'>{$topicStates[$topicState]}</span> &nbsp;|&nbsp; {$connectedContactStatusIcon}</div>
          <div style='font-weight: normal; color: rgb(0, 116, 187); cursor: pointer; font-size: 11px; line-height: 16px;' onclick='alpn_proteam_member_delete({$proTeamRowId});'>Remove</div>
  			</div>
  			<div class='proTeamPanelSettings'>
        External
  			</div>
  		</div>
  		";

  } else {
    $html = "
      <div id='pte_proteam_item_{$proTeamRowId}' class='proteam_user_panel' data-name='{$topicNetworkName}' data-id='{$proTeamRowId}'>
        <div class='proTeamPanelUserOuter'>
          <div id='proTeamPanelUser' data-network-id='{$topicNetworkId}' data-network-dom-id='{$topicDomId}' data-operation='network_info' class='proTeamPanelUser' onclick='pte_handle_interaction_link_object(this);'>{$topicNetworkName}</div>
          <div id='proTeamPanelUserData' class='proTeamPanelUserData'><span id='pte_topic_state'>{$topicStates[$topicState]}</span> &nbsp;|&nbsp; {$connectedContactStatusIcon}</div>
          <div style='font-weight: normal; color: rgb(0, 116, 187); cursor: pointer; font-size: 11px; line-height: 16px;' onclick='alpn_proteam_member_delete({$proTeamRowId});'>Remove</div>
        </div>
        <div class='proTeamPanelSettings'>
          <div id='pte_proteam_controls' class='pte_proteam_controls' data-id='{$topicNetworkId}'>
            <table class='pte_proteam_rights_table' data-pte-proteam-id='{$proTeamRowId}'>
              <tr class='pte_proteam_row'>
                <td class='pte_proteam_cell_left'>
                  <div style='display: inline-block; vertical-align: middle; margin-left: 0px; margin-right: 5px; margin-bottom: 3px; font-weight: bold;'>Access:</div><div style='display: inline-block; vertical-align: middle; margin-bottom: 3px; height: 16px;'>{$permissions}</div>
                  <div class='pte_proteam_row_rights'>
                    <div class='pte_proteam_cell_rights_left'>{$print}</div><div class='pte_proteam_cell_rights_right'>$share</div>
                  </div>
                  <div class='pte_proteam_row_rights'>
                    <div class='pte_proteam_cell_rights_left'>{$download}</div><div class='pte_proteam_cell_rights_right'></div>
                  </div>
                </td>
              </tr>
            </table>
          </div>
        </div>
      </div>
      ";
  }
	return $html;
}



?>
