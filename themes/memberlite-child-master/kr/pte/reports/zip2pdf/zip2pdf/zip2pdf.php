<?php

use \koolreport\KoolReport;
use \koolreport\export\Exportable;

class zip2pdf extends \koolreport\KoolReport
{
	use \koolreport\export\Exportable;

	public function settings() {
		$hostDomainName = PTE_HOST_DOMAIN_NAME;
		return [
           "assets"=>array(
                "path"=>"../../../../assets",
                "url"=>"https://{$hostDomainName}/wp-content/themes/memberlite-child-master/kr/assets"
            )
		];
	}


    public function setup()
    {

		}
}
