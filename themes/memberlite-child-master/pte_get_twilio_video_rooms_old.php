<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die;
}

global $wpdb;
$data = $_POST;
$userInfo = wp_get_current_user();
$userId = $userInfo->data->ID;

alpn_log("Getting Twilio Video Rooms...{$userId}");
use Twilio\Rest\Client;

$accountSid = ACCOUNT_SID;
$authToken = AUTHTOKEN;
$chatService = CHATSERVICESID;

$userRooms = array();
$channelData = array();
$channelParticipants = array();

try {

//TODO Database how risky is this? Check my topics and any where I'm on ProTeam
$channels = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT t.id, t.channel_id, t.name, t.image_handle, t.owner_id, t.special, t.connected_id, t2.image_handle AS connected_image_handle, t2.name AS connected_name FROM alpn_topics t LEFT JOIN alpn_topics t2 ON t2.owner_id = t.connected_id AND t2.special = 'user' WHERE t.owner_id = %d AND t.channel_id <> ''
		UNION
		SELECT t.id, t.channel_id, t.name, t.image_handle, t.owner_id, t.special, '' AS connected_id, '' AS connected_image_handle, '' AS connected_name FROM alpn_proteams p LEFT JOIN alpn_topics t ON t.id = p.topic_id WHERE t.channel_id <> '' AND p.wp_id = %d" , $userId, $userId));

	$twilio = new Client($accountSid, $authToken);

	foreach ($channels as $channel) {

		$channelParticipants = array();

		$channelId = $channel->channel_id;

		if ($channelId) {  //TODO why empty record.

		  $participants = $twilio->video->rooms($channelId)
       ->participants->read(array("status" => "connected"));

		  foreach ($participants as $participant) {
	 				$channelParticipants[] = array(
	 					"sid" => $participant->sid,
						"identity" => $participant->identity,
	 					"status" => $participant->status
	 				);
			}

			if (count($channelParticipants)) {

				$user = $twilio->chat->v2->services($chatService)
																 ->users($channel->owner_id)
																 ->fetch();

				$channelsInvolved = array();

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

				if ($channel->connected_id) {
					$contactChannel = $wpdb->get_results(
						$wpdb->prepare("SELECT t.id, t.channel_id, t.name, t.image_handle, t.owner_id, t.special, t.connected_id, t2.image_handle AS connected_image_handle, t2.name AS connected_name FROM alpn_topics t LEFT JOIN alpn_topics t2 ON t2.owner_id = t.connected_id AND t2.special = 'user' WHERE t.owner_id= %d and t.special = 'user'", $channel->connected_id)
					 );
					 $channelsInvolved[] = array(
	 					"topic_id" => $contactChannel->id,
	 					"name" => $contactChannel->name,
	 					"image_handle" => $contactChannel->image_handle,
	 					"owner_id" => $contactChannel->owner_id,
	 					"connected_id" => $contactChannel->connected_id,
	 					"connected_name" => $contactChannel->connected_name,
	 					"connected_image_handle" => $contactChannel->connected_image_handle
	 				);
				}

				$channelData[$channelId] = array(
				 "participants" => $channelParticipants,
				 "channels_involved" => $channelsInvolved
				);
			}
		}
	}
	//alpn_log($channelData);

	pte_json_out(array("channel_data" => $channelData));

} catch (Exception $e) {
		$response = array(
				'message' =>  $e->getMessage(),
				'code' => $e->getCode(),
				'error' => $e
		);
		alpn_log("get video EXCEPTION...");
		alpn_log($response);
		return;
}

alpn_log('DONE Getting Twilio Video Rooms...');

?>
