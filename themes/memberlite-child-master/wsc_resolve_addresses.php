<?php
set_time_limit(300);
ini_set('memory_limit', '1048M');
ini_set('max_execution_time', 300);
include('/var/www/html/proteamedge/public/wp-blog-header.php');

use Google\Cloud\Storage\StorageClient;

pp("Generate social previews");

$fromNum = 10000;

$nftResults = $wpdb->get_results(
  $wpdb->prepare("SELECT * FROM alpn_nft_meta where %d", "1")
 );

 pp(count($nftResults));

 $storage = new StorageClient([
     'keyFilePath' => GOOGLE_STORAGE_KEY
 ]);

 $bucketName = 'pte_media_store_1';
 $bucket = $storage->bucket($bucketName);

foreach ($nftResults as $key => $nft) {

    if ($key >= $fromNum && $key < ($fromNum + 50)) {

    $fileKey = substr($nft->thumb_large_file_key, 6, -5);

    if ($fileKey) {

      $sourceUrl = PTE_IMAGES_ROOT_URL . $nft->thumb_large_file_key;
      $localDestination = PTE_ROOT_PATH . "tmp/" . $nft->thumb_large_file_key;
      file_put_contents($localDestination, file_get_contents($sourceUrl));

      $fileNameWithExtensionShare = "share_" . $fileKey . ".jpeg";
      $thumbpathShare = PTE_ROOT_PATH . "tmp/". $fileNameWithExtensionShare;
      imageToJpeg($localDestination, $thumbpathShare, 480);

          try {
            $object = $bucket->upload(
                fopen($thumbpathShare, 'r'),
                ['name' => $fileNameWithExtensionShare]
            );

          } catch (Exception $e) {
            pp("FAILED");
            pp($e);
          }

        pp($fileNameWithExtensionShare);
        unlink ($localDestination);
        unlink ($thumbpathShare);
    }
  }
}


pp("DONE");
exit;

// $nfts = array(17827, 17913, 17948, 17738, 17834);
// //wsc_create_vid_from_images($nfts);
//
// $resolveAddress = "0xa93cfddb2d48df5e7492a82ecc57a554d17f0c0c";





pp("Resolving Addresses");

//0xa93cfddb2d48df5e7492a82ecc57a554d17f0c0c  -- Pat
//0x5B93FF82faaF241c15997ea3975419DDDd8362c5  -- Coopatroopha
//0x267be1c1d684f78cb4f6a176c4911b741e4ffdc0  -- Jay Vermont
//0x3B3525F60eeea4a1eF554df5425912c2a532875D  -- Dame
//0xEB889d3FFD7170cD1E25A3B2cB0D522b8EAA5CB7  -- Cantino
//0xFC5446EfE679f109f2772e45EA623CaA63791d5e  -- Rebekah Bastian
//0x6002cA2e11B8e8c0F1F09c67F551B209eb51A0E4  -- Keith Axline
//0x1dF428833f2C9FB1eF098754e5D710432450d706  -- OxJoshua
//0x7221B104fba7701084759FD25FacA19Ac6300855  -- John Paller
//0x7B6f2f3032664691586AEDfeadBd60D6F5d88DA6  -- Jaime Schmidt
//0x14977b0dBE7e155F9907effECbB70c9b7a05e737  -- Nik Kalyani
//0xe11BFCBDd43745d4Aa6f4f18E24aD24f4623af04  -- Chris Dixon
//0xE0036fb4B5A3B232aCfC01fEc3bD1D787a93da75  -- Snoop



$data = array(
	'0x5ea9681C3Ab9B5739810F8b91aE65EC47de62119'

);

foreach ($data as $item) {
	$resolved = wsc_resolve_ens($item);
	pp("{$item} => {$resolved}");
}

pp("DONE");

?>
