<?php

$parms = $this->params;
$headerFooterStyle = $parms['header_footer_style'];
$pteFileKey = $parms['pte_file_key'];
$htmlContent = $parms['html_content'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Wiscle PDF</title>
<style>
    @page {
        size: letter;
        margin: 0.75in 0.5in;

        @top-left {
          vertical-align: middle;
        }

				@top-center {
          vertical-align: middle;
					content: 'Wiscle Archive';
        }
				@top-right {
          vertical-align: middle;
        }
				@bottom-left {
          vertical-align: middle;
        }
				@bottom-center {
					vertical-align: middle;
					content: 'Page ' counter(page) ' of ' counter(pages);					
				}
				@bottom-right {
					vertical-align: middle;
				}
    }

    html {
        font-size: 12pt;
				border-collapse: collapse;
    }

		@font-face {
			font-family: 'Lato';
			font-style: normal;
			font-weight: 400;
			src: local('Lato Regular'), local('Lato-Regular'), url(https://fonts.gstatic.com/s/lato/v16/S6uyw4BMUTPHjxAwXiWtFCfQ7A.woff2) format('woff2');
			unicode-range: U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
		}
		/* latin */
		@font-face {
			font-family: 'Lato';
			font-style: normal;
			font-weight: 400;
			src: local('Lato Regular'), local('Lato-Regular'), url(https://fonts.gstatic.com/s/lato/v16/S6uyw4BMUTPHjx4wXiWtFCc.woff2) format('woff2');
			unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
		}
		/* latin-ext */
		@font-face {
			font-family: 'Lato';
			font-style: normal;
			font-weight: 700;
			src: local('Lato Bold'), local('Lato-Bold'), url(https://fonts.gstatic.com/s/lato/v16/S6u9w4BMUTPHh6UVSwaPGQ3q5d0N7w.woff2) format('woff2');
			unicode-range: U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
		}
		/* latin */
		@font-face {
			font-family: 'Lato';
			font-style: normal;
			font-weight: 700;
			src: local('Lato Bold'), local('Lato-Bold'), url(https://fonts.gstatic.com/s/lato/v16/S6u9w4BMUTPHh6UVSwiPGQ3q5d0.woff2) format('woff2');
			unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
		}

		.pte_pdf_directory_container{
			margin-left: 40px;
			margin-top: 10px;
		}

		.pte_pdf_directory_image_cell{
			width: 35px;
		}

		.pte_pdf_directory_text_cell{
			font-weight: bold;
		}


		.pte_pdf_file{
			margin-left: 42px;
		}

		body{
			color: #444;
			font-size: 14pt;
			font-family: 'Lato', sans-serif;
			font-weight: 400;
		}

</style>
</head>
<body>

	<?php
		echo $htmlContent;
	?>

</body>
</html>
