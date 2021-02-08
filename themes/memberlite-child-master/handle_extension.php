<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');



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
			var url = "<?php echo $site_url ?>/wp-content/themes/memberlite-child-master/handle_extension_ajax.php";
		

			  $.ajax({
            url: url,
            type: "POST",
            data: {},
            dataType: "json",
            complete: function(){
             // alert('Saving complete.');
			  
            }
          });
        });
		</script>
	</head>

	<body>
		<div id = "root" >
			<div class='pte_dialog_title'>ProTeam Edge File Uploader</div>
			
		</div>
		<script src = "main.js" ></script>
	</body>

</html>
