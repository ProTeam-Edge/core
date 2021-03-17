<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
include_once('../pte_config.php');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); 
$root = $_SERVER['DOCUMENT_ROOT'];
global $wpdb;
$array = array();
$input = file_get_contents('php://input');
$data = json_decode($input);
$id = $data->id;
$type = $data->type;

$businessTypesList = get_custom_post_items('pte_profession', 'ASC');
$sql = "SELECT topic_content from alpn_topics where id = ".$id."";
$results = $wpdb->get_row($sql);
$array = array();
if(isset($results->topic_content) && !empty($results->topic_content))
{
	$topic_content_response = json_decode($results->topic_content);
	if(!empty($topic_content_response))
	{
		$i = 0;
			foreach($topic_content_response as $keys=>$vals) {
				if($keys!="pte_meta" && $keys!="" && !empty($vals))
				{
				$array[$i]['label']=$keys;
				if($keys=='person_hasoccupation_occupation_occupationalcategory') {
					$fval = $businessTypesList[$vals];
				}
				else
				$fval = $vals;
				$array[$i]['value']=$fval;
				$i++;
				}
		
		} 
	}
}


if(!empty($array))
$response = array('success' => 1, 'message'=>'Success data found.','data'=>$array);
else
$response = array('success' => 0, 'message'=>'No data found.','data'=>"");

echo json_encode($response); 