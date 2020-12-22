<?php
include('../../../wp-blog-header.php');

$jobData = array(
	"submission_id" => "4603159745321815586",
	"user_id" => 2
);

pte_start_async_job("copy_file_from_drive_to_storage", $jobData);

echo 'Done...';

?>	