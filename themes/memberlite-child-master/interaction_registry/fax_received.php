<?php
//Interaction Function Registry and Process Tree for Sending a Proteam Invitation

use Formapro\Pvm\Token;
use Formapro\Pvm\ProcessBuilder;
use Formapro\Pvm\Exception\WaitExecutionException;

function pte_setup_interaction_fax_received() {

    $process = (new ProcessBuilder())
        ->createNode('fax_received', 'fax_received')->end()
        ->createStartTransition('fax_received')->end()
        ->getProcess();
    return $process;

}

function pte_get_registry_fax_received() {

  $registryArray = array(
      'fax_received' => function(Token $token) {  //Node 1 - waiting for send

            $requestData = $token->getValue("process_context");
            $requestData['interaction_type_name'] = "Fax";
            $requestData['interaction_template_name'] = $requestData["page_count_string"];
            $requestData['interaction_type_status'] = "Received";
            $requestData['interaction_to_from_string'] = "From";
            $requestData['interaction_to_from_name'] =  $requestData['formatted_number'];
            $requestData['interaction_regarding'] = $requestData['topic_name'];
            $requestData['interaction_vault_link'] = "";
            $requestData['interaction_file_away_handling'] = "archive_interaction";

            $requestData['fax_field_fax_number'] = $token->getValue("formatted_number");
            $pageCountString = $requestData["page_count_string"];
            $requestData['page_count_string'] = $pageCountString;
            $requestData['template_name'] = "";
            $requestData['view_link_file_type'] = "Fax";
            $requestData['to_from'] = '';
            $requestData['interaction_complete'] = true;
            $requestData['widget_type_id'] = "information";
            $requestData['information_title'] = "Fax Received";

            $requestData['buttons'] =  array(
              "file" => true
              );

              $requestData['data_lines'] =  array(
                  "from_sender_number_line",
                  "regarding_line",
                );

             $requestData['content_lines'] =  array(
                "vault_item",
                "topic_panel"
              );

            if ($requestData['topic_type_id'] == 5) {  //Personal
              $requestData['content_lines'] =  array(
                "vault_item",
                "personal_panel"
                );
              }

            if ($requestData['topic_type_id'] == 4) {  //Network
              $requestData['content_lines'] =  array(
                "vault_item",
                "network_panel"
                );
              }

            $requestData['message_lines'] =  array(
              );
            $requestData['sync'] = true;
            $requestData['requires_user_attention'] = true;
            $requestData['wsc_send_notification'] = true;
            $token->setValue("process_context", $requestData);
            return true;

      }
  );

return $registryArray;

}



?>
