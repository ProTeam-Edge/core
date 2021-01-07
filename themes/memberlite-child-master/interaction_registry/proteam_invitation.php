<?php
//Interaction Function Registry and Process Tree for Sending a Proteam Invitation

use Formapro\Pvm\Token;
use Formapro\Pvm\ProcessBuilder;
use Formapro\Pvm\Exception\WaitExecutionException;

function pte_setup_proteam_invitation_process() {

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

function pte_get_proteam_invitation_registry() {

  $registryArray = array(
      'request_sent' => function(Token $token) {  //Node 1 - waiting for send

            $requestData = $token->getValue("process_context");
            $requestData['interaction_type_name'] = "Team Invite";
            $requestData['interaction_template_name'] = "";
            $requestData['interaction_type_status'] = "Send";
            $requestData['interaction_to_from_string'] = "To";
            $requestData['interaction_to_from_name'] = $requestData['network_name'];
            $requestData['interaction_regarding'] = $requestData['topic_name'];
            $requestData['interaction_vault_link'] = "";


            $requestData['to_from'] = 'To';

            if ($token->getValue("template_id")) {

              $newRequestData = array( //start a proteam_invitation_received process for network contact
                'template_id' => $requestData["template_id"],
                'template_name' => $requestData["template_name"],
                'message_title' => $requestData["message_title"],
                'message_body' => $requestData["message_body"],
                'expiration_minutes' => $requestData["expiration_minutes"],
                'interacts_with_id' => $requestData["process_id"],
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
              $token->setValue("process_context", $requestData);
              
            return; //if successful TODO: is this always successful?
            }
            $requestData['widget_type_id'] = "message_send";
            $requestData['information_title'] = "Send Invitation";
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
      'handle_request_response' => function(Token $token) {

          $requestData = $token->getValue("process_context");

          $requestData['interaction_template_name'] = $requestData["template_name"];
          $requestData['interaction_type_status'] = "Waiting...";
          $requestData['interaction_to_from_name'] = $requestData["network_name"];

          $buttonOperation = $token->getValue("button_operation");

          alpn_log('Handling Accept/Decline...');

          if ($buttonOperation == 'accept') {
            alpn_log('Handling Accept...');

            //TODO it is easier, and makes for fewer back and forths if we handle all party's db changes and system syncs at one time here. Originally, I wanted an interaction only to manipulate ones own stuff. But they are related on the same system so....


            return; //if successful


          } else if ($buttonOperation == 'decline') {

            alpn_log('Handling Decline...');

            return; //still successful, just not what you wanted to hear
          }
          $requestData['widget_type_id'] = "information";
          $requestData['information_title'] = "Waiting for Response...";
          $requestData['buttons'] =  array(
            "file" => true
            );
            $requestData['data_lines'] =  array(
                "to_from_line",
                "regarding_line",
                "type_line"
              );
          $requestData['content_lines'] =  array(
              "network_panel",
              "topic_panel"
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
          $requestData['interaction_type_status'] = "Complete";

          switch ($requestData['connection_link_type']) {

            case '0':   //join

            break;
            case '1':  //link

                alpn_log('About to add edit topic bi link');

                pte_manage_topic_link('add_edit_topic_bidirectional_link', $requestData);

            break;
            case '2':  //new topic from data


            break;

          }

          $data = array(
            'owner_wp_id' => $requestData['owner_id'],
            'topic_id' => $requestData['topic_id'],
            'user_id' => $requestData['connected_network_id']
          );
          pte_manage_cc_groups("add_member", $data);

          $requestData['interaction_complete'] = true;
          $requestData['widget_type_id'] = "information";
          $requestData['information_title'] = "Invitation |style_1b|Complete|style_1e|";
          $requestData['buttons'] =  array(
            "file" => true
            );
            $requestData['data_lines'] =  array(
                "to_from_line",
                "regarding_line",
                "type_line"
              );
          $requestData['content_lines'] =  array(
            "network_panel",
            "topic_panel"
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
