<?php

$parms = $this->params;
$headerFooterStyle = $parms['header_footer_style'];
$pteFileKey = $parms['pte_file_key'];
$htmlContent = $parms['html_content'];

?>
<html>
	<head>
		<link rel="stylesheet" href="https://proteamedge.com/wp-content/themes/memberlite-child-master/kr/pte/assets/bs3/bootstrap.min.css" />
        <link rel="stylesheet" href="https://proteamedge.com/wp-content/themes/memberlite-child-master/kr/pte/assets/bs3/bootstrap-theme.min.css" />
	</head>
    <body style='margin 0; padding: 0;'>
		<?php include "/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/kr/pte/parts/header_{$headerFooterStyle}.php"; ?>
		<div style='margin: 0;'>
			<div style='margin-top: 0px;'>
				<?php
          echo $htmlContent;
				?>
			</div>
		</div>
		<?php include "/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/kr/pte/parts/footer_{$headerFooterStyle}.php"; ?>
    </body>
</html>
