<?php
require_once __DIR__.'/../vendor/autoload.php';
use ResourceHub\API\Read\Read;

$resource = new Read('resourcehub');
$resource->getStats();
echo $resource->getData();
?>