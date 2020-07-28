<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

//TODO Check logged in, etc
//TODO store HTML in MySql using htmlspecialchars()

$html="";
$pVars = $_POST;
$recordId = isset($pVars['uid']) ? $pVars['uid'] : '';
$tabTypeId = isset($pVars['tab_type_id']) ? $pVars['tab_type_id'] : '';
$topicId = isset($pVars['topic_id']) ? $pVars['topic_id'] : '';
$formId = isset($pVars['form_id']) ? $pVars['form_id'] : '';
$uniqueFieldId = isset($pVars['unique_field_id']) ? $pVars['unique_field_id'] : '';
$pteUserTimezoneOffset = isset($pVars['pte_user_timezone_offset']) ? $pVars['pte_user_timezone_offset'] : '';


$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

if ($recordId) {

$results = $wpdb->get_results(
	$wpdb->prepare("SELECT t.*, tt.id AS tab_type_id, tt.form_id, tt.meta AS tab_type_meta FROM alpn_topic_items t LEFT JOIN alpn_topic_tabs tt ON t.tab_type_id = tt.id WHERE t.dom_id = '%s'", $recordId)
 );

	if (isset($results[0])) {
		$modeHtml = "<div>Edit</div>";
		$tabData = $results[0];
		$itemId = $tabData->id;
		$tabTypeId =  $tabData->tab_type_id;

		$content = json_decode($tabData->content, true);
		$tabTypeMeta = json_decode($tabData->tab_type_meta, true);

		$fieldMap = $tabTypeMeta['field_map'];
		$uniqueFieldId = $tabTypeMeta['pte.meta'];

		$meta = json_decode($content['id'], true);  //Need to writte in row id and write back
		$meta['row_id'] =  $itemId;
		$meta['tab_type_id'] =  $tabTypeId;
		$meta['pte_user_timezone_offset'] =  $pteUserTimezoneOffset;

		$formId = $tabData->form_id;

		$actualValue = '';
		foreach ($content as $key => $value) {
			if (is_array($value)) {
				foreach ($value as $key2 => $value2) {
					$wpf = "wpf{$formId}_{$fieldMap[$key]}_{$key2}";
					$_GET[$wpf] = $value2;
				}
			} else {
				$wpf = "wpf{$formId}_{$fieldMap[$key]}";
				$_GET[$wpf] = $value;
			}
		}
	} else {

	}

} else {
	$modeHtml = "<div>New</div>";
	$meta['row_id'] =  '';
	$meta['tab_type_id'] =  $tabTypeId;
	$meta['topic_id'] =  $topicId;
	$meta['pte_user_timezone_offset'] =  $pteUserTimezoneOffset;

}

$_GET["wpf{$formId}_{$uniqueFieldId}"] = json_encode($meta); //handle unique topic id


$html = $modeHtml . do_shortcode("[wpforms id='{$formId}']");

echo $html;


?>
