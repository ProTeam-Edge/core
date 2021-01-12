<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$siteUrl = get_site_url();
$userId = get_current_user_id();

//TODO check logged in. query


if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die();
}
if(!check_ajax_referer('alpn_script', 'security',FALSE)) {
   echo 'Not a valid request.';
   die();
}


$qVars = $_POST;
$contacts = isset($qVars['contacts']) ? $qVars['contacts'] : '';

//TODO LET DB do the JSON work. There may be a bug associated with our schema names which use dots? Or perhaps malformed JSON_VALUE, JSON_EXTRACT...

$existingContacts = $wpdb->get_results(
	$wpdb_readonly->prepare("SELECT topic_content from alpn_topics WHERE owner_id = '%s' AND topic_type_id = '4'", $userId)
 );

$existingContactEmails = array();
if (isset($existingContacts[0])) {
	foreach ($existingContacts as $key => $value){
		 $details = json_decode($value->topic_content, true);
		 $existingContactEmails[] = $details['person_email'];
	}
}

$sql = "
	INSERT INTO
		alpn_topics (owner_id, topic_type_id, name, about, last_op, created_date, modified_date, topic_content )
  VALUES
";

$now = date ("Y-m-d H:i:s", time());

$newContacts = array();
$dupeContactEmails = array();
foreach ($contacts as $key => $value) {

	if (count($value['email'])) {

		$newEmail = $value['__selectedMail__'];

		$first = isset($value['first_name']) ? $value['first_name'] : "";
		$last = isset($value['last_name']) ? $value['last_name'] : "";

		if ( in_array ( $newEmail, $existingContactEmails) || !$first || !$last) {

			$dupeContactEmails[] = $newEmail;


		} else {

			$title = isset($value['title']) ? $value['title'] : "";

			$phone = "";
			if (isset($value['phone'])) {
				$phoneAll = $value['phone'][0];
				$phone = isset($phoneAll['number']) ? $phoneAll['number'] : "";
			}

			if (isset($value['address'])) {
				$addressAll = $value['address'][0];
				$street = isset($addressAll['street']) ? $addressAll['street'] : "";
				$city = isset($addressAll['city']) ? $addressAll['city'] : "";
				$postalCode = isset($addressAll['postal_code']) ? $addressAll['postal_code'] : "";
				$state = isset($addressAll['region']) ? $addressAll['region'] : "";
			}

			$company = "";
			if (isset($value['companies'])) {
				$company = isset($value['companies'][0]) ? $value['companies'][0] : "";
			}

			$name = $last . ", " . $first;
			$about = $company ? $company : $title;
			$about = $about ? $about : $newEmail;

			$topicContent = array(
				"person_email" => $newEmail,
				"person_jobtitle" => $title,
				"person_givenname" => $first,
				"person_familyname" => $last,
				"person_telephone" => $phone
			);
			$existingContactEmails[] = $newEmail;
			$newContacts[] = $topicContent;
			$topicContent = json_encode($topicContent);

			$line = ' (';
			$line .= '"' . $userId . '", ';
			$line .= '"' . '4' . '", ';
			$line .= '"' . $name . '", ';
			$line .= '"' . $about . '", ';
			$line .= '"' . 'add' . '", ';
			$line .= '"' . $now . '", ';
			$line .= '"' . $now . '", ';
			$line .=  "'" . $topicContent . "'";
			$line .=   '),';

			$sql .= $line;

		}
	}
}

if (count($newContacts)) {
	$sql = rtrim($sql, ",");
	$wpdb->query($sql);
}

//// TODO:
/*

$topicData['contact_topic_id'] = $row_id;
$topicData['contact_email'] = $mappedFields['patient.telecom.1.value'];
pte_manage_user_connection($topicData);

*/




header('Content-Type: application/json');
echo json_encode(array("lq" => $wpdb->last_query, "le" => $wpdb->last_error, "dupes" => $dupeContactEmails, "new" => $newContacts, "provided" => $contacts));

?>
