<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
set_time_limit(120);
ini_set('memory_limit', '1048M');
ini_set('default_socket_timeout', 120);
ini_set('max_execution_time', 120);
register_shutdown_function(function(){

	$error = error_get_last();
	if (isset($error['type']) && $error['type'] == E_ERROR) {
		alpn_log("FATAL ERROR");
		alpn_log($error);
	}
});

 //alpn_log("Handle Background NFT Processing");

$testSingleNft = false;

if ($testSingleNft == true) {

	$nftExists = true;

	$tokenAddress = "0x25ed58c027921e14d86380ea2646e3a1b5c55a8b";
	$tokenId = "4051";

	$results = $wpdb->get_results(
		$wpdb->prepare("SELECT * FROM alpn_nft_meta WHERE contract_address = %s AND token_id = %s", $tokenAddress, $tokenId)
	);

	if (isset($results[0])) {
		$value = json_decode($results[0]->moralis_meta, true);
		pp("PROCESSING SINGLE NFT");
		pp($value);
	}
}

if (!$testSingleNft) {

$verificationKey = (isset($_POST['verification_key']) && strlen($_POST['verification_key']) >= 20 && strlen($_POST['verification_key']) <= 22) ? $_POST['verification_key'] : false;

if ( !$verificationKey ) {
	alpn_log("ASYNC NFT PROCESS -- NO VERIFICATION KEY");
	exit;
}

$value = false;

$data = vit_get_kvp($verificationKey);

$walletAddress = $data['account_address'];

if (!$walletAddress) {
	alpn_log("ASYNC NFT PROCESS -- NO PUBLIC KEY");
	exit;
}

$results = $wpdb->get_results(
	$wpdb->prepare("SELECT * FROM alpn_wallet_meta WHERE account_address = %s", $walletAddress)
);

if (!isset($results[0])) {
	alpn_log("ASYNC NFT PROCESS -- WALLET META");
	exit;
}

$walletData = $results[0];
$state = $walletData->state;

if ($state == 'processing') {

	$nftQueue = json_decode($walletData->nft_queue, true);

	if (isset($nftQueue[0])) { //pop top. Save.

		$value = $nftQueue[0];
		$nftQueue = array_slice($nftQueue, 1);

		$newWalletData['nft_queue'] = json_encode($nftQueue);
		$whereClause['account_address'] = $walletAddress;
		$wpdb->update( 'alpn_wallet_meta', $newWalletData, $whereClause );

	} else {  //nothing to process. Done

		$newWalletData['state'] = 'ready';
		$whereClause['account_address'] = $walletAddress;
		$wpdb->update( 'alpn_wallet_meta', $newWalletData, $whereClause );
	}
}

if (!$value) {
	exit;
}

$nftExists = false;

$nftExistsData = $wpdb->get_results(
	$wpdb->prepare("SELECT id, state FROM alpn_nft_meta WHERE contract_address = %s AND token_id = %s", $value['token_address'], $value['token_id'])
 );
 if (isset($nftExistsData[0])) {
	 $nftExists = true;  //update instead of insert
	 if ($nftExistsData[0]->state == 'ready') { //skip if ready
			 $data = array(
		 		"account_address" => $walletAddress
		 	);
		 	$verificationKey = pte_get_short_id();
		 	$params = array(
		 		"verification_key" => $verificationKey
		 	);
		 	vit_store_kvp($verificationKey, $data);
		 	try {
		 		pte_async_job(PTE_ROOT_URL . "wsc_async_nft_process.php", array('verification_key' => $verificationKey));
				exit;
		 	} catch (Exception $e) {
		 		alpn_log($e);
		 	}
	}
 }
}
	//alpn_log($value);

$meta = json_decode($value['metadata'], true);
if ($testSingleNft == true) {pp($meta);}


if (isset($meta['pdf_url']) && $meta['pdf_url'] || isset($meta['image']) && $meta['image'] || isset($meta['image_url']) && $meta['image_url'] || isset($meta['animation_url']) && $meta['animation_url'] || isset($meta['music_url']) && $meta['music_url']) {

	if ($testSingleNft == true) {pp("TAKING METADATA ROUTE");}

	$pdfUrl = isset($meta['pdf_url']) && $meta['pdf_url'] ? wsc_cleanup_nft_uri($meta['pdf_url']) : "";
	$animationUrl = isset($meta['animation_url']) && $meta['animation_url'] ? wsc_cleanup_nft_uri($meta['animation_url']) : "";
	$musicUrl = isset($meta['music_url']) && $meta['music_url'] ? wsc_cleanup_nft_uri($meta['music_url']) : "";
	$imageUrl = isset($meta['image']) && $meta['image'] ? wsc_cleanup_nft_uri($meta['image']) : wsc_cleanup_nft_uri($meta['image_url']);
	$externalUrl = isset($meta['external_url']) ? $meta['external_url'] : "";
	$name = isset($meta['name']) ? $meta['name'] : "";
	$description = isset($meta['description']) ? $meta['description'] : "";
	$attributes = isset($meta['attributes']) ? $meta['attributes'] : [];

	$newNft = array("error" => "", "temp_file" => '', "mime_type" => "", "name" => $name, "description" => $description,  "pdf_url" => $pdfUrl, "music_url" => $musicUrl, "animation_url" => $animationUrl, "image_url" => $imageUrl, "attributes" => $attributes, "source" => "standard");


} else if ($value['token_uri']) { //no metadata but token

	if ($testSingleNft == true) {pp("TAKING TOKEN URI ROUTE");}

		$fullUrl = wsc_cleanup_nft_uri($value['token_uri']);

		if ($testSingleNft == true) {pp($fullUrl);}

		$tempFileId = pte_get_short_id();
		$tempFileName = PTE_ROOT_PATH . "tmp/" . $tempFileId;
		$response = wsc_get_file($fullUrl, $tempFileName);
		$fileMimeType = mime_content_type($tempFileName);


		if ($testSingleNft == true) {pp($fileMimeType);}

		if ($fileMimeType == "application/json" || $fileMimeType == "text/plain" || $fileMimeType == "text/html" || $fileMimeType == "text/xml") {   //probably a better way to fail


		if ($testSingleNft == true) {pp("TRYING TO PROCESS JSON");}


		 $newFile = file_get_contents($tempFileName);
		 $meta = json_decode($newFile, true);


		 if ($testSingleNft == true) {pp($meta);}


		 // if ($fileMimeType == "text/plain") {
			//  alpn_log("PLAIN");
			//  alpn_log($value);
			//  alpn_log("HERE".$newFile."THERE");
			//  alpn_log($meta);
			//  alpn_log("TEST");
			//  alpn_log(json_decode(trim($newFile), true));
		 // }

		 $pdfUrl = isset($meta['pdf_url']) && $meta['pdf_url'] ? wsc_cleanup_nft_uri($meta['pdf_url']) : "";
		 $animationUrl = isset($meta['animation_url']) && $meta['animation_url'] ? wsc_cleanup_nft_uri($meta['animation_url']) : "";
		 $musicUrl = isset($meta['music_url']) && $meta['music_url'] ? wsc_cleanup_nft_uri($meta['music_url']) : "";
		 $imageUrl = isset($meta['image']) && $meta['image'] ? wsc_cleanup_nft_uri($meta['image']) : false;
		 $imageUrl = (!$imageUrl && isset($meta['image_url'])) ? wsc_cleanup_nft_uri($meta['image_url']) : $imageUrl;
		 $imageUrl = (!$imageUrl && isset($meta['image_data'])) ? $meta['image_url'] : $imageUrl;

		 $externalUrl = isset($meta['external_url']) ? $meta['external_url'] : "";
		 $name = isset($meta['name']) ? $meta['name'] : "";
		 $description = isset($meta['description']) ? $meta['description'] : "";
		 $attributes = isset($meta['attributes']) ? $meta['attributes'] : [];

		 if (trim($animationUrl) || trim($musicUrl) || trim($imageUrl) || trim($pdfUrl)) {
			 $newNft = array("image_url" => $imageUrl, "full_url" => $fullUrl, "file_key" => $tempFileId, "mime_type" => '', "name" => $name, "description" => $description, "attributes" => $attributes, "pdf_url" => $pdfUrl, "music_url" => $musicUrl, "animation_url" => $animationUrl, "value" => $value, "source" => "metadata_file");
		 } else {
			 $newNft = array("error" => "no_media_urls_found", "temp_file" => '', "mime_type" => "", "name" => "", "description" => "", "attributes" => [], "music_url" => '', "animation_url" => '', "pdf_url" => '', "image_url" => '', "token_uri" => $fullUrl, "source" => "metadata_file");
		 }
		 unlink($tempFileName);

		 if ($testSingleNft == true) {pp($newNft);}


	} else { //it's a file  //TODO unlink this file later

		if ($testSingleNft == true) {pp("TAKING FILE ROUTE");}


		 $name = isset($value['name']) ? $value['name'] : "";
		 $description = isset($value['symbol']) ? $value['symbol'] : "";
		 $animationUrl = "";
		 $imageUrl = wsc_cleanup_nft_uri($value['token_uri']);
		 $musicUrl = "";
		 $externalUrl = "";
		 $attributes = [];

		 $newNft = array("error" => "", "file_key" => $tempFileId, "mime_type" => $fileMimeType, "name" => $name, "description" => $description, "attributes" => $attributes, "pdf_url" => "", "animation_url" => "", "image_url" => $imageUrl, "source" => "metadata_file");

}

} else {
	$newNft = array("error" => "no_media_urls_or_token_uri_found", "temp_file" => '', "mime_type" => "", "name" => "", "description" => "", "attributes" => [], "pdf_url" => '', "music_url" => '', "animation_url" => '', "image_url" => '', "source" => "metadata_file");
	unlink($tempFileName);
}

// alpn_log($value);
// alpn_log($newNft);

if ($testSingleNft == true) {pp("NEW NFT");}
if ($testSingleNft == true) {pp($newNft);}

if (!$newNft['error']) {

	if (!$newNft["mime_type"]) {

		$imageUrl = $newNft["image_url"];
		$animationUrl = $newNft["animation_url"];
		$musicUrl = $newNft["music_url"];

		if ($imageUrl && ($animationUrl || $musicUrl)) {
			$contentUrl = ($animationUrl) ? $animationUrl : $musicUrl;
			$thumbUrl = $imageUrl;
		} else if (!$imageUrl && ($animationUrl || $musicUrl)) {
			$contentUrl = ($animationUrl) ? $animationUrl : $musicUrl;
			$thumbUrl = $contentUrl;
		} else if ($imageUrl) {
			$contentUrl = $imageUrl;
			$thumbUrl = $imageUrl;
		}

		if ($contentUrl == $thumbUrl) {

			$tempFileId = pte_get_short_id();
			$tempFileName = PTE_ROOT_PATH . "tmp/" . $tempFileId;
			$response = wsc_get_file($contentUrl, $tempFileName);
			$fileMimeType = mime_content_type($tempFileName);

			if ($testSingleNft == true) {pp($contentUrl);}
			if ($testSingleNft == true) {pp($tempFileName);}
			if ($testSingleNft == true) {pp($fileMimeType);}

			$fileSettings = array(
				"file_key" => $tempFileId,
				"mime_type" => $fileMimeType
			);
			$nftResponseMedia = wsc_store_nft_file($fileSettings, false);

			if ($testSingleNft == true) {pp("RESPONSE MEDIA");}
			if ($testSingleNft == true) {pp($nftResponseMedia);}

			if (isset($nftResponseMedia['status']) && $nftResponseMedia['status'] == "ok" && $nftResponseMedia['media_type'] && $nftResponseMedia['media_type'] != "unsupported") {

				$newThumbData = wsc_process_thumb($nftResponseMedia);
				$fileKeyThumb = $newThumbData['file_key']; //with extension added
				$fileKeyThumbLarge = $newThumbData['large_file_key']; //with extension added
				$fileKeyThumbShare = $newThumbData['share_file_key']; //with extension added
				$mimeTypeThumb = $newThumbData['mime_type'];

				$fileKeyMedia = $nftResponseMedia['file_key']; //with extension added
				$mimeTypeMedia = $nftResponseMedia['mime_type'];

				$meta = json_encode(array(
					"name" => $newNft['name'],
					"description" => $newNft['description'],
					"attributes" => $newNft['attributes']
				));

				try {
					if ($nftExists) {
						$nftData = array (
							"media_file_key" => $fileKeyMedia,
							"media_mime_type" => $mimeTypeMedia,
							"thumb_file_key" => $fileKeyThumb,
							"thumb_large_file_key" => $fileKeyThumbLarge,
							"thumb_share_file_key" => $fileKeyThumbShare,
							"thumb_mime_type" => $mimeTypeThumb,
							"state" => "ready",
							"category_id" => "visible",
							"meta" => $meta
						);
						$whereClause = array (
							"contract_address" => $value['token_address'],
							"token_id" => $value['token_id']
						);

						if ($testSingleNft == true) {pp("UDATING");}
						if ($testSingleNft == true) {pp($nftData);}


						$wpdb->update( 'alpn_nft_meta', $nftData, $whereClause );
					} else {
						$nftData = array (
							"owner_address" => $walletAddress,
							"contract_address" => $value['token_address'],
							"chain_id" => $value['chain_id'],
							"token_id" => $value['token_id'],
							"token_uri" => wsc_cleanup_nft_uri($value['token_uri']),
							"contract_type" => $value['contract_type'],
							"media_file_key" => $fileKeyMedia,
							"media_mime_type" => $mimeTypeMedia,
							"thumb_file_key" => $fileKeyThumb,
							"thumb_large_file_key" => $fileKeyThumbLarge,
							"thumb_share_file_key" => $fileKeyThumbShare,
							"thumb_mime_type" => $mimeTypeThumb,
							"state" => "ready",
							"category_id" => "visible",
							"moralis_meta" => json_encode($value),
							"meta" => $meta
						);
						$wpdb->insert( 'alpn_nft_meta', $nftData );
					}

				} catch (Exception $e) {
					alpn_log("Problem adding an nft to metadata ONE. Dupe?");
					alpn_log($e);
				}

			} else {
				$meta = json_encode(array(
					"name" => $newNft['name'],
					"description" => $newNft['description'],
					"attributes" => $newNft['attributes']
				));
				try {
					if ($nftExists) {
						$nftData = array (
							"state" => "media_failed",
							"category_id" => "error"
						);
						$whereClause = array (
							"contract_address" => $value['token_address'],
							"token_id" => $value['token_id']
						);
						$wpdb->update( 'alpn_nft_meta', $nftData, $whereClause );
					} else {
						$nftData = array (
							"owner_address" => $walletAddress,
							"contract_address" => $value['token_address'],
							"chain_id" => $value['chain_id'],
							"token_id" => $value['token_id'],
							"token_uri" => wsc_cleanup_nft_uri($value['token_uri']),
							"contract_type" => $value['contract_type'],
							"media_file_key" => "",
							"media_mime_type" => "",
							"thumb_file_key" => "",
							"thumb_mime_type" => "",
							"state" => "media_failed",
							"category_id" => "error",
							"moralis_meta" => json_encode($value),
							"meta" => $meta
						);
						$wpdb->insert( 'alpn_nft_meta', $nftData );
				}
				} catch (Exception $e) {
					alpn_log($e);
					alpn_log($nftData);
				}
			}
		} else {  //separate thumb and media

			$tempFileId = pte_get_short_id();
			$tempFileName = PTE_ROOT_PATH . "tmp/" . $tempFileId;
			$response = wsc_get_file($thumbUrl, $tempFileName);
			$fileMimeType = mime_content_type($tempFileName);

			$fileSettings = array(
				"account_address" => $walletAddress,
				"file_key" => $tempFileId,
				"mime_type" => $fileMimeType
			);
			$nftResponseThumb = wsc_store_nft_file($fileSettings, false);
			$thumbIsSupported = ($nftResponseThumb['media_type'] && $nftResponseThumb['media_type'] != "unsupported") ? true : false;

			$tempFileId = pte_get_short_id();
			$tempFileName = PTE_ROOT_PATH . "tmp/" . $tempFileId;
			$response = wsc_get_file($contentUrl, $tempFileName);
			$fileMimeType = mime_content_type($tempFileName);

			$fileSettings = array(
				"account_address" => $walletAddress,
				"file_key" => $tempFileId,
				"mime_type" => $fileMimeType
			);
			$nftResponseMedia = wsc_store_nft_file($fileSettings);
			$mediaIsSupported = ($nftResponseMedia['media_type'] && $nftResponseMedia['media_type'] != "unsupported") ? true : false;

			if ($testSingleNft == true) {pp($nftResponseThumb);}
			if ($testSingleNft == true) {pp($nftResponseMedia);}

			if (isset($nftResponseThumb['status']) && $nftResponseThumb['status'] == "ok" && ($thumbIsSupported || $mediaIsSupported)) {

				if ($mediaIsSupported) {
					$fileKeyMedia = $nftResponseMedia['file_key']; //with extension added
					$mimeTypeMedia = $nftResponseMedia['mime_type'];
				} else {
					$fileKeyMedia = $nftResponseThumb['file_key']; //with extension added
					$mimeTypeMedia = $nftResponseThumb['mime_type'];
				}

				$newThumbData = wsc_process_thumb($nftResponseThumb);
				$fileKeyThumb = $newThumbData['file_key']; //with extension added
				$fileKeyThumbLarge = $newThumbData['large_file_key']; //with extension added
				$fileKeyThumbShare = $newThumbData['share_file_key']; //with extension added
				$mimeTypeThumb = $newThumbData['mime_type'];

				$meta = json_encode(array(
					"name" => $newNft['name'],
					"description" => $newNft['description'],
					"attributes" => $newNft['attributes']
				));

				try {
					if ($nftExists) {
						$nftData = array (
							"media_file_key" => $fileKeyMedia,
							"media_mime_type" => $mimeTypeMedia,
							"thumb_file_key" => $fileKeyThumb,
							"thumb_large_file_key" => $fileKeyThumbLarge,
							"thumb_share_file_key" => $fileKeyThumbShare,
							"thumb_mime_type" => $mimeTypeThumb,
							"state" => "ready",
							"category_id" => "visible",
							"meta" => $meta
						);
						$whereClause = array (
							"contract_address" => $value['token_address'],
							"token_id" => $value['token_id']
						);
						$wpdb->update( 'alpn_nft_meta', $nftData, $whereClause );
					} else {
						$nftData = array (
							"owner_address" => $walletAddress,
							"contract_address" => $value['token_address'],
							"chain_id" => $value['chain_id'],
							"token_id" => $value['token_id'],
							"token_uri" => wsc_cleanup_nft_uri($value['token_uri']),
							"contract_type" => $value['contract_type'],
							"media_file_key" => $fileKeyMedia,
							"media_mime_type" => $mimeTypeMedia,
							"thumb_file_key" => $fileKeyThumb,
							"thumb_large_file_key" => $fileKeyThumbLarge,
							"thumb_share_file_key" => $fileKeyThumbShare,
							"thumb_mime_type" => $mimeTypeThumb,
							"state" => "ready",
							"category_id" => "visible",
							"moralis_meta" => json_encode($value),
							"meta" => $meta
						);
						$wpdb->insert( 'alpn_nft_meta', $nftData );
					}

				} catch (Exception $e) {
					alpn_log("Problem adding an nft to metadata TWO. Dupe?");
					alpn_log($e);
				}
			} else {

				$meta = json_encode(array(
					"name" => $newNft['name'],
					"description" => $newNft['description'],
					"attributes" => $newNft['attributes']
				));

				try {
					if ($nftExists) {
						$nftData = array (
							"state" => "media_failed",
							"category_id" => "error"
						);
						$whereClause = array (
							"contract_address" => $value['token_address'],
							"token_id" => $value['token_id']
						);
						$wpdb->update( 'alpn_nft_meta', $nftData, $whereClause );
					} else {
						$nftData = array (
							"owner_address" => $walletAddress,
							"chain_id" => $value['chain_id'],
							"contract_address" => $value['token_address'],
							"token_id" => $value['token_id'],
							"token_uri" => wsc_cleanup_nft_uri($value['token_uri']),
							"contract_type" => $value['contract_type'],
							"media_file_key" => "",
							"media_mime_type" => "",
							"thumb_file_key" => "",
							"thumb_mime_type" => "",
							"state" => "media_failed",
							"category_id" => "error",
							"moralis_meta" => json_encode($value),
							"meta" => $meta
						);
						$wpdb->insert( 'alpn_nft_meta', $nftData );
					}
				} catch (Exception $e) {
					alpn_log($e);
					alpn_log($nftData);
				}
			}

		}

	} else { //have file with mimetype but not sure what it is

		if ($testSingleNft == true) {pp("FILE ONLY SAVE");}

		$fileSettings = array(
			"account_address" => $walletAddress,
			"file_key" => $newNft['file_key'],
			"mime_type" => $newNft['mime_type']
		);
		$nftResponse = wsc_store_nft_file($fileSettings, true);

		if ($testSingleNft == true) {pp($nftResponse);}

		if (isset($nftResponse['status']) && $nftResponse['status'] == "ok" && $nftResponse['status'] == "ok"  && $nftResponse['media_type'] && $nftResponse['media_type'] != "unsupported") {

			$newThumbData = wsc_process_thumb($nftResponse);

			if ($testSingleNft == true) {pp($newThumbData);}

			$fileKeyThumb = $newThumbData['file_key']; //with extension added
			$fileKeyThumbLarge = $newThumbData['large_file_key']; //with extension added
			$fileKeyThumbShare = $newThumbData['share_file_key']; //with extension added
			$mimeTypeThumb = $newThumbData['mime_type'];

			$fileKey = $nftResponse['file_key']; //with extension added
			$fileMimeType = $nftResponse['mime_type'];

			$meta = json_encode(array(
				"name" => $newNft['name'],
				"description" => $newNft['description'],
				"attributes" => $newNft['attributes']
			));

			try {
				if ($nftExists) {
					$nftData = array (
						"media_file_key" => $fileKey,
						"media_mime_type" => $fileMimeType,
						"thumb_file_key" => $fileKeyThumb,
						"thumb_large_file_key" => $fileKeyThumbLarge,
						"thumb_share_file_key" => $fileKeyThumbShare,
						"thumb_mime_type" => $mimeTypeThumb,
						"state" => "ready",
						"category_id" => "visible",
						"meta" => $meta
					);
					$whereClause = array (
						"contract_address" => $value['token_address'],
						"token_id" => $value['token_id']
					);
					$wpdb->update( 'alpn_nft_meta', $nftData, $whereClause );
				} else {
					$nftData = array (
						"owner_address" => $walletAddress,
						"contract_address" => $value['token_address'],
						"chain_id" => $value['chain_id'],
						"token_id" => $value['token_id'],
						"token_uri" => wsc_cleanup_nft_uri($value['token_uri']),
						"contract_type" => $value['contract_type'],
						"media_file_key" => $fileKey,
						"media_mime_type" => $fileMimeType,
						"thumb_file_key" => $fileKeyThumb,
						"thumb_large_file_key" => $fileKeyThumbLarge,
						"thumb_share_file_key" => $fileKeyThumbShare,
						"thumb_mime_type" => $mimeTypeThumb,
						"state" => "ready",
						"category_id" => "visible",
						"moralis_meta" => json_encode($value),
						"meta" => $meta
					);
					$wpdb->insert( 'alpn_nft_meta', $nftData );
				}

			} catch (Exception $e) {
				alpn_log($e);
				alpn_log($nftData);
			}

		} else {

			$meta = json_encode(array(
				"name" => $newNft['name'],
				"description" => $newNft['description'],
				"attributes" => $newNft['attributes']
			));

			try {
				if ($nftExists) {
					$nftData = array (
						"state" => "media_failed",
						"category_id" => "error"
					);
					$whereClause = array (
						"contract_address" => $value['token_address'],
						"token_id" => $value['token_id']
					);
					$wpdb->update( 'alpn_nft_meta', $nftData, $whereClause );
				} else {
					$nftData = array (
						"owner_address" => $walletAddress,
						"contract_address" => $value['token_address'],
						"chain_id" => $value['chain_id'],
						"token_id" => $value['token_id'],
						"token_uri" => wsc_cleanup_nft_uri($value['token_uri']),
						"contract_type" => $value['contract_type'],
						"media_file_key" => "",
						"media_mime_type" => "",
						"thumb_file_key" => "",
						"thumb_mime_type" => "",
						"state" => "media_failed",
						"category_id" => "error",
						"moralis_meta" => json_encode($value),
						"meta" => $meta
					);
					$wpdb->insert( 'alpn_nft_meta', $nftData );
			 }

			} catch (Exception $e) {
				alpn_log($e);
				alpn_log($nftData);
			}
		}

	}

} else {

	$meta = json_encode(array(
		"name" => $newNft['name'],
		"description" => $newNft['description'],
		"attributes" => $newNft['attributes']
	));

	try {
		if ($nftExists) {
			$nftData = array (
				"state" => "no_urls",
				"category_id" => "error"
			);
			$whereClause = array (
				"contract_address" => $value['token_address'],
				"token_id" => $value['token_id']
			);
			$wpdb->update( 'alpn_nft_meta', $nftData, $whereClause );
		} else {
			$nftData = array (
				"owner_address" => $walletAddress,
				"contract_address" => $value['token_address'],
				"chain_id" => $value['chain_id'],
				"token_id" => $value['token_id'],
				"token_uri" => wsc_cleanup_nft_uri($value['token_uri']),
				"contract_type" => $value['contract_type'],
				"media_file_key" => "",
				"media_mime_type" => "",
				"thumb_file_key" => "",
				"thumb_mime_type" => "",
				"state" => "no_urls",
				"category_id" => "error",
				"moralis_meta" => json_encode($value),
				"meta" => $meta
			);
			$wpdb->insert( 'alpn_nft_meta', $nftData );
		}

	} catch (Exception $e) {
		alpn_log($e);
		alpn_log($nftData);
	}
}


if (!$testSingleNft) {

	$data = array(
		"account_address" => $walletAddress
	);
	$verificationKey = pte_get_short_id();
	$params = array(
		"verification_key" => $verificationKey
	);
	vit_store_kvp($verificationKey, $data);
	try {
		pte_async_job(PTE_ROOT_URL . "wsc_async_nft_process.php", array('verification_key' => $verificationKey));
	} catch (Exception $e) {
		alpn_log($e);
	}

}




?>
