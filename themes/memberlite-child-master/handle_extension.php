<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');

$nonce = wp_create_nonce( 'handle_extension');

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title></title>
		<script data-require="jquery@*" data-semver="3.0.0" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0/jquery.js"></script>
		<style>
			@import url('https://fonts.googleapis.com/css2?family=Lato&display=swap');
			html {
				font-family: "Lato", sans-serif;
			}
			.pte_dialog_title {
				font-size: 14px;
				font-weight: bold;
			}
		</style>
		<script>
		function bindEvent(element, eventName, eventHandler) {
            if (element.addEventListener){
                element.addEventListener(eventName, eventHandler, false);
            } else if (element.attachEvent) {
                element.attachEvent('on' + eventName, eventHandler);
            }
        }
		
         

        // Listen to messages from parent window
        bindEvent(window, 'message', function (e) {
			console.log('e.data.data.blob');
			console.log(e.data.data.blob);
			 var data = new FormData();
			data.append('file', e.data.data.blob);
			data.append('name', e.data.data.file_name);
			data.append('security',"<?php echo $nonce ?>");

			var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/handle_extension_ajax.php";
			$("#results").html('<h3>Loading Please Wait</h3>')

			  $.ajax({
            url: url,
            type: "POST",
           data: data,
		   contentType: false,
			processData: false,
            success: function(data){
             $('#results').html(data)
			  
            }
          });
        });
		</script>
	</head>

	<body>
		<div id = "root" >
			<div class='pte_dialog_title'>ProTeam Edge File Uploader</div>
			<br/>
			<div id="results"></div>
		</div>
		<script src = "main.js" ></script>
	</body>

</html>
