<?php
include('/var/www/html/proteamedge/public/wp-blog-header.php');

$html = "";
$html .= "
      <style>
        #pte_topic_part_container {
          font-family: 'Lato', sans-serif;
          display: flex;
          flex-wrap: wrap;
        }
        #pte_topic_part_row_100 {
          flex-grow: 1;
        	flex-basis: 100%;
        }
        .pte_topic_part_textarea {
          padding: 5px 10px;
          font-size: 12px;
          line-height: 18px;
        	border: solid 1px rgb(204, 204, 204);
        	height: 200px;
          width: 100%;
        	color: #444;
        }
        .pte_topic_part_title{
          font-size: 14px;
          line-height: 20px;
          font-weight: bold;
          margin-bottom: 5px;
      }
        #pte_topic_part_text_buttons {
          padding: 10px 0;
        }
        .pte_link_button {
          margin: 0px 10px;
          color: rgb(0, 116, 187);
          cursor: pointer;
        }
      </style>
      <script>
        function pte_get_form(){
          var fieldContents = jQuery('#pte_topic_part_text_input');
          console.log(fieldContents);


        }


      </script>
";


$html .= "
      <div id='pte_topic_part_container'>
        <div class='pte_topic_part_title'>Topic JSON</div>
        <textarea id='pte_topic_part_text_input' class='pte_topic_part_textarea'></textarea>
        <div id='pte_topic_part_text_buttons'>
          <a class='pte_link_button' onclick='pte_get_form();'>Get Form</a>
          <a class='pte_link_button' onclick='pte_get_html();'>Get HTML</a>
        </div>
        <textarea id='pte_topic_part_text_output' class='pte_topic_part_textarea' readonly></textarea>
      </div>
";


echo $html;

?>
