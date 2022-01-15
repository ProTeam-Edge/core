<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
alpn_log('Delete Tmp File...');
$data = $_POST;
$fileName = isset($data['file_name']) && $data['file_name'] ? $data['file_name'] : false;
if ($fileName) {
  try {
    unlink (PTE_ROOT_PATH . "tmp/" . $fileName);
  } catch(Exception $e) {
    alpn_log("Delete Temp File");
    alpn_log($e);
 	}
}
pte_json_out(PTE_ROOT_PATH . "tmp/" . $fileName);
