<?php 
include('/var/www/html/proteamedge/public/wp-blog-header.php');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 

//Fetching request
$input = file_get_contents('php://input');
$data = json_decode($input);
$id = '';

global $wpdb;
$id = $data->id;
if(!empty($id))
{
	$user = get_user_by( 'id', $id );
	$response = array('success' => 1, 'message'=>'User found','data'=>$user->user_login);
}
else
{
	$response = array('success' => 0, 'message'=>'No User found','data'=>'');
}
echo json_encode($response);
?>