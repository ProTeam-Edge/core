<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');
require_once "c1.php";
require_once "../../../widgets/form/form.php";

$root_report_url = "https://proteamedge.com/wp-content/themes/memberlite-child-master/kr/pte";
$apikey = '4b694d5c-cc64-4745-afc2-7b7f1a40790b';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="ProTeam Edge Report">
    <meta name="author" content="ProTeam Edge, Inc.">
    <meta name="keywords" content="document sharing collaboration communication network">

    <title>PTE Template</title>

    <link href="<?php echo $root_report_url; ?>/assets/fontawesome/font-awesome.min.css" rel="stylesheet">
    <link href="<?php echo $root_report_url; ?>/assets/simpleline/simple-line-icons.min.css" rel="stylesheet">
    <link href="<?php echo $root_report_url; ?>/assets/css/tomorrow.css" rel="stylesheet">

    <link href="<?php echo $root_report_url; ?>/assets/theme/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $root_report_url; ?>/assets/theme/css/main.css" rel="stylesheet">	
</head>
<body> 
    <div class="text-center">
        <a href="export.php" class="btn btn-primary">Download PDF</a>
    </div>
<?php 
	
$reportSettings = array(
	'orientation' => 'portrait',
	'page_size' => 'letter',
	'topic_id' => '11',
	'highlight_color' => '#B6DCF3',
	'header_footer_style' => '0',
	'banner_style' => '1'
);
	
$report = new c1($reportSettings);
$report->run()->render();
		
?>
</body>
</html>