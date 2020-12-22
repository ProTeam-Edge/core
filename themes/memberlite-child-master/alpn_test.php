<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

//TODO Check logged in, etc
//TODO store HTML in MySql using htmlspecialchars()

$html = $faxUx = $proTeamHtml = $networkOptions = $topicOptions = $importantNetworkItems = $importantTopicItems = $interactionTypeSliders = "YODUDE";
$qVars = $_POST;

//pp($qVars);
//pp(json_decode(pte_get_documo_test()));

$recordId = isset($qVars['uniqueRecId']) ? $qVars['uniqueRecId'] : 0;
$pteUserTimezoneOffset = isset($qVars['pte_user_timezone_offset']) ? $qVars['pte_user_timezone_offset'] : '';
$ppCdnBase = "https://storage.googleapis.com/pte_media_store_1/";


//pp($wpdb);

echo $html;

?>
