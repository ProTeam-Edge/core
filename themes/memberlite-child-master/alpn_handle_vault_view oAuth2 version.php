<?php
include('../../../wp-blog-header.php');
require_once 'google/vendor/autoload.php';


//TODO Check logged in, etc

$siteUrl = get_site_url();

$qVars = $_GET;
$dom_id = isset($qVars['dom_id']) ? $qVars['dom_id'] : '';
$form_id = isset($qVars['form_id']) ? $qVars['form_id'] : '';

$userInfo = wp_get_current_user();
$userID = $userInfo->data->ID;

$results = $wpdb->get_results(
	$wpdb->prepare("SELECT * FROM alpn_vault WHERE dom_id = %s", $dom_id) 
 );

if (array_key_exists('0', $results)) {
	$formName = $results[0]->title;
} else {
	$formName = "";
}

function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('ProTeam Edge');
    $client->setScopes(Google_Service_Drive::DRIVE);
    $client->setAuthConfig('client_id.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');	
	
	$redirect_uri = 'http://aginglifeprosd.wpengine.com/wp-content/themes/memberlite-child-master/google/alpn_oAuth2_callback.php';
	$client->setRedirectUri($redirect_uri);
	

    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.
    $tokenPath = 'token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

	return $client;
}

$myClient = getClient();

pp($myClient);

$service = new Google_Service_Drive($myClient);

$files = $service->files->listFiles();

pp($files);


?>