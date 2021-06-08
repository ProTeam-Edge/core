<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include_once('../pte_config.php');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 
$root = $_SERVER['DOCUMENT_ROOT'];
require_once $root.'/wp-content/themes/memberlite-child-master/api_handler/sdk/vendor/autoload.php';
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\ChatGrant;
use Twilio\Rest\Client;



// Find your Account Sid and Auth Token at twilio.com/console
// and set the environment variables. See http://twil.io/secure
/* $sid = ACCOUNT_SID;
$token = AUTHTOKEN;
$twilio = new Client($sid, $token);

$service = $twilio->notify->v1->services
                              ->create();

print($service->sid);
 */
$sid    = ACCOUNT_SID;
$token  =AUTHTOKEN;
$twilio = new Client($sid, $token);
$serviceSid = NOTIFYSSID;
$pushCredentialSid = PUSHCREDENTIALSIDDEV;
$service = $twilio->notify->v1->services($pushCredentialSid);

$json = json_decode(file_get_contents('php://input'), true);


try {
    $notification = $service->notifications->create(
        [
            'identity' => '162',
            'body' => 'Hello world!'
        ]
    );

    $response = array(
        'message' => 'Notification Sent!'
    );
    header('Content-type:application/json;charset=utf-8');
    echo json_encode($response);
} catch (Exception $e) {
    $response = array(
        'message' => 'Error creating notification: ' . $e->getMessage(),
        'error' => $e->getMessage()
    );
    header('Content-type:application/json;charset=utf-8');
    http_response_code(500);
    echo json_encode($response);
}
