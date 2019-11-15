<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 30.10.19
 * Time: 13:27
 */

namespace App\Controller;

use App\JsonResponse;
use App\UserNotFoundError;
use Psr\Http\Message\ServerRequestInterface;
use React\Filesystem\FilesystemInterface;
use React\Filesystem\Stream\ReadableStream;
use React\Http\Response;





class DefaultController extends Controller
{
    /*
    public function __construct(FilesystemInterface $filesystem)
    {
        parent::__construct( $filesystem);

    }

    /**
     * @param ServerRequestInterface $request
     * @return Response|PromiseInterface
     */
    public function __invoke(ServerRequestInterface $request)
    {
        $realPath = ROOT_PATH_TEMPLATES.DS.'Index.html';
      return $this->makeResponseFromFile( $realPath);
        //        return  JsonResponse::ok("hellow I server");
    }
}