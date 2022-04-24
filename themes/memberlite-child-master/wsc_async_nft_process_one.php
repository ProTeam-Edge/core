<?php

	include('/var/www/html/proteamedge/public/wp-blog-header.php');
	set_time_limit(300);
	pp("Processing NFT");

	$walletAddress = "0xa93cfddb2d48df5e7492a82ecc57a554d17f0c0c";

	$contractAddress = "0xcf571b149736f4476a4a47813951fd074846db1c";
	$tokenId = "0";

	$value = json_decode(wsc_get_single_nft_metadata($contractAddress, $tokenId), true);
	//pp($value);

	if (!$value) {
		exit;
	}

	$meta = json_decode($value['metadata'], true);

	//pp($meta);

	if (isset($meta['pdf_url']) && $meta['pdf_url'] || isset($meta['image']) && $meta['image'] || isset($meta['image_url']) && $meta['image_url'] || isset($meta['animation_url']) && $meta['animation_url'] || isset($meta['music_url']) && $meta['music_url']) {

		pp($meta['image']);

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

			$fullUrl = wsc_cleanup_nft_uri($value['token_uri']);

			pp('TOKEN PATH');

			$tempFileId = pte_get_short_id();
			$tempFileName = PTE_ROOT_PATH . "tmp/" . $tempFileId;
			$response = wsc_get_file($fullUrl, $tempFileName);
			$fileMimeType = mime_content_type($tempFileName);

			if ($fileMimeType == "application/json" || $fileMimeType == "text/plain" || $fileMimeType == "text/html" || substr($value['token_uri'], 0, 'data:application/json;utf8,')) {  //json. TODO is it really json? Last one is hack.

			 $newFile = file_get_contents($tempFileName);
			 $meta = json_decode($newFile, true);

			 $pdfUrl = isset($meta['pdf_url']) && $meta['pdf_url'] ? wsc_cleanup_nft_uri($meta['pdf_url']) : "";
			 $animationUrl = isset($meta['animation_url']) && $meta['animation_url'] ? wsc_cleanup_nft_uri($meta['animation_url']) : "";
			 $musicUrl = isset($meta['music_url']) && $meta['music_url'] ? wsc_cleanup_nft_uri($meta['music_url']) : "";
			 $imageUrl = isset($meta['image']) && $meta['image'] ? wsc_cleanup_nft_uri($meta['image']) : wsc_cleanup_nft_uri($meta['image_url']);

			 $externalUrl = isset($meta['external_url']) ? $meta['external_url'] : "";
			 $name = isset($meta['name']) ? $meta['name'] : "";
			 $description = isset($meta['description']) ? $meta['description'] : "";
			 $attributes = isset($meta['attributes']) ? $meta['attributes'] : [];

			 if (trim($animationUrl) || trim($musicUrl) || trim($imageUrl) || trim($pdfUrl)) {
				 $newNft = array("full_url" => $fullUrl, "file_key" => $tempFileId, "mime_type" => '', "name" => $name, "description" => $description, "attributes" => $attributes, "pdf_url" => $pdfUrl, "music_url" => $musicUrl, "animation_url" => $animationUrl, "image_url" => $imageUrl,  "source" => "metadata_file");

				 pp("YO");
				 pp($newNft);

			 } else {
				 $newNft = array("error" => "no_media_urls_found", "temp_file" => '', "mime_type" => "", "name" => "", "description" => "", "attributes" => [], "music_url" => '', "animation_url" => '', "pdf_url" => '', "image_url" => '', "token_uri" => $fullUrl, "source" => "metadata_file");
			 }
			 //unlink($tempFileName);

		} else { //it's a file

			pp("FILE PATH");
			pp(wsc_cleanup_nft_uri($value['token_uri']));


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
		$newNft = array("error" => "no_media_urls_or_token_ure_found", "temp_file" => '', "mime_type" => "", "name" => "", "description" => "", "attributes" => [], "pdf_url" => '', "music_url" => '', "animation_url" => '', "image_url" => '', "source" => "metadata_file");
		//unlink($tempFileName);
}

	pp($newNft);

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

				pp("SAME URL");

				$tempFileId = pte_get_short_id();
				$tempFileName = PTE_ROOT_PATH . "tmp/" . $tempFileId;
				$response = wsc_get_file($contentUrl, $tempFileName);
				$fileMimeType = mime_content_type($tempFileName);

				$fileSettings = array(
					"file_key" => $tempFileId,
					"mime_type" => $fileMimeType
				);
				$nftResponseMedia = wsc_store_nft_file($fileSettings, false);

				pp($fileSettings);
				pp($nftResponseMedia);

				if (isset($nftResponseMedia['status']) && $nftResponseMedia['status'] == "ok") {

					$newThumbData = wsc_process_thumb($nftResponseMedia);
					pp($newThumbData);

					$fileKeyThumb = $newThumbData['file_key']; //with extension added
					$fileKeyThumbLarge = $newThumbData['large_file_key']; //with extension added
					$mimeTypeThumb = $newThumbData['mime_type']; //with extension added

					$fileKeyMedia = $nftResponseMedia['file_key']; //with extension added
					$mimeTypeMedia = $nftResponseMedia['mime_type']; //with extension added

					$meta = json_encode(array(
						"name" => $newNft['name'],
						"description" => $newNft['description'],
						"attributes" => $newNft['attributes']
					));

					try {
						$nftData = array (
							"owner_address" => $walletAddress,
							"contract_address" => $value['token_address'],
							"token_id" => $value['token_id'],
							"token_uri" => wsc_cleanup_nft_uri($value['token_uri']),
							"contract_type" => $value['contract_type'],
							"media_file_key" => $fileKeyMedia,
							"media_mime_type" => $mimeTypeMedia,
							"thumb_file_key" => $fileKeyThumb,
							"thumb_large_file_key" => $fileKeyThumbLarge,
							"thumb_mime_type" => $mimeTypeThumb,
							"state" => "ready",
							"meta" => $meta
						);
						$wpdb->insert( 'alpn_nft_meta', $nftData );

						pp($nftData);

					} catch (Exception $e) {
						pp("Problem adding an nft to metadata ONE. Dupe?");
						pp($e);
					}

				} else {

					$meta = json_encode(array(
						"name" => $newNft['name'],
						"description" => $newNft['description'],
						"attributes" => $newNft['attributes']
					));
					try {
						$nftData = array (
							"owner_address" => $walletAddress,
							"contract_address" => $value['token_address'],
							"token_id" => $value['token_id'],
							"token_uri" => wsc_cleanup_nft_uri($value['token_uri']),
							"contract_type" => $value['contract_type'],
							"media_file_key" => "",
							"media_mime_type" => "",
							"thumb_file_key" => "",
							"thumb_mime_type" => "",
							"state" => "media_failed",
							"meta" => $meta
						);
						$wpdb->insert( 'alpn_nft_meta', $nftData );
					} catch (Exception $e) {
						pp($e);
						pp($nftData);
					}
				}
			} else {

				pp("DIFFERENT URLs");

				$tempFileId = pte_get_short_id();
				$tempFileName = PTE_ROOT_PATH . "tmp/" . $tempFileId;
				$response = wsc_get_file($thumbUrl, $tempFileName);
				$fileMimeType = mime_content_type($tempFileName);

				$fileSettings = array(
					"file_key" => $tempFileId,
					"mime_type" => $fileMimeType
				);
				$nftResponseThumb = wsc_store_nft_file($fileSettings, false);

				pp($fileSettings);
				pp($nftResponseThumb);

				$tempFileId = pte_get_short_id();
				$tempFileName = PTE_ROOT_PATH . "tmp/" . $tempFileId;
				$response = wsc_get_file($contentUrl, $tempFileName);
				$fileMimeType = mime_content_type($tempFileName);

				$fileSettings = array(
					"file_key" => $tempFileId,
					"mime_type" => $fileMimeType
				);
				$nftResponseMedia = wsc_store_nft_file($fileSettings);

				pp($fileSettings);
				pp($nftResponseMedia);

				if (isset($nftResponseThumb['status']) && $nftResponseThumb['status'] == "ok") {

					$newThumbData = wsc_process_thumb($nftResponseThumb);

					pp($newThumbData);

					$fileKeyThumb = $newThumbData['file_key']; //with extension added
					$mimeTypeThumb = $newThumbData['mime_type']; //with extension added
					$fileKeyThumbLarge = $newThumbData['large_file_key']; //with extension added

					if (isset($nftResponseMedia['status']) && $nftResponseMedia['status'] == "ok") {
						$fileKeyMedia = $nftResponseMedia['file_key']; //with extension added
						$mimeTypeMedia = $nftResponseMedia['mime_type']; //with extension added
					} else {
						$fileKeyMedia = $newThumbData['large_file_key']; //with extension added
						$mimeTypeMedia = $newThumbData['mime_type']; //with extension added
					}

					$meta = json_encode(array(
						"name" => $newNft['name'],
						"description" => $newNft['description'],
						"attributes" => $newNft['attributes']
					));

					try {
						$nftData = array (
							"owner_address" => $walletAddress,
							"contract_address" => $value['token_address'],
							"token_id" => $value['token_id'],
							"token_uri" => wsc_cleanup_nft_uri($value['token_uri']),
							"contract_type" => $value['contract_type'],
							"media_file_key" => $fileKeyMedia,
							"media_mime_type" => $mimeTypeMedia,
							"thumb_file_key" => $fileKeyThumb,
							"thumb_large_file_key" => $fileKeyThumbLarge,
							"thumb_mime_type" => $mimeTypeThumb,
							"state" => "ready",
							"meta" => $meta
						);
						$wpdb->insert( 'alpn_nft_meta', $nftData );

					} catch (Exception $e) {
						pp("Problem adding an nft to metadata TWO. Dupe?");
						pp($e);
					}
				} else {

					$meta = json_encode(array(
						"name" => $newNft['name'],
						"description" => $newNft['description'],
						"attributes" => $newNft['attributes']
					));

					try {
						$nftData = array (
							"owner_address" => $walletAddress,
							"contract_address" => $value['token_address'],
							"token_id" => $value['token_id'],
							"token_uri" => wsc_cleanup_nft_uri($value['token_uri']),
							"contract_type" => $value['contract_type'],
							"media_file_key" => "",
							"media_mime_type" => "",
							"thumb_file_key" => "",
							"thumb_mime_type" => "",
							"state" => "media_failed",
							"meta" => $meta
						);
						$wpdb->insert( 'alpn_nft_meta', $nftData );
					} catch (Exception $e) {
						pp($e);
						pp($nftData);
					}
				}

			}

		} else { //have file with mimetype but not sure what it is

			pp("FILE NO META");

			//TODO Figure out what it is?

			$fileSettings = array(
				"file_key" => $newNft['file_key'],
				"mime_type" => $newNft['mime_type']
			);

			$nftResponse = wsc_store_nft_file($fileSettings, false);

			if (isset($nftResponse['status']) && $nftResponse['status'] == "ok") {

				$newThumbData = wsc_process_thumb($nftResponse);

				pp($newThumbData);

				$fileKeyThumb = $newThumbData['file_key']; //with extension added
				$mimeTypeThumb = $newThumbData['mime_type']; //with extension added
				$fileKeyThumbLarge = $newThumbData['large_file_key']; //with extension added
				$fileKeyMedia = $nftResponse['file_key']; //with extension added
				$mimeTypeMedia = $nftResponse['mime_type']; //with extension added

				$meta = json_encode(array(
					"name" => $newNft['name'],
					"description" => $newNft['description'],
					"attributes" => $newNft['attributes']
				));

				try {
					$nftData = array (
						"owner_address" => $walletAddress,
						"contract_address" => $value['token_address'],
						"token_id" => $value['token_id'],
						"token_uri" => wsc_cleanup_nft_uri($value['token_uri']),
						"contract_type" => $value['contract_type'],
						"media_file_key" => $fileKeyMedia,
						"media_mime_type" => $mimeTypeMedia,
						"thumb_file_key" => $fileKeyThumb,
						"thumb_large_file_key" => $fileKeyThumbLarge,
						"thumb_mime_type" => $mimeTypeThumb,
						"state" => "ready",
						"meta" => $meta
					);

					pp($nftData);
					//$wpdb->insert( 'alpn_nft_meta', $nftData );

				} catch (Exception $e) {
					pp($e);
					pp($nftData);
				}

			} else {

				$meta = json_encode(array(
					"name" => $newNft['name'],
					"description" => $newNft['description'],
					"attributes" => $newNft['attributes']
				));

				try {
					$nftData = array (
						"owner_address" => $walletAddress,
						"contract_address" => $value['token_address'],
						"token_id" => $value['token_id'],
						"token_uri" => wsc_cleanup_nft_uri($value['token_uri']),
						"contract_type" => $value['contract_type'],
						"media_file_key" => "",
						"media_mime_type" => "",
						"thumb_file_key" => "",
						"thumb_mime_type" => "",
						"state" => "media_failed",
						"meta" => $meta
					);
					$wpdb->insert( 'alpn_nft_meta', $nftData );

				} catch (Exception $e) {
					pp($e);
					pp($nftData);
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
			$nftData = array (
				"owner_address" => $walletAddress,
				"contract_address" => $value['token_address'],
				"token_id" => $value['token_id'],
				"token_uri" => wsc_cleanup_nft_uri($value['token_uri']),
				"contract_type" => $value['contract_type'],
				"media_file_key" => "",
				"media_mime_type" => "",
				"thumb_file_key" => "",
				"thumb_mime_type" => "",
				"state" => "no_urls",
				"meta" => $meta
			);
			$wpdb->insert( 'alpn_nft_meta', $nftData );

		} catch (Exception $e) {
			pp($e);
			pp($nftData);
		}
	}

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
		pp($e);
	}


?>
