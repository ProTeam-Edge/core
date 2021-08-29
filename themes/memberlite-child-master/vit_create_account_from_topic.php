<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

alpn_log("About to Create New Account from Xlink");

//TODO check logged in. query
if(!check_ajax_referer('alpn_script', 'security', FALSE)) {
   echo 'Not a valid request.';
	 alpn_log( 'Script Problem');
   die;
}
$qVars = $_POST;
$topicId = isset($qVars['topic_id']) && $qVars['topic_id'] ?  $qVars['topic_id'] : false;

$registrationLink = PTE_BASE_URL . "my-account";
$html = "Sorry, this option is not available. Please register here: &nbsp;&nbsp;<a class='vit_forgot_password_link' href='{$registrationLink}'>Registration</a>";

if ($topicId) {
	$topicData = $wpdb->get_results(
		$wpdb->prepare("SELECT topic_content FROM alpn_topics WHERE id = %d", $topicId)
	 );
	if (isset($topicData[0])) {
		$topicContent = json_decode($topicData[0]->topic_content, true);
		$emailAddress = $topicContent['person_email'];
		$familyName = $topicContent['person_familyname'];
		$givenName = $topicContent['person_givenname'];
		 if (!email_exists($emailAddress)) {
			 $userName = pte_get_short_id();
			 $args = array(
				 "first_name" => $givenName,
				 "last_name" => $familyName
			 );
			 $result = wc_create_new_customer( $emailAddress, $userName, "", $args );
			 $html = "Please check your email inbox/spam for your password:&nbsp; {$emailAddress}";
		 } else {
			 $forgotPassword = PTE_BASE_URL . "my-account/lost-password";
			 $loginPage = PTE_BASE_URL . "my-account";
			 $html = "Account already exists for:&nbsp; {$emailAddress} &nbsp;&nbsp;<a class='vit_forgot_password_link' href='{$forgotPassword}'>Forgot Password</a>";
		 }
	}
}
echo $html;
?>
