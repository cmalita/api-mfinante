<?php


require('core/init.php');

if(isset($_GET['cif'])){
	$cif = $_GET['cif'];
} else {
	$cif = '13838336';
}

$company = new Company($cif);

header('Content-Type: application/json; charset=utf-8');
echo $company->getJson();