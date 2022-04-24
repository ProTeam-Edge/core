<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');

if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}

  $limitOption = 500;
  $walletItems = $allTags = array();

	$userInfo = wp_get_current_user();
	$userId = $userInfo->data->ID;

	$qVars = $_POST;

	$ownerId = (isset($qVars['member_id']) && $qVars['member_id']) ? $qVars['member_id'] : $userId;

	$inMissionControl = $qVars['in_mission_control'];
	$accountsInPlaylist = json_decode(stripslashes($qVars['accounts_in_play']), true);
	$toolbarCount = count($accountsInPlaylist);

	if ((!$inMissionControl && $qVars['member_id']) || ($inMissionControl && $userId)) {

    $walletId = $qVars['account_id'];
    $contractId = $qVars['contract_id'];
		$slideId = $qVars['slide_id'];
		$chainId = $qVars['chain_id'];
		$typeId = $qVars['type_id'];
		$tagId = $qVars['set_id'];  //Nomenclature change. set_id is public facing now.
		$categoryId = $qVars['category_id'];
		$nftQuery = $qVars['nft_query'];

		$whereWallet = "";
		if ($walletId) {
			if ($walletId == 'wsc_all_accounts') {
				if (!$inMissionControl && $toolbarCount > 1) {
					unset($accountsInPlaylist[0]);
					$accountList = "'" . implode("','", $accountsInPlaylist) . "'";
					$whereWallet = "AND owner_address IN ({$accountList}) ";
				} else {
					$whereWallet = "";
				}
			} else {
				$whereWallet = "AND owner_address = '{$walletId}' ";
			}
		}

    if (!$contractId || $contractId == 'wsc_all_contracts') {
      $whereContract = "";
    } else {
      $whereContract = "AND contract_address = '{$contractId}' ";
    }

		if (!$chainId || $chainId == 'wsc_all_chains') {
			$whereChain = "";
		} else {
			$whereChain = "AND chain_id = '{$chainId}' ";
		}

		if (!$typeId || $typeId == 'wsc_all_types') {
			$whereType = "";
		} else if ($typeId == 'image'){
			$whereType = "AND media_mime_type IN ('image/png', 'image/webp', 'image/jpeg', 'image/gif', 'image/svg') ";
		} else if ($typeId == 'music'){
			$whereType = "AND media_mime_type IN ('audio/x-wav', 'audio/mp3', 'audio/ogg', 'audio/mpeg') ";
		} else if ($typeId == 'video'){
			$whereType = "AND media_mime_type IN ('video/mp4', 'video/webm') ";
		}

		if (!$tagId || $tagId == 'wsc_all_tags') {
			$whereTag = "";
		} else if ($tagId) {
			$whereTag = "AND id IN (SELECT nft_id FROM alpn_nft_sets WHERE owner_id = {$ownerId} AND set_name = '{$tagId}') ";
    }

		if ($nftQuery) {
			$whereQuery = "AND (JSON_SEARCH(meta, 'one', '%%" .  $nftQuery . "%%') IS NOT NULL) ";  //%% for wbdb
		} else {
			$whereQuery = "";
		}

		if ($inMissionControl && $categoryId) {
			$whereCategory = "AND category_id = '" . $categoryId . "' ";
		} else {
			$whereCategory = "AND category_id = 'visible' ";
		}

    $whereNoFails = "AND state = 'ready' ";

    $rowLimit = "LIMIT {$limitOption} ";
    $ordering = "ORDER BY contract_address, token_id ";

    $fullQueryLimit = $whereWallet . $whereContract . $whereChain . $whereType . $whereTag . $whereQuery . $whereCategory . $whereNoFails . $ordering . $rowLimit;
		$fullQueryNoLimit = $whereWallet . $whereContract . $whereChain . $whereType . $whereTag . $whereQuery . $whereCategory . $whereNoFails;

		$nftResults = $wpdb->get_results(
			$wpdb->prepare("SELECT id, meta, relation, category_id, thumb_mime_type, media_mime_type, media_file_key, thumb_file_key, thumb_large_file_key, JSON_UNQUOTE(JSON_EXTRACT(moralis_meta, '$.name')) AS contract_name, JSON_UNQUOTE(JSON_EXTRACT(moralis_meta, '$.symbol')) AS contract_symbol FROM alpn_nft_owner_view WHERE owner_id = %d {$fullQueryLimit}", $ownerId)
		 );

     $allSetResults = $wpdb->get_results(
       $wpdb->prepare("SELECT * FROM alpn_nft_sets WHERE owner_id = %d AND nft_id IN (SELECT id FROM alpn_nft_owner_view WHERE owner_id = %d {$fullQueryNoLimit}) ORDER BY nft_id", $ownerId, $ownerId)
      );
      $associativeSets = array();
      foreach ($allSetResults as $setItem) {
        $nftId = $setItem->nft_id;
        $associativeSets[$nftId][] = $setItem->set_name;
      }
		 $galleryHtml = $attributeHtml = "";

		 foreach ($nftResults as $key => $nft) {

       if ($slideId == $nft->id) {
         $wscMeta = "";
       }

			 $attributeHtml = "";

			 $meta = json_decode($nft->meta, true);
			 $name = filter_var($meta['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

       $setValues = array();
       if (isset($associativeSets[$nft->id])) {
         $setValues = $associativeSets[$nft->id];
       }
       $setValues = implode(",", $setValues);

			 $description = wsc_linkify_string(nl2br(filter_var($meta['description'] , FILTER_SANITIZE_FULL_SPECIAL_CHARS)));

			 $attributes = $meta['attributes'];

       $nftRelation = $nft->relation;

			 $attributeHtml .= "<table class='wsc_nft_gallery_attributes'>";
			 foreach ($attributes as $attribute) {
				 $traitType = isset($attribute['trait_type']) && $attribute['trait_type'] ? filter_var($attribute['trait_type'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : "-";
				 $traitValue = isset($attribute['value']) && $attribute['value'] ? wsc_linkify_string(filter_var($attribute['value'], FILTER_SANITIZE_FULL_SPECIAL_CHARS)) : "-";
				 $attributeHtml .= "<tr class='wsc_nft_gallery_attributes_row'><td class='wsc_nft_gallery_attributes_cell_left'>{$traitType}</td><td class='wsc_nft_gallery_attributes_cell_right'>{$traitValue}</td></tr>";
			 }
			 $attributeHtml .= "</table>";

			 $allData = "<div class='wsc_gallery_single_nft_container' data-wsc-rel='{$nftRelation}' data-wsc-set='{$setValues}' data-wsc-cat='{$nft->category_id}' data-wsc-nft='{$nft->id}'><div class='wsc_gallery_nft_name'>{$name}</div><div class='wsc_gallery_nft_description'>{$description}</div><div class='wsc_gallery_nft_attributes'>{$attributeHtml}</div></div>";

			 $thumbMimeType = $nft->thumb_mime_type;
			 $thumbType = getFileMetaFromMimeType($thumbMimeType)['type'];

			 $mediaMimeType = $nft->media_mime_type;
			 $mediaType = getFileMetaFromMimeType($mediaMimeType)['type'];

      $nameWithType = "{$name} | {$mediaIcons[$mediaType]}";

      $nftId = $nft->id;
      $galleryLink = "https://wiscle.com/gallery?member_id={$ownerId}";
      $galleryLink = ($walletId && $walletId != "wsc_all_accounts") ? $galleryLink .= "&account_id={$walletId}" : $galleryLink;
      $galleryLink = ($chainId && $chainId != "wsc_all_chains") ? $galleryLink .= "&chain_id={$chainId}" : $galleryLink;
      $galleryLink = ($contractId && $contractId != "wsc_all_contracts") ? $galleryLink .= "&contract_id={$contractId}" : $galleryLink;
      $galleryLink = ($typeId && $typeId != "wsc_all_types") ? $galleryLink .= "&type_id={$typeId}" : $galleryLink;
      $galleryLink = ($tagId && $tagId != "wsc_all_tags") ? $galleryLink .= "&set_id={$tagId}" : $galleryLink;
      $galleryLink = $nftQuery ? $galleryLink . "&nft_query=" . urlencode($nftQuery) : $galleryLink;
      $galleryLink = $galleryLink . "&slide_id=" . $nftId;

      $downloadLink = PTE_IMAGES_ROOT_URL . $nft->media_file_key;

      $socialText = "Curated for you...";

			 if ($thumbType == "image") {
				 $thumbUrl = PTE_IMAGES_ROOT_URL . $nft->thumb_file_key;
				 $largeThumbUrl = PTE_IMAGES_ROOT_URL . $nft->thumb_large_file_key;
				 $mediaUrl = PTE_IMAGES_ROOT_URL . $nft->media_file_key;
				 if ($mediaType == "image") {
					$galleryHtml .= '<a href="' . $mediaUrl . '" data-sub-html="'  . $allData . '" data-download-url="'  . $downloadLink . '" data-pinterest-share-url="'  . $galleryLink . '" data-facebook-share-url="'  . $galleryLink . '" data-twitter-share-url="'  . $galleryLink . '" data-pinterest-text="'  . $socialText . '" data-facebook-text="'  . $socialText . '" data-tweet-text="'  . $socialText . '" data-nft-id="'  . $nftId . '"><img title="' . $nameWithType . '" src="' . $thumbUrl . '" class="wsc_nft_gallery_thumb"/></a>';
				 } else if ($mediaType == "video") {
					 $dataVideo = json_encode(array(
						 "source" => array(array(
							 "src" => $mediaUrl,
							 "type" => $mediaMimeType,
						 )),
						 "attributes" => array(
               "preload" => 'none',
							 "controls" => true,
							 "poster" => $largeThumbUrl
						 )
					 ));
					 $galleryHtml .= '<a data-video=' . $dataVideo . ' data-sub-html="' . $allData  . '" data-download-url="'  . $downloadLink . '" data-pinterest-share-url="'  . $galleryLink . '" data-facebook-share-url="'  . $galleryLink . '" data-twitter-share-url="'  . $galleryLink . '" data-pinterest-text="'  . $socialText . '" data-facebook-text="'  . $socialText . '" data-tweet-text="'  . $socialText . '" data-nft-id="'  . $nftId . '"><img title="' . $nameWithType . '" src="' . $thumbUrl . '" class="wsc_nft_gallery_thumb"/></a>';
				 } else if ($mediaType == "audio") {

							 $dataVideo = json_encode(array(
								 "source" => array(array(
									 "src" => $mediaUrl,
									 "type" => $mediaMimeType,
								 )),
								 "attributes" => array(
                   "preload" => 'none',
									 "controls" => true,
									 "poster" => $largeThumbUrl
									 )
							 ));
					 $galleryHtml .= '<a data-video=' . $dataVideo . ' data-sub-html="' . $allData  . '" data-download-url="'  . $downloadLink . '" data-pinterest-share-url="'  . $galleryLink . '" data-facebook-share-url="'  . $galleryLink . '" data-twitter-share-url="'  . $galleryLink . '" data-pinterest-text="'  . $socialText . '" data-facebook-text="'  . $socialText . '" data-tweet-text="'  . $socialText . '" data-nft-id="'  . $nftId . '"><img title="' . $nameWithType . '" src="' . $thumbUrl . '" class="wsc_nft_gallery_thumb"/></a>';
				 }
			 } else if ($thumbType == "audio") {
				 $thumbUrl = PTE_IMAGES_ROOT_URL ."thumb_wiscle_audio_background_04061968.webp";
				 $largeThumbUrl = PTE_IMAGES_ROOT_URL . "large_wiscle_audio_background_04061968.webp";
				 $mediaUrl = PTE_IMAGES_ROOT_URL . $nft->media_file_key;

				 $dataVideo = json_encode(array(
					 "source" => array(array(
						 "src" => $mediaUrl,
						 "type" => $mediaMimeType,
					 )),
					 "attributes" => array(
             "preload" => 'none',
						 "controls" => true,
						 "poster" => $largeThumbUrl
						 )
				 ));
				$galleryHtml .= '<a data-video=' . $dataVideo . ' data-sub-html="' . $allData  . '" data-download-url="'  . $downloadLink . '" data-pinterest-share-url="'  . $galleryLink . '" data-facebook-share-url="'  . $galleryLink . '" data-twitter-share-url="'  . $galleryLink . '" data-pinterest-text="'  . $socialText . '" data-facebook-text="'  . $socialText . '" data-tweet-text="'  . $socialText . '" data-nft-id="'  . $nftId . '"><img title="' . $nameWithType . '" src="' . $thumbUrl . '" class="wsc_nft_gallery_thumb"/></a>';
			} else if ($thumbType == "video") {
					$thumbUrl = PTE_IMAGES_ROOT_URL ."thumb_wiscle_video_background_04061968.webp";
					$largeThumbUrl = PTE_IMAGES_ROOT_URL . "large_wiscle_video_background_04061968.webp";
					$mediaUrl = PTE_IMAGES_ROOT_URL . $nft->media_file_key;

					 $dataVideo = json_encode(array(
						 "source" => array(array(
							 "src" => $mediaUrl,
							 "type" => $mediaMimeType,
						 )),
						 "attributes" => array(
							 "preload" => 'none',
							 "controls" => true,
							 "poster" => $largeThumbUrl
						 )
					 ));
					 $galleryHtml .= '<a data-video=' . $dataVideo . ' data-sub-html="' . $allData  . '" data-download-url="'  . $downloadLink . '" data-pinterest-share-url="'  . $galleryLink . '" data-facebook-share-url="'  . $galleryLink . '" data-twitter-share-url="'  . $galleryLink . '" data-pinterest-text="'  . $socialText . '" data-facebook-text="'  . $socialText . '" data-tweet-text="'  . $socialText . '" data-nft-id="'  . $nftId . '"><img title="' . $nameWithType . '" src="' . $thumbUrl . '" class="wsc_nft_gallery_thumb"/></a>';
			}
		 }
		 //setup lists every time through
		 // if ($toolbarCount == 1) {  //todo could switch to selecting in list subsequent times.

			 if ($inMissionControl) {
				 //get saved settings. Set defaults.

				 $walletData = $wpdb->get_results(
					 $wpdb->prepare("SELECT w.*, r.relation FROM alpn_wallet_relationships r LEFT JOIN alpn_wallet_meta w ON w.account_address = r.account_address WHERE r.owner_id = %d ORDER BY friendly_name", $userId)
					);
				 $walletItems = array();
				 if (isset($walletData[0])) {
					 foreach($walletData as $walletItem) {
						 $walletItems[] = array("address" => $walletItem->account_address, "friendly_name" => $walletItem->friendly_name, "selected" => ($walletItem->account_address == $walletId) ? true : false);
					 }
				}
				 $TagData = $wpdb->get_results(
					 $wpdb->prepare("SELECT DISTINCT set_name from alpn_nft_sets WHERE owner_id = %d ORDER BY set_name", $userId)
					);
				 $allTags = array();
				 if (isset($TagData[0])) {
					 foreach($TagData as $tagItem) {
             $allTags[] = array("tag" => $tagItem->set_name, "selected" => ($tagItem->set_name == $tagId) ? true : false);
					 }
				 }

         $contractData = $wpdb->get_results(
					 $wpdb->prepare("SELECT DISTINCT contract_address, JSON_UNQUOTE(JSON_EXTRACT(moralis_meta, '$.name')) AS contract_name from alpn_nft_meta WHERE id IN (SELECT id FROM alpn_nft_owner_view WHERE owner_id = %d {$fullQueryNoLimit}) ORDER BY contract_name", $userId)
					);
				 $allContracts = array();
				 if (isset($contractData[0])) {
					 foreach($contractData as $contractItem) {
             if ($contractItem->contract_name && $contractItem->contract_name != 'null') {
               $allContracts[] = array("contract_address" => $contractItem->contract_address, "contract_name" => $contractItem->contract_name, "selected" => ($contractItem->contract_address == $contractId) ? true : false);
             }
					 }
				 }

			 } else { //in gallery
				 //Figure out account list
				 $uniqueAccounts = $wpdb->get_results(
					 $wpdb->prepare("SELECT DISTINCT owner_address, friendly_name FROM alpn_nft_owner_view WHERE owner_id = %d {$fullQueryLimit} ORDER BY friendly_name", $ownerId)
					);
					$walletItems = array();
 				 if (isset($uniqueAccounts[0])) {
	 					 foreach($uniqueAccounts as $walletItem) {
							 $walletItems[] = array("address" => $walletItem->owner_address, "friendly_name" => $walletItem->friendly_name, "selected" => ($walletItem->owner_address == $walletId) ? true : false);
	 					 }
	 				}
					$TagDataInUse = $wpdb->get_results(
 					 $wpdb->prepare("SELECT DISTINCT set_name from alpn_nft_sets WHERE owner_id = %d AND nft_id IN (SELECT id FROM alpn_nft_owner_view WHERE owner_id = %d {$fullQueryNoLimit})", $ownerId, $ownerId)
 					);
          $allTags = array();
          if (isset($TagDataInUse[0])) {
            foreach($TagDataInUse as $tagItem) {
              $allTags[] = array("tag" => $tagItem->set_name, "selected" => ($tagItem->set_name == $tagId) ? true : false);
            }
          }
          $contractData = $wpdb->get_results(
 					 $wpdb->prepare("SELECT DISTINCT contract_address, JSON_UNQUOTE(JSON_EXTRACT(moralis_meta, '$.name')) AS contract_name from alpn_nft_meta WHERE id IN (SELECT id FROM alpn_nft_owner_view WHERE owner_id = %d {$fullQueryNoLimit}) ORDER BY contract_name", $userId)
 					);
          $allContracts = array();

 				 if (isset($contractData[0])) {
 					 foreach($contractData as $contractItem) {
             if ($contractItem->contract_name && $contractItem->contract_name != 'null') {
               $allContracts[] = array("contract_address" => $contractItem->contract_address, "contract_name" => $contractItem->contract_name, "selected" => ($contractItem->contract_address == $contractId) ? true : false);
             }
 					 }
 				 }
			 }  //end in gallery
		 // }

	} else {
		//No Owner ID
		$galleryHtml = "Invalid URL";
	}

pte_json_out(array("html" => $galleryHtml, "account_list" => $walletItems, "tag_list" => $allTags, "contract_list" => $allContracts));

?>
