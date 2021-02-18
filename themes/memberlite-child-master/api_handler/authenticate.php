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
if(!empty($email) && !empty($password))
{
	$verify = get_user_by('email',$email );
	if(empty($verify)) {
		$response = array('success' => 0, 'message'=>'Not a valid user.');
	} 
	else {
		
		if ( $verify && wp_check_password( $password, $verify->data->user_pass, $verify->ID ) ) {
			$hash = md5('proteamedge'.$verify->data->user_login.$verify->ID.time());
			$response_data['ID'] = $verify->ID;
			$response_data['username'] = $verify->data->user_login;
			$response_data['email'] = $verify->data->user_email;
			$response_data['token'] = $hash;
			$update_option = update_option('api_request_token_'.$verify->ID.'',$hash);
			$response = array('success' => 1, 'message'=>'Success authentication successfull.','data'=>$response_data);
		} else {
			$response = array('success' => 0, 'message'=>'Please input correct password and try again');
		}
	}
}
else {
	$response = array('success' => 0, 'message'=>'Not a valid input.');
}
echo json_encode($response);
?>