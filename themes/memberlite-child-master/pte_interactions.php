<?php

require 'vendor/autoload.php';
include('/var/www/html/proteamedge/public/wp-blog-header.php');

use Formapro\Pvm\DefaultBehaviorRegistry;
use Formapro\Pvm\Exception\WaitExecutionException;
use Formapro\Pvm\Process;
use Formapro\Pvm\ProcessEngine;
use Formapro\Pvm\Token;
use Formapro\Pvm\ProcessBuilder;
use function Formapro\Values\get_values;
use function Formapro\Values\add_value;
use function Formapro\Values\get_object;
use function Formapro\Values\get_objects;
use function Formapro\Values\get_value;
use function Formapro\Values\set_object;
use function Formapro\Values\set_value;
use Formapro\Values\ValuesTrait;

//$qVars = unserialize($argv[1]);
$qVars = $_POST;
$data = isset($qVars['data']) ? json_decode(stripslashes($qVars['data']), true) : array();

//alpn_log('Interaction Async Starting Here...');
//alpn_log($qVars);
//alpn_log($data);

if ($data) {
  pte_manage_interaction_proper($data);
}


function pte_get_process_all(string $id): array
{
  global $wpdb;
  //Get topic information

    $results = $wpdb->get_results(
       $wpdb->prepare("SELECT json, ux_meta FROM alpn_interactions WHERE process_id = %s", $id)
     );
   if (isset($results[0])){
     $processData = $results[0];
     $processJson = json_decode($processData->json, true);

     $uxMeta = json_decode($processData->ux_meta, true);
     $process = Process::create($processJson);
     $pteProcessAll = array("process" => $process, "process_context" => $uxMeta);

     return $pteProcessAll;
   }
   return false;
}

function pte_save_process(Process $process, $uxMeta) {

    global $wpdb;

    $impNetworkTotal = IMP_NETWORK_TOTAL;
    $impTopicTotal = IMP_TOPIC_TOTAL;
    $impInteractionTypeTotal = IMP_INTERACTION_TYPE_TOTAL;
    $impRequiresAttentionTotal = IMP_REQUIRES_ATTENTION_TOTAL;
    $impRevisitTotal = IMP_REVISIT_TOTAL;

    $requiresAttentionStep = IMP_REQUIRES_ATTENTION_STEP;
    $requiresAttentionInfo = IMP_REQUIRES_ATTENTION_INFO;

    $networkValueVip = IMP_NETWORK_VIP;
    $networkValueGeneral = IMP_NETWORK_GENERAL;
    $topicValueVit = IMP_TOPIC_VIT;
    $topicValueGeneral = IMP_TOPIC_GENERAL;

    $easingTotal = IMP_EASING_TOTAL;

    $ownerNetworkId =	isset($uxMeta['owner_network_id']) ? $uxMeta['owner_network_id'] : 0;
    $altId =	isset($uxMeta['alt_id']) && !$ownerNetworkId ? $uxMeta['alt_id'] : '';
    $interactsWithId =	isset($uxMeta['interacts_with_id']) ? $uxMeta['interacts_with_id'] : '';
    $expirationMinutes =	isset($uxMeta['expiration_minutes']) ? $uxMeta['expiration_minutes'] : 0;

    $stepRequiresUserAttention = 	isset($uxMeta['requires_user_attention']) ? $uxMeta['requires_user_attention'] : false;
    $stepRequiresUserNotification = 	isset($uxMeta['requires_user_notification']) ? $uxMeta['requires_user_notification'] : false;

    $networkId  =	isset($uxMeta['network_id']) ? $uxMeta['network_id'] : 0;
    $topicId  =	isset($uxMeta['topic_id']) ? $uxMeta['topic_id'] : 0;

    $networkImportant  =	isset($uxMeta['network_important']) ? $uxMeta['network_important'] : false;
    $topicImportant  =	isset($uxMeta['topic_important']) ? $uxMeta['topic_important'] : false;

    if ($ownerNetworkId || $altId) {

      $processId = $process->getId();
      $processValues = json_encode(get_values($process));
      $now = date ("Y-m-d H:i:s", time());

      $requiresAttentionValue = 0;
      if ($stepRequiresUserAttention || $stepRequiresUserNotification) {
        $requiresAttentionValue = $requiresAttentionStep; //TODO Doesn't do this right. Need to fix it. Test for attention then address notification.
      }

      $networkValue = 0;
      if ($networkId) {
        if ($networkImportant) {
          $networkValue = $networkValueVip;
        } else {
          $networkValue = $networkValueGeneral;
        }
      }

      $topicValue = 0;
      if ($topicId) {
        if ($topicImportant) { //TODO IS in VIP list
          $topicValue = $topicValueVit;
        } else {
          $topicValue = $topicValueGeneral;
        }
      }
      $processData = array(
        'owner_network_id' => $ownerNetworkId,
        'imp_network_total' => $impNetworkTotal,
        'imp_topic_total' => $impTopicTotal,
        'imp_interaction_type_total' => $impInteractionTypeTotal,
        'imp_requires_user_attention_total' => $impRequiresAttentionTotal,
        'imp_requires_user_attention_value' => $requiresAttentionValue,
        'imp_time_urgency_total' => $impRevisitTotal,
        'imp_network_id' => $networkId,
        'imp_network_value' => $networkValue,
        'imp_topic_id' => $topicId,
        'imp_topic_value' => $topicValue,
        'imp_easing_total' => $easingTotal,
        'expiration_minutes' => $expirationMinutes,
        'interacts_with_id' => $interactsWithId,
        'alt_id' => $altId,
        'process_id' => $processId,
        'ux_meta' => json_encode($uxMeta),
        'json' => $processValues,
        'modified_date' => $now
      );
      $wpdb->replace( 'alpn_interactions', $processData );
  } else {

  }
}


function pte_get_process_context($processData) { //TODO make this work for all interaction types when not all of this data is needed.

  global $wpdb;
  $processContext = array();

  $ownerNetworkId = isset($processData['owner_network_id']) ? $processData['owner_network_id'] : 0;
  $ownerId = isset($processData['owner_id']) ? $processData['owner_id'] : 0;

  $interactionNetworkId = isset($processData['interaction_network_id']) ? $processData['interaction_network_id'] : 0;
  $getNetworkTopicId = isset($processData['get_network_topic_id']) ? $processData['get_network_topic_id'] : false;

  $topicId = isset($processData['topic_id']) ? $processData['topic_id'] : 0;
  $processId = isset($processData['process_id']) ? $processData['process_id'] : 0;
  $processTypeId = isset($processData['process_type_id']) ? $processData['process_type_id'] : 0;

  $vaultId = isset($processData['vault_id']) ? $processData['vault_id'] : 0;

  $vaultPdfKey = $vaultFileKey = '';
  if ($vaultId) {
    $results = $wpdb->get_results(
      $wpdb->prepare("SELECT topic_id, pdf_key, file_key, dom_id, file_name FROM alpn_vault WHERE id = %s", $vaultId)
     );
     if (isset($results[0])) {
       $vaultPdfKey = $results[0]->pdf_key;
       $vaultFileKey = $results[0]->file_key;
       $vaultTopicId = $results[0]->topic_id;
       $vaultDomId = $results[0]->dom_id;
       $vaultFileName = $results[0]->file_name;
     }
  }

  if ($getNetworkTopicId) { // Important -- If needed, find way back to user's network contact
    $results = $wpdb->get_results(
      $wpdb->prepare("SELECT t.id FROM alpn_topics t JOIN alpn_topics t1 ON t.owner_id = t1.owner_id WHERE t1.id = %s AND t.connected_network_id = %s", $ownerNetworkId, $interactionNetworkId)
     );
     if (isset($results[0])) {
       $interactionNetworkId = $results[0]->id;
     }
  }

  $networkData = (object)array();   //contact
  if ($interactionNetworkId) {
    $results = $wpdb->get_results(
      $wpdb->prepare("SELECT t.name, t.image_handle, t.dom_id, t.alt_id, t.connected_id, t.connected_network_id, u.id AS important_id FROM alpn_topics t LEFT JOIN alpn_user_lists u ON u.item_id = t.id AND u.list_key = 'pte_important_network' AND u.owner_network_id = %s WHERE t.id = %s", $ownerNetworkId, $interactionNetworkId)
     );
     $networkData = isset($results[0]) ? $results[0] : (object)array();
   }

   $topicData = (object)array();
   if ($topicId) {
       $results = $wpdb->get_results(
        $wpdb->prepare("SELECT t.special, t.name, t.image_handle, t.connected_id, t.topic_type_id, t.dom_id, t.id AS topic_id, u.id AS important_id FROM alpn_topics t LEFT JOIN alpn_user_lists u ON u.item_id = t.id AND u.list_key = 'pte_important_topic' AND u.owner_network_id = %s WHERE t.id = %s or t.dom_id = %s", $ownerNetworkId, $topicId, $topicId)
        );
        $topicData = isset($results[0]) ? $results[0] : (object)array();
    }

    $userData = (object)array();
    $friendlyName = 'Member';
    if ($ownerNetworkId) {
        $results = $wpdb->get_results(
         $wpdb->prepare("SELECT topic_content from alpn_topics WHERE id = %s", $ownerNetworkId)
         );
         $userData = isset($results[0]) ? $results[0] : (object)array();
         $topicContent = json_decode($userData->topic_content, true);
         $friendlyName = $topicContent['person_givenname'] . " " . $topicContent['person_familyname'];
     }

    $now = date ("Y-m-d H:i:s", time());
    $processContext = array(
      'vault_id' => $vaultId,
      'vault_dom_id' => $vaultDomId,
      'vault_topic_id' => $vaultTopicId,
      'vault_pdf_key' => $vaultPdfKey,
      'vault_file_key' => $vaultFileKey,
      'vault_file_name' => $vaultFileName,
      "created_date" =>   $now,
      'owner_network_id' => $ownerNetworkId,
      'owner_id' => $ownerId,
      'owner_friendly_name' => $friendlyName,
      'network_id' => $interactionNetworkId,
      'connected_id' => isset($networkData->connected_id) ? $networkData->connected_id : 0,
      'connected_network_id' => isset($networkData->connected_network_id) ? $networkData->connected_network_id : 0,
      'connected_network_dom_id' => isset($networkData->dom_id) ? $networkData->dom_id : '',
      'alt_id' => isset($networkData->alt_id) ? $networkData->alt_id : '',
      'network_name' => isset($networkData->name) ? $networkData->name : '',
      'network_icon' => isset($networkData->image_handle) ? $networkData->image_handle : '',
      'topic_id' => isset($topicData->topic_id) ? $topicData->topic_id : 0,
      'topic_dom_id' => isset($topicData->dom_id) ? $topicData->dom_id : '',
      'topic_type_id' => isset($topicData->topic_type_id) ? $topicData->topic_type_id : 0,
      'topic_special' => isset($topicData->special) ? $topicData->special : 'topic',
      'topic_name' => isset($topicData->name) ? $topicData->name : '',
      'topic_icon' => isset($topicData->image_handle) ? $topicData->image_handle : '',
      'process_id' => $processId,
      'process_type_id' => $processTypeId,
      'network_important' => isset($networkData->important_id) ? $networkData->important_id : 0,
      'topic_important' => isset($topicData->important_id) ? $topicData->important_id : 0,
    );

  return $processContext;

}

function pte_manage_interaction_proper($data) {

  alpn_log("Starting Manager interaction...");
  alpn_log($data);

  $process = "";
  $processContext = $extraContext = array();
  $token = '';
  $firstTimeThrough = false;

  $processId = isset($data['process_id']) ? $data['process_id'] : '';
  $processData = isset($data['process_data']) ? $data['process_data'] : array();
  $processTypeId = isset($data['process_type_id']) ? $data['process_type_id'] : '';

  $ownerNetworkId = isset($data['owner_network_id']) ? $data['owner_network_id'] : 0;
  $ownerId = isset($data['owner_id']) ? $data['owner_id'] : 0;

  $processData['process_type_id'] = $processTypeId;
  $processData['owner_network_id'] = $ownerNetworkId;
  $processData['owner_id'] = $ownerId;

  if ($processId) {
    $processArray = pte_get_process_all($processId);
    $process = $processArray['process'];
    $processContext = $processArray['process_context'];
  }

  switch ($processTypeId) {  //TODO generalize to make widgets and interactions more extensible fully extensible.  ALSO TODO by moving includes here, can I overload function names? Gonna try next time.
		case 'proteam_invitation':
      include_once( __dir__ . "/interaction_registry/proteam_invitation.php" );
      $process = $process ? $process : pte_setup_proteam_invitation_process();
      $registryArray = pte_get_proteam_invitation_registry();
		break;
    case 'proteam_invitation_received':
      include( __dir__ . "/interaction_registry/proteam_invitation_received.php" );
			$process = $process ? $process : pte_setup_proteam_invitation_received_process();
      $registryArray = pte_get_proteam_invitation_received_registry();
      $extraContext = $processData;
	  break;
    case 'file_received':
      include_once( __dir__ . "/interaction_registry/file_received.php" );
			$process = $process ? $process : pte_setup_file_received_process();
      $registryArray = pte_get_file_received_registry();
      break;
    case 'fax_received':
      include_once( __dir__ . "/interaction_registry/fax_received.php" );
			$process = $process ? $process : pte_setup_fax_received_process();
      $registryArray = pte_get_fax_received_registry();
      break;
    case 'fax_send':
      include_once( __dir__ . "/interaction_registry/fax_send.php" );
			$process = $process ? $process : pte_setup_fax_send_process();
      $registryArray = pte_get_fax_send_registry();
	  break;
    case 'email_send':
      include_once( __dir__ . "/interaction_registry/email_send.php" );
			$process = $process ? $process : pte_setup_email_send_process();
      $registryArray = pte_get_email_send_registry();
	  break;
    case 'sms_send':
      include_once( __dir__ . "/interaction_registry/sms_send.php" );
			$process = $process ? $process : pte_setup_sms_send_process();
      $registryArray = pte_get_sms_send_registry();
	  break;
  }
  $processContext = $processContext ? $processContext : array_merge(pte_get_process_context($processData), $extraContext);

  if ($process) {
    $registry = new DefaultBehaviorRegistry($registryArray);
    $engine = new ProcessEngine($registry);
    $tokens = $engine->getProcessTokens($process);
    if ($tokens) {
      foreach ($tokens as $key => $value) {  //TODO switch to array first something...
        $token = $value;
        break;
      }
    }
    if (!$token) {
      $token = $engine->createTokenFor($process->getStartTransition());
    }
    foreach ($processData as $key => $value) { //store the data
      $token->setValue($key, $value);
      $processContext[$key] = $value;
    }
    $processId = $process->getId();
    $processContext['process_id'] = $processId;

    //update contact status evertime interaction is run. TODO What happens when this changes mid interaction?
    $processContext['connected_contact_status'] = 'not_connected_not_member';
    if (!$processContext['connected_id']) { //If not connected, see if member from email alt_id
      if ($processContext['alt_id']) {  //is actually a user based on email so let's engage that way-- create inviation received
        $connectedUserData = get_user_by('email', $processContext['alt_id']);
        if (isset($connectedUserData->data->ID) && $connectedUserData->data->ID) {
          $processContext['connected_contact_status'] = 'not_connected_member';
          $processContext['connected_contact_id_alt'] = $connectedUserData->data->ID;
          $processContext['connected_contact_email_alt'] = $processContext['alt_id'];
          $processContext['connected_contact_topic_id_alt'] = get_user_meta( $connectedUserData->data->ID, 'pte_user_network_id', true );
        }
      }
    } else {
      $processContext['connected_contact_status'] = 'connected_member';
    }

    $token->setValue("process_context", $processContext);  //store context with new data with each token
    try {
      $engine->proceed($token);
    } catch (\Throwable $e) {
      //TODO Check for waitException fallthrought versus others for hardening
      $exMsg = $e->getMessage();

      //alpn_log("Handling Thrown Exception in INTERACTIONS...{$processTypeId}");

    }
    $requestData = $token->getValue("process_context");
    $sync = isset($requestData['sync']) ? $requestData['sync'] : false;
    $requestData['modified_date'] = date ("Y-m-d H:i:s", time());
    if ($sync) {
      $data = array(
        "sync_type" => "add_update_section",
        "sync_section" => "interaction_update",
        "sync_user_id" => $ownerId,
        "sync_payload" => $requestData
      );
      pte_manage_user_sync($data);
      $requestData['sync'] =  false;
    }
    pte_save_process($process, $requestData);

  } else { //TODO Handle No process

  }
    $returnData = array(
      'process_id' => $processId
    );
    return $returnData;
}


?>
