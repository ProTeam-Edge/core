<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');
require_once "c1.php";

$reportSettings = array(
	'orientation' => 'portrait',
	'page_size' => 'letter',
	'topic_id' => '11',
	'highlight_color' => '#B6DCF3',
	'header_footer_style' => '0',
	'banner_style' => '1'
);
	
$report = new c1($reportSettings);

$report->run()
->export('c1Pdf')
->pdf(array(
    "format"=>$reportSettings['page_size'],
    "orientation"=>$reportSettings['orientation'],
	"margin"=>array(
        "top"=>"0.25in",
        "bottom"=>"0.25in",
        "left"=>"0.5in",
        "right"=>"0.5in"
    )
))
->toBrowser("c1.pdf");