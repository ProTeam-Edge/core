<?php
//Interaction Function Registry and Process Tree for Sending a Proteam Invitation

use Formapro\Pvm\Token;
use Formapro\Pvm\ProcessBuilder;
use Formapro\Pvm\Exception\WaitExecutionException;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Cookie\CookieJar;

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

function pte_setup_interaction_mint_nft() {

    $process = (new ProcessBuilder())
        ->createNode('mint_nft', 'mint_nft')->end()
        ->createNode('nft_minted', 'nft_minted')->end()

        ->createTransition('mint_nft', 'nft_minted')->end()
        ->createStartTransition('mint_nft')->end()
        ->getProcess();
    return $process;

}

function pte_get_registry_mint_nft() {

  $registryArray = array(
      'mint_nft' => function(Token $token) {
            alpn_log('START NFT MINT...');

            global $wpdb;

            $requestData = $token->getValue("process_context");
            if (!isset($requestData['nft_media_is_processing'])) {  //start processing media and uploading to IPFS. Once.

              $contractTemplateId = 4; // selectable later
              //create tokenId
              $smartContractData = array(
                "owner_id" => $requestData['owner_id'],
                "process_id" => $requestData['process_id'],
                "contract_template_id" => $contractTemplateId
              );
              $wpdb->insert( 'alpn_smart_contracts', $smartContractData );
              $requestData['nft_token_id'] = $wpdb->insert_id;
              $requestData['nft_contract_template_id'] = $contractTemplateId;
              $contractData = $wpdb->get_results(
                $wpdb->prepare("SELECT contract_address from alpn_smart_contract_templates WHERE id = %d", $contractTemplateId)
               );
              if (isset($contractData[0])) {
                $requestData['nft_contract_address'] = $contractData[0]->contract_address;
              }
              wsc_start_nft_media_processing($requestData);
              $requestData['nft_media_is_processing'] = true;
            }
            $requestData['interaction_type_name'] = "NFT";
            $requestData['interaction_template_name'] = "";
            $requestData['interaction_type_status'] = "Mint NFT";
            $requestData['interaction_to_from_string'] = "To";
            $requestData['interaction_to_from_name'] = "";
            $requestData['interaction_regarding'] = $requestData['topic_name'];
            $requestData['interaction_vault_link'] = "";
            $requestData['interaction_file_away_handling'] = "delete_interaction";

            if (!$requestData['nft_notify_user_files_ready'] && $requestData['nft_ipfs_files']) {
                //TODO update user UI.  //UI screen needs to account for this.
                $requestData['nft_notify_user_files_ready'] = true;
            }

            if (!isset($requestData['nft_token_uri']) && $requestData['nft_ipfs_files'] && $requestData['nft_ready_to_mint']) {

              if (isset($requestData['nft_ipfs_files']['archive_url'])){
                $mediaUrl = $requestData['nft_ipfs_files']['archive_url'];
              } else if (isset($requestData['nft_ipfs_files']['document_url'])) {
                $mediaUrl = $requestData['nft_ipfs_files']['document_url'];
              } else if (isset($requestData['nft_ipfs_files']['image_url'])) {
                $mediaUrl = $requestData['nft_ipfs_files']['image_url'];
              } else if (isset($requestData['nft_ipfs_files']['animation_url'])) {
                $mediaUrl = $requestData['nft_ipfs_files']['animation_url'];
              } else if (isset($requestData['nft_ipfs_files']['music_url'])) {
                $mediaUrl = $requestData['nft_ipfs_files']['music_url'];
              }
              $source = "https://ipfs.moralis.io:2053/ipfs/";
              $sourceLen = strlen($source);
              if (substr($mediaUrl, 0, $sourceLen) == $source) {
                $mediaUrl = "https://gateway.moralisipfs.com/ipfs/" . substr($mediaUrl, $sourceLen);
              }

              $descriptionWithMediaLink = $requestData['nft_description'] . "\n\nMedia: " . $mediaUrl;

              $metaDataArray = array(
                "name" => $requestData['nft_name'],
                "description" => $descriptionWithMediaLink,
                "attributes" => $requestData['nft_attributes'],
                "wscVaultId" => $requestData['vault_id'],
                "wscProcessId" => $requestData['process_id'],
                "wscOwnerId" => $requestData['owner_id']
              );
              foreach($requestData['nft_ipfs_files'] as $key => $value) {
                $metaDataArray[$key] = $value;
              }

              $certArray = $metaDataArray;
              $certArray['description'] = $requestData['nft_description'];

              vit_store_kvp($requestData['process_id'], $metaDataArray);
              $certificate64 = wsc_create_nft_certificate("wiscle_nft_certificate_{$requestData['nft_contract_template_id']}", $certArray);

              $fileArray = array(
                array(
                  "path" => "{$requestData['process_id']}/certificate.png",
                  "content" => $certificate64
                )
              );
              $fileArray = json_encode($fileArray);
              $client = new Client([
                  'timeout'  => 60
              ]);
              $fullUrl = "https://deep-index.moralis.io/api/v2/ipfs/uploadFolder";
              $moralisApiKey = MORALIS_API_KEY;
              $headers = array(
                        "Accept" => "application/json",
                        "x-api-key" => $moralisApiKey,
                        "Content-Type" => "application/json"
                      );
              $request = new Request('POST', $fullUrl, $headers, $fileArray);
              $client->sendAsync($request)->then(
                 function ($value) {  //Promise
                   $results = json_decode($value->getBody()->getContents(), true);
                   $Url1 = $results[0]['path'];
                   if ($Url1) {

                     $processIdRight = strrpos($Url1, "/");   //TODO cookies or passing in variables. Need someone to show me how.
 										 $processId = substr($Url1, 0, $processIdRight);
 										 $processIdLeft = strrpos($processId, "/");
 										 $processId = substr($processId, $processIdLeft + 1);

                     $metaDataArray = vit_get_kvp($processId);  //TODO should instead use WW
                     $metaDataArray['nft_certificate_url'] = $Url1;

                     if (!isset($metaDataArray['image'])) {
                       $metaDataArray['image'] = $Url1;
                    }
                    $client = new GuzzleHttp\Client([
                        'timeout'  => 60
                    ]);
                    $fileArray = array(
                      array(
                        "path" => "{$processId}/wiscleNft.json",
                        "content" => base64_encode(json_encode($metaDataArray))
                      )
                    );
                    $fullUrl = "https://deep-index.moralis.io/api/v2/ipfs/uploadFolder";
                    $moralisApiKey = MORALIS_API_KEY;
                    $headers = array(
                              "Accept" => "application/json",
                              "x-api-key" => $moralisApiKey,
                              "Content-Type" => "application/json"
                            );
                    $uriRequest = new GuzzleHttp\Psr7\Request('POST', $fullUrl, $headers, json_encode($fileArray));

                    $client->sendAsync($uriRequest)->then(
                       function ($value) {  //Promise
                         $results = json_decode($value->getBody()->getContents(), true);
                         $tokenUri = $results[0]['path'];
                         $processIdRight = strrpos($tokenUri, "/");   //TODO cookies or passing in variables. Need someone to show me how. Keep using KVP?
                         $processId = substr($tokenUri, 0, $processIdRight);
                         $processIdLeft = strrpos($processId, "/");
                         $processId = substr($processId, $processIdLeft + 1);

                         $processData = array(
                           'process_id' => $processId,
                           'process_type_id' => "mint_nft",
                           'process_data' => array(
                               'nft_token_uri' => $tokenUri
                             )
                         );
                         pte_manage_interaction($processData);

                        },
                        function ($reason) {
                          alpn_log( 'The final promise was rejected.' );
                          alpn_log( $reason );
                        }
                    )->wait();

                   }

                  },
                  function ($reason) {
                    alpn_log( 'The certificate promise was rejected.' );
                    alpn_log( $reason );
                  }
              )->wait();

              //Interact with client? 1) finish up, full circle notify client.
              $token->setValue("process_context", $requestData);
              throw new WaitExecutionException();
            }

            if (isset($requestData['nft_token_uri'])) {

              alpn_log("HERE");

              $nftMintData = array(
                "nft_token_uri" => $requestData['nft_token_uri'],
                "nft_contract_address" => $requestData['nft_contract_address'],
                "nft_token_id" => $requestData['nft_token_id'],
                "nft_recipient_id" => false
              );
              $data = array(
          			"sync_type" => 'add_update_section',
          			"sync_section" => 'nft_start_mint',
          			"sync_user_id" => $requestData['owner_id'],
          			"sync_payload" => $nftMintData
          		);
          		pte_manage_user_sync($data);

              $token->setValue("process_context", $requestData);
              return;
            }


            $requestData['widget_type_id'] = "mint_nft";
            $requestData['buttons'] =  array(
              "file" => true
              );
            $requestData['template_type_id'] = 1;  //in db -- perhaps use a different key process_type_id
            $requestData['sync'] = true;
            $requestData['requires_user_attention'] = true;


            $token->setValue("process_context", $requestData);
            throw new WaitExecutionException();

      },
      'nft_minted' => function(Token $token) {

          alpn_log('HANDLING NFT MINTED');
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
