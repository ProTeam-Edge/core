<?php

include "../../../parts/page_data.php";
$heightAdjust = 1.35;   //TODO will this work? Inches do not see to be predictable.
$rowHeight = 0.75;

$reportToken =  $report_settings['page_size'] . "_" . $report_settings['orientation']; 
$pageHeight = $pageData["{$reportToken}_height"] * $heightAdjust;

$topicTypeMeta = isset($data['topic_type_meta']) ? json_decode($data['topic_type_meta'], true) : '';
$nameMap = isset($topicTypeMeta['name_map']) ? $topicTypeMeta['name_map'] : '';
$topicContent = isset($data['topic_content']) ? json_decode($data['topic_content'], true) : '';

$orderedFields = array();
foreach($nameMap as $key => $value){
	$orderedFields[$value['field_order']] = 
		array(
			'field_name' => $value['field_name'],
			'field_value' => $topicContent[$key]
		);
}
ksort($orderedFields, SORT_NUMERIC);

$html = "";
$height = $pageData['top_margin'] + $pageData['bottom_margin'] + $pageData['header_height']  + $pageData['footer_height'] + $pageData['banner_height'] + $pageData['extra_page_height'];

foreach($orderedFields as $key => $value){

	$html .= "<div class='row' style='margin-left: 0px; margin-right: 0px; border-top: 1px solid grey; border-left: 1px solid grey; border-right: 1px solid grey; border-bottom: 0px; height: {$rowHeight}in; font-size: 24px;'>";
	$html .= 
			"<div class='col-md-6'>
				{$value['field_name']}
			</div>

			<div class='col-md-6'>
				
			</div>		
			";

	$html .= "</div>"; 
	
	//if (($height + $rowHeight) > $pageHeight$lineCount && ($count % $linesPerPage == 0)){
	//pp($height);
	if (($height + $rowHeight) > $pageHeight){
		$html .= "<div class='row'  style='border-top: 1px solid grey;'></div>";
		$html .= "<div class='page-break'></div>";
		$height = $pageData['top_margin'] + $pageData['bottom_margin'] + $pageData['header_height']  + $pageData['footer_height'] + $pageData['extra_page_height'];
	} else {
		$height += $rowHeight;
	}
}

$html .= "<div class='row'  style='border-top: 1px solid grey;'></div>";

echo $html;

?>