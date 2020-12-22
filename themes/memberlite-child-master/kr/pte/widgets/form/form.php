<?php
use \koolreport\KoolReport;


class PteForm extends \koolreport\core\Widget
{

    protected function onInit()
    {		
    }

    protected function onRender()
    {
		$params = $this->params;		
		$this->template("form_1", $params);	
    }
}

?>