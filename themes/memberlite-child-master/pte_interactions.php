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
       $wpdb->prepare("SELECT json, ux_meta, imp_network_id, imp_topic_id, owner_network_id FROM alpn_interactions WHERE process_id = %s", $id)
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

    // alpn_log("SAVING PROCESS");
    // alpn_log($uxMeta);

    $ownerNetworkId =	isset($uxMeta['owner_network_id']) ? pte_digits($uxMeta['owner_network_id']) : 0;
    $altId =	isset($uxMeta['alt_id']) && !$ownerNetworkId ? $uxMeta['alt_id'] : '';
    $interactsWithId =	isset($uxMeta['interacts_with_id']) ? $uxMeta['interacts_with_id'] : '';
    $expirationMinutes =	isset($uxMeta['expiration_minutes']) ? pte_digits($uxMeta['expiration_minutes']) : 0;

    $networkId  =	isset($uxMeta['network_id']) ? pte_digits($uxMeta['network_id']) : 0;
    $topicId  =	isset($uxMeta['topic_id']) ? pte_digits($uxMeta['topic_id']) : 0;

    $networkImportant  =	isset($uxMeta['network_important']) ? $uxMeta['network_important'] : false;
    $topicImportant  =	isset($uxMeta['topic_important']) ? $uxMeta['topic_important'] : false;
    $stepRequiresUserAttention = 	isset($uxMeta['requires_user_attention']) ? $uxMeta['requires_user_attention'] : false;
    $interactionComplete = 	isset($uxMeta['interaction_complete']) ? $uxMeta['interaction_complete'] : false;

    if ($ownerNetworkId || $altId) {

      $processId = $process->getId();
      $processValues = json_encode(get_values($process));
      $now = date ("Y-m-d H:i:s", time());

      $processData = array(
        'owner_network_id' => $ownerNetworkId,
        'imp_network_id' => $networkId,
        'network_is_important' => $networkImportant,
        'topic_is_important' => $topicImportant,
        'interaction_complete' => $interactionComplete,
        'requires_user_attention' => $stepRequiresUserAttention,
        'imp_topic_id' => $topicId,
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


  alpn_log("Handling Process Context");

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
      $wpdb->prepare("SELECT topic_id, pdf_key, file_key, dom_id, file_name, description FROM alpn_vault WHERE id = %s", $vaultId)
     );
     if (isset($results[0])) {
       $vaultPdfKey = $results[0]->pdf_key;
       $vaultFileKey = $results[0]->file_key;
       $vaultTopicId = $results[0]->topic_id;
       $vaultDomId = $results[0]->dom_id;
       $vaultFileName = $results[0]->file_name;
       $vaultFileDescription = $results[0]->description;
     }
  }

  if ($getNetworkTopicId) { // TODO Important -- If needed, find way back to user's network contact. FIX THIS. Is it fixed with flippity below?

    $results = $wpdb->get_results(
      $wpdb->prepare("SELECT t.id FROM alpn_topics t JOIN alpn_topics t1 ON t.owner_id = t1.owner_id WHERE t1.id = %s AND t.connected_network_id = %s", $ownerNetworkId, $interactionNetworkId)
     );
     if (isset($results[0])) {
       $interactionNetworkId = $results[0]->id;
     }
  }

  $networkData = (object)array();   //OLD CONTACT
  if ($interactionNetworkId) {
    $results = $wpdb->get_results(
      $wpdb->prepare("SELECT t.name, t.image_handle, t.dom_id, t.alt_id, t.connected_id, t.connected_network_id, u.id AS important_id FROM alpn_topics t LEFT JOIN alpn_user_lists u ON u.item_id = t.id AND u.owner_network_id = %d AND u.list_key = 'pte_important_network' WHERE t.id = %d", $ownerNetworkId, $interactionNetworkId)
     );
     $networkData = isset($results[0]) ? $results[0] : (object)array();
   }

   $topicData = (object)array();
   if ($topicId) {
       $results = $wpdb->get_results(
        $wpdb->prepare("SELECT t.owner_id AS topic_owner_id, t.special, t.name, t.image_handle, t.connected_id, t.connected_topic_id, t.topic_type_id, t.dom_id, t.id AS topic_id, u.id AS important_id FROM alpn_topics t LEFT JOIN alpn_user_lists u ON u.item_id = t.id AND u.owner_network_id = %d AND (u.list_key = 'pte_important_topic' OR u.list_key = 'pte_important_network') WHERE t.id = %s OR t.dom_id = %s", $ownerNetworkId, $topicId, $topicId)
        );
        $topicData = isset($results[0]) ? $results[0] : (object)array();
    }

    if ($topicData && ($topicData->topic_owner_id != $ownerId)) {    //connected topic information also known as flippity dippity but only if needed
      $topicData = $results[0];
      $topicOwnerId = $topicData->owner_id;
      $connectedTopicId = $topicData->connected_topic_id;
      $connectedTopicData = (object)array();
      if ( ($topicOwnerId != $ownerId) && $connectedTopicId) {

        $results = $wpdb->get_results(
         $wpdb->prepare("SELECT t.owner_id AS topic_owner_id, t.special, t.name, t.image_handle, t.connected_id, t.connected_topic_id, t.topic_type_id, t.dom_id, t.id AS topic_id, u.id AS important_id FROM alpn_topics t LEFT JOIN alpn_user_lists u ON u.item_id = t.id AND u.owner_network_id = %d WHERE t.id = %s OR t.dom_id = %s", $ownerNetworkId, $connectedTopicId, $connectedTopicId)
         );
         $topicData = isset($results[0]) ? $results[0] : (object)array();
       }
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
      'vault_file_description' => $vaultFileDescription,
      "created_date" =>   $now,
      'owner_network_id' => $ownerNetworkId,
      'owner_id' => $ownerId,
      'owner_friendly_name' => $friendlyName,
      'network_id' => $interactionNetworkId,
      'network_name' => isset($networkData->name) ? $networkData->name : '',
      'network_icon' => isset($networkData->image_handle) ? $networkData->image_handle : '',
      'connected_id' => isset($networkData->connected_id) ? $networkData->connected_id : 0,
      'connected_network_id' => isset($networkData->connected_network_id) ? $networkData->connected_network_id : 0,
      'connected_network_dom_id' => isset($networkData->dom_id) ? $networkData->dom_id : '',
      'alt_id' => isset($networkData->alt_id) ? $networkData->alt_id : '',
      'topic_id' => isset($topicData->topic_id) ? $topicData->topic_id : 0,
      'topic_dom_id' => isset($topicData->dom_id) ? $topicData->dom_id : '',
      'topic_type_id' => isset($topicData->topic_type_id) ? $topicData->topic_type_id : 0,
      'topic_special' => isset($topicData->special) ? $topicData->special : 'topic',
      'topic_owner_id' => isset($topicData->topic_owner_id) ? $topicData->topic_owner_id : 0,
      'topic_name' => isset($topicData->name) ? $topicData->name : '',
      'topic_icon' => isset($topicData->image_handle) ? $topicData->image_handle : '',
      'process_id' => $processId,
      'process_type_id' => $processTypeId,
      'network_important' => isset($networkData->important_id) ? $networkData->important_id : 0,
      'topic_important' => isset($topicData->important_id) ? $topicData->important_id : 0,
    );

  return $processContext;

}

function pte_update_context_with_contact($contextData, $contactNetworkId, $emailContactData){

  $tTypeId =  $emailContactData->topic_type_id;
  $tTypeSpecial =  $emailContactData->special;
  $tConnectedId = $emailContactData->connected_id;
  $contextData['network_important'] = $emailContactData->network_important;

  $tContent = json_decode($emailContactData->topic_content, true);
  $ownerContent = json_decode($emailContactData->owner_topic_content, true);

  $contextData['target_topic_type_id'] = $tTypeId;
  $contextData['target_topic_special'] = $tTypeSpecial;

  if ($tConnectedId) { //if connected, use network contact data
    $tContent = json_decode($emailContactData->connected_topic_content, true);
  }

  $contextData['alt_id'] = $tContent['person_email'];
  $contextData['network_id'] = $contactNetworkId;
  $contextData['network_name'] = trim($tContent['person_familyname']  . ", " . $tContent['person_givenname']);
  $contextData['connected_network_dom_id'] = $emailContactData->email_contact_dom_id;
  $contextData['connected_contact_status'] = 'not_connected_not_member';

  //TODO Make Function
  if ($emailContactData->connected_id) {
    $contextData['connected_id'] = $emailContactData->connected_id;
    $contextData['connected_network_id'] = $emailContactData->connected_network_id;
    $contextData['connected_contact_status'] = 'connected_member';
  } else if ($contextData['alt_id']) {
    $connectedUserData = get_user_by('email', $contextData['alt_id']);
    if (isset($connectedUserData->data->connectedUserData) && $connectedUserData->data->ID) {
      $contextData['connected_contact_status'] = 'not_connected_member';
      $contextData['connected_contact_id_alt'] = $connectedUserData->data->ID;
      $contextData['connected_contact_email_alt'] = $contextData['alt_id'];
      $contextData['connected_contact_topic_id_alt'] = get_user_meta( $connectedUserData->data->ID, 'pte_user_network_id', true );
    }
  }

  return array(
              "context" => $contextData,
              "content" => $tContent
              );
}

function pte_manage_interaction_proper($data) {

  alpn_log("Starting Manager interaction...");
//  alpn_log($data);

  global $wpdb;

  $process = "";
  $processContext = $extraContext = array();
  $token = '';
  $firstTimeThrough = false;

  $processId = isset($data['process_id']) ? $data['process_id'] : '';
  $processData = isset($data['process_data']) ? $data['process_data'] : array();
  $processTypeId = isset($data['process_type_id']) ? $data['process_type_id'] : '';
  $hasExtraContent = isset($data['extra_content']) && $data['extra_content'] ? true : false;

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

  $interactionFileName = __dir__ . "/interaction_registry/{$processTypeId}.php";
  include_once($interactionFileName);
  $processSetupName = "pte_setup_interaction_" . $processTypeId;   //Function names need to be unique because mmultiple can be loaded. Classes!
  $processRegistryName = "pte_get_registry_" . $processTypeId;
  $process = $process ? $process : call_user_func($processSetupName);
  $registryArray = call_user_func($processRegistryName);
  if ($hasExtraContent) {
    $extraContext = $processData;
  }
  $processContext = $processContext ? $processContext : array_merge(pte_get_process_context($processData), $extraContext);

  if ($process) {
    //TODO MARK BUSY HERE
    $registry = new DefaultBehaviorRegistry($registryArray);
    $engine = new ProcessEngine($registry);
    $tokens = $engine->getProcessTokens($process);
    if ($tokens) {
      foreach ($tokens as $key => $value) {  //TODO switch to array first something... I can't find the right function
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
      if ($processContext['alt_id']) {  //is actually a user based on email so let's engage that way--
        $connectedUserData = get_user_by('email', $processContext['alt_id']);
        if (isset($connectedUserData->data->connectedUserData) && $connectedUserData->data->ID) {
          $processContext['connected_contact_status'] = 'not_connected_member';
          $processContext['connected_contact_id_alt'] = $connectedUserData->data->ID;
          $processContext['connected_contact_email_alt'] = $connectedUserData['alt_id'];
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
      $exMsg = $e->getMessage();
    }

    $requestData = $token->getValue("process_context");

    $recalled = isset($requestData['request_operation']) && $requestData['request_operation'] == 'recall_interaction' ? true : false;
    $restart = isset($requestData['restart_interaction']) && $requestData['restart_interaction'] ? true : false;

    if ($recalled) {  //RECALLED -- Handled on Recipients Side

      alpn_log('Deleting because recalled...' . $requestData['process_id']);

      $whereClause = array('process_id' => $requestData['process_id']);
      $wpdb->delete( 'alpn_interactions', $whereClause);

      //remove from ProTeam

      $whereClause = array(
        'process_id' => $requestData['interacts_with_id']
      );
      $wpdb->delete( 'alpn_proteams', $whereClause );

    } else {  //ALL else

      if ($restart) {

        alpn_log('Restarting Process...' . $requestData['process_id']);

        $newData = array(
          'process_id' => "",
          'process_type_id' => $requestData['process_type_id'],
          'owner_network_id' => $requestData['owner_network_id'],
          'owner_id' =>$requestData['owner_id'],
          'process_data' => array(
              'topic_id' => $requestData['topic_id'],
              'interaction_network_id' => $requestData['interaction_network_id'],
              'proteam_row_id' => $requestData['proteam_row_id']
          )
        );
        $newProcess = pte_manage_interaction_proper($newData);  //start new interaction targeting $ownerId
        $requestData['new_interaction_process_id'] = $newProcess['process_id'];

        $whereClause = array('process_id' => $requestData['process_id']);
        $wpdb->delete( 'alpn_interactions', $whereClause);


      } else { //KEEP GOING

        pte_save_process($process, $requestData);

      }

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
      }

    }

  } else { //TODO Handle No process

  }
    //TODO MARK UNBUSY HERE AND add any ERROR Codes here.

    return $requestData;  //all data about this process
}


?>
