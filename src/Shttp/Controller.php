<?php

namespace App\Shttp;

use Psr\Http\Message\ServerRequestInterface;
use React\Filesystem\FilesystemInterface;
use React\Filesystem\Stream\ReadableStream;
use React\Http\Response;


const DS = DIRECTORY_SEPARATOR;

define('ROOT_PATH', realpath( 'src'));
define('ROOT_PATH_TEMPLATES', ROOT_PATH . DS . 'Templates');
define('ROOT_PATH_PUBLIC', ROOT_PATH . DS . 'public');

class  Controller
{
    private $filesystem;
    public $request;

    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool|string
     */
    private function router()
    {
        $routePath = $this->request->getUri()->getPath();

        $matches = explode('/', $routePath);
        if ($matches) {
            switch ($matches[1]) {
                case '':
                    {
                        return ROOT_PATH_TEMPLATES . DS . 'Index.html';
                    }
                case 'public':
                    {
                        return ROOT_PATH .DS. $routePath;
                    }

                default :
                    {
                        return ROOT_PATH_TEMPLATES . DS .$routePath.'.html';
                    }
            }
        }
    }


    /**
     * @param string $filePath
     * @return \React\Promise\PromiseInterface
     */
    private function makeResponseFromFile($filePath)
    {
        $last_modified_time = filemtime($filePath);
        $etag = md5_file($filePath);
        $header = [
            'Modified-Since' => gmdate("D, d M Y H:i:s", $last_modified_time) . " GMT",
            'Etag' => $etag,
            //'Content-Type' => $content_type
        ];

        if (@strtotime($this->request->getHeaderLine('If-Modified-Since')) == $last_modified_time ||
            $this->request->getHeaderLine('If-None-Match') == $etag
        ) {
            return new Response(304);
        } else {

            $file = $this->filesystem->file($filePath);
            return $file->exists()
                ->then(function () use ($file) {
                    return $file->open('r');
                })
                ->then(function (ReadableStream $stream) use ($header) {
                    return new Response(
                        200,
                        $header,
                        $stream
                    );
                },
                    function () {
                        return new Response(
                            404,
                            ['Content-Type' => 'text/plain'],
                            "not found"
                        );
                    });
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return \React\Promise\PromiseInterface
     */
    public function __invoke(ServerRequestInterface $request)
    {
        $this->request = $request;
        $path = $this->router();

        if ($path) {
            return $this->makeResponseFromFile($path);
        } else {
            echo "path not found";
        }
    }


}