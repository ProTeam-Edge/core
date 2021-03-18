<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

alpn_log('Received Callback From Twilio Video...');

use Twilio\Rest\Client;
global $wpdb;

$accountSid = ACCOUNT_SID;
$authToken = AUTHTOKEN;
$chatService = CHATSERVICESID;

$userRooms = array();
$channelData = array();
$channelParticipants = array();
$allIdentities = array();
$channelsInvolved = array();

$data = $_POST;

//alpn_log($data);

$statusCallbackEvent = $data['StatusCallbackEvent'];

switch ($statusCallbackEvent) {

	case 'participant-connected':
	case 'participant-disconnected':

	$roomName = $data['RoomName'];
	$participantIdentity = $data['ParticipantIdentity'];
	$participantStatus = $data['ParticipantStatus'];

	if ($roomName) {

		$twilio = new Client($accountSid, $authToken);

		$channels = $wpdb->get_results(
			$wpdb->prepare("SELECT t.id, t.channel_id, t.name, t.image_handle, t.owner_id, t.special, t.connected_id, t2.image_handle AS connected_image_handle, t2.name AS connected_name FROM alpn_topics t LEFT JOIN alpn_topics t2 ON t2.owner_id = t.connected_id AND t2.special = 'user' WHERE t.channel_id = %s", $roomName)
		 );

//with p2p video, only we know who is interested in an audio channel. Chan't ask Twilio. Only for connected, etc.
		$effected = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT t.id, t.channel_id, t.name, t.image_handle, t.owner_id, t.special, t.connected_id, t2.image_handle AS connected_image_handle, t2.name AS connected_name FROM alpn_topics t LEFT JOIN alpn_topics t2 ON t2.owner_id = t.connected_id AND t2.special = 'user' WHERE t.channel_id = %s
				UNION
				SELECT  t.id, t.channel_id, t.name, t.image_handle, p.wp_id AS owner_id, t.special, '' AS connected_id, '' AS connected_image_handle, '' AS connected_name FROM alpn_proteams p LEFT JOIN alpn_topics t ON t.id = p.topic_id WHERE t.channel_id = %s" , $roomName, $roomName));

		if (isset($channels[0])) {

			foreach ($channels as $channel) {

				$user = $twilio->chat->v2->services($chatService)
				                         ->users($channel->owner_id)
				                         ->fetch();

					$channelsInvolved[] = array(
						"topic_id" => $channel->id,
						"name" => $channel->name,
						"image_handle" => $channel->image_handle,
						"owner_id" => $channel->owner_id,
						"owner_friendly_name" => $user->friendlyName,
						"connected_id" => $channel->connected_id,
						"connected_name" => $channel->connected_name,
						"connected_image_handle" => $channel->connected_image_handle
					);
				}

			 $participants = $twilio->video->rooms($roomName)
					 ->participants->read(array("status" => "connected"));

				 foreach ($participants as $participant) {
		 				$channelParticipants[] = array(
		 					"sid" => $participant->sid,
		 					"identity" => $participant->identity,
							"status" => $participant->status
		 				);
					}
					$channelData[$roomName] = array(
					 "participants" => $channelParticipants,
					 "channels_involved" => $channelsInvolved,
					 "event_details" => $data
				 );
					foreach ($effected as $effectedUser) { //notify all
						$data = array(
							"sync_type" => "add_update_section",
							"sync_section" => "programmable_video_room_update",
							"sync_user_id" => $effectedUser->owner_id,
							"sync_payload" => $channelData
						);
						pte_manage_user_sync($data);
					}
			}
	}
	break;


}
alpn_log('DONE Received Callback From Twilio Video...');

?>
