<?php

use \koolreport\widgets\koolphp\Table;

?>
<div class="report-content">
    <?php
		if (count($this->dataStore('topic_flattened')->data())) {
			PteForm::create(array("report_settings" => $this->params, "data" => $this->dataStore('topic_flattened')->data()[0]));	
		}	
    ?>
</div>