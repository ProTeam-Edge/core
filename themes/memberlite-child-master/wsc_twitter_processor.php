<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
use Abraham\TwitterOAuth\TwitterOAuth;

//error_reporting(E_ERROR);

alpn_log("Handle Scheduled Twitter Post");

$allHeaders = getallheaders();
$isSecure = isset($allHeaders['MORALIS_EXTRA_SECURITY']) && $allHeaders['MORALIS_EXTRA_SECURITY'] == MORALIS_EXTRA_SECURITY && isset($allHeaders['User-Agent']) && $allHeaders['User-Agent'] == "Google-Cloud-Scheduler" ? true : false;

if (!$isSecure) {
	alpn_log("INSECURE");
	exit;
}

$twitterAccountOwnerId = 164;

$availableBanners = array(
	"3x1" => array(
		"max" => 3
	),
	"6x2" => array(
		"max" => 12
	),
	"9x3" => array(
		"max" => 27
	),
	"12x4" => array(
		"max" => 48
	),
	"15x5" => array(
		"max" => 75
	)
);

$availableTweetBanners = array(
	"1x3" => array(
		"max" => 3
	),
	"2x6" => array(
		"max" => 12
	),
	"3x9" => array(
		"max" => 27
	),
	"4x12" => array(
		"max" => 48
	),
	"5x15" => array(
		"max" => 75
	),
	"3x1" => array(
		"max" => 3
	),
	"6x2" => array(
		"max" => 12
	),
	"9x3" => array(
		"max" => 27
	),
	"12x4" => array(
		"max" => 48
	),
	"15x5" => array(
		"max" => 75
	)
);

$twitterData = $wpdb->get_results(
	$wpdb->prepare("SELECT twitter, twitter_meta from alpn_user_metadata WHERE id = %d ", $twitterAccountOwnerId)
 );

if (!isset($twitterData[0]) || $twitterData[0]->twitter == '{}') {
		alpn_log("Re-login to Twitter"); //NOTIFY!!!
		$emailHeader = "Logged Out of Twitter";
		$mailer = WC()->mailer();
		$template = 'vit_generic_email_template.php';
		$content = 	wc_get_template_html( $template, array(
				'email_heading' => $emailHeader,
				'email'         => $mailer,
				'email_body'    => "Use WW to relogin using Wiscle NFT account."
			), PTE_ROOT_PATH . 'woocommerce/emails/', PTE_ROOT_PATH . 'woocommerce/emails/');
		try {
			$mailer->send( "pvermont@wiscle.com", $emailHeader, $content );
		} catch (Exception $e) {
			alpn_log ('Caught EMAIL exception: '. $e->getMessage());
		}
	exit;
}

// alpn_log("FINISHED TWITTER ACCOUNT CHECK EXITING");
// exit;

$twitterContent = $wpdb->get_results(
	$wpdb->prepare("SELECT message, source_set, source_owner_id, twitter_accounts, curator, nft_category FROM alpn_twitter_content WHERE status = 'ready' ORDER BY RAND() LIMIT 1")
 );

if (isset($twitterContent[0])) {

	$setId = $twitterContent[0]->source_set;
	$setOwnerId = $twitterContent[0]->source_owner_id;
	$twitterMessage = $twitterContent[0]->message;
	$isPfp = $twitterContent[0]->nft_category == "pfp" ? true : false;

	$setContent = $wpdb->get_results(
		$wpdb->prepare("SELECT s.nft_id, m.thumb_large_file_key, m.thumb_share_file_key, m.thumb_mime_type FROM alpn_nft_sets s LEFT JOIN alpn_nft_meta m ON m.id = s.nft_id WHERE s.owner_id = %d AND s.set_name = %s ORDER BY RAND()", $setOwnerId, $setId)
	 );

	 $setCount = count($setContent);

	 if ($setCount) {
		 $twitterMeta = json_decode($twitterData[0]->twitter_meta, true);
		 $twitterCreds = json_decode($twitterData[0]->twitter, true);
		 $accessToken = $twitterCreds['oauth_token'];
		 $accessTokenSecret = $twitterCreds['oauth_token_secret'];
		 $connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $accessToken, $accessTokenSecret);

		 $postingAccountScreenName = $twitterCreds['screen_name'];

		 $galleryCuratorTwitter = $twitterContent[0]->curator;
		 $curator = "\ncurator: {$galleryCuratorTwitter}";

		 $creatorList = $twitterContent[0]->twitter_accounts ? "\ncreators: {$twitterContent[0]->twitter_accounts}" : "";
		 $twitterTextData = "gallery: https://wiscle.com/gallery/?member_id={$setOwnerId}&set_id={$setId}{$creatorList}{$curator}";

			$tweetOptions = array("tweet", "tweet", "banner", "banner", "profile_banner");
			if ($isPfp) {
				$tweetOptions[] = "profile_pfp";
			}
		  $doThis = $tweetOptions[array_rand($tweetOptions)];
		  $doThis = "profile_banner";
			switch ($doThis) {
					case "profile_banner":
						alpn_log("PROFILE BANNER");

						$availableSizes = array();
						foreach ($availableBanners as $key => $value) {
							if ($value['max'] <= $setCount) {
								$availableSizes[$key] = $value;
							}
						}
						$counter = 0;
						$randomBannerIndex = rand(0, count($availableSizes));
						foreach ($availableSizes as $key => $value) {
							if ($counter == $randomBannerIndex) {
								break;
							}
							$counter++;
						}
						$shortId = pte_get_short_id();
						$fileId = $shortId . ".webp";
						$filePath = WSC_PREVIEWS_PATH . $fileId;
						try {
							wsc_set_to_banner($fileId, $setId, $key, "", $setOwnerId);
							$bannerData = base64_encode(file_get_contents($filePath));
							$result = $connection->post("account/update_profile_banner", ["banner" => $bannerData]);

							$pfpDescription = isset($twitterMeta['pfp']) ? "\npfp: https://wiscle.com/gallery/?member_id={$twitterMeta['pfp']['set_owner_id']}&set_id={$twitterMeta['pfp']['set_id']} curator: {$twitterMeta['banner']['curator_twitter_id']}" : "";
							$newProfileDescription = "banner: https://wiscle.com/gallery/?member_id={$setOwnerId}&set_id={$setId} curator: {$galleryCuratorTwitter}{$pfpDescription}";
							$result = $connection->post("account/update_profile", ["description" => $newProfileDescription]);

							alpn_log($result);

							$twitterMeta['banner'] = array(
								"set_owner_id" => $setOwnerId,
								"set_id" => $setId,
								"curator_twitter_id" => $galleryCuratorTwitter
							);
							$twitterMetaUpdate = array("twitter_meta" => json_encode($twitterMeta));
							$whereClause['id'] = $twitterAccountOwnerId;
							$wpdb->update( 'alpn_user_metadata', $twitterMetaUpdate, $whereClause );

					 } catch (Exception $error) {
						alpn_log("BOOM1");
						alpn_log($error);
					 }
					try {
						$twitterTextData = "New profile banner @wisclenft\n\n" . $twitterTextData;
						// $media1 = $connection->upload('media/upload', ['media' => $filePath]);
						// $parameters = [
						// 	'status' => $twitterTextData,
						// 	'media_ids' => $media1->media_id_string
						// 	];
					 // $result = $connection->post('statuses/update', $parameters);
					 unlink($filePath);

				 } catch (Exception $error) {
					alpn_log("BOOM2");
					alpn_log($error);
				 }
				unlink($filePath);
			break;
			case "banner":
				alpn_log("BANNER");
				$twitterTextData = $twitterMessage ? "{$twitterMessage}\n\n" . $twitterTextData : $twitterTextData;
				$availableSizes = array();
				 foreach ($availableTweetBanners as $key => $value) {
					 if ($value['max'] <= $setCount) {
						 $availableSizes[$key] = $value;
					 }
				 }
				 $counter = 0;
				 $randomBannerIndex = rand(0, count($availableSizes));
				 foreach ($availableSizes as $key => $value) {
					 if ($counter == $randomBannerIndex) {
						 break;
					 }
					 $counter++;
				 }
				 $shortId = pte_get_short_id();
				 $fileId = $shortId . ".webp";
				 $filePath = WSC_PREVIEWS_PATH . $fileId;
				 try {
					 wsc_set_to_banner($fileId, $setId, $key, "", $setOwnerId);
					 $media = $connection->upload('media/upload', ['media' => $filePath]);
					 $parameters = [
						'status' => $twitterTextData,
						'media_ids' => $media->media_id_string
					 ];
					 $result = $connection->post('statuses/update', $parameters);
					 unlink($filePath);
					} catch (Exception $error) {
					 alpn_log("BOOM5");
					 alpn_log($error);
					}
			break;
			case "tweet":
				alpn_log("TWEET");
				$twitterTextData = $twitterMessage ? "{$twitterMessage}\n\n" . $twitterTextData : $twitterTextData;
				$featureQtyMax = $setCount > 4 ? 4 : $setCount;
				$featureQty = rand(1, $featureQtyMax);
				$fileIds = array();
				for ($i = 1; $i <= $featureQty; $i++) {
					$nft = $setContent[$i];
					try {
						$shareFileKey = $nft->thumb_large_file_key;
						$filePath = WSC_PREVIEWS_PATH . $shareFileKey;
						$nftUrl = PTE_IMAGES_ROOT_URL . $shareFileKey;
						file_put_contents($filePath, file_get_contents($nftUrl));
						$media = $connection->upload('media/upload', ['media' => $filePath]);
						$fileIds[] = $media->media_id_string;
						unlink($filePath);
					} catch (Exception $error) {
					 alpn_log("BOOM3");
					 alpn_log($error);
					}
				}
				try {
					$parameters = [
						 'status' => $twitterTextData,
						 'media_ids' => implode(',', $fileIds)
					];
					$result = $connection->post('statuses/update', $parameters);
				} catch (Exception $error) {
				 alpn_log("BOOM4");
				 alpn_log($error);
				}
			break;
			case "profile_pfp":
				alpn_log("PFP");
				try {
					$nft = $setContent[0];
					$shareFileKey = $nft->thumb_share_file_key;
					$filePath = WSC_PREVIEWS_PATH . $shareFileKey;
					$nftUrl = PTE_IMAGES_ROOT_URL . $shareFileKey;

					$imageData = base64_encode(file_get_contents($nftUrl));
					$result = $connection->post("account/update_profile_image", ["image" => $imageData]);

					$bannerDescription = isset($twitterMeta['banner']) ? "banner: https://wiscle.com/gallery/?member_id={$twitterMeta['banner']['set_owner_id']}&set_id={$twitterMeta['banner']['set_id']} curator: {$twitterMeta['banner']['curator_twitter_id']}\n" : "";
					$newProfileDescription = "{$bannerDescription}pfp: https://wiscle.com/gallery/?member_id={$setOwnerId}&set_id={$setId} curator: {$galleryCuratorTwitter}";
					$result = $connection->post("account/update_profile", ["description" => $newProfileDescription]);

					$twitterMeta['pfp'] = array(
						"set_owner_id" => $setOwnerId,
						"set_id" => $setId,
						"curator_twitter_id" => $galleryCuratorTwitter
					);
					$twitterMetaUpdate = array("twitter_meta" => json_encode($twitterMeta));
					$whereClause['id'] = $twitterAccountOwnerId;
					$wpdb->update( 'alpn_user_metadata', $twitterMetaUpdate, $whereClause );

				} catch (Exception $error) {
					alpn_log("BOOM6");
					alpn_log($error);
				}
				try {
					$twitterTextData = "New PFP @wisclenft\n\n" . $twitterTextData;
					file_put_contents($filePath, file_get_contents($nftUrl));
					$media = $connection->upload('media/upload', ['media' => $filePath]);
					$parameters = [
						'status' => $twitterTextData,
						'media_ids' => $media->media_id_string
						];
				 $result = $connection->post('statuses/update', $parameters);

				 unlink($filePath);

			 } catch (Exception $error) {
				alpn_log("BOOM7");
				alpn_log($error);
			 }
			break;
		}
	}
}


?>
