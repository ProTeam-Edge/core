<?php 
include('/var/www/html/proteamedge/public/wp-blog-header.php');
include_once('../pte_config.php');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 
$root = $_SERVER['DOCUMENT_ROOT'];
require_once $root.'/wp-content/themes/memberlite-child-master/api_handler/sdk/vendor/autoload.php';
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\ChatGrant;
use Twilio\Jwt\Grants\VideoGrant;
$input = file_get_contents('php://input');
$data = json_decode($input);
if(!empty($data->apiToken) && !empty( $data->username))
{
$twilioAccountSid = ACCOUNT_SID;
$twilioApiKey = APIKEY;
$twilioApiSecret = SECRETKEY;
$apiToken =  $data->apiToken;
$serviceSid = CHATSERVICESID;
$NOTIFYSSID = NOTIFYSSID;
$FCMCREDENTIALSID = FCMCREDENTIALSID;
$PUSHCREDENTIALSIDDEV = PUSHCREDENTIALSIDDEV;

	$get_token = get_option('api_request_token_'.$data->username.'');
	/* if($get_token==$apiToken) { */
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
	$chatGrant->setpushCredentialSid($PUSHCREDENTIALSIDDEV);
	
		
	// Add grant to token
	$token->addGrant($chatGrant);
	$grant = new VideoGrant();
	$token->addGrant($grant);
	// render token to string
	echo $token->toJWT();
	/* } else {
		echo 'Not a valid token';
	} */
} else {
	echo 'No required parameters found';
}
