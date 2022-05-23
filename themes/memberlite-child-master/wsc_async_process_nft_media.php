<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');

require 'vendor/autoload.php';
use Google\Cloud\Storage\StorageClient;

alpn_log("Handle ASYNC Process NFT MEDIA");

$supportedFiles = array("pdf", "jpg", "png", "gif", "webp", "svg", "wav", "mp3", "mpeg", "wav", "mp4", "webm");

$verificationKey = (isset($_POST['verification_key']) && strlen($_POST['verification_key']) >= 20 && strlen($_POST['verification_key']) <= 22) ? $_POST['verification_key'] : false;

if ( $verificationKey ) {

	$data = vit_get_kvp($verificationKey);

	if (isset($data['process_id']) && isset($data['owner_id']) && isset($data['vault_id'])) {

			$vId = $data['vault_id'];

			$results = $wpdb->get_results(
				$wpdb->prepare("SELECT mime_type, original_ext, file_name, pdf_key, file_key FROM alpn_vault WHERE id = %d", $vId)
			 );

			if (isset($results[0])) {

				$vaultItem = $results[0];
				$originalExt = $vaultItem->original_ext;
				$currentMimeType = $vaultItem->mime_type;

				try {
					$storage = new StorageClient([
							'keyFilePath' => GOOGLE_STORAGE_KEY
					]);
					$storage->registerStreamWrapper();
					$content = file_get_contents("gs://pte_file_store1/{$objectName}");

				} catch (\Exception $e) { // Global namespace
						$pte_response = array("topic" => "pte_get_vault_google_exception", "message" => "Problem accessing Google Vailt.", "data" => $e);
						pp($pte_response);
						exit;
				}
			}
		}

} else {
	alpn_log("No VERIFICATION KEY");
}
?>
