<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
//alpn_log("Handle VIT ASYNC MANAGE CC GROUP Start");
$verificationKey = (isset($_POST['verification_key']) && strlen($_POST['verification_key']) >= 20 && strlen($_POST['verification_key']) <= 22) ? $_POST['verification_key'] : false;
if ( $verificationKey ) {
	$data = vit_get_kvp($verificationKey);
	alpn_log($data);
	if ($data) {
		$operation = $data['operation'];
		pte_manage_cc_groups($operation, $data);
		//alpn_log("Completed Manage CC Group");
	} else {
		//alpn_log("Failed Verification Key Lookup");
		die;
	}
} else {
	//alpn_log("No Verification Key");
	die;
}
?>
