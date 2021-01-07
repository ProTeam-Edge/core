<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$results = array();
$qVars = $_POST;


$verify = 0;
if(isset($qVars['security']) && !empty($qVars['security']))
	$verify = wp_verify_nonce( $qVars['security'], 'alpn_script' );
if($verify==1) {

$formId = isset($qVars['form_id']) ? $qVars['form_id'] : '';
$topicTypeObject = isset($qVars['topic_type_object']) ? $qVars['topic_type_object'] : "";

function cmp($a, $b) {
    if ($a['new_position'] == $b['new_position']) {
        return 0;
    }
    return ($a['new_position'] < $b['new_position']) ? -1 : 1;
}

$topicTypeArray = json_decode(stripslashes($topicTypeObject), true);
$topicClass = isset($topicTypeArray['topic_class']) && $topicTypeArray['topic_class'] ? $topicTypeArray['topic_class'] : "topic";

//Fix Topic Order. TODO Javascript wants to sort the keys but order means something here.
$newFieldMap = array();
$fieldMap = $topicTypeArray["field_map"];
usort($fieldMap, "cmp");
foreach ($fieldMap as $key => $value) {   //uSort loses keys so replacing them. TODO there is certianly easier. Please fix it. J
	$newFieldMap[$value['name']] = $value;
}
$topicTypeArray["field_map"] = $newFieldMap;

$userInfo = wp_get_current_user();
$ownerId = $userInfo->data->ID;
$ownerNetworkId = get_user_meta( $ownerId, 'pte_user_network_id', true );

if ($formId && $topicTypeObject && $ownerId) {

  $topicTypeArrayEnc = json_encode($topicTypeArray);
  $form = pte_sync_curl("generateForm", $topicTypeArrayEnc);
  $html = pte_sync_curl("generateHTML", $topicTypeArrayEnc);

//Update corresponding wpForm
  $newForm = json_decode($form, true);
  $newForm['id'] = $formId;
  $newForm["fields"]['pte_meta'] = array(
    "id" => "0",
    "type" => "hidden",
    "label" => "ID",
    "label_disable" => "1",
    "default_value" => "",
    "css" => ""
  );

  $newFormData = array(
    "post_content" => json_encode($newForm)
  );
  $formWhereClause = array(
    "ID" => $formId
  );
	$wpdb->update( 'wp_posts', $newFormData, $formWhereClause );

	$metaData = array(
    "topic_class" => $topicClass,
    "html_template" => $html,
		"topic_type_meta" => json_encode($topicTypeArray),
		"name" => $topicTypeArray['topic_friendly_name'],
		"description" => isset($topicTypeArray['topic_description']) ? $topicTypeArray['topic_description'] : ""
	);
	$whereClause['form_id'] = $formId;
	$whereClause['owner_id'] = $ownerId;
	$wpdb->update( 'alpn_topic_types', $metaData, $whereClause );

}

pte_json_out(array("status" => "ok", "ttarray" => $topicTypeArray));
}else
{
	echo $html = 'Not a valid request.';
	die;
}
?>
