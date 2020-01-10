<?php
use App\Auth\JwtAuthenticator;
use App\Auth\JwtEncoder;
use App\Auth\Guard;
use App\Users;
use App\ConnectionsPool;
use React\Http\Server;
use React\MySQL\Factory;

require __DIR__ . '/vendor/autoload.php';

$loop = \React\EventLoop\Factory::create();
$factory = new Factory($loop);
$db = $factory->createLazyConnection('root:Password00@localhost/mydb');
$users = new Users($db);
$authenticator = new JwtAuthenticator(new JwtEncoder('secret'), $users);
//$adapter = new \React\Filesystem\ChildProcess\Adapter($loop);
$fs = \React\Filesystem\Filesystem::create($loop);


$auth = new Guard('/users', $authenticator);

$credentials = ['user' => 'secret'];

$controller = new \App\Shttp\Controller($fs);

$server = new Server([$auth, $controller]);
$socket = new \React\Socket\Server( '127.0.0.1:8000', $loop);
//$socket = new \React\Socket\SecureServer($socket, $loop, array(
  //  'local_cert' =>  __DIR__ . '/localhost.pem'
//));
$server->listen($socket);

$server->on('error', function (Exception $exception) {
    echo $exception->getMessage() . PHP_EOL;
   // echo 'error';
});
echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . "\n";


$loop->run();

