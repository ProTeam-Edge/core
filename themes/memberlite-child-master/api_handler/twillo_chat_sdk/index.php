<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 
$root = $_SERVER['DOCUMENT_ROOT'];

$input = file_get_contents('php://input');
$data = json_decode($input);
if(isset($data->username))
{
$username = $data->username;
// Get the PHP helper library from https://twilio.com/docs/libraries/php
require_once ''.$root.'/wp-content/themes/memberlite-child-master/api_handler/twillo_chat_sdk/vendor/autoload.php'; // Loads the library
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\ChatGrant;

// Required for all Twilio access tokens
$twilioAccountSid = 'AC9ff1cc4c98a58c86d4e1942034ec6d41';
$twilioApiKey = 'SK4f18b268558283cb58ed64093a083acc';
$twilioApiSecret = 'nrDvX76GLS39xq3JPxovT2gXPHxNnkYr';

// Required for Chat grant
$serviceSid = 'IS0e86f36674e9492c8aede1549c216784';
// choose a random username for the connecting user
$identity =$username;

// Create access token, which we will serialize and send to the client
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
else {
echo 'No params found.';
}