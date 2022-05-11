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
            $requestData['interaction_type_name'] = "NFT";
            $requestData['interaction_template_name'] = "";
            $requestData['interaction_type_status'] = "Twitter Actions";
            $requestData['interaction_to_from_string'] = "";
            $requestData['interaction_to_from_name'] = "";
            $requestData['interaction_regarding'] = "";
            $requestData['interaction_vault_link'] = "";
            $requestData['interaction_file_away_handling'] = "delete_interaction";

            $processAction =  $token->getValue("action_to_process");

            $processAction = false;  //TEST

            if ($processAction) {    // TODO Why is this not in requestdata by now?

              $token->setValue("process_context", $requestData);
              return;
            }
            $requestData['widget_type_id'] = "twitter_actions";
            //$requestData['information_title'] = "Send Email";
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

          alpn_log('HANDLING SENT');
          $requestData = $token->getValue("process_context");

          $requestData['interaction_to_from_name'] = $requestData["send_email_address_name"];
          $requestData['static_name'] = $requestData["send_email_address_name"];
          $requestData['interaction_type_status'] = "xLink Sent by Email";
          $requestData['interaction_complete'] = true;
          $requestData['interaction_file_away_handling'] = "archive_interaction";

          $requestData['widget_type_id'] = "information";
          $requestData['template_name'] = $token->getValue("template_name");
          $requestData['buttons'] =  array(
            "file" => true
            );
            $requestData['data_lines'] =  array(
                "to_from_line_static",
                "regarding_line"
              );
          $requestData['content_lines'] =  array(
            "vault_item",
            "url_panel"
            );

          if ($requestData['network_id']){
            $requestData['content_lines'][] = "network_panel";
          }
          if ($requestData['topic_id'] && $requestData['topic_special'] == 'user') {
            $requestData['content_lines'][] = "personal_panel";
          } else {
            $requestData['content_lines'][] = "topic_panel";
          }
          $requestData['message_lines'] =  array(
              "message_view_only"
            );
          $requestData['sync'] = true;
          $requestData['requires_user_attention'] = false;

          $token->setValue("process_context", $requestData);
          return true;
    }
  );

return $registryArray;

}


?>
