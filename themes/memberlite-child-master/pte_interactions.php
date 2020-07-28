<?php

require 'vendor/autoload.php';
include('/var/www/html/proteamedge/public/wp-blog-header.php');

//include all interaction processes
include( __dir__ . "/interaction_registry/proteam_invitation.php" );
include( __dir__ . "/interaction_registry/proteam_invitation_received.php" );

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

$qVars = unserialize($argv[1]);
$data = isset($qVars['data']) ? $qVars['data'] : array();
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

function pte_save_process(Process $process, $uxMeta)
{
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

  if ($getNetworkTopicId) { // Important -- If needed, find way back to user's network contact
    $results = $wpdb->get_results(
      $wpdb->prepare("SELECT t.id FROM alpn_topics t JOIN alpn_topics t1 ON t.owner_id = t1.owner_id WHERE t1.id = %s AND t.connected_network_id = %s", $ownerNetworkId, $interactionNetworkId)
     );
     if (isset($results[0])) {
       $interactionNetworkId = $results[0]->id;
     }
  }

  $results = $wpdb->get_results(
    $wpdb->prepare("SELECT t.name, t.image_handle, t.alt_id, t.connected_id, t.connected_network_id, u.id AS important_id FROM alpn_topics t LEFT JOIN alpn_user_lists u ON u.item_id = t.id AND u.list_key = 'pte_important_network' AND u.owner_network_id = %s WHERE t.id = %s", $ownerNetworkId, $interactionNetworkId)
   );
   if (isset($results[0])) {
     $networkData = $results[0];
     $results = $wpdb->get_results(
      $wpdb->prepare("SELECT t.name, t.image_handle, t.connected_id, u.id AS important_id FROM alpn_topics t LEFT JOIN alpn_user_lists u ON u.item_id = t.id AND u.list_key = 'pte_important_topic' AND u.owner_network_id = %s WHERE t.id = %s", $ownerNetworkId, $topicId)
      );
      if (isset($results[0])) {
        $topicData = $results[0];
        $now = date ("Y-m-d H:i:s", time());
        $processContext = array(
          "created_date" =>   $now,
          'owner_network_id' => $ownerNetworkId,
          'owner_id' => $ownerId,
          'network_id' => $interactionNetworkId,
          'connected_id' => $networkData->connected_id,
          'connected_network_id' => $networkData->connected_network_id,
          'alt_id' => $networkData->alt_id,
          'network_name' => $networkData->name,
          'network_icon' => $networkData->image_handle,
          'topic_id' => $topicId,
          'topic_name' => $topicData->name,
          'topic_icon' => $topicData->image_handle,
          'process_id' => $processId,
          'process_type_id' => $processTypeId,
          'network_important' =>  $networkData->important_id,
          'topic_important' =>  $topicData->important_id
        );
      }

      return $processContext;
    }
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

  switch ($processTypeId) {  //TODO generalize to make widgets and interactions more extensible fully extensible.
		case 'proteam_invitation':
      $process = $process ? $process : pte_setup_proteam_invitation_process();
      $registryArray = pte_get_proteam_invitation_registry();
		break;
    case 'proteam_invitation_received':
			$process = $process ? $process : pte_setup_proteam_invitation_received_process();
      $registryArray = pte_get_proteam_invitation_received_registry();
      $extraContext = $processData;
	  break;
  }

  $processId = $process->getId();
  $processContext = $processContext ? $processContext : array_merge(pte_get_process_context($processData), $extraContext);
  $processContext['process_id'] = $processId;

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
    }
    $token->setValue("process_context", $processContext);  //store context with new data with each token
    try {
      $engine->proceed($token);
    } catch (\Throwable $e) {
      //TODO Check for waitException fallthrought versus others for hardening
      $exMsg = $e->getMessage();   //TODO why is this getting error? Non-blocking but I want to throw and catch typed exceptions... But,when I add Exception on the include, it fails.
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

    return $processId;
}


?>
