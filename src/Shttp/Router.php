<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 27.12.19
 * Time: 15:36
 */

namespace App\Shttp;


class Router
{
    public function __construct()
    {
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $routePath = $request->getUri()->getPath();

        if (preg_match("~(*.?)/(*.?).(*.?)~U", $routePath, $matches)) {
            switch ($matches){
                case '/':
            }
        }
    }

}
