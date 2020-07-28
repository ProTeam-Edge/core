<?php
namespace Formapro\Pvm;
use function Formapro\Values\get_values;

include('/var/www/html/proteamedge/public/wp-blog-header.php');

class pteDAL extends InMemoryDAL
{

    public function __construct()
    {

    }

    public function persistToken(Token $token): void
    {

      // New workflow created, store it.
        $process = $token->getProcess();
        $this->persistProcess($process);
    }

    public function persistProcess(Process $process): void
    {
        global $wpdb;

        $userInfo = wp_get_current_user();
        $userID = $userInfo->data->ID;
        
        $processId = $process->getId();
        $processValues = json_encode(get_values($process));
        $now = date ("Y-m-d H:i:s", time());

        $processData = array(
          'process_id' => $processId,
          'json' => $processValues,
          'created_date' => $now,
          'owner_id' => $userID
        );
        $wpdb->insert( 'alpn_workflows', $processData );
    }

    public function getToken(string $id): Token
    {
      global $wpdb;
      //Get topic information
        $results = $wpdb->get_results(
      	   $wpdb->prepare("SELECT json FROM alpn_workflows WHERE process_id = %s", $id)
         );
       if (isset($results[0])){
         $processData = $results[0];
         $processJson = json_decode($processData->json, true);
         $process = Process::create($processJson);
         return $this->getProcessToken($process, $id);
       }
       return false;
    }

    private function getProcessFile(string $processId): string
    {

    }
}
