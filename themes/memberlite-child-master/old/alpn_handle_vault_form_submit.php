<?php
include('../../../wp-blog-header.php');

alpn_log("Handling JotForm Callback...");

//TODO RUGGEDIZE -- queries, exceptions, size of files??, logs
//TODO Make this non-blocking while we wait for the file to get from jotform to google drive. Schedule in the future. Or get drive notifications to work. I couldn't
//TODO Check logged in, etc. Good Request. User-ID in all mysql

global $wpdb;

$alpn_meta= array();
$alpn_file_name = "";

$fieldvalues = $_REQUEST['rawRequest'];
$obj = json_decode(stripslashes($fieldvalues), true);

$alpn_meta = array();
foreach ($obj as $key => $value) { //find alpn_meta
	if (strpos($key, "alpn_meta") !== false) {
		$alpn_meta = $value;
	}
}

$alpn_meta_array = json_decode($alpn_meta, true);

$rowData = array(
	"submission_id" => $_REQUEST['submissionID'],
	"upload_id" => $alpn_meta_array['add_guid'],
	"vault_id" => $alpn_meta_array['vault_id'],
	"alpn_uid" => $alpn_meta_array['alpn_uid']
);

pte_register_job('handle_form_submit_copy_after_callback', $rowData);

?>	