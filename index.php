<?php
use App\Auth\JwtAuthenticator;
use App\Auth\JwtEncoder;
use App\Auth\Guard;
use App\Controller\CreateUser;
use App\Controller\DeleteUser;
use App\Controller\ListUsers;
use App\Controller\Login;
use App\Controller\UpdateUser;
use App\Controller\ViewUser;
use App\Controller\DefaultController;
use App\Router;
use App\Users;
use App\Controller\Publicher;
use App\Controller\LoadTemlate;
use App\ConnectionsPool;
use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use React\Http\Server;
use React\MySQL\Factory;
use React\Filesystem\Filesystem;
use React\ChildProcess\Process;
use React\Socket\ConnectionInterface;
use React\Filesystem\Stream\ReadableStream;

require __DIR__ . '/vendor/autoload.php';

$loop = \React\EventLoop\Factory::create();
$factory = new Factory($loop);
$db = $factory->createLazyConnection('root:Password00@localhost/mydb');
$users = new Users($db);
$authenticator = new JwtAuthenticator(new JwtEncoder('secret'), $users);
$fs = Filesystem::create($loop);


$routes = new RouteCollector(new Std(), new GroupCountBased());
$routes->get('/{name:[a-z]+}', new LoadTemlate($fs));
$routes->get('/public/{ndomname:.+}', new Publicher($fs));
$routes->get('/', new DefaultController($fs));
$routes->post('/login', new Login($authenticator));
$routes->get('/users/user', new ListUsers($users));
$routes->post('/users', new CreateUser($users));
$routes->get('/users/{id}', new ViewUser($users));
$routes->put('/users/{id}', new UpdateUser($users));
$routes->delete('/users/{id}', new DeleteUser($users));


$auth = new Guard('/users', $authenticator);

$credentials = ['user' => 'secret'];

//$basicAuth = new PSR15Middleware(
//    $loop,
//    \Middlewares\BasicAuthentication::class, [$credentials]
//);
//$server = new Server([$basicAuth, new Router($routes)]);

$server = new Server([$auth, new Router($routes)]);
$socket = new \React\Socket\Server( '127.0.0.1:8000', $loop);
//$socket = new \React\Socket\SecureServer($socket, $loop, array(
  //  'local_cert' =>  __DIR__ . '/localhost.pem'
//));
$server->listen($socket);



$server->on('error', function (Exception $exception) {
    echo $exception->getMessage() . PHP_EOL;
});
echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . "\n";
/*
$serverSocket = new Process('php serverSocketphp');
$serverSocket->start($loop);
$serverSocket->stdout->on('data', function($data) {
    echo $data;
});
*/

$loop->run();

