<?php

require __DIR__ . '/vendor/autoload.php';

use App\SSocket\ServerSocket;

$server = new ServerSocket('127.0.0.1', '8080');

$server->onSubscriber = function () use ($server) {
    $connections = $server->getConnections();
    $data = array();

    foreach ($connections as $conn) {
        $session = '';
        $session = ['id' =>$server->getConnData($conn)->id,
            'ip' =>$server->getConnData($conn)->ip,'user' =>$server->getConnData($conn)->user];

         $data[]=$session;
    }
     $server->Publish('all',null,$data);
    return '';
};

$server->run();