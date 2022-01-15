<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');

alpn_log("Handle VIT ASYNC LOG VIEWER VISIT");
$linkKey = (isset($_POST['link_key']) && strlen($_POST['link_key']) >= 20 && strlen($_POST['link_key']) <= 22) ? $_POST['link_key'] : false;

if ( $linkKey ) {
		global $wpdb;
		$viewData['link_key'] = $linkKey;
	  $viewData['v_ip'] = getUserIP();
	  $url = "http://api.ipstack.com/{$viewData['v_ip']}?access_key=e33e0bb1ae58183755ba15a0bccfa989";
	  $curl = curl_init($url);
	  curl_setopt($curl, CURLOPT_URL, $url);
	  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	  $headers = array(
	     "Accept: application/json",
	  );
	  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	  $resp = json_decode(curl_exec($curl), true);
	  curl_close($curl);

	  $viewData['v_ip'] = $resp['ip'];
	  $viewData['v_type'] = $resp['type'];
	  $viewData['v_continent_code'] = $resp['continent_code'];
	  $viewData['v_continent_name'] = $resp['continent_name'];
	  $viewData['v_country_code'] = $resp['country_code'];
	  $viewData['v_country_name'] = $resp['country_name'];
	  $viewData['v_region_code'] = $resp['region_code'];
	  $viewData['v_city'] = $resp['city'];
	  $viewData['v_zip'] = $resp['zip'];
	  $viewData['v_latitude'] = $resp['latitude'];
	  $viewData['v_longitude'] = $resp['longitude'];

	  $wpdb->insert( 'alpn_viewer_visitors', $viewData ); //new link
}
?>
