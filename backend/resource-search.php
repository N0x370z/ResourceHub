<?php
require_once __DIR__.'/../vendor/autoload.php';
use ResourceHub\API\Read\Read;

$resource = new Read('resourcehub');

if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $resource->search($search);
}

echo $resource->getData();
?>