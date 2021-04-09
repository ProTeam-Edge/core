<?php
include_once('../pte_config.php');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 
$root = $_SERVER['DOCUMENT_ROOT'];
require_once $root.'/wp-content/themes/memberlite-child-master/api_handler/sdk/vendor/autoload.php';
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\ChatGrant;
use Twilio\Jwt\Grants\VideoGrant;
use Twilio\Rest\Client;

$input = file_get_contents('php://input');
$data = json_decode($input);
$twilioAccountSid = ACCOUNT_SID;
$twilioApiKey = APIKEY;
$twilioApiSecret = SECRETKEY;

$serviceSid = CHATSERVICESID;
$NOTIFYSSID = NOTIFYSSID;
$FCMCREDENTIALSID = FCMCREDENTIALSID;
$sid =ACCOUNT_SID;
$token = AUTHTOKEN;
if(!empty($data))
{
	$username = $data->username;
	$identity =$username;
	$token = new AccessToken(
    $twilioAccountSid,
    $twilioApiKey,
    $twilioApiSecret,
    3600,
    $identity
);

	// Create Chat grant
	$chatGrant = new ChatGrant();
	$chatGrant->setServiceSid($serviceSid);
		
	// Add grant to token
	$token->addGrant($chatGrant);
	$grant = new VideoGrant();
	$token->addGrant($grant);
	// render token to string
	$client = new Client($twilioApiKey, $twilioApiSecret, $twilioAccountSid);

try {
   $room = $twilio->video->v1->rooms("CH429707ef8a1c457cb9aaea2a877d9206")
                          ->fetch();
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


print($room->uniqueName);
	echo $token->toJWT();
}