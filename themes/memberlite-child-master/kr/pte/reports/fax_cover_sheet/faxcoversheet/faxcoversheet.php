<?php

use \koolreport\KoolReport;
use \koolreport\export\Exportable;

class faxcoversheet extends \koolreport\KoolReport
{
	use \koolreport\export\Exportable;

	public function settings() {
		return [
           "assets"=>array(
                "path"=>"../../../../assets",
                "url"=>"https://proteamedge.com/wp-content/themes/memberlite-child-master/kr/assets"
            )
		];
	}


    public function setup()
    {

		}
}
