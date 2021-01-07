<?php

use \koolreport\KoolReport;
use \koolreport\export\Exportable;

class faxcoversheet extends \koolreport\KoolReport
{
	use \koolreport\export\Exportable;


	function settings() {
	 $hostDomainName = PTE_HOST_DOMAIN_NAME;
	 return [
					"assets" =>
						 array(
								 "path"=>"../../../../assets",
								 "url"=>"https://{$hostDomainName}/wp-content/themes/memberlite-child-master/kr/assets"
						 ),
					 "dataSources" =>
						 array()
	 ];
 }


    public function setup()
    {

		}
}
