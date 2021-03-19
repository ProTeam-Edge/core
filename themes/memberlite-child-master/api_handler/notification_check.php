<?php
include_once('../pte_config.php');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 
$root = $_SERVER['DOCUMENT_ROOT'];
require_once $root.'/wp-content/themes/memberlite-child-master/api_handler/sdk/vendor/autoload.php';
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\ChatGrant;


$twilioAccountSid = ACCOUNT_SID;
$twilioApiKey = APIKEY;
$twilioApiSecret = SECRETKEY;
$AUTHTOKEN = AUTHTOKEN;

$serviceSid = CHATSERVICESID;
$NOTIFYSSID = NOTIFYSSID;
$FCMCREDENTIALSID = FCMCREDENTIALSID;
$sid = $NOTIFYSSID;
$token = $AUTHTOKEN;
$twilio = new Client($sid, $token);

$notification = $twilio->notify->v1->services("ISd4ca1551946f4360a7dfb215ad84e1d0")
                                   ->notifications
                                   ->create([
                                                "body" => "Hello Bob",
                                                "identity" => ["00000001"]
                                            ]
                                   );

print($notification->sid);