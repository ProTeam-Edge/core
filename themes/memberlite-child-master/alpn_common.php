<?php
date_default_timezone_set('UTC');

include_once('/var/www/html/proteamedge/private/pte_config.php');
require 'vendor/autoload.php';

use Google\Cloud\Storage\StorageClient;
use Twilio\Rest\Client;
use PascalDeVink\ShortUuid\ShortUuid;
use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseUser;
use Parse\ParseException;
use Parse\ParseClient;
use Parse\ParseCloud;
use Ramsey\Uuid\Uuid;
use enshrined\svgSanitize\Sanitizer;
use sybio\GifFrameExtractor\GitFrameExtractor;
use GDText\Box;
use GDText\Color;
use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;
use Throwable;

function test_OpenSea() {



}

function wsc_start_nft_media_processing($data) {
  alpn_log("STARTING MEDIA PROCESSING");
  $verificationKey = pte_get_short_id();
  $params = array(
    "verification_key" => $verificationKey
  );
  vit_store_kvp($verificationKey, $data);
  try {
    pte_async_job(PTE_ROOT_URL . "wsc_async_process_nft_media.php", array('verification_key' => $verificationKey));
  } catch (Exception $e) {
    alpn_log ($e);
  }
}

function wsc_overlay_wtc() {
  //400 x 560
  //310, 43, 65, 60 -- circle
  //80, 385, 273, 31 -- title
  //41, 426, 311, 66 -- attributes

}

function wsc_to_0xid($friendlyId){  //TODO make better

  if ($friendlyId == 'eth') {return "0x1";};
  if ($friendlyId == 'polygon') {return "0x89";};

}

function wsc_call_cloud_function($data = array()) {   //TODO make this smarter

  $cloudFunction = $data['cloud_function'];
  $encKey = isset($data['enc_key']) ? urlencode($data['enc_key']) : "";
  $encIv = isset($data['enc_iv']) ? urlencode($data['enc_iv']) : "";
  $privateKeyEnc = isset($data['pk_enc']) ? urlencode($data['pk_enc']) : "";
  $chainId = isset($data['chain_id']) ? $data['chain_id'] : "";
  $contractTemplateId = isset($data['contract_template_id']) ? $data['contract_template_id'] : "";
  $contractName = isset($data['contract_name']) ? urlencode($data['contract_name']) : "";
  $contractSymbol = isset($data['contract_symbol']) ? urlencode($data['contract_symbol']) : "";

  $nftTokenUri = isset($data['nft_token_uri']) ? urlencode($data['nft_token_uri']) : "";
  $nftAccountAddress = isset($data['nft_account_address']) ? $data['nft_account_address'] : "";
  $nftContractAddress = isset($data['nft_contract_address']) ? $data['nft_contract_address'] : "";
  $nftTokenId = isset($data['nft_token_id']) ? $data['nft_token_id'] : "";
  $nftQuantity = isset($data['nft_quantity']) ? $data['nft_quantity'] : "1";
  $nftRecipientAddress = isset($data['nft_recipient_id']) ? $data['nft_recipient_id'] : "";
  $nftTemplateKey = isset($data['nft_template_key']) ? $data['nft_template_key'] : "";

  $security = MORALIS_EXTRA_SECURITY;
  $moralisAppId = MORALIS_APPID;
  $moralisServerUrl = MORALIS_SERVER_URL;
  $headers = array();
  $fullUrl = "{$moralisServerUrl}/server/functions/{$cloudFunction}?_ApplicationId={$moralisAppId}&security={$security}&pkenc={$privateKeyEnc}&ntk={$nftTemplateKey}&nraa={$nftRecipientAddress}&nqty={$nftQuantity}&nti={$nftTokenId}&nca={$nftContractAddress}&naa={$nftAccountAddress}&ntu={$nftTokenUri}&ctid={$contractTemplateId}&enck={$encKey}&enci={$encIv}&chid={$chainId}&cname={$contractName}&csymb={$contractSymbol}";
  $options = array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_URL => $fullUrl
  );
   $ch = curl_init();
   curl_setopt_array($ch, $options);
   $response = curl_exec($ch);
   curl_close($ch);
   return $response;
}


function wsc_encrypt_string($simple_string, $secret = '') {

  alpn_log("ENC");
  alpn_log($simple_string);

  $secret = $secret ? base64_decode($secret) : openssl_random_pseudo_bytes(32);
  $iv = base64_decode(MORALIS_ENCRYPT_IV);

  $encryptionMethod = "AES-256-CBC";
  $encryptedMessage = openssl_encrypt($simple_string, $encryptionMethod, $secret, 0, $iv);

  return array("encrypted" => $encryptedMessage, "secret" => base64_encode($secret));
}

function wsc_decrypt_string($encryption, $secret, $iv) {
  $encryptionMethod = "AES-256-CBC";
  $decryptedMessage = openssl_decrypt($encryption, $encryptionMethod, $secret, 0, $iv);

  return $decryptedMessage;
}


function wsc_get_nft_query($qVars) {

    $limitOption = 500;

  	$userInfo = wp_get_current_user();
  	$userId = $userInfo->data->ID;

  	$ownerId = (isset($qVars['member_id']) && $qVars['member_id']) ? $qVars['member_id'] : $userId;

  	$inMissionControl = $qVars['in_mission_control'];  //double as topic ID
  	$accountsInPlaylist = json_decode(stripslashes($qVars['accounts_in_play']), true);
  	$toolbarCount = count($accountsInPlaylist);

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
    $ordering = "ORDER BY contract_address DESC, token_id ASC ";

    $listOrdering = "ORDER BY CASE WHEN friendly_name != '' THEN friendly_name ELSE CASE WHEN ens_address != '' THEN ens_address ELSE account_address END END ";

    $fullQueryLimit = $whereWallet . $whereContract . $whereChain . $whereType . $whereTag . $whereQuery . $whereCategory . $whereNoFails . $ordering . $rowLimit;
    $fullQueryLimitListOrder = $whereWallet . $whereContract . $whereChain . $whereType . $whereTag . $whereQuery . $whereCategory . $whereNoFails . $listOrdering . $rowLimit;
		$fullQueryNoLimit = $whereWallet . $whereContract . $whereChain . $whereType . $whereTag . $whereQuery . $whereCategory . $whereNoFails;
    $fullQueryNoLimitAllContracts = $whereWallet . $whereChain . $whereType . $whereTag . $whereQuery . $whereCategory . $whereNoFails;

    return array(
      "full_query_limit" => $fullQueryLimit,
      "full_query_limit_list_order" => $fullQueryLimitListOrder,
      "full_query_no_limit" => $fullQueryNoLimit,
      "full_query_no_limit_all_contracts" => $fullQueryNoLimitAllContracts
    );
}


function wsc_get_contracts_for_query($fullQueryNoLimitAllContracts, $queryFilter, $contractId = "", $page) {

  global $wpdb;
  $contractCount = 0;
  $pageSize = 50;
  $offset = $pageSize * ($page - 1);

  if ($queryFilter) {
    $queryFilter = "JSON_EXTRACT(moralis_meta, '$.name') LIKE '%%{$queryFilter}%%'";
  } else {
    $queryFilter = "JSON_EXTRACT(moralis_meta, '$.name') IS NOT NULL";
  }

  $contractData = $wpdb->get_results(
    $wpdb->prepare("SELECT DISTINCT contract_address, JSON_UNQUOTE(JSON_EXTRACT(moralis_meta, '$.name')) AS contract_name from alpn_nft_owner_view WHERE {$queryFilter} {$fullQueryNoLimitAllContracts} ORDER BY contract_name LIMIT {$offset},{$pageSize}")
   );

  $allContracts = array();
  if ($page == 1) {
    $allContracts[] = array("id" => "wsc_all_contracts", "text" => "All Contracts");
  }
  if (isset($contractData[0])) {
    foreach($contractData as $contractItem) {
      if ($contractItem->contract_name && $contractItem->contract_name != 'null') {
        $allContracts[] = array("id" => $contractItem->contract_address, "text" => $contractItem->contract_name, "selected" => ($contractItem->contract_address == $contractId) ? true : false);
      }
    }
    $contractCount = $wpdb->get_var( "SELECT count(DISTINCT (contract_address)) as contract_count from alpn_nft_owner_view WHERE JSON_EXTRACT(moralis_meta, '$.name') IS NOT NULL {$fullQueryNoLimitAllContracts}" );
  }

return array("all_contracts" => $allContracts, "total_count" => $contractCount);


}


function wsc_linkify_string($string) {
  return  preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', "<a href='$1://$2' target='_blank'>$1://$2</a>", $string);
}

function wsc_resolve_ens($address) {
  $headers = array();
  $moralisApiKey = MORALIS_API_KEY;
  $fullUrl = "https://deep-index.moralis.io/api/v2/resolve/{$address}/reverse";
  $headers[] = "Accept: application/json";
  $headers[] = "X-API-Key: {$moralisApiKey}";
  $options = array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_URL => $fullUrl,
      CURLOPT_HTTPHEADER => $headers
  );
   $ch = curl_init();
   curl_setopt_array($ch, $options);
   $response = curl_exec($ch);
   curl_close($ch);
   return $response;
}

function wsc_process_thumb($thumbData){

$filepath = $oldFilePath = PTE_ROOT_PATH . "tmp/" . $thumbData['original_file_key'];

$sourceFileKey = $thumbData['file_key'];
$sourceMimeType = $thumbData['mime_type'];


alpn_log($filepath);
alpn_log($sourceFileKey);

if (isset($thumbData['status']) && $thumbData['status'] == 'ok') {

  $fileType = getFileMetaFromMimeType($thumbData['mime_type'])['type'];

  if ($fileType == 'video') {

    $newThumb = PTE_ROOT_PATH . "tmp/" . $thumbData['original_file_key'] . ".jpeg";

    try {
          $ffmpeg = FFMpeg\FFMpeg::create();
          $video = $ffmpeg->open($filepath);
          $video
              ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(4))
              ->addFilter(new FFMpeg\Filters\Frame\CustomFrameFilter('scale=800:-1'))
              ->save($newThumb, true);
          $filepath = $newThumb;
          $thumbData['mime_type'] = mime_content_type($filepath);
    } catch (Exception $e) {
      alpn_log($e);
    }
  }

  if ($thumbData['mime_type'] == "image/png" || $thumbData['mime_type'] == "image/jpeg" || $thumbData['mime_type'] == "image/gif" || $thumbData['mime_type'] == "image/webp") {

    $fileNameWithExtension = "thumb_" . $thumbData['original_file_key'] . ".webp";
    $thumbpath = PTE_ROOT_PATH . "tmp/". $fileNameWithExtension;
    imageToWebp($filepath, $thumbpath, 200);
    $thumbFileSize = @filesize($thumbpath);

    $fileNameWithExtensionLarge = "large_" . $thumbData['original_file_key'] . ".webp";
    $thumbpathLarge = PTE_ROOT_PATH . "tmp/". $fileNameWithExtensionLarge;
    imageToWebp($filepath, $thumbpathLarge, 800);
    $thumbLargeFileSize = @filesize($thumbpathLarge);

    $fileNameWithExtensionShare = "share_" . $thumbData['original_file_key'] . ".jpeg";
    $thumbpathShare = PTE_ROOT_PATH . "tmp/". $fileNameWithExtensionShare;
    imageToJpeg($filepath, $thumbpathShare, 480);
    $thumbShareFileSize = @filesize($thumbpathShare);

    if ($thumbFileSize && $thumbLargeFileSize && $thumbShareFileSize) {

        try {
          $storage = new StorageClient([
              'keyFilePath' => GOOGLE_STORAGE_KEY
          ]);

          $bucketName = 'pte_media_store_1';
          $bucket = $storage->bucket($bucketName);

          $object = $bucket->upload(
              fopen($thumbpath, 'r'),
              ['name' => $fileNameWithExtension]
          );

          $object = $bucket->upload(
              fopen($thumbpathLarge, 'r'),
              ['name' => $fileNameWithExtensionLarge]
          );

          $object = $bucket->upload(
              fopen($thumbpathShare, 'r'),
              ['name' => $fileNameWithExtensionShare]
          );

          $thumbData['file_key'] = $fileNameWithExtension;
          $thumbData['large_file_key'] = $fileNameWithExtensionLarge;    //Storing so easier to change on case by case basis
          $thumbData['share_file_key'] = $fileNameWithExtensionShare;    //Storing so easier to change on case by case basis
          $thumbData['mime_type'] = "image/webp";

          unlink($thumbpath);
          unlink($thumbpathLarge);
          unlink($thumbpathShare);

        } catch (Exception $e) {
          alpn_log("FAILED EXCEPTION");
          alpn_log($e);
          alpn_log($thumbData);
          $thumbData['file_key'] = "";
          $thumbData['large_file_key'] = "";
          $thumbData['share_file_key'] = "";
          $thumbData['mime_type'] = "";
        }

      } else {  //failed creating viable thumbs
        $fileType = getFileMetaFromMimeType($thumbData['mime_type'])['type'];

        alpn_log('FAILED CREATING THUMBS');

        if ($fileType == 'image') {
          $thumbData['file_key'] = $thumbData['large_file_key'] = $thumbData['share_file_key'] = $sourceFileKey;
          $thumbData['mime_type'] = $sourceMimeType;
        } else {
          $thumbData['file_key'] = "";
          $thumbData['large_file_key'] = "";
          $thumbData['share_file_key'] = "";
          $thumbData['mime_type'] = "";
        }
      }
  } else if ($thumbData['mime_type'] == "image/svg") {
    alpn_log("THUMB COULD NOT FIND RASTER IMAGE BUT FOUND SVG");
    $fileNameWithExtension = $thumbData['original_file_key'] . ".svg";
    $thumbData['file_key'] = $fileNameWithExtension;
    $thumbData['large_file_key'] = $fileNameWithExtension;
    $thumbData['share_file_key'] = $fileNameWithExtension;
    $thumbData['mime_type'] = "image/svg";
  } else {
    $thumbData['file_key'] = "";
    $thumbData['large_file_key'] = "";
    $thumbData['share_file_key'] = "";
    $thumbData['mime_type'] = "";
  }
}


unlink($filepath);

if ($filepath != $oldFilePath) {
  unlink($oldFilePath);
}


return $thumbData;

}

function wsc_create_vid_from_images($imageArray) {

  $newVideoFileName = pte_get_short_id() . ".mp4";
  $videoFilePath = PTE_ROOT_PATH . 'tmp/' . $newVideoFileName;
  $loopCommandsStr = $blendStr = $commandStr = "";
  for ($key = 0; $key < count($imageArray); $key++) {


    //get it.
    //make it 800x800
    //save temp file


    $imageItem = $imageArray[$key];
    $filePath = PTE_ROOT_PATH . 'tmp/' . $imageItem;
    $loopCommandsStr .= '-loop 1 -t 1.0 -i "' . $filePath . '" ';

    if ($key < count($imageArray) - 1) {
      $nextKey = $key + 1;
      $blendStr .= "[{$nextKey}:v][{$key}:v]blend=all_expr='A*(if(gte(T,0.5),1,T/0.5))+B*(1-(if(gte(T,0.5),1,T/0.5)))'[b{$nextKey}v]; ";
      $commandStr .= "[{$key}:v][b{$nextKey}v]";
    }
  }
  $imageCount = count($imageArray);
  $commandStr .= "[" . ($imageCount - 1) . ":v]";
  $segmentCount = $imageCount * 2 - 1;

  $execStr = 'ffmpeg -framerate 20 ';
  $execStr .= $loopCommandsStr;
  $execStr .= '-c:v libx264 ';
  $execStr .= '-filter_complex "';
  $execStr .= $blendStr;
  $execStr .= $commandStr . 'concat=n=' . $segmentCount . ':v=1:a=0,format=yuv420p[v]" -map "[v]" "' . $videoFilePath . '"';

  $results = shell_exec($execStr);

  pp($results);

  // $output = shell_exec("php -v");
  //
  // pp($output);


  return $newVideoFileName;

}

function wsc_create_pfp_from_set($previewId, $setId) {
  global $wpdb;

  $userInfo = wp_get_current_user();
  $userId = $userInfo->data->ID;

  $nfts = $wpdb->get_results(
    $wpdb->prepare("SELECT n.* from alpn_nft_meta n LEFT JOIN alpn_nft_sets s ON s.nft_id = n.id WHERE s.owner_id = %d AND s.set_name = %s ORDER BY s.id LIMIT 1", $userId, $setId)
   );

   if (isset($nfts[0]) && $nfts[0]->thumb_share_file_key) {
     try {

       $destinationFile = WSC_PREVIEWS_PATH . $previewId;
       $nftUrl = PTE_IMAGES_ROOT_URL . $nfts[0]->thumb_share_file_key;
       file_put_contents($destinationFile, file_get_contents($nftUrl));
       return true;

     } catch (Exception $e) {
       alpn_log("FAILED CREATING PFP FROM SET");
     }
   }

   return false;

}


function wsc_prepare_tweet_elements($fileId, $setName, $action, $words) {
  global $wpdb;

  $userInfo = wp_get_current_user();
  $userId = $userInfo->data->ID;

  $nfts = $wpdb->get_results(
    $wpdb->prepare("SELECT n.* from alpn_nft_meta n LEFT JOIN alpn_nft_sets s ON s.nft_id = n.id WHERE s.owner_id = %d AND s.set_name = %s ORDER BY RAND()", $userId, $setName)
   );

   if (isset($nfts[0])) {

     try {
       $imageCounter = 0;
       foreach ($nfts as $key => $nft) {
         if ($nft->thumb_mime_type == "image/webp") {
           $previewId = $fileId . "_{$imageCounter}.webp";
           $destinationFile = WSC_PREVIEWS_PATH . $previewId;
           $nftUrl = PTE_IMAGES_ROOT_URL . $nft->thumb_large_file_key;
           file_put_contents($destinationFile, file_get_contents($nftUrl));
           if ($imageCounter > 3) {break;}
           $imageCounter++;
         }
       }
       return $imageCounter;
     } catch (Exception $e) {
       alpn_log("FAILED PREPARING TWEET ELEMENTS");
     }

   }
   return false;
}

function wsc_get_twitter_preview_art($setName, $action, $uniqueId, $words = "") {

  global $wpdb;

  $html = "";
  $useWordsClass = "wsc_owner_tools_on";

  switch ($action)
  {
      case "tweet":
      $picCount = wsc_prepare_tweet_elements($uniqueId, $setName, $action, $words);
      if ($picCount) {
        $imageOneUrl = WSC_PREVIEWS_URL . "{$uniqueId}_0.webp?" . time();
        $imageTwoUrl = WSC_PREVIEWS_URL . "{$uniqueId}_1.webp?" . time();
        $imageThreeUrl = WSC_PREVIEWS_URL . "{$uniqueId}_2.webp?" . time();
        $imageFourUrl = WSC_PREVIEWS_URL  . "{$uniqueId}_3.webp?" . time();
        $imageOnePath = WSC_PREVIEWS_PATH . "{$uniqueId}_0.webp";
        $imageTwoPath = WSC_PREVIEWS_PATH . "{$uniqueId}_1.webp";
        $imageThreePath = WSC_PREVIEWS_PATH . "{$uniqueId}_2.webp";
        $imageFourPath = WSC_PREVIEWS_PATH  . "{$uniqueId}_3.webp";
        switch ($picCount) {
            case 1:
              if(filesize($imageTwoPath)) {unlink($imageTwoPath);}
              if(filesize($imageThreePath)) {unlink($imageThreePath);}
              if(filesize($imageFourPath)) {unlink($imageFourPath);}
              $html = "<div class='wsc_nft_ww_row'>
                         <div class='wsc_nft_ww_flex_container'>
                          <a href ='{$imageOneUrl}' target='_blank'><img class='wsc_ww_image_preview_wide' src='{$imageOneUrl}'></a>
                         </div>
                       </div>
                      ";
            break;
            case 2:
              if(filesize($imageThreePath)) {unlink($imageThreePath);}
              if(filesize($imageFourPath)) {unlink($imageFourPath);}
              $html = "<div class='wsc_nft_ww_row'>
                         <div class='wsc_nft_ww_flex_container_50'>
                          <a href ='{$imageOneUrl}' target='_blank'><img class='wsc_ww_image_preview_single' src='{$imageOneUrl}'></a>
                         </div>
                         <div class='wsc_nft_ww_flex_container_50'>
                          <a href ='{$imageTwoUrl}' target='_blank'><img class='wsc_ww_image_preview_single' src='{$imageTwoUrl}'></a>
                         </div>
                       </div>
                      ";
            break;
            case 3:
              if(filesize($imageFourPath)) {unlink($imageFourPath);}
              $html = "<div class='wsc_nft_ww_row'>
                         <div class='wsc_nft_ww_flex_container_50'>
                          <a href ='{$imageOneUrl}' target='_blank'><img class='wsc_ww_image_preview_single' src='{$imageOneUrl}'></a>
                         </div>
                         <div class='wsc_nft_ww_flex_container_50'>
                          <a href ='{$imageTwoUrl}' target='_blank'><img class='wsc_ww_image_preview_single' src='{$imageTwoUrl}'></a>
                         </div>
                       </div>
                       <div class='wsc_nft_ww_row'>
                        <div class='wsc_nft_ww_flex_container_50'>
                         <a href ='{$imageThreeUrl}' target='_blank'><img class='wsc_ww_image_preview_single' src='{$imageThreeUrl}'></a>
                        </div>
                        <div class='wsc_nft_ww_flex_container_50'>
                        </div>
                       </div>
                      ";
            break;
            case 4:
              $html = "<div class='wsc_nft_ww_row'>
                         <div class='wsc_nft_ww_flex_container_50'>
                          <a href ='{$imageOneUrl}' target='_blank'><img class='wsc_ww_image_preview_single' src='{$imageOneUrl}'></a>
                         </div>
                         <div class='wsc_nft_ww_flex_container_50'>
                          <a href ='{$imageTwoUrl}' target='_blank'><img class='wsc_ww_image_preview_single' src='{$imageTwoUrl}'></a>
                         </div>
                       </div>
                       <div class='wsc_nft_ww_row'>
                        <div class='wsc_nft_ww_flex_container_50'>
                         <a href ='{$imageThreeUrl}' target='_blank'><img class='wsc_ww_image_preview_single' src='{$imageThreeUrl}'></a>
                        </div>
                        <div class='wsc_nft_ww_flex_container_50'>
                         <a href ='{$imageFourUrl}' target='_blank'><img class='wsc_ww_image_preview_single' src='{$imageFourUrl}'></a>
                        </div>
                       </div>
                      ";
            break;
        }
      }
      break;
      case "pfp":
      $useWordsClass = 'wsc_owner_tools_off';
        $fileId = $uniqueId . ".jpeg";
        if (wsc_create_pfp_from_set($fileId, $setName)) {
          $imageUrl = WSC_PREVIEWS_URL . $fileId . "?" . time();
          $html = "<div class='wsc_nft_ww_row'>
                     <div class='wsc_nft_ww_flex_container_max'>
                      <a href ='{$imageUrl}' target='_blank'><img class='wsc_ww_image_preview_single' src='{$imageUrl}'></a>
                     </div>
                   </div>
                  ";
        }
      break;

      case "3x1":
      case "6x2":
      case "9x3":
      case "12x4":
      case "15x5":
        $useWordsClass = 'wsc_owner_tools_off';
      case "2xwords":
      case "8xwords":
      case "18xwords":
      case "32xwords":
        $fileId = $uniqueId . ".webp";
        if (wsc_set_to_banner($fileId, $setName, $action, $words)) {
          $imageUrl = WSC_PREVIEWS_URL . $fileId . "?" . time();
          $html = "<div class='wsc_nft_ww_row'>
                     <div class='wsc_nft_ww_flex_container'>
                      <a href ='{$imageUrl}' target='_blank'><img class='wsc_ww_image_preview_wide' src='{$imageUrl}'></a>
                     </div>
                   </div>
                  ";
        }
      break;
    }

    $html .= "
      <script>
        var wsc_twitter_words_class = '{$useWordsClass}';
      </script>
    ";

    return $html;
}


function wsc_resize_image($sourceImage, $targetImage, $maxWidth, $maxHeight)
{
    // Get dimensions of source image.
    list($origWidth, $origHeight) = getimagesize($sourceImage);
    if ($maxWidth == 0)
    {
        $maxWidth  = $origWidth;
    }
    if ($maxHeight == 0)
    {
        $maxHeight = $origHeight;
    }
    $widthRatio = $maxWidth / $origWidth;
    $heightRatio = $maxHeight / $origHeight;
    $ratio = min($widthRatio, $heightRatio);
    $newWidth  = (int)$origWidth  * $ratio;
    $newHeight = (int)$origHeight * $ratio;
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
    imagejpeg($newImage, $targetImage, $quality);
    imagedestroy($image);
    imagedestroy($newImage);

    return true;
}


function wsc_create_wiscle_fungie($data) {

  alpn_log("Creating FUNGIE");
  // alpn_log($data);

  $fungieBackground = PTE_IMAGES_ROOT_URL . $data['template_background'];
  $localFile = PTE_ROOT_PATH . "tmp/" . $data['template_background'];

  if (!file_exists($localFile)) {
    file_put_contents($localFile, file_get_contents($fungieBackground));
  }

  list($templateWidth, $templateHeight, $type) = getimagesize($localFile);

  switch ($type)
  {
      case IMAGETYPE_JPEG:
          $image = imagecreatefromjpeg($localFile);
          break;
      case IMAGETYPE_PNG:
          $image = imagecreatefrompng($localFile);
          break;
  }

  $newAssembly = imagecreatetruecolor($templateWidth, $templateHeight);
  imagealphablending($newAssembly, true);

  list($primaryR, $primaryG, $primaryB) = sscanf($data['primary_color'], "#%02x%02x%02x");
  list($secondaryR, $secondaryG, $secondaryB) = sscanf($data['secondary_color'], "#%02x%02x%02x");
  list($tertiaryR, $tertiaryG, $tertiaryB) = sscanf($data['tertiary_color'], "#%02x%02x%02x");

  $primaryColor = imagecolorallocate($newAssembly, $primaryR, $primaryG, $primaryB);
  $secondaryColor = imagecolorallocate($newAssembly, $secondaryR, $secondaryG, $secondaryB);
  $tertiaryColor = imagecolorallocate($newAssembly, $tertiaryR, $tertiaryG, $tertiaryB);

  imagecopymerge($newAssembly, $image, 0, 0, 0, 0, $templateWidth, $templateHeight, 100);
  imagedestroy($image);

  if ($data['quote']) {
    $data['quote']['font_color'] = new Color($primaryR, $primaryG, $primaryB);
    $newAssembly = wsc_addtext_to_nft_certificate($newAssembly, $data['quote'], $data['quote']['words']);
  }

  if ($data['date']) {
    $data['date']['font_color'] = new Color($primaryR, $primaryG, $primaryB);
    $newAssembly = wsc_addtext_to_nft_certificate($newAssembly, $data['date'], $data['date']['words']);
  }

  if ($data['tweet_text']) {
    $data['tweet_text']['font_color'] = new Color($primaryR, $primaryG, $primaryB);
    $newAssembly = wsc_addtext_to_nft_certificate($newAssembly, $data['tweet_text'], $data['tweet_text']['words']);
  }

  if ($data['sender']) {
    $pfpType = isset($data['sender']['pfp_type']) ? $data['sender']['pfp_type'] : "round_name";
    $data['sender']['pfp_type'] = $pfpType;
    $data['sender']['font_color'] = new Color($tertiaryR, $tertiaryG, $tertiaryB);
    $data['sender']['shape_color'] = $secondaryColor;
    $newAssembly = wsc_add_pfp_to_image($newAssembly, $data['sender']);
  }

  $tagKey = 'tagged_' . $data['tag_count'];
  $taggedUsers = $data[$tagKey];

  foreach ($taggedUsers as $key => $user) {
    $pfpType = isset($data['sender']['pfp_type']) ? $data['sender']['pfp_type'] : "round_name";
    $user['pfp_type'] = $pfpType;
    $user['font_color'] = new Color($tertiaryR, $tertiaryG, $tertiaryB);
    $user['shape_color'] = $secondaryColor;
    $newAssembly = wsc_add_pfp_to_image($newAssembly, $user);
  }

  $shortId = pte_get_short_id();
  $localFile = PTE_ROOT_PATH . "tmp/" . "{$shortId}.jpeg";
  imagejpeg($newAssembly, $localFile, 100);
  imagedestroy($newAssembly);

  return "{$shortId}.jpeg";
}

function wsc_add_pfp_to_image($image, $data) {
//  pp($data);

  switch ($data['pfp_type']) {
      case "round_name":

       $pfpTextBoxWidth = isset($data['pfp_text_box_width']) ? $data['pfp_text_box_width'] : 50;
       $pfpTextBoxHeight = isset($data['pfp_text_box_height']) ? $data['pfp_text_box_height'] : 75;
       $pfpTextVerticalAdjust = isset($data['pfp_text_vertical_adjust']) ? $data['pfp_text_vertical_adjust'] : 20;

        $image = wsc_add_rounded_image_to_certificate($image, $data);
        $x1 = $data['x'] - $pfpTextBoxWidth;
        $y1 = $data['y'] + $data['height'] - $pfpTextVerticalAdjust;
        $x2 = $data['x'] + $data['width'] + $pfpTextBoxWidth;
        $y2 = $y1 + $pfpTextBoxHeight;
        //wsc_image_rounded_rectangle($image, $x1, $y1, $x2, $y2, 10, $data['shape_color']);
        $data['x'] = $x1;
        $data['y'] = $y1;
        $data['width'] = $x2 - $x1;
        $data['height'] = $y2 - $y1;
        //$image = wsc_addtext_to_nft_certificate($image, $data, "@" . $data['words']);
      break;
  }
  return $image;
}

function wsc_image_rounded_rectangle(&$img, $x1, $y1, $x2, $y2, $r, $color){
    $r = min($r, floor(min(($x2 - $x1) / 2, ($y2 - $y1) / 2)));
    imagefilledarc($img, $x1 + $r, $y1 + $r, $r * 2, $r * 2, 180, 270, $color, IMG_ARC_PIE);
    imagefilledarc($img, $x2 - $r, $y1 + $r, $r * 2, $r * 2, 270, 0, $color, IMG_ARC_PIE);
    imagefilledarc($img, $x2 - $r, $y2 - $r, $r * 2, $r * 2, 0, 90, $color, IMG_ARC_PIE);
    imagefilledarc($img, $x1 + $r, $y2 - $r, $r * 2, $r * 2, 0, 180, $color, IMG_ARC_PIE);
    imagefilledrectangle($img, $x1+$r, $y1, $x2-$r, $y2, $color);
    imagefilledrectangle($img, $x1, $y1+$r, $x1+$r, $y2-$r, $color);
    imagefilledrectangle($img, $x2-$r, $y1+$r, $x2, $y2-$r, $color);
    return true;
}


function wsc_add_rounded_image_to_certificate($newAssembly, $imageData) {
  $srcFile = $imageData['image_url'];
  list($width_orig, $height_orig, $type) = getimagesize($srcFile);
  switch ($type)
  {
      case IMAGETYPE_GIF:
          $image = imagecreatefromgif($srcFile);
          break;
      case IMAGETYPE_JPEG:
          $image = imagecreatefromjpeg($srcFile);
          break;
      case IMAGETYPE_PNG:
          $image = imagecreatefrompng($srcFile);
          break;
      case IMAGETYPE_WEBP:
          $image = imagecreatefromwebp($srcFile);
          break;
  }
  $newwidth = $imageData['width'];
  $newheight = $imageData['height'];
  $newX = $imageData['x'];
  $newY = $imageData['y'];

  $newImage = imagecreatetruecolor($newwidth, $newheight);
  imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newwidth, $newheight, $width_orig, $height_orig);

  $mask = imagecreatetruecolor($newwidth, $newheight);
  $transparent = imagecolorallocate($mask, 255, 0, 0);
  $bg = imagecolorallocate($mask, 121, 43, 51);
  imagefill($mask, 0, 0, $bg);
  imagecolortransparent($mask, $transparent);
  imagefilledellipse($mask, $newwidth/2, $newheight/2, $newwidth, $newheight, $transparent);

  imagecopymerge($newImage, $mask, 0, 0, 0, 0, $newwidth, $newheight, 100);
  imagedestroy($mask);
  imagecolortransparent($newImage, $bg);
  imagefill($newImage, 0, 0, $bg);

  imagecopymerge($newAssembly, $newImage, $newX, $newY, 0, 0, $newwidth, $newheight, 100);
  imagedestroy($newImage);
  return $newAssembly;
}



function wsc_create_dm_certificate($data) {

  alpn_log("Creating DM CERTIFICATE");
  // alpn_log($data);

  $name = isset($data['screen_name']) && $data['screen_name'] ? "@{$data['screen_name']}" : " -- ";
  $today = date("F j, Y");

  $twitterRecipientId = isset($data['twitter_recipient_id']) && $data['twitter_recipient_id'] ? $data['twitter_recipient_id'] : false;
  $description = isset($data['description']) && $data['description'] ? $data['description'] : " -- ";
  $templateFile = isset($data['template_file']) && $data['template_file'] ? $data['template_file'] : false;

  $imageUrl = isset($data['pfp_url']) && $data['pfp_url'] ? $data['pfp_url'] : false;

  $certificateUrl = PTE_IMAGES_ROOT_URL . $templateFile;
  $localFile = PTE_ROOT_PATH . "tmp/" . $templateFile;

  if (!file_exists($localFile)) {
    file_put_contents($localFile, file_get_contents($certificateUrl));
  }

  list($templateWidth, $templateHeight) = getimagesize($localFile);
  $certificateImage = imagecreatefrompng($localFile);

  $newAssembly = imagecreatetruecolor($templateWidth, $templateHeight);
  imagealphablending($newAssembly, true);

  imagecopymerge($newAssembly, $certificateImage, 0, 0, 0, 0, $templateWidth, $templateHeight, 100);
  imagedestroy($certificateImage);

  if ($imageUrl) {
      $newAssembly = wsc_add_rounded_image_to_certificate($newAssembly, $data);
  }

  $titleBox = array(
    'font_face' => 'Best Valentina TTF.ttf',
    'font_color' => new Color(0, 85, 135),
    'font_size' => 150,
    'line_height' => 1.25,
    'horizontal_align' => 'center',
    'vertical_align' => 'center',
    'x' => 465,
    'y' => 500,
    'width' => 957,
    'height' => 151
  );
  $newAssembly = wsc_addtext_to_nft_certificate($newAssembly, $titleBox, $name);

  $titleBox = array(
    'font_face' => 'OpenSans-Semibold.ttf',
    'font_color' => new Color(0, 0, 0),
    'font_size' => 42,
    'line_height' => 1.25,
    'horizontal_align' => 'center',
    'vertical_align' => 'center',
    'x' => 356,
    'y' => 975,
    'width' => 323,
    'height' => 72
  );
  $newAssembly = wsc_addtext_to_nft_certificate($newAssembly, $titleBox, $today);

  $titleBox = array(
    'font_face' => 'OpenSans-Semibold.ttf',
    'font_color' => new Color(0, 0, 0),
    'font_size' => 30,
    'line_height' => 1.5,
    'horizontal_align' => 'left',
    'vertical_align' => 'center',
    'x' => 325,
    'y' => 725,
    'width' => 1275,
    'height' => 165
  );
  $newAssembly = wsc_addtext_to_nft_certificate($newAssembly, $titleBox, $description);

  $shortId = pte_get_short_id();
  $localFile = PTE_ROOT_PATH . "tmp/" . "{$shortId}.png";
  imagepng($newAssembly, $localFile);
  imagedestroy($newAssembly);

  return "{$shortId}.png";
}


function wsc_create_nft_certificate($certificateName, $data) {

  // alpn_log("Creating CERTIFICATE");
  // alpn_log($data);

  $name = (isset($data['name']) && $data['name']) ? stripslashes($data['name']) : " -- ";
  $description = (isset($data['description']) && $data['description']) ? stripslashes($data['description']) : " -- ";
  $attributes = (isset($data['attributes']) && $data['attributes']) ? $data['attributes'] : array();
  $processId = (isset($data['wscProcessId']) && $data['wscProcessId']) ? $data['wscProcessId'] : false;  //UNIQUE ID
  $mediaType = (isset($data['wsc_media_type']) && $data['wsc_media_type']) ? $data['wsc_media_type'] : "Image NFT";
  $titleBoxText = (isset($data['wsc_title_box']) && $data['wsc_title_box']) ? $data['wsc_title_box'] : "NFT Contract";

  if (isset($data['image_url']) && $data['image_url']) {
    $mediaUrl = $data['image_url'];
  } else if (isset($data['archive_url']) && $data['archive_url']) {
    $mediaUrl = $data['archive_url'];
  } else if (isset($data['document_url']) && $data['document_url']) {
    $mediaUrl = $data['document_url'];
  } else if (isset($data['music_url']) && $data['music_url']) {
    $mediaUrl = $data['music_url'];
  } else if (isset($data['animation_url']) && $data['animation_url']) {
    $mediaUrl = $data['animation_url'];
  }

  $imageUrl = isset($data['image']) && $data['image'] ? $data['image'] : false;

  $certificateUrl = PTE_IMAGES_ROOT_URL . "{$certificateName}.png";
  $localFile = PTE_ROOT_PATH . "tmp/" . "{$certificateName}.png";
  $destinationFileQR = PTE_ROOT_PATH . "tmp/qr_{$processId}.png";

  if (!file_exists($localFile)) {
    file_put_contents($localFile, file_get_contents($certificateUrl));
  }
  $image = imagecreatefrompng($localFile);

  if ($imageUrl) {
    $mediaImage = false;
    $imageType = exif_imagetype($imageUrl);
    $supportedTypes = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP);
    if (in_array($imageType, $supportedTypes)) {
      $destinationImage = PTE_ROOT_PATH . "tmp/img_{$processId}.tmp";
      file_put_contents($destinationImage, file_get_contents($imageUrl));
      switch ($imageType)
      {
          case IMAGETYPE_GIF:
              $mediaImage = imagecreatefromgif($destinationImage);
              break;
          case IMAGETYPE_JPEG:
              $mediaImage = imagecreatefromjpeg($destinationImage);
              break;
          case IMAGETYPE_PNG:
              $mediaImage = imagecreatefrompng($destinationImage);
              break;
          case IMAGETYPE_WEBP:
              $mediaImage = imagecreatefromwebp($destinationImage);
              break;
      }
      unlink($destinationImage);
      if ($mediaImage) {

        $containerX = 45;
        $containerY = 380;
        $containerWidth = 685;
        $containerHeight = 310;

        $origWidth = imagesx($mediaImage);
        $origHeight = imagesy($mediaImage);
        $maxWidth = $containerWidth;
        $maxHeight = $containerHeight;

        $widthRatio = $maxWidth / $origWidth;
        $heightRatio = $maxHeight / $origHeight;
        $ratio = min($widthRatio, $heightRatio);
        $newWidth  = (int)$origWidth * $ratio;
        $newHeight = (int)$origHeight * $ratio;

        $newXIndent = (int)($containerWidth - $newWidth) / 2;
        $newXIndent = $newXIndent > 0 ? $newXIndent : 0;
        $newYIndent = (int)($containerHeight - $newHeight) / 2;
        $newYIndent = $newYIndent > 0 ? $newYIndent : 0;

        $newX = $containerX + $newXIndent;
        $newY = $containerY + $newYIndent;

        imagecopyresampled($image, $mediaImage, $newX, $newY, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
      }
    }
  }

  $titleBox = array(
    'font_face' => 'OpenSans-ExtraBold.ttf',
    'font_color' => new Color(0, 0, 0),
    'font_size' => 64,
    'line_height' => 1.25,
    'horizontal_align' => 'left',
    'vertical_align' => 'center',
    'x' => 33,
    'y' => 44,
    'width' => 578,
    'height' => 96
  );
  $image = wsc_addtext_to_nft_certificate($image, $titleBox, $titleBoxText);

  $titleBox = array(
    'font_face' => 'OpenSans-ExtraBold.ttf',
    'font_color' => new Color(1, 59, 143),
    'font_size' => 48,
    'line_height' => 1.25,
    'horizontal_align' => 'center',
    'vertical_align' => 'center',
    'x' => 20,
    'y' => 300,
    'width' => 1060,
    'height' => 64
  );
  $image = wsc_addtext_to_nft_certificate($image, $titleBox, $name);

  $titleBox = array(
    'font_face' => 'OpenSans-Semibold.ttf',
    'font_color' => new Color(255, 255, 255),
    'font_size' => 28,
    'line_height' => 1.25,
    'horizontal_align' => 'center',
    'vertical_align' => 'center',
    'x' => 33,
    'y' => 194,
    'width' => 350,
    'height' => 45
  );
  $image = wsc_addtext_to_nft_certificate($image, $titleBox, $mediaType);

  $titleBox = array(
    'font_face' => 'Lato-Regular.ttf',
    'font_color' => new Color(0, 0, 0),
    'font_size' => 24,
    'line_height' => 1.40,
    'horizontal_align' => 'left',
    'vertical_align' => 'top',
    'x' => 755,
    'y' => 375,
    'width' => 300,
    'height' => 300
  );
  $image = wsc_addtext_to_nft_certificate($image, $titleBox, $description);

  $options = new QROptions([
	'version'             => 7,
	'outputType'          => QRCode::OUTPUT_IMAGE_PNG,
	'scale'               => 10,
	'imageBase64'         => false,
	'imageTransparent'    => false,
	'drawCircularModules' => true,
	'circleRadius'        => 0.4
]);

$QRCode = new QRCode($options);
$certificateImage = $QRCode->render($mediaUrl);
file_put_contents($destinationFileQR, $certificateImage);
$certificateImageRes = imagecreatefrompng($destinationFileQR);
unlink($destinationFileQR);
imagecopyresampled($image, $certificateImageRes, 558, 150, 0, 0, 140, 140, imagesx($certificateImageRes), imagesy($certificateImageRes));
ob_start();
imagepng($image);
$contents = ob_get_contents();
ob_end_clean();
imagedestroy($image);
imagedestroy($certificateImageRes);
return base64_encode($contents);
}

function wsc_addtext_to_nft_certificate($certificateImage, $textBox, $words){

  $box = new Box($certificateImage);
  $box->setFontFace(PTE_ROOT_DIST_FONTS . $textBox['font_face']);
  $box->setFontColor($textBox['font_color']);
  $box->setFontSize($textBox['font_size']);
  $box->setLineHeight($textBox['line_height']);
  $box->setBox($textBox['x'], $textBox['y'], $textBox['width'], $textBox['height']);
  $box->setTextAlign($textBox['horizontal_align'], $textBox['vertical_align']);
  $box->draw($words);

  return $certificateImage;

}

function wsc_create_nft_liftoff_card($data) {

  pp("Creating LIFTOFF CARD");

  $cardId = (isset($data['card_id']) && $data['card_id']) ? $data['card_id'] : "01";
  $cardTitle = (isset($data['title']) && $data['title']) ? $data['title'] : "Enterprise";
  $attributes = (isset($data['attributes']) && $data['attributes']) ? $data['attributes'] : array();
  $showUrl = isset($data['nft_contract_address']) && $data['nft_contract_address'] && isset($data['nft_token_id']) && $data['nft_token_id'] ? true : false;

  $cardUrl = PTE_ROOT_PATH . "dist/assets/" . "wtc_{$cardId}.png";
  $localFile = PTE_ROOT_PATH . "tmp/" . "wtc_{$cardId}.png";

  $image = imagecreatefrompng($cardUrl);

  $titleBox = array(
    'font_face' => 'Enchanted Land.otf',
    'font_color' => new Color(89,39,32),
    'font_size' => 52,
    'line_height' => 1.25,
    'horizontal_align' => 'center',
    'vertical_align' => 'center',
    'x' => 76,
    'y' => 375,
    'width' => 270,
    'height' => 33
  );
  $image = wsc_addtext_to_nft_certificate($image, $titleBox, $cardTitle);

  $calculatedTitleSize = strlen($attributes['wcl-own']) >= 4 ? 32 : 36;

  $titleBox = array(
    'font_face' => 'Enchanted Land.otf',
    'font_color' => new Color(89,39,32),
    'font_size' => $calculatedTitleSize,
    'line_height' => 1.25,
    'horizontal_align' => 'center',
    'vertical_align' => 'center',
    'x' => 311,
    'y' => 38,
    'width' => 65,
    'height' => 65
  );
  $image = wsc_addtext_to_nft_certificate($image, $titleBox, $attributes['wcl-own']);

  $counter = 0;
  $columnCount = 2;
  $boxWidth = 310;
  $columnWidth = intdiv($boxWidth, $columnCount);
  $boxHeight = 100;
  $attributeCount = count($attributes);
  $rowHeight = 28;
  $boxX = 52;
  $boxY = 412;
  $labelWidthPercent = 0.46;

foreach ($attributes as $key => $value) {

  $row = intdiv($counter, 2);
  $column = $counter % 2;

  $labelX = $boxX + ($column * $columnWidth);
  $labelY = $boxY + ($row * $rowHeight);
  $labelWidth = $columnWidth * $labelWidthPercent;
  $labelHeight = $rowHeight;

  $attributeBox = array(
    'font_face' => 'Enchanted Land.otf',
    'font_color' => new Color(89,39,32),
    'font_size' => 28,
    'line_height' => 1.25,
    'horizontal_align' => 'left',
    'vertical_align' => 'top',
    'x' => $labelX,
    'y' => $labelY + 3,
    'width' => $labelWidth,
    'height' => $labelHeight
  );
  $image = wsc_addtext_to_nft_certificate($image, $attributeBox, "{$key}");

  $valueX = $labelX + $labelWidth;
  $valueY = $boxY + ($row * $rowHeight);
  $valueWidth = intval($columnWidth * (1 - $labelWidthPercent));
  $valueHeight = $rowHeight;

  $attributeBox = array(
    'font_face' => 'Enchanted Land.otf',
    'font_color' => new Color(89,39,32),
    'font_size' => 32,
    'line_height' => 1.25,
    'horizontal_align' => 'left',
    'vertical_align' => 'top',
    'x' => $valueX,
    'y' => $valueY,
    'width' => $valueWidth,
    'height' => $valueHeight
  );
  $image = wsc_addtext_to_nft_certificate($image, $attributeBox, $value);
  $counter++;
}

if ($showUrl) {

  $viewUrl = PTE_BASE_URL . "nftview/?ca={$data['nft_contract_address']}&ti={$data['nft_token_id']}";

  $options = new QROptions([
  'version'             => 7,
  'outputType'          => QRCode::OUTPUT_IMAGE_PNG,
  'scale'               => 10,
  'imageBase64'         => false,
  'imageTransparent'    => true,
  'drawCircularModules' => false,
  'circleRadius'        => 0.4
]);
  $QRCode = new QRCode($options);
  $qrImage = $QRCode->render($viewUrl);
  $destinationFileQR = PTE_ROOT_PATH . "tmp/" . "qr_{$data['nft_token_id']}.png";
  file_put_contents($destinationFileQR, $qrImage);
  $qrImageRes = imagecreatefrompng($destinationFileQR);
  unlink($destinationFileQR);
  imagecopyresampled($image, $qrImageRes, 43, 46, 0, 0, 75, 75, imagesx($qrImageRes), imagesy($qrImageRes));
}
  imagepng($image, $localFile);
  imagedestroy($image);
}


function wsc_set_to_banner($previewId, $setId, $bannerType, $words = "", $userIdOveride = false) {

  global $wpdb;

  $bannerType = explode("x", $bannerType);
  $across = $bannerType[0];
  $down = $bannerType[1];

  $bannerWidth = 1500;
  $bannerWidthNFTs = 1500;
  $bannerHeight = 500;

  if ($down == "words") {    // 1, 6, 24
    $bannerWidthNFTs = 1000;
    switch ($across)
    {
        case 8:
            $down = 2;
            $across = 4;
        break;
        case 18:
            $down = 3;
            $across = 6;
        break;
        case 32:
            $down = 4;
            $across = 8;
        break;
        default:
            $down = 1;
            $across = 2;
    }
  } else if ($down > $across) {
    $bannerWidth = 500;
    $bannerWidthNFTs = 500;
    $bannerHeight = 1500;
  }

  $individualSize =  $bannerWidthNFTs / $across;
  $blankPlaceHolder = PTE_IMAGES_ROOT_URL . "500x500_empty.webp";

  $userInfo = wp_get_current_user();
  $userId = $userIdOveride ? $userIdOveride : $userInfo->data->ID;

  $nfts = $wpdb->get_results(
    $wpdb->prepare("SELECT n.* from alpn_nft_meta n LEFT JOIN alpn_nft_sets s ON s.nft_id = n.id WHERE s.owner_id = %d AND s.set_name = %s ORDER BY RAND()", $userId, $setId)
   );

   $destinationFile = WSC_PREVIEWS_PATH . $previewId;
   $nftCounter = 0;
   if (isset($nfts[0])) {
     $newImage = imagecreatetruecolor($bannerWidth, $bannerHeight);
     $backgroundColor = imagecolorallocate($newImage, 255, 255, 255);
     imagefill($newImage, 0, 0, $backgroundColor);
     for ($y = 0; $y < $down; $y++) {
       for ($x = 0; $x < $across; $x++) {
          $srcFile = (isset($nfts[$nftCounter])) ?  PTE_IMAGES_ROOT_URL . $nfts[$nftCounter]->thumb_large_file_key : $blankPlaceHolder;
          $nftCounter++;
          list($width_orig, $height_orig, $type) = getimagesize($srcFile);
          switch ($type)
          {
              case IMAGETYPE_GIF:
                  $image = imagecreatefromgif($srcFile);
                  break;
              case IMAGETYPE_JPEG:
                  $image = imagecreatefromjpeg($srcFile);
                  break;
              case IMAGETYPE_PNG:
                  $image = imagecreatefrompng($srcFile);
                  break;
              case IMAGETYPE_WEBP:
                  $image = imagecreatefromwebp($srcFile);
                  break;
              default:
                  list($width_orig, $height_orig, $type) = getimagesize($blankPlaceHolder);
                  $image = imagecreatefromwebp($blankPlaceHolder);
          }
          $destinationX = $x * $individualSize;
          $destinationY = $y * $individualSize;
          imagecopyresampled($newImage, $image, $destinationX, $destinationY, 0, 0, $individualSize + 1, $individualSize + 1, $width_orig, $width_orig);
          imagedestroy($image);
       }
     }
      if ($words) {
        $box = new Box($newImage);
        $box->setFontFace(PTE_ROOT_DIST_FONTS . 'OpenSans-Semibold.ttf');
        $box->setFontColor(new Color(0, 0, 0));
        $box->setFontSize(32);
        $box->setLineHeight(1.25);
        $box->setBox(1050, 0, 400, 500);
        $box->setTextAlign('left', 'bottom');
        $box->draw($words);
      }
      imagewebp($newImage, $destinationFile, 95);
      imagedestroy($newImage);
      return true;
   }

  return false;
}

function imageToJpeg($srcFile, $thumbFile, $maxSize = 100) {
    list($width_orig, $height_orig, $type) = getimagesize($srcFile);
    $ratio_orig = $width_orig / $height_orig;
    $width  = $maxSize;
    $height = $maxSize;
    if ($ratio_orig < 1) {
        $width = $height * $ratio_orig;
    }
    else {
        $height = $width / $ratio_orig;
    }
    switch ($type)
    {
        case IMAGETYPE_GIF:
            $gfe = new GifFrameExtractor\GifFrameExtractor();
            if ($gfe->isAnimatedGif($srcFile)) {
              $gfe->extract($srcFile, true);
              $frames = $gfe->getFrames();
              $frame = $frames[0];
              $image = $frame['image'];
            } else {
              alpn_log("NON ANIMATED?");
              $gfe->extract($srcFile);
              $frames = $gfe->getFrames();
              alpn_log($frames);
              $image = imagecreatefromgif($srcFile);
            }
            break;
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($srcFile);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($srcFile);
            break;
        case IMAGETYPE_WEBP:
            $image = imagecreatefromwebp($srcFile);
            break;
        default:
            return false;
    }
    $newImage = imagecreatetruecolor($width, $height);
    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
    imagejpeg($newImage, $thumbFile, 95);
    imagedestroy($newImage);
    return true;
}



function imageToWebp($srcFile, $thumbFile, $maxSize = 100) {

    list($width_orig, $height_orig, $type) = getimagesize($srcFile);
    $ratio_orig = $width_orig / $height_orig;
    $width  = $maxSize;
    $height = $maxSize;
    if ($ratio_orig < 1) {
        $width = $height * $ratio_orig;
    }
    else {
        $height = $width / $ratio_orig;
    }
    switch ($type)
    {
        case IMAGETYPE_GIF:
            $gfe = new GifFrameExtractor\GifFrameExtractor();
            if ($gfe->isAnimatedGif($srcFile)) {
              $gfe->extract($srcFile, true);
              $frames = $gfe->getFrames();
              $frame = $frames[0];
              $image = $frame['image'];
            } else {
              $image = imagecreatefromgif($srcFile);
            }
            break;
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($srcFile);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($srcFile);
            break;
        case IMAGETYPE_WEBP:
            $image = imagecreatefromwebp($srcFile);
            break;
        default:
            return false;
    }
    $newImage = imagecreatetruecolor($width, $height);
    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
    imagewebp($newImage, $thumbFile, 95);
    imagedestroy($newImage);

    return true;
}

function wsc_get_url_meta($url) {

  $supportedFilesArchive = array("zip");
  $supportedFilesDocument = array("pdf");
  $supportedFilesImage = array("jpg", "png", "gif", "webp", "svg");
  $supportedFilesMusic = array("mpeg", "wav", "mp3");
  $supportedFilesVideo = array("mp4", "webm");
  $supportedFiles = array_merge($supportedFilesArchive, $supportedFilesDocument, $supportedFilesImage, $supportedFilesMusic, $supportedFilesVideo);

}


function getFileMetaFromMimeType($mimeType) {
  $extension = $type = "";
  switch ($mimeType) {
    case 'image/png':
      $extension = "png";
      $type = "image";
    break;
    case 'image/webp':
      $extension = "webp";
      $type = "image";
    break;
    case 'image/jpeg':
      $extension = "jpeg";
      $type = "image";
    break;
    case 'image/gif':
      $extension = "gif";
      $type = "image";
    break;
    case 'audio/x-wav':
      $extension = "wav";
      $type = "audio";
    break;
    case 'audio/mp3':
      $extension = "mp3";
      $type = "audio";
    break;
    case 'video/mp4':
      $extension = "mp4";
      $type = "video";
    break;
    case 'audio/x-aiff':
      $extension = "aif";
      $type = "unsupported";
    break;
    case 'video/x-m4v':
      $extension = "m4v";
      $type = "unsupported";
    break;
    case 'video/quicktime':
      $extension = "mov";
      $type = "unsupported";
    break;
    case 'video/webm':
      $extension = "webm";
      $type = "video";
    break;
    case 'audio/ogg':
      $extension = "ogg";
      $type = "audio";
    break;
    case 'audio/mpeg':
      $extension = "mpeg";
      $type = "audio";
    break;
    case 'image/svg':
      $extension = "svg";
      $type = "image";
    break;
    case 'application/octet-stream':
      $extension = "oct";
      $type = "unsupported";
    break;
  }

  return array("extension" => $extension, "type" => $type);
}

function wsc_track_accounts($walletAddress) {

  // pp("Track Account -- " . $walletAddress);

  try { //track non authenticated addresses. Srill need to know transfers.
    $parseClient = new ParseClient;
    $parseClient->initialize( MORALIS_APPID, null, MORALIS_MK );
    $parseClient->setServerURL(MORALIS_SERVER_URL, 'server');
    $results = ParseCloud::run("watchEthAddress", array(
      "address" => $walletAddress,
      "sync_historical" => true
    ), array("useMasterKey" => true));
    // pp($results);
    $results = ParseCloud::run("watchPolygonAddress", array(
      "address" => $walletAddress,
      "sync_historical" => true
    ), array("useMasterKey" => true));
    // pp($results);
    // pp("DONE");
 } catch (ParseException $error) {
  pp("PARSE TRACK ACCOUNTS FAILED");
  pp($error);
 }
}

function wsc_get_owned_web3_accounts_list() {

  global $wpdb;

  $userInfo = wp_get_current_user();
  $ownerId = $userInfo->data->ID;

  $ownedAccounts = $wpdb->get_results(
		$wpdb->prepare("SELECT r.account_address, m.friendly_name, m.ens_address, m.pk_enc IS NOT NULL AS is_custodial FROM alpn_wallet_relationships r LEFT JOIN alpn_wallet_meta m ON m.account_address = r.account_address WHERE r.owner_id = %d AND r.relation = 'owner' ORDER BY CASE WHEN m.friendly_name != '' THEN m.friendly_name ELSE CASE WHEN m.ens_address != '' THEN m.ens_address ELSE r.account_address END END;", $ownerId)
	);

  $selectedAccountAddress = "";

  if (isset($ownedAccounts[0])) {

    $selectedAccountAddress = $ownedAccounts[0]->account_address;

    $accountSelect = "<div class='wsc_ww_select_wrapper'><select id='alpn_select2_small_owned_accounts'>";
    foreach ($ownedAccounts as  $key => $value) {
      // $selectedItem = ($value->contract_address == $selectedContract) ? " SELECTED " : "";
      $selectedItem = "";
      $accountFriendlyName = $value->friendly_name ? $value->friendly_name . " | " : "";
      $accountEnsName = $value->ens_address ? $value->ens_address . " | " : "";
      $accountName = $accountFriendlyName . $accountEnsName . $value->account_address;
      $accountSelect .= "<option data-cust='{$value->is_custodial}' value='{$value->account_address}' $selectedItem>{$accountName}</option>";
    }
    $accountSelect .= "</select></div>";
  } else {
    $accountSelect = "<div class='wsc_ww_select_wrapper wsc_ww_attention'>Please add a web3 account</div>";
  }

  return array("html" => $accountSelect, "selected_account_address" => $selectedAccountAddress);

}

function wsc_get_available_contracts_list($data = array()) {

  global $wpdb;

  $userInfo = wp_get_current_user();
  $ownerId = $userInfo->data->ID;

  $selectedContract = isset($data['selected_contract']) && $data['selected_contract'] ? $data['selected_contract'] : "";
  $chainId = isset($data['chain_id']) && $data['chain_id'] ? $data['chain_id'] : "polygon";
  $accountAddress = isset($data['account_address']) && $data['account_address'] ? $data['account_address'] : "";

  $availableContracts = $wpdb->get_results(
		$wpdb->prepare("SELECT contract_address, collection_name, collection_symbol, contract_name, contract_description FROM alpn_deployed_contracts_view WHERE owner_id = %d AND wallet_address = %s AND chain_id = %s AND state = 'ready' ORDER BY collection_name", $ownerId, $accountAddress, $chainId)
	);

  if (isset($availableContracts[0])) {
    $contractSelect = "<div class='wsc_ww_select_wrapper'><select id='alpn_select2_small_smart_contracts'>";
  	foreach ($availableContracts as  $key => $value) {
  		$collectionName = $value->collection_name ? $value->collection_name : $value->contract_name . " | " . $value->contract_address;
  		$selectedItem = ($value->contract_address == $selectedContract) ? " SELECTED " : "";
  		$contractSelect .= "<option value='{$value->contract_address}' $selectedItem>{$collectionName}</option>";
  	}
  	$contractSelect .= "</select></div>";
  } else {
    $contractSelect = "<div class='wsc_ww_select_wrapper wsc_ww_attention'>Please deploy a smart contract/collection and then come back</div>";
  }
return $contractSelect;

}


function wsc_get_nft_view_toolbar(){

    $waitIndicator = PTE_ROOT_URL . "pdf/web/images/loading-icon.gif";

  	$nftToolbar = "
  	<select id='alpn_select2_wallets_new' class='alpn_selector'><option value='wsc_all_accounts'>All web3 Accounts</option></select>
  	<select id='alpn_select2_chains' class='alpn_selector'><option value='wsc_all_chains'>All Chains</option><option value='eth'>Ethereum</option><option value='polygon'>Polygon</option></select>
    <select id='alpn_select2_contracts' class='alpn_selector'><option value='wsc_all_contracts'>All Contracts</option></select>
  	<select id='alpn_select2_tags' class='alpn_selector'><option value='wsc_all_tags'>All Sets</option></select>
    <select id='alpn_select2_types' class='alpn_selector'><option value='wsc_all_types'>All Types</option><option value='image'>Image</option><option value='music'>Music</option><option value='video'>Video</option></select>
  	<select id='alpn_select2_categories' class='alpn_selector'><option value='visible'>Visible</option><option value='staging'>Staging</option><option value='archived'>Archived</option><option value='error'>Error</option><option value='spam'>SPAM</option></select>
  	<input id='wsc_nft_query_input' title='Search name, description and attributes for text' placeholder='Filter by text' style='font-size: 12px !important; color: black; padding: 0 0 0 10px !important; line-height: 20px; border-style: solid; border-width: 1px; border-radius: 0 0 0 0 !important; border-color: #ccc; height: 24px; width: 100px; font-weight: normal; background-color: white;'>
    <i id='wsc_clear_filters' style='margin-left: 5px;' class='far fa-times-circle wsc_nft_scan' title='Clear Filters' onclick='wsc_clear_gallery();'></i>
    <div class='wsc_toolbar_wait_container'><img id='wsc_nft_loading_wait' class='wsc_nft_loading_wait' src='{$waitIndicator}'></div>
    <br>
    <span style='font-size: 14px; margin-right: 5px;' class='wsc_nft_toolbar_help_text'>Add your account:</span>
  	<i id='wsc_scan_wallet' class='far fa-qrcode wsc_nft_scan' title='Add your web3 account with owner rights by scanning a QR code with your mobile wallet' onclick='wsc_scan_account_to_attach();'></i>
    <span style='font-size: 14px; margin-right: 5px; margin-left: 10px;' class='wsc_nft_toolbar_help_text'>Follow any account:</span>
    <input id='wsc_nft_add_wallet_address' title='Follow accounts with visitor rights by pasting its public key' placeholder='Paste any web3 public key' style='font-size: 12px !important; color: black; padding: 0 0 0 10px !important; line-height: 20px; border-style: solid; border-width: 1px; border-radius: 0 0 0 0 !important; border-color: #ccc; height: 24px; width: 175px; font-weight: normal; background-color: white;'>
    <i id='wsc_copy_gallery_link' style='margin-left: 5px;' class='far fa-link wsc_nft_scan' title='Copy Link to this Gallery' onclick='wsc_copy_gallery_link();'></i>
  	<script>
    alpn_wait_for_ready(10000, 250,
      function(){
        if (typeof jQuery('#wsc_nft_query_input').donetyping != 'undefined') {
            return true;
        }
        return false;
      },
      function(){
        jQuery('#wsc_nft_query_input').donetyping(function(){
          wsc_change_nfts();
        });
      },
      function(){ //Handle Error
        console.log('Failed to find donetyping');
      });
      jQuery('#alpn_select2_wallets_new').select2({
				theme: 'bootstrap',
				width: '165px',
				allowClear: false
			});
			jQuery('#alpn_select2_wallets_new').on('select2:select', function (e) {
					wsc_change_nfts();
			});

			jQuery('#alpn_select2_contracts').select2({
				theme: 'bootstrap',
				width: '165px',
        ajax: {
            type: 'POST',
            url: alpn_templatedir + 'wsc_get_next_contract_page.php',
            dataType: 'json',
            delay: 250,
            data: function (params) {
              var queryData = wsc_gather_nft_query_data();
              const security = specialObj.security;
              return {
                q: params.term,
                member_id: queryData.member_id,
                contract_id: queryData.contract_id,
                account_id: queryData.account_id,
                chain_id: queryData.chain_id,
                type_id: queryData.type_id,
                set_id: queryData.set_id,
                category_id: queryData.category_id,
                nft_query: queryData.nft_query,
                slide_id: queryData.slide_id,
                open_nav: queryData.open_nav,
                page: params.page,
                security: security
              };
            },
            processResults: function (data, params) {
              params.page = params.page || 1;
              return {
                results: data.items,
                pagination: {
                  more: (params.page * 50) < data.total_count
                }
              };
            },
            cache: true
          },
          placeholder: 'Search for a Contract',
          minimumInputLength: 0,
          templateResult: wsc_format_contract,
          templateSelection: wsc_format_contract_selection
			});
			jQuery('#alpn_select2_contracts').on('select2:select', function (e) {
					wsc_change_nfts();
			});

			jQuery('#alpn_select2_chains').select2({
				theme: 'bootstrap',
				width: '85px',
				allowClear: false,
				minimumResultsForSearch: -1
			});
			jQuery('#alpn_select2_chains').on('select2:select', function (e) {
				wsc_change_nfts();
			});
			jQuery('#alpn_select2_categories').select2({
				theme: 'bootstrap',
				width: '80px',
				allowClear: false,
				minimumResultsForSearch: -1
			});
			jQuery('#alpn_select2_categories').on('select2:select', function (e) {
				wsc_change_nfts();
			});

			jQuery('#alpn_select2_types').select2({
				theme: 'bootstrap',
				width: '80px',
				minimumResultsForSearch: -1
			});
			jQuery('#alpn_select2_types').on('select2:select', function (e) {
				wsc_change_nfts();
			});
			jQuery('#alpn_select2_tags').select2({
				theme: 'bootstrap',
				width: '115px',
				allowClear: false,
			});
			jQuery('#alpn_select2_tags').on('select2:select', function (e) {
				wsc_change_nfts();
			});
      jQuery('input#wsc_nft_add_wallet_address').on('paste', function(e){
          wsc_handle_wallet_pasted(e);
      });

  	</script>
  	";

  return $nftToolbar;
}


function wsc_store_nft_file($fileSettings, $unlinkSource = true){

  $error = false;
  $fileKey = $fileSettings["file_key"];
  $mimeType = $fileSettings["mime_type"];

  $localFile =  PTE_ROOT_PATH . "tmp/" . $fileKey;

  if (!filesize($localFile)) {
    alpn_log("ZERO BYTES");
  }

  $typeLookup = getFileMetaFromMimeType($mimeType);

  if ($typeLookup['type'] && $typeLookup['type'] != "unsupported") {

    // if ($mimeType == "image/svg") {  //Sanitize file
    if (false) {  // Turned off santizer. It was altering ENS svg badly
      $sanitizer = new Sanitizer();
      $svgFile = @file_get_contents($localFile);
      $cleanSVG = $sanitizer->sanitize($svgFile);
      if ($cleanSVG) {
        @file_put_contents($localFile, $cleanSVG);
      } else {
        $error = "invalid_svg";
      }
    }

    if (!$error) {

      $fileNameWithExtension = $fileKey . "." . getFileMetaFromMimeType($mimeType)['extension'];
    	try {
    		$storage = new StorageClient([
    	    	'keyFilePath' => GOOGLE_STORAGE_KEY
    		]);

        $bucketName = 'pte_media_store_1';
        $bucket = $storage->bucket($bucketName);
        $object = $bucket->upload(
            fopen($localFile, 'r'),
            ['name' => $fileNameWithExtension]
        );

        if ($unlinkSource) {
          unlink($localFile);
        }

        $fileMeta = getFileMetaFromMimeType($mimeType);
        $fileInfo = array(
          'status' => 'ok',
          'original_file_key' => $fileKey,
          'file_key' => $fileNameWithExtension,   //careful double used with thumb
          'mime_type' => $mimeType,
          'media_type' => $fileMeta['type']
        );

        return $fileInfo;

    	} catch (\Exception $e) {

        if ($unlinkSource) {
          //alpn_log("Trying to unlink TWO -- " . $localFile);
          unlink($localFile);
        }

        $fileInfo = array(
          'status' => 'error',
          'details' => $e
        );
    			return $fileInfo;
    	}
    } else { //error
      unlink($localFile);
      $fileInfo = array(
        'status' => 'error',
        'details' => $error,
        'original_file_key' => $fileKey,
        'file_key' => "",
        'mime_type' => "",
        'media_type' => "unsupported"
      );
      return $fileInfo;
    }
  } else {

    if ($unlinkSource) {
      unlink($localFile);
    }

    $fileInfo = array(
      'status' => 'error',
      'details' => 'empty file or html',
      'original_file_key' => $fileKey,
      'file_key' => "",
      'mime_type' => "",
      'media_type' => "unsupported"
    );

    alpn_log($fileInfo);
      return $fileInfo;
  }
}

function wsc_get_single_nft_metadata ($nftAddress, $tokenId, $chain='eth'){

  $headers = array();
  $moralisApiKey = MORALIS_API_KEY;
  $fullUrl = "https://deep-index.moralis.io/api/v2/nft/{$nftAddress}/{$tokenId}?chain={$chain}";
  $headers[] = "Accept: application/json";
  $headers[] = "X-API-Key: {$moralisApiKey}";
  $options = array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_URL => $fullUrl,
      CURLOPT_HTTPHEADER => $headers
  );
   $ch = curl_init();
   curl_setopt_array($ch, $options);
   $response = curl_exec($ch);
   curl_close($ch);
   return $response;
}

function wsc_resync_single_nft_metadata ($nftAddress, $tokenId, $chain='eth', $updateType='uri'){

  $headers = array();
  $moralisApiKey = MORALIS_API_KEY;
  $fullUrl = "https://deep-index.moralis.io/api/v2/nft/{$nftAddress}/{$tokenId}/metadata/resync?chain={$chain}&flag={$updateType}&mode=sync";
  $headers[] = "Accept: application/json";
  $headers[] = "X-API-Key: {$moralisApiKey}";
  $options = array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_URL => $fullUrl,
      CURLOPT_HTTPHEADER => $headers
  );
   $ch = curl_init();
   curl_setopt_array($ch, $options);
   $response = curl_exec($ch);
   curl_close($ch);
   return $response;
}

function wsc_nft_deep_update($contractAddress, $tokenId, $chain="eth") {

  global $wpdb;

  $resyncResult = wsc_resync_single_nft_metadata($contractAddress, $tokenId, $chain);
  $newMetaDataResult = wsc_get_single_nft_metadata($contractAddress, $tokenId, $chain);
  $nftData = array(
                    "state" => "processing",
                    "moralis_meta" => $newMetaDataResult,
                    "error_code" => NULL
  );
  $whereClause = array(
                    'contract_address' => $contractAddress,
                    'token_id' => $tokenId,
                    'chain_id' => $chain,
                  );
  return $wpdb->update( 'alpn_nft_meta', $nftData, $whereClause );

}

function wsc_get_nft_metadata ($nftAddress, $chain='eth'){  //for contract

  $headers = array();
  $moralisApiKey = MORALIS_API_KEY;
  $fullUrl = "https://deep-index.moralis.io/api/v2/{$nftAddress}/metadata?chain={$chain}";
  $headers[] = "Accept: application/json";
  $headers[] = "X-API-Key: {$moralisApiKey}";
  $options = array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_URL => $fullUrl,
      CURLOPT_HTTPHEADER => $headers
  );
   $ch = curl_init();
   curl_setopt_array($ch, $options);
   $response = curl_exec($ch);
   curl_close($ch);
   return $response;
}

function wsc_update_nft_metadata($nftContractAddress, $tokenId) {

  $headers = array();
  $moralisApiKey = MORALIS_API_KEY;
  $fullUrl = "https://deep-index.moralis.io/api/v2/nft/{$nftContractAddress}/{$tokenId}?chain={$chain}&format=decimal";
  $headers[] = "Accept: application/json";
  $headers[] = "X-API-Key: {$moralisApiKey}";
  $options = array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_URL => $fullUrl,
      CURLOPT_HTTPHEADER => $headers
  );
   $ch = curl_init();
   curl_setopt_array($ch, $options);
   $response = curl_exec($ch);
   curl_close($ch);
   return $response;

}

function wsc_cleanup_nft_uri($uri) {

  //$useIpfs = "https://ipfs.io/ipfs/";
  $useIpfs = "https://ipfs.moralis.io:2053/ipfs/";

  $uri = stripslashes($uri);

  $source = "https://ipfs.fleek.co/ipfs/";
  $sourceLen = strlen($source);
  if (substr($uri, 0, $sourceLen) == $source) {
    return $useIpfs . substr($uri, $sourceLen);
  }
  // $source = "https://gateway.pinata.cloud/ipfs/";
  // $sourceLen = strlen($source);
  // if (substr($uri, 0, $sourceLen) == $source) {
  //   return $useIpfs . substr($uri, $sourceLen);
  // }
  $source = "https://gateway.moralisipfs.com/ipfs/https://ipfs.io/ipfs/";
  $sourceLen = strlen($source);
  if (substr($uri, 0, $sourceLen) == $source) {
    return $useIpfs . substr($uri, $sourceLen);
  }
  // $source = "https://gateway.moralisipfs.com/ipfs/";
  // $sourceLen = strlen($source);
  // if (substr($uri, 0, $sourceLen) == $source) {
  //   return $useIpfs . substr($uri, $sourceLen);
  // }
  $source = "http://";
  $sourceLen = strlen($source);
  if (substr($uri, 0, $sourceLen) == $source) {
    return "https://" . substr($uri, $sourceLen);
  }
  $source = "ipfs://ipfs/";
  $useIpfs = "https://ipfs.io/ipfs/";
  $sourceLen = strlen($source);
  if (substr($uri, 0, $sourceLen) == $source) {
    return $useIpfs . substr($uri, $sourceLen);
  }
  $source = "ipfs://";
  $useIpfs = "https://ipfs.io/ipfs/";
  $sourceLen = strlen($source);
  if (substr($uri, 0, $sourceLen) == $source) {
    return $useIpfs . substr($uri, $sourceLen);
  }
  return $uri;
}

function wsc_get_best_nft_image_url($data) {
//TODO Make this way better
  if (isset($data['properties']) && isset($data['properties']['image']) && $data['properties']['image']) {
    return $data['properties']['image'];
  }
  if (isset($data['image_url']) && $data['image_url']) {
    return $data['image_url'];
  }
}

function wsc_call_process_single_nft($data) {
    try {
      $headers = array();
      $options = array(
          CURLOPT_TIMEOUT => 100,
          CURLOPT_CONNECTTIMEOUT => 20,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_URL => $source,
          CURLOPT_HTTPHEADER => $headers
      );
       $ch = curl_init();
       curl_setopt_array($ch, $options);
       curl_setopt($ch, CURLOPT_HEADERFUNCTION,
         function($curl, $header) use (&$headers)
         {
           $len = strlen($header);
           $header = explode(':', $header, 2);
           if (count($header) < 2) // ignore invalid headers
             return $len;
           $headers[strtolower(trim($header[0]))][] = trim($header[1]);
           return $len;
         }
       );
       curl_exec($ch);
       $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
       curl_close($ch);
       fclose($file);
       if ($httpCode != "200") {
         alpn_log("ERROR GETTING FILE");
         alpn_log($httpCode);
         alpn_log($source);
         alpn_log(file_get_contents($destination));
       }
      if (!filesize($destination)) {
        @file_put_contents($destination, @file_get_contents($source));
      }
    } catch (Exception $error) {
     alpn_log("GET FILE EXCEPTION");
     alpn_log($error);
    }
   return true;
}


function wsc_get_file($source, $destination, $retry = 1) {
    $fallback = false;
    $openSeaMetaDataFailed = false;

    $template = "data:image/gif;base64,";
    $templateLen = strlen($template);
    if (substr($source, 0, $templateLen) == $template) {
      file_put_contents($destination, base64_decode(substr($source, $templateLen)));
      return array("http_code" => "embedded", "fall_back" => $fallback, "opensea_meta" => $openSeaMetaDataFailed);
    }
    $template = "data:application/json;utf8,";
    $templateLen = strlen($template);
    if (substr($source, 0, $templateLen) == $template) {
      file_put_contents($destination, substr($source, $templateLen));
      return array("http_code" => "embedded", "fall_back" => $fallback, "opensea_meta" => $openSeaMetaDataFailed);
    }
    $template = "data:application/json;base64,";
    $templateLen = strlen($template);
    if (substr($source, 0, $templateLen) == $template) {
      file_put_contents($destination, base64_decode(substr($source, $templateLen)));
      return array("http_code" => "embedded", "fall_back" => $fallback, "opensea_meta" => $openSeaMetaDataFailed);
    }
    $template = "data:image/svg+xml;base64,";
    $templateLen = strlen($template);
    if (substr($source, 0, $templateLen) == $template) {
      $fileContent = trim(base64_decode(substr($source, $templateLen)));
      $template2 = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>';
      $template2Len = strlen($template2);
      if (substr($fileContent, 0, $template2Len) == $template2) {
        $fileContent = substr($fileContent, $template2Len);
      }
      file_put_contents($destination, $fileContent);
      return array("http_code" => "embedded", "fall_back" => $fallback, "opensea_meta" => $openSeaMetaDataFailed);
    }
    $template = "data:image/svg+xml;utf8,";
    $templateLen = strlen($template);
    if (substr($source, 0, $templateLen) == $template) {
      $fileContent = trim(substr($source, $templateLen));
      $template2 = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>';
      $template2Len = strlen($template2);
      if (substr($fileContent, 0, $template2Len) == $template2) {
        $fileContent = substr($fileContent, $template2Len);
      }
      file_put_contents($destination, $fileContent);
      return array("http_code" => "embedded", "fall_back" => $fallback, "opensea_meta" => $openSeaMetaDataFailed);
    }
    try {
      $file = fopen($destination, "w");
      $headers = array();
      $options = array(
          CURLOPT_FILE => $file,
          CURLOPT_TIMEOUT => 100,
          CURLOPT_CONNECTTIMEOUT => 20,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_URL => $source,
          CURLOPT_HTTPHEADER => $headers
      );
       $ch = curl_init();
       curl_setopt_array($ch, $options);
       curl_setopt($ch, CURLOPT_HEADERFUNCTION,
         function($curl, $header) use (&$headers)
         {
           $len = strlen($header);
           $header = explode(':', $header, 2);
           if (count($header) < 2) // ignore invalid headers
             return $len;
           $headers[strtolower(trim($header[0]))][] = trim($header[1]);
           return $len;
         }
       );
       curl_exec($ch);
       $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
       curl_close($ch);
       fclose($file);

       if ($httpCode >= 300) {
         alpn_log("ERROR GETTING FILE - " . $httpCode);
         // alpn_log($httpCode);
         // alpn_log($headers);
         if ($httpCode == 404 && substr($source, 0, strlen("https://api.opensea.io/api/v1/metadata")) == "https://api.opensea.io/api/v1/metadata") {
           // alpn_log("OS META FAILED");
           $openSeaMetaDataFailed = true;
         } else {
           alpn_log($source);
           alpn_log(file_get_contents($destination));
         }
         if ($httpCode == 429 && $retry <= 3) {
           alpn_log("WAITING - " . ($retry * 10));
           sleep($retry * 10);
           wsc_get_file($source, $destination, $retry + 1);
         }
         return array("http_code" => $httpCode, "fall_back" => $fallback, "opensea_meta" => $openSeaMetaDataFailed, "retry" => $retry);
       }
       if (!filesize($destination)) {
         alpn_log("FALLING BACK TO FILE GET CONTENTS");
         file_put_contents($destination, file_get_contents($source));
         alpn_log($source);
         alpn_log($destination);
         alpn_log(filesize($destination));

         if (filesize($destination)) {

           return array("http_code" => 200, "fall_back" => true, "opensea_meta" => false);
         }
       }
    } catch (Exception $error) {
     alpn_log("GET FILE EXCEPTION");
     alpn_log($error);
    }
   return array("http_code" => $httpCode, "fall_back" => $fallback, "opensea_meta" => $openSeaMetaDataFailed);
}

//TODO BIG BUG. On Gary Vee who has 16000+ on Polygon
//Moralis restarts after hitting page 17 Keeps sending a valid cursor.
//Forcing stop after 8000

function wsc_get_all_member_nfts($walletAddress) {

  global $wpdb;

  $maxPerChain = 1000;  //TODO scale up

  $chains = array("eth", "polygon");
  $nftTotal = false;
  $tooMany = array();

  try {

    foreach ($chains as $chain) {

      $nftResults = wsc_get_nft_page($walletAddress, '', $chain, $maxPerChain);

      if (!$nftResults['total']) {

        alpn_log("TOO MANY OR NONE -- BREAKING -- " . $chain);
        $tooMany[$chain] = true;
        //0xD387A6E4e84a6C86bd90C158C6028A58CC8Ac459
      } else {

        $nftTotal = intval($nftResults['total']);

        alpn_log("CHAIN -- " . $chain . " -- " . $nftResults['total']);

        if (isset($nftResults['cursor']) && $nftResults['cursor']) {

          $nftPageSize = intval($nftResults['page_size']);
          $nftPage = intval($nftResults['page']);
          $cursor = $nftResults['cursor'];

          while ( $cursor  && ($nftPage * $nftPageSize <= $nftTotal ) ) {
            alpn_log($nftPage . " -- " . $nftPageSize . " -- " . $nftPage * $nftPageSize . " -- " . $nftTotal);
            $nftResults = wsc_get_nft_page($walletAddress, $cursor, $chain);
            $cursor = $nftResults['cursor'];
            $nftPage = intval($nftResults['page']);
          }
        }
      }
    }
  } catch (Exception $error) {
   alpn_log("FAILED GETTING ALL NFTS");
   alpn_log($error);
  }

if (!$nftTotal) {

  $walletInfo = array(
    "state" => "too_many"
  );
  $whereClause = array(
    "account_address" => $walletAddress
  );
  $wpdb->update( 'alpn_wallet_meta', $walletInfo, $whereClause );
  alpn_log("TELL USER THERE'S A WALLET PROBLEM");
  alpn_log($tooMany);
}

return $nftTotal;
}


function wsc_handle_insert_multiple_nfts($nftPlaceholders, $values) {

  global $wpdb;

  $query           = "INSERT IGNORE INTO alpn_nft_meta (`owner_address`, `contract_address`, `token_id`, `chain_id`, `state`, `process_after`, `moralis_meta`) VALUES ";
  $query           .= implode( ', ', $nftPlaceholders);
  $sql             = $wpdb->prepare( "$query ", $values );

  try {

      if ( $wpdb->query( $sql ) ) {
        alpn_log("Inserting Multiple Success");
        return true;
      } else {
        alpn_log("Inserting Multiple FAIL");
        alpn_log($wpdb->last_query);
        alpn_log($wpdb->last_error);
        return false;
      }

  } catch (Exception $error) {
      alpn_log("Inserting Multiple Error");
      alpn_log($values);
      alpn_log($error);
  }

}

function wsc_get_nft_page ($walletAddress, $cursor = '', $chain = 'eth', $maxPerChain = 1000){

  try {
    $headers = array();
    $moralisApiKey = MORALIS_API_KEY;
    if ($cursor) {
      $fullUrl = "https://deep-index.moralis.io/api/v2/{$walletAddress}/nft?&cursor={$cursor}&limit=100&chain={$chain}";
    } else {
      $fullUrl = "https://deep-index.moralis.io/api/v2/{$walletAddress}/nft?&chain={$chain}&limit=100&format=decimal";
    }
    $headers[] = "Accept: application/json";
    $headers[] = "X-API-Key: {$moralisApiKey}";
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_URL => $fullUrl,
        CURLOPT_HTTPHEADER => $headers
    );

     $ch = curl_init();
     curl_setopt_array($ch, $options);
     curl_setopt($ch, CURLOPT_HEADERFUNCTION,
       function($curl, $header) use (&$headers)
       {
         $len = strlen($header);
         $header = explode(':', $header, 2);
         if (count($header) < 2) // ignore invalid headers
           return $len;
         $headers[strtolower(trim($header[0]))][] = trim($header[1]);
         return $len;
       }
     );
     $response = curl_exec($ch);
     $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
     curl_close($ch);
     // alpn_log("NFT PAGING HTTP CODE -- " . $httpCode);
     // alpn_log($response);

     $nftResults = json_decode($response, true);

     if ($nftResults['total'] > $maxPerChain) { //BREAK SCALE
       $newResponse = array(
         "total" => 0,
         "page_size" => 0,
         "page" => 0,
         "cursor" => "",
         "response_code" => "too_many_nfts"
       );
       return $newResponse;
     }

     $allNfts = $nftResults['result'];
     $nfts = array(); $nftPlaceholders = array();
     for ($i = 0; $i < count($allNfts); $i++) {
       $nowGm = gmdate ("Y-m-d H:i:s", time());
       array_push( $nfts, $allNfts[$i]['owner_of'], $allNfts[$i]['token_address'], $allNfts[$i]['token_id'], $chain, "processing", $nowGm, json_encode($allNfts[$i]));
       $nftPlaceholders[] = "( %s, %s, %s, %s, %s, %s, %s)";
       // if (($i % 100) == 0) { //write -- MOralis decreased to 100 so...
       //   wsc_handle_insert_multiple_nfts($nftPlaceholders, $nfts);
       //   $nfts = array(); $nftPlaceholders = array();
       // }
     }
     if (count($nfts)) {  //balance
       wsc_handle_insert_multiple_nfts($nftPlaceholders, $nfts);
     }

     $newResponse = array(
       "total" => $nftResults["total"],
       "page_size" => $nftResults["page_size"],
       "page" => $nftResults["page"],
       "cursor" => $nftResults["cursor"],
       "response_code" => $httpCode
     );
     return $newResponse;

  } catch (Exception $error) {
      alpn_log("Getting Page Error");
      alpn_log($walletAddress);
      alpn_log($error);
  }
}


function wsc_log_current_user_into_parse() {

  global $wpdb;
  $sessionToken = "";

  $userInfo = wp_get_current_user();
  $userID = $userInfo->data->ID;

  $results = $wpdb->get_results(
		$wpdb->prepare("SELECT parse_user_name, parse_password from alpn_topics WHERE owner_id = %d AND special = 'user'", $userID)
	 );

   if (isset($results[0]) && $results[0]->parse_user_name && $results[0]->parse_password) {

     try { //log in
      $parseClient = new ParseClient;
      $parseClient->initialize( MORALIS_APPID, null, MORALIS_MK );
      $parseClient->setServerURL(MORALIS_SERVER_URL, 'server');

      $user = ParseUser::logIn($results[0]->parse_user_name, $results[0]->parse_password);
      $sessionToken = $user->getSessionToken();

      alpn_log("PARSE USER LOGGED IN - " . $sessionToken);

     } catch (ParseException $error) {
      // The login failed. Check error to see why.
      alpn_log("PARSE USER LOGIN FAILED");
      alpn_log($error);
     }

   }
  return $sessionToken;

}


function wsc_create_web3_support($userId){

  global $wpdb;

//  wsc_create_new_parse_user($userId);

  $data = array(
    'cloud_function' => 'wsc_create_wallet',
    'contract_template_id' => '',
    'pk' => ''
  );

  $walletInfo = json_decode(wsc_call_cloud_function($data), true);

  if (isset($walletInfo['result']) && $walletInfo['result']) {

    $walletAddress = $walletInfo['result']['address'];
    $walletPk = wsc_encrypt_string($walletInfo['result']['privateKey']);
    $walletMnemonic = wsc_encrypt_string($walletInfo['result']['mnemonic'], $walletPk['secret']);

    wsc_track_accounts($walletAddress);

   $walletData = array(
      'account_address' => $walletAddress,
      'friendly_name' => "Wiscle Plus",
      'state' => "ready",
      'pk_enc' => $walletPk['encrypted'],
      'mnemonics_enc' => $walletMnemonic['encrypted'],
      'enc_key' => $walletPk['secret']
    );
    $wpdb->insert( 'alpn_wallet_meta', $walletData );

    $walletRelationshipData = array(
      'account_address' => $walletAddress,
      'relation' => "owner",
      'owner_id' => $userId
    );
    $wpdb->insert( 'alpn_wallet_relationships', $walletRelationshipData );
  }

  //$cloudFunction = "wsc_create_wallet";
  //$cloudFunction = "wsc_sign_transaction";
  //$cloudFunction = "wsc_setup_contract_object";
  // $contractTemplateId = "eSIybJulHThMDnZZ39tDFmXy";
  // $privateKey = "0xfaa849a9ae4ea5cad2c7f2f8cf7afa6438532a3ffe48b547c77effc499dc1051";


}

function wsc_create_new_parse_user($userId) {

  alpn_log("CREATING PARSE USER");

  global $wpdb;

  if ($userId) {

    try {

      $newParseUserName = Uuid::uuid4()->toString();
      $newParsePassword = Uuid::uuid4()->toString();

      $parseClient = new ParseClient;
      $parseClient->initialize( MORALIS_APPID, null, MORALIS_MK );
      $parseClient->setServerURL(MORALIS_SERVER_URL,'server');

      $user = new ParseUser;
      $user->set("username", $newParseUserName);
      $user->set("password", $newParsePassword);

      try {
        $user->signUp();
        $topicData['parse_user_name'] = $newParseUserName;
        $topicData['parse_password'] = $newParsePassword;
        $whereClause['owner_id'] = $userId;
        $whereClause['special'] = 'user';
        $wpdb->update( 'alpn_topics', $topicData, $whereClause );
      } catch (ParseException $ex) {
        // Show the error message somewhere and let the user try again.
        alpn_log ("Error: " . $ex->getCode() . " " . $ex->getMessage());
      }

    } catch (ParseException $e) {
    // catch throwables when the connection is not a success
      alpn_log("Error Creating Parse User");
      alpn_log($e);
    }
  }
}


function wsc_query_parse_single($mclass, $mquery) {

  try {
  	$parseClient = new ParseClient;
  	$parseClient->initialize( MORALIS_APPID, null, MORALIS_MK );
  	$parseClient->setServerURL(MORALIS_SERVER_URL,'server');
  	$query = new ParseQuery($mclass);
  	$query->equalTo("objectId", $mquery);
  	$query->limit(1);
  	$results = $query->find(true);

  	pp($results);

  	if (isset($results[0])) {
  		$result = $results[0];
  		$moralisSessionToken = $result->get('sessionToken');
  	} else {
  		$moralisSessionToken = "";
  	}

  } catch (ParseException $e) {
  // catch throwables when the connection is not a success
  	pp($e);
  }

}





function pte_user_rights_check($resourceType, $data){
  //alpn_log('RIGHTS CHECK');
  global $wpdb;
  $userInfo = wp_get_current_user();
  $userId = $userInfo->data->ID;
  $userNetworkId = get_user_meta( $userId, 'pte_user_network_id', true );
  switch ($resourceType) {

    case 'vault_item_edit':   //Mine or Shared Contact Topic.
      $vaultId = $data['vault_id'];
      $results = $wpdb->get_results($wpdb->prepare(
        "SELECT v.id FROM alpn_vault v WHERE v.owner_id = %d AND v.id = %d
         UNION SELECT v.id FROM alpn_vault v INNER JOIN alpn_topics t ON t.id = v.topic_id AND t.connected_id = %d WHERE v.id = %d", $userId, $vaultId, $userId, $vaultId));
       if (isset($results[0])) {
         return true;
       }
    break;

    case 'vault_item':
    case 'vault_item_view':
     $vaultId = $data['vault_id'];    //Mine, My Contacts, My Topics
     $results = $wpdb->get_results($wpdb->prepare(
       "SELECT v.id FROM alpn_vault v WHERE v.owner_id = %d AND v.id = %d
         UNION
        SELECT v.id FROM alpn_vault v INNER JOIN alpn_topics t ON t.id = v.topic_id AND t.connected_id = %d WHERE v.id = %d
         UNION
        SELECT v.id FROM alpn_vault v INNER JOIN alpn_proteams p ON p.topic_id = v.topic_id AND p.wp_id = %d AND v.access_level <= p.access_level WHERE v.id = %d
       ", $userId, $vaultId, $userId, $vaultId, $userId, $vaultId));

      if (isset($results[0])) {
        return true;
      }
   break;

   case 'topic_dom_view':
    $topicDomId = $data['topic_dom_id'];
    $results = $wpdb->get_results($wpdb->prepare(
      "SELECT t.id FROM alpn_topics t WHERE t.owner_id = %d AND t.dom_id = %s UNION SELECT t.id FROM alpn_proteams p LEFT JOIN alpn_topics t ON t.id = p.topic_id WHERE p.wp_id = %d AND t.dom_id = %s", $userId, $topicDomId, $userId, $topicDomId));
     if (isset($results[0])) {
       return true;
     }
   break;
   case 'topic_dom_edit':  //TODO Future allow editing as part of comprehensive multuser capability.
    $topicDomId = $data['topic_dom_id'];
    $results = $wpdb->get_results($wpdb->prepare(
      "SELECT t.id FROM alpn_topics t WHERE t.owner_id = %d AND t.dom_id = %s", $userId, $topicDomId));
     if (isset($results[0])) {
       return true;
     }
   break;

   case 'action':
    alpn_log('ACTION');


   break;

 }

  return false;
}

function delTree($dir)
{
    $files = array_diff(scandir($dir), array('.', '..'));

    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }

    return rmdir($dir);
}



function wsc_send_notifications($data) {

  $sendBrowserNotification = true;
  $sendSMSNotification = false;
  $sendEmailNotification = false;

  $memberNotificationPreferences = array(
    "chat_message_normal" => array("browser", "mobile"),
    "chat_message_priority" => array("sms", "email", "browser", "mobile"),
    "interaction_normal" => array("browser", "mobile"),
    "interaction_priority" => array("sms", "email", "browser", "mobile"),
    "audio_room_entered_normal" => array("browser", "mobile"),
    "audio_room_entered_priority" => array("sms", "email", "browser", "mobile")
  );

  global $wpdb;
  $accountSid = ACCOUNT_SID;
  $authToken = AUTHTOKEN;
  $notificationSid = NOTIFYSSID;
  $chatService = CHATSERVICESID;


  $notificationType = isset($data['type']) && $data['type'] ? $data['type'] : false;
  $channelSid = isset($data['channel_sid']) && $data['channel_sid'] ? $data['channel_sid'] : false;
  $topicId = isset($data['channel_unique_name']) && $data['channel_unique_name'] ? $data['channel_unique_name'] : false;
  $channelFriendlyName = isset($data['channel_friendly_name']) && $data['channel_friendly_name'] ? $data['channel_friendly_name'] : false;
  $ownerId = isset($data['owner_id']) && $data['owner_id'] ? $data['owner_id'] : false;
  $contactId = isset($data['contact_id']) && $data['contact_id'] ? $data['contact_id'] : false;
  $targetId = isset($data['identity']) && $data['identity'] ? $data['identity'] : false;
  $senderId = isset($data['sender_id']) && $data['sender_id'] ? $data['sender_id'] : false;
  $senderFirst = isset($data['sender_name']) && $data['sender_name'] ? $data['sender_name'] : 'System';
  $notificationBody = isset($data['body']) && $data['body'] ? stripslashes($data['body']) : false;
  $previewFileUrl = isset($data['preview_file_name']) && $data['preview_file_name'] ? PTE_ROOT_URL . $data['preview_file_name'] : '';
  $roomMembers = isset($data['members']) && $data['members'] ? $data['members'] : array();
  $roomMemberString = $roomMembers ? implode(",", $roomMembers) : "0";

  $topicIsImportant = $memberIsImportant = false;
  $emailAddress = $mobilePhone = $dOwnerId = $dTopicName = $dOwnerFirst = $dContactId = $dContactFirst = "";

  if ($senderId && $targetId && $senderId == $targetId) {
  //  alpn_log("Do Not Notify Sender");
    return;
  }

  //TODO combine, cache -- lots of opportunity to reduce db load
  if ($topicId && $targetId) {
    $targetDataAll = $wpdb->get_results(
  		$wpdb->prepare("SELECT t.topic_content, t.notification_prefs, t.alt_id, l1.id AS topic_is_important, (SELECT l2.id FROM alpn_user_lists l2 WHERE l2.contact_id IN ({$roomMemberString}) AND l2.list_key = 'pte_important_network' AND l2.owner_id = t.owner_id LIMIT 1) AS member_is_important FROM alpn_topics t LEFT JOIN alpn_user_lists l1 ON l1.owner_id = t.owner_id AND l1.item_id = %d AND l1.list_key = 'pte_important_topic' WHERE t.owner_id = %d AND t.special = 'user' LIMIT 1", $topicId, $targetId));

      if (isset($targetDataAll[0])) {
        //  alpn_log("TARGET DATA");
          $targetData = $targetDataAll[0];
          $topicContent = json_decode($targetData->topic_content, true);
          $emailAddress = $targetData->alt_id;
          $mobilePhone = $topicContent['person_telephone'];
          $topicIsImportant = $targetData->topic_is_important ? true : false;
          $memberIsImportant = $targetData->member_is_important ? true : false;
      }

    	$results = $wpdb->get_results(
    		$wpdb->prepare("SELECT t.owner_id, t.name AS topic_name, t.topic_content AS topic_content, t1.topic_content AS owner_topic_content, t1.name AS owner_topic_name, t2.owner_id AS contact_id, t2.name AS contact_topic_name, t2.topic_content AS contact_topic_content, t3.name AS sender_topic_name, t3.topic_content AS sender_topic_content FROM alpn_topics t LEFT JOIN alpn_topics t1 ON t1.owner_id = t.owner_id AND t1.special = 'user' LEFT JOIN alpn_topics t2 ON t2.id = t.connected_topic_id LEFT JOIN alpn_topics t3 ON t3.owner_id = t.owner_id AND t3.special = 'user' WHERE t.id = %d", $topicId));

    if (isset($results[0])) {

        $topicData = $results[0];
        $dTopicName = stripslashes($topicData->topic_name);  // If regular Topic, then this is the topic name
        $dOwnerId = $topicData->owner_id;
        $ownerTopicContent = json_decode($topicData->owner_topic_content, true);
        $dOwnerFirst = $ownerTopicContent['person_givenname'];
        $dContactId = $topicData->contact_id;
        $contactTopicContent = json_decode($topicData->contact_topic_content, true);
        $dContactFirst = $contactTopicContent['person_givenname'];

        if ( $dOwnerId && $dContactId ) {
          $isContact = true;
        } else {  //regular Topic
          $isContact = false;
        }
      }
   }

   $important = $topicIsImportant || $memberIsImportant ? "★ " : "";

   switch ($notificationType) {
    case 'new_chat_message':
      $wscIcon = "567c768d-notichat3.png";
      $notificationTitle = $isContact ? "{$important}Chat received from {$senderFirst}" : "{$important}Chat received from {$senderFirst} regarding {$dTopicName}";
    break;
    case 'new_av_room_participant':
      $wscIcon = "d50ddf9e-audio6.png";
      $notificationTitle = $isContact ? "{$important}{$senderFirst} joined Audio" : "{$important}{$senderFirst} joined Audio for {$dTopicName}";
    break;
    case 'new_interaction':
      $wscIcon = "e5e34eac-notiinteraction4.png";
      //$notificationTitle = $isContact ? "Interaction from {$senderFirst}" : "Interaction from {$senderFirst} regarding {$dTopicName}";
      $notificationTitle = "{$important}Interaction regarding {$dTopicName}";
    break;
  }


  $twilio = new Client($accountSid, $authToken);
  $user = $twilio->chat->v2->services($chatService)
                         ->users($targetId)
                         ->fetch();

  $targetIsOnline = $user->isOnline;
  $targetIsNotifiable = $user->isNotifiable;

  if ($sendBrowserNotification) {
    try {
      $notification = $twilio->notify->v1->services($notificationSid)
      ->notifications
      ->create([
                    "title" => $notificationTitle,
                    "body" => $notificationBody,
                    "identity" => array($targetId),
                    "sound" => "default",
                    "data" => array("topic_id" => $topicId, "wsc_icon" => $wscIcon, "wsc_notification_type" => $notificationType)
               ]
      );
    } catch(Exception $e) {
      alpn_log("EXCEPTION TWILIO NOTIFICATION");
      alpn_log($e);
    }
  }

  if ($sendSMSNotification && $mobilePhone && !$targetIsOnline) {
    try {

      $notificationBody = $notificationBody ? $notificationBody : " -" ;

      $smsData = array(
        "type" => "notification",
        "body_text" => stripslashes($notificationBody) . " ",
        "from_name" => $senderFirst,
        "subject_text" => stripslashes($notificationTitle),
        "send_mobile_number" => $mobilePhone
      );
      pte_send_sms($smsData);

    } catch(Exception $e) {
      alpn_log("EXCEPTION SMS NOTIFICATION");
      alpn_log($e);
    }
  }

  if ($sendEmailNotification && $emailAddress && !$targetIsOnline) {

    try {
      $emailHeader = "Notification";
      $mailer = WC()->mailer();
      $template = 'vit_generic_email_template.php';
      $content = 	wc_get_template_html( $template, array(
          'email_heading' => $emailHeader,
          'email'         => $mailer,
          'email_body'    => stripslashes($notificationBody) . $previewFileHtml
        ), PTE_ROOT_PATH . 'woocommerce/emails/', PTE_ROOT_PATH . 'woocommerce/emails/');
      try {
        $mailer->send( $emailAddress, stripslashes($notificationTitle), $content );
      } catch (Exception $e) {
          alpn_log ('Caught exception: '. $e->getMessage());
      }
    } catch(Exception $e) {
      alpn_log("EXCEPTION EMAIL NOTIFICATION");
      alpn_log($e);
    }
  }
}


function pte_encode_value ($s) {
    return htmlentities($s, ENT_COMPAT|ENT_QUOTES,'ISO-8859-1', true);
}

function pte_digits($sourceString){
  return preg_replace('/\D/', '', $sourceString);
}

//TODO centralize this with report usages on ZIP

function pte_add_to_proteam($data) {

  global $wpdb;

  $accessLevel = isset($data['access_level']) && $data['access_level'] ? $data['access_level'] : "10";
  $state = isset($data['state']) && $data['state'] ? $data['state'] : "10";

  $defaultMemberRights = array(
    'delete' => 1,
    'edit' => 1,
    'new' => 1,
    'links' => 1,
    'chat' => 1,
    'download_pdf' => 1,
    'download_original' => 1,
    'print' => 1,
    'interaction_start' => 1
		);

  $memberRights = isset($data['member_rights']) && $data['member_rights'] ? json_decode($data['member_rights'], true) : $defaultMemberRights;
  $connectedType = isset($data['connected_type']) && $data['connected_type'] ? $data['connected_type'] : "external";
  $processId = isset($data['process_id']) && $data['process_id'] ? $data['process_id'] : "";
  $linkedTopicId = isset($data['linked_topic_id']) && $connectedType == "link" ? $data['linked_topic_id'] : 0;

	$proTeamData = array( //TODO
		'owner_id' => $data['owner_id'],
		'topic_id' => $data['topic_id'],  //topicContext
		'proteam_member_id' => $data['proteam_member_id'],
		'wp_id' => $data['wp_id'],
		'access_level' => $accessLevel,
		'state' => $state,
    'connected_type' => $connectedType,
    'process_id' => $processId,
		'member_rights' => json_encode($memberRights),
    'linked_topic_id' => $linkedTopicId
	);

	$wpdb->insert( 'alpn_proteams', $proTeamData );

  return $wpdb->insert_id;
}

function delete_from_cloud_storage($fileKey){
  alpn_log("Deleting Vault Item in Cloud Storage.");
  alpn_log($fileKey);
	try {
		$storage = new StorageClient([
	    	'keyFilePath' => GOOGLE_STORAGE_KEY
		]);
    $bucket = $storage->bucket('pte_file_store1');
    $object = $bucket->object($fileKey);
    $object->delete();
    return true;
	} catch (\Exception $e) { // Global namespace
    alpn_log('Failed to Delete from Cloud Storage');
    return false;
	}
}

function storePdf($pdfSettings){
  alpn_log('STORING PDF');
  $pdfKey = $pdfSettings["pdf_key"];
	$localFile = $pdfSettings["local_file"];
  $doNotUnlinkLocal = $pdfSettings["do_not_unlink_local"];
	try {
		$storage = new StorageClient([
	    	'keyFilePath' => '/var/www/html/proteamedge/private/proteam-edge-cf8495258f58.json'
		]);
		$storage->registerStreamWrapper();
		$fileContent = file_get_contents($localFile);
		$options = ['gs' => ['Content-Type' => "application/pdf"]];
		$context = stream_context_create($options);
		$response = file_put_contents("gs://pte_file_store1/{$pdfKey}", $fileContent, 0, $context);
		if (!$doNotUnlinkLocal) {unlink ($localFile);}
    $fileInfo = array(
      'status' => 'ok',
      'pdf_size' => $response,
      'pdf_key' => $pdfKey
    );
    return $fileInfo;

	} catch (\Exception $e) { // Global namespace
			$pte_response = array("topic" => "pte_get_cloud_file_google_exception", "message" => "Problem accessing Google Cloud Storage.", "data" => $e);
			alpn_log($pte_response);
			exit;
	}
}

function pte_date_to_js($sourceDateTime, $prefix=''){
  $shortId = pte_get_short_id();
  return "<div id='{$shortId}'><script>pte_date_to_js('{$sourceDateTime}', '{$shortId}', '{$prefix}');</script></div>";
}

function pte_map_extract($theMap){
  $extractedMap = array();
  foreach ($theMap as $key => $value) {    //TODO find the right function
    $extractedMap[$key] = isset($value['id']) ? $value['id'] : "";
 }
  return ($extractedMap);
}

function pte_add_quotes($str) {
    return sprintf("'%s'", $str);
}

function pte_get_available_topic_fields($formId, $editorMode) {

  global $wpdb;

  $userInfo = wp_get_current_user();
  $userID = $userInfo->data->ID;

  $topicTypeMap = array();
  $tokens = array();
  alpn_log("pte_get_available_topic_fields");
  //Get the desired topic
  $results = $wpdb->get_results($wpdb->prepare("SELECT id, name, type_key, schema_key, topic_type_meta, special FROM alpn_topic_types WHERE form_id = %d",	$formId));
  if (isset($results[0])) {
  	$ttData = $results[0];
  	$ttMeta = isset($ttData->topic_type_meta) ? json_decode($ttData->topic_type_meta, true) : array();
  	$fieldmap = isset($ttMeta['field_map']) ? $ttMeta['field_map'] : array();
    $schemaKey = $ttData->schema_key;
    $topicTypeId = $ttData->id;
    $ttIdEncoded = base_convert($topicTypeId, 10, 36);
    $ttName = $ttData->name ? $ttData->name : $schemaKey;
    $ttSpecial = $ttData->special;
    foreach ($fieldmap as $key => $value) {
      if (isset($value['type']) && substr($value['type'], 0, 5) == "core_") {
        if ($editorMode != "message") {  //messages can't travel links. TODO: Maybe one day but will require adding filtering to Interactions. Probably interesting.
          $topicTypeMap[$value['type']] = $value['type'];
        }
  		} else {
        $hiddenPrint = isset($value['hidden_print']) && $value['hidden_print'] == 'true' ? true : false;
        if ($key && !$hiddenPrint && $value['id'] != "0"){
          $fieldId = $value['id'];
          $fieldFriendlyName = isset($value['friendly']) && $value['friendly'] ? $value['friendly'] : "NA";
          $friendlyKey = "{$ttName} | {$fieldFriendlyName}";
          $tokens[] = array(
            "text" => $friendlyKey,
            "topic_type_id" => $ttIdEncoded,
            "field_name" => $key
          );
        }
      }
    }

  $recipientTypeKey = '';
  if ($editorMode == "message") { //add recipient (based on core_person) But only on messages since this will come from the interaction.
      $recipientTypeKey = "core_person";
      $topicTypeMap[$recipientTypeKey] = '';
  }
  //Get linked topic fields, if any
  if (count($topicTypeMap)) {
    $topicListString = "('" . implode("','", array_keys($topicTypeMap)) . "')";
    $results = $wpdb->get_results("SELECT id, name, type_key, schema_key, topic_type_meta FROM alpn_topic_types WHERE type_key IN {$topicListString}");
    foreach ($results as $key => $value) {
    	$ttMeta = isset($value->topic_type_meta) ? json_decode($value->topic_type_meta, true) : array();
    	$fieldmap = isset($ttMeta['field_map']) ? $ttMeta['field_map'] : array();
      $schemaKey = $value->schema_key;
      $topicTypeId = $value->id;
      $ttIdEncoded = base_convert($topicTypeId, 10, 36);
      $ttName = $recipientTypeKey ? "Recipient" : $value->name;
      foreach ($fieldmap as $key1 => $value1) {
        $hiddenPrint = isset($value1['hidden_print']) && $value1['hidden_print'] == 'true' ? true : false;
        if (!$hiddenPrint && $key1 && $value1['id'] != "0" && (isset($value1['type']) && substr($value1['type'], 0, 5) != "core_")) {
          $fieldId = $value1['id'];
          $fieldFriendlyName = $value1['friendly'];
          $friendlyKey = "{$ttName} | {$fieldFriendlyName}";
          $tokens[] = array(
            "text" => $friendlyKey,
            "topic_type_id" => $ttIdEncoded,
            "field_name" => $key1
          );
        }
      }
    }
  }
  }
  sort($tokens);

  return json_encode($tokens, true);
}

function pte_name_extract($theMap){
  $extractedNames = array();
  foreach ($theMap as $key => $value) {    //TODO find the right function
    $extractedNames[$key] = isset($value['friendly']) ? $value['friendly'] : $key;
 }
  return ($extractedNames);
}


function pte_file_interaction_away($processId) {

  alpn_log('pte_file_interaction_away');

  global $wpdb;
  $uxMeta = array();

  $results = $wpdb->get_results(
  	$wpdb->prepare("SELECT ux_meta FROM alpn_interactions WHERE process_id = %s", $processId)
   );

   if (isset($results[0])) {
  	 $interactionDetails = $results[0];
  	 $uxMeta = json_decode($interactionDetails->ux_meta, true);
  	 $fileInteractionOperation = isset($uxMeta['interaction_file_away_handling']) ? $uxMeta['interaction_file_away_handling'] : false;

  	 switch ($fileInteractionOperation) {

  		case 'delete_interaction':
  			alpn_log('delete_interaction');

  			$whereClause['process_id'] = $processId;
  			$wpdb->delete( 'alpn_interactions', $whereClause );
   		break;
  		case 'archive_interaction':
  			alpn_log('archive_interaction');

        $interactionData = array(
          "state" => "filed"
        );
        $whereClause['process_id'] = $processId;
        $wpdb->update( 'alpn_interactions', $interactionData, $whereClause );

  		break;

  		case 'decline_archive_interaction':
  			alpn_log('decline_archive_interaction');

  		break;
  	}

   }

   return $uxMeta;
}


function pte_remove_proteam_member($rowToDelete) {

  alpn_log('pte_remove_proteam_member');

  global $wpdb;
  $ptRow = array();

  $proTeamMemberResults = $wpdb->get_results(
  	$wpdb->prepare("SELECT id, topic_id, wp_id, process_id FROM alpn_proteams WHERE id = %d", $rowToDelete)
   );

  if (isset($proTeamMemberResults[0])) {

  	$ptRow = $proTeamMemberResults[0];
  	$wpId = $ptRow->wp_id;
  	$topicId = $ptRow->topic_id;

  	$deletedChannelToo = false;
  	if ($wpId) {
  		$data = array(
  			'topic_id' => $topicId,
  			'user_id' => $wpId
  		);
  		$deletedChannelToo = pte_manage_cc_groups("delete_member", $data);   //TODO handle async. Takes several seconds.
  	}
  	$deleteResults = $wpdb->delete('alpn_proteams', array('id' => $rowToDelete));
  }
  return (array)$ptRow;
}

function pte_filename_sanitizer($name) {
    // remove illegal file system characters https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
    $name = str_replace(array_merge(
        array_map('chr', range(0, 31)),
        array('<', '>', ':', '"', '/', '\\', '|', '?', '*')
    ), '', $name);
    // maximise filename length to 255 bytes http://serverfault.com/a/9548/44086
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $name= mb_strcut(pathinfo($name, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($name)) . ($ext ? '.' . $ext : '');
    return $name;
}

function pte_get_short_id() {
  $shortUuid = new ShortUuid();
  return $shortUuid->uuid4();
}

function getRootPath()
{
    return str_replace("\\","/",realpath(dirname(dirname(__FILE__))));
}

function getRootUrl()
{
    return PTE_ROOT_URL;
}

function pte_manage_interaction($payload) {
  //TODO MAKE SURE ALL OF THIS IS securable with nonces
    $sitePath = getRootUrl() . "pte_interactions.php";
    pte_async_job ($sitePath, array("data" => json_encode($payload)));
}

function vit_store_kvp($verificationKey, $data) {
  global $wpdb;
  $kvpData = array(
    "item_key" => $verificationKey,
    "item_value" => json_encode($data)
  );
  $wpdb->insert( 'alpn_kvp', $kvpData);
}

function vit_get_kvp($verificationKey) {
  global $wpdb;
  $kvpData = $wpdb->get_results(
    $wpdb->prepare("SELECT item_value FROM alpn_kvp WHERE item_key = %s", $verificationKey)
  );
  if (isset($kvpData[0])) {
    $whereclause = array("item_key" => $verificationKey);
    $wpdb->delete( "alpn_kvp", $whereclause );
    return json_decode($kvpData[0]->item_value, true);
  }
  return array();
}

function vit_send_async_connect($data) {
  $verificationKey = pte_get_short_id();
  $params = array(
    "verification_key" => $verificationKey
  );
  vit_store_kvp($verificationKey, $data);
  try {
    pte_async_job(PTE_ROOT_URL . "vit_async_connect.php", array('verification_key' => $verificationKey));
  } catch (Exception $e) {
    alpn_log ($e);
  }
}

//When member changes main email. Update in profile, Also make connections based on members new email if they and other members are double opted in
function vit_update_contacts_new_email ($userId, $newEmailAddress){
  global $wpdb;
  $newMemberData = $whereClause = array();
  $memberData = $wpdb->get_results(
    $wpdb->prepare("SELECT id, alt_id, topic_content FROM alpn_topics WHERE owner_id = %d AND special='user'", $userId)
  );
  if (isset($memberData[0]) && $memberData[0]->alt_id != $newEmailAddress) {
    alpn_log('Needs to be updated');
    //alpn_log($_REQUEST);
    //update  record
    $topicContent = json_decode($memberData[0]->topic_content, true);
    $topicContent['person_email'] = $newEmailAddress;
    $newMemberData['alt_id'] = $newEmailAddress;
    $newMemberData['topic_content'] = json_encode($topicContent);
    $whereClause['id'] = $memberData[0]->id;
    $wpdb->update( 'alpn_topics', $newMemberData, $whereClause );
    $connectList = $wpdb->get_results(
      $wpdb->prepare("SELECT a1.alt_id, a1.connected_owner_id FROM alpn_member_connections_wtc a1 LEFT JOIN alpn_member_connections_wtc a2 ON a2.connected_owner_id = a1.owner_id WHERE a1.owner_id = %d LIMIT 1", $userId)
    );
    if (isset($connectList[0])) {
      alpn_log('Connections List');
    //  alpn_log($connectList);
      foreach($connectList as $key => $value) {
        $data = array(
          "alt_id" => $value->alt_id,
          "connected_owner_id" => $value->connected_owner_id,
          "user_id" => $userId
        );
        vit_send_async_connect($data);
      }
    }
  }
}

function wsc_get_permissions_string($value){
  $permissionStrings = array(
    "0" => "View Only",
    "1" => "View and Print",
    "2" => "View, Print, Copy and Download"
  );
  return $permissionStrings[$value];
}

function pte_get_link_expiration_string($value){
	$linkExpirationMap = array(
    "0" => "Does Not Expire",
		"30" => "30 Mins",
		"60" => "1 Hour",
		"480" => "8 Hours",
		"1440" => "1 Day",
		"2880" => "2 Days",
		"10080" => "1 Week"
	);
	return $linkExpirationMap[$value];
}

function pte_send_mail ($data = array()) {

  global $wpdb;

  $siteDomain = PTE_HOST_DOMAIN_NAME;
  $smallUser = PTE_ROOT_URL . "dist/assets/small_user_w.png";

  $emailType = isset($data['email_type']) && $data['email_type'] ? $data['email_type'] : 'view-download';
  $topicId = isset($data['topic_id']) && $data['topic_id'] ? $data['topic_id'] : false;

  $passWordString = isset($data['link_interaction_password']) && $data['link_interaction_password'] ? "Required" : "Not Required";
  $linkInteractionExpiration = isset($data['link_interaction_expiration']) && $data['link_interaction_expiration'] ? pte_get_link_expiration_string($data['link_interaction_expiration']) : "Does Not Expire";
  $linkInteractionOptions = isset($data['link_interaction_options']) && $data['link_interaction_options'] ? wsc_get_permissions_string($data['link_interaction_options']) : "View Only";

  $fromName =  $data['from_name'];
  $fromEmail =  $data['from_email'];

  $mailer = WC()->mailer();
  $template = 'vit_generic_email_template.php';
  //format the email

  $fromFirstName = isset($data['from_first_name']) && $data['from_first_name'] ? $data['from_first_name'] : false;

  $toEmail = $data['to_email'];
  $toName =  $data['to_name'];

  $isLink = isset($data['link_type']) && $data['link_type'] == "file" ? true : false;

  $emailSubject =  isset($data['subject_text']) && $data['subject_text'] ? $data['subject_text'] : "No Subject";

  $fileName = isset($data['vault_file_name']) && $data['vault_file_name'] ? $data['vault_file_name'] : " -";
  $fileDescription = isset($data['vault_file_description']) && $data['vault_file_description'] ? $data['vault_file_description'] : " -";
  $fileLinkButton = isset($data['link_id']) && $data['link_id'] ? "<div class='pte_button' title='Securely View this File at Wiscle.com'><a class='pte_button_link' href='https://{$siteDomain}/viewer/?{$data['link_id']}'>View File in Browser...</a></div>" : "" ;

  $replaceStrings["-{pte_site_domain}-"] = $siteDomain;
  $replaceStrings["-{pte_email_body}-"] = $data['body_text'];

  $topicInfoSection = "";
  if ($topicId) {
    $topicInfo = $wpdb->get_results(
      $wpdb->prepare("SELECT t.name, t.about, t2.topic_content, t.email_route_id, f.pstn_number FROM alpn_topics t LEFT JOIN alpn_topics t2 ON t2.owner_id = t.owner_id AND t2.special = 'user' LEFT JOIN alpn_pstn_numbers f ON f.topic_id = t.id WHERE t.id = %d", $topicId)
    );

    if (isset($topicInfo[0])) {

      $topicInfo = $topicInfo[0];

      $pstnRoutingNumber = $topicInfo->pstn_number? pte_format_pstn_number($topicInfo->pstn_number) : "--";
      $emailRoutingAddress = $topicInfo->email_route_id ? $topicInfo->email_route_id . "@files.wiscle.com" : "--";
      $topicContent = json_decode($topicInfo->topic_content, true);
      $fromFirstName = $topicContent['person_givenname'] ? $topicContent['person_givenname'] : "Wiscle Member";
      $topicName = $topicInfo->name ? $topicInfo->name : "--";
      $topicAbout = $topicInfo->about ? $topicInfo->about : "--";

      if ($topicId && $emailType == "proteam_invitation"){
        $topicInfoSection = "
        <div id='wsc_table_description_0'>Invitation to Collaborate</div>
        <div id='wsc_table_description_1'>Regarding:</div>
        <table class='wsc_file_table'>
          <tr class='wsc_file_row'>
            <td class='wsc_file_left_cell wsc_file_borders wsc_bg_white'>Topic Name</td><td class='wsc_file_right_cell wsc_file_borders wsc_bg_white'>{$topicName}</td>
          </tr>
          <tr>
            <td class='wsc_file_left_cell wsc_file_borders wsc_bg_white'>About</td><td class='wsc_file_right_cell wsc_file_borders wsc_bg_white'>{$topicAbout}</td>
          </tr>
        </table>
        <div id='wsc_table_description_2'>Route files to this Topic by:</div>
        <table class='wsc_file_table'>
          <tr class='wsc_file_row'>
            <td class='wsc_file_left_cell wsc_file_borders wsc_bg_white'>Email</td><td class='wsc_file_right_cell wsc_file_borders wsc_bg_white'>{$emailRoutingAddress}</td>
          </tr>
          <tr>
            <td class='wsc_file_left_cell wsc_file_borders wsc_bg_white'>Fax</td><td class='wsc_file_right_cell wsc_file_borders wsc_bg_white'>{$pstnRoutingNumber}</td>
          </tr>
        </table>
        ";
      }
    }
  }
  $replaceStrings["-{wsc_topic_info_section}-"] = $topicInfoSection;

  $noFormatEmail = str_replace("@", "<span>@</span>", $fromEmail);
  $noFormatEmail = str_replace(".", "<span>.</span>", $fromEmail);
  $bodyTitle =  "<span title='Sent From'><img style='vertical-align: top;' src='{$smallUser}'>{$fromName} -- {$noFormatEmail}</span>";

  $linkSection = "";
  if ($isLink) {
    $linkSection = "
    <div id='wsc_table_description_0'>Secure File xLink Received</div>
    <div id='wsc_table_description_2'>Regarding:</div>
    <table class='wsc_file_table'>
      <tr class='wsc_file_row'>
        <td class='wsc_file_left_cell wsc_file_borders wsc_bg_white'>Topic</td><td class='wsc_file_right_cell wsc_file_borders wsc_bg_white'>{$topicName}</td>
      </tr>
      <tr>
        <td class='wsc_file_left_cell wsc_file_borders wsc_bg_white'>About</td><td class='wsc_file_right_cell wsc_file_borders wsc_bg_white'>{$topicAbout}</td>
      </tr>
      <tr class='wsc_file_row'>
        <td class='wsc_file_left_cell wsc_file_borders wsc_bg_white'>File Name</td><td class='wsc_file_right_cell wsc_file_borders wsc_bg_white'>{$fileName}</td>
      </tr>
      <tr>
        <td class='wsc_file_left_cell wsc_file_borders wsc_bg_white'>Description</td><td class='wsc_file_right_cell wsc_file_borders wsc_bg_white'>{$fileDescription}</td>
      </tr>
      <tr>
        <td class='wsc_file_left_cell wsc_file_borders wsc_bg_white'>Permissions</td><td class='wsc_file_right_cell wsc_file_borders wsc_bg_white'>{$linkInteractionOptions}</td>
      </tr>
      <tr>
        <td class='wsc_file_left_cell wsc_file_borders wsc_bg_white'>Expiration</td><td class='wsc_file_right_cell wsc_file_borders wsc_bg_white'>{$linkInteractionExpiration}</td>
      </tr>
      <tr>
        <td class='wsc_file_left_cell wsc_file_borders wsc_bg_white'>Passcode</td><td class='wsc_file_right_cell wsc_file_borders wsc_bg_white'>{$passWordString}</td>
      </tr>
      <tr>
        <td class='wsc_file_left_cell'>{$fileLinkButton}</td><td class='wsc_file_right_cell'></td>
      </tr>
    </table>
    ";
  }

  $replaceStrings["-{wsc_link_section}-"] = $linkSection;

  $emailTemplateHtml = "
    <style>
    #wsc_table_description_0{
      margin-bottom: 20px;
      color: #005587;
      text-align: left;
      line-height: 24pt;
      font-size: 18pt;
    }
    #wsc_table_description_1{
    }
    #wsc_table_description_2{
      margin-top: 30px;
    }
    .wsc_file_table{
      width: 100%;
      border-collapse: collapse;
    }
    .wsc_file_left_cell{
      width: 35%;
      font-weight: bold;
      padding: 3px 5px !important;
      vertical-align: top;
    }
    .wsc_file_right_cell{
      width: 65%;
      padding: 3px 5px !important;
      vertical-align: top;
    }
    .wsc_bg_white {
      background-color: white;
    }
    .pte_button_link {
    	color: white !important;
      text-decoration: none !important;
    }
    .wsc_file_borders {
      border: solid 1px #dcdcdc;
    }
    .pte_button {
  		width: 180px;
  		height: 30px;
  		background-color: #0074BB;
  		text-align: center;
  		line-height: 24pt;
  		font-size: 12pt;
      margin-top: 20px;
    }
    .element_bold{
      font-weight: bold;
    }
    </style>
    <p>-{pte_email_body}-</p>
    <p>-{wsc_link_section}-</p>
    <p>-{wsc_topic_info_section}-</p>
  ";

  $emailTemplateHtml = str_replace(array_keys($replaceStrings), $replaceStrings, $emailTemplateHtml);

  $content = 	wc_get_template_html( $template, array(
  		'email_heading' => $bodyTitle,
  		'email'         => $mailer,
      'email_body'    => $emailTemplateHtml
  	), PTE_ROOT_PATH . 'woocommerce/emails/', PTE_ROOT_PATH . 'woocommerce/emails/');

  if ($fromName && $fromEmail){
    $header = 'Reply-to: ' . $fromName . ' <' . $fromEmail . ">\r\n";
  }

  try {
    global $senderName;  //hacky fix
    $senderName = $fromName;
    $mailer->send( $toEmail, $emailSubject, $content, $header );
    $senderName = "Wiscle";
  } catch (Exception $e) {
      alpn_log ('Caught exception: '. $e->getMessage());
  }
}


function pte_send_group_mms($data){

  $data = array(
    "numbers" => array(0 => "+14084100365", 1 => "+16505268577"),
    "message" => "Hi Baby, it's me geeking out with Group Texting...I need this for Wiscle anyway!"
  );


  $accountSid = ACCOUNT_SID;
  $authToken = AUTHTOKEN;
  $messagingServiceId = MESSAGINGSERVICEID;

  $twilio = new Client($accountSid, $authToken);

  $numbers = $data['numbers'];
  $message = $data['message'];



      try {


        $conversation = $twilio->conversations->v1->conversations
                                                  ->create([
                                                           ]
                                                  );

        pp($conversation->sid);

        // $participant = $twilio->conversations->v1->conversations($conversation->sid)
        //                                          ->participants
        //                                          ->create([
        //                                                       "messagingBindingProjectedAddress" => "+14083518493"
        //                                                   ]
        //                                          );



        foreach ($numbers as $value) {
          pp($value);

          $participant = $twilio->conversations->v1->conversations($conversation->sid)
                                         ->participants
                                         ->create([
                                                      "messagingBindingAddress" => $value
                                                  ]
                                         );

        pp($participant->sid);

      }

        $message = $twilio->conversations->v1->conversations($conversation->sid)
                                     ->messages
                                     ->create([
                                                  "body" => $message,
                                                  "author" => "friend"
                                              ]
                                     );

pp($message->sid);


      } catch (Exception $e) {
          $response = array(
              'message' =>  $e->getMessage(),
              'code' => $e->getCode(),
              'error' => $e
          );
          pp("Prob Conversations...");
          pp($response);
          return;
      }

//  pp($data);
}


function pte_send_sms($data){

  alpn_log("pte_send twilio SMS...");
  global $wpdb;
  $domainName = PTE_HOST_DOMAIN_NAME;

  $fromName = isset($data['from_name']) ? $data['from_name'] : 'Error';
  $sendMobileNumber = isset($data['send_mobile_number']) ? "+1" . preg_replace('/\D/', '', $data['send_mobile_number'])  : '';
  $subject = isset($data['subject_text']) ? $data['subject_text'] : 'File Received';
  $body = isset($data['body_text']) ? $data['body_text'] : '';
  $link = isset($data['link_id']) ? "https://{$domainName}/viewer/?" . $data['link_id'] : '';
  $fileName = isset($data['vault_file_name']) ? $data['vault_file_name'] : '';
  $notificationType = isset($data['type']) && $data['type'] ? $data['type'] : 'send_link';

  switch ($notificationType) {
    case "send_link":
      $body = "Wiscle.com: Secure link received from {$fromName}\n{$subject}\n{$body}\n$fileName -- {$link}";
      $body = substr($body, 0, 1575);
    break;
    case "notification":
      $body = "Wiscle.com: {$subject}\n{$body}";
    break;
  }

  $accountSid = ACCOUNT_SID;
  $authToken = AUTHTOKEN;
  $messagingServiceId = MESSAGINGSERVICEID;

  try {
    $twilio = new Client($accountSid, $authToken);
    $message = $twilio->messages
        ->create($sendMobileNumber, // to
                 [
                     "body" => $body,
                     "messagingServiceSid" => $messagingServiceId
                 ]
        );
  } catch (Exception $e) {
      $response = array(
          'message' =>  $e->getMessage(),
          'code' => $e->getCode(),
          'error' => $e
      );
      alpn_log("pte_manage_user_sync EXCEPTION...");
      alpn_log($response);
      return;
  }
}


function pte_duplicate_topic_type($data){

  //alpn_log('pte_duplicate_topic_type');
  //alpn_log($data);
  global $wpdb;
  $relatedId = $data["related_id"];
  $topicTypeMap = $data["topic_type_map"];
  $topicTypeValue = $data["topic_type_value"];
  $newOwnerId = $data["new_owner_id"];
  $formId = $topicTypeValue['form_id'];
  $typeKey = $topicTypeValue['type_key'];
  $skipTopicLinks = isset($data['skip_topic_links']) ? $data['skip_topic_links'] : false;
  $uuid = $topicTypeValue['uuid'];
  $newTypeKey = "{$typeKey}_{$uuid}";
  $nameDetail = $relatedId ? " - {$relatedId}" : "";

  if ($formId && $newOwnerId) {
    $resultsPosts = $wpdb->get_results($wpdb->prepare("SELECT * FROM wp_posts WHERE ID = %d", $formId));
    if (isset($resultsPosts[0])) {
      //create new wpform based on source
       $postData = $resultsPosts[0];
       $now = date ("Y-m-d H:i:s", time());
       $nowGm = gmdate ("Y-m-d H:i:s", time());
       unset($postData->ID);
       $postData->post_date = $now;
       $postData->post_modified = $now;
       $postData->post_date_gmt = $nowGm;
       $postData->post_modified_gmt = $nowGm;
       $postData->post_author = 1;
       $postData->post_title = "User - {$newTypeKey}";
       $postData->post_name = "{$newTypeKey}";
       $wpdb->insert( 'wp_posts', (array) $postData );
       $newFormId = $wpdb->insert_id;
       $postContent = json_decode($postData->post_content, true);
       $postContent['id'] = $newFormId;
       $newContent['post_content'] = json_encode($postContent);
       $whereClause['ID'] = $newFormId;
       $wpdb->update( 'wp_posts', $newContent, $whereClause );
       //create newTopicType based on source
       unset($topicTypeValue['id']);
       $sourceTypeKey = $topicTypeValue['type_key'];
       $topicTypeValue['type_key'] = $topicTypeMap[$topicTypeValue['type_key']];
       $topicTypeValue['form_id'] = $newFormId;
       $topicTypeMeta = json_decode($topicTypeValue['topic_type_meta'], true);
       $fieldMap = $topicTypeMeta['field_map'];
       foreach ($fieldMap as $key1 => $value1 ) {    //Maps all core fields to their new topics.
         $typeKey = isset($value1['type']) ? $value1['type'] : "";
         $pos = strpos($typeKey, "_", strpos($typeKey, "_") + 1);
         if ($pos) {
           $typeKey = substr($typeKey, 0, $pos);
         }
         if (substr($typeKey, 0, 5) == 'core_') {
           if ($skipTopicLinks) {
             unset($topicTypeMeta['field_map'][$key1]);
          } else {
            $topicTypeMeta['field_map'][$key1]['type'] = $topicTypeMap[$typeKey];
          }
         }
       }
       $topicTypeValue['topic_type_meta'] = json_encode($topicTypeMeta);
       $topicTypeValue['owner_id'] = $newOwnerId;
       $topicTypeValue['topic_state'] = "user";
       $topicTypeValue['source_type_key'] = $sourceTypeKey;
       $topicTypeValue['name'] = $topicTypeValue['name'] . $nameDetail;
       $wpdb->insert( 'alpn_topic_types', $topicTypeValue);
       $newTopicTypeId = $wpdb->insert_id;
       return $newTopicTypeId;
     }
 }
 return false;
}

function pte_topic_type_deep_copy($sourceTopicTypeId, $newOwnerId) {

  global $wpdb;
  $topicTypeMap = array();

  alpn_log("pte_topic_type_deep_copy");
  //Get the desired topic
  $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM alpn_topic_types WHERE id = %d",	$sourceTopicTypeId));
  if (isset($results[0])) {

  	$ttData = $results[0];
  	$ttMeta = isset($ttData->topic_type_meta) ? json_decode($ttData->topic_type_meta, true) : array();
  	$fieldmap = isset($ttMeta['field_map']) ? $ttMeta['field_map'] : array();

    //Determine what topic types are used by selected topic type and create mappings to new ones for this user. Deep Copy. TODO we will want to allow users to map to their existing topics
    foreach ($fieldmap as $key => $value) {   //prepare unique new ids for forthcoming tts Must include all required mappings for dupe.
      if (substr($value['type'], 0, 5) == "core_") {
        $newUuid = pte_get_short_id();
        $typeKey = $value['type'];
        $newTypeKey = "{$typeKey}_{$newUuid}";
        $topicTypeMap[$typeKey] = $newTypeKey;
  		}
    }
  }

  //go get all TTs that make up the deep copy.
  $topicListString = "('" . implode("','", array_keys($topicTypeMap)) . "')";
  $results = $wpdb->get_results("SELECT * FROM alpn_topic_types WHERE type_key IN {$topicListString}");

  $relatedId = substr(str_shuffle("0123456789"), 0, 3);

  foreach ($results as $key => $value) {
    $data = array(
      "related_id" => $relatedId,
      "new_owner_id" => $newOwnerId,
      "topic_type_map" => $topicTypeMap,
      "topic_type_value" => (array) $value
    );
    //Making Copies
    $newTopicTypeId = pte_duplicate_topic_type($data);
  }

  $currentTypeKey = $ttData->type_key;
  $newUuid = pte_get_short_id();
  $newTypeKey = "{$currentTypeKey}_{$newUuid}";
  $topicTypeMap[$currentTypeKey] = $newTypeKey;

  $data = array(
    "related_id" => $relatedId,
    "new_owner_id" => $newOwnerId,
    "topic_type_map" => $topicTypeMap,
    "topic_type_value" => (array) $ttData
  );
  $newTopicTypeId = pte_duplicate_topic_type($data);

  return $newTopicTypeId;
}

function pte_topic_type_copy ($sourceTopicTypeId, $newOwnerId) {

  global $wpdb;
  $topicTypeMap = array();

  alpn_log("pte_topic_type_copy...");
  //Get the desired topic
  $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM alpn_topic_types WHERE id = %d",	$sourceTopicTypeId));
  if (isset($results[0])) {
      $ttData = $results[0];
      $ttMeta = isset($ttData->topic_type_meta) ? json_decode($ttData->topic_type_meta, true) : array();
      $fieldmap = isset($ttMeta['field_map']) ? $ttMeta['field_map'] : array();

      $relatedId = substr(str_shuffle("0123456789"), 0, 3);
      $currentTypeKey = $ttData->type_key;
      $currentTypeKeyArray = explode("_", $currentTypeKey);
      if (count($currentTypeKeyArray) == 3) {
        $currentTypeKey = $currentTypeKeyArray[0] . "_" . $currentTypeKeyArray[1];
      }

      $newUuid = pte_get_short_id();
      $newTypeKey = "{$currentTypeKey}_{$newUuid}";
      $topicTypeMap[$currentTypeKey] = $newTypeKey;

      $data = array(
      "related_id" => $relatedId,
      "new_owner_id" => $newOwnerId,
      "topic_type_map" => $topicTypeMap,
      "topic_type_value" => (array) $ttData,
      "skip_topic_links" => true
      );
      $newTopicTypeId = pte_duplicate_topic_type($data);

      return $newTopicTypeId;
    }
    return false;
  }

  function pte_create_topic($formId, $ownerId, $data, $iconImage = '', $logoImage = '', $emailRoute = '') {
    alpn_log('pte_create_topic');
    $entry = array(
      'id' => $formId,  //source user template type  Using custom TT
      'owner_id' => $ownerId,
      'fields' => $data,
      'icon_image' => $iconImage,
      'logo_image' => $logoImage,
      'create_email_route' => $emailRoute
    );
    return alpn_handle_topic_add_edit ('', $entry, '', '' );	//Add user
  }


function pte_create_default_topics($newOwnerId, $createSampleData = false) {

  global $wpdb;
  $shortUuid = new ShortUuid();
  $topicState = "active";
  $coreUserFormId = "";
  $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM alpn_topic_types WHERE topic_state = %s", $topicState));
  $topicTypeMap = array();

  foreach ($results as $key => $value) {   //prepare unique new ids for forthcoming tts Must include all required mappings for dupe.
    $newUuid = pte_get_short_id();
    $typeKey = $value->type_key;
    $newTypeKey = "{$typeKey}_{$newUuid}";
    $topicTypeMap[$typeKey] = $newTypeKey;
    $results[$key]->uuid = $newUuid;
  }
  foreach ($results as $key => $value) {
    $data = array(
      "related_id" => "",
      "new_owner_id" => $newOwnerId,
      "topic_type_map" => $topicTypeMap,
      "topic_type_value" => (array) $value
    );
    $duplicateResult = pte_duplicate_topic_type($data);
    }
    $topicTypeListString = "('" . implode("','", array_values($topicTypeMap)) . "')";
    $formRows = $wpdb->get_results("SELECT form_id, type_key FROM alpn_topic_types WHERE type_key IN {$topicTypeListString}");
    if (isset($formRows[0])) {
      foreach ($formRows as $key => $value) {
        if ($value->type_key == $topicTypeMap['core_user']) {
          $coreUserFormId = $value->form_id;
        }
        if ($createSampleData && $value->type_key == $topicTypeMap['core_contact']) {
          $iconImage = '6d906171ee3c42c492d350f52a0056d1.jpg';
          $sampleContact1 = array(
            0 => "{}",
            4 => "Miranda (sample)",
            2 => "Chang",
            6 => "Fiduciary",
            5 => "38",
            1 => "adipiscing@eratVivamusnisi.org",
            10 => "https://linkedin.com/arbella32",
            8 => "(873) 800-0488",
            3 => "(408) 357-2824",
            7 => "#aginglife",
            9 => "Expert in the field."
          );
          $sampleContact1Id = pte_create_topic($value->form_id, $newOwnerId, $sampleContact1, $iconImage);
          $iconImage = 'b98c23c4d0c2447f83183d394ac8e211.jpg';
          $sampleContact2 = array(
            0 => "{}",
            4 => "Rudyard (sample)",
            2 => "Lambert",
            6 => "Geriatrician",
            5 => "40",
            1 => "laoreet@Suspendisse.org",
            10 => "https://linkedin.com/lambertr01",
            8 => "(778) 275-6832",
            3 => "(408) 357-2824",
            7 => "#concierge #medical",
            9 => "Primary contact of Suspendisse medical team."
          );
          $sampleContact2Id = pte_create_topic($value->form_id, $newOwnerId, $sampleContact2, $iconImage);
        }
        if ($createSampleData && $value->type_key == $topicTypeMap['core_person']) {
          $iconImage = '03d5606550d740de82b9120a16c38ac1.jpg';
          $samplePerson1 = array(
            0 => "{}",
            4 => "Harriet (sample)",
            2 => "Kalinski",
            6 => "Customer",
            5 => "41",
            1 => "hkalinski12@xfinity.com",
            10 => "",
            8 => "(227) 555-6832",
            3 => "",
            7 => "#caregiving #meds",
            9 => "Sweet lady requires regular help with meds. Has 24/7 care."
          );
          $samplePerson1EmailId = $shortUuid->uuid4();
          $samplePerson1Id = pte_create_topic($value->form_id, $newOwnerId, $samplePerson1, $iconImage, "", $samplePerson1EmailId);
        }
        if ($createSampleData && $value->type_key == $topicTypeMap['core_organization']) {
          $iconImage = '91d3a2bf8d404858a4799b9f78850ba8.png';
          $logoImage = 'c7791a4c74dc43e9897035d2b1e53536.png';
          $sampleOrganization1 = array(
            0 => "{}",
            7 => "Acme Corporation (sample)",
            5 => "(619) 555-1233",
            3 => "(408) 357-2824",
            2 => "info@acmecorp.cc",
            8 => "https://acmecorp.cc",
            6 => "Maker of fine rockets and associated gear."
          );
          $sampleOrganization1Id = pte_create_topic($value->form_id, $newOwnerId, $sampleOrganization1, $iconImage, $logoImage);
        }
        if ($createSampleData && $value->type_key == $topicTypeMap['core_general']) {
          $iconImage = '9deaa5a2e2b84bc590daa2c1a409d481.png';
          $sampleGeneral1 = array(
            0 => "{}",
            2 => "White Paper Research (sample)",
            1 => "A place to organize and discuss our findings and recommendations."
          );
          pte_create_topic($value->form_id, $newOwnerId, $sampleGeneral1, $iconImage);
        }
        if ($createSampleData && $value->type_key == $topicTypeMap['core_place']) {
          $samplePlace1 = array(
            0 => "{}",
            8 => "Home (sample)",
            4 => "1029 Summer Breeze Street",
            1 => "San Diego",
            2 => "CA",
            3 => "96192",
            6 => "(619) 555-3957",
            5 => "(408) 357-2824",
            7 => "Main Residence",
            9 => ""
          );
          $samplePlace1Id = pte_create_topic($value->form_id, $newOwnerId, $samplePlace1);
          $samplePlace2 = array(
            0 => "{}",
            8 => "Office (sample)",
            4 => "222 Borderline Avenue, Suite B",
            1 => "San Diego",
            2 => "CA",
            3 => "96193",
            6 => "(619) 555-9385",
            5 => "(408) 357-2824",
            7 => "Headquarters",
            9 => ""
          );
          $samplePlace2Id = pte_create_topic($value->form_id, $newOwnerId, $samplePlace2);
        }
      }
    }
    return array(  //TODO rework this ugly thing
      'core_user_form_id' => $coreUserFormId,
      'sample_place_id_1' => $samplePlace1Id,
      'sample_place_id_2' => $samplePlace2Id,
      'sample_organization_id_1' => $sampleOrganization1Id,
      'sample_person_id_1' => $samplePerson1Id,
      'sample_person_email_id_1' => $samplePerson1EmailId
    );
}


function pte_manage_link($operation, $requestData){
  global $wpdb;
  switch ($operation) {
    case "create_link":
      $linkKey = pte_get_short_id();
      $linkType = isset($requestData['link_type']) ? $requestData['link_type'] : 'file';
      $ownerId = isset($requestData['owner_id']) ? $requestData['owner_id'] : 0;
      $vaultId = isset($requestData['vault_id']) ? $requestData['vault_id'] : 0;
      $about = isset($requestData['link_about']) ? $requestData['link_about'] : 'Manual';
      $now = date ("Y-m-d H:i:s", time());
      $rowData = array(
        'owner_id' => $ownerId,
        'uid' => $linkKey,
        'vault_id' => $vaultId,
        'link_type' => $linkType,
        'about' => $about,
        'link_meta' => json_encode($requestData),
        'created_date' => $now,
        'last_update' => $now
      );
      $wpdb->insert( 'alpn_links', $rowData );
      return $linkKey;
    break;
    case "expire_link":
      $linkId = isset($requestData['link_id']) ? $requestData['link_id'] : 0;
      $ownerId = isset($requestData['owner_id']) ? $requestData['owner_id'] : 0;
      $now = date ("Y-m-d H:i:s", time());
      $linkData = array(
        "expired" => 'true',
        'last_update' => $now
      );
      $whereClause = array(
        'owner_id' => $ownerId,
        'id' => $linkId
      );
      $wpdb->update( 'alpn_links', $linkData,  $whereClause);
    break;
  }

}

function pte_get_viewer_template() {

  $pdfViewer = "
  <template role='layout-template-container'>
  	<webpdf>
  		<toolbar name='toolbar'>
  			<div style='display: flex; flex-direction: row; padding: 0 0 0 0; border 0;'>
  				<group-list name='home-toolbar-group-list'>
  					<group name='home-tab-group-select' retain-count='7'>
  						<zoom-out-button icon-class='pte_viewer_zoomout_icon'></zoom-out-button>
  						<zoom-in-button icon-class='pte_viewer_zoomin_icon'></zoom-in-button>
  						<editable-zoom-dropdown></editable-zoom-dropdown>
  						<goto-prev-page-button icon-class='pte_viewer_prevpage_icon'></goto-prev-page-button>
  						<goto-next-page-button icon-class='pte_viewer_nextpage_icon'></goto-next-page-button>
  						<goto-page-input></goto-page-input>
  					</group>
  				</group-list>
  			</div>
  		</toolbar>
  		<div class='fv__ui-body'>
  			<sidebar name='pte_sidebar' @controller='sidebar:SidebarController'>
  				<search-sidebar-panel icon-class='pte_viewer_search_icon'></search-sidebar-panel>
  				<bookmark-sidebar-panel icon-class='pte_viewer_bookmark_icon'></bookmark-sidebar-panel>
  				<thumbnail-sidebar-panel icon-class='pte_viewer_thumbnail_icon'></thumbnail-sidebar-panel>
  			</sidebar>
  			<distance:ruler-container name='pdf-viewer-container-with-ruler'>
  				<slot>
  					<viewer @zoom-on-pinch @zoom-on-doubletap @zoom-on-wheel @touch-to-scroll></viewer>
  				</slot>
  			</distance:ruler-container>
  		</div>
  		<print:print-dialog></print:print-dialog>
  		<page-contextmenu></page-contextmenu>
  	</webpdf>
  </template>
  ";

  return $pdfViewer;
}

function pte_get_topic_manager($topicManagerSettings){
  //$sidebarState = isset($topicManagerSettings['sidebar_state']) ? $topicManagerSettings['sidebar_state'] : 'closed';
  $topicTable = do_shortcode("[wpdatatable id=9]");
  $topicTable = str_replace('table_1', 'table_topic_types', $topicTable);
  $topicTable = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $topicTable);

  $deleteButton =  "<i id='pte_delete_topic_type_button' class='far fa-trash-alt pte_topic_type_button pte_ipanel_button_disabled' title='Delete Topic' onclick='pte_delete_topic_link(\"\");'></i>";
  $duplicateButton =  "<i id='pte_dupe_topic_type_button' class='far fa-clone pte_topic_type_button pte_ipanel_button_disabled' title='Delete Topic' onclick='pte_delete_topic_link(\"\");'></i>";
  $extraTableControls =  json_encode("<div class='pte_topic_type_buttons'>{$deleteButton}{$duplicateButton}</div>");

  $addaTopicHtml = pte_get_topic_list('active_core_topic_types', '', 'pte_active_core_topic_types');

  //pte_topic_manager_inner is the container to switch between add/edit
  $html = "";
  $html .= "
    <div class='pte_vault_row pte_topic_manager_outer'>
      <div class='pte_vault_row_25'>
      <div class='pte_editor_title'>
        <div class='pte_vault_row_75'>
          Topic Types -- DNI/WIP
        </div>
        <div class='pte_vault_row_25 pte_vault_right'>
          &nbsp;
        </div>
      </div>
        <div class='pte_topic_type_add_container'>{$addaTopicHtml}</div>
        {$topicTable}
      </div>
      <div id='pte_topic_manager_container' class='pte_vault_row_75'>
        <div id='alpn_message_area' class='pte_template_editor_message_area'></div>
        <div id='pte_topic_manager_inner' class=''>
          &nbsp;
        </div>
      </div>
    </div>
  <script src='https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js'></script>
  <script src='https://cdn.jsdelivr.net/npm/jquery-sortablejs@latest/jquery-sortable.js'></script>
  <script>
    pte_topic_manager_loaded = true;

    jQuery('#pte_active_core_topic_types').select2( {
      theme: 'bootstrap',
      width: '100%',
      allowClear: true,
      closeOnSelect: false,
      placeholder: 'Add a Topic Type...'
    });
    jQuery('#pte_active_core_topic_types').on('select2:select', function (e) {
      var data = e.params.data;
      pte_add_a_new_topic_type(data);
    });
    jQuery('#pte_active_core_topic_types').on('select2:close', function (e) {
      jQuery('#pte_active_core_topic_types').val('').trigger('change');
    });
    alpn_wait_for_ready(10000, 250,  //Network Table
      function(){
        if (typeof wpDataTables != 'undefined' && typeof wpDataTables.table_topic_types != 'undefined') {
            return true;
        }
        return false;
      },
      function(){
        jQuery({$extraTableControls}).insertBefore('#table_topic_types_filter');
      },
      function(){ //Handle Error
        console.log('Error Adding to Table Toolbar...'); //TODO Handle Error
      });

  </script>
  ";
return $html;
}

function pte_create_topic_team_member($data) {    //add to proteam.

    global $wpdb;
    $ownerId = isset($data['owner_id']) ? $data['owner_id'] : '';
    $topicId = isset($data['topic_id']) ? $data['topic_id'] : '';
    $proTeamMemberId = isset($data['proteam_member_id']) ? $data['proteam_member_id'] : '';
    $wpId = isset($data['wp_id']) ? $data['wp_id'] : '';
    $processId = isset($data['process_id']) ? $data['process_id'] : '';
    $state = isset($data['state']) ? $data['state'] : '20';

  	//TODO make this a user options
  	$defaultMemberRights = array(
  		'download' => '0',
  		'share' => '0',
  		'delete' => '0',
  		'fax'  => '0',
  		'email' => '0',
  		'new' => '1',
  		'edit' => '1',
  		'chat' => '1',
  		'action' => '1',
  		'print' => '1',
  		'transfer' => '1',
    );

  	$defaultAccessLevel = "10";
  	$memberRights = json_encode($defaultMemberRights);

  	$proTeamData = array( //TODO start IA and store processID
  		'owner_id' => $ownerId,
  		'topic_id' => $topicId,
  		'proteam_member_id' => $proTeamMemberId,
      'process_id' => $processId,
		  'wp_id' => $wpId,
  		'access_level' => $defaultAccessLevel,
  		'state' => $state,
  		'member_rights' => $memberRights
  	);
  	$wpdb->insert( 'alpn_proteams', $proTeamData );
    $proTeamData['id'] = $wpdb->insert_id;

    return $proTeamData;
}

function pte_is_on_topic_team($topicId, $emailContactTopicId){
  global $wpdb;
  $results = $wpdb->get_results(
    $wpdb->prepare("SELECT id from alpn_proteams WHERE topic_id = %d AND proteam_member_id = %d", $topicId, $emailContactTopicId)
   );
   if (isset($results[0])) {
     return $results[0];
   }
return false;
}

function pte_get_recipient($ownerNetworkId, $topicId){
  //alpn_log('Getting Recipient...');
  global $wpdb;
  $results = $wpdb->get_results(
    $wpdb->prepare("SELECT u.id AS network_important, t.id, t.special, t.owner_id, t.topic_type_id, t.connected_id, t.topic_content, t.dom_id AS email_contact_dom_id, t.connected_network_id, p.access_level, f.pstn_number, tt.id AS topic_type_id, tt.form_id, tt.name AS topic_name, tt.icon, tt.topic_type_meta, tt.html_template, t3.name AS owner_name, t3.topic_content AS owner_topic_content, t2.image_handle AS profile_handle, t2.topic_content AS connected_topic_content, t2.id AS connected_topic_id, t2.dom_id AS connected_topic_dom_id FROM alpn_topics t LEFT JOIN alpn_proteams p ON p.topic_id = t.id AND p.owner_id = t.owner_id LEFT JOIN alpn_pstn_numbers f ON f.topic_id = t.id LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id LEFT JOIN alpn_topics t2 ON t2.owner_id = t.connected_id AND t2.special = 'user' LEFT JOIN alpn_topics t3 ON t3.owner_id = t.owner_id AND t3.special = 'user' LEFT JOIN alpn_user_lists u ON u.item_id = t.id AND u.owner_network_id = %d AND u.list_key = 'pte_important_network' WHERE t.id = %s", $ownerNetworkId, $topicId)
   );
   if (isset($results[0])) {
     return $results[0];
   }
return false;
}

function pte_get_template_editor($editorSettings) {
  //$sidebarState = isset($topicManagerSettings['sidebar_state']) ? $topicManagerSettings['sidebar_state'] : 'closed';
  $topicTable = do_shortcode("[wpdatatable id=9]");
  $topicTable = str_replace('table_1', 'table_topic_types', $topicTable);
  $topicTable = str_replace('"sPaginationType":"full_numbers",', '"sPaginationType":"full",', $topicTable);

  //pte_topic_manager_inner is the container to switch between add/edit
  $html = "";
  $html .= "
    <div class='pte_vault_row pte_topic_manager_outer'>
      <div class='pte_vault_row_25 pte_max_width_25'>
      <div class='pte_editor_title'>
        <div class='pte_vault_row_75'>
          <div>Templates</div>
        </div>
        <div class='pte_vault_row_25 pte_vault_right'>
          &nbsp;
        </div>
      </div>
        {$topicTable}
      </div>
      <div id='pte_topic_manager_container' class='pte_vault_row_75 pte_max_width_75'>
      <div id='alpn_message_area' class='pte_template_editor_message_area'></div>
      <div id='template_editor_container'>
        &nbsp;
      </div>
      </div>
    </div>
  <script>
      pte_template_editor_loaded = true;
  </script>
  ";
return $html;
}

function wsc_get_gallery($gallerySettings) {

  global $wpdb;

  $galleryId = is_numeric(array_key_first($_GET)) ? array_key_first($_GET) : false ;

  $twitterMeta = "{}";

  if ($galleryId) {
    $galleryDetails = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT twitter_meta from alpn_user_metadata WHERE id = %d;", $galleryId)
    );
    if (isset($galleryDetails[0]) && $galleryDetails[0]->twitter_meta != '{}') {
      $twitterMeta = json_decode($galleryDetails[0]->twitter_meta, true);
      $galleryMeta = json_encode(array(
        "set_owner_id" => $twitterMeta['profile']['set_owner_id'],
        "set_id" => $twitterMeta['profile']['set_id']
      ));
    }
  }

  $nftToolbar = wsc_get_nft_view_toolbar();

  $html .= "
  <div class='outer_button_line' style='min-height: 42px; text-align: center; margin: 0 0 10px 0;'>
    <div class='pte_vault_row_100'>
      {$nftToolbar}
    </div>
    <div id='alpn_message_area' class='alpn_message_area' onclick='pte_clear_message();'></div>
  </div>
  <div id='outer-gallery-container' style='padding: 0 20px 0 20px; text-align: center;'>
    <div id='nft-gallery-container' class='nft-gallery-container'>
    </div>
  </div>
  <script>
    wsc_change_nfts('', {$galleryMeta});
  </script>
  ";

  return $html;
}

function pte_get_viewer($viewerSettings){

  $sidebarState = isset($viewerSettings['sidebar_state']) ? $viewerSettings['sidebar_state'] : 'closed';
  $linkKey = isset($viewerSettings['link_key']) ? $viewerSettings['link_key'] : '';

  $data = $_GET;
  $html = $createAccountHtml = "";
  global $wpdb;

  if (!$linkKey) { //get first variable passed in.
    foreach ($data as $key => $value) {
      $linkKey = $key;
      break;
    }
  }

  $viewData = array(
    'link_key' => $linkKey
  );
  pte_async_job(PTE_ROOT_URL . "vit_async_log_visit.php", $viewData);

  $linkKeyLength = strlen($linkKey);
  if ($linkKeyLength >= 20 && $linkKeyLength <= 22) {  //Valid Length.
    $results = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT v.id, v.file_name, v.description, v.mime_type, v.modified_date, l.* FROM alpn_links l LEFT JOIN alpn_vault v ON v.id = l.vault_id WHERE l.uid = '%s';", $linkKey)   //Case sensitive
    );
    if (isset($results[0])) {
      $linkRow = $results[0];

      $linkLastUpdate = $linkRow->last_update;
      $linkMeta = json_decode($linkRow->link_meta, true);

      $sendEmail = isset($linkMeta['send_email_address']) && $linkMeta['send_email_address'] ? $linkMeta['send_email_address'] : false;
      $sendEmailGiven = isset($linkMeta['send_email_address_givenname']) && $linkMeta['send_email_address_givenname'] ? $linkMeta['send_email_address_givenname'] : " - ";
      $sendEmailFamily = isset($linkMeta['send_email_address_familyname']) && $linkMeta['send_email_address_familyname'] ? $linkMeta['send_email_address_familyname'] : " - ";
      $sendEmailTopicId = isset($linkMeta['send_email_source_topic_id']) && $linkMeta['send_email_source_topic_id'] ? $linkMeta['send_email_source_topic_id'] : false;

      if ($sendEmailTopicId && !is_user_logged_in()) {
        $createAccountHtml = "show_cta('<span title=\"Register instantly using email &nbsp;-&nbsp; {$sendEmail}\" onclick=\"vit_create_account_from_topic({$sendEmailTopicId});\" id=\"vit_call_to_action_link\">Create</span> your free collaboration account. No marketing, ever.');";
      }

      $linkInteractionExpiration = isset($linkMeta['link_interaction_expiration']) ? $linkMeta['link_interaction_expiration'] : 0;

      $now = new DateTime();
      $lastUpdateDate = new DateTime($linkLastUpdate);
      $lastUpdateDate->modify("+{$linkInteractionExpiration} minutes");
      $linkExpired = (($lastUpdateDate < $now) && ($linkInteractionExpiration > 0)) || ($linkRow->expired == 'true');

      if ($linkExpired) {
        return ("<div class='pmpro_content_message'><div class='pte_membership_message'>Access to this link has expired for security reasons. Please contact the original sender.</div></div>");
      }

      $linkInteractionPassword = isset($linkMeta['link_interaction_password']) ? $linkMeta['link_interaction_password'] : '';
      $linkInteractionOptions = isset($linkMeta['link_interaction_options']) ? $linkMeta['link_interaction_options'] : 0;
      $templateDirectory = get_template_directory_uri();
      $pdfViewer = pte_get_viewer_template();
      $vaultId = $linkRow->vault_id;
      $linkLastUpdate = $linkRow->last_update;
      $vaultFileName = stripslashes($linkRow->file_name);
      $vaultDescription = stripslashes($linkRow->description);
      $vaultMimeType = $linkRow->mime_type;
      $vaultModifiedDate = $linkRow->modified_date;

      $fileMeta = json_encode(array(
        "file_name" => $vaultFileName,
        "description" => $vaultDescription,
        "mime_type" => $vaultMimeType,
        "modified_data" => $vaultModifiedDate,
        "vault_id" => $vaultId,
        "link_token" => $linkKey
      ));

      $passwordHtml = $md5Password = $viewDocumentHtml = "";
      $downloadFiles = $printFiles = $copyFile = "pte_ipanel_button_disabled";
      $showPassword= 'none';

      if ($linkInteractionPassword) {
        $md5Password = md5($linkInteractionPassword);
        $showPassword = 'inline-block';
        $toolbar = "<div id='pte_viewer_toolbar' class='pte_viewer_toolbar'>
                      <div class='pte_vault_row_100'>
                          <div id='pte_viewer_password_container' class='pte_viewer_password_container' style='display: {$showPassword};'>File Passcode:&nbsp;&nbsp;<input type='text' id='pte_viewer_password_input' placeholder='Required...'><div class='pte_button_new' data-pte-pe='{$md5Password}' data-pte-vi='{$vaultId}' data-pte-io='{$linkInteractionOptions}' data-pte-token='{$linkKey}' onclick='pte_check_viewer_password(this);'>Open</div><span id='pte_check_viewer_password_error'></span></div>
            		      </div>
                    </div>
                  ";
      } else {
        $viewDocumentHtml = "pte_view_document({$vaultId}, '{$linkKey}');";
        if ($linkInteractionOptions == 1) {
          $printFiles = 'pte_ipanel_button_enabled';
        }
        if ($linkInteractionOptions == 2) {
          $printFiles = 'pte_ipanel_button_enabled';
          $downloadFiles = 'pte_ipanel_button_enabled';
          $copyFile = 'pte_ipanel_button_enabled';
        }
        $toolbar = "<div id='pte_viewer_toolbar' class='pte_viewer_toolbar'>
                      <div class='pte_vault_row_40'>
                        <i id='alpn_vault_print' class='far fa-print pte_icon_button {$printFiles}' title='Print File' onclick='alpn_vault_control(\"print\")'></i>
                        <i id='alpn_vault_download_original' class='far fa-file-download pte_icon_button {$downloadFiles}' title='Download Original File' onclick='alpn_vault_control(\"download_original\")'></i>
                        <i id='alpn_vault_download_pdf' class='far fa-file-pdf pte_icon_button {$downloadFiles}' title='Download PDF File' onclick='alpn_vault_control(\"download_pdf\")'></i>
            		      </div>
                      <div class='pte_vault_row_60 pte_vault_right'>
                      <div class='pte_viewer_info_outer wsc_w_small'><div class='pte_viewer_info_inner_message'>File Name</div><div id='pte_viewer_info_filename' class='pte_viewer_info_inner_name'>{$vaultFileName}</div></div>
                      <div class='pte_viewer_info_outer wsc_w_large' style='margin-left: 10px;'><div class='pte_viewer_info_inner_message'>Description</div><div id='pte_viewer_info_description' class='pte_viewer_info_inner_name'>{$vaultDescription}</div></div>
              		    </div>
                    </div>
                  ";
      }


      //TODO  Make Sense? <i id='alpn_vault_copy' class='far fa-file-export pte_icon_button  {$copyFile}' title='Copy File to Linked Topic' onclick='alpn_vault_control(\"copy_file\")'></i>

      //TODO Make Vault Access More seecure at back end and nonces.
      //TODO See what other kinds of controls are needed - for instance number retries. Captcha

      $viewerSettings = json_encode(array(
        "sidebar_state" => $sidebarState
      ));

      $html .= "
          <div id='alpn_vault_preview_embedded'>
              {$toolbar}
              <div id='pte_overlay_viewer'><div id='pte_overlay_message'></div></div>
              <div id='pte_pdf_ui'></div>
              {$pdfViewer}
          </div>
 			 		<script>
 						alpn_templatedir = '{$templateDirectory}-child-master/';
 	          pte_setup_pdf_viewer({$viewerSettings});
            pte_viewer_file_meta = {$fileMeta};
            {$viewDocumentHtml}
            {$createAccountHtml}
 					</script>
      ";
    } else {

      pp("Error1");

    }

  } else {

    pp("Error2");

  }

return $html;
}


function pte_get_page_number($data) { //uses row_number from database and per_page to calculate proper page in table.. Queries need to match those in the tables.

  alpn_log("pte_get_page_number");
  //alpn_log($data);

  global $wpdb;
  $data = $data['data'];
  $type = isset($data['table_type']) ? $data['table_type'] : '';
  $ownerId = isset($data['owner_id']) ? $data['owner_id'] : 0;
  $domId = isset($data['dom_id']) ? $data['dom_id'] : 0;
  $vaultId = isset($data['vault_id']) ? $data['vault_id'] : 0;
  $topicId = isset($data['topic_id']) ? $data['topic_id'] : 0;

  $topicKey = isset($data['topic_key']) ? $data['topic_key'] : 0;
  $permission = isset($data['permission']) ? $data['permission'] : 0;

  $perPage = isset($data['per_page']) ? $data['per_page'] : 5;
  $subjectToken = isset($data['subject_token']) ? $data['subject_token'] : '';

  $connectedTopicId = isset($data['connected_topic_id']) ? $data['connected_topic_id'] : 0;
  $connectedTopicDomId = isset($data['connected_topic_dom_id']) ? $data['connected_topic_dom_id'] : '';

  $topicTypeFormId = isset($data['topic_type_form_id']) ? $data['topic_type_form_id'] : 0;   //Topic Manager

  switch ($type) {
    case "topic_link":   //TODO broken. Only supports one subject_token per topic.
      $query = "
        WITH tempList AS
        (
          SELECT connected_topic_id, row_number() OVER ( order by name ) AS row_num
          FROM alpn_topics_linked_view
          WHERE owner_id = {$ownerId} AND owner_topic_id = {$topicId} AND subject_token = '{$subjectToken}'
        )
        SELECT row_num
        FROM tempList
        WHERE connected_topic_id = '{$connectedTopicId}'
      ";
    break;
    case "network":
      $query = "
        WITH tempList AS
        (
          SELECT dom_id, row_number() OVER ( order by name ) AS row_num
          FROM alpn_topics_network_profile
          WHERE owner_id = {$ownerId}
        )
        SELECT row_num
        FROM tempList
        WHERE dom_id = '{$domId}'
      ";
    break;
    case "topic":
    $query = "
        WITH tempList AS
        (
          SELECT dom_id, row_number() OVER ( order by name ) AS row_num
          FROM alpn_topics_with_joins
          WHERE search_key = {$ownerId}
        )
        SELECT row_num
        FROM tempList
        WHERE dom_id = '{$domId}'
    ";
    break;
    case "vault":
      $query = "
      WITH tempList AS
      (
        SELECT id, row_number() OVER ( order by modified_date DESC ) AS row_num
        FROM alpn_vault_all
        WHERE topic_key = '{$topicKey}' AND access_level <= {$permission}
      )
      SELECT  row_num
      FROM    tempList
      WHERE   id = {$vaultId}
    ";
    break;
    case "table_topic_types":
    $query = "
      WITH tempList AS
      (
        SELECT form_id, row_number() OVER ( order by name ASC ) AS row_num
        FROM alpn_topic_types
        WHERE owner_id = {$ownerId}
      )
      SELECT  row_num
      FROM    tempList
      WHERE   form_id = {$topicTypeFormId}
    ";
    break;
}
  if ($query) {
    $result = $wpdb->get_row($query);
    if (isset($result->row_num)) {
      $rowNum = $result->row_num;
      return intval(($rowNum - 1) / $perPage);
    }
  }
  return -1;
}

function pte_sync_curl_nft($endPoint, $postRequest) {

  //$domainName = "10.138.0.60";

  $host= gethostname();
  $ip_server = gethostbyname($host);
  $domainName = $ip_server;

  $baseUrl = "http://{$domainName}/wp-content/themes/memberlite-child-master/";
  $fullUrl = "{$baseUrl}{$endPoint}.php";
  $headers[] = "Accept: application/json";
  $options = array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 100,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_POST => true,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => $postRequest,
      CURLOPT_URL => $fullUrl,
      CURLOPT_HTTPHEADER => $headers
  );
   $ch = curl_init();
   curl_setopt_array($ch, $options);
   $response = curl_exec($ch);
   curl_close($ch);
   return $response;
}





function pte_sync_curl($endPoint, $postRequest) {
  $domainName = PTE_HOST_DOMAIN_NAME;
  $baseUrl = "https://{$domainName}/wp-content/themes/memberlite-child-master/topics/";
  $fullUrl = "{$baseUrl}{$endPoint}.php";
  $headers[] = "Accept: application/json";
  $options = array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 100,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_POST => true,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => array('payload' => $postRequest),
      CURLOPT_URL => $fullUrl,
      CURLOPT_HTTPHEADER => $headers
  );
   $ch = curl_init();
   curl_setopt_array($ch, $options);
   $response = curl_exec($ch);
   curl_close($ch);
   return $response;
}

 function pte_async_job_old_2 ($url, $postParameters) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postParameters);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_POSTREDIR, 3);
    $pageResponse = curl_exec($ch);
    curl_close($ch);
}


function pte_async_job_old_1 ($url, $params) {
	$fullUrl = "php -f '{$url}' "  . escapeshellarg(serialize($params)) . " > /dev/null &";
	shell_exec($fullUrl);
}

function pte_async_job ($url, $params) {

    foreach ($params as $key => &$val) {
      if (is_array($val)) $val = implode(',', $val);
        $post_params[] = $key.'='.urlencode($val);
    }
    $post_string = implode('&', $post_params);
    $parts = parse_url($url);
    $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);
    $out = "POST ".$parts['path']." HTTP/1.1\r\n";
    $out.= "Host: ".$parts['host']."\r\n";
    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
    $out.= "Content-Length: ".strlen($post_string)."\r\n";
    $out.= "Connection: Close\r\n\r\n";
    if (isset($post_string)) $out.= $post_string;
    fwrite($fp, $out);
    fclose($fp);
}

function pte_format_pstn_number($phoneNumber){
  $phoneNumber = (substr($phoneNumber, 0, 1) == "+") ? $phoneNumber : "+{$phoneNumber}";
	$lastFour = substr($phoneNumber, 8);
	$firstThree = substr($phoneNumber, 5, 3);
	$areaCode = substr($phoneNumber, 2, 3);
	$country = substr($phoneNumber, 0, 2);
  $country = '';
	return ($country . " (" . $areaCode . ") " . $firstThree . "-" . $lastFour);
}

function pte_release_all_pstn_numbers($ownerId){
  global $wpdb;
  $pstnNumbers = $wpdb->get_results(
    $wpdb->prepare("SELECT pstn_number from alpn_pstn_numbers WHERE owner_id = %s AND release_date IS NULL", $ownerId)
   );
   foreach ($pstnNumbers as $key => $value) {
     $phoneNumber = $value->pstn_number;
     pte_release_pstn_number($phoneNumber);
   }
}

function pte_release_pstn_number($phoneNumber) {
  global $wpdb;
  if ($phoneNumber) {
    try {
      $results = $wpdb->get_results(
        $wpdb->prepare("SELECT pstn_uuid from alpn_pstn_numbers WHERE pstn_number = %s", $phoneNumber)
       );
       if (isset($results[0])) {
        $pstnUuid = $results[0]->pstn_uuid;
         //Release
        $webhook = pte_call_documo('number_release', array('pstn_uuid' => $pstnUuid));
        $webhookData = json_decode($webhook, true);

        $now = date ("Y-m-d H:i:s", time());
        $pstnData = array(
          "release_date" => $now
        );
        $whereClause = array(
          'pstn_uuid' => $pstnUuid
        );
        $wpdb->update( 'alpn_pstn_numbers', $pstnData, $whereClause );
        return array(
          "error" => false,
          "message_key" => "pte_pstn_number_release_success",
          "pstn_uuid" => $pstnUuid
        );
       } else {
         return array(
           "error" => true,
           "message_key" => "pte_pstn_number_release_number_not_found"
         );
       }
    } catch (\Exception $e) {
        alpn_log($e);
        return array(
          "error" => true,
          "message_key" => "pte_pstn_number_release_exception",
          "exception" => $e
        );
    }
  }
  return array(
    "error" => true,
    "message_key" => "pte_pstn_number_release_number_not_provided"
  );
}

function get_user_fax_numbers() {

  global $wpdb_readonly;
  $faxNumbers = '';
  $userInfo = wp_get_current_user();
  $ownerId = $userInfo->data->ID;
  $ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true );
  $resultsNumbers = $wpdb_readonly->get_results(
    $wpdb_readonly->prepare(
      "SELECT p.id, p.pstn_number, p.topic_id FROM alpn_pstn_numbers p LEFT JOIN alpn_topics t ON t.id = p.topic_id WHERE p.owner_id = %s AND ISNULL(release_date) ORDER BY t.name ASC;", $ownerId)
  );
  if (isset($resultsNumbers[0])) {

    $resultsTopics = $wpdb_readonly->get_results(
      $wpdb_readonly->prepare("SELECT t.id, t.name FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON tt.id = t.topic_type_id WHERE t.owner_id = %d AND t.special = 'topic' AND t.name != '' AND (tt.topic_class = 'topic' OR tt.topic_class = 'link') ORDER BY name ASC", $ownerId)
    );

    foreach ($resultsNumbers as $key => $value) {
      $topicList = '';
      $phoneNumber = $value->pstn_number;
      $formattedNumber = pte_format_pstn_number($phoneNumber);
      $topicId = $value->topic_id;
    	$phoneNumberKey = substr($phoneNumber, 1);


    	$topicList .= "<select id='alpn_select2_small_{$phoneNumberKey}' data-ptrid='{$phoneNumber}'>";
    	$topicList .= "<option value='{$ownerNetworkId}'>Personal</option>";
    	foreach ($resultsTopics as $key1 => $value1) {
          $selected = ($value1->id == $topicId) ? " SELECTED" : "";
      		$id = $value1->id;
      		$name = $value1->name;
      		$topicList .= "<option value='{$id}' {$selected}>{$name}</option>";
  	  }
	    $topicList .= "</select>";
      $faxNumbers .= "<li class='pte_important_topic_scrolling_list_item' style='' >";
      $faxNumbers .= "<div class='pte_scrolling_item_left'><div class='pte_pstn_topic_list'>" . $topicList  . "</div><div class='pte_pstn_number_list'>" . $formattedNumber  . "</div></div>";
      $faxNumbers .= "<div class='pte_scrolling_item_right'><i class='far fa-minus-circle pte_scrolling_list_remove' title='Release Fax Number' onclick='pte_handle_release_fax_number(this);'></i></div>";
      $faxNumbers .= "<div style='clear: both;'>";
      $faxNumbers .= "</div>";
      $faxNumbers .= "</li>";
      $faxNumbers .= "
      <script>
          jQuery('#alpn_select2_small_' + '{$phoneNumberKey}').select2({
            theme: 'bootstrap',
            width: '130px',
            allowClear: false
          });
          jQuery('#alpn_select2_small_' + '{$phoneNumberKey}').on('select2:select', function (e) {
            var ptrid = jQuery(e.currentTarget).data('ptrid');
            var data = e.params.data;
            pte_update_fax_route_topic(ptrid, data);
          });
      </script>
      ";
  }
}
  return $faxNumbers;
}

function get_network_contact_topics($networkContactId) {

  global $wpdb_readonly;
  $contactTopics = '';

  $userInfo = wp_get_current_user();
  $ownerId = $userInfo->data->ID;
  $ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true );

  $resultTopics = $wpdb_readonly->get_results(
    $wpdb_readonly->prepare(
      "SELECT t.id, t.name, t.dom_id, t.topic_type_id, t.about FROM alpn_proteams p JOIN alpn_topics t ON t.id = p.topic_id WHERE p.owner_id = '%s' AND p.proteam_member_id = '%s' ORDER BY name ASC;", $ownerId, $networkContactId)
  );

  if (isset($resultTopics[0])) {

    $contactTopics .= "<div class='pte_proteam_title_container'><div class='pte_proteam_title_left'>Teams</div><div class='pte_proteam_title_right'></div></div>";
    $contactTopics .= "<div id='pte_contacts_topics_container'>";
    foreach ($resultTopics as $key => $value) {
      $topicList = '';
      $topicId = $value->id;
      $topicName = $value->name;
      $topicDomId = $value->dom_id;
      $topicTypeId = $value->topic_type_id;
      $topicAbout = $value->about;

      $topicAll = "{$topicName} - {$topicAbout}";

      $contactTopics .= "<li class='pte_important_topic_scrolling_list_item'>";
      $contactTopics .= "<div class='pte_scrolling_item_full' title='Link to this topic'><div class='pte_link_bar_link_contacts'><div data-topic-id='{$topicId}' data-topic-dom-id='{$topicDomId}' data-topic-type-id='{$topicTypeId}' data-operation='topic_info' class='interaction_panel_row_link' onclick='pte_handle_interaction_link_object(this);' style='text-align: left; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;'><div class='pte_icon_interaction_link'><i class='far fa-info-circle'></i></div>{$topicAll}</div></div></div>";
      $contactTopics .= "</li>";
  }
  $contactTopics .= "</div>";
}
  return $contactTopics;
}


function get_routing_email_addresses() {

  global $wpdb_readonly;
  $domainName = PTE_HOST_DOMAIN_NAME;
  $emailAddresses = '';
  $userInfo = wp_get_current_user();
  $ownerId = $userInfo->data->ID;
  $ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true );

  $resultsEmails = $wpdb_readonly->get_results(
    $wpdb_readonly->prepare(
      "SELECT id, email_route_id, name FROM alpn_topics WHERE owner_id = '%s' AND email_route_id IS NOT NULL ORDER BY name ASC;", $ownerId)
  );

  if (isset($resultsEmails[0])) {
    foreach ($resultsEmails as $key => $value) {
      $topicList = '';
      $topicId = $value->id;
      $topicName = $value->name;

      $dottedName = str_replace(array(', ', ',', "'", '"'), array('.', '.', "", ""), $topicName);
      $emailAddress = "{$dottedName} - Wiscle <{$value->email_route_id}@files.{$domainName}>";

      $emailAddresses .= "<li class='pte_important_topic_scrolling_list_item'>";
      $emailAddresses .= "<div class='pte_scrolling_item_left' title='Copy Email Address to Clipboard'><div class='pte_pstn_topic_list pte_topic_link' onclick='pte_topic_link_copy_string(\"Email\", \"{$emailAddress}\");'><i class='far fa-copy' style='margin-right: 5px;'></i>" . $topicName  . "</div></div>";
      $emailAddresses .= "<div class='pte_scrolling_item_right'><i class='far fa-minus-circle pte_scrolling_list_remove' title='Remove Email Route' onclick='pte_handle_release_email_route({$topicId});'></i></div>";
      $emailAddresses .= "<div style='clear: both;'>";
      $emailAddresses .= "</div>";
      $emailAddresses .= "</li>";
  }
}
  return $emailAddresses;
}

function wsc_get_wallet_addresses_ux() {

  $html = "
    <button id='moralis-login-button'>Attach a Wallet</button><br><br>

    <script>

    jQuery('#moralis-login-button').on( 'click', moralisAttachWalletConnect );

    </script>
  ";


  return $html;
}

function pte_get_email_ux() {

  global $wpdb_readonly;
  $topicOptions = $topicList = '';
  $userInfo = wp_get_current_user();
  $ownerId = $userInfo->data->ID;

  $results = $wpdb_readonly->get_results(
    $wpdb_readonly->prepare(
      "SELECT id, name, '0' AS row_type FROM alpn_topics WHERE owner_id = '%s' AND special = 'user' AND name != '' UNION
       SELECT id, name, '1' AS row_type FROM alpn_topics WHERE owner_id = '%s' AND special = 'contact' AND name != '' UNION
       SELECT t.id, t.name, '2' AS row_type FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON tt.id = t.topic_type_id WHERE t.owner_id = '%s' AND t.special = 'topic' AND t.name != '' AND (tt.topic_class = 'topic' OR tt.topic_class = 'link')
       ORDER BY row_type ASC, name ASC;",
       $ownerId, $ownerId, $ownerId)
  );

  if (isset($results[0])) {

    $topicOptions .= "
      <option></option>
      <optgroup label='Personal'>
      <option value='{$results[0]->id}'>{$results[0]->name}</option>
      </optgroup>
      <optgroup label='Contacts'>
    ";

    foreach ($results as $key => $value) {
        if ($value->row_type == 1) {
          $topicOptions .= "
            <option value='{$value->id}'>{$value->name}</option>
            ";
        }
    }

    $topicOptions .= "
      </optgroup>
      <optgroup label='Topics'>
    ";

    foreach ($results as $key => $value) {
      if ($value->row_type == 2) {
        $topicOptions .= "
          <option value='{$value->id}'>{$value->name}</option>
          ";
      }
    }

    $topicOptions .= "
      </optgroup>
    ";
  }

  $topicList .= "<select id='pte_extension_topic_select'>";
  $topicList .= $topicOptions;
  $topicList .= "</select>";
  $emailAddresses = get_routing_email_addresses();
  $emailUx = "
    <div id='pte_email_ux_container'>
      <div id='pte_email_ux_container_inner'>
        <div class='pte_fax_words'>
          Email attachments securely route to the designated Topic Vault. Click on the Topic Name to copy the email address to the clipoard. Disposible in case of abuse.
        </div>
        <div class='pte_email_address_selector_outer'><div class='pte_email_address_selector_left'></div><div class='pte_email_address_selector_right'>{$topicList}</div></div>
        <ul id='pte_emails_assigned' class='pte_important_topic_scrolling_list'>{$emailAddresses}</ul>
      </div>
    </div>
  ";
  $emailUx .= "
  <script>
      jQuery('#pte_extension_topic_select').select2({
        theme: 'bootstrap',
        width: '100%',
        allowClear: true,
        closeOnSelect: false,
        placeholder: 'Select a Topic...'
      });
      jQuery('#pte_extension_topic_select').on('select2:select', function (e) {
        var data = e.params.data;
        pte_update_email_route(data);
        jQuery('#pte_extension_topic_select').val('').trigger('change');
      });
  </script>
  ";
  return $emailUx;
}

function pte_get_fax_ux() {
  $faxNumbers = get_user_fax_numbers();
  $faxUx = "
  <div id='pte_fax_ux_container'>
    <div id='pte_fax_ux_container_inner'>
      <div id='pte_pstn_number_widget'>
          <div id='pte_pstn_number_widget_left'>
            <input type='text' id='pte_pstn_widget_area_code' placeholder='Area Code'>
            <button id='pte_pstn_widget_lookup' class='btn btn-danger btn-sm' onclick='pte_pstn_widget_lookup();'>Lookup</button>
          </div>
          <div id='pte_pstn_number_widget_right'>
            <div class='pte_inner_widget_text'>Enter desired area code and press 'Lookup'. Press 'Use', then assign the Topic to the fax number.</span></div>
          </div>
          <div style='clear: both;'></div>
        </div>
        <div class='pte_fax_words'>Faxes securely route to the selected Topic Vault in PDF format. Disposible in case of abuse. Non-transferable.</div>
        <ul id='pte_fax_numbers_assigned' class='pte_important_topic_scrolling_list' style='padding: 5px;'>{$faxNumbers}</ul>
      </div>
    </div>
  ";
  return $faxUx;
}
//        <div class='pte_fax_words'>By pressing 'Use Fax Number', you will be billed $1/day up to a maximum of $10/month per fax number plus per-page fees. To stop using a fax number, press the 'Release Fax Number' icon.</div>

function pte_documo_fax_send($sendData){
  $sitePath = getRootUrl() . "alpn_send_documo_fax.php";
  pte_async_job ($sitePath, array("data" => json_encode($sendData)));
}


function pte_call_documo($type, $data){
  $urlbase = 'https://api.documo.com/v1/';
  $apiKey = FAX_DOCUMO_API_KEY;
  $query = '';
  $headers = array(
    "Authorization: Basic {$apiKey}"
  );
  $options = array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
  );
  switch ($type) {
    case "get_accounts":
      $endPoint = 'accounts';
      $query = http_build_query($data);
    break;
    case "number_search":
      $endPoint = 'numbers/provision/search';
      $headers[] = "Accept: application/json";
      $query = http_build_query($data);
    break;
    case "send_fax":
    $endPoint = 'faxes';
    $headers[] = "Content-Type: multipart/form-data";
    $pstnNumber = $data['pstn_number'];
    $attachmentPath = $data['attachment_path'];
    $coverSheetPath = $data['cover_sheet_path'];
     $body = array(
       "attachments['file1']" => new CURLFile($coverSheetPath, 'application/pdf', 'cover_sheet'),
       "attachments['file2']" => new CURLFile($attachmentPath, 'application/pdf', 'attachment'),
       "faxNumber" => $pstnNumber,
       "coverPage" => "false"
     );
      $options[CURLOPT_POST] = true;
      $options[CURLOPT_CUSTOMREQUEST] = "POST";
      $options[CURLOPT_POSTFIELDS] = $body;
    break;
    case "get_webhooks":
      $endPoint = 'webhooks';
      $urlbase = 'https://api.documo.com/';
      $query = http_build_query($data);
    break;
    case "setup_webhook":
      $hostDomain = PTE_HOST_DOMAIN_NAME;
      $endPoint = 'webhooks';
      $headers[] = "Content-Type: application/x-www-form-urlencoded";
      $urlbase = 'https://api.documo.com/';
      $pstnUuid = $data['pstn_uuid'];
      $pstnNumber = $data['pstn_number'];
      $body = array(
        'name' => "For: {$pstnNumber}",
        'url' => "https://{$hostDomain}/wp-content/themes/memberlite-child-master/pte_fax_in_out.php",
        'events' => '{"fax.inbound":true}',
        'numberId' => $pstnUuid,
        'attachmentEnabled' => true
      );
      $options[CURLOPT_POST] = true;
      $options[CURLOPT_CUSTOMREQUEST] = "POST";
      $options[CURLOPT_POSTFIELDS] = http_build_query($body);
    break;
    case "get_numbers":
      $endPoint = 'numbers';
      $headers[] = "Accept: application/json";
      $query = http_build_query($data);
    break;
    case "number_provision":
      $endPoint = 'numbers/provision';
      $headers[] = "Content-Type: application/x-www-form-urlencoded";
      $body = array(
        'numbers' => $data['phone_number'],
        'type' => 'local'
      );
      $options[CURLOPT_POST] = true;
      $options[CURLOPT_CUSTOMREQUEST] = "POST";
      $options[CURLOPT_POSTFIELDS] = http_build_query($body);
    break;
    case "number_release":
      $uuidPhoneNumber = isset($data['pstn_uuid']) ? $data['pstn_uuid'] : "";
      $endPoint = "numbers/{$uuidPhoneNumber}/release";
      $options[CURLOPT_CUSTOMREQUEST] = "DELETE";
    break;
  }
  $options[CURLOPT_URL] = "{$urlbase}{$endPoint}?{$query}";
  $options[CURLOPT_HTTPHEADER] = $headers;
  $ch = curl_init();
  curl_setopt_array($ch, $options);
  $response = curl_exec($ch);
  curl_close($ch);
  return $response;
}


function pte_get_interaction_settings_sliders($data) {

  global $wpdb_readonly;
  $sliders = "";
  $userInfo = wp_get_current_user();
  $ownerId = $userInfo->data->ID;
  $ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true );


  //TODO from DB, with color to match interactions
  $data = array("ProTeam Invitation", "Fax Send", "Fax Received", "Message", "Added to Network", "File Share", "File Received", "Copy Request", "Reminder", "Chat Activity", "Form Fill Request");

  sort($data);

  $sliders .= "<div id='pte_sliders_container'><div id='pte_sliders_container_inner'>";
  foreach ($data as $key) {
      $rando = rand (1,3);

      $sliders .= "<div class='pte_sliders_line'><div class='pte_sliders_type'>{$key}</div><div class='pte_interaction_slider'><input type='range' min='1' max='3' step='1' value='{$rando}' onchange='pte_handler_interaction_setting_slider(this);'></div><div style='clear: both;'></div></div>";
  }
  $sliders .= "</div></div>";
  return $sliders;
}


function pte_manage_topic_link($operation, $requestData, $subjectToken = 'pte_external'){

  global $wpdb;
  switch ($operation) {
    case "add_edit_topic_bidirectional_link":
      $ownerId = isset($requestData['owner_id']) ? $requestData['owner_id'] : 0;  //context is the other person.
      $topicId = isset($requestData['topic_id']) ? $requestData['topic_id'] : 0;
      $listDefault = isset($requestData['list_default']) && $requestData['list_default'] ? $requestData['list_default'] : 'no';
      $connectedId = isset($requestData['connected_id']) ? $requestData['connected_id'] : 0;
      $connectionLinkTopicId = isset($requestData['connection_link_topic_id']) ? $requestData['connection_link_topic_id'] : 0;
      $results = $wpdb->get_results(
      	$wpdb->prepare("SELECT id FROM alpn_topic_links WHERE owner_topic_id_1 = %s AND owner_topic_id_2 = %s AND subject_token = %s", $topicId, $connectionLinkTopicId, $subjectToken)
       );
       if (!isset($results[0])) {  //new insert

         $rowData = array(      //TODO
           'owner_id_1' => $ownerId,
           'owner_topic_id_1' => $topicId,
           'owner_id_2' => $connectedId,
           'owner_topic_id_2' => $connectionLinkTopicId,
           'subject_token' => $subjectToken,
           'list_default' => $listDefault
         );
         $wpdb->insert( 'alpn_topic_links', $rowData ); //new link
         //TODO can we reduce db hits?
          $results = $wpdb->get_results(
         	  $wpdb->prepare("SELECT dom_id FROM alpn_topics WHERE id = %s", $connectionLinkTopicId)
          );

          if (isset($results[0])) {
            $rowData['owner_dom_id_2'] = $results[0]->dom_id;
            return $rowData;
          }
       }
       return false;  //existing link
    break;
    case "delete_topic_bidirectional_link":
      $linkId = isset($requestData['link_id']) ? $requestData['link_id'] : 0;
      if ($linkId) {
        $whereclause = array('id' => $linkId);
        $wpdb->delete( "alpn_topic_links", $whereclause );
        return true;
      } else {
       return false;
     }
    break;
  }
}

function pte_update_interaction_weight($listKey, $data) {
    global $wpdb;
    $interactionUpdates = $whereClause = array();
    $operation = isset($data['operation']) ? $data['operation'] : "important_added";
    $importanceValue = ($operation == 'important_added') ? 1 : 0;

    $whereClause['owner_network_id'] = $data['owner_network_id'];
    $whereClause['state'] = 'active';

    if ($listKey == 'pte_important_network') {
      $interactionUpdates['network_is_important'] = $importanceValue;
      $whereClause['imp_network_id'] = $data['item_id'];
      $wpdb->update( 'alpn_interactions', $interactionUpdates, $whereClause );
    }

    //Topic  Regarding or Context Topic can be any of Personal, Topic or Contact(Network) So if a user list is changed, importance is updated

    //clean up query and run every time on Topic.
    $interactionUpdates = array();
    if (isset($whereClause['imp_network_id'])) {unset ($whereClause['imp_network_id']);}
    $interactionUpdates['topic_is_important'] = $importanceValue;
    $whereClause['imp_topic_id'] = $data['item_id'];
    $wpdb->update( 'alpn_interactions', $interactionUpdates, $whereClause );

}

function pte_get_important_items($listKey){
  global $wpdb_readonly;
  $listItems = "";
  $userInfo = wp_get_current_user();
  $ownerId = $userInfo->data->ID;
  $ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true );
  if ($ownerNetworkId && $listKey) {
    $results = $wpdb_readonly->get_results(
      $wpdb_readonly->prepare(
        "SELECT u.item_id, t.name FROM alpn_user_lists u LEFT JOIN alpn_topics t ON t.id = u.item_id WHERE u.owner_network_id = %s AND list_key = %s ORDER BY t.name ASC", $ownerNetworkId, $listKey)
    );
    if (count($results)) {
      foreach ($results as $key => $value) {
        $selectedId = $value->item_id;
        $selectedName = $value->name;
        $listItems .= "<li class='pte_important_topic_scrolling_list_item' data-topic-id='{$selectedId}'><div class='pte_scrolling_item_left'>{$selectedName}</div><div class='pte_scrolling_item_right'><i class='far fa-minus-circle pte_scrolling_list_remove' title='Remove Item' onclick='pte_handle_remove_list_item(this);'></i></div><div style='clear: both;'></div></li>";
      }
    }
  }
  return $listItems;
}


function pte_get_create_linked_form($ownerTopicId, $subjectToken, $topicKey){

  global $wpdb;
  $userInfo = wp_get_current_user();
  $ownerId = $userInfo->data->ID;
  $domID = '';

  $results = $wpdb->get_results(
    $wpdb->prepare("SELECT t.dom_id, tl.id FROM alpn_topic_links tl LEFT JOIN alpn_topics t ON t.id = tl.owner_topic_id_2 WHERE tl.owner_topic_id_1 = %d AND tl.subject_token = %s ", $ownerTopicId, $subjectToken)
   );

   if (isset($results[0])) {
      $domId = $results[0]->dom_id;
   } else {
     //Create a default topic and link, then return dom_id.
     $results = $wpdb->get_results(
       $wpdb->prepare("SELECT form_id FROM alpn_topic_types WHERE type_key = %s ", $topicKey)
      );

     if (isset($results[0])) {
        $formId = $results[0]->form_id;
        $now = date ("Y-m-d H:i:s", time());
        $entry = array(
          'id' => $formId,
          'fields' => array()
        );
        $newTopicId =  alpn_handle_topic_add_edit ('', $entry, '', '' );	//Add a topic of proper type
        //Make the link
        $requestData = array(
        	'owner_id' => $ownerId,
        	'topic_id' => $ownerTopicId,
        	'connected_id' => $ownerId,
        	'connection_link_topic_id' => $newTopicId
        );
        pte_manage_topic_link('add_edit_topic_bidirectional_link', $requestData, $subjectToken);

        $results = $wpdb->get_results(
          $wpdb->prepare("SELECT dom_id FROM alpn_topics WHERE id = %d ", $newTopicId)
         );

         if (isset($results[0])) {
           $domId = $results[0]->dom_id;
         }
    }
   }

   return "<div id='pte_tab_record_wrapper' data-dom_id='{$domId}'>" . pte_get_linked_form($domId) . "</div>";
}


function pte_get_linked_form($domId){   //TODO Merge with select topic
    $ppCdnBase = PTE_IMAGES_ROOT_URL;
    global $wpdb;
    $html = $topicHtml = '';
    $replaceStrings = array();
  	$results = $wpdb->get_results(  //TODO should this be selecting based on links?
  		$wpdb->prepare("SELECT concat(JSON_UNQUOTE(JSON_EXTRACT(t3.topic_content, '$.person_givenname')), ' ', JSON_UNQUOTE(JSON_EXTRACT(t3.topic_content, '$.person_familyname'))) AS owner_nice_name, t.*, p.access_level, f.pstn_number, tt.id AS topic_type_id, tt.type_key, tt.form_id, tt.name AS topic_name, tt.icon, tt.topic_type_meta, tt.html_template, t2.topic_content AS connected_contact_topic_content, t3.name AS owner_name, t3.topic_content AS owner_topic_content, t2.image_handle AS profile_handle FROM alpn_topics t LEFT JOIN alpn_proteams p ON p.topic_id = t.id AND p.owner_id = t.owner_id LEFT JOIN alpn_pstn_numbers f ON f.topic_id = t.id LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id LEFT JOIN alpn_topics t2 ON t2.owner_id = t.connected_id AND t2.special = 'user' LEFT JOIN alpn_topics t3 ON t3.owner_id = t.owner_id AND t3.special = 'user' WHERE t.dom_id = %s", $domId)
  	 );
  	if (isset($results[0])) {   //TODO Merge/generalize with topic_select
  		$topicData = $results[0];
  		$topicMeta = json_decode($topicData->topic_type_meta, true);
  		$topicContent = json_decode($topicData->topic_content, true);
  		$topicHtml = stripcslashes($topicData->html_template);
      $topicLogoHandle = $topicData->logo_handle;
      $topicDomId = $topicData->dom_id;
  		$typeKey = $topicData->type_key;
  		$nameMap = pte_name_extract($topicMeta['field_map']);
  		$fieldMap = array_flip($nameMap);
  		foreach($topicContent as $key => $value){	   //deals with date/time being arrays
  			if (is_array($value)) {
  				foreach ($value as $key2 => $value2) {
  					$actualValue = $value2;
  				}
  			} else {
  				$actualValue = $value;
  			}
        switch ($key) {  //TODO this is iterating through stored data. If schema changes, then the data is out of date. Workaround, edit/save the record. Shouldn't be a problem if topic starts with these system fields but what about new system fields?
          case 'pte_added_date':
            $replaceStrings['-{' . 'pte_added_date' . '}-'] = pte_date_to_js($topicData->created_date);
            $replaceStrings['-{' . 'pte_added_date_title' . '}-'] = $nameMap['pte_added_date'];
          break;
          case 'pte_modified_date':
            $replaceStrings['-{' . 'pte_modified_date' . '}-'] = pte_date_to_js($topicData->modified_date);
            $replaceStrings['-{' . 'pte_modified_date_title' . '}-'] = $nameMap['pte_modified_date'];
          break;
          case 'pte_image_logo':
            $topicLogoUrl = "";
            if ($topicLogoHandle) {
              $topicLogoUrl = "<img class='pte_logo_image_screen' src='{$ppCdnBase}{$topicLogoHandle}'>";
            }
            $replaceStrings['-{' . 'pte_image_logo' . '}-'] = $topicLogoUrl;
            $replaceStrings['-{' . 'pte_image_logo_title' . '}-'] = $nameMap['pte_image_logo'];
          break;
          default:
            $replaceStrings['-{' . $key . '}-'] = $actualValue;
      			$replaceStrings['-{' . $key . '_title}-'] = isset($nameMap[$key]) ? $nameMap[$key] : "";
          break;
        }
  		}
  		$replaceStrings["{topicDomId}"] = $topicDomId;
      $businessTypesList = get_custom_post_items('pte_profession', 'ASC');
      if (isset($replaceStrings['-{person_hasoccupation_occupation_occupationalcategory}-'])) {  //TODO test this
      	$replaceStrings['-{person_hasoccupation_occupation_occupationalcategory}-'] = $businessTypesList[$replaceStrings['-{person_hasoccupation_occupation_occupationalcategory}-']];
      } else {
      	$replaceStrings['-{person_hasoccupation_occupation_occupationalcategory}-'] = "Not Specified";
      }
  	}
    return str_replace(array_keys($replaceStrings), $replaceStrings, $topicHtml);
}


//TODO Make this use SELECT2 AJAX infinite scroll paging.

function wic_get_domId_from_topicId($topicId) {
  global $wpdb;
  $results = $wpdb->get_results(
    $wpdb->prepare(
      "SELECT dom_id FROM alpn_topics WHERE id = %d", $topicId)
  );
  return isset($results[0]) ? $results[0]->dom_id : false;
}

function wic_get_topic_name_from_topicId($topicId) {
  global $wpdb;
  $results = $wpdb->get_results(
    $wpdb->prepare(
      "SELECT name FROM alpn_topics WHERE id = %d", $topicId)
  );
  return isset($results[0]) ? $results[0]->name : false;
}

function pte_get_topic_list($listType, $topicTypeId = 0, $uniqueId = '', $typeKey = '', $hidePlaceholder = false, $emptyMessage = '') {
  global $wpdb_readonly;
  $topicOptions = "";
  $userInfo = wp_get_current_user();
  $userID = $userInfo->data->ID;
  $userNetworkId = get_user_meta( $userID, 'pte_user_network_id', true );

  // alpn_log('pte_get_topic_list');
  // alpn_log($topicTypeId);

  if ($userID && $listType) {
    switch ($listType) {
      case "linked_topics":
      $results = $wpdb_readonly->get_results(
        $wpdb_readonly->prepare(
          "SELECT connected_topic_id as id, name FROM alpn_topics_linked_view WHERE owner_topic_id = %d AND subject_token = %s AND owner_id = %d ORDER BY name ASC", $topicTypeId, $typeKey, $userID)
      );
      // alpn_log('pte_get_topic_list - RESULTS');
      // alpn_log($wpdb_readonly->last_query);
      // alpn_log($wpdb_readonly->last_error);
      // alpn_log($results);

      $id = $uniqueId ? $uniqueId : 'pte_by_type_key';
      break;
      case "type_key":
      $results = $wpdb_readonly->get_results(
        $wpdb_readonly->prepare(
          "SELECT t.id, t.name FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON tt.id = t.topic_type_id WHERE t.owner_id = %d AND tt.type_key = %s ORDER BY t.name ASC;", $userID, $typeKey)
      );
      $id = $uniqueId ? $uniqueId : 'pte_by_type_key';
      break;
      case "network_contacts":
      $results = $wpdb_readonly->get_results(
        $wpdb_readonly->prepare(
          "SELECT id, name FROM alpn_topics WHERE owner_id = %d AND special = 'contact' ORDER BY name ASC;", $userID)
      );
      $id = 'pte_important_network_topic_list';
      break;
      case "topics":    //Primary only
        $results = $wpdb_readonly->get_results(
          $wpdb_readonly->prepare(
            "SELECT t.id, t.name FROM alpn_topics t RIGHT JOIN alpn_topic_types tt ON tt.id = t.topic_type_id AND tt.topic_class = 'topic' AND tt.special = 'topic' WHERE t.owner_id = '%s' ORDER BY name ASC;", $userID)
        );
        // alpn_log($results);
        $id = 'pte_important_topic_list';
        break;
        case "single_schema_type":
          $results = $wpdb_readonly->get_results(
            $wpdb_readonly->prepare(
              "SELECT t.id, t.name FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON tt.id = t.topic_type_id WHERE t.owner_id = '%s' AND tt.schema_key = %s ORDER BY name ASC;", $userID, $topicTypeId)
          );
          $id = $uniqueId ? $uniqueId : 'pte_single_topic_type_list';
        break;
        case "active_core_topic_types":
          $topicTypeState = 'active';
          $results = $wpdb_readonly->get_results(
            $wpdb_readonly->prepare(
              "SELECT id, name FROM alpn_topic_types WHERE topic_state = %s AND special = 'topic' ORDER BY name ASC;", $topicTypeState)
          );
          $id = $uniqueId ? $uniqueId : 'pte_active_core_topic_types';
        break;
    }
    if (isset($results[0])) {
      $topicOptions .= "<select id='{$id}'>";
      if (!$hidePlaceholder) {$topicOptions .= "<option></option>";}
      foreach ($results as $key => $value) {
          $topicOptions .= "<option value='{$value->id}'>{$value->name}</option>";
      }
      $topicOptions .= "</select>";
    } else {
      $topicOptions = "<div class='pte_widget_message'>$emptyMessage</div>";
    }
  }
  return $topicOptions;
}


function wsc_check_team_dupe($linkedTopicId, $visitingOwnerId, $proteamRowId){
  alpn_log("Checking Team Dupe...");
  global $wpdb;
  $isDupe = false;
  $currentLinkId = 0;

  $proTeam = $wpdb->get_results(
    $wpdb->prepare(
      "SELECT id FROM alpn_proteams WHERE topic_id = %d AND wp_id = %d", $linkedTopicId, $visitingOwnerId)
   );

   if (isset($proTeam[0])) {
    $isDupe = true;
    $currentLink = $wpdb->get_results(
       $wpdb->prepare(
         "SELECT linked_topic_id FROM alpn_proteams WHERE id = %d", $proteamRowId)
        );
     $currentLinkId = isset($currentLink[0]) ? $currentLink[0]->linked_topic_id : 0;
   }

  return array("is_dupe" => $isDupe, "current_link_id" => $currentLinkId);
}

function pte_proteam_state_change_sync($data){
  alpn_log("pte_proteam_state_change_sync...");
  alpn_log($data);

  global $wpdb;

  $connectedType =  isset($data['connected_type']) ? $data['connected_type'] : '';
  $ptState =  isset($data['state']) ? $data['state'] : 0;
  $ptId =  isset($data['proteam_row_id']) ? $data['proteam_row_id'] : 0;
  $ownerId = isset($data['owner_id']) ? $data['owner_id'] : 0;
  $connectedId = isset($data['connected_id']) ? $data['connected_id'] : 0;
  $processId = isset($data['process_id']) ? $data['process_id'] : '';
  $linkedTopicId = isset($data['linked_topic_id']) && $connectedType == 'link' ? $data['linked_topic_id'] : NULL;

if ($connectedType && $ptState && $ptId) {

    $proTeamData = array(
      "connected_type" => $connectedType,
      "state" => $ptState,
      "process_id" => $processId,
      "linked_topic_id" => $linkedTopicId
    );
    $whereClause = array(
      "id" => $ptId
    );
    $wpdb->update("alpn_proteams", $proTeamData, $whereClause);

    if ($ownerId) {

      $syncdata = array(
        "sync_type" => "add_update_section",
        "sync_section" => "proteam_card_update",
        "sync_user_id" => $ownerId,
        "sync_payload" => $data
      );
      pte_manage_user_sync($syncdata);
      alpn_log('ProTeams Sync Sent...');
    }

  }
}

function pte_manage_user_sync($data){   ///Must be a user and have a wpid

  alpn_log("pte_manage_user_sync...");
  //alpn_log($data);

  global $wpdb;

  $userInfo = wp_get_current_user();
  $userID = $userInfo->data->ID;

  $accountSid = ACCOUNT_SID;
  $authToken = AUTHTOKEN;
  $syncServiceId = SYNCSERVICEID;

  try {
    $twilio = new Client($accountSid, $authToken);
  } catch (Exception $e) {
      $response = array(
          'message' =>  $e->getMessage(),
          'code' => $e->getCode(),
          'error' => $e
      );
      alpn_log("pte_manage_user_sync EXCEPTION...");
      alpn_log($response);
      return;
  }

  $syncType = isset($data['sync_type']) ? $data['sync_type'] : false ;
  $syncSection = isset($data['sync_section']) ? $data['sync_section'] : '';
  $syncUserId = isset($data['sync_user_id']) ? $data['sync_user_id'] : $userID;

  if (!$syncUserId) {
    $syncId = '';
  } else {
    $syncId = get_user_meta( $syncUserId, 'pte_user_sync_id', true );
  }

  switch ($syncType) {
    case "update_all_sync_ids":

      $results = $wpdb->get_results("SELECT id, owner_id, sync_id, name FROM alpn_topics where special = 'user'");

      foreach($results as $value) {
        pp("Handled " . $value->name);

        if ($value->sync_id && $value->owner_id) {

          try {
            $sync_map = $twilio->sync->v1->services($syncServiceId)
                                         ->syncMaps($value->sync_id)
                                         ->update(array(
                                           "uniqueName" => $value->owner_id
                                         ));

          } catch (Exception $e) {
              $syncId = "Fail";
              $response = array(
                  'message' =>  $e->getMessage(),
                  'code' => $e->getCode(),
                  'error' => $e
              );
              alpn_log($response);
          }
        }
      }

      return;

    break;
    case "return_create_sync_id":
    alpn_log("return_create_sync_id...");
    if (!$syncId) {
            try {
              $sync_map = $twilio->sync->v1->services($syncServiceId)
                                           ->syncMaps
                                           ->create(array(
                                             "uniqueName" => $syncUserId
                                           ));
              $syncId = $sync_map->sid;
              $topicData = array(
                "sync_id" => $syncId
              );
          		$whereClause['owner_id'] = $syncUserId;
              $whereClause['special'] = 'user';
              $wpdb->update( 'alpn_topics', $topicData, $whereClause );  //persist channelid

              update_user_meta( $syncUserId, "pte_user_sync_id",  $syncId); //SH

            } catch (Exception $e) {
                $syncId = "Fail";
                $response = array(
                    'message' =>  $e->getMessage(),
                    'code' => $e->getCode(),
                    'error' => $e
                );
                alpn_log($response);
            }
          }
          return $syncId;
		break;

    case "add_update_section":

    alpn_log("add_update_section...");

    try {

      alpn_log("Trying to edit...");

      $sync_map_item = $twilio->sync->v1->services($syncServiceId)
                                      ->syncMaps($syncId)
                                      ->syncMapItems($syncSection)
                                      ->update(array("data" => $data));
    } catch (Exception $e) {

      alpn_log("Adding, not exist...");

        $response = array(
            'message' =>  $e->getMessage(),
            'code' => $e->getCode(),
            'error' => $e
        );

        try {

          alpn_log("Trying to add...");

          $sync_map_item = $twilio->sync->v1->services($syncServiceId)
                                            ->syncMaps($syncId)
                                            ->syncMapItems
                                            ->create($syncSection, $data);
        } catch (Exception $e) {
          $response = array(
              'message' =>  $e->getMessage(),
              'code' => $e->getCode(),
              'error' => $e
          );

        }


    }

      alpn_log("Updated Item...");

    break;

  }


}

function wcl_notify_contact_of_request($data){

  global $wpdb;

  alpn_log('wcl_notify_contact_of_request...');
  alpn_log($data);

  $contactId = isset($data['contact_id']) ? $data['contact_id'] : false ;

  if ($contactId) {
    $connectedStatus = "wants_to_connect";
    $connections = $wpdb->get_results(
      $wpdb->prepare("SELECT id FROM alpn_member_connections WHERE owner_id = %d AND connected_status COLLATE utf8mb4_general_ci = %s", $contactId, $connectedStatus)
    );
    $hasConnectionRequests = isset($connections[0]) ? "has_connects" : 'no_connects';

    $syncdata = array(
      "sync_type" => "add_update_section",
      "sync_section" => $hasConnectionRequests,
      "sync_user_id" => $contactId,
      "sync_payload" => array()
    );
    pte_manage_user_sync($syncdata);
    alpn_log('Sent wcl_notify_contact_of_request...');

  }
}

function pte_manage_user_connection($data){
  //If I add you to my network and you add me to your network than we're connected... We then show connected demographics....
  //TODO Handle exceptions, etc.
  alpn_log('pte_manage_user_connection...');
  alpn_log($data);

  global $wpdb;

  $notifyData = $data;

  $contactEmail = $data['contact_email'];
  $contactTopicId = $data['contact_topic_id'];
  $contactInfo = get_user_by('email', $contactEmail);

  if (isset($contactInfo->ID)) {
    $contactId = $contactInfo->ID;
    $data['contact_id'] = $contactId;
    $notifyData['contact_id'] = $contactId;

    $contactNetworkId = get_user_meta( $contactId, 'pte_user_network_id', true ); //Contact Topic ID
    $userId = isset($data['owner_wp_id']) ? $data['owner_wp_id'] : '';
    alpn_log($userId);
    $userInfo = get_user_by('id', $userId);
    $userEmail =  $userInfo->data->user_email;
    $userNetworkId = get_user_meta( $userId, 'pte_user_network_id', true ); //Owners Topic ID

    //go find ME in contact's contacts by email.
    $results = $wpdb->get_results(
    	$wpdb->prepare("SELECT id, owner_id, connected_id FROM alpn_topics WHERE owner_id = %s AND special = 'contact' AND alt_id = %s", $contactId, $userEmail)
     );
     if (isset($results[0])) {
       $contactId = $results[0]->owner_id;
       $connectedId = $results[0]->connected_id;
       $connectedTopicId = $results[0]->id;
       if (!$connectedId) {
         //Now go find contact in my Topics by email.
          $user = $wpdb->get_results(
          	$wpdb->prepare("SELECT id, name, about FROM alpn_topics WHERE owner_id = %d AND special = 'contact' AND alt_id = %s", $userId, $contactEmail)
           );
           if (isset($user[0])) {
             $userTopicId = $user[0]->id;
             $userName = $user[0]->name;
             $userAbout = $user[0]->about;

             $data = array(
              'owner_id' => $userId,
         			'topic_id' => $userTopicId,
              "contact_id" => $contactId
           		);

              alpn_log("ABOUT TO CREATE CHANNEL");

         		$newChannelId = pte_manage_cc_groups("get_create_channel", $data);     //create a channel for contact. Adds contact. Stores channel for contact

            alpn_log($newChannelId);


            $contactTopicData = $wpdb->get_results(
              $wpdb->prepare("SELECT name, about FROM alpn_topics WHERE id = %d", $contactNetworkId)
             );
             $contactName = isset($contactTopicData[0]) ? $contactTopicData[0]->name : "n/a";
             $contactAbout = isset($contactTopicData[0]) ? $contactTopicData[0]->about : "n/a";
             //user
             $topicData = array(
               'connected_id' => $contactId,
               'connected_network_id' => $contactNetworkId,
               'connected_topic_id' => $connectedTopicId,
               'name' => $contactName,
               'about' => $contactAbout
             );
             $whereClause = array(
               'id' => $userTopicId
             );
             $wpdb->update( 'alpn_topics', $topicData, $whereClause );

             $data = array(  //add contact to channel
              'topic_id' => $userTopicId,
              'user_id' => $contactId,
              'owner_id' => $userId
              );

            alpn_log("ABOUT TO ADD MEMBER");
            alpn_log($data);

            pte_manage_cc_groups("add_member", $data);
            $userTopicData = $wpdb->get_results(
              $wpdb->prepare("SELECT name, about FROM alpn_topics WHERE id = %d", $userNetworkId)
             );
             $userName = isset($userTopicData[0]) ? $userTopicData[0]->name : "n/a";
             $userAbout = isset($userTopicData[0]) ? $userTopicData[0]->about : "n/a";
             //contact
             $topicData = array(
               'connected_id' => $userId,
               'connected_network_id' => $userNetworkId,
               'connected_topic_id' => $userTopicId,
               'channel_id' => $newChannelId,
               'name' => $userName,
               'about' => $userAbout
             );
             $whereClause = array(
               'id' => $connectedTopicId
             );
             $wpdb->update( 'alpn_topics', $topicData, $whereClause );
         }
      }
    } else {  //TODO if contact in system, send IA offering to connect them with user... $contactId


    }
  }

  wcl_notify_contact_of_request($notifyData);

}

//2118 - CHa7947532b4f04fcc8d1745854be2131c


function async_pte_manage_cc_groups($operation, $data) {

  $data['operation'] = $operation;

  $verificationKey = pte_get_short_id();
  $params = array(
    "verification_key" => $verificationKey
  );
  vit_store_kvp($verificationKey, $data);
  try {
    alpn_log("Before Async");
    pte_async_job(PTE_ROOT_URL . "vit_async_manage_cc_group.php", array('verification_key' => $verificationKey));
    alpn_log("After Async");
  } catch (Exception $e) {
    alpn_log ($e);
  }
}

function pte_manage_cc_groups($operation, $data) {

  global $wpdb;
  $ownerInfo = wp_get_current_user();

  $accountSid = ACCOUNT_SID;
  $authToken = AUTHTOKEN;
  $chatServiceId = CHATSERVICESID;

  try {
    $twilio = new Client($accountSid, $authToken);
  } catch (Exception $e) {
      $response = array(
          'message' =>  $e->getMessage(),
          'code' => $e->getCode(),
          'error' => $e
      );
      alpn_log($response);
      return;
  }

  $topicId = isset($data['topic_id']) ? $data['topic_id'] : "";
  $userId = isset($data['user_id']) ? $data['user_id'] : "";
  $syncId = isset($data['sync_id']) ? $data['sync_id'] : "";
  $topicName = isset($data['topic_name']) && $data['topic_name'] ? $data['topic_name'] : "New";
  $fullName = isset($data['full_name']) && $data['full_name'] ? $data['full_name'] : "";
  $imageHandle = isset($data['image_handle']) && $data['image_handle'] ? $data['image_handle'] : false;
  $ownerId = (isset($data['owner_id']) && $data['owner_id']) ? $data['owner_id'] : "";
  $contactId = (isset($data['contact_id']) && $data['contact_id']) ? $data['contact_id'] : false;
  $channelIdToDelete = (isset($data['channel_id']) && $data['channel_id']) ? $data['channel_id'] : false;

	switch ($operation) {

//TODO Exceptions - rooms there that didn't get deleted causing issues,
    case "update_channel_image":  //update channel with new name accounting for simple and shared topics.
      alpn_log("UPDATING CHANNEL IMAGE");
      $channelId = pte_manage_cc_groups("get_create_channel", $data);   //get or create for the first time.
      $channel = $twilio->chat->v2->services($chatServiceId)
                            ->channels($channelId)
                            ->fetch();
      $channelAttributes = json_decode($channel->attributes, true);  //Start with what's there
      $channelAttributes['image_handle'] = $imageHandle;   //Overwrite New
      $channel = $twilio->chat->v2->services($chatServiceId)
      ->channels($channelId)
      ->update(array(
          'attributes' => json_encode($channelAttributes)
        )
      );
    break;
    case "update_channel":  //update channel with new name accounting for simple and shared topics.
      alpn_log("UPDATING CHANNEL");
      $channelId = pte_manage_cc_groups("get_create_channel", $data);   //get or create for the first time.
      $channel = $twilio->chat->v2->services($chatServiceId)
                            ->channels($channelId)
                            ->fetch();
      $channelAttributes = json_decode($channel->attributes, true);
      $channelAttributes['image_handle'] = $imageHandle;
      $channelAttributes['topic_owner_id'] = $ownerId;
      $channel = $twilio->chat->v2->services($chatServiceId)
      ->channels($channelId)
      ->update(array(
          'friendlyName' => $topicName,
          'attributes' => json_encode($channelAttributes)
        )
      );
    break;

		case "get_create_channel":
    $channelId = "";
    if ($topicId && $ownerId) {
      $results = $wpdb->get_results(
      	$wpdb->prepare("SELECT channel_id, name, image_handle FROM alpn_topics WHERE id = %s", $topicId)
       );
      if (isset($results[0])) {
        $channelId = $results[0]->channel_id;
        $channelName = $results[0]->name;
        $imageHandle = $results[0]->image_handle;
        if (!$channelId) {
          try {
            $channelAttributes = array(
              'image_handle' => $imageHandle,
              'topic_owner_id' => $ownerId
            );
            if ($contactId) {
              $nameAttributes = array(
                'owner_id' => $ownerId,
                'contact_id' => $contactId
              );
              $channelName = json_encode($nameAttributes);
            }
            $channel = $twilio->chat->v2->services($chatServiceId)
              ->channels
              ->create(array(
                'type' => 'private',
                'friendlyName' => $channelName,
                'attributes' => json_encode($channelAttributes),
                'uniqueName' => $topicId
              ));
            $channelId = $channel->sid;
            $member = $twilio->chat->v2  //Add owner to new channel
              ->services($chatServiceId)
              ->channels($channelId)
              ->members
              ->create($ownerId);

            $messageAttributes = array("message_type" => "message");
            $message = $twilio->chat->v2  //Joined Message
              ->services($chatServiceId)
              ->channels($channelId)
              ->messages
              ->create(array(
                  'from' => $ownerId,
                  'body' => "Joined",
                  'attributes' => json_encode($messageAttributes)
                )
              );
              $topicData = array(
                "channel_id" => $channelId
              );
          		$whereClause['id'] = $topicId;
          		$wpdb->update( 'alpn_topics', $topicData, $whereClause );  //persist channelid
              alpn_log("Created New Channel..." . $channelId);
          } catch (Exception $e) {
              $response = array(
                  'message' =>  $e->getMessage(),
                  'code' => $e->getCode(),
                  'error' => $e
              );
              alpn_log('get_create_channel');
              alpn_log($response);
          }

        } else {
          //TODO Handle Error -- did not create a channel
        }
      } else {
        //TODO HANDLE ERROR -- did not find Topic




      }
    } else {
      //TODO HANDLE ERROR -- No TopicID Found
    }

    if ($channelId) { //Make sure it exists
      try {
        $channel = $twilio->chat->v2->services($chatServiceId)
          ->channels($channelId)
          ->fetch();
      } catch (Exception $e) {

          $response = array(
              'message' =>  $e->getMessage(),
              'code' => $e->getCode(),
              'error' => $e
          );

          if ($topicId) {
            alpn_log('CLEARING CHANNEL');
            $topicData = array(
              "channel_id" => ""
            );
            $whereClause['id'] = $topicId;
            $wpdb->update( 'alpn_topics', $topicData, $whereClause );  //clear channelid
        }
      }
    }
    return $channelId;
		break;

    case "add_member":


    alpn_log("Adding Member..." . $userId);
    alpn_log($data);

      $channelId = pte_manage_cc_groups("get_create_channel", $data);   //get or create for the first time.

      if ($channelId) {
        try {
          $member = $twilio->chat->v2->services($chatServiceId)   //check if exists. Shouldn't need but
                                    ->channels($channelId)
                                    ->members($userId)
                                    ->fetch();

          $memberSid= $member->sid;

        } catch (Exception $e) {
            $response = array(
                'message' =>  $e->getMessage(),
                'code' => $e->getCode(),
                'error' => $e
            );
            alpn_log('could not find the member so add them.');
            //alpn_log($response);
            $memberSid = false;
        }

        try {

          if (!$memberSid) {
            alpn_log("Adding Member.." . $userId);
            $member = $twilio->chat->v2
              ->services($chatServiceId)
              ->channels($channelId)
              ->members
              ->create($userId);
          }

          alpn_log("About to Send.." . $userId);

          $messageAttributes = array("message_type" => "message");
          $message = $twilio->chat->v2  //Joined Message
            ->services($chatServiceId)
            ->channels($channelId)
            ->messages
            ->create(array(
                'from' => $userId,
                'body' => "Joined",
                'attributes' => json_encode($messageAttributes)
              )
            );

          alpn_log("Added Member..." . $userId);

        } catch (Exception $e) {
            $response = array(
                'message' =>  $e->getMessage(),
                'code' => $e->getCode(),
                'error' => $e
            );
            alpn_log('add_member');
            alpn_log($response);
            alpn_log('Not sure why this is happening -- should be deleted as a member -- but shouldnt be a problem');
        }
    } else {   //TODO Handle error



    }

		break;



    case "delete_member":


    alpn_log("Deleting Member..." . $userId);
    alpn_log($data);
    $channelId = pte_manage_cc_groups("get_create_channel", $data);

    alpn_log("Deleting Member 1..." . $channelId);


    if ($channelId) {  //TODO SEEMS Like this should not require a loop
      try {
        $members = $twilio->chat->v2
          ->services($chatServiceId)
          ->channels($channelId)
          ->members
          ->read([], 100);

          $memberCount = count($members);


          alpn_log("Deleting Members");
          alpn_log($data);
          alpn_log($memberCount);



           foreach ($members as $record) {
             if ($record->identity == $userId) {

               $user = $twilio->chat->v2
                ->services($chatServiceId)
                ->channels($channelId)
                ->members($record->sid)
                ->delete();
                $memberCount--;
                break;
              }
            }
            if ($memberCount <= 1) {   //owner left. Delete channel. Free up since user can only be concurrently assigned to 1000 channels.
              pte_manage_cc_groups("delete_channel", $data);
              return true;
            }
      } catch (Exception $e) {
          $response = array(
              'message' =>  $e->getMessage(),
              'code' => $e->getCode(),
              'error' => $e
          );
          alpn_log('delete_member');
          alpn_log($response);
        }
    } else {  //TODO Handle Error


    }
    return false;
    break;

    case "delete_channel_by_channel_id":
      alpn_log("About to delete channel by channel ID...");
      if ($channelIdToDelete) {
        try {
          $channel = $twilio->chat->v2
            ->services($chatServiceId)
            ->channels($channelIdToDelete)
            ->delete();
            return true;
        } catch (Exception $e) {
            $response = array(
                'message' =>  $e->getMessage(),
                'code' => $e->getCode(),
                'error' => $e
            );
            alpn_log('delete_channel_by_channel_id');
            alpn_log($response);
        }
      } else { //TODO handle not channelID.


      }
      return false;
    break;

		case "delete_channel":

    alpn_log("About to delete channel...");
    $channelId = pte_manage_cc_groups("get_create_channel", $data);

    if ($channelId && $topicId) {

      $topicData = array(
        "channel_id" => ""
      );
      $whereClause['id'] = $topicId;
      $wpdb->update( 'alpn_topics', $topicData, $whereClause );  //clear channelid

      try {
        $channel = $twilio->chat->v2
          ->services($chatServiceId)
          ->channels($channelId)
          ->delete();
          return true;
      } catch (Exception $e) {
          $response = array(
              'message' =>  $e->getMessage(),
              'code' => $e->getCode(),
              'error' => $e
          );
          alpn_log('delete_channel');
          alpn_log($response);
      }
    } else { //TODO handle error.


    }
    return false;
		break;

		case "add_user":
      try {
        $user = $twilio->chat->v2->services($chatServiceId)
          ->users
          ->create($ownerId);
        $imageHandle = "pte_icon_letter_n.png";  //TODO for new
        $attributes = json_encode(array(
          "image_handle" => $imageHandle,
          "full_name" => $fullName,
          "sync_id" => $syncId
        ));
        $updates = array(
                        "attributes" => $attributes,
                        "friendlyName" => $topicName
                       );
        $user = $twilio->chat->v2->services($chatServiceId)
                                 ->users($ownerId)
                                 ->update($updates);

        alpn_log("Created user with updated settings... " . $ownerId);

      } catch (Exception $e) {
          $response = array(
              'message' =>  $e->getMessage(),
              'code' => $e->getCode(),
              'error' => $e
          );
          alpn_log('add user');
          alpn_log($response);
      }

		break;
    case "update_all_user_attributes":

      $results = $wpdb->get_results("SELECT id, owner_id, sync_id, name, image_handle FROM alpn_topics where special = 'user'");

      foreach($results as $value) {

        if ($value->owner_id) {

          try {
            $user = $twilio->chat->v2->services($chatServiceId)
                                     ->users($value->owner_id)
                                     ->fetch();
            $attributes = json_decode($user->attributes, true);
            $attributes["image_handle"] = $value->image_handle;
            $attributes["full_name"] = $value->name;
            $attributes["sync_id"] = $value->sync_id;
            $updates = array(
                            "attributes" => json_encode($attributes)
                           );

            $user = $twilio->chat->v2
              ->services($chatServiceId)
              ->users($value->owner_id)
              ->update($updates);
              alpn_log("Updated user... " . $ownerId);

          } catch (Exception $e) {
              $response = array(
                  'message' =>  $e->getMessage(),
                  'code' => $e->getCode(),
                  'error' => $e
              );
              alpn_log($response);
          }
        }
      }

    break;

    case "update_user":
      $user = $twilio->chat->v2->services($chatServiceId)
                             ->users($ownerId)
                             ->fetch();
      $attributes = json_decode($user->attributes, true);
      $attributes["image_handle"] = $imageHandle;
      $attributes["full_name"] = $fullName;
      $attributes["sync_id"] = $syncId;


      alpn_log("Updated DATA... ");
      alpn_log($attributes);



      $updates = array(
                      "attributes" => json_encode($attributes),
                      "friendlyName" => $topicName
                     );

    $user = $twilio->chat->v2
      ->services($chatServiceId)
      ->users($ownerId)
      ->update($updates);
      alpn_log("Updated user... " . $ownerId);
		break;

    case "update_user_image":
      alpn_log("Updating user image... ");
      try {

        $user = $twilio->chat->v2->services($chatServiceId)
                               ->users($ownerId)
                               ->fetch();
        $attributes = json_decode($user->attributes, true);


        alpn_log("Before... ");
        alpn_log($attributes);



        $attributes["image_handle"] = $imageHandle;

        alpn_log("After... ");
        alpn_log($attributes);

        $user = $twilio->chat->v2
          ->services($chatServiceId)
          ->users($ownerId)
          ->update(array(
            "attributes" => json_encode($attributes, true)
          ));

          alpn_log("Updated user image... ");
        } catch (Exception $e) {
            alpn_log("Tried to Update user image... ");
            $response = array(
                'message' =>  $e->getMessage(),
                'code' => $e->getCode(),
                'error' => $e
            );
            alpn_log($response);
        }		break;

    case "delete_user":
      try {
        $user = $twilio->chat->v2
          ->services($chatServiceId)
          ->users($ownerId)
          ->delete();
          alpn_log("Deleted user... " . $ownerId);
      } catch (Exception $e) {
          $response = array(
              'message' =>  $e->getMessage(),
              'code' => $e->getCode(),
              'error' => $e
          );
          alpn_log($response);
      }
		break;
	}

}

function pte_record_event(){


}

function pp($objtopp) {
	echo "<pre>"; print_r($objtopp); echo "</pre>";
}

function alpn_log($logstr){
  global $logId;
  $logForUsers = array();
  $logForUsers = array(124, 160, 162, 164, 166, 170, 172, 276, 277);
  $userInfo = wp_get_current_user();
  $userId = isset($userInfo->data->ID) && $userInfo->data->ID ? $userInfo->data->ID : 0;

  if ($logId == 'all' || in_array($userId, $logForUsers)) {
    error_log(date('Y/m/d H:i:s') . ' >>' . PHP_EOL . print_r($logstr, true) . PHP_EOL, 3, get_theme_file_path() . '/logs/alpn_error.log');
  }
}

function pte_make_string($theItems, $theFields, $theMap){

	//Make local Dates.

  // alpn_log('pte_make_string');
  // alpn_log($theItems);
  // alpn_log($theFields);
  // alpn_log($theMap);

	$theString = '';
	foreach ($theItems as $itemKey => $itemValue) {
		$key = $itemValue['type'];
		$value = $itemValue['value'];
		switch ($key){
			case "modified_date_pretty":
				$theString = strtotime("now");
				$theString = date("F j, Y, g:iA", $theString);
			break;
			case "modified_date":
				$theString = strtotime("now");
				$theString = substr("00000000" . $theString, -14);
			break;
			case "make_date":
				$date = $theFields[$theMap[$itemValue['date_field']]];
				$time = $theFields[$theMap[$itemValue['time_field']]];
				$theString = strtotime($date['date'] . " " . $time['time']);
				$theString = substr("00000000" . $theString, -14);
			break;
			case "field":
				if (array_key_exists ($value, $theMap)){
					$theField = $theFields[$theMap[$value]];
					if (is_array($theField)) {
						if (isset($theField['date'])) {
							$pDate = strtotime($theField['date']);
							$theString .= date('F j, Y', $pDate);
						} else if (isset($theField['time'])){
							$pTime = strtotime($theField['time']);
							$theString .= date('g:iA', $pTime);
						}
					} else {
						$theString .= $theFields[$theMap[$value]];
					}
				}
			break;
      case "field_date":
        if (array_key_exists ($value, $theMap)){
          $theDate = $theFields[$theMap[$value]];
          $theString .= date("F j, Y, g:i a", $theDate);


          alpn_log('Testing The Field');
          alpn_log($theDate);
          alpn_log($theString);

        }
      break;
			case "string":
				$theString .= $value;
			break;
			case "field_if_empty":
				if (array_key_exists ($value, $theMap)){
					$theField = $theFields[$theMap[$value]];
					if ($theField != "") {
						return $theField;
					}
				}
			break;
		}
	}
	return $theString;
}


function pte_time_elapsed($secs){
    $bit = array(
        'y' => $secs / 31556926 % 12,
        'w' => $secs / 604800 % 52,
        'd' => $secs / 86400 % 7,
        'h' => $secs / 3600 % 24,
        'm' => $secs / 60 % 60,
        's' => $secs % 60,
		'ms' => $secs
        );

    foreach($bit as $k => $v)
        if($v > 0)$ret[] = $v . $k;

    return join(' ', $ret);
}


function pte_json_out($theObject) {
	header('Content-Type: application/json');
	echo json_encode($theObject);
	return;
}

function get_custom_post_items($post_type, $order){
	$args = array(
		'post_type'=> $post_type,
		'order'    => $order,
		'orderby' => 'title',
		'posts_per_page' => 100
	);

    $loop = new WP_Query( $args );
	if (isset($loop->posts)) {
		$items = $loop->posts;
		foreach ($items as $key => $value) {
			$id = $value->ID;
			$title = $value->post_title;
			$postItems[$id] = $title;
		}
	return ($postItems);
	}
	return ('error');
}

function pte_create_panel($value){

  alpn_log("Creating Panel");

  $value = (is_array($value)) ? (object) $value : $value;

  $topicNetworkId = $value->id;
  $topicDomIdProTeam = $value->dom_id;
  $topicNetworkName = $value->name;
  $topicAccessLevel = $value->access_level;
  $connectedContactStatus = 'not_connected_not_member';
  if ($value->connected_id) {
    $connectedContactStatus = 'connected_member';
  } else if ($value->alt_id) {
     $userData = get_user_by('email', $value->alt_id);
     if (isset($userData->data->ID) && $userData->data->ID) {
       $connectedContactStatus = 'not_connected_member';
     }
  }
  $topicNetworkRights = json_decode($value->member_rights, true);
  $checked = array();
  foreach ($topicNetworkRights as $key2 => $value2) {
    $checked[$key2] = $value2;
  }
  $topicPanelData = array(
    'proTeamRowId' => $value->id,
    'topicNetworkId' => $value->proteam_member_id,
    'topicDomId' => $topicDomIdProTeam,
    'topicNetworkName' => $value->name,
    'topicAccessLevel' => $topicAccessLevel,
    'state' => $value->state,
    'checked' => $checked,
    'connected_contact_status' => $connectedContactStatus,
    'linked_topic_id' => $value->linked_topic_id,
    'linked_topic_name' => $value->linked_topic_name,
    'linked_topic_dom_id' => $value->linked_topic_dom_id,
    'panel_type' => $value->panel_type,
    'connected_type' => $value->connected_type,
    'team_member_wp_id' => $value->wp_id
  );
  return pte_make_rights_panel_view($topicPanelData);
}


function  pte_make_rights_panel_view($panelData) {

  global $wpdb;


//  alpn_log("pte_make_rights_panel_view");
//  alpn_log($panelData);

  $userInfo = wp_get_current_user();
  $userId = $userInfo->data->ID;
  $userNetworkId = get_user_meta( $userId, 'pte_user_network_id', true );

	$topicStates = array('10' => "ADDED", '20' => "Invited", '30' => "CJ", '40' => "CL", '70' => "External", '80' => "Email Sent", '90' => "Declined");

  $panelType = $panelData['panel_type'];
  $teamMemberWpId = $panelData['team_member_wp_id'];
  $linkedTopicId = $panelData['linked_topic_id'];
  $linkedTopicName = $panelData['linked_topic_name'];
  $linkedTopicDomId = $panelData['linked_topic_dom_id'];
  $proTeamRowId = $panelData['proTeamRowId'];
  $topicNetworkId = $panelData['topicNetworkId'];
	$topicDomId = $panelData['topicDomId'];
  $topicNetworkName = $panelData['topicNetworkName'];
	$connectedContactStatus = $panelData['connected_contact_status'];
	$topicAccessLevel = $panelData['topicAccessLevel'];
	$topicState = $panelData['state'];
  $checked = $panelData['checked'];
	$connectedType = $panelData['connected_type'];

  $canEditProteam = ($topicState == '70') ? "none" : "block";

  if ($topicAccessLevel == '10') {
    $generalChecked = "SELECTED";
    $restrictedChecked = "";
  } else if ($topicAccessLevel == '30') {
    $generalChecked = "";
    $restrictedChecked = "SELECTED";
  }

  $connectedContactStatusIcon = "<i class='far fa-user-slash' title='Not a Member'></i>";
  if ($connectedContactStatus == 'not_connected_member') {
    $connectedContactStatusIcon = "<i class='far fa-user' title='Member, Not Connected'></i>";
  } else if ($connectedContactStatus == 'connected_member') {
    $connectedContactStatusIcon = "<i class='far fa-user-check' title='Member, Connected'></i>";
  }


  $permissions = "
    <select id='alpn_select2_small_{$proTeamRowId}' class='alpn_select2_small' data-ptrid='{$proTeamRowId}'>
      <option value='10' {$generalChecked}>Shared</option>
      <option value='30' {$restrictedChecked}>Restricted</option>
    </select>
  ";

	//TODO Loop array.
	$download = (isset($checked['download']) && $checked['download']) ? "<div id='proteam_download' data-item='download' pte-state='set' data-ptid='{$proTeamRowId}' class='proteam_rights_check' onclick='alpn_rights_check(this);'><div class='pte_panel_check'><i class='fa fa-check' style='font-size: 0.9em; color: #4499d7;'></i></div>Download</div>" : "<div id='proteam_download' data-item='download' pte-state='' data-ptid='{$proTeamRowId}' class='proteam_rights_check' onclick='alpn_rights_check(this);'><div class='pte_panel_check'></div>Copy/Download</div>";

  //$share = (isset($checked['share']) && $checked['share']) ? "<div id='proteam_share' data-item='share' pte-state='set' data-ptid='{$proTeamRowId}' class='proteam_rights_check' onclick='alpn_rights_check(this);'><div class='pte_panel_check'><i class='fa fa-check' style='font-size: 0.9em; color: #4499d7;'></i></div>Share</div>" : "<div id='proteam_share' data-item='share' pte-state='' data-ptid='{$proTeamRowId}' class='proteam_rights_check' onclick='alpn_rights_check(this);'><div class='pte_panel_check'></div>Share</div>";
  $share = ' ';

  $print = (isset($checked['print']) && $checked['print']) ? "<div id='proteam_print' data-item='print' pte-state='set' data-ptid='{$proTeamRowId}' class='proteam_rights_check' onclick='alpn_rights_check(this);'><div class='pte_panel_check'><i class='fa fa-check' style='font-size: 0.9em; color: #4499d7;'></i></div>Print</div>" : "<div id='proteam_print' data-item='print' pte-state='' data-ptid='{$proTeamRowId}' class='proteam_rights_check' onclick='alpn_rights_check(this);'><div class='pte_panel_check'></div>Print</div>";

  //if ($connectedContactStatus == 'not_connected_not_member') {

  switch ($panelType) {

    case 'member':   //Mine

      if ($connectedType == 'link') {  //Linked Topic
        $topicStateHtml ="<div title='Visit Linked Topic' data-topic-id='{$linkedTopicId}' data-topic-special='topic' data-topic-dom-id='{$linkedTopicDomId}' data-operation='to_topic_info_by_id' class='team_panel_topic_link' onclick='pte_handle_interaction_link_object(this);'><i class='far fa-link team_panel_topic_link_icon_actual'></i> {$linkedTopicName}</div>";
      } else if ($connectedType == 'join') {
        $topicStateHtml = "Joined";
      } else {
        $topicStateHtml = $topicStates[$topicState];
      }
      $html = "
      <div id='pte_proteam_item_{$proTeamRowId}' class='proteam_user_panel' data-name='{$topicNetworkName}' data-id='{$proTeamRowId}'>
        <div class='pte_vault_row_50'>
            <div id='proTeamPanelUser' data-network-id='{$topicNetworkId}' data-network-dom-id='{$topicDomId}' data-operation='network_info' class='proTeamPanelUser' onclick='pte_handle_interaction_link_object(this);'>{$topicNetworkName} &nbsp;{$connectedContactStatusIcon}</div>
            <div id='proTeamPanelUserData' class='proTeamPanelUserData'><span id='pte_topic_state'>{$topicStateHtml}</span></div>
            <div style='font-weight: normal; color: rgb(0, 116, 187); cursor: pointer; font-size: 11px; line-height: 16px;' onclick='alpn_proteam_member_delete({$proTeamRowId});'>Remove</div>
        </div>
        <div class='pte_vault_row_50'>
          <div id='pte_proteam_controls' class='pte_proteam_controls' data-id='{$topicNetworkId}' style='display: {$canEditProteam};'>
            <table class='pte_proteam_rights_table' data-pte-proteam-id='{$proTeamRowId}'>
              <tr class='pte_proteam_row'>
                <td class='pte_proteam_cell_left'>
                  <div style='display: inline-block; vertical-align: middle; margin-left: 0px; margin-right: 5px; margin-bottom: 3px; font-weight: bold;'>Access:</div><div style='display: inline-block; vertical-align: middle; margin-bottom: 3px; height: 16px;'>{$permissions}</div>
                  <div class='pte_proteam_row_rights'>
                    <div class='pte_proteam_cell_rights_left'>{$print}</div><div class='pte_proteam_cell_rights_right'>$share</div>
                  </div>
                  <div class='pte_proteam_row_rights'>
                    <div class='pte_proteam_cell_rights_left'>{$download}</div><div class='pte_proteam_cell_rights_right'></div>
                  </div>
                </td>
              </tr>
            </table>
          </div>
        </div>
      </div>
        ";

    break;

    case 'visitor':   //Visitor

      $teamMemberNetworkId = get_user_meta( $teamMemberWpId, 'pte_user_network_id', true );

      if ($userNetworkId == $teamMemberNetworkId) {

        if ($connectedType == 'join') { //joined
          $linked = '';
          $showLinkedSelector = 'none';
        }
        if ($connectedType == 'link') {  //linked
          $joined = '';
          $linked = 'SELECTED';
          $showLinkedSelector = 'block';
        }

        $linkTopics = $wpdb->get_results(
      		$wpdb->prepare("SELECT id, name FROM alpn_topics WHERE special = 'topic' AND owner_id = %s ORDER BY NAME ASC", $teamMemberWpId)
      	);
      	$linkTopicSelect = "<select id='alpn_select2_small_link_topic_select_card'>";

      	foreach ($linkTopics as $key => $value) {
          $selected = ($value->id == $linkedTopicId) ? "SELECTED" : '';
      		$linkTopicSelect .= "<option value='{$value->id}'  {$selected}>{$value->name}</option>";
      	}
      	$linkTopicSelect .= "</select>";


      	$connectionTypeSelect = "<select id='alpn_select2_small_connection_type_select_card'>";
      	$connectionTypeSelect .= "<option value='0' {$joined}>Join Topic</option>";
      	$connectionTypeSelect .= "<option value='1' {$linked}>Link to My Topic</option>";
      	//$connectionTypeSelect .= "<option value='2'>Create New Linked Topic</option>";   TODO implement this
      	$connectionTypeSelect .= "</select>";

        //Modify self panel
        $html = "
        <div id='pte_proteam_item_{$proTeamRowId}' class='proteam_user_panel' data-name='{$topicNetworkName}' data-id='{$proTeamRowId}'>
          <div class='pte_vault_row_50'>
            <div id='proTeamPanelUser' data-network-id='{$topicNetworkId}' data-network-dom-id='{$topicDomId}' data-operation='network_info' class='pte_vault_bold'>{$topicNetworkName}</div>
            <div style='font-weight: normal; color: rgb(0, 116, 187); cursor: pointer; font-size: 11px; line-height: 16px;' onclick='alpn_proteam_member_delete({$proTeamRowId});'>Leave</div>
          </div>
          <div class='pte_vault_row_50'>
            <div style='margin-top: 3px;'>
            {$connectionTypeSelect}
            </div>
            <div id='pte_topic_existing_card' style='display: {$showLinkedSelector};''>
            {$linkTopicSelect}
            </div>
          </div>
        </div>
        <script>
    				function pte_handle_connection_type_changed_card(data) {
    					console.log('pte_handle_connection_type_changed...');
    					if (typeof data != 'undefined') {
    						switch(data.id) {
    							case '0':  //join
    								jQuery('#pte_topic_existing_card').hide();
    								// jQuery('#pte_topic_data_transfer_card').hide();
    							break;
    							case '1': //linked
    								jQuery('#pte_topic_existing_card').show();
    								// jQuery('#pte_topic_data_transfer_card').hide();
    							break;
    							case '2':
    								jQuery('#pte_topic_existing_card').hide();
    								// jQuery('#pte_topic_data_transfer_card').show();
    							break;
    						}
                data.proteam_row_id = '{$proTeamRowId}';
                vit_handle_persist_proteam_change(data);
    					}
    				}
    				jQuery('#alpn_select2_small_connection_type_select_card').select2( {
    					theme: 'bootstrap',
    					width: '100%',
    					allowClear: false,
    					minimumResultsForSearch: -1
    				});
            jQuery('#alpn_select2_small_link_topic_select_card').select2( {
    					theme: 'bootstrap',
    					width: '100%',
    					allowClear: false,
    				});
    				jQuery('#alpn_select2_small_connection_type_select_card').on('select2:select', function (e) {
              var newData = e.params.data;
              newData.list_changed = 'connection_type';
    					pte_handle_connection_type_changed_card(newData);
    				});
            jQuery('#alpn_select2_small_link_topic_select_card').on('select2:select', function (e) {
              var newData = e.params.data;
              newData.list_changed = 'topic_id';
    					pte_handle_connection_type_changed_card(newData);
            });
            //pte_handle_connection_type_changed_card();
    		</script>
          ";

      } else {  //connection view panel

        $html = "
        <div id='pte_proteam_item_{$proTeamRowId}' class='proteam_user_panel' data-name='{$topicNetworkName}' data-id='{$proTeamRowId}'>
          <div class='pte_vault_row_50'>
            <div id='proTeamPanelUser' data-network-id='{$topicNetworkId}' data-network-dom-id='{$topicDomId}' data-operation='network_info' class=''>{$topicNetworkName}</div>
          </div>
          <div class='pte_vault_row_50' style='font-size: 12px; line-height: 16px;'>
            Double Opt-In Intro Experience Coming Soon
          </div>
        </div>
        <script>
        </script>
          ";
      }

    break;

  }

    // $html = "
  	// 	<div id='pte_proteam_item_{$proTeamRowId}' class='proteam_user_panel' data-name='{$topicNetworkName}' data-id='{$proTeamRowId}'>
    //     <div class='proTeamPanelUserOuter'>
    //       <div id='proTeamPanelUser' data-network-id='{$topicNetworkId}' data-network-dom-id='{$topicDomId}' data-operation='network_info' class='proTeamPanelUser' onclick='pte_handle_interaction_link_object(this);'>{$topicNetworkName}</div>
  	// 			<div id='proTeamPanelUserData' class='proTeamPanelUserData'><span id='pte_topic_state'>{$topicStates[$topicState]}</span> &nbsp;|&nbsp; {$connectedContactStatusIcon}</div>
    //       <div style='font-weight: normal; color: rgb(0, 116, 187); cursor: pointer; font-size: 11px; line-height: 16px;' onclick='alpn_proteam_member_delete({$proTeamRowId});'>Remove</div>
  	// 		</div>
  	// 		<div class='proTeamPanelSettings'>
    //     External
  	// 		</div>
  	// 	</div>
  	// 	";

	return $html;
}

function getUserIP()
{
    // Get real visitor IP behind CloudFlare network
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
              $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
              $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }

    return $ip;
}



?>
