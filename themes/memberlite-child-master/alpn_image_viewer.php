<?php

$qVars = $_GET;
$file = isset($qVars['file']) ? $qVars['file'] : '';
	
$html ="
<!DOCTYPE html>
<html>
<head>
<style>
#alpn_image_viewer{
    width:100%; height: 100%;
    position:relative;
}

#alpn_image_viewer img {
    width: 100% !important;
    height: 100% !important;
}

</style>
<script type='text/javascript' src='./panzoom-master/dist/panzoom.js'></script>
</head>
<body>
<div id='alpn_image_viewer'>
<img src='{$file}' onload='alpn_pagerendered();'></img>
</div>
<script>
var area = document.querySelector('#alpn_image_viewer')
panzoom(area, {
  // now all zoom operations will happen based on the center of the screen
  transformOrigin: {x: 0.5, y: 0.5}
});

function alpn_pagerendered(){
	parent.postMessage({proteamedge: '', message_type: 'pagerendered'},'*');  //  `*` on any domain         
}
</script>
</body>
</html>
";
echo $html;
?>