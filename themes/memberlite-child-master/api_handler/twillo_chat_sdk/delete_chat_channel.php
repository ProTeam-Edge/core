<?php 
include_once('../../pte_config.php');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 
$root = $_SERVER['DOCUMENT_ROOT'];
require_once $root.'/wp-content/themes/memberlite-child-master/api_handler/twillo_chat_sdk/vendor/autoload.php';
use Twilio\Rest\Client;
$sid = ACCOUNT_SID;
$token =APIKEY;
$twilio = new Client($sid, $token);

/* $response = $twilio->chat->v2->services(CHATSERVICESID)
                 ->channels("CHc12cfcabc00f4d3c8fb4cab900868de7")
                 ->delete();
				 echo '<pre>';
				 print_r($response); */
?>