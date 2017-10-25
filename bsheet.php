<?php

require('core/init.php');

if (isset($_GET['cif'])){
	$cif = $_GET['cif'];
} else {
	$cif = '6859662';
}

if (isset($_GET['year'])){
	$year = $_GET['year'];
} else {
	$year = '2009';
}

$bilant = new BalanceSheet($cif, $year);

header('Content-Type: application/json; charset=utf-8');
echo $bilant->getJson();