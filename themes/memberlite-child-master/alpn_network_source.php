<?php
include('../../../wp-blog-header.php');

header("HTTP/1.1 200 OK"); // Forcing the 200 OK header as WP can return 404 otherwise


$sql = "SELECT u1.meta_value first_name, u2.meta_value last_name, u3.meta_value business_name
FROM alpn_user_network s 
INNER JOIN wp_usermeta u1 ON s.b_id = u1.user_id AND u1.meta_key = 'first_name' 
INNER JOIN wp_usermeta u2 ON s.b_id = u2.user_id AND u2.meta_key = 'last_name' 
INNER JOIN wp_usermeta u3 ON s.b_id = u3.user_id AND u3.meta_key = 'alpn_business_name' 
WHERE s.a_id = " . get_current_user_id();


$return_array = array();
$network_users = $wpdb->get_results($sql);


foreach ($network_users as $key => $value) {
	
	$return_array[] = array(
		'first_name' => $value->first_name,
		'last_name' => $value->last_name,
		'business_name' => $value->business_name,
		'something_else' => "<div>" . $value->business_name . "</div>"
	);
}

echo serialize( $return_array );
?>