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

 // alpn_log("Handle SINGLE NFT Processing");

 $qVars = $_POST;
 $value = json_decode(stripslashes($qVars['moralis_meta']), true);
 $tokenAddress = $qVars['contract_address'];
 $tokenId = $qVars['token_id'];
 $chainId = $qVars['chain_id'];

 if (!$value && $tokenAddress) {
		 $valueRaw = wsc_get_single_nft_metadata($tokenAddress, $tokenId, $chainId);
		 $value = json_decode($valueRaw, true);
		 if (isset($value['token_address'])) {
			 alpn_log("METADATA HANDLER UPDATING NFT");
			 $moralisMeta = array("moralis_meta" => $valueRaw);
			 $whereClause = array('contract_address' => $tokenAddress, "token_id" => $tokenId, "chain_id" => $chainId);
			 $wpdb->update( 'alpn_nft_meta', $moralisMeta, $whereClause );
		 } else {
			 alpn_log("METADATA HANDLER UNABLE TO UPDATE -- NO TOKEN ADDRESS");
		 }
 }

 $value['chain_id'] = $chainId;

if (!$value) {
	exit;
}

// alpn_log($value);

$nftMeta = json_decode($value['metadata'], true);
$nftMetaImage = (isset($nftMeta['image']) && $nftMeta['image']) ? wsc_cleanup_nft_uri($nftMeta['image']) : false;

if ($value['token_uri'] || $nftMetaImage) {

	// alpn_log("TOKEN URI OR META IMAGE");

		$fullUrl = wsc_cleanup_nft_uri($value['token_uri']);
		$tempFileId = pte_get_short_id();
		$tempFileName = PTE_ROOT_PATH . "tmp/" . $tempFileId;

		$openSeaMetaDataFailed = false;
		$response = wsc_get_file($fullUrl, $tempFileName);
		$httpResponseCode = $response["http_code"];
		if ($httpResponseCode >= 300 && $nftMetaImage) {  //super fallback if can't reach token_uri
				$openSeaMetaDataFailed = isset($response['opensea_meta']) && $response['opensea_meta'] ? true : false;
				$response = wsc_get_file($nftMetaImage, $tempFileName);
				$fullUrl = $nftMetaImage;
		}
		$fileMimeType = mime_content_type($tempFileName);

		if ($fileMimeType == "application/json" || $fileMimeType == "text/plain" || $fileMimeType == "text/html" || $fileMimeType == "text/xml") {   //probably a better way to fail
		 $newFile = file_get_contents($tempFileName);
		 $meta = json_decode($newFile, true);
		 $pdfUrl = isset($meta['pdf_url']) && $meta['pdf_url'] ? wsc_cleanup_nft_uri($meta['pdf_url']) : "";
		 $animationUrl = isset($meta['animation_url']) && $meta['animation_url'] ? wsc_cleanup_nft_uri($meta['animation_url']) : "";
		 $musicUrl = isset($meta['music_url']) && $meta['music_url'] ? wsc_cleanup_nft_uri($meta['music_url']) : "";
		 $imageUrl = isset($meta['image']) && $meta['image'] ? wsc_cleanup_nft_uri($meta['image']) : false;
		 $imageUrl = (!$imageUrl && isset($meta['image_url'])) ? wsc_cleanup_nft_uri($meta['image_url']) : $imageUrl;
		 $imageUrl = (!$imageUrl && $nftMetaImage) ? $nftMetaImage : $imageUrl;

		 $externalUrl = isset($meta['external_url']) ? $meta['external_url'] : "";
		 $name = isset($meta['name']) ? $meta['name'] : "";
		 $description = isset($meta['description']) ? $meta['description'] : "";
		 $attributes = isset($meta['attributes']) ? $meta['attributes'] : [];

		 if (trim($animationUrl) || trim($musicUrl) || trim($imageUrl) || trim($pdfUrl)) {
			 $newNft = array("opensea_meta" => $openSeaMetaDataFailed, "image_url" => $imageUrl, "full_url" => $fullUrl, "file_key" => $tempFileId, "mime_type" => '', "name" => $name, "description" => $description, "attributes" => $attributes, "pdf_url" => $pdfUrl, "music_url" => $musicUrl, "animation_url" => $animationUrl, "value" => $value, "source" => "metadata_file");
		 } else {
			 $newNft = array("error" => "no_media_urls_found", "temp_file" => '', "mime_type" => "", "name" => "", "description" => "", "attributes" => [], "music_url" => '', "animation_url" => '', "pdf_url" => '', "image_url" => '', "token_uri" => $fullUrl, "source" => "metadata_file");
		 }
		 unlink($tempFileName);

	} else { //it's a file  //TODO unlink this file later

		// alpn_log("FILE");

		 $name = isset($value['name']) ? $value['name'] : "";
		 $description = isset($value['symbol']) ? $value['symbol'] : "";
		 $animationUrl = "";
		 $imageUrl = $fullUrl;
		 $musicUrl = "";
		 $externalUrl = "";
		 $attributes = [];

		 $newNft = array("opensea_meta" => $openSeaMetaDataFailed, "error" => "", "file_key" => $tempFileId, "mime_type" => $fileMimeType, "name" => $name, "description" => $description, "attributes" => $attributes, "pdf_url" => "", "animation_url" => "", "image_url" => $imageUrl, "source" => "metadata_file");
}

} else {
	// alpn_log("NO URLS");
	$newNft = array("error" => "no_media_urls_or_token_uri_found", "temp_file" => '', "mime_type" => "", "name" => "", "description" => "", "attributes" => [], "pdf_url" => '', "music_url" => '', "animation_url" => '', "image_url" => '', "source" => "metadata_file");
	unlink($tempFileName);
}

// alpn_log("ABOUT TO PROCESS");
// alpn_log($newNft);


if (!$newNft['error']) {

	if (!$newNft["mime_type"]) {

		$imageUrl = $newNft["image_url"];
		$animationUrl = $newNft["animation_url"];
		$musicUrl = $newNft["music_url"];

		$openSeaDelisted = isset($newNft["opensea_meta"]) && $newNft["opensea_meta"] ? true : false;

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

			$fileSettings = array(
				"file_key" => $tempFileId,
				"mime_type" => $fileMimeType
			);
			$nftResponseMedia = wsc_store_nft_file($fileSettings, false);

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
					"attributes" => $newNft['attributes'],
					"opensea_delisted" => $openSeaDelisted
				));
				$nftData = array (
					"media_file_key" => $fileKeyMedia,
					"media_mime_type" => $mimeTypeMedia,
					"thumb_file_key" => $fileKeyThumb,
					"thumb_large_file_key" => $fileKeyThumbLarge,
					"thumb_share_file_key" => $fileKeyThumbShare,
					"thumb_mime_type" => $mimeTypeThumb,
					"state" => "ready",
					"error_code" => NULL,
				  "category_id" => "visible",
					"meta" => $meta
				);
				pte_json_out($nftData);
				exit;

			} else {
				$meta = json_encode(array(
					"name" => $newNft['name'],
					"description" => $newNft['description'],
					"attributes" => $newNft['attributes'],
					"opensea_delisted" => $openSeaDelisted
				));
				$nftData = array (
					"state" => "failed",
					"error_code" => "media_failed",
					"category_id" => "error",
					"meta" => $meta
				);
				pte_json_out($nftData);
				exit;
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
					"attributes" => $newNft['attributes'],
					"opensea_delisted" => $openSeaDelisted
				));
				$nftData = array (
					"media_file_key" => $fileKeyMedia,
					"media_mime_type" => $mimeTypeMedia,
					"thumb_file_key" => $fileKeyThumb,
					"thumb_large_file_key" => $fileKeyThumbLarge,
					"thumb_share_file_key" => $fileKeyThumbShare,
					"thumb_mime_type" => $mimeTypeThumb,
					"state" => "ready",
					"error_code" => NULL,
					"category_id" => "visible",
					"meta" => $meta
				);
				pte_json_out($nftData);
				exit;

			} else {
				$meta = json_encode(array(
					"name" => $newNft['name'],
					"description" => $newNft['description'],
					"attributes" => $newNft['attributes'],
					"opensea_delisted" => $openSeaDelisted
				));
				$nftData = array (
					"state" => "failed",
					"error_code" => "media_failed",
					"category_id" => "error",
					"meta" => $meta
				);
				pte_json_out($nftData);
				exit;
			}
		}

	} else { //have file with mimetype but not sure what it is

		$openSeaDelisted = isset($newNft["opensea_meta"]) && $newNft["opensea_meta"] ? true : false;

		$fileSettings = array(
			"account_address" => $walletAddress,
			"file_key" => $newNft['file_key'],
			"mime_type" => $newNft['mime_type']
		);
		$nftResponse = wsc_store_nft_file($fileSettings, true);

		if (isset($nftResponse['status']) && $nftResponse['status'] == "ok" && $nftResponse['status'] == "ok"  && $nftResponse['media_type'] && $nftResponse['media_type'] != "unsupported") {

			$newThumbData = wsc_process_thumb($nftResponse);

			$fileKeyThumb = $newThumbData['file_key']; //with extension added
			$fileKeyThumbLarge = $newThumbData['large_file_key']; //with extension added
			$fileKeyThumbShare = $newThumbData['share_file_key']; //with extension added
			$mimeTypeThumb = $newThumbData['mime_type'];

			$fileKey = $nftResponse['file_key']; //with extension added
			$fileMimeType = $nftResponse['mime_type'];

			$meta = json_encode(array(
				"name" => $newNft['name'],
				"description" => $newNft['description'],
				"attributes" => $newNft['attributes'],
				"opensea_delisted" => $openSeaDelisted
			));

			$nftData = array (
				"media_file_key" => $fileKey,
				"media_mime_type" => $fileMimeType,
				"thumb_file_key" => $fileKeyThumb,
				"thumb_large_file_key" => $fileKeyThumbLarge,
				"thumb_share_file_key" => $fileKeyThumbShare,
				"thumb_mime_type" => $mimeTypeThumb,
				"state" => "ready",
				"error_code" => NULL,
				"category_id" => "visible",
				"meta" => $meta
			);
			pte_json_out($nftData);
			exit;

		} else {
			$meta = json_encode(array(
				"name" => $newNft['name'],
				"description" => $newNft['description'],
				"attributes" => $newNft['attributes']
			));
			$nftData = array (
				"state" => "failed",
				"error_code" => "media_failed",
				"category_id" => "error",
				"meta" => $meta
			);
			pte_json_out($nftData);
			exit;
		}
	}

} else {
	$meta = json_encode(array(
		"name" => $newNft['name'],
		"description" => $newNft['description'],
		"attributes" => $newNft['attributes']
	));
	$nftData = array (
		"state" => "failed",
		"error_code" => "no_urls",
		"category_id" => "error",
		"meta" => $meta
	);
	pte_json_out($nftData);
	exit;
}

?>
