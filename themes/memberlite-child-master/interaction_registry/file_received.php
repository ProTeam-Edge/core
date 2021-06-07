<?php
//Interaction Function Registry and Process Tree for Sending a Proteam Invitation

use Formapro\Pvm\Token;
use Formapro\Pvm\ProcessBuilder;
use Formapro\Pvm\Exception\WaitExecutionException;

function pte_setup_interaction_file_received() {

    $process = (new ProcessBuilder())
        ->createNode('file_received', 'file_received')->end()
        ->createStartTransition('file_received')->end()
        ->getProcess();
    return $process;

}

function pte_get_registry_file_received() {

  $registryArray = array(
      'file_received' => function(Token $token) {  //Node 1 - waiting for send

            $requestData = $token->getValue("process_context");
            $requestData['interaction_type_name'] = "File";
            $requestData['interaction_template_name'] = '';
            $requestData['interaction_type_status'] = "Received";
            $requestData['interaction_to_from_string'] = "From";
            $requestData['interaction_to_from_name'] = $requestData['static_name'];
            $requestData['interaction_regarding'] = $requestData['topic_name'];
            $requestData['interaction_vault_link'] = "";
            $requestData['interaction_file_away_handling'] = "archive_interaction";


            $requestData['template_name'] = "";
            $requestData['view_link_file_type'] = "File";
            $requestData['to_from'] = 'From';
            $requestData['interaction_complete'] = true;
            $requestData['widget_type_id'] = "information";
            $requestData['information_title'] = "|style_2b|File |style_1b|Received|style_1e| Email Route|style_2e|";

            $requestData['buttons'] =  array(
              "file" => true
              );

              $requestData['data_lines'] =  array(
                  "to_from_line_static",
                  "file_name"
                );

              $requestData['message_lines'] =  array(
                "message_view_only"
                );


                if ($requestData['topic_id'] && $requestData['topic_special'] == 'user') {
                  $requestData['content_lines'][] = "personal_panel";
                } else {
                  $requestData['content_lines'][] = "topic_panel";
                }


            $requestData['sync'] = true;
            $requestData['requires_user_attention'] = true;
            $token->setValue("process_context", $requestData);
            return true;

      }
  );

return $registryArray;

}



?>
