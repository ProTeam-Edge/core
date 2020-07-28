<?php

require_once "SakilaRental.php";
$report = new SakilaRental;

$report->run()
->export('SakilaRentalPdf')
->pdf(array(
    "format"=>"Letter",
    "orientation"=>"portrait"
))
->toBrowser("sakila_rental.pdf");