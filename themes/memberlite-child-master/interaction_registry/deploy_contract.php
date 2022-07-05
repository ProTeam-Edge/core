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

function pte_setup_interaction_deploy_contract() {

    $process = (new ProcessBuilder())
        ->createNode('deploy_contract', 'deploy_contract')->end()
        ->createNode('wait_for_contract_ready', 'wait_for_contract_ready')->end()
        ->createNode('contract_deployed', 'contract_deployed')->end()

        ->createTransition('deploy_contract', 'wait_for_contract_ready')->end()
        ->createTransition('wait_for_contract_ready', 'contract_deployed')->end()
        ->createStartTransition('deploy_contract')->end()
        ->getProcess();
    return $process;

}

function pte_get_registry_deploy_contract() {

  $registryArray = array(
      'deploy_contract' => function(Token $token) {
            alpn_log('START DEPLOY CONTRACT...');

            global $wpdb;

            $requestData = $token->getValue("process_context");

            $requestData['interaction_type_name'] = "Smart Contract";
            $requestData['interaction_template_name'] = "";
            $requestData['interaction_type_status'] = "Deploy";
            $requestData['interaction_to_from_string'] = "To";
            $requestData['interaction_to_from_name'] = "";
            $requestData['interaction_regarding'] = $requestData['topic_name'];
            $requestData['interaction_vault_link'] = "";
            $requestData['interaction_file_away_handling'] = "delete_interaction";

            if (isset($requestData['contract_start_deploy'])) {  //Client
              $token->setValue("process_context", $requestData);
              return;
            }

            if (isset($requestData['deploy_contract_as_custodial'])) {  //server

              $accountAddress = $requestData['contract_account'];

              $custodialAccount = $wpdb->get_results(
                $wpdb->prepare("SELECT pk_enc, enc_key FROM alpn_wallet_meta WHERE account_address = %s", $accountAddress)
              );

              if (isset($custodialAccount[0])) {
                $data = array(
                  'cloud_function' => 'wsc_deploy_contract',
                  'contract_template_id' => 'eSIybJulHThMDnZZ39tDFmXy',
                  'chain_id' => wsc_to_0xid($requestData['contract_blockchain']),
                  'contract_name' => $requestData['contract_name'],
                  'contract_symbol' => $requestData['contract_symbol'],
                  'pk_enc' => $custodialAccount[0]->pk_enc,
                  'enc_key' => $custodialAccount[0]->enc_key
                );
                $contractInfo = json_decode(wsc_call_cloud_function($data), true);

                $smartContractData = array(
                  "contract_address" => $contractInfo['result']['contract_address'],
                  "wallet_address" => $requestData['contract_account'],
                  "process_id" => $requestData['process_id'],
                  "chain_id" => $requestData['contract_blockchain'],
                  "collection_name" => $requestData['contract_name'],
                  "collection_symbol" => $requestData['contract_symbol'],
                  "template_id" => $requestData['contract_template_id']
                );
                $wpdb->insert( 'alpn_smart_contracts_deployed', $smartContractData );

                $requestData['smart_contract_address'] = $contractInfo['result']['contract_address'];
                $requestData['smart_contract_chain_id'] = $requestData['contract_blockchain'];
                $requestData['smart_contract_template_id'] = $requestData['contract_template_id'];

                //refresh client.
                $contractData = array("process_id" => $requestData['process_id']);
                $data = array(
                  "sync_type" => 'add_update_section',
                  "sync_section" => 'smart_contract_is_ready', //not quite -- just refreshes process.
                  "sync_user_id" => $requestData['owner_id'],
                  "sync_payload" => $contractData
                );
                pte_manage_user_sync($data);

              }

              $token->setValue("process_context", $requestData);
              return;
            }

            $requestData['widget_type_id'] = "deploy_contract";
            $requestData['buttons'] =  array(
              "file" => true
              );
            $requestData['template_type_id'] = 1;  //in db -- perhaps use a different key process_type_id
            $requestData['sync'] = true;
            $requestData['requires_user_attention'] = true;

            $token->setValue("process_context", $requestData);
            throw new WaitExecutionException();

      },
      'wait_for_contract_ready' => function(Token $token) {

          alpn_log('HANDLING WAIT FOR CONTRACT');
          $requestData = $token->getValue("process_context");

          if (isset($requestData['contract_finish_deploy'])) {
            $contractData = array("process_id" => $requestData['process_id']);
            $data = array(
              "sync_type" => 'add_update_section',
              "sync_section" => 'smart_contract_is_ready',
              "sync_user_id" => $requestData['owner_id'],
              "sync_payload" => $contractData
            );
            pte_manage_user_sync($data);

            $token->setValue("process_context", $requestData);
            return;
            }

          $requestData['interaction_type_status'] = "Waiting...";
          $requestData['interaction_file_away_handling'] = "archive_interaction";

          $requestData['widget_type_id'] = "information";
          $requestData['buttons'] =  array(
            "file" => true
            );
          $requestData['data_lines'] =  array(
            "smart_contract_address"
            );
          $requestData['sync'] = true;
          $requestData['requires_user_attention'] = false;

          $token->setValue("process_context", $requestData);
          throw new WaitExecutionException();
    },
    'contract_deployed' => function(Token $token) {

        alpn_log('HANDLING CONTRACT DEPLOYED');
        $requestData = $token->getValue("process_context");

        $requestData['interaction_type_status'] = "Ready";
        $requestData['interaction_complete'] = true;
        $requestData['interaction_file_away_handling'] = "archive_interaction";

        $requestData['widget_type_id'] = "information";
        $requestData['buttons'] =  array(
          "file" => true
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
