<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 
$root = $_SERVER['DOCUMENT_ROOT'];
require_once $root.'/wp-content/themes/memberlite-child-master/api_handler/twillo_chat_sdk/vendor/autoload.php';
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\ChatGrant;

$input = file_get_contents('php://input');
$data = json_decode($input);
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

	// render token to string
	echo $token->toJWT();
}