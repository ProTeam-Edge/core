<?php
use \koolreport\datagrid\DataTables;
use \koolreport\widgets\koolphp\Table;

require_once "../../../widgets/form/form.php";

$data = array();
if (count($this->dataStore('topic_flattened')->data())) {
	$data = $this->dataStore('topic_flattened')->data()[0];
}

$reportSettings = $this->params;
$headerFooterStyle = $reportSettings['header_footer_style'];
$bannerStyle = $reportSettings['banner_style'];

?>
<html>
	<head>
		<link rel="stylesheet" href="https://proteamedge.com/wp-content/themes/memberlite-child-master/kr/pte/assets/bs3/bootstrap.min.css" />
        <link rel="stylesheet" href="https://proteamedge.com/wp-content/themes/memberlite-child-master/kr/pte/assets/bs3/bootstrap-theme.min.css" />
	</head>
    <body style='margin 0; padding: 0;'>
		<div style='margin: 0;'>
			<?php include "../../../parts/banner_{$bannerStyle}.php"; ?>
			<div style='margin-top: 0px;'>
				<?php
					PteForm::create(array("report_settings" => $this->params, "data" => $data));
				?>
			</div>
		</div>
    </body>
</html>
