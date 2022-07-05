<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');

require 'vendor/autoload.php';
use Google\Cloud\Storage\StorageClient;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Cookie\CookieJar;

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

$supportedFilesArchive = array("zip");
$supportedFilesDocument = array("pdf");
$supportedFilesImage = array("jpg", "png", "gif", "webp", "svg");
$supportedFilesMusic = array("mpeg", "wav", "mp3");
$supportedFilesVideo = array("mp4", "webm");
$supportedFiles = array_merge($supportedFilesArchive, $supportedFilesDocument, $supportedFilesImage, $supportedFilesMusic, $supportedFilesVideo);

alpn_log("Handle ASYNC Process NFT MEDIA");

$verificationKey = (isset($_POST['verification_key']) && strlen($_POST['verification_key']) >= 20 && strlen($_POST['verification_key']) <= 22) ? $_POST['verification_key'] : false;

if ( $verificationKey ) {

	$tmpDir = false;

	$data = vit_get_kvp($verificationKey);

	if (isset($data['process_id']) && isset($data['owner_id']) && isset($data['vault_id']) && isset($data['nft_token_id'])) {

			$tokenId = $data['nft_token_id'];

			$name = $description = $imageContent = "";
			$results = $wpdb->get_results(
				$wpdb->prepare("SELECT mime_type, original_ext, file_name, pdf_key, file_key FROM alpn_vault WHERE id = %d", $data['vault_id'])
			 );

			if (isset($results[0])) {

				$vaultItem = $results[0];
				$originalExt = $vaultItem->original_ext;
				$currentMimeType = $vaultItem->mime_type;

				if (in_array($originalExt, $supportedFiles)) {

					$fileKey = $vaultItem->file_key;

					try {
						$storage = new StorageClient([
								'keyFilePath' => GOOGLE_STORAGE_KEY
						]);
						$storage->registerStreamWrapper();

						if ($originalExt == "zip") {

							$tmpDir = PTE_ROOT_PATH . "tmp/" . $data['process_id'];
							mkdir($tmpDir);
							$content = file_get_contents("gs://pte_file_store1/{$fileKey}");
							$destination = "{$tmpDir}/{$fileKey}";
							file_put_contents($destination, $content);
							$zip = new ZipArchive;
							if ($zip->open($destination)) {
								if ($zip->getFromName("wiscle_nft_manifest.json")) {
									alpn_log("Handling Manifest");
									$zip->extractTo($tmpDir);
									$zip->close();
									$manifest = json_decode(file_get_contents($tmpDir . "/wiscle_nft_manifest.json"), true);
									if (isset($manifest['media_file_name']) && $manifest['media_file_name']) {
										$fileKey = $manifest['media_file_name'];
										$mediaFile = $tmpDir . "/{$fileKey}";
										if (file_exists($mediaFile)) {
											$name = (isset($manifest['name']) && $manifest['name']) ? $manifest['name'] : "";
											$description = (isset($manifest['description']) && $manifest['description']) ? $manifest['description'] : "";
											$attributes = (isset($manifest['attributes']) && $manifest['attributes']) ? $manifest['attributes'] : array();
											$content = file_get_contents($mediaFile);
											if (isset($manifest['image_file_name']) && $manifest['image_file_name']) {
												$originalImageExt = substr($manifest['image_file_name'], strrpos($manifest['image_file_name'], ".") + 1);
												$imageKey = "{$tokenId}.{$originalImageExt}";
												$imageFile = $tmpDir . "/{$imageKey}";
												if (file_exists($imageFile)) {
													$imageContent = file_get_contents($imageFile);
												}
											}
										}
									}

									//TODO send it to process. Process should handle inclulding blowing away fields.

								} else {
									//Handles .zip as an archiveUrl. Nothing to do since $content contains the .zip
								}

								if ($tmpDir) {
									shell_exec("rm -rf " . $tmpDir);
								}

							} else {
								alpn_log('Unzip Process failed');
								exit;
							}

						} else if ($currentMimeType == "application/zip") {

							$content = file_get_contents("gs://pte_file_store1/{$fileKey}");

							$destination = PTE_ROOT_PATH . "tmp/{$fileKey}";
							file_put_contents($destination, $content);
							$zip = new ZipArchive;
							if ($zip->open($destination)) {
								$fileKey = $zip->getNameIndex(0);
								$zip->extractTo(PTE_ROOT_PATH . "tmp");
								$zip->close();
								unlink($destination);
								$newFile = PTE_ROOT_PATH . "tmp/{$fileKey}";
								$content = file_get_contents($newFile);
								unlink($newFile);
							} else {
								alpn_log('Unzip Process failed');
							}
						} else {
							$content = file_get_contents("gs://pte_file_store1/{$fileKey}");
						}
						//Check filesize of media. 50mb

						if (!$imageContent && in_array($originalExt, $supportedFilesImage)) {
							$imageKey = "_p_{$tokenId}.{$originalExt}";
							$imageContent = $content; //TODO alts
						}

						$fileArray = array(
							array(
								"path" => "{$data['process_id']}/{$tokenId}.{$originalExt}",
								"content" => base64_encode($content)
							)
						);
						if ($imageContent) {
							$fileArray[] = array(
								"path" => "{$data['process_id']}/{$imageKey}",
								"content" => base64_encode($imageContent)
							);
						}

						$fileArray = json_encode($fileArray);
						$client = new Client([
								'timeout'  => 60
						]);

						$fullUrl = "https://deep-index.moralis.io/api/v2/ipfs/uploadFolder";
						$moralisApiKey = MORALIS_API_KEY;
						$headers = array(
											"Accept" => "application/json",
											"x-api-key" => $moralisApiKey,
											"Content-Type" => "application/json"
										);
						$request = new Request('POST', $fullUrl, $headers, $fileArray);
						$client->sendAsync($request)->then(
							 function ($value) {  //Promise
										//TODO put these centrally
										$supportedFilesArchive = array("zip");
										$supportedFilesDocument = array("pdf");
										$supportedFilesImage = array("jpg", "png", "gif", "webp", "svg");
										$supportedFilesMusic = array("mpeg", "wav", "mp3");
										$supportedFilesVideo = array("mp4", "webm");
										$supportedFiles = array_merge($supportedFilesArchive, $supportedFilesDocument, $supportedFilesImage, $supportedFilesMusic, $supportedFilesVideo);

										$results = json_decode($value->getBody()->getContents(), true);
										$Url1 = $results[0]['path'];  //media

										$processIdRight = strrpos($Url1, "/");
										$processId = substr($Url1, 0, $processIdRight);
										$processIdLeft = strrpos($processId, "/");
										$processId = substr($processId, $processIdLeft + 1);

										if (isset($results[1])) {  //preview image
											$metaDataArray['image'] = $results[1]['path'];
										}
									  $originalExt = substr($Url1, strrpos($Url1, ".") + 1);
									  if (in_array($originalExt, $supportedFilesArchive)) {
									    $metaDataArray['archive_url'] = $Url1;
									    $metaDataArray['wsc_media_type'] = "Archive";
									  } else if (in_array($originalExt, $supportedFilesDocument)) {
									    $metaDataArray['document_url'] = $Url1;
											$metaDataArray['wsc_media_type'] = "Document";
									  } else if (in_array($originalExt, $supportedFilesImage)) {
									    $metaDataArray['image_url'] = $Url1;
											$metaDataArray['wsc_media_type'] = "Image";
									  } else if (in_array($originalExt, $supportedFilesMusic)) {
									    $metaDataArray['music_url'] = $Url1;
											$metaDataArray['wsc_media_type'] = "Music";
									  } else if (in_array($originalExt, $supportedFilesVideo)) {
									    $metaDataArray['animation_url'] = $Url1;
											$metaDataArray['wsc_media_type'] = "Video";
									  }
										$processData = array(
											'process_id' => $processId,
											'process_type_id' => "mint_nft",
											'process_data' => array(
													'nft_ipfs_files' => $metaDataArray
												)
										);
										pte_manage_interaction($processData);
						    },
						    function ($reason) {
						      alpn_log( 'The promise was rejected.' );
									alpn_log( $reason );
						    }
						)->wait();

						//Update WW and refresh UI.


					} catch (\Exception $e) {
							alpn_log("PROCESS NFT MEDIA FAILED");
							alpn_log($e);
					}
				}
			}
		}

} else {
	alpn_log("No VERIFICATION KEY");
}
?>
