<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

alpn_log('Received Callback From Twilio VIDEO...');

use Twilio\Rest\Client;
global $wpdb;

$accountSid = ACCOUNT_SID;
$authToken = AUTHTOKEN;
$chatService = CHATSERVICESID;

$data = $_POST;

$participantConnected = isset($data['StatusCallbackEvent']) && $data['StatusCallbackEvent'] == "participant-connected" ? true : false;

if ($participantConnected) {

  $channelSid = isset($data['RoomName']) && $data['RoomName'] ? $data['RoomName'] : false;  //Channel ID
  $participantId = isset($data['ParticipantIdentity']) && $data['ParticipantIdentity'] ? $data['ParticipantIdentity'] : false;  //WP ID

  try {

    $roomMembers = array();

    $twilio = new Client($accountSid, $authToken);

    $channel = $twilio->chat->v2->services($chatService)
                                ->channels($channelSid)
                                ->fetch();

    $members = $twilio->chat->v2->services($chatService)
                                ->channels($channelSid)
                                ->members
                                ->read([], 100);

    $sender = $twilio->chat->v2->services($chatService)
                                ->users($participantId)
                                ->fetch();

    foreach ($members as $member) {
      $roomMembers[] = get_user_meta( $member->identity, 'pte_user_network_id', true );
    }

    foreach ($members as $member) {
      $notificationData = array(
        "type" => "new_av_room_participant",
        "channel_sid" => $channelSid,
        "channel_unique_name" => $channel->uniqueName,
        "channel_friendly_name" => $channel->friendlyName,
        "identity" => $member->identity,
        "sender_id" => $participantId,
        "sender_name" => $sender->friendlyName,        
        "body" => "",
        "members" => $roomMembers
      );
      wsc_send_notifications($notificationData);
    }

  } catch(Exception $e) {

    alpn_log("EXCEPTION TWILIO VIDEO CALLBACK");
    alpn_log($e);
 	}

}
//

//
// }
//
// alpn_log('DONE -- Received Callback From Twilio Chat...');

http_response_code(200);
