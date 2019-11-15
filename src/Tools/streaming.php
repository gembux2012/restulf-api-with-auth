<?php

namespace App\Tools;



use React\Http\Server;
use React\Http\Response;
use React\EventLoop\Factory;
use React\Promise\PromiseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class Streaming
{
    private $filesystem;

    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param ServerRequestInterface $request
     * @return Response|PromiseInterface
     */
    function __invoke(ServerRequestInterface $request)
    {
        $filePath = $this->getFilePath($request);

        if ($filePath === null) {
            return new Response(200, ['Content-Type' => 'text/plain'], 'Video streaming server');
        }

        return $this->makeResponseFromFile($filePath);
    }

    /**
     * @param string $filePath
     * @return PromiseInterface
     */
    private function makeResponseFromFile($filePath)
    {
        $file = $this->filesystem->file($filePath);

        return $file->exists()->then(
            function () use ($file) {
                return $file->open('r')->then(
                    function (ReadableStream $stream) {
                        return new Response(200, ['Content-Type' => 'video/mp4'], $stream);
                    }
                );
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
}


