<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');

$schema_master_file = WP_CONTENT_DIR . "/plugins/proteamedge/schema.jsonId";
if ($json = file_get_contents($schema_master_file)) {
    error_log("Got contents successfully.", 0);
    //echo "Got contents successfully.";
}
else {
    error_log("JSON Get Contents Failed.", 0);
    //echo "JSON Get Contents Failed.";
}
if ($graphData = json_decode($json, true)) {
    error_log("JSON Decode Successful.", 0);
    //echo "JSON Decode Successful.";
}
else {
    error_log("JSON Decode Failed.", 0);
    //echo "JSON Decode Failed.";
}

die;

?>
