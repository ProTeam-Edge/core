<?php
use \koolreport\KoolReport;


class PteForm extends \koolreport\core\Widget
{

    protected function onInit()
    {		
    }

    protected function onRender()
    {
		
		echo $this->template("form_1", $this->params['data'], true);	
		
	
    }
}

?>