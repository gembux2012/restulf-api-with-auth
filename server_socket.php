<?php

require __DIR__ . '/vendor/autoload.php';

use React\Socket\ConnectionInterface;
use App\ConnectionsPool;
use React\Http\Server;
use React\Http\StreamingServer;





$pool = new ConnectionsPool();
$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server('127.0.0.1:8080', $loop);




$socket->on('connection', function (React\Socket\ConnectionInterface $connection) use($pool){
    $connection->on('data', function ($chunk) use($pool,$connection){
        $headers = [];
        $line = preg_split("/\r\n|\n|\r/", $chunk);
        foreach ($line as $header) {
            if (preg_match('/\A(\S+): (.*)\z/', $header, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }
//           echo $headers;
        if (empty($headers['Sec-WebSocket-Key'])) {
          $pool->prepareData($connection, $chunk);
        } else{
            $seckey = $headers['Sec-WebSocket-Key'].'258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
            $seckey = sha1($seckey,true);
            $seckey = base64_encode($seckey);
         $headers= "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
             "Upgrade: websocket\r\n" .
        "Connection: Upgrade\r\n" .
        "Sec-WebSocket-Accept:$seckey\r\n\r\n" ;
        //"Sec-WebSocket-Protocol: wamp.2.json\r\n\r\n";

         $connection->write($headers);
         //????????? handshake
            //????????? ? ???
            $pool->add($connection);

        }
    });


});







$loop->run();
