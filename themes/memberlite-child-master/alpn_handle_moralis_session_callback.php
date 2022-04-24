<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

alpn_log("MORALIS CALLBACK");
alpn_log($moralisData);


if (isset($_POST['transloadit'])){

	$moralisData = json_decode(stripslashes($_POST['transloadit']), true);


	$status = $moralisData['ok'];
	$uploads = $moralisData['uploads'];

	$results = $moralisData['results'];
	$zippedUnsupportedTypes1 = isset($results['zipped_unsupported_types_1']) ? wsc_org_result_by_id($results['zipped_unsupported_types_1']) : false;

	//  alpn_log($original);
	//  alpn_log($zippedUnsupportedTypes1);
	//  alpn_log($zippedUnsupportedTypes2);
	// alpn_log($convertedDocTypes);
	// alpn_log($convertedImageTypes);

}

?>
