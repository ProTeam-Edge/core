<?php
//Interaction Function Registry and Process Tree for Sending a Proteam Invitation

use Formapro\Pvm\Token;
use Formapro\Pvm\ProcessBuilder;
use Formapro\Pvm\Exception\WaitExecutionException;

function pte_setup_interaction_receive_fax() {

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

function pte_get_registry_receive_fax() {

  $registryArray = array(
      'request_sent' => function(Token $token) {  //Node 1 - waiting for send

            $requestData = $token->getValue("process_context");
            $requestData['interaction_type_name'] = "ProTeam Invite";
            $requestData['to_from'] = 'To';

            if ($token->getValue("template_id")) {
              $newRequestData = array( //start a proteam_invitation_received process for network contact
                'template_id' => $token->getValue("template_id"),
                'template_name' => $token->getValue("template_name"),
                'message_title' => $token->getValue("message_title"),
                'message_body' => $token->getValue("message_body"),
                'expiration_minutes' => $token->getValue("expiration_minutes"),
                'interacts_with_id' => $token->getValue("process_id"),
                'process_id' => '',
                'process_type_id' => '',
                'interaction_network_id' => $requestData['owner_network_id'],
                'get_network_topic_id' => true,
                'topic_id' => $requestData['topic_id']
              );
              $data = array(
            		'process_id' => "",
            		'process_type_id' => "proteam_invitation_received",
                'owner_network_id' => $requestData['connected_network_id'],
            		'owner_id' => $requestData['connected_id'],
            		'process_data' => $newRequestData
            	);
            	$requestData['interacts_with_id'] = pte_manage_interaction_proper($data);  //start new interaction targeting $ownerId
              $requestData['template_id'] = $token->getValue("template_id");
              $requestData['template_name'] = $token->getValue("template_name");
              $requestData['message_title'] = $token->getValue("message_title");
              $requestData['message_body'] = $token->getValue("message_body");
              $requestData['expiration_minutes'] = $token->getValue("expiration_minutes");
              $token->setValue("process_context", $requestData);

            return; //if successful TODO: is this always successful?
            }
            $requestData['widget_type_id'] = "message_send";
            $requestData['information_title'] = "Send Invitation";
            $requestData['buttons'] =  array(
              "file" => true,
              "recall" => false
              );
            $requestData['template_type_id'] = 1;  //in db -- perhaps use a different key process_type_id
            $requestData['sync'] = true;
            $requestData['requires_user_attention'] = true;
            $token->setValue("process_context", $requestData);
            throw new WaitExecutionException();  //proper way to fail -- still having trouble in pte_interactions catching this exception type

        //TODO check for data in $requestData to indicate that message has been sent. Need to handle cancel and some others too. Should be common for common stuff like delete, archive, Rerport, forward, remind...
      },
      'handle_request_response' => function(Token $token) {

          $requestData = $token->getValue("process_context");
          $buttonOperation = $token->getValue("button_operation");
          if ($buttonOperation == 'accept' || $buttonOperation == 'decline') {
            $data = array(
              'owner_wp_id' => $requestData['owner_id'],
        			'topic_id' => $requestData['topic_id'],
        			'user_id' => $requestData['connected_network_id']
        		);
        		pte_manage_cc_groups("add_member", $data);
            $updateRequestData = array(
              'process_id' => $requestData['interacts_with_id'],
              'process_type_id' => "proteam_invitation_received",
              'interaction_network_id' => $requestData['owner_network_id'],
              'interaction_complete' =>  true
            );
            $data = array(
              'process_id' => $requestData['interacts_with_id'],
              'process_type_id' => "proteam_invitation_received",
              'owner_network_id' => $requestData['connected_network_id'],
              'owner_id' => $requestData['connected_id'],
              'process_data' => $updateRequestData
            );
            pte_manage_interaction($data);
            return; //if successful
          }
          $requestData['button_operation'] = $token->getValue("button_operation");
          $requestData['message_response'] = $token->getValue("message_response");
          $requestData['widget_type_id'] = "information";
          $requestData['information_title'] = "Waiting for Response...";
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
              "message_editable_update"
            );

          $requestData['sync'] = true;
          $requestData['requires_user_attention'] = false;
          $token->setValue("process_context", $requestData);
          throw new WaitExecutionException();

      },
      'handle_complete' => function(Token $token) {

          $requestData = $token->getValue("process_context");
          $requestData['interaction_complete'] = true;
          $requestData['button_operation'] = $token->getValue("button_operation");
          $requestData['message_response'] = $token->getValue("message_response");
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
