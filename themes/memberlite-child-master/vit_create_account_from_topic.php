<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

alpn_log("About to Create New Account from Xlink");

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
		$wpdb->prepare("SELECT topic_content, owner_id FROM alpn_topics WHERE id = %d", $topicId)
	 );
	if (isset($topicData[0])) {
    $topicOwnedId = $topicData[0]->owner_id;
    $topicOwner = get_user_by( 'id', $topicOwnedId );
    $user_roles = $topicOwner->roles;
    $isAdmin = in_array( 'administrator', $user_roles, true );

		$topicContent = json_decode($topicData[0]->topic_content, true);
		$emailAddress = $topicContent['person_email'];
		$familyName = $topicContent['person_familyname'];
		$givenName = $topicContent['person_givenname'];
		 if (!email_exists($emailAddress)) {
			 $userName = pte_get_short_id();
			 $args = array(
				 "first_name" => $givenName,
				 "last_name" => $familyName,
         "role" => "contributor"
			 );
			 $result = wc_create_new_customer( $emailAddress, $userName, "", $args );

       $user = get_user_by( 'email', $emailAddress );
       $user->remove_role( 'subscriber' );
       $user->add_role( 'contributor' );

			 $html = "Please check your email inbox/spam for your password:&nbsp; {$emailAddress}";
		 } else {
			 $forgotPassword = PTE_BASE_URL . "my-account/lost-password";
			 $loginPage = PTE_BASE_URL . "my-account";
			 $html = "Account exists:&nbsp; {$emailAddress} &nbsp;<a class='vit_forgot_password_link' href='{$forgotPassword}'>Forgot Password</a>";
		 }

     $html = $isAdmin ? $html  : "";

	}
}
echo $html;
?>
