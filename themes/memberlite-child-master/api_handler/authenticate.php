<?php 
include('/var/www/html/proteamedge/public/wp-blog-header.php');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 

//Fetching request
$input = file_get_contents('php://input');
$data = json_decode($input);

//Wordpress authentication
global $wpdb;
$response_data = array();
$email = $data->email;
$password = $data->password_field;
$device_token = $data->device_token;
if(!empty($email) && !empty($password))
{
	$verify = get_user_by('email',$email );
	if(empty($verify)) {
		$response = array('success' => 0, 'message'=>'No account found with this email.');
	} 
	else {
		
		if ( $verify && wp_check_password( $password, $verify->data->user_pass, $verify->ID ) ) {
			$hash = md5('proteamedge'.$verify->data->user_login.$verify->ID.time());
			$sql = "SELECT * from alpn_topics where owner_id = ".$verify->ID." and special = 'user' and sync_id !=''";
			$get_alpn_result = $wpdb->get_row($sql);
			$response_data['ID'] = strval($verify->ID);
			$response_data['username'] = $verify->data->user_login;
			$response_data['email'] = $verify->data->user_email;
			$response_data['alpn_id'] = $get_alpn_result->id;
			$response_data['token'] = $hash;
			$update_option = update_option('api_request_token_'.$verify->ID.'',$hash);
			$update_sql= "update wp_users set device_token ='".$device_token."'  where ID =".$verify->ID." ";
			$update_data = $wpdb->query($update_sql);
			$response = array('success' => 1, 'message'=>'Login Success! Redirecting..','data'=>$response_data);
		} else {
			$response = array('success' => 0, 'message'=>'Your email and password do not match.');
		}
	}
}
else {
	$response = array('success' => 0, 'message'=>'Error missing required parameters.');
}

echo json_encode($response);
?>