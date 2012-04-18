<?php
require_once 'classes/Application.php';

$app = new Application();
if(getenv('APPLICATION_ENV') == 'dev') {
    $app->setConfigFile('config/config.local.ini');
} else {
    $app->setConfigFile('config/config.ini');
}

$app->run();