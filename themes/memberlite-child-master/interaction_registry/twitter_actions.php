<?php
//Interaction Function Registry and Process Tree for Sending a Proteam Invitation

use Formapro\Pvm\Token;
use Formapro\Pvm\ProcessBuilder;
use Formapro\Pvm\Exception\WaitExecutionException;

function pte_setup_interaction_twitter_actions() {

    $process = (new ProcessBuilder())
        ->createNode('send_twitter', 'send_twitter')->end()
        ->createNode('twitter_sent', 'twitter_sent')->end()

        ->createTransition('send_twitter', 'twitter_sent')->end()
        ->createStartTransition('send_twitter')->end()
        ->getProcess();
    return $process;

}

function pte_get_registry_twitter_actions() {

  $registryArray = array(
      'send_twitter' => function(Token $token) {
            alpn_log('Start Twitter Sending...');
            global $wpdb;
            $requestData = $token->getValue("process_context");
            $requestData['interaction_type_name'] = "Twitter Fun";
            $requestData['interaction_template_name'] = "";
            $requestData['interaction_type_status'] = "Select Options";
            $requestData['interaction_to_from_string'] = "";
            $requestData['interaction_to_from_name'] = "";
            $requestData['interaction_regarding'] = "";
            $requestData['interaction_vault_link'] = "";
            $requestData['interaction_file_away_handling'] = "delete_interaction";

            if ($requestData['twitter_finish_action']) {

              $token->setValue("process_context", $requestData);
              return;
            }
            $requestData['widget_type_id'] = "twitter_actions";
            $requestData['buttons'] =  array(
              "file" => true
              );
            $requestData['template_type_id'] = 1;  //in db -- perhaps use a different key process_type_id
            $requestData['sync'] = true;
            $requestData['requires_user_attention'] = true;
            $token->setValue("process_context", $requestData);
            throw new WaitExecutionException();  //proper way to fail -- still having trouble in pte_interactions catching this exception type

        //TODO check for data in $requestData to indicate that message has been sent. Need to handle cancel and some others too. Should be common for common stuff like delete, archive, Rerport, forward, remind...
      },
      'twitter_sent' => function(Token $token) {

          alpn_log('HANDLING Twitter SENT');
          $requestData = $token->getValue("process_context");

          $requestData['interaction_type_status'] = "Complete";
          $requestData['interaction_complete'] = true;
          $requestData['interaction_file_away_handling'] = "archive_interaction";
          $requestData['interaction_type_status'] = "Complete";

          $requestData['data_lines'] =  array(
            "twitter_results"
            );

          $requestData['widget_type_id'] = "information";
          $requestData['buttons'] =  array(
            "file" => true
            );

          $requestData['sync'] = true;
          $requestData['requires_user_attention'] = false;

          $contractData = array("process_id" => $requestData['process_id']);
          $data = array(
            "sync_type" => 'add_update_section',
            "sync_section" => 'twitter_action_done',
            "sync_user_id" => $requestData['owner_id'],
            "sync_payload" => $contractData
          );
          pte_manage_user_sync($data);

          $token->setValue("process_context", $requestData);
          return true;
    }
  );

return $registryArray;

}


?>
