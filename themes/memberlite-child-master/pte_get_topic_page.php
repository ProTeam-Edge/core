<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$data = $_POST;
$pageNumber = pte_get_page_number($data);
pte_json_out(array("page_number" => $pageNumber));

?>
