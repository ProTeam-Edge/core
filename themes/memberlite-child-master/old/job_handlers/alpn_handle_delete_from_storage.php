<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
require('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/vendor/autoload.php');

alpn_log('Start fire and forget google cloud storage delete...');

$now = date ("Y-m-d H:i:s", time());

use Google\Cloud\Storage\StorageClient;

//TODO RUGGEDIZE -- queries, exceptions, size of files??, logs SECURITY!!!, pass in id and check for match. nonce thing maybe?
//alpn_log("after db..." . pte_time_elapsed(microtime() - $nowtime));
//$nowtime = microtime();	

$pVars = unserialize($argv[1]);
$bucketName = isset($pVars['bucket_name']) ? $pVars['bucket_name'] : '';
$fileKey = isset($pVars['file_key']) ? $pVars['file_key'] : '';
$jobId = isset($pVars['job_id']) ? $pVars['job_id'] : '';

try {
	$storage = new StorageClient([
    	'keyFilePath' => '/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/proteam-edge-cf8495258f58.json'
	]);
	
	$bucket = $storage->bucket($bucketName);
    $object = $bucket->object($fileKey);
    $object->delete();	
	
} catch (\Exception $e) { // Global namespace
		$pte_response = array("topic" => "pte_google_storage_delete_exception", "message" => "Problem deleting at Google Storage.", "data" => $e);
		alpn_log($pte_response);
}	

$rowData1 = array(
	"status" => 'closed',
	"reason" => 'success',
	"completed_date" => $now
);	
$whereClause1['id'] = $jobId;
$wpdb->update( 'alpn_jobs', $rowData1, $whereClause1 );

?>