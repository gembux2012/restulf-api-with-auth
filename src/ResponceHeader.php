<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 12.11.19
 * Time: 9:04
 */

namespace App;


use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;

class ResponceHeader
{
    public function __invoke(ServerRequestInterface $request)
    {
        $headers = $request->getHeaders();
        $key = $headers['Sec-WebSocket-Key'][0];
        $hash = $key.'258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
        $hash = sha1($hash,true);
        $hash = base64_encode($hash);
        if ($headers['Upgrade'][0] ==  'websocket') {
            return new Response(
                101,
                array(
                    'Upgrade' => 'websocket',
                    'Connection' => 'Upgrade',
                    'Sec-WebSocket-Accept' => $hash
                    //'Sec-WebSocket-Protocol' => 'chat'
                ),''
            );

        }


    }

}