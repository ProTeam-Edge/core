<?php
//Interaction Function Registry and Process Tree for Sending a Proteam Invitation

use Formapro\Pvm\Token;
use Formapro\Pvm\ProcessBuilder;
use Formapro\Pvm\Exception\WaitExecutionException;

function pte_setup_sms_send_process() {

    $process = (new ProcessBuilder())
        ->createNode('send_sms', 'send_sms')->end()
        ->createNode('sms_sent', 'sms_sent')->end()

        ->createTransition('send_sms', 'sms_sent')->end()
        ->createStartTransition('send_sms')->end()
        ->getProcess();
    return $process;

}

function pte_get_sms_send_registry() {

  $registryArray = array(
      'send_sms' => function(Token $token) {

            alpn_log('Start SMS Sending...');
            global $wpdb;

            $requestData = $token->getValue("process_context");
            $requestData['interaction_type_name'] = "File";
            $requestData['interaction_template_name'] = "";
            $requestData['interaction_type_status'] = "Send URL by SMS";
            $requestData['interaction_to_from_string'] = "To";
            $requestData['interaction_to_from_name'] = "";
            $requestData['interaction_regarding'] = $requestData['topic_name'];
            $requestData['interaction_vault_link'] = "";

            $mobileContactTopicId =  $token->getValue("send_email_address_id");  //TODO generalize since it is used across fax, sms, email.

            if ($mobileContactTopicId) {    // TODO Why is this not in requestdata by now?

              $results = $wpdb->get_results(
              	$wpdb->prepare("SELECT t.topic_type_id, t.connected_id, t.topic_content, t.dom_id AS email_contact_dom_id,  p.access_level, f.pstn_number, tt.id AS topic_type_id, tt.form_id, tt.name AS topic_name, tt.icon, tt.topic_type_meta, tt.html_template, t3.name AS owner_name, t3.topic_content AS owner_topic_content, t2.image_handle AS profile_handle, t2.topic_content AS connected_topic_content, t2.id AS connected_topic_id, t2.dom_id AS connected_topic_dom_id FROM alpn_topics t LEFT JOIN alpn_proteams p ON p.topic_id = t.id AND p.owner_id = t.owner_id LEFT JOIN alpn_pstn_numbers f ON f.topic_id = t.id LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id LEFT JOIN alpn_topics t2 ON t2.owner_id = t.connected_id AND t2.special = 'user' LEFT JOIN alpn_topics t3 ON t3.owner_id = t.owner_id AND t3.special = 'user' WHERE t.id = %s", $mobileContactTopicId)
               );
               if (count($results)) {
                  $mobileContactData = $results[0];
                  $tTypeId =  $mobileContactData->topic_type_id;
                  $tConnectedId = $mobileContactData->connected_id;

                  $tContent = json_decode($mobileContactData->topic_content, true);
                  $ownerContent = json_decode($mobileContactData->owner_topic_content, true);

                  $requestData['target_topic_type_id'] = $tTypeId;  //can be 4 or non-5.

                  if ($tTypeId == 4) {
                    if ($tConnectedId) { //if connected, use network contact data
                      $tContent = json_decode($mobileContactData->connected_topic_content, true);
                    }
                    $requestData['network_id'] = $mobileContactTopicId;
                    $requestData['connected_network_dom_id'] = $mobileContactData->email_contact_dom_id;
                  } else { //A topic that is a Person that has an email address -- they all should since required.  TODO can non-person's have email address?
                    $requestData['topic_id'] = $mobileContactTopicId;
                    $requestData['topic_dom_id'] = $mobileContactData->email_contact_dom_id;
                  }

                  $mobileNumber = $tContent['person_telephone'];
                  $emailAddress = $tContent['person_email'];
                  $emailAddressName = trim($tContent['person_givenname'] . " " . $tContent['person_familyname']);

                  $requestData["send_email_address_name"] = $requestData["network_name"] = $emailAddressName;
                  $requestData["send_email_address"] = $emailAddress;
                  $requestData["send_mobile_number"] = $mobileNumber;

                  $linkData = array(
                      'link_type' => 'file',
                      'send_email_address' => $emailAddress,
                      'send_email_address_name' => $emailAddressName,
                      'send_mobile_number' => $mobileNumber,
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
                  $ownerEmailAddressName = trim($ownerContent['person_givenname'] . " " . $ownerContent['person_familyname']);
                  $smsData = array(
                    'link_type' => 'file',
                  	"to_name" => $requestData['send_email_address_name'],
                  	"to_email" => $requestData['send_email_address'],
                    "from_name" => $ownerEmailAddressName,
                    "from_email" => $ownerEmailAddress,
                    "send_mobile_number" => $mobileNumber,
                    "link_id" => $linkUid,
                    "vault_file_name" => $requestData['vault_file_name'],
                    "vault_id" => $requestData['vault_id'],
                  	"subject_text" => $requestData['message_title'] ? $requestData['message_title'] : "File Received",
                  	"body_text" => $requestData['message_body'] ? $requestData['message_body'] : "No Message."
                  );
                pte_send_sms($smsData);
              }

              $token->setValue("process_context", $requestData);
              return;
            }
            $requestData['widget_type_id'] = "sms_send";
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
      'sms_sent' => function(Token $token) {

          alpn_log('HANDLING SENT SMS');
          $requestData = $token->getValue("process_context");

          $requestData['interaction_to_from_name'] = $requestData["send_email_address_name"];
          $requestData['static_name'] = $requestData["send_email_address_name"];
          $requestData['interaction_type_status'] = "URL Sent by SMS";
          $requestData['interaction_complete'] = true;

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
