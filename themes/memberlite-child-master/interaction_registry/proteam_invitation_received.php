<?php
//Interaction Function Registry and Process Tree for Sending a Proteam Invitation

use Formapro\Pvm\Token;
use Formapro\Pvm\ProcessBuilder;
use Formapro\Pvm\Exception\WaitExecutionException;


function pte_setup_proteam_invitation_received_process() {

    $process = (new ProcessBuilder())
        ->createNode('request_sent', 'request_sent')->end()
        ->createNode('handle_request_response', 'handle_request_response')->end()
        ->createNode('handle_complete', 'handle_complete')->end()
        ->createTransition('request_sent', 'handle_request_response')->end()
        ->createTransition('handle_request_response', 'handle_complete')->end()
        ->createStartTransition('request_sent')->end()
        ->getProcess();

    return $process;

}

function pte_get_proteam_invitation_received_registry() {

  $registryArray = array(
      'request_sent' => function(Token $token) {  //Node 1 - waiting for send

          $requestData = $token->getValue("process_context");
          $requestData['interaction_type_name'] = "ProTeam Invite Received";
          $requestData['to_from'] = 'From';

          $buttonOperation = $token->getValue("button_operation");
          if ($buttonOperation == 'accept' || $buttonOperation == 'decline') {

            $updateRequestData = array( //The ol swaparoo
              'process_id' => $requestData['interacts_with_id'],
              'process_type_id' => "proteam_invitation",
              'interaction_network_id' => $requestData['owner_network_id'],
              'button_operation' =>  $token->getValue("button_operation"),
              'message_response' =>  $token->getValue("message_response")
            );

            $data = array(
              'process_id' => $requestData['interacts_with_id'],
              'process_type_id' => "proteam_invitation",
              'owner_network_id' => $requestData['connected_network_id'],
              'owner_id' => $requestData['connected_id'],
              'process_data' => $updateRequestData
            );
            pte_manage_interaction($data);
            //Persist new data here too
            $requestData['button_operation'] = $buttonOperation;
            $requestData['message_response'] = $token->getValue("message_response");
            $token->setValue("process_context", $requestData);

            return; //if successful
          }

          $templateName = $requestData['template_name'];
          $requestData['widget_type_id'] = "information";
          $requestData['information_title'] = "Invitation Received";
          $requestData['buttons'] =  array(
              "file" => true,
              "recall" => false
            );
            $requestData['data_lines'] =  array(
                "to_from_line",
                "regarding_line",
                "type_line"
              );

              $requestData['message_lines'] =  array(
                  "message_view_only",
                  "accept_decline_response"
                );

            $requestData['content_lines'] =  array(
                "network_chat",
                "network_audio",
                "network_vault"
              );


           $requestData['sync'] = true;
          $token->setValue("process_context", $requestData);
          $requestData['requires_user_attention'] = true;
          throw new WaitExecutionException();  //proper way to fail -- still having trouble in pte_interactions catching this exception type


        //TODO check for data in $requestData to indicate that message has been sent. Need to handle cancel and some others too. Should be common for common stuff like delete, archive, Rerport, forward, remind...
      },
      'handle_request_response' => function(Token $token) {

        $requestData = $token->getValue("process_context");
        $interactionComplete = $token->getValue("interaction_complete");

        if ($interactionComplete) {
          return; //if successful
        }

        //Common
        $requestData['widget_type_id'] = "information";
        $requestData['information_title'] = "Waiting for Confirmation...";

        $requestData['buttons'] =  array(
          "file" => true,
          "recall" => false
          );

        $requestData['message_lines'] =  array();

        $requestData['content_lines'] =  array(
            "network_chat",
            "network_audio",
            "network_vault",
            "topic_vault"
          );
          $requestData['sync'] = true;
        $token->setValue("process_context", $requestData);
        $requestData['requires_user_attention'] = false;
        throw new WaitExecutionException();
      },
      'handle_complete' => function(Token $token) {

          alpn_log("Handling Complete (Received)...");
          $requestData = $token->getValue("process_context");

          $requestData['interaction_complete'] = true;
          $requestData['widget_type_id'] = "information";
          $requestData['information_title'] = "Invitation |style_1b|Complete|style_1e|";
          $requestData['template_name'] = $token->getValue("template_name");
          $requestData['buttons'] =  array(
            "file" => true,
            "recall" => false
            );
            $requestData['data_lines'] =  array(
                "to_from_line",
                "regarding_line",
                "type_line"
              );
          $requestData['content_lines'] =  array(
              "network_chat",
              "network_audio",
              "network_vault",
              "topic_vault"
            );
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
