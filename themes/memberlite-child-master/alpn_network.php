527728226


curl -X POST -d "username=Vermont" -d "password=JmV13131!" "https://hipaa-api.jotform.com/user/login"



https://aginglifeprosd.wpengine.com/wp-content/themes/memberlite-child-master/alpn_handle_vault_form_submit.php






808816623627.apps.googleusercontent.com	
	
	Google Service Account Client ID: 117315790018162369667


JotForm: @6ar#Y./f^
APIkey: 10d92b301d484018f296123d32229b96

JotForm: JmV13131$

Hipaa Vault: 8&UPTED92uj

ben@cometchat.com

	
	-H "APIKEY: {myApiKey}"
	
	
curl -X POST -H "APIKEY: 10d92b301d484018f296123d32229b96" -d "username=Vermont" -d "password=JmV13131$" "https://hipaa-api.jotform.com/user/login"
	
	


curl https://hipaa.jotform.com/server.php?action=getSubmissionPDF&sid=4573402365321042474&direct=1
curl https://hipaa.jotform.com/pdf-submission/4573402365321042474

curl -X POST -d "username=Vermont" -d "password=JmV13131$" "https://hipaa-api.jotform.com/user/login"


$html .= print_r($wpdb->last_query, true);
$html .= print_r($wpdb->last_error, true);


VAULT ITEMS

1) My Vault Items (for this topic?)
2)  topic linked

SELECT v.id, v.owner_id, v.access_level, v.created_date, v.modified_date, v.name, v.description, v.file_name, v.submission_id, v.doc_type, v.form_id, v.dom_id, v.draw_id, v.topic_id 
FROM alpn_vault v
WHERE owner_id = %CURRENT_USER_ID%

UNION

SELECT v.id, v.owner_id, v.access_level, v.created_date, v.modified_date, v.name, v.description, v.file_name, v.submission_id, v.doc_type, v.form_id, v.dom_id, v.draw_id, v.topic_id 
FROM alpn_vault v
JOIN alpn_proteams p
ON v.topic_id = p.connected_topic_id
WHERE p.proteam_member_id = %CURRENT_USER_ID% AND p.connected_topic_id > 0 AND v.access_level <= p.access_level

ORDER BY modified_date DESC
																											  
																																																				  
																											  
SELECT id, owner_id, access_level, created_date, modified_date, name, description, file_name, submission_id, doc_type, form_id, dom_id, draw_id, topic_id 
FROM alpn_vault
																											  
UNION
																											  
SELECT v.id, v.owner_id, v.access_level, v.created_date, v.modified_date, v.name, v.description, v.file_name, v.submission_id, v.doc_type, v.form_id, v.dom_id, v.draw_id, v.topic_id 
FROM alpn_vault v
JOIN alpn_proteams p
ON v.topic_id = p2.connected_topic_id AND v2.access_level <= p2.access_level																												  
WHERE owner_id = '@v1'
																											  																											  


WHERE proteam_member_id = '@v1'
																											  
																											  
ORDER BY modified_date DESC
																											  
																											  
																											  
																											  
CREATE VIEW alpn_connected_topics_view AS
SELECT v2.id, v2.owner_id, v2.access_level, v2.created_date, v2.modified_date, v2.name, v2.description, v2.file_name, v2.submission_id, v2.doc_type, v2.form_id, v2.dom_id, v2.draw_id, v2.topic_id, p2.proteam_member_id
FROM alpn_vault v2
JOIN alpn_proteams p2
ON v2.topic_id = p2.connected_topic_id AND v2.access_level <= p2.access_level	


WHERE p2.connected_topic_id > 0 																										  
																											  
																											 																									  
																											  
																											  
																											  
																											  
																										
CREATE VIEW alpn_network_view AS
SELECT *
FROM alpn_connections
LEFT JOIN alpn_network
ON alpn_connections.connection_id = alpn_network.id	
	
	
CREATE VIEW alpn_forms_view AS
SELECT f.owner_id AS o_id, s.*
FROM alpn_forms f
LEFT JOIN alpn_form_sources s
ON f.form_id = s.id	
	
	
DELIMITER $$
CREATE TRIGGER `create_draw_id_vault` BEFORE INSERT ON `alpn_vault` FOR EACH ROW BEGIN
    SET NEW.dom_id = uuid_short(); 
END
$$
DELIMITER ;
	
	
//Register the block by adding it to the page.	
$html .= "
<div class='wp-block-kadence-rowlayout alignnone'>
	<div id='kt-layout-id_4e38e7-c3' class='kt-row-layout-inner  kt-layout-id_4e38e7-c3'>
		<div class='kt-row-column-wrap kt-has-3-columns kt-gutter-default kt-v-gutter-default kt-row-valign-top kt-row-layout-right-half kt-tab-layout-inherit kt-m-colapse-left-to-right kt-mobile-layout-row'>
			<div class='wp-block-kadence-column inner-column-1 kadence-column_91ad9e-9d'>
				<div class='kt-inside-inner-col'>
					<p>Apple</p>
				</div>
			</div>
			<div class='wp-block-kadence-column inner-column-2 kadence-column_5f52bf-02'>
				<div class='kt-inside-inner-col'>
					<p>Orange</p>
				</div>
			</div>
			<div class='wp-block-kadence-column inner-column-3 kadence-column_27732d-65'>
				<div class='kt-inside-inner-col'>
					<p>Pickle</p>
				</div>
			</div>
		</div>
	</div>
</div>
<div class='wp-block-kadence-rowlayout alignnone'>
	<div id='kt-layout-id_8efe5c-05' class='kt-row-layout-inner  kt-layout-id_8efe5c-05'>
		<div class='kt-row-column-wrap kt-has-2-columns kt-gutter-default kt-v-gutter-default kt-row-valign-top kt-row-layout-right-golden kt-tab-layout-inherit kt-m-colapse-left-to-right kt-mobile-layout-row'>
			<div class='wp-block-kadence-column inner-column-1 kadence-column_ac33b0-bc'>
				<div class='kt-inside-inner-col'>
					<p>Grapes</p>
				</div>
			</div>
			<div class='wp-block-kadence-column inner-column-2 kadence-column_136f0d-23'>
				<div class='kt-inside-inner-col'>
					<p>YO YO</p>
				</div>
			</div>
		</div>
	</div>
</div>
";	

