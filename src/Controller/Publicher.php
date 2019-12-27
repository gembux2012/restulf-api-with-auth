<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 07.11.19
 * Time: 10:46
 */

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface;
use React\Filesystem\FilesystemInterface;
use React\Filesystem\Stream\ReadableStream;
use React\Http\Response;


class Publicher extends Controller
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

        $realPath = ROOT_PATH.$request->getRequestTarget();
        return $this->makeResponseFromFile( $realPath);
        //        return  JsonResponse::ok("hellow I server");
    }

}