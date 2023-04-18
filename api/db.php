<?php
require '../vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

$connectionString = "mongodb+srv://YOURACCOUNT:PASSWARD@cluster0.evsebmk.mongodb.net/assignment05?retryWrites=true&w=majority";
$database = (new MongoDB\Client($connectionString))->selectDatabase('assignment05');
