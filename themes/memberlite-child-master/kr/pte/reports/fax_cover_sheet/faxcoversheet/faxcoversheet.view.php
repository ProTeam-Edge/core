<?php

$reportSettings = $this->params;
$highlightColor = $reportSettings['highlight_color'];
$topicContent = $reportSettings['topic_content'];

?>
<html>
	<head>
		<link rel="stylesheet" href="https://proteamedge.com/wp-content/themes/memberlite-child-master/kr/pte/assets/bs3/bootstrap.min.css" />
        <link rel="stylesheet" href="https://proteamedge.com/wp-content/themes/memberlite-child-master/kr/pte/assets/bs3/bootstrap-theme.min.css" />
	</head>
    <body style='margin 0; padding: 0;'>
		<?php include "/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/kr/pte/parts/header_fax_cover_sheet.php"; ?>
		<?php include "/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/kr/pte/parts/banner_fax_cover_sheet.php"; ?>
		<div style='margin: 0;'>
				<div style='font-size: 18pt; width: 100%; height: 50px; line-height: 50px; padding: 0 10px; border: solid 1px grey; font-weight: bold;'><?php echo $reportSettings["message_title"]; ?></div>
				<div style='font-size: 18pt; width: 100%; line-height: 50px; margin-top: 20px;'><?php echo $reportSettings["message_body"]; ?></div>
		</div>
		<?php include "/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/kr/pte/parts/footer_fax_cover_sheet.php"; ?>
    </body>
</html>
