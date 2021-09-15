<?php
//Interaction Function Registry and Process Tree for Sending a Proteam Invitation

use Formapro\Pvm\Token;
use Formapro\Pvm\ProcessBuilder;
use Formapro\Pvm\Exception\WaitExecutionException;

function pte_setup_interaction_email_send() {

    $process = (new ProcessBuilder())
        ->createNode('send_email', 'send_email')->end()
        ->createNode('email_sent', 'email_sent')->end()

        ->createTransition('send_email', 'email_sent')->end()
        ->createStartTransition('send_email')->end()
        ->getProcess();
    return $process;

}

function pte_get_registry_email_send() {

  $registryArray = array(
      'send_email' => function(Token $token) {
            alpn_log('Start Everything Sending...');
            global $wpdb;
            $requestData = $token->getValue("process_context");
            $requestData['interaction_type_name'] = "File";
            $requestData['interaction_template_name'] = "";
            $requestData['interaction_type_status'] = "Send xLink by Email";
            $requestData['interaction_to_from_string'] = "To";
            $requestData['interaction_to_from_name'] = "";
            $requestData['interaction_regarding'] = $requestData['topic_name'];
            $requestData['interaction_vault_link'] = "";
            $requestData['interaction_file_away_handling'] = "delete_interaction";

            $emailContactTopicId =  $token->getValue("send_email_address_id");  //TODO identical for all

            if ($emailContactTopicId) {    // TODO Why is this not in requestdata by now?

              $results = pte_get_recipient($requestData['owner_network_id'], $emailContactTopicId);

               if (count($results)) {

                  $newData = pte_update_context_with_contact($requestData, $emailContactTopicId, $results);
                  $requestData = $newData['context'];
                  $tContent = $newData['content'];

                  $emailAddress = $tContent['person_email'];
                  $emailAddressName = trim($tContent['person_givenname'] . " " . $tContent['person_familyname']);

                  $requestData["send_email_address_name"] = $requestData["network_name"] = $emailAddressName;
                  $requestData["send_email_address"] = $emailAddress;

                  $linkData = array(
                      'link_type' => 'file',
                      'link_about' => 'Interaction',
                      'send_email_address' => $emailAddress,
                      'send_email_address_name' => $emailAddressName,
                      'send_email_address_givenname' => $tContent['person_givenname'],
                      'send_email_address_familyname' => $tContent['person_familyname'],
                      'send_email_source_topic_id' => $emailContactTopicId,
                      'link_interaction_password' => $requestData["link_interaction_password"],
                      'link_interaction_expiration' => $requestData["link_interaction_expiration"],
                      'link_interaction_options' => $requestData["link_interaction_options"],
                      'process_id' => $requestData['process_id'],
                      'owner_id' => $requestData['owner_id'],
                      'owner_network_id' => $requestData['owner_network_id'],
                      'owner_from_name' => $ownerEmailAddressName,
                      'vault_id' => $requestData['vault_id'],
                      'vault_pdf_key' => $requestData['vault_pdf_key'],
                      'vault_file_key' => $requestData['vault_file_key'],
                      'vault_file_name' => $requestData['vault_file_name'],
                      'template_name' => $requestData['template_name'],
                      'message_title' => $requestData['message_title'],
                      'message_body' => $requestData['message_body']
                  );

                  $linkUid = pte_manage_link("create_link", $linkData);
                  $requestData['link_id'] = $linkUid;
                  $ownerAccountDetails = get_user_by('id', $requestData['owner_id']);

                  $ownerEmailAddress = $ownerAccountDetails->user_email;
                  $ownerFirstName = $ownerAccountDetails->first_name;

                  $ownerEmailAddressName = $requestData['owner_friendly_name'];
                  $emailData = array(
                    'link_type' => 'file',
                  	"to_name" => $requestData['send_email_address_name'],
                  	"to_email" => $requestData['send_email_address'],
                    "from_first_name" => $ownerFirstName,
                    "from_name" => $ownerEmailAddressName,
                    "from_email" => $ownerEmailAddress,
                    "link_id" => $linkUid,
                    'link_interaction_password' => $requestData["link_interaction_password"],
                    'link_interaction_expiration' => $requestData["link_interaction_expiration"],
                    'link_interaction_options' => $requestData["link_interaction_options"],                    
                    "topic_id" => $requestData['topic_id'],
                    "vault_file_name" => $requestData['vault_file_name'],
                    "vault_file_description" => $requestData['vault_file_description'],
                    "vault_id" => $requestData['vault_id'],
                  	"subject_text" => $requestData['message_title'] ? $requestData['message_title'] : "Secure File xLink Received",
                  	"body_text" => $requestData['message_body'] ? nl2br($requestData['message_body']) : "No Message",
                    "email_type" => "view-download"
                  );
                pte_send_mail($emailData);

                if ($requestData["link_interaction_password"]) {  //send second email with passcode, if exists
                  $emailData = array(
                    'link_type' => '',
                    "to_name" => $requestData['send_email_address_name'],
                    "to_email" => $requestData['send_email_address'],
                    "from_first_name" => $ownerFirstName,
                    "from_name" => $ownerEmailAddressName,
                    "from_email" => $ownerEmailAddress,
                    "link_id" => '',
                    "vault_file_name" => '',
                    "vault_id" => $requestData['vault_id'],
                    "subject_text" => $requestData['message_title'] ? "Passcode for - " . $requestData['message_title'] : "Passcode for xLink",
                    "body_text" => "Passcode - " . $requestData["link_interaction_password"],
                    "email_type" => "separate-password"
                  );
                  pte_send_mail($emailData);
                }
              }

              $token->setValue("process_context", $requestData);
              return;
            }
            $requestData['widget_type_id'] = "email_send";
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
      'email_sent' => function(Token $token) {

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
