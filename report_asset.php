<?php

require 'IlluminateJasper.php';

Illuminate\Jasper::$url = 'https://your_username:your_password@jasper.yourdomain.com:8443/jasperserver/';

$file = new Illuminate\Jasper\ReportAsset(array(
	'jsessionid' => $_GET['id'],
	'file'       => $_GET['uri'],
));
$file->output();