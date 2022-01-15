<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

alpn_log('Received Callback From Twilio Chat...');

use Twilio\Rest\Client;
global $wpdb;

$accountSid = ACCOUNT_SID;
$authToken = AUTHTOKEN;
$chatService = CHATSERVICESID;

$data = $_POST;

$channelSid = isset($data['ChannelSid']) && $data['ChannelSid'] ? $data['ChannelSid'] : false;
$messageAttributes = isset($data['Attributes']) && $data['Attributes'] ? json_decode(stripslashes($data['Attributes']), true) : array();
$previewFileId = isset($messageAttributes['file_name']) && $messageAttributes['file_name'] ? $messageAttributes['file_name'] : "";

$roomMembers = array();

if ($channelSid) {

  try {

    $twilio = new Client($accountSid, $authToken);

    $channel = $twilio->chat->v2->services($chatService)
                                ->channels($channelSid)
                                ->fetch();

    $members = $twilio->chat->v2->services($chatService)
                                ->channels($channelSid)
                                ->members
                                ->read([], 100);

    $sender = $twilio->chat->v2->services($chatService)
                                ->users($data['From'])
                                ->fetch();

    foreach ($members as $member) {
      $roomMembers[] = get_user_meta( $member->identity, 'pte_user_network_id', true );
    }

    foreach ($members as $member) {

      $notificationData = array(
        "type" => "new_chat_message",
        "channel_sid" => $channelSid,
        "channel_unique_name" => $channel->uniqueName,
        "channel_friendly_name" => $channel->friendlyName,
        "identity" => $member->identity,
        "sender_id" => $data['From'],
        "sender_name" => $sender->friendlyName,
        "body" => $data['Body'],
        "members" => $roomMembers,
        "preview_file_name" => $previewFileName
      );
      wsc_send_notifications($notificationData);
    }

  } catch(Exception $e) {

    alpn_log("EXCEPTION TWILIO CHAT CALLBACK");
    alpn_log($e);
 	}

}
//

//
// }
//
// alpn_log('DONE -- Received Callback From Twilio Chat...');

http_response_code(200);
