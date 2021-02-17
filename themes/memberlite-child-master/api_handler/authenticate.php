<?php 
include('/var/www/html/proteamedge/public/wp-blog-header.php');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 

//Fetching request
$input = file_get_contents('php://input');
$data = json_decode($input);

//Wordpress authentication
global $wpdb;
$email = $data->email;
$password = $data->password_field;
$verify = get_user_by('email',$email );
if(empty($verify)) {
	$response = array('success' => 0, 'message'=>'Not a valid user.');
}
else {
	echo '<pre>';
	print_r($verify);
}
echo $response;
?>