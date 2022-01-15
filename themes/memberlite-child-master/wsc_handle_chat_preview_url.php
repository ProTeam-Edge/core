<?php
include('../../../wp-blog-header.php');

use Ramsey\Uuid\Uuid;
use transloadit\Transloadit;

alpn_log("Handle Chat Preview");

global $wpdb;

//TODO Check logged in, etc. Good Request. User-ID in all mysql
if(!is_user_logged_in() ) {
	echo 'Not a valid request.';
	die;
}

if(!check_ajax_referer('alpn_script', 'security', FALSE)) {
   echo 'Not a valid request.';
   die;
}

$qVars = $_POST;

$siteUrl = isset($qVars['site_url']) ? $qVars['site_url'] : false;
$fileId = isset($qVars['file_id']) ? $qVars['file_id'] : false;

$imageUrl = $pageTitle = $pageDescription = "";
$fileName = "";

if ($siteUrl && $fileId) {

	//unsafe fail.
	if (strtolower(substr($siteUrl, 0, )) == "http://") {
		return false;
		pte_json_out(array(
			"error" => "Data missing",
			"data" => $qVars
		));
		exit;
	}

	if (strtolower(substr($siteUrl, 0, 8)) != "https://") {
		$siteUrl = "https://" . $siteUrl;
	}

	$webPage = get_web_page($siteUrl);    //prev file_get_contents. Many sites not finding final meta fields. Unknown why
	$dom = new DOMDocument;
	$dom->loadHTML($webPage['content']);

	foreach ($dom->getElementsByTagName('meta') as $tag) {
	    if (!$imageUrl && $tag->getAttribute('property') === 'og:image') {
				$imageUrl = $tag->getAttribute('content');
	    }
			if (!$pageTitle && $tag->getAttribute('property') === 'og:title') {
				$pageTitle = $tag->getAttribute('content');
	    }
			if (!$pageDescription && $tag->getAttribute('property') === 'og:description') {
				$pageDescription = $tag->getAttribute('content');
	    }
			if (!$pageDescription && $tag->getAttribute('name') === 'description') {
				$pageDescription = $tag->getAttribute('content');
	    }
	}

	foreach ($dom->getElementsByTagName('title') as $tag) {
			if (!$pageTitle && $tag->textContent) {
				$pageTitle = $tag->textContent;
				break;
			}
	}

	if (substr($imageUrl, 0, 1) == "/") {    //Google?
		$imageUrl = $siteUrl . $imageUrl;
	}

	$fileParts = pathinfo($imageUrl);
	$fileParts['extension'] = $fileParts['extension'] ? $fileParts['extension'] : "jpeg";
	$hasVars = strpos($fileParts['extension'], "?");
	$cleansedExt = $hasVars ? "." . substr($fileParts['extension'], 0, strpos($fileParts['extension'], "?")) : "." . $fileParts['extension'];
	$fileName = $fileId . $cleansedExt;
	$tempFile = PTE_ROOT_PATH . "tmp/" . $fileName;
	$fileContents = file_get_contents($imageUrl);
	if ($fileContents) {
		file_put_contents($tempFile, $fileContents);
	} else {
		$imageUrl = "";
	}
}

pte_json_out(array(
	"file_name" => $fileName,
	"title" => $pageTitle,
	"description" => $pageDescription,
	"site_url" => $siteUrl,
	"image_url" => $imageUrl,
	"parts" => $fileParts
));

function get_web_page( $url ) {
    $res = array();
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => true,    // do not return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
    );
    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $res['content'] = $content;
    $res['url'] = $header['url'];
    return $res;
}

?>
