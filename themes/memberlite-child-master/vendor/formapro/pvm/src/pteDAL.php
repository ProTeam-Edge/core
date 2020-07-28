<?php
namespace Formapro\Pvm;
use function Formapro\Values\get_values;

include('/var/www/html/proteamedge/public/wp-blog-header.php');

class pteDAL extends InMemoryDAL
{

    public function __construct()
    {

    }


    public function saveProcess(Process $process, $uxMeta): void
    {
        global $wpdb;
        $ownerNetworkId =	isset($uxMeta['owner_network_id']) ? $uxMeta['owner_network_id'] : 0;
        $altId =	isset($uxMeta['alt_id']) && !$ownerNetworkId ? $uxMeta['alt_id'] : '';
        $interactsWithId =	isset($uxMeta['interacts_with_id']) ? $uxMeta['interacts_with_id'] : '';

        if ($ownerNetworkId || $altId) {

          $priority =	isset($uxMeta['priority']) ? $uxMeta['priority'] : 0.0;
          $processId = $process->getId();
          $processValues = json_encode(get_values($process));



alpn_log($processValues);

          $now = date ("Y-m-d H:i:s", time());

          $processData = array(
            'interacts_with_id' => $interactsWithId,
            'owner_network_id' => $ownerNetworkId,
            'alt_id' => $altId,
            'priority' => $priority,
            'process_id' => $processId,
            'ux_meta' => json_encode($uxMeta),
            'json' => $processValues,
            'modified_date' => $now
          );
          $wpdb->replace( 'alpn_interactions', $processData );
      }
    }

    public function getProcess(string $id): Process
    {

alpn_log('Getting Process...');
alpn_log($pteProcessAll);

      global $pteProcessAll;
      return $pteProcessAll['process'];
    }


}
