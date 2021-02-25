<?php 
include_once('../pte_config.php');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 
$root = $_SERVER['DOCUMENT_ROOT'];
require_once $root.'/wp-content/themes/memberlite-child-master/api_handler/sdk/vendor/autoload.php';
use Twilio\Rest\Client;
$input = file_get_contents('php://input');
$data = json_decode($input);
if(isset( $data->id) && !empty($data->id)) {
	$id = $data->id;
	$sid = ACCOUNT_SID;
	$token =AUTHTOKEN;
	$cservice =CHATSERVICESID;
	$twilio = new Client($sid, $token);
	$call = $twilio->chat->v2->services($cservice)->channels($id)->delete();
	if($call==1) {
		$response = array('success' => 1, 'message'=>'Channel deleted.');
	}
}
else {
	$response = array('success' => 0, 'message'=>'No valid channel id passed.');
}
echo json_encode($response);
?>