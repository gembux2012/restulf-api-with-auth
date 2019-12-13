<?php

namespace App\SSocket;

use React;

class ServerSocket
{
    private $ConnectionPool, $loop;
    public  $onSubscriber ;
    public $onOpen;
    public $onPublish;
    public function __construct($host, $port)
    {
        $_host = $host;
        $_port = $port;
        $this->ConnectionPool = new ConnectionsPool($this);
        $pool =$this->ConnectionPool;
        $loop = React\EventLoop\Factory::create();
        $this->loop =$loop;
        $socket = new React\Socket\Server($_host . ':' . $_port, $loop);

        $socket->on('connection', function (React\Socket\ConnectionInterface $connection) use ($pool) {
            $connection->on('data', function ($chunk) use ($pool, $connection) {
                $headers = [];
                $line = preg_split("/\r\n|\n|\r/", $chunk);
                foreach ($line as $header) {
                    if (preg_match('/\A(\S+): (.*)\z/', $header, $matches)) {
                        $headers[$matches[1]] = $matches[2];
                    }
                }

                if (empty($headers['Sec-WebSocket-Key'])) {
                    $pool->prepareData($connection, $chunk);
                } else {
                    $seckey = $headers['Sec-WebSocket-Key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
                    $seckey = sha1($seckey, true);
                    $seckey = base64_encode($seckey);
                    $headers = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
                        "Upgrade: websocket\r\n" .
                        "Connection: Upgrade\r\n" .
                        "Sec-WebSocket-Accept:$seckey\r\n\r\n";
                    //"Sec-WebSocket-Protocol: wamp.2.json\r\n\r\n";

                    $connection->write($headers);
                    $pool->add($connection);

                }
            });


        });



    }

    public function onOpen($connect){
        if (is_callable($this->onOpen))
            return call_user_func($this->onOpen, $connect);
        else
            return $this->ConnectionPool->getConnectionData($connect);
    }

    public function onPublish($data){
        if (is_callable($this->onPublish))
            return call_user_func($this->onPublish,$data);
        else
            return $data;
    }

    public function onSubscriber(){
        if (is_callable($this->onSubscriber))
            return call_user_func($this->onSubscriber);
        else
            return '';
    }



    public function Publish($topic, $conn,$data)
    {
        $this->ConnectionPool->publish($topic, $conn,$data);
    }

    public function getConnData($connection){
         return $this->ConnectionPool->getConnectionData($connection);
    }

    public function getConnections(){
        return $this->ConnectionPool->connections;
    }

    public function run(){
        $this->loop->run();
    }
}
