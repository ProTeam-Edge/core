<?php

use \koolreport\KoolReport;
use \koolreport\processes\JsonSpread;
use \koolreport\export\Exportable;
use \koolreport\processes\CalculatedColumn;
use \koolreport\processes\RemoveColumn;

class c1 extends \koolreport\KoolReport
{
	use \koolreport\export\Exportable;

//TODO FIX CERT _ Figure out how to make it check. Move to pte_config.

	public function settings() {
		return [
           "assets"=>array(
                "path"=>"../../../../assets",
                "url"=>"https://proteamedge.com/wp-content/themes/memberlite-child-master/kr/assets"
            ),
			"dataSources"=>[
				"proteam_edge"=>[
                    "connectionString"=>"mysql:host=sky0001654.mdb0001643.db.skysql.net:5003;dbname=proteam_edge",   //TODO CENTRALIZE THIS BEFORE MAKING MORE REPORTS
					"username"=>"DB00002069",
					"password"=>"U5lp93,RmjZbSs7199kn1X4",
					"charset"=>"utf8",
					"options"=>[
						\PDO::MYSQL_ATTR_SSL_CA =>'/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/skysql_chain.pem',
						\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
					]
				]
			]
		];
	}


    public function setup()
    {

		$reportSettings = $this->params;
		$topicId = $reportSettings['topic_id'];

      $this->src('proteam_edge')
        ->query("SELECT t.*, tt.id AS topic_type_id, tt.form_id, tt.name AS topic_name, tt.icon, tt.topic_type_meta FROM alpn_topics t LEFT JOIN alpn_topic_types tt ON t.topic_type_id = tt.id WHERE t.id = :id")
        ->params(array(
            ":id"=>$topicId
        ))
		->pipe(new CalculatedColumn(array(
			"image_url"=>array(
				"exp"=>function($data){
					return $data["image_handle"] ? "https://cdn.filestackcontent.com/resize=height:96,width:96,fit:crop/circle/" . $data["image_handle"] : "";
				},
				"type"=>"string",
			)
		)))
		->pipe(new CalculatedColumn(array(
			"logo_url"=>array(
				"exp"=>function($data){
					return $data["logo_handle"] ? "https://cdn.filestackcontent.com/output=format:jpg/resize=width:300,height:100/" . $data["logo_handle"] : "";
				},
				"type"=>"string",
			)
		)))
   		->pipe(new RemoveColumn(array(
            "draw_id"
        )))
        ->pipe($this->dataStore('topic_flattened'));

    }
}


/*

        ->pipe(new JsonSpread(array(
            "topic_content"
        )))

*/
