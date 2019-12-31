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

    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool|string
     */
    private function router(ServerRequestInterface $request)
    {
        $routePath = $request->getUri()->getPath();

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
        $file = null;
        $file = $this->filesystem->file($filePath);
        return $file->exists()
            ->then(function () use ($file) {
                return $file->open('r');
            })
            ->then(function (ReadableStream $stream) {
                return new Response(
                    200,
                    [],
                    $stream
                );
            })
            ->otherwise(function () {
                return new Response(
                    404,
                    ['Content-Type' => 'text/plain'],
                    "not found"
                );
            });
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $path = $this->router($request);

        if ($path) {
            return $this->makeResponseFromFile($path);
        } else {
            echo "path not found";
        }
    }

    public function if_modified()
    {
        $last_modified_time = filemtime($file);
        $etag = md5_file($file);
        $header = [
            'Modified-Since' => gmdate("D, d M Y H:i:s", $last_modified_time) . " GMT",
            'Etag' => $etag,

        ];
    }
}