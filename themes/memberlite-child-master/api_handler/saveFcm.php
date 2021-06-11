<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
if(isset($_POST['token']) && !empty($_POST['token'])) {
    $token = $_POST['token'];
    $userId = $_POST['userId'];
    $sql = 'update '.$wpdb->prefix.'users set device_token_web = '.$token.' where ID = '.$userId.'';
    $data = $wpdb->query($sql);
    if($data)
    $output = 'success'; 
}
else 
    $output = 'Not allwed';

echo json_encode($output);
?>