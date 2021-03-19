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



$sid    = ACCOUNT_SID;
$token  =AUTHTOKEN;
$twilio = new Client($sid, $token);
$serviceSid = SYNCSERVICEID;
$service = $twilio->chat->v2->services($serviceSid)
                            ->update(array(
                                         "notificationsAddedToChannelEnabled" => True,
                                         "notificationsAddedToChannelSound" => "default",
                                         "notificationsAddedToChannelTemplate" => "A New message"
                                     )
                            );
echo '<pre>';
print_r($service);
print($service->friendlyName);