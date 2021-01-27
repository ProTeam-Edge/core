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

          global $wpdb;

            $requestData = $token->getValue("process_context");
            $requestData['interaction_type_name'] = "Team Invite";
            $requestData['interaction_template_name'] = "";
            $requestData['interaction_type_status'] = "Send";
            $requestData['interaction_to_from_string'] = "To";
            $requestData['interaction_to_from_name'] = $requestData['network_name'];
            $requestData['interaction_regarding'] = $requestData['topic_name'];
            $requestData['interaction_vault_link'] = "";
            $requestData['to_from'] = 'To';

            if ($token->getValue("message_title")) { //As long as there is a title, we can send.

              //TODO mark an interaction as BUSY so that accidental secondary messages are ignored while processing primmary.

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
                'topic_id' => $requestData['topic_id'],
                'alt_id' => $requestData['alt_id']
              );

              if ($requestData['connected_id'] || $requestData['connected_contact_id_alt']) { //connected -- create inviation received

                $data = array(
              		'process_id' => "",
              		'process_type_id' => "proteam_invitation_received",
                  'owner_network_id' => $requestData['connected_network_id'] ? $requestData['connected_network_id'] : $requestData['connected_contact_topic_id_alt'],
              		'owner_id' => $requestData['connected_id'] ? $requestData['connected_id'] : $requestData['connected_contact_id_alt'],
              		'process_data' => $newRequestData
              	);
              	$interactsWithProcessResponse = pte_manage_interaction_proper($data);  //start new interaction targeting $ownerId
                $requestData['interacts_with_id'] = $interactsWithProcessResponse['process_id'];

                $data = array(
                  'connected_type' => 'none',
                  'state' => 20,
                  'proteam_row_id' => $requestData['proteam_row_id'],
                  'owner_id' => $requestData['owner_id']
                );
                pte_proteam_state_change_sync($data);

              } else {

                  $userInfo = get_user_by('id', $requestData['owner_id']);

                  if (isset($userInfo->data)) {
                    $data = array(
                      'from_name' => $requestData['owner_friendly_name'],
                      'from_email' => $userInfo->data->user_email,
                      'to_email' => $requestData['alt_id'] ,
                      'to_name' => $requestData['network_name'],
                      'link_type' => "",
                      'vault_file_name' => "",
                      'subject_text' => $requestData['message_title'],
                      'body_text' => nl2br($requestData['message_body']),
                      'link_id' => ""
                    );
                    alpn_log($data);
                    pte_send_mail ($data);

                    $data = array(
                      'connected_type' => 'external',
                      'state' => 80,
                      'proteam_row_id' => $requestData['proteam_row_id'],
                      'owner_id' => $requestData['owner_id']
                    );
                    pte_proteam_state_change_sync($data);

                  }
              }

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

          global $wpdb;

          $requestData = $token->getValue("process_context");

          $buttonOperation = $token->getValue("button_operation");

          switch ($buttonOperation) {
            case 'accept':
              alpn_log('Handling Accept...');
              //alpn_log($requestData);
              if ($requestData['connected_contact_status'] == 'not_connected_member') {
                // if member but not connected, connect them, handle as connected.
                alpn_log('Making connection to not_connected_member...');
                $connectData = array(
                  'contact_topic_id' => $requestData['connected_contact_topic_id_alt'],
                  'contact_email' => $requestData['connected_contact_email_alt'],
                  'owner_wp_id' => $requestData['owner_id']
                );
                pte_manage_user_connection($connectData);
                $requestData['connected_contact_status'] = 'connected_member';
                $requestData['connected_id'] = $requestData['connected_contact_id_alt'];
                $requestData['connected_network_id'] = $requestData['connected_contact_topic_id_alt'];
              }

              //add user to chat group if they are a member. For the Topic.
              if ($requestData['connected_contact_status'] == 'connected_member') {   //add them as long as they are members.
                $data = array(
                  'owner_wp_id' => $requestData['owner_id'],
                  'topic_id' => $requestData['topic_id'],
                  'user_id' => $requestData['connected_network_id']
                );
                pte_manage_cc_groups("add_member", $data);
              }

              //creates a link between this topic and the connected user topic if link type.
              $connectedType = "join";
              $ptState = 30;
              if ($requestData['connection_link_type'] == 1) {  //Link type
                pte_manage_topic_link('add_edit_topic_bidirectional_link', $requestData);
                $connectedType = "link";
                $ptState = 40;
              }
              //Update ProTeam with Join/Link type and Status.
              if (isset($requestData['proteam_row_id']) && $requestData['proteam_row_id']) {
                alpn_log('Handling ProTeam Update...');
                $data = array(
                  'connected_type' => $connectedType,
                  'state' => $ptState,
                  'proteam_row_id' => $requestData['proteam_row_id'],
                  'owner_id' => $requestData['owner_id']
                );
                pte_proteam_state_change_sync($data);
              }
              return;
            break;
            case 'decline':
              alpn_log('Handling Decline...');
              $data = array(
                'connected_type' => 'none',
                'state' => 90,  //declined
                'proteam_row_id' => $requestData['proteam_row_id'],
                'owner_id' => $requestData['owner_id']
              );
              pte_proteam_state_change_sync($data);
              return;
            break;
            case 'update':
              alpn_log('Handling Update...');

              //update title and message in interaction here.
              //Send a message to the other interaction. at $requestData['interacts_with_id']
              //Both sides show updated.


              $token->setValue("process_context", $requestData);
              throw new WaitExecutionException();
            break;
            case 'recall':
              alpn_log('Handling Recall...');

              $newRequestData = array(
                'request_operation' => "recall_interaction"
              );

              $data = array(
                'process_id' => $requestData['interacts_with_id'],
                'process_type_id' => "proteam_invitation_received",
                'owner_network_id' => $requestData['connected_network_id'] ? $requestData['connected_network_id'] : $requestData['connected_contact_topic_id_alt'],
                'owner_id' => $requestData['connected_id'] ? $requestData['connected_id'] : $requestData['connected_contact_id_alt'],
                'process_data' => $newRequestData
              );
              $interactsWithProcessResponse = pte_manage_interaction_proper($data);  //start new interaction targeting $ownerId

              //On this side, the interaction should be reset to before hitting send
              //How do we show feedback here and there?


              $token->setValue("process_context", $requestData);
              throw new WaitExecutionException();
            break;
          }

          $requestData['interaction_template_name'] = $requestData["template_name"];
          $requestData['interaction_type_status'] = "Waiting...";
          $requestData['interaction_to_from_name'] = $requestData["network_name"];

          $requestData['content_lines'] =  array(
              "network_panel",
              "topic_panel"
            );
          $requestData['message_lines'] =  array(
              "message_editable_update"
            );

          if ($requestData['connected_contact_status'] == "not_connected_not_member") {  //Non member
            $requestData['interaction_type_status'] = "Complete";
            $requestData['interaction_complete'] = true;
            $requestData['message_lines'] =  array(
                "message_view_only"
              );
          }


          //TODO Show button pressed and response message at Top

          $requestData['widget_type_id'] = "information";
          $requestData['buttons'] =  array(
              "file" => true
          );
          $requestData['data_lines'] =  array(
              "to_from_line",
              "regarding_line"
          );
          $requestData['content_lines'] =  array(
              "network_panel",
              "topic_panel"
          );
          $requestData['sync'] = true;
          $requestData['requires_user_attention'] = false;

          $token->setValue("process_context", $requestData);
          throw new WaitExecutionException();

      },
      'handle_complete' => function(Token $token) {

          $requestData = $token->getValue("process_context");
          $requestData['interaction_type_status'] = "Complete";

          $requestData['interaction_complete'] = true;
          $requestData['widget_type_id'] = "information";
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
