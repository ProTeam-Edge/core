<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');



?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title></title>
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
		var results = document.getElementById('results');
         

        // Listen to messages from parent window
        bindEvent(window, 'message', function (e) {
            results.innerHTML = e.data;
        });
		</script>
	</head>

	<body>
		<div id = "root" >
			<div class='pte_dialog_title'>ProTeam Edge File Uploader</div>
			<h3>Result</h3>
			<div id="results"></div>
		</div>
		<script src = "main.js" ></script>
	</body>

</html>
