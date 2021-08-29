<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');
alpn_log("Handle VIT CONNECT Start");
$verificationKey = (isset($_POST['verification_key']) && strlen($_POST['verification_key']) >= 20 && strlen($_POST['verification_key']) <= 22) ? $_POST['verification_key'] : false;
if ( $verificationKey ) {
	$data = vit_get_kvp($verificationKey);
	//alpn_log($data);
	if ($data) {
		$newData = array(
			"contact_email" => $data['alt_id'],
			"contact_topic_id" => $data['connected_owner_id'],
			"owner_wp_id" => $data['user_id']
		);
		pte_manage_user_connection($newData);
	//	alpn_log("Completed User Connection");

	} else {
		alpn_log("Failed Verification Key Lookup");
		die;
	}
} else {
	alpn_log("No Verification Key");
	die;
}
?>
