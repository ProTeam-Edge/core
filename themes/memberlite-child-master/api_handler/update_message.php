<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include_once('../pte_config.php');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 
$root = $_SERVER['DOCUMENT_ROOT'];
require_once $root.'/wp-content/themes/memberlite-child-master/api_handler/sdk/vendor/autoload.php';
use Twilio\Rest\Client;
$array = array();
$input = file_get_contents('php://input');
$data = json_decode($input);
$sid    = ACCOUNT_SID;
$token  =AUTHTOKEN;
$serviceSid = CHATSERVICESID;
$twilio = new Client($sid, $token);
$message_id = $data->message_id;
$channel_id = $data->channel_id;
$message = $data->message;
if(!empty($data)) {
	try {
		$message = $twilio->chat->v2->services($serviceSid)
                            ->channels($channel_id)
                            ->messages($message_id)
                            ->update(["body" =>$message]);
		
		$response = array('success' => 1, 'message'=>'Message updated successfully.','data'=>'');
	} catch (Exception $e) {
		$response = array('success' => 2, 'message'=>'There was some error','data'=>$e->getMessage());
	}
} else {
	$response = array('success' => 0, 'message'=>'No required parameters found.','data'=>'');
}
echo json_encode($response); 