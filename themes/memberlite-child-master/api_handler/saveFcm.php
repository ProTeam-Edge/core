<?php
include_once('../pte_config.php');
$root = $_SERVER['DOCUMENT_ROOT'];
require_once $root.'/wp-content/themes/memberlite-child-master/api_handler/sdk/vendor/autoload.php';
include('/var/www/html/proteamedge/public/wp-blog-header.php');
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\ChatGrant;
use Twilio\Rest\Client;
$sid    = ACCOUNT_SID;
$token  =AUTHTOKEN;
$twilio = new Client($sid, $token);
$serviceSid = NOTIFYSSID;
global $wpdb;
if(isset($_POST['token']) && !empty($_POST['token'])) {
    $token = $_POST['token'];
    $userId = $_POST['userId'];
    $sql = 'update '.$wpdb->prefix.'users set device_token_web = "'.$token.'" where ID = "'.$userId.'"';
    $data = $wpdb->query($sql);
    if($data)
    $output = 'success';
    try {
        $binding = $twilio->notify->v1->services($serviceSid)
        ->bindings
        ->create($userId, "fcm", $token);
    } catch (Exception $e) {
        $response = array(
            'message' => 'Error creating notification: ' . $e->getMessage(),
            'error' => $e->getMessage()
        );

        $output = json_encode($response);
    }
}
else
    $output = 'Not allowed';

echo json_encode($output);
?>
