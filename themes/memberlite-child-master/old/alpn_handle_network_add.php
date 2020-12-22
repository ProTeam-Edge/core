<?php
include('../../../wp-blog-header.php');

$siteUrl = get_site_url();

$qVars = $_GET;

$tableID = isset($qVars['tableId']) ? $qVars['tableId'] : 0;

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;


//Add lots of checks: Logged in, etc.


http://aginglifeprosd.wpengine.com.test:3000/wp-content/uploads/2020/02/edit.png

$html="";
$html .= "<div class='alpn_container_title_2'>
			<div class='alpn_container_2_left'>New</div>
			<div class='alpn_container_2_right'><i class='fa fa-plus-circle' style='margin-bottom: 5px; font-size: 1.2em; color: #4499d7;'></i>&nbsp;&nbsp;Network</div>
		  </div>";
$html .= do_shortcode("[wpforms id='$tableID']");
echo $html;

?>	