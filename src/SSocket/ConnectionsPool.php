<?php
namespace App\SSocket;

use React\Socket\ConnectionInterface;
use App\SSocket\Session;

class ConnectionsPool
{
   protected $connections;
   public $socket;

    public function __construct(ServerSocket $socket)
    {
        $this->socket = $socket;
        $this->connections = new \SplObjectStorage();
    }

    public function __get($property){
        switch ($property)
        {
            case 'connections':
               return  $this->connections;
                break;

        }
    }


    /**
     * @param ConnectionInterface $connection
     */
    public function add(ConnectionInterface $connection)
    {
        $session = new Session();
        $this->setConnectionData($connection, []);
        $session->id = $this->connections->getHash($connection);
        $session->ip = $connection->getRemoteAddress();
        $this->setConnectionData($connection, $session);
        $this->send($connection, $this->socket->onOpen($connection));
        echo 'Всего соединений' . $this->connections->count();
    }

    /**
     * @param ConnectionInterface $connection
     */
   public function prepareData($connection,$data)
   {
      if($this->decode($data)['type'] == 'close'){
          $this->close($connection);
      } else {
          $type = json_decode($this->decode($data)['payload'])->type;
          switch ($type){
              case 'subscribe': {
                  $this->subsriber(json_decode($this->decode($data)['payload'])->topic,
                      $connection);
                  break;}
              case 'publish': {
                  $this->publish(json_decode($this->decode($data)['payload'])->topic, $connection);
                  break;}
          }
          echo $type;
      }
   }

   private function subsriber($topic, $connecton)
   {
     $data =$this->getConnectionData($connecton);
     $data->topic = $topic;
     $this->setConnectionData($connecton,$data);
     $this->send($connecton,['type' => 'signed',$this->socket->onSubscriber()]);

   }

   public function publish($topic,$connection = null, $data=null)
   {
       $_data = $this->socket->onPublish($data);
       foreach ($this->connections as $conn) {
           $data = $this->getConnectionData($conn);
           if($connection) {
                if ($conn !== $connection && $topic == $data->topic)
                    $this->send($conn, ['type' => 'subscribe', 'data' => $_data]);
            } else
                $this->send($conn, ['type' => 'subscribe', 'data' => $_data]);

       }
   }



   private function send($connection,$data)
   {
       $_data = null;
       if (json_encode($data))
            $_data = json_encode($data);
        else
            $_data = $data;
       $connection->write(($this->encode($_data)));
   }



   public function message(ConnectionInterface $connection,$data)
   {
         $msg=$this->decode($data)['payload'];

       $connection->write($this->encode($msg));
       echo $msg;
   }

   public function close(ConnectionInterface $connection)
   {

       $this->connections->offsetUnset($connection);
       echo 'Всего соединений'. $this->connections->count();
   }

    protected function initEvents(ConnectionInterface $connection)
    {
        // On receiving the data we loop through other connections
        // from the pool and write this data to them
        $connection->on(
            'data', function ($data) use ($connection) {
            $connectionData = $this->getConnectionData($connection);

            // It is the first data received, so we consider it as
            // a users name.
            if (empty($connectionData)) {
                $this->addNewMember($data, $connection);
                return;
            }

            $name = $connectionData['name'];
            $this->sendAll("$name: $data", $connection);
        }
        );

        // When connection closes detach it from the pool

    }



    protected function setConnectionData(ConnectionInterface $connection, $data)
    {
        $this->connections->offsetSet($connection, $data);
    }

    public function getConnectionData(ConnectionInterface $connection)
    {
        return $this->connections->offsetGet($connection);
    }

    /**
     * Send data to all connections from the pool except
     * the specified one.
     *
     * @param mixed $data
     * @param ConnectionInterface $except
     */






    protected function encode($payload, $type = 'text', $masked = false)
    {
        $frameHead = array();
        $payloadLength = strlen($payload);

        switch ($type) {
            case 'text':
                // first byte indicates FIN, Text-Frame (10000001):
                $frameHead[0] = 129;
                break;

            case 'close':
                // first byte indicates FIN, Close Frame(10001000):
                $frameHead[0] = 136;
                break;

            case 'ping':
                // first byte indicates FIN, Ping frame (10001001):
                $frameHead[0] = 137;
                break;

            case 'pong':
                // first byte indicates FIN, Pong frame (10001010):
                $frameHead[0] = 138;
                break;
        }

        // set mask and payload length (using 1, 3 or 9 bytes)
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }
            // most significant bit MUST be 0
            if ($frameHead[2] > 127) {
                return array('type' => '', 'payload' => '', 'error' => 'frame too large (1004)');
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }

        // convert frame-head to string:
        foreach (array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        }
        if ($masked === true) {
            // generate a random mask:
            $mask = array();
            for ($i = 0; $i < 4; $i++) {
                $mask[$i] = chr(rand(0, 255));
            }

            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);

        // append payload to frame:
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }

        return $frame;
    }


    protected function decode($data)
    {
        $unmaskedPayload = '';
        $decodedData = array();

        // estimate frame type:
        $firstByteBinary = sprintf('%08b', ord($data[0]));
        $secondByteBinary = sprintf('%08b', ord($data[1]));
        $opcode = bindec(substr($firstByteBinary, 4, 4));
        $isMasked = ($secondByteBinary[0] == '1') ? true : false;
        $payloadLength = ord($data[1]) & 127;

        // unmasked frame is received:
        if (!$isMasked) {
            return array('type' => '', 'payload' => '', 'error' => 'protocol error (1002)');
        }

        switch ($opcode) {
            // text frame:
            case 1:
                $decodedData['type'] = 'text';
                break;

            case 2:
                $decodedData['type'] = 'binary';
                break;

            // connection close frame:
            case 8:
                $decodedData['type'] = 'close';
                break;

            // ping frame:
            case 9:
                $decodedData['type'] = 'ping';
                break;

            // pong frame:
            case 10:
                $decodedData['type'] = 'pong';
                break;

            default:
                return array('type' => '', 'payload' => '', 'error' => 'unknown opcode (1003)');
        }

        if ($payloadLength === 126) {
            $mask = substr($data, 4, 4);
            $payloadOffset = 8;
            $dataLength = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset;
        } elseif ($payloadLength === 127) {
            $mask = substr($data, 10, 4);
            $payloadOffset = 14;
            $tmp = '';
            for ($i = 0; $i < 8; $i++) {
                $tmp .= sprintf('%08b', ord($data[$i + 2]));
            }
            $dataLength = bindec($tmp) + $payloadOffset;
            unset($tmp);
        } else {
            $mask = substr($data, 2, 4);
            $payloadOffset = 6;
            $dataLength = $payloadLength + $payloadOffset;
        }

        /**
         * We have to check for large frames here. socket_recv cuts at 1024 bytes
         * so if websocket-frame is > 1024 bytes we have to wait until whole
         * data is transferd.
         */
        if (strlen($data) < $dataLength) {
            return false;
        }

        if ($isMasked) {
            for ($i = $payloadOffset; $i < $dataLength; $i++) {
                $j = $i - $payloadOffset;
                if (isset($data[$i])) {
                    $unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
                }
            }
            $decodedData['payload'] = $unmaskedPayload;
        } else {
            $payloadOffset = $payloadOffset - 4;
            $decodedData['payload'] = substr($data, $payloadOffset);
        }

        return $decodedData;
    }

    public function onOpen($conection,$data){

    }
}
