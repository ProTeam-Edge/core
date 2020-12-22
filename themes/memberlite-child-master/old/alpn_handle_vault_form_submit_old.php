<?php
include('../../../wp-blog-header.php');
include('pusher/Pusher.php');
include('pusher/PusherCrypto.php');
include('pusher/PusherException.php');
include('pusher/PusherInstance.php');
include('pusher/Webhook.php');

global $wpdb;

//TODO Check logged in, etc. Good Request. User-ID in all mysql

// Don't forget to solve the uploads problem. Also, what if users design forms with uploads???

$alpn_meta= array();
$alpn_file_name = "";

$fieldvalues = $_REQUEST['rawRequest'];
$obj = json_decode(stripslashes($fieldvalues), true);

$alpn_meta = array();
foreach ($obj as $key => $value) { //find alpn_meta - FT concats field number which we will ignore
	if (strpos($key, "alpn_meta") !== false) {
		$alpn_meta = $value;
	}
	if (strpos($key, "alpn_file_name") !== false) {
		$alpn_file_name = basename(urldecode($value));
	}	
}
$alpn_meta_array = json_decode($alpn_meta, true);
$alpn_meta_array['obj'] = $obj;
$alpn_meta_array['requests'] = $_REQUEST;


//alpn_log($alpn_meta_array);

//$alpn_file_name = $alpn_meta_array;
//$alpn_vault_doc_type = pdf versus form

$alpn_uid = $alpn_meta_array['alpn_uid'];
$alpn_topic_id = $alpn_meta_array['alpn_topic_id'];
$alpn_vault_id = $alpn_meta_array['vault_id'];
$alpn_form_name = $_REQUEST['formTitle'];
$alpn_submission_id = $_REQUEST['submissionID'];
$alpn_form_id = $_REQUEST['formID'];
$alpn_meta_array['file_name'] = $alpn_file_name;
$now = date ("Y-m-d H:i:s", time());

$rowData = array(
	"owner_id" => $alpn_uid,
	"submission_id" => $alpn_submission_id,
	"form_id" => $alpn_form_id,
	"name" => $alpn_form_name,
	"file_name" => $alpn_file_name,
	"modified_date" => $now,
	"topic_id" => $alpn_topic_id
);

if ($alpn_vault_id) {
	$whereClause['id'] = $alpn_vault_id; 
	$wpdb->update( 'alpn_vault', $rowData, $whereClause );	
	$alpn_meta_array['operation'] = 'edit';
} else { 	
	$rowData['created_date'] =  $now;
	$wpdb->insert( 'alpn_vault', $rowData );
	$alpn_meta_array['vault_id'] = $wpdb->insert_id;
	$alpn_meta_array['operation'] = 'add';	
}

$alpn_meta_array[submission_id] = $alpn_submission_id;
$alpn_meta_array[form_id] = $alpn_form_id;

//$alpn_meta_array['last_query'] = $wpdb->last_query;
//$alpn_meta_array['last_error'] = $wpdb->last_error;

$channel = "channel_" . $alpn_uid;

$options = array(
	'cluster' => 'us3',
	'useTLS' => true
);
$pusher = new Pusher\Pusher(
	'92d3804ab35123212f0c',
	'71782d10c00c8aef249f',
	'949505',
$options
);

$data['message'] = $alpn_meta_array;
$pusher->trigger($channel, 'form_submit', $data);

?>	