<?php
include('./jotform/JotForm.php');
include('./alpn_common.php');
	
$qVars = $_GET;
$submissionId = isset($qVars['submissionId']) ? $qVars['submissionId'] : '';

//$submissionId = '4580498785323109696';  // TODO Delete

try {

	$jotformAPI = new JotForm("20c6392fb493bcd212c4db53452cd42e");
	$submission = $jotformAPI->getSubmission($submissionId);
	if (array_key_exists("answers", $submission)) {
		$answers = $submission['answers'];	

		header('Content-Type: application/json');
		echo json_encode($answers);
	}
}
//catch exception
catch(Exception $e) {
  echo 'Message: ' .$e->getMessage();
}

?>	