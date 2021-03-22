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
$serviceSid = 'ISe2ce4eeed597d0b555132fa36d43f6be';
$service = $twilio->chat->v2->services($serviceSid)
                            ->update(array(
                                         "notificationsAddedToChannelEnabled" => True,
                                         "notificationsAddedToChannelSound" => "default",
                                         "notificationsAddedToChannelTemplate" => "A New message"
                                     )
                            );



print($service->friendlyName);