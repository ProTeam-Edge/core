<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
include('../vendor/autoload.php');

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;
use Twilio\Jwt\Grants\SyncGrant;
use Twilio\Jwt\Grants\ChatGrant;

// An identifier for your app - can be anything you'd like
$appName = 'ProTeam Edge Chat';

$accountSid = ACCOUNT_SID;
$apiKey = APIKEY;
$secretKey = SECRETKEY;
$chatServiceId = CHATSERVICESID;
$syncServiceId = SYNCSERVICEID;

$token = new AccessToken(
    $accountSid,
    $apiKey,
    $secretKey,
    3600,
    $userID
);
// Grant access to Video
$grant = new VideoGrant();
$token->addGrant($grant);

$syncGrant = new SyncGrant();
$syncGrant->setServiceSid($syncServiceId);
$token->addGrant($syncGrant);

$chatGrant = new ChatGrant();
$chatGrant->setServiceSid($chatServiceId);
$token->addGrant($chatGrant);

// return serialized token and the user's randomly generated ID
header('Content-type:application/json;charset=utf-8');
echo json_encode(array(
    'identity' => $userID,
    'token' => $token->toJWT(),
));
