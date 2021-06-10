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



try {
    $binding = $twilio->notify->v1->services($serviceSid)
    ->bindings
    ->create("128", "fcm", "fAoQw49JAEwEf3n_EA7pv2:APA91bF9VVIDY-YuSTDnSMPOzy-M_luWMWjFdYxM2EkX7G4r9oZsAKTR6cRgW-nkosGby4Mk3eVN-E-ixIeNaeJo2OYfjBcJLQd3dswNnXaazYnTRKl9TzfUje-uniJoJITBAFCSTy38");

print($binding->sid).'11';
} catch (Exception $e) {
    $response = array(
        'message' => 'Error creating notification: ' . $e->getMessage(),
        'error' => $e->getMessage()
    );
    header('Content-type:application/json;charset=utf-8');
    http_response_code(500);
    echo json_encode($response);
}
