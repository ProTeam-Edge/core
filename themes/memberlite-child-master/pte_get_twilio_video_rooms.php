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

$channelData = array();

try {

//TODO Database how risky is this? Check my topics and any where I'm on ProTeam
$channels = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT channel_id FROM alpn_topics WHERE owner_id = %d AND channel_id <> ''
		UNION
		SELECT t.channel_id FROM alpn_proteams p LEFT JOIN alpn_topics t ON t.id = p.topic_id WHERE t.channel_id <> '' AND p.wp_id = %d" , $userId, $userId));

	$twilio = new Client($accountSid, $authToken);

	foreach ($channels as $channel) {

		$participantData = array();
		$channelId = $channel->channel_id;

		if ($channelId) {

		  $participants = $twilio->video->rooms($channelId)
       ->participants->read(array("status" => "connected"));

			 foreach ($participants as $participant) {

				 if ($participant->status == 'connected') {
					 $participantData[] = array(
						 "sid" => $participant->sid,
						 "identity" => $participant->identity,
						 "status" => $participant->status
					 );
				 }
			 }

			if (count($participantData)) {
				$channelData[$channelId] = $participantData;
			}
		}
	}

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
