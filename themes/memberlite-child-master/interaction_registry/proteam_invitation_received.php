<?php
//Interaction Function Registry and Process Tree for Sending a Proteam Invitation

use Formapro\Pvm\Token;
use Formapro\Pvm\ProcessBuilder;
use Formapro\Pvm\Exception\WaitExecutionException;

function pte_setup_proteam_invitation_received_process() {

    $process = (new ProcessBuilder())
        ->createNode('request_sent', 'request_sent')->end()
        ->createNode('handle_complete', 'handle_complete')->end()
        ->createTransition('request_sent', 'handle_complete')->end()
        ->createStartTransition('request_sent')->end()
        ->getProcess();

    return $process;

}

function pte_get_proteam_invitation_received_registry() {

  $registryArray = array(
      'request_sent' => function(Token $token) {  //Node 1 - waiting for send

          $requestData = $token->getValue("process_context");

          $requestData['interaction_type_name'] = "Team Invite";
          $requestData['interaction_template_name'] = $requestData["template_name"];
          $requestData['interaction_type_status'] = "Received";
          $requestData['interaction_to_from_string'] = "From";
          $requestData['interaction_to_from_name'] = $requestData["network_name"];
          $requestData['interaction_regarding'] = $requestData['topic_name'];
          $requestData['interaction_vault_link'] = "";

          $requestData['to_from'] = 'From';

          $buttonOperation = $token->getValue("button_operation");
          $requestOperation = $token->getValue("request_operation");

          if ($requestOperation == 'recall_interaction') {
            alpn_log('Interaction Received Recall Interaction');


          }

          if ($buttonOperation == 'accept' || $buttonOperation == 'decline') {


            if ($buttonOperation == 'accept') {
              alpn_log('Interaction Received Handle Setting Up ProTeam Relationship.');


              //TODO do the contact side: setup topic, connections, etc. Or should the other side do it? It feels like it should be here.

              // Create New Topic, if needed.

              $formId = "aaa"; //From drop down list of acceptable Topic Types.
              $userId = "xyz"; // the user who owns this topic
              $entry = array(
                'id' => $formId,  //source user template type  Using custom TT
                'new_owner' => $userId,
                'fields' => array()  //required fields?
              );
              //alpn_handle_topic_add_edit ('', $entry, '', '' );	//Add user


              // Create ProTeam Entry for the Topic to my Connection with proper state and Link type (only)


              // Add ProTeam Card
              // Create topic Link.
              // Client Side Updates?


              }

              //TODO only do this stuff if all the setup worked for this guy above?

            $updateRequestData = array( //The ol swaparoo
              'process_id' => $requestData['interacts_with_id'],
              'process_type_id' => "proteam_invitation",
              'interaction_network_id' => $requestData['owner_network_id'],
              'connection_link_type' => $requestData['connection_link_type'],
              'connection_link_topic_id' => $requestData['connection_link_topic_id'],
              'button_operation' =>  $buttonOperation,
              'message_response' =>  $requestData["message_response"]
            );

            $data = array(  //call originating process with new data (accept/decline)
              'process_id' => $requestData['interacts_with_id'],
              'process_type_id' => "proteam_invitation",
              'owner_network_id' => $requestData['connected_network_id'],
              'owner_id' => $requestData['connected_id'],
              'process_data' => $updateRequestData
            );
            $interactsWithProcessResponse = pte_manage_interaction_proper($data);  //TODO WHEN this is ASYNC, drawing fails. What is being done here that needs to be syncronous?


            $token->setValue("process_context", $requestData);
            return; //if successful
          }

          $templateName = $requestData['template_name'];
          $requestData['widget_type_id'] = "proteam_invitation_received";
          $requestData['information_title'] = "Invitation Received";
          $requestData['buttons'] =  array(
              "file" => true
            );

          $requestData['sync'] = true;
          $requestData['requires_user_attention'] = true;
          $token->setValue("process_context", $requestData);
          throw new WaitExecutionException();  //proper way to stop the process and wait
      },
      'handle_complete' => function(Token $token) {

          alpn_log("Handling Complete (Received)...");
          $requestData = $token->getValue("process_context");
          $requestData['interaction_type_status'] = "Complete";

          $requestData['interaction_complete'] = true;
          $requestData['widget_type_id'] = "information";
          $requestData['information_title'] = "Invitation |style_1b|Complete|style_1e|";
          $requestData['template_name'] = $token->getValue("template_name");
          $requestData['buttons'] =  array(
            "file" => true
            );
            $requestData['data_lines'] =  array(
                "to_from_line",
                "regarding_line",
                "separator",
                "response_selected",
                "connect_type",
                "response_message"
              );
          $requestData['content_lines'] =  array(
            "network_panel"
            );

          if ($requestData['button_operation'] == 'accept') {
            $requestData['content_lines'][] = 'topic_panel';
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
