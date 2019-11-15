<?php

require __DIR__ . '/vendor/autoload.php';

use React\Socket\ConnectionInterface;
use App\ConnectionsPool;
use React\Http\Server;
use React\Http\StreamingServer;

class HttpServer{
    private $_port, $_host,$_server;

    public function __construct(\React\EventLoop\LoopInterface $loop,$host,$port,$socket)
    {
        $this-> _port = $port;
        $this->_host = $host;
        $this->_server = new Server(new \App\ResponceHeader());
        $this->_server ->listen($socket);
        echo "Listening on {$socket->getAddress()}\n";

    }

}

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server('127.0.0.1:8080', $loop);
$http = new HttpServer($loop,'127.0.0.1','8080',$socket);

//$pool = new ConnectionsPool();

/*
$socket->on(
    'connection', function (ConnectionInterface $connection) use ($pool) {
    echo 'connection';
    $connection > on(
        'data', function ($data) use ($connection) {
        echo 'Data';
    });
    });


//    $pool->add($connection);
*/



$loop->run();
