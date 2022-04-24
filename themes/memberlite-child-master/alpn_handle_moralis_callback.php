<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

alpn_log("MORALIS CALLBACK");
$post = file_get_contents('php://input');
$moralisData = json_decode($post, true);
alpn_log($moralisData);


//Check a bunch of security stuff then
//handle the change in our database. Determine change based on data or I can pass something in.
//create events

if (false) {


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
