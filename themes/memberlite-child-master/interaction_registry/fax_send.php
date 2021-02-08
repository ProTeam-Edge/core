<?php
//Interaction Function Registry and Process Tree for Sending a Proteam Invitation

use Formapro\Pvm\Token;
use Formapro\Pvm\ProcessBuilder;
use Formapro\Pvm\Exception\WaitExecutionException;

function pte_setup_interaction_fax_send() {

    $process = (new ProcessBuilder())
        ->createNode('send_fax', 'send_fax')->end()
        ->createNode('fax_decision', 'fax_decision')->end()
        ->createNode('fax_resend', 'handle_fax_resend')->end()
        ->createNode('fax_sent', 'handle_fax_sent')->end()

        ->createTransition('send_fax', 'fax_decision')->end()
        ->createTransition('fax_decision', 'fax_resend', 'resend')->end()
        ->createTransition('fax_decision', 'fax_sent', 'sent')->end()

        ->createStartTransition('send_fax')->end()
        ->getProcess();
    return $process;

}

function pte_get_registry_fax_send() {

  $registryArray = array(
      'send_fax' => function(Token $token) {
            $requestData = $token->getValue("process_context");
            $requestData['interaction_type_name'] = "Fax";
            $requestData['interaction_template_name'] = "";
            $requestData['interaction_type_status'] = "Send";
            $requestData['interaction_to_from_string'] = "To";
            $requestData['interaction_to_from_name'] = "";
            $requestData['interaction_regarding'] = $requestData['topic_name'];
            $requestData['interaction_vault_link'] = "";
            $requestData['interaction_file_away_handling'] = "delete_interaction";

            $requestData['view_link_file_type'] = "File";
            $requestData['to_from'] = 'To';

            if ($token->getValue("fax_field_fax_number")) {
              $requestData['fax_field_fax_number_plain'] =  preg_replace( '/[^0-9]/', '', $requestData['fax_field_fax_number'] );
              $requestData['fax_field_fax_number_plain'] = count($requestData['fax_field_fax_number_plain']) == 11 ?  $requestData['fax_field_fax_number_plain'] : '1' . $requestData['fax_field_fax_number_plain'];
              //Send fax
              $sendData = array(
                  'process_id' => $requestData['process_id'],
                  'network_contact_name' => $requestData['fax_field_first'] . " " . $requestData['fax_field_last'],
                  'owner_id' => $requestData['owner_id'],
                  'owner_network_id' => $requestData['owner_network_id'],
                  'topic_id' => $requestData['topic_id'],
                  'vault_id' => $requestData['vault_id'],
                  'vault_pdf_key' => $requestData['vault_pdf_key'],
                  'vault_file_key' => $requestData['vault_file_key'],
                  'company_name' => $requestData['fax_field_company'],
                  'pstn_number' => $requestData['fax_field_fax_number_plain'],
                  'template_name' => $requestData['template_name'],
                  'message_title' => $requestData['message_title'],
                  'message_body' => $requestData['message_body']
              );

              pte_documo_fax_send($sendData);

              $token->setValue("process_context", $requestData);
            return;
            }
            $requestData['widget_type_id'] = "fax_send";
            $requestData['information_title'] = "Send Fax";
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
      'fax_decision' => function(Token $token) {

          $requestData = $token->getValue("process_context");
          $requestData['interaction_file_away_handling'] = "archive_interaction";

          $requestData['interaction_type_status'] = "Sending...";
          $requestData['interaction_to_from_name'] = $requestData['fax_field_fax_number'];

          if ($requestData['documo_processing_status_name'] == 'success') {  //move to sent

            alpn_log('RETURNING SENT');

            return 'sent';

          }


          if (false) {  //move to resend

            return  'resend';

            }

          if (false) {//neither, update status


            }

          $requestData['widget_type_id'] = "information";
          $requestData['buttons'] =  array(
            "file" => true
            );
            $requestData['data_lines'] =  array(
                "to_recipient_number_line",
                "regarding_line",
                "type_line"
              );

          $requestData['content_lines'] =  array(
              "vault_item",
              "topic_panel"
            );

            if ($requestData['topic_special'] == 'user') {  //Personal
              $requestData['content_lines'] =  array(
                "vault_item",
                "personal_panel"
                );
              }

            if ($requestData['topic_special'] == 'network') {  //Network
              $requestData['content_lines'] =  array(
                "vault_item",
                "network_panel"
                );
              }

          $requestData['message_lines'] =  array(
              "message_view_only"
            );

          $requestData['sync'] = true;
          $requestData['requires_user_attention'] = false;
          $token->setValue("process_context", $requestData);
          throw new WaitExecutionException();

      },
      'handle_fax_resend' => function(Token $token) {

        alpn_log('HANDLING RESEND');

          $requestData = $token->getValue("process_context");
          $requestData['widget_type_id'] = "information";
          $requestData['information_title'] = "Wrong next step...";
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
      'handle_fax_sent' => function(Token $token) {

        alpn_log('HANDLING SENT');

          $requestData = $token->getValue("process_context");

          $requestData['interaction_type_status'] = "Sent";
          $requestData['interaction_complete'] = true;

          $requestData['sync'] = true;
          $requestData['requires_user_attention'] = false;
          $token->setValue("process_context", $requestData);
          return true;
    }
  );

return $registryArray;

}


?>
