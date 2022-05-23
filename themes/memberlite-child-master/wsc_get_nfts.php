<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');

if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}

  $walletItems = $allTags = array();

  $chains = array(
    "eth" => "Ethereum",
    "polygon" => "Polygon"
  );

	$userInfo = wp_get_current_user();
	$userId = $userInfo->data->ID;

	$qVars = $_POST;
	$ownerId = (isset($qVars['member_id']) && $qVars['member_id']) ? $qVars['member_id'] : $userId;

	$inMissionControl = $qVars['in_mission_control'];  //double as topic ID
	$accountsInPlaylist = json_decode(stripslashes($qVars['accounts_in_play']), true);
	$toolbarCount = count($accountsInPlaylist);

	if ((!$inMissionControl && $qVars['member_id']) || ($inMissionControl && $userId)) {

    $queryArray = wsc_get_nft_query($qVars);

    $fullQueryLimit = $queryArray["full_query_limit"];
    $fullQueryLimitListOrder = $queryArray["full_query_limit_list_order"];
    $fullQueryNoLimit = $queryArray["full_query_no_limit"];
    $fullQueryNoLimitAllContracts = $queryArray["full_query_no_limit_all_contracts"];

		$nftResults = $wpdb->get_results(
			$wpdb->prepare("SELECT id, meta, contract_address, token_id, chain_id, account_address, friendly_name, ens_address, relation, category_id, thumb_mime_type, media_mime_type, media_file_key, thumb_file_key, thumb_large_file_key, JSON_UNQUOTE(JSON_EXTRACT(moralis_meta, '$.name')) AS contract_name, JSON_UNQUOTE(JSON_EXTRACT(moralis_meta, '$.symbol')) AS contract_symbol FROM alpn_nft_owner_view WHERE owner_id = %d {$fullQueryLimit}", $ownerId)
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
       $associativeLinks = "";
       if (isset($associativeSets[$nft->id])) {
         $setValues = $associativeSets[$nft->id];
         foreach ($setValues as $setItem) {
           $associativeLinks .= "<a onclick='wsc_handle_filter(`set`, `{$setItem}`);' class='wsc_filter_link'>{$setItem}</a> &nbsp; ";
         }
       }
       $setValues = implode(",", $setValues);

			 $description = wsc_linkify_string(nl2br(filter_var($meta['description'] , FILTER_SANITIZE_FULL_SPECIAL_CHARS)));

			 $attributes = $meta['attributes'];

       $nftRelation = $nft->relation;

       $accountFriendlyName = $nft->friendly_name ? $nft->friendly_name . " | " : "";
       $accountEnsName = $nft->ens_address ? $nft->ens_address . " | " : "";
       $nftOwner = $accountFriendlyName . $accountEnsName . $nft->account_address;

       $contractName = $nft->contract_name && $nft->contract_name != 'null' ? $nft->contract_name : "-";
       $contractSymbol = $nft->contract_symbol && $nft->contract_symbol != 'null' ? $nft->contract_symbol : "-";

       $mediaMimeType = $nft->media_mime_type ? $nft->media_mime_type : " -";
       $mediaInfo = getFileMetaFromMimeType($mediaMimeType);
       $mediaExtension = $mediaInfo['extension'];
       $mediaType = $mediaInfo['type'];
       $mediaAll = $mediaType . " | "  . $mediaExtension;
       $mediaAll = "<a onclick='wsc_handle_filter(`media_type`);' class='wsc_filter_link'>{$mediaAll}</a>";
       $chainName = $chains[$nft->chain_id];

       if ($nft->chain_id == "eth") {
         $openInScan = "https://etherscan.io/token/{$nft->contract_address}?a={$nft->token_id}";
         $openInScanName = "Etherscan";
         $openInRarible = "https://rarible.com/token/{$nft->contract_address}:{$nft->token_id}";
         $openInOpenSea = "https://opensea.io/assets/{$nft->contract_address}/{$nft->token_id}";
       } else if ($nft->chain_id == "polygon") {
         $openInScan = "https://polygonscan.com/token/{$nft->contract_address}?a={$nft->token_id}";
         $openInScanName = "Polygonscan";
         $openInRarible = "https://rarible.com/token/polygon/{$nft->contract_address}:{$nft->token_id}";
         $openInOpenSea = "https://opensea.io/assets/matic/{$nft->contract_address}/{$nft->token_id}";
       }

       $infoPanel = "
          <div class='wsc_nft_info_container'>
            <div class='wsc_nft_info_inner'>
              <div class='wsc_nft_info_row'>
                <div class='pte_vault_row_15 wsc_nft_info_title_column'>
                    Contract
                </div>
                <div class='pte_vault_row_35 wsc_nft_info_data_column wsc_space_right'>
                    <a onclick='wsc_handle_filter(`contract`);' class='wsc_filter_link'>{$contractName}</a>
                </div>
                <div class='pte_vault_row_15 wsc_nft_info_title_column wsc_space_left'>
                    Symbol
                </div>
                <div class='pte_vault_row_35 wsc_nft_info_data_column'>
                  <a onclick='wsc_handle_filter(`contract`);' class='wsc_filter_link'>{$contractSymbol}</a>
                </div>
              </div>
              <div class='wsc_nft_info_row'>
                <div class='pte_vault_row_15 wsc_nft_info_title_column'>
                    Chain
                </div>
                <div class='pte_vault_row_35 wsc_nft_info_data_column wsc_space_right'>
                  <a onclick='wsc_handle_filter(`chain`);' class='wsc_filter_link'>{$chainName}</a>
                </div>
                <div class='pte_vault_row_15 wsc_nft_info_title_column wsc_space_left'>
                  Media
                </div>
                <div class='pte_vault_row_35 wsc_nft_info_data_column'>
                  {$mediaAll}
                </div>
              </div>
              <div class='wsc_nft_info_row'>
                <div class='pte_vault_row_15 wsc_nft_info_title_column'>
                  Owner
                </div>
                <div class='pte_vault_row_35 wsc_nft_info_data_column wsc_space_right wsc_link_color'>
                  <a onclick='wsc_handle_filter(`account_address`);' class='wsc_filter_link'>{$nftOwner}</a>
                </div>
                <div class='pte_vault_row_15 wsc_nft_info_title_column wsc_space_left'>
                  Sets
                </div>
                <div class='pte_vault_row_35 wsc_nft_info_data_column'>
                {$associativeLinks}
                </div>
              </div>
            <div class='wsc_nft_info_row'>
            <div class='pte_vault_row_100'><span class='wsc_nav_label'>View on:</span><a class='wsc_nav_link' href='{$openInOpenSea}' target='_blank'>OpenSea</a><a class='wsc_nav_link' href='{$openInRarible}' target='_blank'>Rarible</a><a class='wsc_nav_link' href='{$openInScan}' target='_blank'>{$openInScanName}</a></div>
            </div>
            </div>
          </div>
       ";

			 $attributeHtml .= "<table class='wsc_nft_gallery_attributes'>";
			 foreach ($attributes as $attribute) {
				 $traitType = isset($attribute['trait_type']) && $attribute['trait_type'] ? filter_var($attribute['trait_type'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : "-";
				 $traitValue = isset($attribute['value']) && $attribute['value'] ? wsc_linkify_string(filter_var($attribute['value'], FILTER_SANITIZE_FULL_SPECIAL_CHARS)) : "-";
				 $attributeHtml .= "<tr class='wsc_nft_gallery_attributes_row'><td class='wsc_nft_gallery_attributes_cell_left'>{$traitType}</td><td class='wsc_nft_gallery_attributes_cell_right'>{$traitValue}</td></tr>";
			 }
			 $attributeHtml .= "</table>";

			 $allData = "<div class='wsc_gallery_single_nft_container' data-wsc-rel='{$nftRelation}' data-wsc-set='{$setValues}' data-wsc-cat='{$nft->category_id}' data-wsc-nft='{$nft->id}' data-wsc-contract='{$nft->contract_address}' data-wsc-owner='{$nft->account_address}' data-wsc-chain='{$nft->chain_id}' data-wsc-media-type='{$mediaType}'><div class='wsc_gallery_nft_name'>{$name}</div><div class='wsc_gallery_nft_description'>{$description}</div>{$infoPanel}<div class='wsc_gallery_nft_attributes'>{$attributeHtml}</div></div>";

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
      $galleryLink = $galleryLink . "&open_nav=1";  //open to individual NFT view

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

			 if ($inMissionControl) {

				 $walletData = $wpdb->get_results(
					 $wpdb->prepare("SELECT w.*, r.relation FROM alpn_wallet_relationships r LEFT JOIN alpn_wallet_meta w ON w.account_address = r.account_address WHERE r.owner_id = %d ORDER BY CASE WHEN w.friendly_name != '' THEN w.friendly_name ELSE CASE WHEN w.ens_address != '' THEN w.ens_address ELSE w.account_address END END ", $userId)
					);

				 $walletItems = array();
				 if (isset($walletData[0])) {
					 foreach($walletData as $walletItem) {
						 $walletItems[] = array("address" => $walletItem->account_address, "ens_address" => $walletItem->ens_address, "friendly_name" => $walletItem->friendly_name, "selected" => (strtolower($walletItem->account_address) == strtolower($walletId)) ? true : false);
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

         //save state// TODO only if user, not topic visitor.
         $filterElements = array(
           'account_id' => $walletId,
           'contract_id' => $contractId,
           'chain_id' => $chainId,
           'type_id' => $typeId,
           'set_id' => $tagId,
           'category_id' => $categoryId
         );

         $newNftViewStateData = array("nft_view_state" => json_encode($filterElements));
         $whereClause = array('id' => $inMissionControl, "owner_id" => $userId);
         $wpdb->update( 'alpn_topics', $newNftViewStateData, $whereClause );

			 } else { //in gallery
				 //Figure out account list
				 $uniqueAccounts = $wpdb->get_results(
					 $wpdb->prepare("SELECT DISTINCT owner_address, friendly_name, ens_address FROM alpn_nft_owner_view WHERE owner_id = %d {$fullQueryLimitListOrder}", $ownerId)
					);

					$walletItems = array();
 				 if (isset($uniqueAccounts[0])) {
	 					 foreach($uniqueAccounts as $walletItem) {
							 $walletItems[] = array("address" => $walletItem->owner_address, "ens_address" => $walletItem->ens_address, "friendly_name" => $walletItem->friendly_name, "selected" => (strtolower($walletItem->owner_address) == strtolower($walletId)) ? true : false);
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

			 }  //end in gallery

	} else {
		//No Owner ID
		$galleryHtml = "Invalid URL";
	}

pte_json_out(array("html" => $galleryHtml, "account_list" => $walletItems, "tag_list" => $allTags));

?>
