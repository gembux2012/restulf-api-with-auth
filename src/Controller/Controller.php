<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 06.11.19
 * Time: 13:30
 */

namespace App\Controller;

use React\Filesystem\FilesystemInterface;
use React\Filesystem\Stream\ReadableStream;
use React\Http\Response;


const DS = DIRECTORY_SEPARATOR;

define('ROOT_PATH', realpath(__DIR__ . '/../'));
define('ROOT_PATH_TEMPLATES', ROOT_PATH . DS . 'Templates');
define('ROOT_PATH_PUBLIC', ROOT_PATH . DS . 'public');

class  Controller
{
    private $filesystem;

    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

     public function GetFile(ServerRequestInterface $request)
    {
        $filePath = $this->getFilePath($request);

        if ($filePath === null) {
            return new Response(200, ['Content-Type' => 'text/plain'], 'Video streaming server');
        }

        return $this->makeResponseFromFile($filePath);
    }

    /**
     * @param string $filePath
     * @return PromiseInterface|Response
     */
    protected function makeResponseFromFile($filePath)
    {
        $file = $this->filesystem->file($filePath);
//         $file->
        return $file->exists()->then(

            function () use ($file) {
                /*
                return $file->open('r') ->then(
                    function (ReadableStream $stream) use($file) {
                       // $last_modified_time = $file->time();
                        //$etag = md5_file($file);
                        return new Response(200, [], $stream);
                    }


                );
                */
               return  $file->getContents()->then(function ($contents) {
                   return new Response(200, [], $contents);
                });

            }, function () {
            return new Response(404, ['Content-Type' => 'text/plain'], "This video doesn't exist on server.");
        });
    }

    /**
     * @param ServerRequestInterface $request
     * @return string|null
     */
    private function getFilePath(ServerRequestInterface $request)
    {
        $file = $request->getQueryParams()['video'] ?? null;

        if ($file === null) {
            return null;
        }

        return __DIR__ . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . basename($file);
    }

    protected static function getRealPath($path){

    }

    public function if_modified(){
        $last_modified_time = filemtime($file);
        $etag = md5_file($file);
        $header = [
            'Modified-Since' => gmdate("D, d M Y H:i:s", $last_modified_time) . " GMT",
            'Etag' => $etag,

        ];
    }
}