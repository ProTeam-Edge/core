<?php

use Google\Cloud\Storage\StorageClient;
use Twilio\Rest\Client;


function pte_send_message ($recipientTopicId, $details) {  //TODO Certainly could use a bit o' optmizing

	global $wpdb;
	$userInfo = wp_get_current_user();
	$userId = $userInfo->data->ID;
		
		if ($userId) {  //From User is logged in
			
			$title = $details['title'];
			$body = $details['body'];
			$sendLevel = $details['send_level'];
			
			$fromUser = $wpdb->get_results(
				$wpdb->prepare("SELECT topic_content FROM alpn_topics WHERE owner_id = '%s' AND topic_type_id = '5'", $userId) 
			);
			
			if (isset($fromUser[0])) { //Found From User Info
				
				$fromUserDetails = json_decode($fromUser[0]->topic_content, true);
				$fromUserFriendlyName = $fromUserDetails['alpn_profile_first_name'] . " " . $fromUserDetails['alpn_profile_last_name'];

				$users = $wpdb->get_results(  //Find the To User Network record belonging to the sender
					$wpdb->prepare("SELECT topic_content FROM alpn_topics WHERE id = '%s'", $recipientTopicId) 
				);

				if (isset($users[0])) { //Found the record -- pull what the sender has as the record. Try to find user by email address. If found speak to recipient as they choose.
					
					$user = $users[0];
					$userDetails = json_decode($user->topic_content, true);
					$userEmail = $userDetails['alpn_profile_primary_email'];
					$userFriendlyName = $userDetails['alpn_profile_first_name'] . " " . $userDetails['alpn_profile_last_name'];
					$defaultMethod = 'email';			

					$wpUser = get_user_by( 'email', $userEmail );
					$wpId = (isset($wpUser->ID)) ? $wpUser->ID : '';	

					if ($wpId) { //member, get message preferences, otherwise send to email.

						$members = $wpdb->get_results( //for select box
							$wpdb->prepare("SELECT topic_content, topic_meta FROM alpn_topics WHERE owner_id = '%s' AND topic_type_id = '5'", $wpId) 
						);		

						if (isset($members[0])) {
							$member = $members[0];
							$userPrefs = json_decode($member->topic_meta, true);
							$userDetails = json_decode($member->topic_content, true);
							$userFriendlyName = $userDetails['alpn_profile_first_name'] . " " . $userDetails['alpn_profile_last_name'];

							foreach ($userPrefs as $key => $value) {

								$typeKey = $value['type_key'];
								$sendGreen = $value['send_green'];
								$sendYellow = $value['send_yellow'];
								$sendRed = $value['send_red'];
								$settings = $value['settings'];
								
								if (($sendGreen && ($sendLevel == 'green')) || ($sendYellow && ($sendLevel == 'yellow')) || ($sendRed && ($sendLevel == 'red'))) {

									pp("Sending {$typeKey} when {$sendLevel}...");	
									
									$cSettings = array();
									switch ($typeKey) {
										case 'email':	
											$cSettings['to_email'] = $settings['email_address'];
											$cSettings['to_name'] = $userFriendlyName;
											$cSettings['from_email'] = $userInfo->data->user_email;
											$cSettings['from_name'] = $fromUserFriendlyName;
											$cSettings['title'] = $title;
											$cSettings['body'] = $body;
											
											//log event
											//call email.
											
										break;
									}
								}
							}

						}
					}
				}
		}
	}
}



function pte_handle_fax(){
	

	$ownerId = '2';
	$vaultId = '1400';
	$outboundFaxNumber = '+16507290910';
	$pteFaxNumber = '+114084191490';


	if ($vaultId && $ownerId) {

		$sid = 'ACa3cfb8ff4e9f2b263e37a00f35c3e1ae';
		$token = 'e74ba46c3b14d739c731429877b37fc3';
		$client = new Client($sid, $token);

		$templateDirectory = get_template_directory_uri();
		$fileUri = "{$templateDirectory}-child-master/alpn_get_vault_file.php?which_file=pdf&v_id={$vaultId}";	

		pp($fileUri);

		$fax = $client->fax->v1->faxes
			->create($outboundFaxNumber,
				$fileUri,
				array("from" => $pteFaxNumber)
	   );	

		pp($fax->sid);
		pp($fax);

		pp ("Fax complete...");

	}

}



?>